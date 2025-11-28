<?php
// Ajustar Zona Horaria
date_default_timezone_set('America/Mexico_City');

// Lógica Híbrida: ¿Estamos en la Nube o en Local?
// Railway usa variables de entorno (Environment Variables)
$host = getenv('RAILWAY_DB_HOST') ?: "db"; // Si no hay variable nube, usa "db" (Docker local)
$user = getenv('RAILWAY_DB_USER') ?: "admin";
$pass = getenv('RAILWAY_DB_PASS') ?: "password123";
$db   = getenv('RAILWAY_DB_NAME') ?: "electrofast_db";
$port = getenv('RAILWAY_DB_PORT') ?: 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Ajuste de caracteres
$conn->set_charset("utf8");
?>