<?php
// includes/inscripciones_helper.php

function actualizarEstadoInscripcion(mysqli $conn, int $idInscripcion): void
{
    // 1) Obtener datos de inscripción y evento
    $sql = "
        SELECT 
            i.ID_INS,
            i.ID_EVE_CUR,
            i.ESTADO_INS,
            i.EST_PAG_INS,
            e.MOD_EVE_CUR
        FROM INSCRIPCIONES i
        JOIN EVENTOS_CURSOS e ON e.ID_EVE_CUR = i.ID_EVE_CUR
        WHERE i.ID_INS = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idInscripcion);
    $stmt->execute();
    $res = $stmt->get_result();
    $ins = $res->fetch_assoc();
    $stmt->close();

    if (!$ins) {
        return; // inscripción no existe
    }

    // Si está cancelado NO tocamos nada
    if ($ins['ESTADO_INS'] === 'Cancelado') {
        return;
    }

    $idEvento   = (int)$ins['ID_EVE_CUR'];
    $modEvento  = $ins['MOD_EVE_CUR'];

    // 2) Validar pago (solo si es pagado)
    $pagoOk = true;
    $nuevoEstadoPago = $ins['EST_PAG_INS'];

    if ($modEvento === 'Pagado') {
        $sqlPag = "SELECT COUNT(*) AS c FROM PAGOS WHERE ID_INS = ? AND ESTADO_VALIDACION = 'Aprobado'";
        $stmt = $conn->prepare($sqlPag);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        $rowPag = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $pagoOk = ((int)$rowPag['c'] > 0);
        $nuevoEstadoPago = $pagoOk ? 'Pagado' : 'Pendiente';
    }

    // 3) Validar requisitos obligatorios (evidencias)
    $sqlReq = "
        SELECT 
            COUNT(*) AS total_oblig,
            SUM(
                CASE 
                  WHEN ev.ESTADO_VALIDACION = 'Aprobado'
                       AND (
                            er.VALOR_MINIMO_APROBATORIO IS NULL
                            OR ev.VALOR_NUMERICO IS NULL
                            OR ev.VALOR_NUMERICO >= er.VALOR_MINIMO_APROBATORIO
                       )
                  THEN 1 ELSE 0 
                END
            ) AS total_aprob
        FROM EVENTOS_REQUISITOS er
        LEFT JOIN EVIDENCIAS ev
          ON ev.ID_REQ = er.ID_REQ
         AND ev.ID_INS = ?
        WHERE er.ID_EVE_CUR = ?
          AND er.OBLIGATORIO = 1
    ";
    $stmt = $conn->prepare($sqlReq);
    $stmt->bind_param("ii", $idInscripcion, $idEvento);
    $stmt->execute();
    $rowReq = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $totalOblig = (int)($rowReq['total_oblig'] ?? 0);
    $totalAprob = (int)($rowReq['total_aprob'] ?? 0);

    $requisitosOk = ($totalOblig === 0) || ($totalOblig === $totalAprob);

    // 4) Decidir ESTADO_INS
    $nuevoEstadoIns = ($pagoOk && $requisitosOk) ? 'Completado' : 'Inscrito';

    // 5) Actualizar inscripción
    $sqlUpd = "
        UPDATE INSCRIPCIONES
        SET ESTADO_INS = ?, EST_PAG_INS = ?
        WHERE ID_INS = ?
    ";
    $stmt = $conn->prepare($sqlUpd);
    $stmt->bind_param("ssi", $nuevoEstadoIns, $nuevoEstadoPago, $idInscripcion);
    $stmt->execute();
    $stmt->close();
}
?>