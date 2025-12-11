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

// Si no hay cupos, redirigir a lista de espera
if ($evento['CUPOS_DISPONIBLES'] <= 0) {
    // Verificar si ya está en lista de espera
    $check_waitlist = $conn->prepare("SELECT * FROM lista_espera WHERE ID_EVE_CUR = ? AND CED_USU = ? AND ESTADO = 'Activa'");
    $check_waitlist->bind_param("is", $id_evento, $cedula);
    $check_waitlist->execute();
    
    if ($check_waitlist->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya estás en la lista de espera para este evento']);
        exit;
    }
    
    // Agregar a lista de espera
    $conn->begin_transaction();
    try {
        // Obtener última posición
        $last_pos = $conn->query("SELECT COALESCE(MAX(POSICION), 0) as last_pos FROM lista_espera WHERE ID_EVE_CUR = $id_evento")->fetch_assoc()['last_pos'];
        $nueva_posicion = $last_pos + 1;
        
        $stmt = $conn->prepare("INSERT INTO lista_espera (ID_EVE_CUR, CED_USU, POSICION, ESTADO) VALUES (?, ?, ?, 'Activa')");
        $stmt->bind_param("isi", $id_evento, $cedula, $nueva_posicion);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Agregado a lista de espera. Posición: ' . $nueva_posicion]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error al agregar a lista de espera: ' . $e->getMessage()]);
    }
    exit;
}

$conn->begin_transaction();

