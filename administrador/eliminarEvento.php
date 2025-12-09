<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: crearEvento.php");
    exit();
}

$id = intval($_GET['id']);

// Validar si ya hay datos relacionados (ejemplo: inscripciones)
$validar = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM INSCRIPCIONES
    WHERE ID_EVE_CUR = ?
");
$validar->bind_param("i", $id);
$validar->execute();
$res = $validar->get_result()->fetch_assoc();

if ($res['total'] > 0) {
    header("Location: crearEvento.php?msg=No se puede eliminar: el evento ya tiene inscripciones");
    exit();
}

// Si no tiene relaciones → eliminar
$del = $conn->prepare("DELETE FROM EVENTOS_CURSOS WHERE ID_EVE_CUR = ?");
$del->bind_param("i", $id);

if ($del->execute()) {
    header("Location: crearEvento.php?msg=Evento eliminado correctamente");
} else {
    header("Location: crearEvento.php?msg=Error al eliminar");
}
exit();
