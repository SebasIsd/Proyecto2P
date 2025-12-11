<?php
session_start();

// 1. Verificación de seguridad y conexión
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    exit(json_encode(['success' => false, 'message' => 'No autorizado. Solo administradores pueden actualizar usuarios.']));
}

require_once '../includes/conexion.php';

$cedula = $_POST['cedula'] ?? '';

if (!$cedula) {
    exit(json_encode(['success' => false, 'message' => 'Falta la cédula del usuario a actualizar.']));
}

$updates = []; 
$params = [];  
$types = '';   

// 2. Mapeo de campos y determinación de tipos
// Estos nombres ('nom_pri', 'correo', etc.) deben coincidir con el atributo 'name' de tus inputs HTML.
$campo_map = [
    'nom_pri'      => 'NOM_PRI_USU',
    'nom_seg'      => 'NOM_SEG_USU',
    'ape_pri'      => 'APE_PRI_USU',
    'ape_seg'      => 'APE_SEG_USU',
    'correo'       => 'COR_USU',
    'telefono'     => 'TEL_USU',
    'direccion'    => 'DIR_USU',
    'fec_nac_usu'  => 'FEC_NAC_USU', 
    'rol_id'       => 'ID_ROL_USU',
    'carrera_id'   => 'ID_CARRERA_USU',
    'activo'       => 'ACTIVO'
];

// Columnas que requieren tipado entero 'i'
$integer_fields = ['ID_ROL_USU', 'ID_CARRERA_USU', 'ACTIVO'];


foreach ($campo_map as $post_name => $db_column) {
    if (isset($_POST[$post_name])) {
        
        $val = trim($_POST[$post_name]);
        $val_to_bind = $val;
        $type_char = 's';

        // Lógica de Tipado (se mantiene la que ya funcionó)
        if (in_array($db_column, $integer_fields)) {
            $type_char = 'i';
            
            if ($db_column === 'ACTIVO') {
                // ACTIVO es un TINYINT(1) y se espera 1 o 0
                $val_to_bind = (int)$val; 
            } else {
                // IDs: si el campo está vacío o cero, se enlaza NULL si la columna lo permite
                $val_to_bind = ($val === '' || $val == 0) ? null : (int)$val;
            }
        } elseif ($val === '') {
            $val_to_bind = null; // Campos de texto opcionales
        }

        $updates[] = "$db_column = ?";
        $params[] = $val_to_bind; 
        $types .= $type_char;     
    }
}

// 3. Manejo de la contraseña
if (!empty($_POST['clave']) && strlen($_POST['clave']) >= 6) {
    $updates[] = "PAS_USU = ?";
    $passwordHash = password_hash($_POST['clave'], PASSWORD_DEFAULT);
    $params[] = $passwordHash;
    $types .= 's';
}

if (empty($updates)) {
    exit(json_encode(['success' => true, 'message' => 'No se detectaron campos para actualizar.']));
}

// 4. Preparación de la consulta
$sql = "UPDATE USUARIOS SET " . implode(', ', $updates) . " WHERE CED_USU = ?";
$params[] = $cedula; // Añadir la cédula para el WHERE
$types .= 's';       

$stmt = $conn->prepare($sql);
if (!$stmt) {
    exit(json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]));
}

// 5. Enlazar
if (!$stmt->bind_param($types, ...$params)) {
    exit(json_encode(['success' => false, 'message' => 'Error al enlazar parámetros. Tipos: ' . $types . ' Error: ' . $stmt->error]));
}

// 6. SOLUCIÓN: Ejecutar una sola vez y verificar affected_rows
if ($stmt->execute()) {
    $rows_affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();
    
    // Devolver el mensaje basado en las filas afectadas
    $message = ($rows_affected > 0) ? 'Usuario actualizado correctamente.' : 'Usuario encontrado, pero no se detectaron cambios.';
    exit(json_encode(['success' => true, 'message' => $message]));
} else {
    $error_msg = $stmt->error;
    $stmt->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Error al ejecutar la actualización: ' . $error_msg]));
}