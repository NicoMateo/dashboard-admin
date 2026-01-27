<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-user-shield"></i>
        <h3>Admin Panel</h3>
    </div>

    <nav class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <a href="usuarios.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Usuarios</span>
        </a>

        <a href="perfil.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'perfil.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Mi Perfil</span>
        </a>

        <?php if ($_SESSION['rol'] === 'admin'): ?>
        <div class="menu-section">
            <h4>Administración</h4>
        </div>

        <a href="#" class="menu-item">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>

        <a href="#" class="menu-item">
            <i class="fas fa-chart-bar"></i>
            <span>Reportes</span>
        </a>
        <?php endif; ?>

        <div class="menu-section">
            <h4>Cuenta</h4>
        </div>

        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <p>&copy; 2025 Dashboard Admin</p>
        <p>v1.0.0</p>
    </div>
</aside>
