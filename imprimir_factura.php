<?php
session_start();
require('libs/fpdf/fpdf.php');
include 'php/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_POST['id_pedido'])) { die("Acceso no autorizado"); }

$id_pedido = intval($_POST['id_pedido']);
$id_usuario = $_SESSION['id_usuario'];
$rfc_cliente = strtoupper($_POST['rfc']);
$razon_social = strtoupper($_POST['razon_social']);
$regimen = $_POST['regimen'];
$uso_cfdi = $_POST['uso_cfdi'];
$cp_cliente = $_POST['cp'];

// Obtener datos del pedido
$sql = "SELECT * FROM pedidos WHERE id_pedido = $id_pedido AND id_usuario = $id_usuario";
$res = $conn->query($sql);
if ($res->num_rows == 0) die("Pedido no encontrado");
$pedido = $res->fetch_assoc();

// --- GENERADORES DE DATOS FALSOS PARA SIMULACIÓN ---
$folio_fiscal = strtoupper(md5(uniqid(rand(), true))) . "-" . strtoupper(substr(md5(time()), 0, 12));
$certificado_sat = "00001000000" . rand(400000000, 500000000);
$fecha_timbrado = date("Y-m-d\TH:i:s");
$sello_digital = substr(base64_encode(md5(uniqid())), 0, 150) . "...";

class PDF extends FPDF {
    function Header() {
        // Logo y Datos Emisor
        $this->SetFont('Arial', 'B', 14);
        if (file_exists('img/logo.png')) { $this->Image('img/logo.png', 10, 10, 30); }
        $this->SetXY(45, 10);
        $this->Cell(0, 6, texto('PROTOHUB ELECTRONICS S.A. DE C.V.'), 0, 1);
        $this->SetFont('Arial', '', 9);
        $this->SetX(45);
        $this->Cell(0, 5, 'RFC: PHE231005ABC', 0, 1);
        $this->SetX(45);
        $this->Cell(0, 5, texto('Régimen Fiscal: 601 - General de Ley Personas Morales'), 0, 1);
        $this->SetX(45);
        $this->Cell(0, 5, 'Lugar de Expedición: 55700 - Coacalco, Edo. Mex.', 0, 1);
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-25);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 4, texto('Este documento es una representación impresa de un CFDI (Simulado para fines académicos).'), 0, 1, 'C');
        $this->Cell(0, 4, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

function texto($str) { return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str); }

$pdf = new PDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 30);

// --- DATOS DEL RECEPTOR Y FACTURA ---
$pdf->SetY(40);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 6, 'DATOS DEL RECEPTOR', 1, 0, 'L', true);
$pdf->Cell(5, 6, '', 0, 0);
$pdf->Cell(95, 6, 'DATOS DEL COMPROBANTE', 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 9);

// Columna Izquierda (Receptor)
$x_start = 10;
$y_start = $pdf->GetY();
$pdf->SetXY($x_start, $y_start);
$pdf->MultiCell(95, 5, texto("Nombre: $razon_social\nRFC: $rfc_cliente\nC.P.: $cp_cliente\nRégimen: $regimen\nUso CFDI: $uso_cfdi"), 0, 'L');

// Columna Derecha (Factura)
$x_col2 = 110;
$pdf->SetXY($x_col2, $y_start);
$pdf->MultiCell(95, 5, texto("Folio Interno: F-" . str_pad($id_pedido, 5, "0", STR_PAD_LEFT) . "\nTipo de Comprobante: I - Ingreso\nMétodo de Pago: PUE - Pago en una sola exhibición\nForma de Pago: 04 - Tarjeta de crédito\nMoneda: MXN - Peso Mexicano"), 0, 'L');

$pdf->Ln(10);

// --- TABLA DE PRODUCTOS ---
$pdf->SetFillColor(52, 152, 219); // Azul bonito
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(15, 7, 'CANT', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'UNIDAD', 1, 0, 'C', true);
$pdf->Cell(105, 7, texto('DESCRIPCIÓN'), 1, 0, 'L', true);
$pdf->Cell(25, 7, 'P. UNIT', 1, 0, 'R', true);
$pdf->Cell(25, 7, 'IMPORTE', 1, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 9);

