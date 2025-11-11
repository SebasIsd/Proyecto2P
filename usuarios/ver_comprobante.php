<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$id_evento = $_GET['id'] ?? 0;
$cedula = $_SESSION['cedula'];

$stmt = $conn->prepare("
    SELECT p.URL_COMPROBANTE
    FROM PAGOS p
    JOIN INSCRIPCIONES i ON p.ID_INS = i.ID_INS
    WHERE i.ID_EVE_CUR = ? AND i.CED_USU = ?
");
$stmt->bind_param("is", $id_evento, $cedula);
$stmt->execute();
$result = $stmt->get_result();
$comprobante = $result->fetch_assoc()['URL_COMPROBANTE'] ?? null;

if (!$comprobante) {
    echo 'No hay comprobante subido o pendiente de validación.';
    exit;
}

$filePath = '../uploads/comprobantes/' . $comprobante;  // Asume path relativo
$fileType = pathinfo($filePath, PATHINFO_EXTENSION);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    link rel="stylesheet" href="../css/basic.css">
    <style>
        body { background: #f5f5f5; padding: 40px; }
        .card { max-width: 800px; margin: 0 auto; box-shadow: 0 8px 25px rgba(0,0,0,0.12); border-radius: 16px; }
        canvas { max-width: 100%; border: 1px solid #eee; border-radius: 8px; margin-top: 20px; }
        embed { width: 100%; height: 600px; border: none; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header bg-primary text-white">Comprobante de Pago</div>
        <div class="card-body">
            <?php if (in_array(strtolower($fileType), ['jpg', 'jpeg', 'png'])): ?>
                <canvas id="canvasPreview"></canvas>
                <script>
                    const canvas = document.getElementById('canvasPreview');
                    const ctx = canvas.getContext('2d');
                    const img = new Image();
                    img.src = '<?= $filePath ?>';
                    img.onload = () => {
                        canvas.width = Math.min(img.width, 800);
                        canvas.height = canvas.width * (img.height / img.width);
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    };
                    img.onerror = () => { document.body.innerHTML += '<p>Error al cargar la imagen.</p>'; };
                </script>
            <?php elseif (strtolower($fileType) === 'pdf'): ?>
                <embed src="<?= $filePath ?>" type="application/pdf" />
            <?php else: ?>
                <p>Tipo de archivo no soportado para vista previa.</p>
                <a href="<?= $filePath ?>" download class="btn btn-primary">Descargar Archivo</a>
            <?php endif; ?>
            <a href="mis_eventos.php" class="btn btn-secondary mt-3">Volver a Mis Eventos</a>
        </div>
    </div>
</body>
</html>