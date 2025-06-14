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
// Obtener datos del borrador original
$query = "SELECT * FROM emails WHERE id = ? AND from_user_id = ? AND folder = 'drafts'";
$stmt = $con->prepare($query);
$stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
$email = $result->fetch_assoc();
if (!$email) {
    echo json_encode(['error' => 'Correo no encontrado o no autorizado']);
    exit;
}
// Crear copia en bandeja de salida (sent)
$insert_sent = $con->prepare("
    INSERT INTO emails (from_user_id, to_user_id, subject, message, status, folder, created_at) 
    VALUES (?, ?, ?, ?, 'enviado', 'sent', NOW())
");
$insert_sent->bind_param("iiss", $email['from_user_id'], $email['to_user_id'], $email['subject'], $email['message']);
$insert_sent->execute();

// Crear copia en bandeja de entrada (inbox) para el destinatario
$insert_inbox = $con->prepare("
    INSERT INTO emails (from_user_id, to_user_id, subject, message, status, folder, created_at) 
    VALUES (?, ?, ?, ?, 'pendiente', 'inbox', NOW())
");
$insert_inbox->bind_param("iiss", $email['from_user_id'], $email['to_user_id'], $email['subject'], $email['message']);
$insert_inbox->execute();

// Eliminar borrador original
$delete = $con->prepare("DELETE FROM emails WHERE id = ?");
$delete->bind_param("i", $id);
$delete->execute();

echo json_encode(['success' => true]);
