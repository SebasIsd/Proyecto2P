<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['cedula'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../includes/conexion.php';

$id_evento = $_POST['id_evento'] ?? 0;
$cedula = $_SESSION['cedula'];

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        UPDATE INSCRIPCIONES 
        SET ESTADO_INS = 'Cancelado' 
        WHERE ID_EVE_CUR = ? AND CED_USU = ? AND ESTADO_INS IN ('Preinscrito', 'Inscrito')
    ");
    $stmt->bind_param("is", $id_evento, $cedula);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Aumentar cupos disponibles
        $conn->query("UPDATE EVENTOS_CURSOS SET CUPOS_DISPONIBLES = CUPOS_DISPONIBLES + 1 WHERE ID_EVE_CUR = $id_evento");
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Inscripción cancelada correctamente']);
    } else {
        throw new Exception('No se encontró la inscripción o no se puede cancelar');
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}