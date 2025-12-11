<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/conexion.php';

if (!isset($_SESSION['cedula'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_ins = $_POST['id_ins'] ?? 0;
$cedula = $_SESSION['cedula'];

if ($id_ins <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de inscripción inválido']);
    exit;
}

// Verificar que la inscripción pertenece al usuario y que el pago está pendiente
$stmt = $conn->prepare("
    SELECT i.ID_INS, e.MOD_EVE_CUR 
    FROM INSCRIPCIONES i 
    JOIN EVENTOS_CURSOS e ON i.ID_EVE_CUR = e.ID_EVE_CUR 
    WHERE i.ID_INS = ? AND i.CED_USU = ? AND e.MOD_EVE_CUR = 'Pagado'
");
$stmt->bind_param("is", $id_ins, $cedula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Inscripción no encontrada o no requiere pago']);
    exit;
}

if (!isset($_FILES['comprobante_pago']) || $_FILES['comprobante_pago']['error'] != 0) {
    echo json_encode(['success' => false, 'message' => 'Debes subir un comprobante de pago']);
    exit;
}

$file = $_FILES['comprobante_pago'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
    echo json_encode(['success' => false, 'message' => 'Formato no permitido. Usa PDF, JPG o PNG']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande (máx 5MB)']);
    exit;
}

$uploadDir = '../uploads/comprobantes/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$nombre = time() . "_pago_" . $cedula . "_" . $id_ins . ".$ext";
$ruta = $nombre;

if (move_uploaded_file($file['tmp_name'], $ruta)) {
    // ACTUALIZACIÓN CORREGIDA: Solo usamos URL_COMPROBANTE
    $stmt = $conn->prepare("
        UPDATE PAGOS 
        SET URL_COMPROBANTE = ? 
        WHERE ID_INS = ?
    ");
    $stmt->bind_param("si", $ruta, $id_ins);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => '¡Comprobante subido con éxito! Esperando aprobación del administrador.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al mover el archivo']);
}
?>