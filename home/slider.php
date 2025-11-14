<?php
include __DIR__ . '/../includes/conexion.php';

$sql = "SELECT item_car_1, des_car_1, item_car_2, des_car_2, item_car_3, des_car_3 FROM home LIMIT 1";
$res = $conn->query($sql);

$carousel = [];

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();

    $carousel = [
        ["titulo" => $row["item_car_1"], "descripcion" => $row["des_car_1"]],
        ["titulo" => $row["item_car_2"], "descripcion" => $row["des_car_2"]],
        ["titulo" => $row["item_car_3"], "descripcion" => $row["des_car_3"]],
    ];
}

return $carousel;
