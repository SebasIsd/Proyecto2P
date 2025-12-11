<?php
session_start();
include("../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

$correo = trim($_POST["usuario"] ?? '');
$clave  = trim($_POST["clave"] ?? '');

if ($correo === '' || $clave === '') {
    header("Location: ../index.php?error=datos_incompletos&modal=login");
    exit();
}

/* 1) Traer usuario (sin usar directamente r.NOM_ROL para evitar crash) */
$sqlUser = "SELECT CED_USU, COR_USU, PAS_USU, ID_ROL_USU, ACTIVO
            FROM USUARIOS
            WHERE COR_USU = ? LIMIT 1";
if (!($stmt = $conn->prepare($sqlUser))) {
    // Error preparando la consulta: reportarlo para debug
    error_log("SQL prepare error (user): " . $conn->error);
    header("Location: ../index.php?error=error_servidor&modal=login");
    exit();
}
$stmt->bind_param("s", $correo);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    header("Location: ../index.php?error=usuario_no_encontrado&modal=login");
    exit();
}
$fila = $res->fetch_assoc();
$stmt->close();

/* 2) Verificar activo y contraseña */
if ((int)$fila['ACTIVO'] !== 1) {
    header("Location: ../index.php?error=usuario_no_verificado&modal=login");
    exit();
}
if (!password_verify($clave, $fila['PAS_USU'])) {
    header("Location: ../index.php?error=contraseña_incorrecta&modal=login");
    exit();
}

/* 3) Intentar obtener nombre del rol por ID_ROL_USU (fallback seguro) */
$rolNombre = null;
if (!empty($fila['ID_ROL_USU'])) {
    $sqlRole = "SELECT * FROM ROLES WHERE ID_ROL = ? LIMIT 1";
    if ($stmt2 = $conn->prepare($sqlRole)) {
        $stmt2->bind_param("i", $fila['ID_ROL_USU']);
        $stmt2->execute();
        $resRole = $stmt2->get_result();
        if ($resRole && $resRole->num_rows > 0) {
            $rowRole = $resRole->fetch_assoc();
            // Normalmente aquí existe 'NOM_ROL'; buscamos variantes posibles
            if (isset($rowRole['NOM_ROL'])) {
                $rolNombre = $rowRole['NOM_ROL'];
            } elseif (isset($rowRole['NOMBRE_ROL'])) {
                $rolNombre = $rowRole['NOMBRE_ROL'];
            } elseif (isset($rowRole['ROL_NOMBRE'])) {
                $rolNombre = $rowRole['ROL_NOMBRE'];
            } else {
                // No encontramos nombre de rol con nombre esperado: registrar para debug
                error_log("Roles table row fetched but no NOM_ROL-like column found: " . json_encode(array_keys($rowRole)));
                $rolNombre = null;
            }
        } else {
            // No hay fila en roles para ese ID -> debug
            error_log("No role found for ID_ROL = " . $fila['ID_ROL_USU']);
        }
        $stmt2->close();
    } else {
        // error preparando SELECT roles
        error_log("SQL prepare error (roles): " . $conn->error);
    }
}

/* 4) Guardar sesión */
$_SESSION['cedula']     = $fila['CED_USU'];
$_SESSION['correo']     = $fila['COR_USU'];
$_SESSION['rol_id']     = $fila['ID_ROL_USU'];
$_SESSION['rol_nombre'] = $rolNombre;

/* 5) Respetar redirect_to si viene */
$redirect_to = isset($_POST['redirect_to']) && !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : null;
if ($redirect_to) {
    header("Location: ../usuarios/" . $redirect_to);
    exit();
}

/* 6) Buscar en PERSONAL_EVENTO por CED_USU si es responsable o ponente (ANY evento) */
$cedula = $fila['CED_USU'];
$sqlPe = "SELECT ID_EVE_CUR FROM PERSONAL_EVENTO
          WHERE CED_USU = ? AND (ES_RESPONSABLE = 1 OR UPPER(ROL_EVENTO) = 'PONENTE')";
$eventosAsignados = [];
if ($stmt3 = $conn->prepare($sqlPe)) {
    $stmt3->bind_param("s", $cedula);
    $stmt3->execute();
    $resPe = $stmt3->get_result();
    while ($r = $resPe->fetch_assoc()) {
        $eventosAsignados[] = (int)$r['ID_EVE_CUR'];
    }
    $stmt3->close();
} else {
    error_log("SQL prepare error (personal_evento): " . $conn->error);
}

/* 7) Guardar flags de staff en sesión */
if (!empty($eventosAsignados)) {
    $_SESSION['is_event_staff'] = true;
    $_SESSION['event_ids'] = json_encode(array_values(array_unique($eventosAsignados)));
} else {
    $_SESSION['is_event_staff'] = false;
    $_SESSION['event_ids'] = json_encode([]);
}

/* 8) Redirecciones finales (ajusta rutas si tus carpetas cambian) */
$rolLower = strtolower($rolNombre ?? '');

if ($rolLower === 'administrador') {
    header("Location: ../administrador/adminInicio.php");
    exit();
}

if (!empty($eventosAsignados)) {
    header("Location: ../admin/admin_inicio.php");
    exit();
}

if ($rolLower === 'asistente') {
    header("Location: ../usuarios/usuarios_inicio.php");
    exit();
}

/* Si llegamos aquí: no sabemos el rol textualmente -> fallback */
header("Location: ../index.php?error=rol_no_valido&modal=login");
exit();
?>