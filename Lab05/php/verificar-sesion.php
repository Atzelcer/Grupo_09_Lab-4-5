<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['usuario_id']) && isset($_SESSION['nombre'])) {
    echo json_encode([
        'loggedIn' => true,
        'user' => [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['nombre'],
            'correo' => $_SESSION['correo'],
            'rol' => $_SESSION['rol']
        ]
    ]);
} else {
    echo json_encode([
        'loggedIn' => false
    ]);
}
?>
