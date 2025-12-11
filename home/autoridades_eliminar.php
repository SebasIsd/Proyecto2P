<?php
include __DIR__ . '/../includes/conexion.php';

$id = intval($_POST["id"]); // seguridad

// 1. Obtener la ruta de la imagen
$sql = "SELECT foto FROM autoridades WHERE id = $id";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();

    // Verificar si existe foto
    if (!empty($row["foto"])) {
        $rutaFoto = "../" . $row["foto"];

        // Borrar archivo físico solo si existe
        if (file_exists($rutaFoto)) {
            unlink($rutaFoto);
        }
    }
}

// 2. Eliminar registro
$conn->query("DELETE FROM autoridades WHERE id = $id");

echo json_encode([
    "status" => "success",
    "msg" => "Autoridad eliminada correctamente"
]);
