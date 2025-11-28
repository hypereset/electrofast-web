<?php
session_start();
include '../php/conexion.php';

if (!isset($_SESSION['id_usuario'])) exit;

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

if (strlen($q) > 1) {
    // Buscamos coincidencia en nombre o código
    $sql = "SELECT id_producto, nombre, sku_barras, stock_actual, precio_unitario 
            FROM productos 
            WHERE (nombre LIKE '%$q%' OR sku_barras LIKE '%$q%') 
            AND estado = 'activo' 
            LIMIT 8";
    
    $res = $conn->query($sql);
    $resultados = [];
    
    while($row = $res->fetch_assoc()){
        $resultados[] = $row;
    }
    
    echo json_encode($resultados);
}
?>