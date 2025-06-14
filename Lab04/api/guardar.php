<?php
session_start();
require_once '../db/conection.php';
// Verifica si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
// Lee los datos del JSON recibido
$data = json_decode(file_get_contents('php://input'), true);

$from_user_id = $_SESSION['usuario_id'];
$destinatario_email = $data['destinatario'];
$subject = $data['asunto'];
$message = $data['mensaje'];

// Buscar el ID del destinatario
$stmt = $con->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
$stmt->bind_param("s", $destinatario_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'El destinatario no existe o está suspendido']);
    exit;
}

$to_user_id = $result->fetch_assoc()['id'];

// Insertar en bandeja "drafts" del remitente
$stmt = $con->prepare("INSERT INTO emails (from_user_id, to_user_id, subject, message, status, folder, created_at) 
                       VALUES (?, ?, ?, ?, 'borrador', 'drafts', NOW())");
$stmt->bind_param("iiss", $from_user_id, $to_user_id, $subject, $message);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Error al guardar en enviados']);
    exit;
}

echo json_encode(['success' => true]);
?>
