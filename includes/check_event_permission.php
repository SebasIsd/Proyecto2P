<?php
// includes/check_event_permission.php
// require: $conn (mysqli) y session_start() ya ejecutados antes de incluir

/**
 * Devuelve true si $cedula es Responsable o PONENTE para $id_eve_cur
 */
function user_is_event_staff($conn, string $cedula, int $id_eve_cur): bool {
    $sql = "SELECT 1 FROM PERSONAL_EVENTO
            WHERE ID_EVE_CUR = ? AND CED_USU = ? 
              AND (ES_RESPONSABLE = 1 OR UPPER(ROL_EVENTO) = 'PONENTE')
            LIMIT 1";
    if (!($stmt = $conn->prepare($sql))) return false;
    $stmt->bind_param("is", $id_eve_cur, $cedula);
    $stmt->execute();
    $stmt->store_result();
    $ok = ($stmt->num_rows > 0);
    $stmt->close();
    return $ok;
}

/**
 * Devuelve true si $cedula figura como Responsable o PONENTE en ANY evento
 */
function user_is_any_event_staff($conn, string $cedula): bool {
    $sql = "SELECT 1 FROM PERSONAL_EVENTO
            WHERE CED_USU = ? AND (ES_RESPONSABLE = 1 OR UPPER(ROL_EVENTO) = 'PONENTE') LIMIT 1";
    if (!($stmt = $conn->prepare($sql))) return false;
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $stmt->store_result();
    $ok = ($stmt->num_rows > 0);
    $stmt->close();
    return $ok;
}
?>