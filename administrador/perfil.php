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


        .card-header-custom {
            background: var(--primary);
            color: white;
            padding: 18px 28px;
            font-weight: 600;
            font-size: 1.15rem;
        }

        .card-body-custom {
            padding: 30px;
        }

        .avatar-circle {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: bold;
            font-size: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(163,0,0,0.2);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header h3 {
            margin: 0 0 8px;
            font-size: 1.7rem;
            font-weight: 600;
            color: var(--dark);
        }

        .role-badge {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px dashed #eee;
        }

        .info-item i {
            color: var(--primary);
            width: 20px;
            text-align: center;
        }

        .info-item span {
            color: var(--gray);
            font-size: 0.95rem;
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

        .btn-save {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-save:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(163,0,0,0.25);
        }

        .alert {
            border: none;
            border-left: 5px solid var(--primary);
            background: var(--primary-light);
            color: var(--primary);
            padding: 15px 20px;
            border-radius: 12px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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

    <!-- Sidebar -->
   <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="adminInicio.php" ><i class="fas fa-home me-2"></i> Inicio</a>
        <a href="crearevento.php"><i class="fas fa-calendar-check me-2"></i> Gestionar Eventos</a>
        <a href="gestionUsuarios.php"><i class="fas fa-users me-2"></i> Gestionar Usuarios</a>
        <a href="perfil.php" class="active"><i class="fas fa-user me-2"></i> Perfil</a>
        <a href="configuraciones.php"><i class="fas fa-cog me-2"></i> Configuraciones</a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
                                      <br> <br> <br><br><br><br><br><br><br><br><br>  <br>
        <hr>
          <a href="https://sdsnt2003.atlassian.net/servicedesk/customer/portal/102" target="_blank"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>Encontraste un fallo?</a>
    </div>

    <!-- Contenido -->
    <div class="content">
        <div class="page-header">
            <i class="fas fa-user"></i>
            <h1>Mi Perfil</h1>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="avatar-circle">
                                <?= strtoupper(substr($usuario['NOM_PRI_USU'], 0, 1) . substr($usuario['APE_PRI_USU'], 0, 1)) ?>
                            </div>
                            <h3><?= htmlspecialchars(trim($usuario['NOM_PRI_USU'] . ' ' . ($usuario['NOM_SEG_USU'] ?? '') . ' ' . $usuario['APE_PRI_USU'] . ' ' . ($usuario['APE_SEG_USU'] ?? ''))) ?></h3>
                            <div class="role-badge"><?= ucfirst($_SESSION['rol_nombre']) ?></div>
                        </div>
                    </div>

                    <div class="card mt-4">
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
                </div>

                <div class="col-lg-8">
                    <?= $mensaje ?>

                    <div class="card">
                        <div class="card-header-custom">
                            <i class="fas fa-edit me-2"></i> Actualizar Datos
                        </div>
                        <div class="card-body-custom">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Correo Electrónico</label>
                                        <input type="email" class="form-control" name="correo" value="<?= $usuario['COR_USU'] ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" name="telefono" value="<?= $usuario['TEL_USU'] ?? '' ?>" placeholder="0991234567">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Dirección</label>
                                        <input type="text" class="form-control" name="direccion" value="<?= $usuario['DIR_USU'] ?? '' ?>" placeholder="Av. Principal 123">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Nueva Contraseña <small class="text-muted">(opcional)</small></label>
                                        <input type="password" class="form-control" name="clave" placeholder="Mínimo 6 caracteres">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn-save">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>