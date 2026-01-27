<?php
require_once 'config.php';
verificarSesion();

$pageTitle = 'Gestión de Usuarios';
$error = '';
$success = '';

// Paginación
$porPagina = 10;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $porPagina;

// Búsqueda
$busqueda = isset($_GET['busqueda']) ? sanitizar($_GET['busqueda']) : '';

// CREAR USUARIO
if (isset($_POST['crear_usuario'])) {
    verificarRol(['admin', 'moderador']);

    $nombre = sanitizar($_POST['nombre']);
    $email = sanitizar($_POST['email']);
    $password = $_POST['password'];
    $rol = sanitizar($_POST['rol']);
    $activo = sanitizar($_POST['activo']);

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        try {
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error = 'El email ya está registrado';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $email, $passwordHash, $rol, $activo]);

                registrarActividad($pdo, $_SESSION['usuario_id'], 'crear_usuario', "Creó nuevo usuario: $nombre");
                $success = 'Usuario creado exitosamente';
            }
        } catch (PDOException $e) {
            $error = 'Error al crear usuario';
            error_log("Error al crear usuario: " . $e->getMessage());
        }
    }
}

// EDITAR USUARIO
if (isset($_POST['editar_usuario'])) {
    verificarRol(['admin', 'moderador']);

    $id = (int)$_POST['id'];
    $nombre = sanitizar($_POST['nombre']);
    $email = sanitizar($_POST['email']);
    $rol = sanitizar($_POST['rol']);
    $activo = sanitizar($_POST['activo']);
    $password = $_POST['password'];

    if (empty($nombre) || empty($email)) {
        $error = 'Nombre y email son obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } else {
        try {
            // Verificar si el email ya existe en otro usuario
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);

            if ($stmt->fetch()) {
                $error = 'El email ya está en uso por otro usuario';
            } else {
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error = 'La contraseña debe tener al menos 6 caracteres';
                    } else {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ?, rol = ?, activo = ? WHERE id = ?");
                        $stmt->execute([$nombre, $email, $passwordHash, $rol, $activo, $id]);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ?, activo = ? WHERE id = ?");
                    $stmt->execute([$nombre, $email, $rol, $activo, $id]);
                }

                if (empty($error)) {
                    registrarActividad($pdo, $_SESSION['usuario_id'], 'editar_usuario', "Editó usuario: $nombre");
                    $success = 'Usuario actualizado exitosamente';
                }
            }
        } catch (PDOException $e) {
            $error = 'Error al actualizar usuario';
            error_log("Error al actualizar usuario: " . $e->getMessage());
        }
    }
}

// ELIMINAR USUARIO
if (isset($_GET['eliminar']) && $_SESSION['rol'] === 'admin') {
    $id = (int)$_GET['eliminar'];

    // No permitir eliminar el propio usuario
    if ($id === $_SESSION['usuario_id']) {
        $error = 'No puedes eliminar tu propia cuenta';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);

                registrarActividad($pdo, $_SESSION['usuario_id'], 'eliminar_usuario', "Eliminó usuario: {$usuario['nombre']}");
                $success = 'Usuario eliminado exitosamente';
            }
        } catch (PDOException $e) {
            $error = 'Error al eliminar usuario';
            error_log("Error al eliminar usuario: " . $e->getMessage());
        }
    }
}

// OBTENER USUARIOS
try {
    // Contar total de usuarios (con búsqueda)
    if ($busqueda) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios WHERE nombre LIKE ? OR email LIKE ?");
        $stmt->execute(["%$busqueda%", "%$busqueda%"]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    }
    $result = $stmt->fetch();
$totalUsuarios = $result ? $result['total'] : 0;
    $totalPaginas = ceil($totalUsuarios / $porPagina);

    // Obtener usuarios de la página actual
    if ($busqueda) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute(["%$busqueda%", "%$busqueda%", $porPagina, $offset]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute([$porPagina, $offset]);
    }
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error al obtener usuarios';
    error_log("Error al obtener usuarios: " . $e->getMessage());
    $usuarios = [];
    $totalPaginas = 1;
}

