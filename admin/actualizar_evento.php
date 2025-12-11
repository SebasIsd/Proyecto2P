<?php
// actualizar_evento.php - versión FINAL corregida
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../includes/conexion.php';
session_start();

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: miseventos.php?err=" . urlencode("Método no permitido"));
        exit;
    }

    // ==========================
    //   DATOS PRINCIPALES
    // ==========================
    $idEvento = (int)($_POST['ID_EVE_CUR'] ?? 0);
    if ($idEvento <= 0) throw new Exception("ID de evento inválido.");

    $descripcion = $_POST['DES_EVE_CUR'] ?? null;
    $insDesde = $_POST['INSCRIPCION_DESDE'] ?? null;
    $insHasta = $_POST['INSCRIPCION_HASTA'] ?? null;
    $fecIni = $_POST['FEC_INI_EVE_CUR'] ?? null;
    $fecFin = $_POST['FEC_FIN_EVE_CUR'] ?? null;
    $modalidad = $_POST['MOD_EVE_CUR'] ?? 'Gratis';
    $costo = ($_POST['COS_EVE_CUR'] ?? '') === '' ? 0.0 : (float)$_POST['COS_EVE_CUR'];
    $lugar = $_POST['LUGAR'] ?? null;
    $ubicacion = $_POST['UBICACION_DETALLE'] ?? null;
    $capacidad = (int)($_POST['CAPACIDAD_MAXIMA'] ?? 0);

    $cupos = isset($_POST['CUPOS_DISPONIBLES'])
        ? (int)$_POST['CUPOS_DISPONIBLES']
        : $capacidad;

    $horas = ($_POST['HORAS_TOTALES'] ?? '') !== '' ? (int)$_POST['HORAS_TOTALES'] : null;
    $idTipo = (int)($_POST['ID_TIPO_EVE'] ?? 0);
    $activo = isset($_POST['ACTIVO']) ? (int)$_POST['ACTIVO'] : 1;

    if (!$fecIni || !$fecFin || $idTipo <= 0) {
        throw new Exception("Datos incompletos del formulario.");
    }

    // ==========================
    //   VALIDACIONES DE FECHAS
    // ==========================
    if ($insDesde && $insHasta && $insHasta < $insDesde)
        throw new Exception("Inscripción hasta no puede ser menor que inscripción desde.");

    if ($insHasta && $fecIni && $fecIni < $insHasta)
        throw new Exception("El evento no puede iniciar antes del fin de inscripción.");

    if ($fecFin < $fecIni)
        throw new Exception("La fecha de fin no puede ser menor a la de inicio.");

    // ==========================
    // MANEJO DE IMAGEN (OPCIONAL)
    // ==========================
    $rutaImagen = null;

    if (isset($_FILES['IMG_EVE_CUR']) && $_FILES['IMG_EVE_CUR']['error'] === UPLOAD_ERR_OK) {

        $dir = __DIR__ . '/../uploads/eventos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['IMG_EVE_CUR']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $permitidas))
            throw new Exception("Formato de imagen no permitido (jpg, png, gif, webp).");

        $nombreNuevo = "evento_{$idEvento}_" . time() . "." . $ext;
        $destino = $dir . $nombreNuevo;

        if (!move_uploaded_file($_FILES['IMG_EVE_CUR']['tmp_name'], $destino))
            throw new Exception("Error al guardar imagen.");

        $rutaImagen = "uploads/eventos/" . $nombreNuevo;
    }

    // ==========================
    // INICIAR TRANSACCIÓN
    // ==========================
    $conn->begin_transaction();


    // ==========================
    //   UPDATE PRINCIPAL
    // ==========================
    if ($rutaImagen) {
        $sql = "
            UPDATE EVENTOS_CURSOS
            SET DES_EVE_CUR=?, INSCRIPCION_DESDE=?, INSCRIPCION_HASTA=?,
                FEC_INI_EVE_CUR=?, FEC_FIN_EVE_CUR=?, MOD_EVE_CUR=?, COS_EVE_CUR=?,
                LUGAR=?, UBICACION_DETALLE=?, CAPACIDAD_MAXIMA=?, CUPOS_DISPONIBLES=?,
                HORAS_TOTALES=?, ID_TIPO_EVE=?, ACTIVO=?, IMG_EVE_CUR=?
            WHERE ID_EVE_CUR=?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssdssiiiiisi",
            $descripcion,
            $insDesde,
            $insHasta,
            $fecIni,
            $fecFin,
            $modalidad,
            $costo,
            $lugar,
            $ubicacion,
            $capacidad,
            $cupos,
            $horas,
            $idTipo,
            $activo,
            $rutaImagen,
            $idEvento
        );
    } else {
        $sql = "
            UPDATE EVENTOS_CURSOS
            SET DES_EVE_CUR=?, INSCRIPCION_DESDE=?, INSCRIPCION_HASTA=?,
                FEC_INI_EVE_CUR=?, FEC_FIN_EVE_CUR=?, MOD_EVE_CUR=?, COS_EVE_CUR=?,
                LUGAR=?, UBICACION_DETALLE=?, CAPACIDAD_MAXIMA=?, CUPOS_DISPONIBLES=?,
                HORAS_TOTALES=?, ID_TIPO_EVE=?, ACTIVO=?
            WHERE ID_EVE_CUR=?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssdssiiiiii",
            $descripcion,
            $insDesde,
            $insHasta,
            $fecIni,
            $fecFin,
            $modalidad,
            $costo,
            $lugar,
            $ubicacion,
            $capacidad,
            $cupos,
            $horas,
            $idTipo,
            $activo,
            $idEvento
        );
    }

    if (!$stmt->execute())
        throw new Exception("Error al actualizar evento: " . $stmt->error);

    $stmt->close();


    // ==========================
    //    ELIMINAR REQUISITOS
    // ==========================
    $conn->query("DELETE FROM EVENTOS_REQUISITOS WHERE ID_EVE_CUR = $idEvento");

    // Arrays
    $reqIns = $_POST['REQ_INSCRIPCION'] ?? [];
    $reqApr = $_POST['REQ_APROBACION'] ?? [];
    $valMinIns = $_POST['VALOR_MIN'] ?? $_POST['VALOR_MIN_INSCRIPCION'] ?? [];
    $valMinApr = $_POST['VALOR_MIN_APR'] ?? $_POST['VALOR_MIN_APROBACION'] ?? [];

    // INSCRIPCIÓN
    if ($reqIns) {
        $sql = "INSERT INTO EVENTOS_REQUISITOS
                (ID_EVE_CUR, ID_REQ, TIPO_REQUISITO, OBLIGATORIO, VALOR_MINIMO_APROBATORIO)
                VALUES (?, ?, 'INSCRIPCION', 1, ?)";
        $stmt = $conn->prepare($sql);

        foreach ($reqIns as $idR) {
            $vm = $valMinIns[$idR] ?? null;
            $vm = $vm !== '' ? (float)$vm : null;

            $stmt->bind_param("iid", $idEvento, $idR, $vm);
            $stmt->execute();
        }

        $stmt->close();
    }

    // APROBACIÓN
    if ($reqApr) {
        $sql = "INSERT INTO EVENTOS_REQUISITOS
                (ID_EVE_CUR, ID_REQ, TIPO_REQUISITO, OBLIGATORIO, VALOR_MINIMO_APROBATORIO)
                VALUES (?, ?, 'APROBACION', 0, ?)";
        $stmt = $conn->prepare($sql);

        foreach ($reqApr as $idR) {
            $vm = $valMinApr[$idR] ?? null;
            $vm = $vm !== '' ? (float)$vm : null;

            $stmt->bind_param("iid", $idEvento, $idR, $vm);
            $stmt->execute();
        }

        $stmt->close();
    }

    // ==========================
    //      CARRERAS
    // ==========================
    $conn->query("DELETE FROM EVENTOS_CARRERAS WHERE ID_EVE_CUR = $idEvento");

    $carreras = $_POST['CARRERAS'] ?? [];
    if ($carreras) {
        $sql = "INSERT INTO EVENTOS_CARRERAS (ID_EVE_CUR, ID_CARRERA) VALUES (?,?)";
        $stmt = $conn->prepare($sql);

        foreach ($carreras as $car) {
            $car = (int)$car;
            if ($car > 0) {
                $stmt->bind_param("ii", $idEvento, $car);
                $stmt->execute();
            }
        }

        $stmt->close();
    }

    // ==========================
    //  FIN Y REDIRECCIÓN
    // ==========================
    $conn->commit();
    header("Location: miseventos.php?ok=actualizado");
    exit;

} catch (Throwable $ex) {

    if ($conn->in_transaction) {
        $conn->rollback();
    }

    header("Location: miseventos.php?err=" . urlencode($ex->getMessage()));
    exit;
}
