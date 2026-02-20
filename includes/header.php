<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard Admin'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="wrapper"> 
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <nav class="topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
                </div>

                <div class="topbar-right">
                    <div class="user-menu">
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                            <span class="user-role badge badge-<?php echo $_SESSION['rol']; ?>">
                                <?php echo ucfirst($_SESSION['rol']); ?>
                            </span>
                        </div>
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="dropdown-menu">
                            <a href="perfil.php">
                                <i class="fas fa-user"></i> Mi Perfil
                            </a>
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="page-content">
