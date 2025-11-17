<?php
session_start();

if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: verificar_pagos.php");
    exit();
}

$idPag   = isset($_POST['id_pag']) ? (int)$_POST['id_pag'] : null;
$idIns   = isset($_POST['id_ins']) ? (int)$_POST['id_ins'] : null;
$idEvento = isset($_POST['id_evento']) ? (int)$_POST['id_evento'] : null;
$estado = $_POST['estado'] ?? null;

// Ajusta esto al campo donde guardas la CED_USU del admin en la sesión
$cedulaAdmin = $_SESSION['cedula'] ?? null; // ej: $_SESSION['cedula']

if (!$idPag || !$idIns || !$estado) {
    $destino = $_SERVER['HTTP_REFERER'] ?? "verificar_pagos.php";
    header("Location: $destino");
    exit();
}

$estadosPermitidos = ['Pendiente','Aprobado','Rechazado'];
if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = 'Pendiente';
}

// 1. Actualizar estado del pago
$sql = "
    UPDATE PAGOS
    SET
        ESTADO_VALIDACION = ?,
        REVISADO_POR      = ?,
        REVISADO_EN       = NOW()
    WHERE ID_PAG = ?
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ssi", $estado, $cedulaAdmin, $idPag);
    $stmt->execute();
    $stmt->close();
}

// 2. Opcional: actualizar el estado de pago en INSCRIPCIONES
//    Si el pago está aprobado -> EST_PAG_INS = 'Pagado'
//    Si está Pendiente o Rechazado -> EST_PAG_INS = 'Pendiente'
$estadoPagoIns = ($estado === 'Aprobado') ? 'Pagado' : 'Pendiente';

$sql2 = "UPDATE INSCRIPCIONES SET EST_PAG_INS = ? WHERE ID_INS = ?";
$stmt2 = $conn->prepare($sql2);
if ($stmt2) {
    $stmt2->bind_param("si", $estadoPagoIns, $idIns);
    $stmt2->execute();
    $stmt2->close();
}

// Volver a la pantalla anterior
$destino = $_SERVER['HTTP_REFERER'] ?? "verificar_pagos.php?evento=".$idEvento;
header("Location: $destino");
exit();
?>