<?php
session_start();
require_once '../db/conection.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['correo'];
$password = $data['clave'];

$stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $usuario = $res->fetch_assoc();

    if ($usuario['status'] !== 'active') {
        echo json_encode(["success" => false, "message" => "Cuenta suspendida"]);
        exit;
    }

    if (password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['role'] = $usuario['role'];

        $redirect = ($usuario['role'] === 'admin') ? 'dashboard_admin.php' : 'dashboard_user.php';
        echo json_encode(["success" => true, "redirect" => $redirect]);
    } else {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Correo no registrado"]);
}