$sql_det = "SELECT d.*, p.nombre, p.sku_barras FROM detalle_pedido d JOIN productos p ON d.id_producto = p.id_producto WHERE d.id_pedido = $id_pedido";
$res_det = $conn->query($sql_det);
$subtotal = 0;

while ($row = $res_det->fetch_assoc()) {
    $importe = $row['cantidad'] * $row['precio_aplicado'];
    $subtotal += $importe;
    
    $pdf->Cell(15, 6, $row['cantidad'], 'B', 0, 'C');
    $pdf->Cell(25, 6, 'H87 - Pieza', 'B', 0, 'C');
    $pdf->Cell(105, 6, texto(substr($row['nombre'], 0, 55)), 'B', 0, 'L');
    $pdf->Cell(25, 6, '$' . number_format($row['precio_aplicado'], 2), 'B', 0, 'R');
    $pdf->Cell(25, 6, '$' . number_format($importe, 2), 'B', 1, 'R');
}

// Envío si existe
if ($pedido['costo_envio'] > 0) {
    $subtotal += $pedido['costo_envio'];
    $pdf->Cell(15, 6, '1', 'B', 0, 'C');
    $pdf->Cell(25, 6, 'E48 - Serv', 'B', 0, 'C');
    $pdf->Cell(105, 6, texto('Servicio de Envío / Entrega'), 'B', 0, 'L');
    $pdf->Cell(25, 6, '$' . number_format($pedido['costo_envio'], 2), 'B', 0, 'R');
    $pdf->Cell(25, 6, '$' . number_format($pedido['costo_envio'], 2), 'B', 1, 'R');
}

$pdf->Ln(5);

// --- TOTALES ---
$iva = $subtotal * 0.16;
$total = $subtotal + $iva;

$pdf->SetX(135);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(35, 6, 'SUBTOTAL:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(25, 6, '$' . number_format($subtotal, 2), 0, 1, 'R');

$pdf->SetX(135);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(35, 6, 'IVA (16%):', 0, 0, 'R');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(25, 6, '$' . number_format($iva, 2), 0, 1, 'R');

$pdf->SetX(135);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 8, 'TOTAL:', 1, 0, 'R', true);
$pdf->Cell(25, 8, '$' . number_format($total, 2), 1, 1, 'R', true);

$pdf->Ln(10);

// --- SECCIÓN FISCAL FALSA (QR y Cadenas) ---
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(0, 5, 'Sello Digital del CFDI:', 0, 1);
$pdf->SetFont('Courier', '', 7);
$pdf->MultiCell(0, 3, $sello_digital . $sello_digital, 0, 'L');
$pdf->Ln(3);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(0, 5, 'Sello del SAT:', 0, 1);
$pdf->SetFont('Courier', '', 7);
$pdf->MultiCell(0, 3, strrev($sello_digital) . $sello_digital, 0, 'L');
$pdf->Ln(3);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(0, 5, 'Cadena Original del complemento de certificación digital del SAT:', 0, 1);
$pdf->SetFont('Courier', '', 7);
$cadena = "||1.1|$folio_fiscal|$fecha_timbrado|SFE0807172W8|$sello_digital|$certificado_sat||";
$pdf->MultiCell(0, 3, $cadena, 0, 'L');

// QR Code (Usamos la imagen existente o un cuadro vacío)
$pdf->Ln(5);
if (file_exists('qrcode.gif')) {
    $pdf->Image('qrcode.gif', 10, $pdf->GetY(), 30, 30);
} else {
    $pdf->Rect(10, $pdf->GetY(), 30, 30);
    $pdf->SetXY(10, $pdf->GetY() + 12);
    $pdf->Cell(30, 5, 'QR CODE', 0, 0, 'C');
}

// Datos de Timbrado a la derecha del QR
$pdf->SetXY(45, $pdf->GetY() - 12); // Ajustar altura
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, 'Folio Fiscal (UUID):', 0, 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, $folio_fiscal, 0, 1);

$pdf->SetX(45);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, texto('No. de Serie del Certificado del SAT:'), 0, 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, $certificado_sat, 0, 1);

$pdf->SetX(45);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, texto('Fecha y hora de certificación:'), 0, 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, $fecha_timbrado, 0, 1);

$pdf->Output('I', "Factura_$folio_fiscal.pdf");
?>