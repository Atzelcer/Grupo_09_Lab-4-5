-- Crear base de datos
CREATE DATABASE IF NOT EXISTS email_system;
USE email_system;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
);

-- Tabla de correos
CREATE TABLE IF NOT EXISTS emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pendiente', 'leído', 'enviado', 'borrador') NOT NULL,
    folder ENUM('inbox', 'sent', 'drafts') NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertar usuario administrador por defecto
INSERT INTO users (email, name, password, role, status, created_at)
VALUES ('admin@company.com', 'Administrador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW());

-- Insertar algunos usuarios de ejemplo
INSERT INTO users (email, name, password, role, status, created_at)
VALUES 
('usuario1@company.com', 'Usuario Uno', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', NOW()),
('usuario2@company.com', 'Usuario Dos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', NOW()),
('usuario3@company.com', 'Usuario Tres', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'suspended', NOW());

-- Insertar algunos correos de ejemplo
INSERT INTO emails (from_user_id, to_user_id, subject, message, status, folder, created_at)
VALUES 
-- Correo de bienvenida en la bandeja de entrada del usuario1
(1, 2, 'Bienvenido al sistema', 'Hola Usuario Uno, bienvenido al sistema de correo interno de la compañía.', 'pendiente', 'inbox', NOW()),
-- Copia en la bandeja de enviados del admin
(1, 2, 'Bienvenido al sistema', 'Hola Usuario Uno, bienvenido al sistema de correo interno de la compañía.', 'enviado', 'sent', NOW()),

-- Correo de actualización en la bandeja de entrada del usuario2
(1, 3, 'Actualización de cuenta', 'Hola Usuario Dos, tu cuenta ha sido actualizada correctamente.', 'leído', 'inbox', NOW()),
-- Copia en la bandeja de enviados del admin
(1, 3, 'Actualización de cuenta', 'Hola Usuario Dos, tu cuenta ha sido actualizada correctamente.', 'enviado', 'sent', NOW()),

-- Borrador del usuario1
(2, 3, 'Consulta sobre proyecto', 'Hola Usuario Dos, tengo una consulta sobre el proyecto...', 'borrador', 'drafts', NOW()),

-- Correo del usuario2 al usuario1
(3, 2, 'Respuesta a consulta', 'Hola Usuario Uno, respecto a tu consulta...', 'pendiente', 'inbox', NOW()),
-- Copia en la bandeja de enviados del usuario2
(3, 2, 'Respuesta a consulta', 'Hola Usuario Uno, respecto a tu consulta...', 'enviado', 'sent', NOW());
