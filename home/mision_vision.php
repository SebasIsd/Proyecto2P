<?php
// home/mision_vision.php
// Devuelve un array con mision y vision para ser incluido.

include __DIR__ . '/../includes/conexion.php'; // ruta relativa desde /home

$data = [
    "mision" => "Información no disponible",
    "vision" => "Información no disponible"
];

$sql = "SELECT Mision, Vision FROM home LIMIT 1";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $data = [
        "mision" => $row["Mision"] ?? $data["mision"],
        "vision" => $row["Vision"] ?? $data["vision"]
    ];
}

// IMPORTANT: return the array (no echo, no header)
return $data;
