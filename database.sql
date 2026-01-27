-- Crear base de datos
CREATE DATABASE IF NOT EXISTS dashboard_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dashboard_admin;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'moderador', 'usuario') NOT NULL DEFAULT 'usuario',
    activo ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de actividades (log)
CREATE TABLE IF NOT EXISTS actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuarios de prueba
-- Password para todos: admin123
INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES
('Administrador', 'admin@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo'),
('Moderador Test', 'moderador@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderador', 'activo'),
('Usuario Test', 'usuario@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', 'activo'),
('Usuario Inactivo', 'inactivo@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', 'inactivo'),
('Juan Pérez', 'juan@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', 'activo'),
('María García', 'maria@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderador', 'activo'),
('Carlos López', 'carlos@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', 'activo'),
('Ana Martínez', 'ana@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', 'activo'),
('Pedro Sánchez', 'pedro@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', 'inactivo'),
('Laura Fernández', 'laura@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', 'activo');

-- Insertar actividades de ejemplo
INSERT INTO actividades (usuario_id, accion, descripcion, ip_address) VALUES
(1, 'login', 'Inicio de sesión exitoso', '127.0.0.1'),
(1, 'crear_usuario', 'Creó nuevo usuario: Juan Pérez', '127.0.0.1'),
(2, 'login', 'Inicio de sesión exitoso', '127.0.0.1'),
(3, 'editar_perfil', 'Actualizó su información de perfil', '127.0.0.1'),
(1, 'eliminar_usuario', 'Eliminó usuario ID: 15', '127.0.0.1');
