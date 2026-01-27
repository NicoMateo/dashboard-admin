<?php
require_once 'config.php';
verificarSesion();

$pageTitle = 'Dashboard';

// Obtener estadísticas
try {
    // Total de usuarios
    $totalUsuarios = $pdo->query("SELECT COUNT(*) as total FROM usuarios")->fetchColumn();

    // Usuarios activos
    $usuariosActivos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1")->fetchColumn();

    // Usuarios inactivos
    $usuariosInactivos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 0")->fetchColumn();

    // Usuarios por rol
    $stmt = $pdo->query("SELECT rol, COUNT(*) as total FROM usuarios GROUP BY rol");
    $usuariosPorRol = $stmt->fetchAll();

    // Actividad reciente
    $stmt = $pdo->query("
        SELECT a.*, u.nombre
        FROM actividades a
        INNER JOIN usuarios u ON a.usuario_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $actividadesRecientes = $stmt->fetchAll();

    // Nuevos usuarios este mes
    $nuevosUsuariosMes = $pdo->query("
        SELECT COUNT(*) as total
        FROM usuarios
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ")->fetchColumn();
    } 
    catch (PDOException $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    $totalUsuarios = $usuariosActivos = $usuariosInactivos = $nuevosUsuariosMes = 0;
    $usuariosPorRol = [];
    $actividadesRecientes = [];
}

    // Preparar datos para Chart.js
    $rolesLabels = [];
    $rolesData = [];
    foreach ($usuariosPorRol as $rol) {
    $rolesLabels[] = ucfirst($rol['rol']);
    $rolesData[] = $rol['total'];
}

include 'includes/header.php';
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $totalUsuarios; ?></h3>
            <p>Total Usuarios</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $usuariosActivos; ?></h3>
            <p>Usuarios Activos</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-user-times"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $usuariosInactivos; ?></h3>
            <p>Usuarios Inactivos</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-user-plus"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $nuevosUsuariosMes; ?></h3>
            <p>Nuevos Este Mes</p>
        </div>
    </div>
</div>

<!-- Charts and Activity -->
<div class="dashboard-grid">
    <!-- Chart Card -->
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-chart-pie"></i>
                Usuarios por Rol
            </h3>
        </div>
        <div class="card-body">
            <canvas id="rolesChart"></canvas>
        </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-history"></i>
                Actividad Reciente
            </h3>
        </div>
        <div class="card-body">
            <div class="activity-list">
                <?php if (empty($actividadesRecientes)): ?>
                    <p class="text-muted">No hay actividad reciente</p>
                <?php else: ?>
                    <?php foreach ($actividadesRecientes as $actividad): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?php
                                $iconClass = 'fa-circle-info';
                                switch ($actividad['accion']) {
                                    case 'login':
                                        $iconClass = 'fa-sign-in-alt';
                                        break;
                                    case 'crear_usuario':
                                        $iconClass = 'fa-user-plus';
                                        break;
                                    case 'editar_usuario':
                                    case 'editar_perfil':
                                        $iconClass = 'fa-user-edit';
                                        break;
                                    case 'eliminar_usuario':
                                        $iconClass = 'fa-user-minus';
                                        break;
                                }
                                ?>
                                <i class="fas <?php echo $iconClass; ?>"></i>
                            </div>
                            <div class="activity-details">
                                <p class="activity-user"><?php echo htmlspecialchars($actividad['nombre']); ?></p>
                                <p class="activity-description"><?php echo htmlspecialchars($actividad['descripcion']); ?></p>
                                <p class="activity-time">
                                    <i class="fas fa-clock"></i>
                                    <?php
                                    $fecha = new DateTime($actividad['fecha']);
                                    echo $fecha->format('d/m/Y H:i');
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-bolt"></i>
            Acciones Rápidas
        </h3>
    </div>
    <div class="card-body">
        <div class="quick-actions">
            <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'moderador'): ?>
                <a href="usuarios.php?action=crear" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Crear Usuario
                </a>
            <?php endif; ?>
            <a href="perfil.php" class="btn btn-secondary">
                <i class="fas fa-user-edit"></i>
                Editar Perfil
            </a>
            <a href="usuarios.php" class="btn btn-info">
                <i class="fas fa-list"></i>
                Ver Usuarios
            </a>
        </div>
    </div>
</div>

<script>
// Gráfico de usuarios por rol
const ctx = document.getElementById('rolesChart');
if (ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($rolesLabels); ?>,
            datasets: [{
                label: 'Usuarios',
                data: <?php echo json_encode($rolesData); ?>,
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
