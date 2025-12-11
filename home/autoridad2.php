<?php
header("Content-Type: application/json");
include '../includes/conexion.php';

$autoridades = [];
$sql = "SELECT id, titulo, nombre, cargo, resumen, direccion, telefono, telefono_ext, horario, email, foto
        FROM autoridades
        WHERE activo = 1
        ORDER BY orden ASC, id ASC";

if ($res = $conn->query($sql)) {
  while ($row = $res->fetch_assoc()) {
    $autoridades[] = $row;
  }
  $res->free();
}

$conn->close();

// Siempre devolver JSON válido
echo json_encode($autoridades, JSON_UNESCAPED_UNICODE);
