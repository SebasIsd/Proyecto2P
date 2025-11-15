<?php
header("Content-Type: application/json");
include __DIR__ . '/../includes/conexion.php';

// Validar datos obligatorios
if (
    empty($_POST["nombre"]) ||
    empty($_POST["apellido"]) ||
    empty($_POST["correo"]) ||
    empty($_POST["descripcion"])
) {
    echo json_encode(["status" => "error", "msg" => "Todos los campos son obligatorios"]);
    exit;
}

$nombre = $_POST["nombre"];
$apellido = $_POST["apellido"];
$correo = $_POST["correo"];
$descripcion = $_POST["descripcion"];

// Validación del correo institucional
if (!str_ends_with($correo, "@uta.edu.ec")) {
    echo json_encode(["status" => "error", "msg" => "El correo debe terminar en @uta.edu.ec"]);
    exit;
}

// Validar que haya foto
if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== 0) {
    echo json_encode(["status" => "error", "msg" => "La foto es obligatoria"]);
    exit;
}

// Procesar foto
$nombreFoto = time() . "_" . $_FILES["foto"]["name"];
$destino = "../images/desarrolladores/" . $nombreFoto;

if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $destino)) {
    echo json_encode(["status" => "error", "msg" => "Error al subir la imagen"]);
    exit;
}

// Insertar en BD
$sql = "INSERT INTO desarrolladores (nombre, apellido, correo, descripcion, ruta)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nombre, $apellido, $correo, $descripcion, $nombreFoto);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Desarrollador agregado correctamente"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al guardar en la base de datos"]);
}
