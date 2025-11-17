<?php
session_start();
header("Content-Type: application/json");
include __DIR__ . '/../includes/conexion.php';

// Verificar campos
$des = $_POST['des_noso'] ?? '';
$link = $_POST['link_noso'] ?? '';
$maps = $_POST['maps'] ?? '';   // <-- NUEVO
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


// Construir SQL dinámico según si hay imagen o no
if ($rutaFinal) {
    $sql = "UPDATE contactanos SET des_noso=?, link_noso=?, ruta=?, maps=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $des, $link, $rutaFinal, $maps);
} else {
    $sql = "UPDATE contactanos SET des_noso=?, link_noso=?, maps=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $des, $link, $maps);
}


// Ejecutar
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Datos guardados correctamente"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al guardar"]);
}
