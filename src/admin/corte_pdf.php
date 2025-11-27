<?php
session_start();
include '../php/conexion.php';
require('../libs/fpdf/fpdf.php');

// Seguridad: Solo Admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Recibir fecha
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Consultas
$sql_corte = "SELECT metodo_pago, COUNT(*) as cantidad, SUM(total_final) as total 
              FROM pedidos 
              WHERE DATE(fecha_pedido) = '$fecha' 
              AND estatus_pedido != 'cancelado' 
              GROUP BY metodo_pago";
$res = $conn->query($sql_corte);

// --- FUNCIÓN MODERNA PARA ACENTOS (Reemplaza a utf8_decode) ---
function texto($str) {
    if ($str === null) return '';
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
}

class PDF extends FPDF {
    function Header() {
        // Logo
        if (file_exists('../img/logo.png')) $this->Image('../img/logo.png', 12, 8, 45);
        elseif (file_exists('../img/logo.jpg')) $this->Image('../img/logo.jpg', 10, 8, 25);
        
        // Títulos
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'ProtoHub - CORTE DE CAJA', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, texto('Reporte de Ingresos Diarios'), 0, 1, 'C');
        $this->Ln(15);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, texto('Página ') . $this->PageNo() . '/{nb} - Generado por: ' . texto($_SESSION['nombre_usuario']), 0, 0, 'C');
    }
}

// Iniciar PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0);

// Subtítulo con fecha
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, texto("Fecha del Corte: " . date('d/m/Y', strtotime($fecha))), 0, 1, 'L');
$pdf->Ln(5);

// --- TABLA DE DESGLOSE ---
$pdf->SetFillColor(13, 71, 161); // Azul ProtoHub
$pdf->SetTextColor(255); // Blanco
$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(80, 10, texto('Método de Pago'), 1, 0, 'L', true);
$pdf->Cell(30, 10, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Ventas', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Total ($)', 1, 1, 'R', true);

$pdf->SetTextColor(0); // Volver a negro
$pdf->SetFont('Arial', '', 10);

$total_dia = 0;
$total_efectivo = 0;
$total_digital = 0;

if($res->num_rows > 0){
    while($row = $res->fetch_assoc()){
        $total_dia += $row['total'];
        
        // Clasificar
        $es_efectivo = ($row['metodo_pago'] == 'efectivo' || $row['metodo_pago'] == 'efectivo_contraentrega');
        
        if ($es_efectivo) {
            $total_efectivo += $row['total'];
            $tipo = "Efectivo";
        } else {
            $total_digital += $row['total'];
            $tipo = "Banco";
        }

        // Imprimir fila
        $pdf->Ln();
        $pdf->Cell(80, 8, texto(ucfirst(str_replace('_', ' ', $row['metodo_pago']))), 1, 0, 'L');
        $pdf->Cell(30, 8, $tipo, 1, 0, 'C');
        $pdf->Cell(30, 8, $row['cantidad'], 1, 0, 'C');
        $pdf->Cell(50, 8, '$' . number_format($row['total'], 2), 1, 0, 'R');
    }
} else {
    $pdf->Ln();
    $pdf->Cell(190, 10, texto('No hay ventas registradas en esta fecha.'), 1, 0, 'C');
}

$pdf->Ln(15);

// --- RESUMEN FINAL (CUADROS GRANDES) ---
$pdf->SetFont('Arial', 'B', 12);

// Cuadro Efectivo
$pdf->SetFillColor(220, 255, 220); // Verde claro
$pdf->Cell(90, 10, texto('TOTAL EN CAJA (EFECTIVO)'), 1, 0, 'L', true);
$pdf->Cell(50, 10, '$' . number_format($total_efectivo, 2), 1, 1, 'R', true);

// Cuadro Banco
$pdf->SetFillColor(220, 240, 255); // Azul claro
$pdf->Cell(90, 10, texto('TOTAL DIGITAL (BANCO)'), 1, 0, 'L', true);
$pdf->Cell(50, 10, '$' . number_format($total_digital, 2), 1, 1, 'R', true);

// Gran Total
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(90, 12, 'VENTA TOTAL DEL DIA', 0, 0, 'R');
$pdf->SetTextColor(13, 71, 161);
$pdf->Cell(50, 12, '$' . number_format($total_dia, 2), 0, 1, 'R');

// Espacio para firmas
$pdf->SetTextColor(0);
$pdf->Ln(30);
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(90, 0, '_________________________', 0, 0, 'C');
$pdf->Cell(90, 0, '_________________________', 0, 1, 'C');
$pdf->Ln(5);
$pdf->Cell(90, 0, 'Firma del Cajero', 0, 0, 'C');
$pdf->Cell(90, 0, 'Firma del Supervisor', 0, 1, 'C');

$pdf->Output('I', 'Corte_Caja_'.$fecha.'.pdf');
?>