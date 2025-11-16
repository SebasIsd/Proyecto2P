<?php
require_once '../../includes/conexion.php';

$respuesta = [
    "todos" => "*",
    "sin_carrera" => ".sin_carrera",
    "carreras" => []
];

$sql = "SELECT ID_CARRERA, NOMBRE_CARRERA FROM TIPOS_CARRERA ORDER BY NOMBRE_CARRERA ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $respuesta["carreras"][] = [
            "id" => $row["ID_CARRERA"],
            "nombre" => $row["NOMBRE_CARRERA"]
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($respuesta);
$conn->close();
?>
