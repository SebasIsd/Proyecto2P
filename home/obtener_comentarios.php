<?php
header('Content-Type: application/json');
include '../includes/conexion.php';  // 🔥 Subimos un nivel porque estamos en /home/

$comentarios = [];
$sql = "SELECT id, nombre, comentario, fecha 
        FROM comentarios 
        ORDER BY fecha DESC, id DESC
        LIMIT 10";   // Muestra los 10 más recientes

if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $comentarios[] = $row;
    }
}

echo json_encode($comentarios);
$conn->close();
?>
