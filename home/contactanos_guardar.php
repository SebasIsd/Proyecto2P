<?php
session_start();
header("Content-Type: application/json");
include __DIR__ . '/../includes/conexion.php';

// Verificar campos
$des = $_POST['des_noso'] ?? '';
$link = $_POST['link_noso'] ?? '';
$rutaFinal = null;

// Si llega una imagen
if (!empty($_FILES['imgNosotros']['name'])) {
    $nombre = time() . "_" . basename($_FILES['imgNosotros']['name']);
    $rutaDestino = "../images/nosotros/" . $nombre;

    if (move_uploaded_file($_FILES['imgNosotros']['tmp_name'], $rutaDestino)) {
       $rutaFinal = $nombre;
    } else {
        echo json_encode(["status" => "error", "msg" => "No se pudo subir la imagen"]);
        exit();
    }
}


// Actualizar en BD
if ($rutaFinal) {
    $sql = "UPDATE contactanos SET des_noso=?, link_noso=?, ruta=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $des, $link, $rutaFinal);
} else {
    $sql = "UPDATE contactanos SET des_noso=?, link_noso=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $des, $link);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Datos guardados correctamente"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al guardar"]);
}
