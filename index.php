<?php
require_once 'config.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizar($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, complete todos los campos';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, email, password, rol, activo FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();            
            echo "Email buscado: " . $_POST['email'] . "<br>";
            echo "Password ingresado: " . $_POST['password'] . "<br>";
            echo "Usuario encontrado: ";
            var_dump($usuario);
            
            if ($usuario && password_verify($password, $usuario['password'])) {
                if (!$usuario['activo']) { 
                    $error = 'Su cuenta está inactiva. Contacte al administrador';
                } else {
                    // Login exitoso
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['email'] = $usuario['email'];
                    $_SESSION['rol'] = $usuario['rol'];

                    // Actualizar última conexión
                    $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                    $stmt->execute([$usuario['id']]);

                    // Registrar actividad
                    registrarActividad($pdo, $usuario['id'], 'login', 'Inicio de sesión exitoso');

                    header('Location: dashboard.php');
                    exit();
                }
            } else {
                $error = 'Credenciales incorrectas';
                // Registrar intento fallido (opcional)
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage(); 
            error_log("Error de login: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h1>Dashboard Admin</h1>
                <p>Inicie sesión para continuar</p>
            </div>

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

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Correo electrónico
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@dashboard.com"
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <div class="password-input">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="login-footer">
                <div class="demo-credentials">
                    <h4>Credenciales de prueba:</h4>
                    <p><strong>Admin:</strong> admin@dashboard.com / admin123</p>
                    <p><strong>Moderador:</strong> moderador@dashboard.com / admin123</p>
                    <p><strong>Usuario:</strong> usuario@dashboard.com / admin123</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
