<?php
session_start();
$host = 'localhost';
$dbname = 'gestion_eventos_academicos';
$username = 'tu_usuario'; // Cambia
$password = 'tu_contrasena'; // Cambia

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>