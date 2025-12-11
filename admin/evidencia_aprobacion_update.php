<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/check_event_permission.php';
require_once __DIR__ . '/../includes/inscripciones_helper.php';

// Verificar sesión
if (!isset($_SESSION['correo']) || !isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: requisitosAprobacion.php");
    exit();
}

// Recibir datos
$idEvento = isset($_POST['id_evento']) ? (int)$_POST['id_evento'] : null;
$idIns    = isset($_POST['id_ins'])    ? (int)$_POST['id_ins']    : null;
$idReq    = isset($_POST['id_req'])    ? (int)$_POST['id_req']    : null;
$tipo     = $_POST['tipo']            ?? null; 
$obs      = trim($_POST['observacion'] ?? '');

$valorNum = isset($_POST['valor_numerico']) ? trim($_POST['valor_numerico']) : null;
$valorTxt = isset($_POST['valor_texto'])    ? trim($_POST['valor_texto'])    : null;

$cedulaAdmin = $_SESSION['cedula'];

// Validación mínima
if (!$idIns || !$idReq || !$tipo) {
    $destino = $_SERVER['HTTP_REFERER'] ?? "requisitosAprobacion.php?evento=$idEvento";
    header("Location: $destino");
    exit();
}

// ===============================================
// 1) OBTENER VALOR MÍNIMO DEL REQUISITO SI ES NUMÉRICO
// ===============================================
$valorMinimo = null;

if ($tipo === 'NUMERICO') {
    $sqlMin = "SELECT VALOR_MINIMO FROM REQUISITOS WHERE ID_REQ = ?";
    $stmtMin = $conn->prepare($sqlMin);
    $stmtMin->bind_param("i", $idReq);
    $stmtMin->execute();
    $stmtMin->bind_result($valorMinimo);
    $stmtMin->fetch();
    $stmtMin->close();
}

// ===============================================
// 2) DEFINIR ESTADO AUTOMÁTICO (LÓGICA CORRECTA)
// ===============================================
$estadoFinal = "Pendiente";
$valorNumerico = null;
$valorTexto = null;

if ($tipo === 'NUMERICO') {

    $valorNumerico = ($valorNum !== '' ? (float)$valorNum : null);

    if ($valorNumerico === null) {
        // SIN NOTA → Pendiente
        $estadoFinal = "Pendiente";
    }
    else {
        // CON NOTA → comparar
        if ($valorNumerico >= $valorMinimo) {
            $estadoFinal = "Aprobado";
        } else {
            $estadoFinal = "Rechazado";
        }
    }
}

elseif ($tipo === 'TEXTO_CORTO') {
    $valorTexto = ($valorTxt !== '' ? $valorTxt : null);
    $estadoFinal = $_POST['estado'] ?? 'Pendiente';
}

else { 
    // documento
    $estadoFinal = $_POST['estado'] ?? 'Pendiente';
}

// Normalizar estado
$estadosPermitidos = ['Pendiente','Aprobado','Rechazado'];
if (!in_array($estadoFinal, $estadosPermitidos, true)) {
    $estadoFinal = 'Pendiente';
}

// ===============================================
// 3) EJECUTAR UPDATE
// ===============================================
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
$stmt->bind_param(
    "dsssiii",
    $valorNumerico,
    $valorTexto,
    $estadoFinal,
    $obs,
    $cedulaAdmin,
    $idIns,
    $idReq
);

$stmt->execute();
$stmt->close();

// Recalcular estado general
actualizarEstadoInscripcion($conn, $idIns);

// ===============================================
// 4) Redirigir manteniendo filtros
// ===============================================
$destino = $_SERVER['HTTP_REFERER'] ?? "requisitosAprobacion.php?evento=$idEvento";
$sep = (strpos($destino, '?') !== false) ? '&' : '?';

header("Location: {$destino}{$sep}success=1");
exit();

?>
