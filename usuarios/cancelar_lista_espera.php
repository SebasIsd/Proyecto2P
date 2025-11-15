<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['cedula'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../includes/conexion.php';

$id_lista_espera = $_POST['id_lista_espera'] ?? 0;
$cedula = $_SESSION['cedula'];

try {
    // Verificar que pertenece al usuario
    $check = $conn->prepare("SELECT * FROM lista_espera WHERE ID_LISTA_ESPERA = ? AND CED_USU = ?");
    $check->bind_param("is", $id_lista_espera, $cedula);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        throw new Exception('No se encontró la solicitud');
    }
    
    $stmt = $conn->prepare("UPDATE lista_espera SET ESTADO = 'Cancelada' WHERE ID_LISTA_ESPERA = ?");
    $stmt->bind_param("i", $id_lista_espera);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Solicitud cancelada correctamente']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>