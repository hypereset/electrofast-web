<?php
session_start();
include '../php/conexion.php';

// Si no hay nada, regresar
if (empty($_SESSION['pos_carrito'])) { header("Location: pos.php"); exit; }

$id_admin = $_SESSION['id_usuario']; 
$cliente = $_SESSION['pos_cliente']; 
$total_calculado = $_POST['total_venta'];
$metodo_real = $_POST['pago']; 
$monto_recibido = isset($_POST['monto_recibido']) ? $_POST['monto_recibido'] : $total_calculado;

$total_productos_real = $total_calculado; 

// Mapeo para BD
$metodo_bd = ($metodo_real == 'efectivo' || $metodo_real == 'tarjeta') ? $metodo_real : 'sucursal';

$id_usuario_pedido = $cliente ? $cliente['id_usuario'] : $id_admin; 
$nombre_receptor = $cliente ? $cliente['nombre_completo'] : 'Venta de Mostrador';

// Puntos
$puntos_usados = 0;
if (isset($_POST['usar_puntos']) && $cliente) {
    $puntos_disponibles = $cliente['puntos'];
    if ($puntos_disponibles >= $total_calculado) {
        $puntos_usados = $total_calculado;
        $total_calculado = 0;
    } else {
        $puntos_usados = $puntos_disponibles;
        $total_calculado -= $puntos_usados;
    }
    $conn->query("UPDATE usuarios SET puntos = puntos - $puntos_usados WHERE id_usuario = {$cliente['id_usuario']}");
}

// Insertar Pedido
$sql = "INSERT INTO pedidos (id_usuario, tipo_entrega, direccion_texto, referencias, receptor_nombre, receptor_telefono, metodo_pago, tipo_envio, costo_envio, total_productos, total_final, estatus_pedido, fecha_pedido) 
        VALUES ($id_usuario_pedido, 'tienda', 'Venta en Mostrador', 'Pago: $metodo_real', '$nombre_receptor', '0000000000', '$metodo_bd', 'normal', 0, $total_productos_real, $total_calculado, 'entregado', NOW())";

if ($conn->query($sql)) {
    $id_pedido = $conn->insert_id;

    // Detalles
    foreach ($_SESSION['pos_carrito'] as $id_prod => $cant) {
        $prod = $conn->query("SELECT * FROM productos WHERE id_producto=$id_prod")->fetch_assoc();
        $precio = ($cant >= 5) ? $prod['precio_mayoreo'] : $prod['precio_unitario'];
        $conn->query("INSERT INTO detalle_pedido VALUES (NULL, $id_pedido, $id_prod, $cant, $precio)");
        $nuevo_stock = max(0, $prod['stock_actual'] - $cant);
        $conn->query("UPDATE productos SET stock_actual = $nuevo_stock WHERE id_producto = $id_prod");
    }

    // Cashback
    if ($cliente && $total_calculado > 0) {
        $puntos_nuevos = $total_calculado * 0.05;
        $conn->query("UPDATE usuarios SET puntos = puntos + $puntos_nuevos WHERE id_usuario = {$cliente['id_usuario']}");
    }

    unset($_SESSION['pos_carrito']);
    unset($_SESSION['pos_cliente']);
    
    // --- REDIRECCIÓN A PANTALLA DE ÉXITO (CAMBIO IMPORTANTE) ---
    header("Location: pos_exito.php?id=$id_pedido&recibido=$monto_recibido");

} else {
    echo "Error: " . $conn->error;
}
?>