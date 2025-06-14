<?php
session_start();
require_once '../db/conection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}
$user_id = $_SESSION['usuario_id'];
// Permitir que el usuario borre correos donde sea el remitente o el destinatario
$query = "DELETE FROM emails WHERE id = ? AND (from_user_id = ? OR to_user_id = ?)";
$stmt = $con->prepare($query);
$stmt->bind_param("iii", $id, $user_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'No se pudo eliminar']);
}
