<?php
header("Content-Type: application/json");
include __DIR__ . '/../includes/conexion.php';

// Verificar si llegan los datos
if (
    !isset($_POST["titulo_1"]) || !isset($_POST["descripcion_1"]) ||
    !isset($_POST["titulo_2"]) || !isset($_POST["descripcion_2"]) ||
    !isset($_POST["titulo_3"]) || !isset($_POST["descripcion_3"])
) {
    echo json_encode(["status" => "error", "msg" => "Datos incompletos"]);
    exit;
}

// Sanitizar entradas
$t1 = $conn->real_escape_string($_POST["titulo_1"]);
$d1 = $conn->real_escape_string($_POST["descripcion_1"]);
$t2 = $conn->real_escape_string($_POST["titulo_2"]);
$d2 = $conn->real_escape_string($_POST["descripcion_2"]);
$t3 = $conn->real_escape_string($_POST["titulo_3"]);
$d3 = $conn->real_escape_string($_POST["descripcion_3"]);

// Actualizar la tabla HOME
$sql = "UPDATE home SET 
            item_car_1 = '$t1',
            des_car_1 = '$d1',
            item_car_2 = '$t2',
            des_car_2 = '$d2',
            item_car_3 = '$t3',
            des_car_3 = '$d3'
        LIMIT 1";

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "msg" => "Slider actualizado correctamente"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al actualizar"]);
}
