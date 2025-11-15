<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';
$cedula = $_SESSION['cedula'];

// Obtener lista de espera del usuario
$lista_espera = $conn->query("
    SELECT le.*, ec.TIT_EVE_CUR, ec.CUPOS_DISPONIBLES
    FROM lista_espera le
    JOIN eventos_cursos ec ON le.ID_EVE_CUR = ec.ID_EVE_CUR
    WHERE le.CED_USU = '$cedula' AND le.ESTADO = 'Activa'
    ORDER BY le.POSICION ASC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Lista de Espera - UTA</title>
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
        <a href="mis_eventos.php"><i class="fas fa-calendar-alt"></i> <span>Mis Eventos</span></a>
        <a href="lista_espera.php" class="active"><i class="fas fa-clock"></i> <span>Lista de Espera</span></a>
        <a href="buscar_eventos.php"><i class="fas fa-search"></i> <span>Buscar Eventos</span></a>
        <a href="perfil_usuario.php"><i class="fas fa-user"></i> <span>Perfil</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>

    <div class="content">
        <div class="page-header">
            <i class="fas fa-clock"></i>
            <h1>Mi Lista de Espera</h1>
        </div>

        <?php if (empty($lista_espera)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No estás en ninguna lista de espera.
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header-custom">Eventos en Lista de Espera</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Posición</th>
                                    <th>Cupos Disponibles</th>
                                    <th>Fecha de Solicitud</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista_espera as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['TIT_EVE_CUR']) ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?= $item['POSICION'] ?></span>
                                        </td>
                                        <td><?= $item['CUPOS_DISPONIBLES'] ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($item['FECHA_SOLICITUD'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="cancelarListaEspera(<?= $item['ID_LISTA_ESPERA'] ?>)">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function cancelarListaEspera(id) {
            if (!confirm('¿Cancelar tu lugar en la lista de espera?')) return;
            
            fetch('cancelar_lista_espera.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_lista_espera=' + id
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            })
            .catch(() => alert('Error de conexión'));
        }
    </script>
</body>
</html>