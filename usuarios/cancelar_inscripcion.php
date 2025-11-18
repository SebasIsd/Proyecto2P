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

// Función para eliminar archivos físicos
function eliminarArchivosInscripcion($id_ins, $conn) {
    // Eliminar comprobantes de pago
    $pagos = $conn->query("SELECT URL_COMPROBANTE FROM PAGOS WHERE ID_INS = $id_ins");
    while ($pago = $pagos->fetch_assoc()) {
        if ($pago['URL_COMPROBANTE'] && file_exists('../uploads/comprobantes/' . $pago['URL_COMPROBANTE'])) {
            unlink('../uploads/comprobantes/' . $pago['URL_COMPROBANTE']);
        }
    }
    
    // Eliminar archivos de evidencias
    $evidencias = $conn->query("SELECT URL_ARCHIVO FROM EVIDENCIAS WHERE ID_INS = $id_ins");
    while ($evidencia = $evidencias->fetch_assoc()) {
        if ($evidencia['URL_ARCHIVO'] && file_exists($evidencia['URL_ARCHIVO'])) {
            unlink($evidencia['URL_ARCHIVO']);
        }
    }
}

$conn->begin_transaction();

try {
    // 1. Obtener el ID de la inscripción
    $stmt = $conn->prepare("SELECT ID_INS FROM INSCRIPCIONES WHERE ID_EVE_CUR = ? AND CED_USU = ?");
    $stmt->bind_param("is", $id_evento, $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('No se encontró la inscripción');
    }
    
    $inscripcion = $result->fetch_assoc();
    $id_ins = $inscripcion['ID_INS'];

    // 2. Eliminar archivos físicos primero
    eliminarArchivosInscripcion($id_ins, $conn);

    // 3. Eliminar registros en PAGOS (si existen)
    $conn->query("DELETE FROM PAGOS WHERE ID_INS = $id_ins");

    // 4. Eliminar registros en EVIDENCIAS (si existen)
    $conn->query("DELETE FROM EVIDENCIAS WHERE ID_INS = $id_ins");

    // 5. Eliminar la inscripción
    $stmt = $conn->prepare("DELETE FROM INSCRIPCIONES WHERE ID_INS = ?");
    $stmt->bind_param("i", $id_ins);
    $stmt->execute();

    // 6. Aumentar cupos disponibles
    $conn->query("UPDATE EVENTOS_CURSOS SET CUPOS_DISPONIBLES = CUPOS_DISPONIBLES + 1 WHERE ID_EVE_CUR = $id_evento");

    // 7. Si hay alguien en lista de espera, inscribirlo automáticamente
    $siguiente_lista_espera = $conn->query("
        SELECT * FROM lista_espera 
        WHERE ID_EVE_CUR = $id_evento AND ESTADO = 'Activa' 
        ORDER BY POSICION ASC 
        LIMIT 1
    ")->fetch_assoc();

    if ($siguiente_lista_espera) {
        $cedula_lista_espera = $siguiente_lista_espera['CED_USU'];
        $id_lista_espera = $siguiente_lista_espera['ID_LISTA_ESPERA'];
        
        // Insertar inscripción para el siguiente en lista de espera
        $stmt = $conn->prepare("
            INSERT INTO INSCRIPCIONES (CED_USU, ID_EVE_CUR, FEC_INI_INS, FEC_CIE_INS, ESTADO_INS, EST_PAG_INS) 
            VALUES (?, ?, NOW(), (SELECT FEC_FIN_EVE_CUR FROM EVENTOS_CURSOS WHERE ID_EVE_CUR = ?), 'Preinscrito', 'Pendiente')
        ");
        $stmt->bind_param("sii", $cedula_lista_espera, $id_evento, $id_evento);
        $stmt->execute();
        
        // Actualizar estado en lista de espera
        $conn->query("UPDATE lista_espera SET ESTADO = 'Atendida' WHERE ID_LISTA_ESPERA = $id_lista_espera");
        
        // Enviar notificación (opcional)
        error_log("Usuario $cedula_lista_espera fue inscrito automáticamente desde lista de espera al evento $id_evento");
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Inscripción cancelada y todos los datos eliminados correctamente']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>