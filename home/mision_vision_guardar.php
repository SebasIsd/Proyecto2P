<?php
// home/mision_vision_guardar.php

include __DIR__ . '/../includes/conexion.php';

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["ok" => false, "msg" => "Método no permitido"]);
    exit;
}

// Recibir datos
$mision = $_POST["mision"] ?? "";
$vision = $_POST["vision"] ?? "";

// Validación básica
if (trim($mision) === "" || trim($vision) === "") {
    echo json_encode(["ok" => false, "msg" => "Complete ambos campos"]);
    exit;
}

// Actualizar en DB
$sql = "UPDATE home SET Mision = ?, Vision = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $mision, $vision);

if ($stmt->execute()) {
    echo json_encode(["ok" => true, "msg" => "Guardado correctamente"]);
} else {
    echo json_encode(["ok" => false, "msg" => "Error al guardar"]);
}
