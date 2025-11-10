<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../includes/conexion.php"; // conexión MySQLi

// Verificar que la petición sea POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "msg" => "Método no permitido"]);
    exit;
}


// Buscar el ID del rol "Asistente" en la base de datos
$rol = null;
$nombreRol = 'Asistente';

$stmt = $conn->prepare("SELECT ID_ROL FROM roles WHERE NOM_ROL = ?");
$stmt->bind_param("s", $nombreRol);
$stmt->execute();
$result = $stmt->get_result();

if ($fila = $result->fetch_assoc()) {
    $rol = $fila['ID_ROL'];
} else {
    echo json_encode(["status" => "error", "msg" => "No se encontró el rol 'Asistente' en la base de datos."]);
    exit;
}
$stmt->close();


// Capturar los valores enviados
$cedula    = trim($_POST["cedula"] ?? "");
$nomPri    = trim($_POST["primer_nombre"] ?? "");
$nomSeg    = trim($_POST["segundo_nombre"] ?? "");
$apePri    = trim($_POST["primer_apellido"] ?? "");
$apeSeg    = trim($_POST["segundo_apellido"] ?? "");
$correo    = trim($_POST["correo"] ?? "");
$password  = trim($_POST["password"] ?? "");
$telefono  = trim($_POST["telefono"] ?? "");
$direccion = trim($_POST["direccion"] ?? "");
$fechaNac  = trim($_POST["fecha_nac"] ?? "");
$carrera   = intval($_POST["carrera"] ?? 0);

// Validar campos obligatorios
if (
    empty($cedula) || empty($nomPri) || empty($apePri) || empty($correo) ||
    empty($password) || empty($telefono) || empty($direccion) || empty($rol)
) {
    echo json_encode(["status" => "error", "msg" => "Todos los campos obligatorios deben ser llenados."]);
    exit;
}

// Validar formato de correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "msg" => "El correo no tiene un formato válido."]);
    exit;
}

// Validar contraseña (mayúscula, minúscula, número y símbolo)
if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@$#_\-]).{8,}$/', $password)) {
    echo json_encode(["status" => "error", "msg" => "La contraseña debe tener mínimo 8 caracteres, incluir mayúsculas, minúsculas, números y un símbolo (!@$#_-)."]);
    exit;
}

// Validar teléfono (09 + 8 dígitos)
if (!preg_match("/^09\d{8}$/", $telefono)) {
    echo json_encode(["status" => "error", "msg" => "El teléfono debe comenzar con 09 y tener 10 dígitos."]);
    exit;
}

// Validar longitud de cédula (10 dígitos)
if (!preg_match("/^\d{10}$/", $cedula)) {
    echo json_encode(["status" => "error", "msg" => "La cédula debe tener exactamente 10 dígitos."]);
    exit;
}
$fecha_naci = new DateTime($fechaNac);
$hoy = new DateTime();
$edad = $hoy->diff($fecha_naci)->y;

if ($edad < 17) {
    echo json_encode(["status" => "error", "msg" => "Debes tener al menos 17 años para registrarte."]);
    exit;
}


// Verificar si la cédula ya existe
$stmt = $conn->prepare("SELECT CED_USU FROM USUARIOS WHERE CED_USU = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "msg" => "La cédula ya está registrada."]);
    exit;
}
$stmt->close();

// Verificar si el correo ya está registrado
$stmt = $conn->prepare("SELECT COR_USU FROM USUARIOS WHERE COR_USU = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "msg" => "El correo ya está registrado."]);
    exit;
}
$stmt->close();

// Encriptar la contraseña
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Insertar nuevo usuario
$sql = "INSERT INTO USUARIOS 
        (CED_USU, NOM_PRI_USU, NOM_SEG_USU, APE_PRI_USU, APE_SEG_USU, COR_USU, PAS_USU, TEL_USU, DIR_USU, FEC_NAC_USU, ID_ROL_USU, ID_CARRERA_USU, ACTIVO)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssii",
    $cedula, $nomPri, $nomSeg, $apePri, $apeSeg,
    $correo, $passwordHash, $telefono, $direccion, $fechaNac,
    $rol, $carrera
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "msg" => "Usuario registrado correctamente."]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al registrar el usuario: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
