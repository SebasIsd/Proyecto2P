<?php
session_start();
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre'] ?? '') !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/conexion.php';

$titulo = trim($_POST['TIT_EVE_CUR'] ?? '');
$responsable = trim($_POST['RESPONSABLE_CED'] ?? '');

if ($titulo === '' || $responsable === '') {
    header("Location: crearEvento.php?err=" . urlencode("Título y responsable son obligatorios."));
    exit;
}

// Validar que el responsable exista
$chk = $conn->prepare("SELECT CED_USU FROM USUARIOS WHERE CED_USU = ? LIMIT 1");
$chk->bind_param("s", $responsable);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    $chk->close();
    header("Location: crearEvento.php?err=" . urlencode("El usuario no existe."));
    exit;
}
$chk->close();

// Crear evento solo con lo necesario
$stmt = $conn->prepare("
    INSERT INTO EVENTOS_CURSOS (
        TIT_EVE_CUR, RESPONSABLE_CED, ACTIVO
    ) VALUES (?, ?, 1)
");
$stmt->bind_param("ss", $titulo, $responsable);
$stmt->execute();
$idEvento = $conn->insert_id;
$stmt->close();

// Registrar responsable del evento
$pstmt = $conn->prepare("
    INSERT IGNORE INTO PERSONAL_EVENTO (ID_EVE_CUR, CED_USU, ROL_EVENTO, ES_RESPONSABLE)
    VALUES (?, ?, 'PONENTE', 1)
");
$pstmt->bind_param("is", $idEvento, $responsable);
$pstmt->execute();
$pstmt->close();

header("Location: crearEvento.php?msg=" . urlencode("Evento creado con ID: $idEvento"));

exit();
?>
