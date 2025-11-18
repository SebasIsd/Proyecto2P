<?php
header("Content-Type: application/json");
include __DIR__ . '/../includes/conexion.php';

if (empty($_POST["ruta"])) {
    echo json_encode(["status" => "error", "msg" => "Datos incompletos"]);
    exit;
}

$ruta = $_POST["ruta"];

// 1️⃣ Borrar imagen
$archivo = "../images/desarrolladores/" . $ruta;

if (file_exists($archivo)) {
    unlink($archivo);
}

// 2️⃣ Borrar registro
$sql = "DELETE FROM desarrolladores WHERE ruta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ruta);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Desarrollador eliminado"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al eliminar"]);
}
