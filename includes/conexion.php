<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "proyectomanejo");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>