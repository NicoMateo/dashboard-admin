# dashboard-admin
# Dashboard de Administración Profesional en PHP

Sistema completo de administración con dashboard, gestión de usuarios, autenticación y panel de control profesional.

## Características

- Sistema de login con sesiones seguras
- Validación de roles (admin, moderador, usuario)
- Dashboard con estadísticas en tiempo real
- Gráficos interactivos con Chart.js
- CRUD completo de usuarios
- Paginación (10 usuarios por página)
- Búsqueda de usuarios por nombre/email
- Registro de actividades (log)
- Diseño responsive y moderno
- Seguridad con PDO y prepared statements
- Contraseñas encriptadas con password_hash()

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache (XAMPP, WAMP, LAMP, etc.)
- Navegador web moderno

## Instalación

### 1. Clonar o copiar los archivos

Copiar todos los archivos en la carpeta `htdocs` de XAMPP:
```
c:\xampp\htdocs\dashboard-admin\
```

### 2. Crear la base de datos

1. Iniciar Apache y MySQL desde el panel de control de XAMPP
2. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
3. Importar el archivo `database.sql` o ejecutar el siguiente SQL:

```sql
CREATE DATABASE IF NOT EXISTS dashboard_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Luego importar las tablas ejecutando el archivo completo `database.sql`

### 3. Configurar la conexión

El archivo `config.php` ya está configurado con los valores por defecto:
- Host: localhost
- Usuario: root
- Password: (vacío)
- Base de datos: dashboard_admin

Si tus credenciales son diferentes, edita el archivo `config.php`.

### 4. Acceder al sistema

Abrir en el navegador:
```
http://localhost/dashboard-admin/
```

## Credenciales de Prueba

El sistema viene con usuarios de prueba precargados:

### Administrador
- Email: `admin@dashboard.com`
- Password: `admin123`
- Permisos: Acceso completo (crear, editar, eliminar usuarios)

### Moderador
- Email: `moderador@dashboard.com`
- Password: `admin123`
- Permisos: Crear y editar usuarios (no puede eliminar)

### Usuario
- Email: `usuario@dashboard.com`
- Password: `admin123`
- Permisos: Solo ver y editar su propio perfil

## Estructura del Proyecto

```
dashboard-admin/
├── assets/
│   ├── css/
│   │   └── style.css          # Estilos del sistema
│   └── js/
│       └── main.js            # JavaScript principal
├── includes/
│   ├── header.php             # Header reutilizable
│   ├── sidebar.php            # Sidebar de navegación
│   └── footer.php             # Footer reutilizable
├── config.php                 # Configuración y conexión DB
├── index.php                  # Página de login
├── dashboard.php              # Dashboard principal
├── usuarios.php               # Gestión de usuarios (CRUD)
├── perfil.php                 # Editar perfil del usuario
├── logout.php                 # Cerrar sesión
├── database.sql               # Script de base de datos
└── README.md                  # Este archivo
```

## Funcionalidades Detalladas

### Dashboard (dashboard.php)
- Total de usuarios registrados
- Usuarios activos e inactivos
- Nuevos usuarios del mes
- Gráfico de usuarios por rol (Chart.js)
- Actividad reciente del sistema
- Acciones rápidas

### Gestión de Usuarios (usuarios.php)
- Listar todos los usuarios
- Crear nuevos usuarios (admin y moderador)
- Editar usuarios existentes (admin y moderador)
- Eliminar usuarios (solo admin)
- Búsqueda por nombre o email
- Paginación de 10 usuarios por página
- Vista en modal para crear/editar

### Perfil (perfil.php)
- Ver información del usuario logueado
- Editar nombre y email
- Cambiar contraseña
- Ver fecha de registro
- Ver última conexión

### Sistema de Roles

**Admin:**
- Acceso completo al sistema
- Puede crear, editar y eliminar usuarios
- Puede asignar cualquier rol

**Moderador:**
- Puede crear y editar usuarios
- No puede eliminar usuarios
- No puede asignar rol de admin

**Usuario:**
- Solo puede ver el dashboard
- Solo puede editar su propio perfil
- No puede gestionar otros usuarios

### Registro de Actividades

El sistema registra automáticamente:
- Inicios de sesión
- Creación de usuarios
- Edición de usuarios
- Eliminación de usuarios
- Cambios de perfil
- Cambios de contraseña

Cada actividad incluye:
- ID de usuario
- Acción realizada
- Descripción detallada
- Dirección IP
- Fecha y hora

## Seguridad

El sistema implementa:

- PDO con prepared statements (previene SQL injection)
- Contraseñas encriptadas con `password_hash()` y `password_verify()`
- Validación de sesiones en cada página protegida
- Validación de roles antes de acciones sensibles
- Sanitización de entradas del usuario
- HttpOnly en cookies de sesión
- CSRF protection recomendado (implementar tokens)

## Personalización

### Cambiar colores

Editar las variables CSS en `assets/css/style.css`:

```css
:root {
    --primary-color: #4e73df;
    --success-color: #1cc88a;
    --danger-color: #e74a3b;
    /* ... más colores */
}
```

### Agregar nuevas páginas

1. Crear el archivo PHP
2. Incluir `config.php` al inicio
3. Usar `verificarSesion()` para proteger la página
4. Incluir header y footer con:
```php
<?php include 'includes/header.php'; ?>
<!-- Tu contenido aquí -->
<?php include 'includes/footer.php'; ?>
```

### Cambiar ítems por página

Editar en `usuarios.php`:
```php
$porPagina = 10; // Cambiar a 20, 50, etc.
```

## Tecnologías Utilizadas

- PHP 7.4+ (Backend)
- MySQL (Base de datos)
- HTML5 (Estructura)
- CSS3 (Estilos)
- JavaScript (Interactividad)
- Chart.js (Gráficos)
- Font Awesome (Iconos)
- PDO (Conexión a DB)

## Soporte de Navegadores

- Chrome (recomendado)
- Firefox
- Safari
- Edge
- Opera

## Responsive Design

El sistema es completamente responsive y se adapta a:
- Escritorio (1920px+)
- Laptop (1366px - 1920px)
- Tablet (768px - 1366px)
- Móvil (< 768px)

## Problemas Comunes

### Error de conexión a la base de datos
- Verificar que MySQL esté corriendo en XAMPP
- Verificar credenciales en `config.php`
- Verificar que la base de datos `dashboard_admin` exista

### No se muestran los estilos
- Verificar que la carpeta `assets` esté en la raíz del proyecto
- Verificar la ruta en el navegador
- Limpiar caché del navegador

### Sesión no persiste
- Verificar permisos de la carpeta temporal de PHP
- Verificar configuración de `session.save_path` en `php.ini`

## Mejoras Futuras Sugeridas

- Implementar recuperación de contraseña por email
- Agregar autenticación de dos factores (2FA)
- Implementar tokens CSRF
- Agregar exportación de datos a Excel/PDF
- Agregar sistema de notificaciones en tiempo real
- Implementar API REST
- Agregar más tipos de gráficos
- Sistema de permisos más granular
- Upload de avatares de usuario
- Tema oscuro/claro

## Licencia

Este proyecto es de código abierto y puede ser utilizado libremente para proyectos personales o comerciales.

## Autor

Nicolas Mateo

---

Desarrollado con PHP, MySQL y mucho café ☕

"Panel de administración completo con PHP, MySQL y sistema de roles"
