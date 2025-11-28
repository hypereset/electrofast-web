<?php
session_start();
include 'php/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'No login']);
    exit;
}

$id_user = $_SESSION['id_usuario'];
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'consultar';

// --- ACCIÓN 1: MARCAR COMO LEÍDAS (BORRAR) ---
if ($accion == 'borrar') {
    $conn->query("UPDATE pedidos SET notificacion_leida = 1 WHERE id_usuario = $id_user");
    echo json_encode(['status' => 'ok']);
    exit;
}

// --- ACCIÓN 2: CONSULTAR ESTADO (POLLING) ---
// Buscamos pedidos activos o que acaban de cambiar y no han sido leídos
$sql = "SELECT id_pedido, estatus_pedido, total_final, notificacion_leida 
        FROM pedidos 
        WHERE id_usuario = $id_user 
        ORDER BY fecha_pedido DESC LIMIT 5";

$res = $conn->query($sql);
$pedidos = [];
$hay_novedades = false;

while($row = $res->fetch_assoc()){
    // Formatear estatus para leerse bonito
    $estatus_texto = ucfirst(str_replace('_', ' ', $row['estatus_pedido']));
    
    // Determinar ícono y color
    $icono = "fas fa-box";
    $color = "text-secondary";
    
    if($row['estatus_pedido'] == 'pendiente') { $icono = "fas fa-clock"; $color = "text-warning"; }
    if($row['estatus_pedido'] == 'en_camino') { $icono = "fas fa-motorcycle"; $color = "text-primary"; }
    if($row['estatus_pedido'] == 'entregado') { $icono = "fas fa-check-circle"; $color = "text-success"; }
    if($row['estatus_pedido'] == 'cancelado') { $icono = "fas fa-times-circle"; $color = "text-danger"; }

    // Solo agregamos a la lista de notificaciones si NO ha sido leída (borrada)
    if($row['notificacion_leida'] == 0){
        $hay_novedades = true;
        $pedidos[] = [
            'id' => $row['id_pedido'],
            'estatus' => $row['estatus_pedido'], // Para lógica interna
            'texto' => "Orden #".str_pad($row['id_pedido'], 4, "0", STR_PAD_LEFT)." está: <strong>$estatus_texto</strong>",
            'icono' => $icono,
            'color' => $color
        ];
    }
}

echo json_encode(['notificaciones' => $pedidos, 'cantidad' => count($pedidos)]);
?>