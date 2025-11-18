<?php
session_start();
header("Content-Type: application/json");
include __DIR__ . '/../includes/conexion.php';

$maps = $_POST["maps"] ?? "";

if (empty($maps)) {
    echo json_encode(["status" => "error", "msg" => "El iframe no puede estar vacío"]);
    exit();
}

$sql = "UPDATE contactanos SET maps=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $maps);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Mapa actualizado correctamente"]);
} else {
    echo json_encode(["status" => "error", "msg" => "No se pudo guardar"]);
}
