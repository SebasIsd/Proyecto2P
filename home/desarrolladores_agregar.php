<?php
header("Content-Type: application/json");
include __DIR__ . '/../includes/conexion.php';

if (
    empty($_POST["nombre"]) ||
    empty($_POST["apellido"]) ||
    empty($_POST["correo"]) ||
    empty($_POST["descripcion"]) ||
    empty($_POST["github"]) ||
    empty($_POST["telefono"])
) {
    echo json_encode(["status" => "error", "msg" => "Todos los campos son obligatorios"]);
    exit;
}

$nombre = $_POST["nombre"];
$apellido = $_POST["apellido"];
$correo = $_POST["correo"];
$descripcion = $_POST["descripcion"];
$github = $_POST["github"];
$telefono = $_POST["telefono"];

if (!str_ends_with($correo, "@uta.edu.ec")) {
    echo json_encode(["status" => "error", "msg" => "El correo debe terminar en @uta.edu.ec"]);
    exit;
}

if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== 0) {
    echo json_encode(["status" => "error", "msg" => "La foto es obligatoria"]);
    exit;
}

$nombreFoto = time() . "_" . $_FILES["foto"]["name"];
$destino = "../images/desarrolladores/" . $nombreFoto;

if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $destino)) {
    echo json_encode(["status" => "error", "msg" => "Error al subir la imagen"]);
    exit;
}

$sql = "INSERT INTO desarrolladores (nombre, apellido, correo, descripcion, ruta, github, telefono)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $nombre, $apellido, $correo, $descripcion, $nombreFoto, $github, $telefono);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Desarrollador agregado correctamente"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al guardar en la base de datos"]);
}