// Obtener usuario para editar
$usuarioEditar = null;
if (isset($_GET['editar'])) {
    verificarRol(['admin', 'moderador']);
    $id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuarioEditar = $stmt->fetch();
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

<!-- Búsqueda y acciones -->
<div class="card">
    <div class="card-body">
        <div class="toolbar">
            <form method="GET" class="search-form">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        name="busqueda"
                        placeholder="Buscar por nombre o email..."
                        value="<?php echo htmlspecialchars($busqueda); ?>"
                    >
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if ($busqueda): ?>
                        <a href="usuarios.php" class="btn btn-secondary">Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'moderador'): ?>
                <button class="btn btn-success" onclick="mostrarModalCrear()">
                    <i class="fas fa-plus"></i>
                    Nuevo Usuario
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>activo</th>
                        <th>Registro</th>
                        <th>Última Conexión</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No se encontraron usuarios</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['id']; ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $usuario['rol']; ?>">
                                        <?php echo ucfirst($usuario['rol']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $usuario['activo'] === 'activo' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($usuario['activo']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $fecha = new DateTime($usuario['created_at']);
                                    echo $fecha->format('d/m/Y');
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($usuario['ultimo_acceso']) {
                                        $fecha = new DateTime($usuario['ultimo_acceso']);
                                        echo $fecha->format('d/m/Y H:i');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'moderador'): ?>
                                            <button
                                                class="btn btn-sm btn-info"
                                                onclick='editarUsuario(<?php echo json_encode($usuario); ?>)'
                                                title="Editar"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($_SESSION['rol'] === 'admin' && $usuario['id'] !== $_SESSION['usuario_id']): ?>
                                            <a
                                                href="usuarios.php?eliminar=<?php echo $usuario['id']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Está seguro de eliminar este usuario?')"
                                                title="Eliminar"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <?php if ($paginaActual > 1): ?>
                    <a href="?pagina=<?php echo $paginaActual - 1; ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>

                <span class="pagination-info">
                    Página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?>
                </span>

                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="?pagina=<?php echo $paginaActual + 1; ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>" class="btn btn-sm btn-secondary">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Crear Usuario -->
<div id="modalCrear" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Crear Nuevo Usuario</h3>
            <button class="modal-close" onclick="cerrarModalCrear()">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="crear_nombre">Nombre:</label>
                    <input type="text" id="crear_nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="crear_email">Email:</label>
                    <input type="email" id="crear_email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="crear_password">Contraseña:</label>
                    <input type="password" id="crear_password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="crear_rol">Rol:</label>
                    <select id="crear_rol" name="rol" required>
                        <option value="usuario">Usuario</option>
                        <option value="moderador">Moderador</option>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <option value="admin">Admin</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="crear_activo">activo:</label>
                    <select id="crear_activo" name="activo" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalCrear()">Cancelar</button>
                <button type="submit" name="crear_usuario" class="btn btn-success">Crear Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Usuario</h3>
            <button class="modal-close" onclick="cerrarModalEditar()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" id="editar_id" name="id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="editar_nombre">Nombre:</label>
                    <input type="text" id="editar_nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="editar_email">Email:</label>
                    <input type="email" id="editar_email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="editar_password">Nueva Contraseña (dejar vacío para mantener):</label>
                    <input type="password" id="editar_password" name="password" minlength="6">
                </div>

                <div class="form-group">
                    <label for="editar_rol">Rol:</label>
                    <select id="editar_rol" name="rol" required>
                        <option value="usuario">Usuario</option>
                        <option value="moderador">Moderador</option>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <option value="admin">Admin</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="editar_activo">activo:</label>
                    <select id="editar_activo" name="activo" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalEditar()">Cancelar</button>
                <button type="submit" name="editar_usuario" class="btn btn-primary">Actualizar Usuario</button>
            </div>
        </form>
    </div>
</div>

<script>
function mostrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'flex';
}

function cerrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'none';
}

function editarUsuario(usuario) {
    document.getElementById('editar_id').value = usuario.id;
    document.getElementById('editar_nombre').value = usuario.nombre;
    document.getElementById('editar_email').value = usuario.email;
    document.getElementById('editar_rol').value = usuario.rol;
    document.getElementById('editar_activo').value = usuario.activo;
    document.getElementById('editar_password').value = '';
    document.getElementById('modalEditar').style.display = 'flex';
}

function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modalCrear = document.getElementById('modalCrear');
    const modalEditar = document.getElementById('modalEditar');
    if (event.target === modalCrear) {
        cerrarModalCrear();
    }
    if (event.target === modalEditar) {
        cerrarModalEditar();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
