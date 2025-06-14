<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    // Hash en MD5 para comparación (según tu SQL actual)
    $claveHash = md5($clave);

    $stmt = $con->prepare("SELECT * FROM usuarios WHERE correo = ? AND password = ?");
    $stmt->bind_param("ss", $correo, $claveHash);
    $stmt->execute();
    $resultado = $stmt->get_result();    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Guardar datos de sesión (compatibles con ambos sistemas)
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nombre'];
        $_SESSION['user_email'] = $usuario['correo'];
        $_SESSION['user_rol'] = $usuario['rol'];
        
        // Variables adicionales para compatibilidad con el sistema admin
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = $usuario['rol'];

        // Si es admin, redirigir al dashboard
        if ($usuario['rol'] === 'admin') {
            echo json_encode([
                'success' => true,
                'redirect' => 'admin/dashboard.php',
                'user' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'],
                    'rol' => $usuario['rol']
                ]
            ]);
        } else {
            // Usuario normal - permanecer en la misma página
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'],
                    'rol' => $usuario['rol']
                ]
            ]);
        }
    } else {
        // Credenciales incorrectas
        echo json_encode([
            'success' => false,
            'message' => 'Correo o contraseña incorrectos'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>
