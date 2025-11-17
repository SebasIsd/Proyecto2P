<?php
header("Content-Type: application/json");
include __DIR__ . "/../includes/conexion.php";

// Validar que existan los campos requeridos
$nombre = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$correo = $_POST['correo'] ?? '';
$comentario = $_POST['comentario'] ?? '';

// Validaciones backend
$errores = [];

if (strlen(trim($nombre)) < 0) {
    $errores[] = "El nombre es demasiado corto.";
}

if (!preg_match('/^09[0-9]{8}$/', $telefono)) {
    $errores[] = "El teléfono debe comenzar con 09 y tener 10 dígitos.";
}

if (!preg_match('/^[a-zA-Z0-9._%+-]+@uta\.edu\.ec$/', $correo)) {
    $errores[] = "El correo debe ser institucional (@uta.edu.ec).";
}

if (strlen(trim($comentario)) < 0) {
    $errores[] = "El comentario es demasiado corto.";
}

if (!empty($errores)) {
    echo json_encode(["status" => "error", "msg" => implode("\n", $errores)]);
    exit;
}

// Fecha actual
$fecha = date("Y-m-d");

// Insertar comentario
$sql = "INSERT INTO comentarios (nombre, telefono, correo, comentario, fecha) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nombre, $telefono, $correo, $comentario, $fecha);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Comentario enviado con éxito"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al guardar el comentario"]);
}
