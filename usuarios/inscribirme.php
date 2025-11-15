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
        :root {
            --primary: #a30000;
            --primary-hover: #d51313;
            --primary-light: #ffebeb;
            --gray-light: #f8f9fa;
            --gray: #6c757d;
            --dark: #333;
            --shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            --radius: 16px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            color: var(--dark);
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 260px;
            height: 100vh;
            background: var(--primary);
            color: white;
            padding: 25px 0;
            box-shadow: 5px 0 20px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 40px;
            padding: 0 25px;
        }

        .sidebar .logo img {
            width: 130px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.25);
            transition: all 0.3s;
        }

        .sidebar .logo img:hover {
            transform: scale(1.08);
            border-color: white;
        }

        .sidebar a {
            color: rgba(255,255,255,0.9);
            padding: 16px 28px;
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar a i {
            width: 24px;
            margin-right: 14px;
            font-size: 1.15rem;
        }

        .sidebar a:hover, .sidebar a.active {
            background: var(--primary-hover);
            color: white;
            border-left-color: white;
            padding-left: 32px;
        }

        .content {
            margin-left: 260px;
            padding: 40px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header i {
            font-size: 1.8rem;
            color: var(--primary);
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card-header-custom {
            background: var(--primary);
            color: white;
            padding: 18px 28px;
            font-weight: 600;
            font-size: 1.15rem;
        }

        .btn-inscribir {
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            border: none;
        }

        .btn-inscribir:hover {
            background: var(--primary-hover);
            color: white;
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar .logo img { width: 50px; }
            .sidebar a span { display: none; }
            .sidebar a { padding: 16px; justify-content: center; }
            .sidebar a:hover { padding-left: 16px; }
            .content { margin-left: 80px; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="usuarios_inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
        <a href="mis_eventos.php" class="active"><i class="fas fa-calendar-alt"></i> <span>Mis Eventos</span></a>
        <a href="lista_espera.php"><i class="fas fa-clock"></i> <span>Lista de Espera</span></a>
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
            <div class="card-header-custom">Evento: <?= htmlspecialchars($evento['TIT_EVE_CUR']) ?> (<?= $evento['MOD_EVE_CUR'] == 'Pagado' ? 'Pagado ($' . $evento['COS_EVE_CUR'] . ')' : 'Gratis' ?>)</div>
            <div class="card-body p-4">
                <form id="inscripcionForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_evento" value="<?= $id ?>">

                    <?php if (empty($requisitos)): ?>
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> Este evento no requiere requisitos adicionales.
                        </div>
                    <?php else: ?>
                        <h5 class="mb-3">Requisitos del Evento:</h5>
                        <?php foreach ($requisitos as $r): ?>
                            <div class="mb-3">
                                <label class="form-label <?= $r['TIPO'] !== 'NUMERICO' ? 'text-danger' : '' ?>">
                                    <?= htmlspecialchars($r['NOM_REQ']) ?> 
                                    <?php if ($r['TIPO'] !== 'NUMERICO'): ?>* (OBLIGATORIO)<?php endif; ?>
                                </label>
                                <?php if ($r['TIPO'] === 'DOCUMENTO'): ?>
                                    <input type="file" class="form-control" name="req_<?= $r['ID_REQ'] ?>" required accept=".pdf,.jpg,.jpeg,.png" onchange="previewFile(this, 'preview_req_<?= $r['ID_REQ'] ?>')">
                                    <canvas id="preview_req_<?= $r['ID_REQ'] ?>" style="max-width: 200px; display: none; margin-top: 10px;"></canvas>
                                    <small class="text-muted">Máx. 5MB. Formatos: PDF, JPG, PNG</small>
                                <?php elseif ($r['TIPO'] === 'TEXTO_CORTO'): ?>
                                    <input type="text" class="form-control" name="req_<?= $r['ID_REQ'] ?>" required maxlength="500">
                                <?php elseif ($r['TIPO'] === 'TEXTO_LARGO'): ?>
                                    <textarea class="form-control" name="req_<?= $r['ID_REQ'] ?>" rows="3" required maxlength="500"></textarea>
                                <?php elseif ($r['TIPO'] === 'NUMERICO'): ?>
                                    <!-- NO mostrar input para el usuario -->
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Este requisito será evaluado y completado por el docente/administrador.
                                    </div>
                                    <input type="hidden" name="req_<?= $r['ID_REQ'] ?>" value="0">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($evento['MOD_EVE_CUR'] == 'Pagado'): ?>
                        <div class="mb-3">
                            <label class="form-label text-danger"><i class="fas fa-exclamation-triangle"></i> Comprobante de Pago * (OBLIGATORIO)</label>
                            <input type="file" class="form-control" name="comprobante_pago" required accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Máx. 5MB. Formatos: PDF, JPG, PNG</small>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <button type="submit" class="btn-inscribir">
                            <i class="fas fa-paper-plane"></i> Enviar Inscripción
                        </button>
                        <a href="detalle_evento.php?id=<?= $id ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewFile(input, canvasId) {
            const file = input.files[0];
            if (file && file.type.startsWith('image/')) {
                const canvas = document.getElementById(canvasId);
                const ctx = canvas.getContext('2d');
                const img = new Image();
                img.src = URL.createObjectURL(file);
                img.onload = () => {
                    canvas.width = 200;
                    canvas.height = 200 * (img.height / img.width);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    canvas.style.display = 'block';
                };
            }
        }

        document.getElementById('inscripcionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('procesar_inscripcion.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'mis_eventos.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Error en la conexión: ' + err));
        });
    </script>
</body>
</html>