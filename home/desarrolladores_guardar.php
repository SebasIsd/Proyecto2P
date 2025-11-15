<?php
header("Content-Type: application/json");
include __DIR__ . '/../includes/conexion.php';

$nombre = $_POST["nombre"];
$apellido = $_POST["apellido"];
$correo = $_POST["correo"];
$descripcion = $_POST["descripcion"];
$ruta_actual = $_POST["ruta_actual"]; // nombre del archivo actual

// Validaciones
if ($nombre == "" || $apellido == "" || $correo == "" || $descripcion == "") {
    echo json_encode(["status" => "error", "msg" => "Todos los campos son obligatorios"]);
    exit;
}

$rutaFinal = $ruta_actual;

// Subir foto nueva si existe
if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {

    $nombreFoto = time() . "_" . $_FILES["foto"]["name"];
    $destino = "../images/desarrolladores/" . $nombreFoto;

    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $destino)) {
        $rutaFinal = $nombreFoto;
    }
}

$sql = "UPDATE desarrolladores 
        SET nombre=?, apellido=?, correo=?, descripcion=?, ruta=?
        WHERE rutA=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $nombre, $apellido, $correo, $descripcion, $rutaFinal, $ruta_actual);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Desarrollador actualizado correctamente"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al actualizar"]);
}
