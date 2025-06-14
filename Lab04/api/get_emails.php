<?php
session_start();
require_once '../db/conection.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$user_id = $_SESSION['usuario_id'];
$tipo = $_GET['tipo']; // inbox, sent, drafts

if ($tipo === 'inbox') {
    $query = "
        SELECT e.*, u.email AS correo_origen 
        FROM emails e 
        JOIN users u ON e.from_user_id = u.id 
        WHERE e.to_user_id = ? AND e.folder = 'inbox'
    ";
} elseif ($tipo === 'sent') {
    $query = "
        SELECT e.*, u.email AS correo_destino 
        FROM emails e 
        JOIN users u ON e.to_user_id = u.id 
        WHERE e.from_user_id = ? AND e.folder = 'sent'
    ";
} elseif ($tipo === 'drafts') {
    $query = "
        SELECT e.*, u.email AS correo_destino 
        FROM emails e 
        JOIN users u ON e.to_user_id = u.id 
        WHERE e.from_user_id = ? AND e.folder = 'drafts'
    ";
} else {
    echo json_encode([]);
    exit;
}

$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$datos = [];
while ($fila = $res->fetch_assoc()) {
    $datos[] = $fila;
}
echo json_encode($datos);
