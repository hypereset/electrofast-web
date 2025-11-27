<?php
session_start();
include 'php/conexion.php';

// 1. VERIFICACIÓN DE SEGURIDAD
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Si intentan entrar directo escribiendo la URL
    header("Location: index.php");
    exit;
}

if (empty($_SESSION['carrito'])) {
    // Si el carrito está vacío, no hay nada que procesar
    header("Location: catalogo.php");
    exit;
}

// 2. RECOPILAR DATOS
$id_usuario = $_SESSION['id_usuario'];
$receptor_nombre = $_POST['receptor_nombre'];
$receptor_telefono = $_POST['receptor_telefono'];
$metodo_pago = $_POST['metodo_pago'];
$tipo_entrega = $_POST['tipo_entrega'];
$tipo_envio = isset($_POST['tipo_envio']) ? $_POST['tipo_envio'] : 'normal';

// Manejo de nulos
$id_escuela = ($tipo_entrega == 'escuela' && isset($_POST['id_escuela'])) ? $_POST['id_escuela'] : 'NULL';
$direccion_texto = "";
$referencias = "";

if ($tipo_entrega == 'escuela') {
    $direccion_texto = "Entrega en Institución Educativa";
    $referencias = isset($_POST['ref_escuela']) ? $_POST['ref_escuela'] : '';
} elseif ($tipo_entrega == 'domicilio_particular') {
    $direccion_texto = isset($_POST['direccion_casa']) ? $_POST['direccion_casa'] : '';
    $referencias = isset($_POST['ref_casa']) ? $_POST['ref_casa'] : '';
} else {
    $direccion_texto = "Recolección en Tienda Central";
    $referencias = "Cliente pasa a recoger";
    $tipo_envio = 'normal';
}

// 3. CALCULAR TOTALES (Backend = Seguridad)
$total_productos = 0;
foreach ($_SESSION['carrito'] as $id => $cantidad) {
    $res = $conn->query("SELECT precio_unitario, precio_mayoreo FROM productos WHERE id_producto = $id");
    if ($res && $res->num_rows > 0) {
        $prod = $res->fetch_assoc();
        $precio = ($cantidad >= 5) ? $prod['precio_mayoreo'] : $prod['precio_unitario'];
        $total_productos += ($precio * $cantidad);
    }
}

// Calcular Envío Gratis
$es_envio_gratis = ($total_productos >= 250);
$costo_envio = 0;

if ($tipo_entrega != 'tienda') {
    if ($tipo_envio == 'urgente') {
        $costo_envio = $es_envio_gratis ? 10 : 30;
    } else {
        $costo_envio = $es_envio_gratis ? 0 : 20;
    }
}

$total_final = $total_productos + $costo_envio;

// 4. INSERTAR PEDIDO
$sql = "INSERT INTO pedidos (id_usuario, tipo_entrega, id_escuela_destino, direccion_texto, referencias, receptor_nombre, receptor_telefono, metodo_pago, tipo_envio, costo_envio, total_productos, total_final) 
        VALUES ($id_usuario, '$tipo_entrega', $id_escuela, '$direccion_texto', '$referencias', '$receptor_nombre', '$receptor_telefono', '$metodo_pago', '$tipo_envio', $costo_envio, $total_productos, $total_final)";

if ($conn->query($sql) === TRUE) {
    $id_pedido = $conn->insert_id;

    // 5. INSERTAR DETALLES
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $res_prod = $conn->query("SELECT precio_unitario, precio_mayoreo, stock_actual FROM productos WHERE id_producto = $id");
        if ($res_prod && $res_prod->num_rows > 0) {
            $p = $res_prod->fetch_assoc();
            $precio_usado = ($cantidad >= 5) ? $p['precio_mayoreo'] : $p['precio_unitario'];

            // Guardar detalle
            $conn->query("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_aplicado) VALUES ($id_pedido, $id, $cantidad, $precio_usado)");

            // Restar stock
            $nuevo_stock = max(0, $p['stock_actual'] - $cantidad);
            $conn->query("UPDATE productos SET stock_actual = $nuevo_stock WHERE id_producto = $id");
        }
    }

    // 6. LIMPIAR Y ÉXITO
    unset($_SESSION['carrito']);
    echo "<script>window.location='exito.php?id=$id_pedido';</script>";

} else {
    die("Error al procesar el pedido: " . $conn->error);
}
?>