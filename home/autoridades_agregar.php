<?php
include __DIR__ . '/../includes/conexion.php';

$titulo = $_POST["titulo"];
$nombre = $_POST["nombre"];
$cargo = $_POST["cargo"];
$resumen = $_POST["resumen"];
$direccion = $_POST["direccion"];
$telefono = $_POST["telefono"];
$telefono_ext = $_POST["ext"];
$horario = $_POST["horario"];
$email = $_POST["email"];
$orden = $_POST["orden"];
$activo = 1;

$rutaFoto = "";

if (!empty($_FILES["foto"]["name"])) {

    $nombreArchivo = time() . "_" . basename($_FILES["foto"]["name"]);
    $rutaFoto = "images/autoridades/" . $nombreArchivo;

    move_uploaded_file($_FILES["foto"]["tmp_name"], "../" . $rutaFoto);
}

$sql = "INSERT INTO autoridades(titulo, nombre, cargo, resumen, direccion, telefono, telefono_ext, horario, email, foto, activo, orden)
VALUES(
    '".$conn->real_escape_string($titulo)."',
    '".$conn->real_escape_string($nombre)."',
    '".$conn->real_escape_string($cargo)."',
    '".$conn->real_escape_string($resumen)."',
    '".$conn->real_escape_string($direccion)."',
    '".$conn->real_escape_string($telefono)."',
    '".$conn->real_escape_string($telefono_ext)."',
    '".$conn->real_escape_string($horario)."',
    '".$conn->real_escape_string($email)."',
    '".$conn->real_escape_string($rutaFoto)."',
    $activo,
    $orden
)";


$conn->query($sql);

echo json_encode(["status" => "success", "msg" => "Autoridad agregada correctamente"]);
