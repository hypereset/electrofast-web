<?php
session_start();
include '../php/conexion.php';

if (!isset($_SESSION['id_usuario'])) exit; // Seguridad básica

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

if (strlen($q) > 1) {
    // Buscamos por nombre o correo, limitamos a 5 resultados para no saturar
    $sql = "SELECT id_usuario, nombre_completo, email, puntos 
            FROM usuarios 
            WHERE nombre_completo LIKE '%$q%' OR email LIKE '%$q%' 
            LIMIT 5";
    
    $res = $conn->query($sql);
    $resultados = [];
    
    while($row = $res->fetch_assoc()){
        $resultados[] = $row;
    }
    
    echo json_encode($resultados);
}
?>