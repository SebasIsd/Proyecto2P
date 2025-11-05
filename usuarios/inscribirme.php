<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$id = $_GET['id'] ?? 0;
$cedula = $_SESSION['cedula'];

$stmt = $conn->prepare("
    SELECT e.*, t.NOM_TIPO_EVE
    FROM EVENTOS_CURSOS e
    JOIN TIPOS_EVENTO t ON e.ID_TIPO_EVE = t.ID_TIPO_EVE
    WHERE e.ID_EVE_CUR = ? AND e.ACTIVO = 1 AND e.CUPOS_DISPONIBLES > 0
");
$stmt->bind_param("i", $id);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if (!$evento) {
    header("Location: buscar_eventos.php");
    exit();
}

$requisitos = $conn->query("
    SELECT r.ID_REQ, r.NOM_REQ, r.TIPO
    FROM EVENTOS_REQUISITOS er
    JOIN REQUISITOS r ON er.ID_REQ = r.ID_REQ
    WHERE er.ID_EVE_CUR = $id
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscribirme - <?= htmlspecialchars($evento['TIT_EVE_CUR']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Mismo estilo */
        :root { --primary: #a30000; --primary-hover: #d51313; }
        .sidebar { /* igual */ }
        .content { margin-left: 260px; padding: 40px; }
        .card { background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .form-control, .form-select { border: 2px solid #e9ecef; border-radius: 12px; padding: 12px; }
        .btn-save { background: var(--primary); color: white; padding: 12px 30px; border-radius: 12px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="usuarios_inicio.php" class="active"><i class="fas fa-home"></i> <span>Inicio</span></a>
        <a href="mis_eventos.php"><i class="fas fa-calendar-alt"></i> <span>Mis Eventos</span></a>
        <a href="buscar_eventos.php"><i class="fas fa-search"></i> <span>Buscar Eventos</span></a>
        <a href="perfil_usuario.php"><i class="fas fa-user"></i> <span>Perfil</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>

    <div class="content">
        <div class="page-header">
            <i class="fas fa-user-edit"></i>
            <h1>Inscribirme al Evento</h1>
        </div>

        <div class="card">
            <div class="card-header-custom">Evento: <?= htmlspecialchars($evento['TIT_EVE_CUR']) ?></div>
            <div class="card-body p-4">
                <form method="POST" action="procesar_inscripcion.php" enctype="multipart/form-data">
                    <input type="hidden" name="id_evento" value="<?= $id ?>">
                    <?php foreach ($requisitos as $r): ?>
                        <div class="mb-3">
                            <label class="form-label"><?= htmlspecialchars($r['NOM_REQ']) ?> *</label>
                            <?php if ($r['TIPO'] === 'ARCHIVO'): ?>
                                <input type="file" class="form-control" name="req_<?= $r['ID_REQ'] ?>" required accept=".pdf,.jpg,.png">
                            <?php elseif ($r['TIPO'] === 'TEXTO_CORTO'): ?>
                                <input type="text" class="form-control" name="req_<?= $r['ID_REQ'] ?>" required>
                            <?php elseif ($r['TIPO'] === 'TEXTO_LARGO'): ?>
                                <textarea class="form-control" name="req_<?= $r['ID_REQ'] ?>" rows="3" required></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="mt-4">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-paper-plane"></i> Enviar Inscripción
                        </button>
                        <a href="detalle_evento.php?id=<?= $id ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>