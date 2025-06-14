<?php
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave = trim($_POST['clave'] ?? '');
    $confirmar_clave = trim($_POST['confirmar_clave'] ?? '');

    // Validaciones
    if (empty($nombre) || empty($correo) || empty($clave) || empty($confirmar_clave)) {
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son obligatorios'
        ]);
        exit;
    }

    if ($clave !== $confirmar_clave) {
        echo json_encode([
            'success' => false,
            'message' => 'Las contraseñas no coinciden'
        ]);
        exit;
    }

    if (strlen($clave) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'La contraseña debe tener al menos 6 caracteres'
        ]);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'El correo electrónico no es válido'
        ]);
        exit;
    }

    // Verificar si el correo ya existe
    $stmt = $con->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Este correo electrónico ya está registrado'
        ]);
        exit;
    }

    // Crear nuevo usuario con rol "usuario"
    $rol = 'usuario';
    $claveHash = md5($clave);

    $stmt = $con->prepare("INSERT INTO usuarios (nombre, correo, password, rol, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $nombre, $correo, $claveHash, $rol);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => '¡Cuenta creada con éxito! Ahora puedes acceder con tus credenciales.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear la cuenta. Intenta nuevamente.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>