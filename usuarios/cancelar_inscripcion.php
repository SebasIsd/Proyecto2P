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

$stmt = $conn->prepare("
    UPDATE INSCRIPCIONES 
    SET ESTADO_INS = 'Cancelado' 
    WHERE ID_EVE_CUR = ? AND CED_USU = ? AND ESTADO_INS IN ('Preinscrito', 'Confirmado')
");
$stmt->bind_param("is", $id_evento, $cedula);

echo json_encode([
    'success' => $stmt->execute(),
    'message' => $stmt->execute() ? 'Inscripción cancelada' : 'No se pudo cancelar'
]);