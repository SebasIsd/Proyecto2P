<?php

include __DIR__ . '/../includes/conexion.php';


$id = $_POST["id"];
$titulo = $conn->real_escape_string($_POST["titulo"]);
$nombre = $conn->real_escape_string($_POST["nombre"]);
$cargo = $conn->real_escape_string($_POST["cargo"]);
$resumen = $conn->real_escape_string($_POST["resumen"]);
$direccion = $conn->real_escape_string($_POST["direccion"]);
$telefono = $conn->real_escape_string($_POST["telefono"]);
$telefono_ext = $conn->real_escape_string($_POST["ext"]);
$horario = $conn->real_escape_string($_POST["horario"]);
$email = $conn->real_escape_string($_POST["email"]);
$orden = $conn->real_escape_string($_POST["orden"]);

// --- SI SUBE UNA FOTO ---
$sqlFoto = "";
if (!empty($_FILES["foto"]["name"])) {

    // Generar nombre único
    $nombreArchivo = time() . "_" . basename($_FILES["foto"]["name"]);
    $rutaFoto = "images/autoridades/" . $nombreArchivo;

    // Guardar físicamente la imagen (IMPORTANTE ../)
    move_uploaded_file($_FILES["foto"]["tmp_name"], "../" . $rutaFoto);

    // Actualizar campo en SQL
    $sqlFoto = ", foto='" . $conn->real_escape_string($rutaFoto) . "'";
}

// --- QUERY FINAL ---
$sql = "UPDATE autoridades SET
        titulo='$titulo',
        nombre='$nombre',
        cargo='$cargo',
        resumen='$resumen',
        direccion='$direccion',
        telefono='$telefono',
        telefono_ext='$telefono_ext',
        horario='$horario',
        email='$email',
        orden='$orden'
        $sqlFoto
        WHERE id=$id";

$conn->query($sql);

echo json_encode(["status" => "success", "msg" => "Autoridad editada correctamente"]);
