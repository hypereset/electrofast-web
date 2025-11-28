<?php
session_start();
include '../php/conexion.php';
require('../libs/fpdf/fpdf.php');

// Seguridad: Solo Admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Recibir fechas
$fecha_inicio = isset($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fin']) ? $_GET['fin'] : date('Y-m-d');

// Consulta
$sql = "SELECT p.nombre, p.sku_barras, p.stock_actual, SUM(d.cantidad) as total_vendido, SUM(d.cantidad * d.precio_aplicado) as dinero_generado
        FROM detalle_pedido d
        JOIN pedidos ped ON d.id_pedido = ped.id_pedido
        JOIN productos p ON d.id_producto = p.id_producto
        WHERE ped.estatus_pedido != 'cancelado'
        AND DATE(ped.fecha_pedido) BETWEEN '$fecha_inicio' AND '$fecha_fin'
        GROUP BY p.id_producto
        ORDER BY total_vendido DESC";
$res = $conn->query($sql);

// --- FUNCIÓN DE AYUDA PARA ACENTOS (MODERNA) ---
function texto($str) {
    if ($str === null) return '';
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
}

class PDF extends FPDF {
    function Header() {
        // Logo
        if (file_exists('../img/logo.png')) $this->Image('../img/logo.png', 8, 7, 45);
        elseif (file_exists('../img/logo.jpg')) $this->Image('../img/logo.jpg', 10, 8, 25);
        
        // Títulos
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'ProtoHub - REPORTE DE VENTAS', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, texto('Análisis de Inventario y Ganancias'), 0, 1, 'C');
        $this->Ln(15);
        
        // Encabezados de Tabla
        $this->SetFillColor(13, 71, 161); // Azul
        $this->SetTextColor(255); // Blanco
        $this->SetFont('Arial', 'B', 10);
        
        $this->Cell(80, 10, 'Producto', 1, 0, 'L', true);
        $this->Cell(30, 10, 'Vendidos', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Stock', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Ingresos ($)', 1, 1, 'R', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, texto('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Iniciar PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);

$gran_total = 0;

if($res->num_rows > 0){
    while($row = $res->fetch_assoc()){
        $gran_total += $row['dinero_generado'];
        
        $stock = $row['stock_actual'];
        $vendidos = $row['total_vendido'];
        
        // Imprimir fila
        // Usamos texto() para corregir acentos en nombres de productos
        $pdf->Cell(80, 8, texto(substr($row['nombre'], 0, 40)), 1, 0, 'L');
        $pdf->Cell(30, 8, $vendidos, 1, 0, 'C');
        
        // Si stock es bajo, negrita
        if($stock < 10) $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 8, $stock, 1, 0, 'C');
        $pdf->SetFont('Arial', '', 10); // Reset
        
        $pdf->Cell(50, 8, '$' . number_format($row['dinero_generado'], 2), 1, 1, 'R');
    }
    
    // Total Final
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 10, 'TOTAL GENERADO:', 0, 0, 'R');
    $pdf->Cell(50, 10, '$' . number_format($gran_total, 2), 1, 1, 'R');

} else {
    $pdf->Cell(0, 10, texto('No se encontraron ventas en este rango de fechas.'), 1, 1, 'C');
}

// Mostrar fechas
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 5, texto("Rango del reporte: $fecha_inicio al $fecha_fin"), 0, 1, 'L');

$pdf->Output('I', 'Reporte_Ventas.pdf');
?>