<?php
// guardarTipoEvento.php (soporta crear varios requisitos nuevos + asociar existentes)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$nombre_tipo = trim($_POST['nombre_tipo'] ?? '');
$requisitos_raw = $_POST['requisitos'] ?? null;              // puede ser JSON array de objetos o array de ids
$reqExistentes_raw = $_POST['requisitos_existentes'] ?? null; // array de ids (JSON o array)
if ($nombre_tipo === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "El nombre del tipo es obligatorio."]);
    exit;
}

// normalizar requisitos (puede traer objetos nuevos: {nombre, tipo, valor_min} o ids numéricos)
$nuevos_requisitos_obj = []; // [{nombre, tipo, valor_min}, ...]
$ids_requisitos = [];        // [1,2,3...]

if (is_string($requisitos_raw) && $requisitos_raw !== '') {
    $dec = json_decode($requisitos_raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
        foreach ($dec as $it) {
            if (is_array($it) && !empty($it['nombre'])) {
                // objeto nuevo
                $nuevos_requisitos_obj[] = [
                   'nombre' => trim($it['nombre']),
                   'tipo'   => ($it['tipo'] ?? 'TEXTO_CORTO'),
                   'valor_min' => (isset($it['valor_min']) && $it['valor_min'] !== '') ? $it['valor_min'] : null
                ];
            } elseif (is_int($it) || ctype_digit((string)$it)) {
                $ids_requisitos[] = (int)$it;
            }
        }
    } elseif (strpos($requisitos_raw, ',') !== false) {
        foreach (explode(',', $requisitos_raw) as $p) {
            $p = trim($p);
            if ($p !== '' && ctype_digit($p)) $ids_requisitos[] = (int)$p;
        }
    }
} elseif (is_array($requisitos_raw)) {
    foreach ($requisitos_raw as $it) {
        if (is_array($it) && !empty($it['nombre'])) {
            $nuevos_requisitos_obj[] = [
               'nombre' => trim($it['nombre']),
               'tipo'   => ($it['tipo'] ?? 'TEXTO_CORTO'),
               'valor_min' => (isset($it['valor_min']) && $it['valor_min'] !== '') ? $it['valor_min'] : null
            ];
        } elseif (ctype_digit((string)$it)) {
            $ids_requisitos[] = (int)$it;
        }
    }
}

// requisitos existentes marcados (otra fuente)
$reqExistArr = [];
if (is_string($reqExistentes_raw) && $reqExistentes_raw !== '') {
    $dec2 = json_decode($reqExistentes_raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($dec2)) {
        foreach ($dec2 as $v) if (ctype_digit((string)$v)) $reqExistArr[] = (int)$v;
    } else {
        foreach (explode(',', $reqExistentes_raw) as $p) {
            $p = trim($p);
            if ($p !== '' && ctype_digit($p)) $reqExistArr[] = (int)$p;
        }
    }
} elseif (is_array($reqExistentes_raw)) {
    foreach ($reqExistentes_raw as $v) if (ctype_digit((string)$v)) $reqExistArr[] = (int)$v;
}

// unir ids existentes
$ids_requisitos = array_unique(array_merge($ids_requisitos, $reqExistArr));

$conn->begin_transaction();
try {
    // Insert tipo (sin imagen)
    $stmt = $conn->prepare("INSERT INTO TIPOS_EVENTO (NOM_TIPO_EVE) VALUES (?)");
    if (!$stmt) throw new Exception("Prepare TIPOS_EVENTO: " . $conn->error);
    $stmt->bind_param("s", $nombre_tipo);
    $stmt->execute();
    $id_tipo = $stmt->insert_id;
    $stmt->close();

    // Crear todos los requisitos nuevos y capturar sus ids
    if (!empty($nuevos_requisitos_obj)) {
        $stmtInsReq = $conn->prepare("INSERT INTO REQUISITOS (NOM_REQ, DES_REQ, TIPO, VALOR_MINIMO) VALUES (?, ?, ?, ?)");
        if (!$stmtInsReq) throw new Exception("Prepare REQUISITOS: " . $conn->error);
        foreach ($nuevos_requisitos_obj as $r) {
            $nom = $r['nombre'];
            $des = $r['nombre'];
            $tipo = in_array($r['tipo'], ['NUMERICO','DOCUMENTO','TEXTO_CORTO']) ? $r['tipo'] : 'TEXTO_CORTO';
            // valor mínimo sólo si NUMERICO y viene
            $valMin = ($tipo === 'NUMERICO' && $r['valor_min'] !== null && $r['valor_min'] !== '') ? $r['valor_min'] : null;
            // bind: sssd (string,string,string,double) -> permitimos NULL pasando null variable
            if ($valMin === null) {
                // for simplicity bind as string null and let DB set NULL via param types: use "sss" and omitted fourth? easiest: set to NULL via casting
                $stmtInsReq->bind_param("sssd", $nom, $des, $tipo, $valMin);
            } else {
                $stmtInsReq->bind_param("sssd", $nom, $des, $tipo, $valMin);
            }
            if (!$stmtInsReq->execute()) throw new Exception("Execute insert requisito failed: " . $stmtInsReq->error);
            $ids_requisitos[] = $stmtInsReq->insert_id;
        }
        $stmtInsReq->close();
    }

    // Ahora asociar todos los ids de requisitos (existentes + los nuevos creados) al tipo
    $ids_requisitos = array_unique(array_filter($ids_requisitos, function($v){ return ctype_digit((string)$v); }));
    if (!empty($ids_requisitos)) {
        $stmtRel = $conn->prepare("INSERT IGNORE INTO TIPOS_EVENTO_REQUISITOS (ID_TIPO_EVE, ID_REQ) VALUES (?, ?)");
        if (!$stmtRel) throw new Exception("Prepare TIPOS_EVENTO_REQUISITOS: " . $conn->error);
        foreach ($ids_requisitos as $idReq) {
            $idReq = (int)$idReq;
            $stmtRel->bind_param("ii", $id_tipo, $idReq);
            if (!$stmtRel->execute()) throw new Exception("Execute rel failed: " . $stmtRel->error);
        }
        $stmtRel->close();
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Tipo guardado y requisitos asociados.", "id_tipo" => $id_tipo, "requisitos_asociados" => array_values($ids_requisitos)]);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    error_log("guardarTipoEvento error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error interno al guardar."]);
    exit;
}
?>