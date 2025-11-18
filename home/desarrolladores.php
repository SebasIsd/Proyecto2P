<?php
// home/desarrolladores.php
// Devuelve un array con todos los desarrolladores.

include __DIR__ . '/../includes/conexion.php';

$data = [];

$sql = "SELECT nombre, apellido, correo, descripcion, ruta, github, telefono 
        FROM desarrolladores";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {

    while ($row = $res->fetch_assoc()) {

        // construir ruta completa automáticamente
        $row["ruta_completa"] = "images/desarrolladores/" . $row["ruta"];

        // construir URL listas (no guardarlas en DB)
        $row["github_url"] = "https://github.com/" . $row["github"];
        $row["whatsapp_url"] = "https://wa.me/" . $row["telefono"];
        $row["correo_url"] = "https://outlook.office.com/mail/deeplink/compose?to=" . urlencode($row["correo"]);

        $data[] = $row;
    }

} else {
    // Datos por defecto si no hay registros
    $data[] = [
        "nombre" => "Sin datos",
        "apellido" => "",
        "correo" => "",
        "descripcion" => "No se encontró información",
        "ruta_completa" => "images/desarrolladores/default.jpg",
        "github_url" => "#",
        "whatsapp_url" => "#",
        "correo_url" => "#"
    ];
}

return $data;
