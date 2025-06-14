<?php
require("conexion.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(["error" => "ID inválido"]);
    exit;
}

$id = intval($_GET['id']);

$stmt = $con->prepare("SELECT id, nombre, correo, telefono, rol FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(["error" => "Usuario no encontrado"]);
    exit;
}

$usuario = $resultado->fetch_assoc();


echo json_encode($usuario);
?>
