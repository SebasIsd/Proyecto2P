<!-- perfil.php - Perfil Personal Mejorado -->
<?php
session_start();
if (!isset($_SESSION['correo'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/conexion.php';

$cedula = $_SESSION['cedula'];
$stmt = $conn->prepare("SELECT CED_USU, NOM_PRI_USU, NOM_SEG_USU, APE_PRI_USU, APE_SEG_USU, COR_USU, TEL_USU, DIR_USU FROM USUARIOS WHERE CED_USU = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoCorreo = trim($_POST['correo']);
    $nuevoTel = trim($_POST['telefono']);
    $nuevaDir = trim($_POST['direccion']);
    $nuevaClave = trim($_POST['clave']);

    $updates = [];
    $params = [];
    $types = '';

    if ($nuevoCorreo && filter_var($nuevoCorreo, FILTER_VALIDATE_EMAIL)) {
        $updates[] = "COR_USU = ?";
        $params[] = $nuevoCorreo;
        $types .= 's';
        $_SESSION['correo'] = $nuevoCorreo;
    }
    if ($nuevoTel !== $usuario['TEL_USU']) {
        $updates[] = "TEL_USU = ?";
        $params[] = $nuevoTel;
        $types .= 's';
    }
    if ($nuevaDir !== $usuario['DIR_USU']) {
        $updates[] = "DIR_USU = ?";
        $params[] = $nuevaDir;
        $types .= 's';
    }
    if ($nuevaClave) {
        $hashed = password_hash($nuevaClave, PASSWORD_DEFAULT);
        $updates[] = "PAS_USU = ?";
        $params[] = $hashed;
        $types .= 's';
    }

    if (!empty($updates)) {
        $sql = "UPDATE USUARIOS SET " . implode(', ', $updates) . " WHERE CED_USU = ?";
        $params[] = $cedula;
        $types .= 's';
        $updateStmt = $conn->prepare($sql);
        $updateStmt->bind_param($types, ...$params);
        $updateStmt->execute();
        $mensaje = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Perfil actualizado correctamente.</div>';
        // Recargar datos
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - UTA</title>
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

        .table {
            margin: 0;
        }

        .table thead {
            background: var(--primary);
            color: white;
        }

        .table thead th {
            border: none;
            font-weight: 600;
            padding: 16px;
            font-size: 0.95rem;
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-color: #eee;
        }

        .table tbody tr:hover {
            background: var(--primary-light);
        }

        .badge {
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-edit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-edit:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(163,0,0,0.2);
        }

        .avatar-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: bold;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Modal */
        .modal-content {
            border-radius: var(--radius);
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .modal-header {
            background: var(--primary);
            color: white;
            border-radius: var(--radius) var(--radius) 0 0;
            padding: 18px 25px;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 30px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(163,0,0,0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .btn-save-modal {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-save-modal:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
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
        <a href="<?= (strtolower($_SESSION['rol_nombre']) === 'administrador') ? 'admin_inicio.php' : 'usuarios_inicio.php' ?>">
            <i class="fas fa-home"></i> <span>Inicio</span>
        </a>
        <a href="perfil_usuario.php" class="active">
            <i class="fas fa-user"></i> <span>Perfil</span>
        </a>
        <a href="../Login/logout.php">
            <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
        </a>
    </div>

    <div class="content">
        <div class="profile-container">
            <?= $mensaje ?>

            <div class="profile-header">
                <div class="avatar-circle">
                    <?= strtoupper(substr($usuario['NOM_PRI_USU'], 0, 1) . substr($usuario['APE_PRI_USU'], 0, 1)) ?>
                </div>
                <h3><?= htmlspecialchars(trim($usuario['NOM_PRI_USU'] . ' ' . ($usuario['NOM_SEG_USU'] ?? '') . ' ' . $usuario['APE_PRI_USU'] . ' ' . ($usuario['APE_SEG_USU'] ?? ''))) ?></h3>
                <div class="role-badge"><?= ucfirst($_SESSION['rol_nombre']) ?></div>
            </div>

            <div class="profile-card">
                <div class="card-header-custom">
                    <i class="fas fa-id-card me-2"></i> Información Personal
                </div>
                <div class="card-body-custom">
                    <div class="info-grid">
                        <div class="info-item"><i class="fas fa-id-badge"></i><span><strong>Cédula:</strong> <?= $usuario['CED_USU'] ?></span></div>
                        <div class="info-item"><i class="fas fa-envelope"></i><span><strong>Correo:</strong> <?= $usuario['COR_USU'] ?></span></div>
                        <div class="info-item"><i class="fas fa-phone"></i><span><strong>Teléfono:</strong> <?= $usuario['TEL_USU'] ?? 'No registrado' ?></span></div>
                        <div class="info-item"><i class="fas fa-map-marker-alt"></i><span><strong>Dirección:</strong> <?= $usuario['DIR_USU'] ?? 'No registrada' ?></span></div>
                    </div>
                </div>
            </div>

            <div class="profile-card mt-4">
                <div class="card-header-custom">
                    <i class="fas fa-edit me-2"></i> Actualizar Datos
                </div>
                <div class="card-body-custom">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" value="<?= $usuario['COR_USU'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?= $usuario['TEL_USU'] ?? '' ?>" placeholder="0991234567">
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" value="<?= $usuario['DIR_USU'] ?? '' ?>" placeholder="Av. Principal 123">
                        </div>
                        <div class="mb-3">
                            <label for="clave" class="form-label">Nueva Contraseña <small class="text-muted">(opcional)</small></label>
                            <input type="password" class="form-control" id="clave" name="clave" placeholder="Mínimo 6 caracteres">
                        </div>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>