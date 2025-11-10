
<?php
/***************************************************
 * roles.php — devuelve todos los roles
 ***************************************************/
require_once '../../includes/conexion.php';

// Consulta SQL
$sql = "SELECT ID_ROL, NOM_ROL FROM ROLES ORDER BY NOM_ROL ASC";
$result = $conn->query($sql);

// Arreglo para guardar los resultados
$roles = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($roles);

// Cerrar conexión
$conn->close();
?>
