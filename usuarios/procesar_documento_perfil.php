<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}

$cedula = $_SESSION['cedula'];
$id_req = $_POST['id_req'] ?? 0;

if ($id_req == 0 || !isset($_FILES['documento']) || $_FILES['documento']['error'] != 0) {
    header("Location: perfil_usuario.php?error=invalid");
    exit();
}

$file = $_FILES['documento'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
    header("Location: perfil_usuario.php?error=formato");
    exit();
}
if ($file['size'] > 5 * 1024 * 1024) {
    header("Location: perfil_usuario.php?error=tamano");
    exit();
}

$uploadDir = '../uploads/documentos_usuarios/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
$nombre = time() . "_doc_" . $cedula . "_req_" . $id_req . "." . $ext;
$ruta = $uploadDir . $nombre;

if (move_uploaded_file($file['tmp_name'], $ruta)) {
    // Verificar si ya existe y actualizar o insertar
    $stmt_check = $conn->prepare("SELECT ID_DOC FROM usuarios_documentos WHERE CED_USU = ? AND ID_REQ = ?");
    $stmt_check->bind_param("si", $cedula, $id_req);
    $stmt_check->execute();
    $existing = $stmt_check->get_result()->fetch_assoc();

    if ($existing) {
        // Reemplazar (actualizar)
        $stmt = $conn->prepare("UPDATE usuarios_documentos SET NOMBRE_ARCHIVO = ?, URL_ARCHIVO = ?, TIPO_MIME = ?, TAMANIO_BYTES = ?, FECHA_SUBIDA = NOW() WHERE ID_DOC = ?");
        $stmt->bind_param("sssii", $nombre, $ruta, $file['type'], $file['size'], $existing['ID_DOC']);
    } else {
        // Insertar nuevo
        $stmt = $conn->prepare("INSERT INTO usuarios_documentos (CED_USU, ID_REQ, NOMBRE_ARCHIVO, URL_ARCHIVO, TIPO_MIME, TAMANIO_BYTES) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssi", $cedula, $id_req, $nombre, $ruta, $file['type'], $file['size']);
    }
    $stmt->execute();
    
    header("Location: perfil_usuario.php?success=uploaded");
} else {
    header("Location: perfil_usuario.php?error=upload");
}
?>