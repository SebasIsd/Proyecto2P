<?php
require_once __DIR__ . '/../includes/conexion.php';


$sql = "SELECT ID_REQ, NOM_REQ FROM requisitos ORDER BY NOM_REQ ASC";
$res = $conn->query($sql);

$datos = [];

while ($row = $res->fetch_assoc()) {
    $datos[] = $row;
}

echo json_encode($datos);
?>