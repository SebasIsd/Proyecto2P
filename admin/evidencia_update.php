<?php
session_start();

// 1. Verificar sesión y rol
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/inscripciones_helper.php';

// 2. Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: evidencias_por_evento.php");
    exit();
}

// 3. Recibir datos del formulario
$idEvento = isset($_POST['id_evento']) ? (int)$_POST['id_evento'] : null;
$idIns    = isset($_POST['id_ins'])    ? (int)$_POST['id_ins']    : null;
$idReq    = isset($_POST['id_req'])    ? (int)$_POST['id_req']    : null;
$tipo     = $_POST['tipo'] ?? null; // NUMERICO / TEXTO_CORTO / DOCUMENTO
$estado   = $_POST['estado'] ?? null; // Pendiente / Aprobado / Rechazado
$obs      = trim($_POST['observacion'] ?? '');

// estos vienen según el tipo
$valorNum = isset($_POST['valor_numerico']) ? trim($_POST['valor_numerico']) : null;
$valorTxt = isset($_POST['valor_texto'])    ? trim($_POST['valor_texto'])    : null;

// ⚠ Ajusta esto al nombre real de tu variable de sesión de cédula
$cedulaAdmin = $_SESSION['cedula'] ?? null; // por ejemplo: $_SESSION['cedula']

// Validación mínima
if (!$idIns || !$idReq || !$tipo || !$estado) {
    // puedes manejar un mensaje de error si quieres
    $destino = $_SERVER['HTTP_REFERER'] ?? "evidencias_por_evento.php?evento=$idEvento";
    header("Location: $destino");
    exit();
}

// Normalizar estado (por seguridad, solo estos 3)
$estadosPermitidos = ['Pendiente','Aprobado','Rechazado'];
if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = 'Pendiente';
}

// Según el tipo, definimos qué valor actualizar
$valorNumerico = null;
$valorTexto    = null;

if ($tipo === 'NUMERICO') {
    $valorNumerico = ($valorNum !== '' ? (float)$valorNum : null);
} elseif ($tipo === 'TEXTO_CORTO') {
    $valorTexto = ($valorTxt !== '' ? $valorTxt : null);
}

// 4. Construir el UPDATE
$sql = "
    UPDATE EVIDENCIAS
    SET
        VALOR_NUMERICO   = ?,
        VALOR_TEXTO      = ?,
        ESTADO_VALIDACION = ?,
        OBSERVACION      = ?,
        REVISADO_POR     = ?,
        REVISADO_EN      = NOW()
    WHERE ID_INS = ? AND ID_REQ = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    // error al preparar consulta
    $destino = $_SERVER['HTTP_REFERER'] ?? "evidencias_por_evento.php?evento=$idEvento";
    header("Location: $destino");
    exit();
}

$stmt->bind_param(
    "dsssiii",
    $valorNumerico,
    $valorTexto,
    $estado,
    $obs,
    $cedulaAdmin,
    $idIns,
    $idReq
);

$stmt->execute();
$stmt->close();

actualizarEstadoInscripcion($conn, $idIns);
// 5. Redirigir de vuelta a la página anterior, conservando filtros si es posible
$destino = $_SERVER['HTTP_REFERER'] ?? "evidencias_global.php?evento=$idEvento";
header("Location: $destino");
exit();


?>