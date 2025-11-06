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

// Verificar cupos y modalidad
$stmt = $conn->prepare("SELECT CUPOS_DISPONIBLES, MOD_EVE_CUR, COS_EVE_CUR FROM EVENTOS_CURSOS WHERE ID_EVE_CUR = ?");
$stmt->bind_param("i", $id_evento);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if ($evento['CUPOS_DISPONIBLES'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'No hay cupos disponibles']);
    exit;
}

$conn->begin_transaction();

try {
    // Insertar inscripción (FEC_CIE_INS = FEC_FIN_EVE_CUR, asumiendo)
    $stmt = $conn->prepare("
        INSERT INTO INSCRIPCIONES (CED_USU, ID_EVE_CUR, FEC_INI_INS, FEC_CIE_INS, ESTADO_INS, EST_PAG_INS) 
        VALUES (?, ?, NOW(), (SELECT FEC_FIN_EVE_CUR FROM EVENTOS_CURSOS WHERE ID_EVE_CUR = ?), 'Preinscrito', 'Pendiente')
    ");
    $stmt->bind_param("sii", $cedula, $id_evento, $id_evento);
    $stmt->execute();
    $id_ins = $conn->insert_id;

    // Reducir cupos
    $conn->query("UPDATE EVENTOS_CURSOS SET CUPOS_DISPONIBLES = CUPOS_DISPONIBLES - 1 WHERE ID_EVE_CUR = $id_evento");

    // Manejar pago si es 'Pagado'
    if ($evento['MOD_EVE_CUR'] == 'Pagado') {
        if (isset($_FILES['comprobante_pago']) && $_FILES['comprobante_pago']['error'] == 0) {
            $uploadDir = '../uploads/comprobantes/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $comprobante = time() . '_' . basename($_FILES['comprobante_pago']['name']);
            if (!move_uploaded_file($_FILES['comprobante_pago']['tmp_name'], $uploadDir . $comprobante)) {
                throw new Exception('Error al subir comprobante');
            }
            $stmt = $conn->prepare("
                INSERT INTO PAGOS (ID_INS, FEC_PAG, MON_PAG, MET_PAG, URL_COMPROBANTE, ESTADO_VALIDACION) 
                VALUES (?, NOW(), ?, 'Transferencia', ?, 'Pendiente')
            ");
            $stmt->bind_param("ids", $id_ins, $evento['COS_EVE_CUR'], $comprobante);
            $stmt->execute();
        } else {
            throw new Exception('Comprobante requerido para eventos pagados');
        }
    }

    // Guardar evidencias para requisitos
// === SUBIR REQUISITOS (Cédula, etc.) ===
foreach ($_POST as $key => $value) {
    if (str_starts_with($key, 'req_')) {
        $id_req = substr($key, 4);
        $tipo_req = $conn->query("SELECT TIPO FROM REQUISITOS WHERE ID_REQ = $id_req")->fetch_assoc()['TIPO'];

        if ($tipo_req === 'ARCHIVO' && isset($_FILES[$key]) && $_FILES[$key]['error'] == 0) {
            $file = $_FILES[$key];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                throw new Exception("Formato no permitido en {$file['name']}");
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("Archivo demasiado grande: {$file['name']}");
            }

            $uploadDir = '../uploads/requisitos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $nombre = time() . "_req_{$id_req}_" . $cedula . ".$ext";
            $ruta = $uploadDir . $nombre;

            if (!move_uploaded_file($file['tmp_name'], $ruta)) {
                throw new Exception("Error al subir requisito");
            }

            $stmt = $conn->prepare("
                INSERT INTO EVIDENCIAS (ID_INS, ID_REQ, NOMBRE_ARCHIVO, URL_ARCHIVO, ESTADO_VALIDACION, REGISTRADO_POR)
                VALUES (?, ?, ?, ?, 'Pendiente', ?)
            ");
            $stmt->bind_param("iisss", $id_ins, $id_req, $nombre, $ruta, $cedula);
            $stmt->execute();
        }
    }
}

// === SUBIR COMPROBANTE DE PAGO (solo si es pagado) ===
if ($evento['MOD_EVE_CUR'] == 'Pagado') {
    if (!isset($_FILES['comprobante_pago']) || $_FILES['comprobante_pago']['error'] != 0) {
        throw new Exception("El comprobante de pago es obligatorio");
    }

    $file = $_FILES['comprobante_pago'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
        throw new Exception("Formato de comprobante no permitido");
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("Comprobante demasiado grande");
    }

    $uploadDir = '../uploads/comprobantes/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $nombre = time() . "_pago_" . $cedula . ".$ext";
    $ruta = $uploadDir . $nombre;

    if (!move_uploaded_file($file['tmp_name'], $ruta)) {
        throw new Exception("Error al subir comprobante");
    }

    $stmt = $conn->prepare("
        INSERT INTO PAGOS (ID_INS, FEC_PAG, MON_PAG, URL_COMPROBANTE, ESTADO_VALIDACION)
        VALUES (?, NOW(), ?, ?, 'Pendiente')
    ");
    $stmt->bind_param("ids", $id_ins, $evento['COS_EVE_CUR'], $nombre);
    $stmt->execute();
}

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Inscripción enviada. Esperando revisión.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}