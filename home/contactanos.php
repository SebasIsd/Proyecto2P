<?php
// home/contactanos.php
// Devuelve un array con los datos de la tabla contactanos incluyendo el mapa

include __DIR__ . '/../includes/conexion.php';

// Valores por defecto
$data = [
    "id"        => "Información no disponible",
    "des_noso"  => "Información no disponible",
    "link_noso" => "Información no disponible",
    "ruta"      => "default.png",
    "maps"      => "" // iframe vacío por defecto
];

// Consulta incluyendo la nueva columna
$sql = "SELECT id, des_noso, link_noso, ruta, maps 
        FROM contactanos 
        LIMIT 1";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();

    $data = [
        "id"        => $row["id"]        ?? $data["id"],
        "des_noso"  => $row["des_noso"]  ?? $data["des_noso"],
        "link_noso" => $row["link_noso"] ?? $data["link_noso"],
        "ruta"      => $row["ruta"]      ?? $data["ruta"],
        "maps"      => $row["maps"]      ?? $data["maps"]
    ];
}

return $data;
