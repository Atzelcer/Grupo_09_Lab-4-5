<?php
session_start();
require_once '../db/conection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!isset($_SESSION['usuario_id']) || !$id) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Solo cambiar si el correo fue recibido por el usuario y está pendiente
$query = "UPDATE emails SET status = 'leído' WHERE id = ? AND to_user_id = ? AND status = 'pendiente'";
$stmt = $con->prepare($query);
$stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se actualizó']);
}
