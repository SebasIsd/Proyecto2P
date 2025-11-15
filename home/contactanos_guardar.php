<?php
header("Content-Type: application/json");
include __DIR__ . '/../includes/conexion.php';

$des = $_POST["des_noso"] ?? "";
$link = $_POST["link_noso"] ?? "";

if ($des == "" || $link == "") {
    echo json_encode(["status" => "error", "msg" => "Todos los campos son obligatorios"]);
    exit;
}

$sql = "UPDATE contactanos SET des_noso=?, link_noso=? WHERE id=1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $des, $link);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Información actualizada correctamente"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al actualizar"]);
}
