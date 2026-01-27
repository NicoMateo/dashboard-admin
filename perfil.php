<?php
require_once 'config.php';
verificarSesion();

$pageTitle = 'Mi Perfil';
$error = '';
$success = '';

// Obtener datos del usuario actual
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        header('Location: logout.php');
        exit();
    }
} catch (PDOException $e) {
    $error = 'Error al cargar el perfil';
    error_log("Error al cargar perfil: " . $e->getMessage());
}

// Actualizar perfil
if (isset($_POST['actualizar_perfil'])) {
    $nombre = sanitizar($_POST['nombre']);
    $email = sanitizar($_POST['email']);

    if (empty($nombre) || empty($email)) {
        $error = 'Nombre y email son obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } else {
        try {
            // Verificar si el email ya existe en otro usuario
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['usuario_id']]);

            if ($stmt->fetch()) {
                $error = 'El email ya está en uso';
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
                $stmt->execute([$nombre, $email, $_SESSION['usuario_id']]);

                // Actualizar sesión
                $_SESSION['nombre'] = $nombre;
                $_SESSION['email'] = $email;

                // Recargar datos del usuario
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->execute([$_SESSION['usuario_id']]);
                $usuario = $stmt->fetch();

                registrarActividad($pdo, $_SESSION['usuario_id'], 'editar_perfil', 'Actualizó su información de perfil');
                $success = 'Perfil actualizado exitosamente';
            }
        } catch (PDOException $e) {
            $error = 'Error al actualizar perfil';
            error_log("Error al actualizar perfil: " . $e->getMessage());
        }
    }
}

// Cambiar contraseña
if (isset($_POST['cambiar_password'])) {
    $passwordActual = $_POST['password_actual'];
    $passwordNuevo = $_POST['password_nuevo'];
    $passwordConfirmar = $_POST['password_confirmar'];

    if (empty($passwordActual) || empty($passwordNuevo) || empty($passwordConfirmar)) {
        $error = 'Todos los campos de contraseña son obligatorios';
    } elseif (!password_verify($passwordActual, $usuario['password'])) {
        $error = 'La contraseña actual es incorrecta';
    } elseif (strlen($passwordNuevo) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres';
    } elseif ($passwordNuevo !== $passwordConfirmar) {
        $error = 'Las contraseñas nuevas no coinciden';
    } else {
        try {
            $passwordHash = password_hash($passwordNuevo, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->execute([$passwordHash, $_SESSION['usuario_id']]);

            // Recargar datos del usuario
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $usuario = $stmt->fetch();

            registrarActividad($pdo, $_SESSION['usuario_id'], 'cambiar_password', 'Cambió su contraseña');
            $success = 'Contraseña actualizada exitosamente';
        } catch (PDOException $e) {
            $error = 'Error al cambiar contraseña';
            error_log("Error al cambiar contraseña: " . $e->getMessage());
        }
    }
}

include 'includes/header.php';
?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="profile-grid">
    <!-- Información del perfil -->
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-user"></i>
                Información del Perfil
            </h3>
        </div>
        <div class="card-body">
            <div class="profile-info">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="profile-details">
                    <h2><?php echo htmlspecialchars($usuario['nombre']); ?></h2>
                    <p class="text-muted"><?php echo htmlspecialchars($usuario['email']); ?></p>
                    <p>
                        <span class="badge badge-<?php echo $usuario['rol']; ?>">
                            <?php echo ucfirst($usuario['rol']); ?>
                        </span>
                        <span class="badge badge-<?php echo $usuario['activo'] === 'activo' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($usuario['activo']); ?>
                        </span>
                    </p>
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-item">
                    <i class="fas fa-calendar"></i>
                    <div>
                        <strong>Miembro desde</strong>
                        <p>
                            <?php
                            $fecha = new DateTime($usuario['created_at']);
                            echo $fecha->format('d/m/Y');
                            ?>
                        </p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Última conexión</strong>
                        <p>
                            <?php
                            if ($usuario['ultimo_acceso']) {
                                $fecha = new DateTime($usuario['ultimo_acceso']);
                                echo $fecha->format('d/m/Y H:i');
                            } else {
                                echo 'Nunca';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Editar información -->
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-edit"></i>
                Editar Información
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($usuario['email']); ?>"
                        required
                    >
                </div>

                <button type="submit" name="actualizar_perfil" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>

    <!-- Cambiar contraseña -->
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-lock"></i>
                Cambiar Contraseña
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password_actual">Contraseña Actual:</label>
                    <input
                        type="password"
                        id="password_actual"
                        name="password_actual"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_nuevo">Nueva Contraseña:</label>
                    <input
                        type="password"
                        id="password_nuevo"
                        name="password_nuevo"
                        minlength="6"
                        required
                    >
                    <small class="text-muted">Mínimo 6 caracteres</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmar">Confirmar Nueva Contraseña:</label>
                    <input
                        type="password"
                        id="password_confirmar"
                        name="password_confirmar"
                        minlength="6"
                        required
                    >
                </div>

                <button type="submit" name="cambiar_password" class="btn btn-warning">
                    <i class="fas fa-key"></i>
                    Cambiar Contraseña
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
