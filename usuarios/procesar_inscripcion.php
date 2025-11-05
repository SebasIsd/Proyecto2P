<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/conexion.php';

if (!isset($_SESSION['cedula'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_evento = $_POST['id_evento'] ?? 0;
$cedula = $_SESSION['cedula'];

// Verificar cupos
$stmt = $conn->prepare("SELECT CUPOS_DISPONIBLES FROM EVENTOS_CURSOS WHERE ID_EVE_CUR = ?");
$stmt->bind_param("i", $id_evento);
$stmt->execute();
$cupos = $stmt->get_result()->fetch_assoc()['CUPOS_DISPONIBLES'];

if ($cupos <= 0) {
    echo json_encode(['success' => false, 'message' => 'No hay cupos disponibles']);
    exit;
}

$conn->begin_transaction();

try {
    // Insertar inscripción
    $stmt = $conn->prepare("INSERT INTO INSCRIPCIONES (ID_EVE_CUR, CED_USU, ESTADO_INS, FEC_INI_INS) VALUES (?, ?, 'Preinscrito', NOW())");
    $stmt->bind_param("is", $id_evento, $cedula);
    $stmt->execute();

    // Reducir cupo
    $conn->query("UPDATE EVENTOS_CURSOS SET CUPOS_DISPONIBLES = CUPOS_DISPONIBLES - 1 WHERE ID_EVE_CUR = $id_evento");

    // Guardar respuestas (simplificado)
    foreach ($_POST as $key => $value) {
        if (str_starts_with($key, 'req_')) {
            $id_req = substr($key, 4);
            // Guardar en tabla de respuestas si existe
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Inscripción enviada']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}