try {
    // Insertar inscripción
    $stmt = $conn->prepare("
        INSERT INTO INSCRIPCIONES (CED_USU, ID_EVE_CUR, FEC_INI_INS, FEC_CIE_INS, ESTADO_INS, EST_PAG_INS) 
        VALUES (?, ?, NOW(), (SELECT FEC_FIN_EVE_CUR FROM EVENTOS_CURSOS WHERE ID_EVE_CUR = ?), 'Preinscrito', 'Pendiente')
    ");
    $stmt->bind_param("sii", $cedula, $id_evento, $id_evento);
    $stmt->execute();
    $id_ins = $conn->insert_id;

    // Reducir cupos
    $conn->query("UPDATE EVENTOS_CURSOS SET CUPOS_DISPONIBLES = CUPOS_DISPONIBLES - 1 WHERE ID_EVE_CUR = $id_evento");

    // Obtener requisitos del evento
    $requisitos = $conn->query("
        SELECT r.ID_REQ, r.NOM_REQ, r.TIPO 
        FROM EVENTOS_REQUISITOS er 
        JOIN REQUISITOS r ON er.ID_REQ = r.ID_REQ 
        WHERE er.ID_EVE_CUR = $id_evento
    ")->fetch_all(MYSQLI_ASSOC);

    // Procesar cada requisito
    foreach ($requisitos as $requisito) {
        $id_req = $requisito['ID_REQ'];
        $campo = 'req_' . $id_req;
        
        switch ($requisito['TIPO']) {
        case 'DOCUMENTO':
            $campo = 'req_' . $id_req;

            if (isset($_POST[$campo . '_from_perfil'])) {
                // === USAR DOCUMENTO SUBIDO EN EL PERFIL ===
                $stmt_doc = $conn->prepare("SELECT * FROM usuarios_documentos WHERE CED_USU = ? AND ID_REQ = ?");
                $stmt_doc->bind_param("si", $cedula, $id_req);
                $stmt_doc->execute();
                $doc_perfil = $stmt_doc->get_result()->fetch_assoc();

                if (!$doc_perfil) {
                    throw new Exception("Documento de perfil no encontrado para el requisito: {$requisito['NOM_REQ']}");
                }

                // Copiar el archivo del perfil a la carpeta de evidencias
                $uploadDir = '../uploads/requisitos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $ext = pathinfo($doc_perfil['NOMBRE_ARCHIVO'], PATHINFO_EXTENSION);
                $nombre = time() . "_req_{$id_req}_" . $cedula . "." . $ext;
                $ruta = $uploadDir . $nombre;

                if (!copy($doc_perfil['URL_ARCHIVO'], $ruta)) {
                    throw new Exception("Error al copiar el documento del perfil: {$requisito['NOM_REQ']}");
                }

                // Insertar evidencia copiada
                $stmt = $conn->prepare("
                    INSERT INTO EVIDENCIAS (ID_INS, ID_REQ, NOMBRE_ARCHIVO, URL_ARCHIVO, TIPO_MIME, TAMANIO_BYTES, ESTADO_VALIDACION, REGISTRADO_POR)
                    VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', ?)
                ");
                $stmt->bind_param("iississ", $id_ins, $id_req, $nombre, $ruta, $doc_perfil['TIPO_MIME'], $doc_perfil['TAMANIO_BYTES'], $cedula);
                $stmt->execute();

            } else {
                // === SUBIDA MANUAL (código original cuando no está en perfil) ===
                if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] == 0) {
                    $file = $_FILES[$campo];
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
                        throw new Exception("Error al subir requisito: {$requisito['NOM_REQ']}");
                    }

                    $stmt = $conn->prepare("
                        INSERT INTO EVIDENCIAS (ID_INS, ID_REQ, NOMBRE_ARCHIVO, URL_ARCHIVO, TIPO_MIME, TAMANIO_BYTES, ESTADO_VALIDACION, REGISTRADO_POR)
                        VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', ?)
                    ");
                    $tipo_mime = $file['type'];
                    $tamanio = $file['size'];
                    $stmt->bind_param("iississ", $id_ins, $id_req, $nombre, $ruta, $tipo_mime, $tamanio, $cedula);
                    $stmt->execute();
                } else {
                    throw new Exception("El requisito {$requisito['NOM_REQ']} es obligatorio");
                }
            }
            break;

            case 'TEXTO_CORTO':
            case 'TEXTO_LARGO':
                if (isset($_POST[$campo]) && !empty(trim($_POST[$campo]))) {
                    $valor = trim($_POST[$campo]);
                    $stmt = $conn->prepare("
                        INSERT INTO EVIDENCIAS (ID_INS, ID_REQ, VALOR_TEXTO, ESTADO_VALIDACION, REGISTRADO_POR)
                        VALUES (?, ?, ?, 'Pendiente', ?)
                    ");
                    $stmt->bind_param("iiss", $id_ins, $id_req, $valor, $cedula);
                    $stmt->execute();
                } else {
                    throw new Exception("El requisito {$requisito['NOM_REQ']} es obligatorio");
                }
                break;

            case 'NUMERICO':
                // Para requisitos numéricos, normalmente se llenan después por el docente
                $stmt = $conn->prepare("
                    INSERT INTO EVIDENCIAS (ID_INS, ID_REQ, ESTADO_VALIDACION, REGISTRADO_POR)
                    VALUES (?, ?, 'Pendiente', ?)
                ");
                $stmt->bind_param("iis", $id_ins, $id_req, $cedula);
                $stmt->execute();
                break;
        }
    }

    // Manejar pago si es 'Pagado'
    if ($evento['MOD_EVE_CUR'] == 'Pagado') {
        // Insertar pago PENDIENTE sin archivo (se subirá después)
        $stmt = $conn->prepare("
            INSERT INTO PAGOS (ID_INS, FEC_PAG, MON_PAG, MET_PAG, URL_COMPROBANTE, ESTADO_VALIDACION)
            VALUES (?, NOW(), ?, 'Transferencia', NULL, 'Pendiente')
        ");
        $stmt->bind_param("id", $id_ins, $evento['COS_EVE_CUR']);
        $stmt->execute();
        
        // Actualizar inscripción con pago pendiente
        $conn->query("UPDATE INSCRIPCIONES SET EST_PAG_INS = 'Pendiente' WHERE ID_INS = $id_ins");
    } else {
        // Para gratis, no pago
        $conn->query("UPDATE INSCRIPCIONES SET EST_PAG_INS = 'No Aplica' WHERE ID_INS = $id_ins");
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Inscripción enviada. Esperando revisión.']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>