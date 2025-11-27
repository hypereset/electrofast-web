<?php
session_start();
include 'php/conexion.php';
require('libs/fpdf/fpdf.php');

if (!isset($_SESSION['id_usuario'])) { header("Location: login.php"); exit; }
if (!isset($_GET['id'])) { die("Error: Falta ID"); }

$id_pedido = $_GET['id'];
$id_usuario = $_SESSION['id_usuario'];
$filtro = ($_SESSION['rol'] == 1) ? "" : "AND p.id_usuario = $id_usuario";

$sql = "SELECT p.*, u.nombre_completo, u.email, e.nombre as nombre_escuela 
        FROM pedidos p 
        JOIN usuarios u ON p.id_usuario = u.id_usuario 
        LEFT JOIN escuelas_coacalco e ON p.id_escuela_destino = e.id_escuela
        WHERE p.id_pedido = $id_pedido $filtro";

$res = $conn->query($sql);
if ($res->num_rows == 0) die("Acceso denegado.");
$pedido = $res->fetch_assoc();

function texto($str) {
    if ($str === null) return '';
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 20);
        if (file_exists('img/logo.png')) { $this->Image('img/logo.png', 7, 5, 25); } 
        elseif (file_exists('img/logo.jpg')) { $this->Image('img/logo.jpg', 10, 8, 25); }
        
        $this->Cell(80);
        $this->Cell(30, 10, 'ProtoHub', 0, 0, 'C');
        $this->Ln(6);
        $this->SetFont('Courier', '', 10);
        $this->Cell(80);
        $this->Cell(30, 10, texto('Innovación en minutos'), 0, 0, 'C');
        $this->Ln(15);
        $this->Cell(0, 0, '-------------------------------------------------------------------------', 0, 1, 'C');
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Courier', 'I', 8);
        $this->Cell(0, 10, texto('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// --- CUERPO ---
$pdf->SetFont('Courier', '', 11);
$pdf->SetFont('Courier', 'B', 12);
$pdf->Cell(0, 10, texto('TICKET DE VENTA #' . str_pad($pedido['id_pedido'], 6, "0", STR_PAD_LEFT)), 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFont('Courier', '', 10);
$pdf->Cell(40, 6, 'FECHA:', 0, 0);
$pdf->Cell(0, 6, date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])), 0, 1);
$pdf->Cell(40, 6, 'CLIENTE:', 0, 0);
$pdf->Cell(0, 6, texto($pedido['receptor_nombre']), 0, 1);
$pdf->Cell(40, 6, 'ENTREGA:', 0, 0);
$pdf->Cell(0, 6, texto(ucfirst($pedido['tipo_entrega'])), 0, 1);

// --- LÓGICA DE DIRECCIÓN MEJORADA ---
$direccion_final = "";

if ($pedido['tipo_entrega'] == 'escuela' && !empty($pedido['nombre_escuela'])) {
    $direccion_final = $pedido['nombre_escuela'] . ' (' . $pedido['referencias'] . ')';
} 
elseif ($pedido['tipo_entrega'] == 'tienda') {
    // AQUÍ ESTÁ EL CAMBIO: Dirección real en el PDF
    $direccion_final = "SUCURSAL CENTRAL:\nBlvd de las Rosas 45, Villa de las Flores.\nHorario: L-V 8-19h, S-D 9-17h";
} 
else {
    $direccion_final = $pedido['direccion_texto'] . ' (' . $pedido['referencias'] . ')';
}

$pdf->Cell(40, 6, texto('DIRECCIÓN:'), 0, 0);
$pdf->MultiCell(0, 6, texto($direccion_final), 0, 1);

$pdf->Ln(5);
$pdf->Cell(0, 0, '-------------------------------------------------------------------------', 0, 1, 'C');
$pdf->Ln(5);

// --- PRODUCTOS ---
$pdf->SetFont('Courier', 'B', 10);
$pdf->Cell(15, 8, 'CANT', 0, 0, 'C');
$pdf->Cell(110, 8, texto('DESCRIPCIÓN'), 0, 0, 'L');
$pdf->Cell(30, 8, 'PRECIO', 0, 0, 'R');
$pdf->Cell(35, 8, 'TOTAL', 0, 1, 'R');

$pdf->SetFont('Courier', '', 10);
$sql_det = "SELECT d.*, p.nombre FROM detalle_pedido d JOIN productos p ON d.id_producto = p.id_producto WHERE d.id_pedido = $id_pedido";
$res_det = $conn->query($sql_det);

while ($row = $res_det->fetch_assoc()) {
    $importe = $row['cantidad'] * $row['precio_aplicado'];
    $pdf->Cell(15, 6, $row['cantidad'], 0, 0, 'C');
    $pdf->Cell(110, 6, texto(substr($row['nombre'], 0, 45)), 0, 0, 'L');
    $pdf->Cell(30, 6, '$' . number_format($row['precio_aplicado'], 2), 0, 0, 'R');
    $pdf->Cell(35, 6, '$' . number_format($importe, 2), 0, 1, 'R');
}

$pdf->Ln(2);
$pdf->Cell(0, 0, '-------------------------------------------------------------------------', 0, 1, 'C');
$pdf->Ln(5);

// --- TOTALES ---
$pdf->SetFont('Courier', '', 11);
$pdf->Cell(125); $pdf->Cell(30, 6, texto('ENVÍO:'), 0, 0, 'R'); $pdf->Cell(35, 6, '$' . number_format($pedido['costo_envio'], 2), 0, 1, 'R');
$pdf->SetFont('Courier', 'B', 14);
$pdf->Cell(125); $pdf->Cell(30, 10, 'TOTAL:', 0, 0, 'R'); $pdf->Cell(35, 10, '$' . number_format($pedido['total_final'], 2), 0, 1, 'R');

$pdf->Ln(15);
$pdf->SetFont('Courier', '', 9);
$pdf->MultiCell(0, 5, texto("¡Gracias por tu compra!\n\nSi tienes dudas contacta al 56-1167-6809\n\nPROXIMAMENTE\nwww.protohub.com\n\nEl Tony y ProtoHub les desea Feliz Navidad y Prospero Año Nuevo 2026"), 0, 'C');


$pdf->Ln(5);
$pdf->SetFont('Arial', '', 8); 
$pdf->Cell(0, 5, '|||||||||||| |||||||||| |||||| |||||||||||||', 0, 1, 'C');
$pdf->Cell(0, 5, $id_pedido . date('dmY'), 0, 1, 'C');

$pdf->Output('I', 'Ticket_'.$id_pedido.'.pdf');
?>