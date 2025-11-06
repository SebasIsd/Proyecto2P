<?php
/***************************************************
 * carreras.php — devuelve todas las carreras
 ***************************************************/
require_once '../../includes/conexion.php';

// Consulta SQL
$sql = "SELECT ID_CARRERA, NOMBRE_CARRERA FROM TIPOS_CARRERA ORDER BY NOMBRE_CARRERA ASC";
$result = $conn->query($sql);

// Arreglo para guardar los resultados
$carreras = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $carreras[] = $row;
    }
}

// Respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($carreras);

// Cerrar conexión
$conn->close();
?>
