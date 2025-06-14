<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    // Hash en MD5 para comparación (según tu SQL actual)
    $claveHash = md5($clave);

    $stmt = $con->prepare("SELECT * FROM usuarios WHERE correo = ? AND password = ?");
    $stmt->bind_param("ss", $correo, $claveHash);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Guardar datos de sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        // Redirigir por rol
        if ($usuario['rol'] === 'admin') {
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            header("Location: ../cliente/panel.php");
            exit;
        }
    } else {
        // Credenciales incorrectas
        echo "<script>alert('Correo o contraseña incorrectos'); window.history.back();</script>";
        exit;
    }
} else {
    // Acceso no permitido
    header("Location: ../index.html");
    exit;
}
