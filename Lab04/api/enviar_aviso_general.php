<?php
session_start();
require_once '../db/conection.php';

header('Content-Type: application/json');

// Verificar autenticación y rol de admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$asunto = $data['asunto'] ?? '';
$mensaje = $data['mensaje'] ?? '';
$solo_activos = $data['solo_activos'] ?? true;

// Validaciones
if (empty($asunto) || empty($mensaje)) {
    echo json_encode(['error' => 'Asunto y mensaje son obligatorios']);
    exit;
}

$admin_id = $_SESSION['usuario_id'];

try {
    // Comenzar transacción
    $con->begin_transaction();
    
    // Obtener usuarios destinatarios
    $query_usuarios = "SELECT id FROM users WHERE id != ?"; // Excluir al admin actual
    $params = [$admin_id];
    $types = 'i';
    
    if ($solo_activos) {
        $query_usuarios .= " AND status = 'active'";
    }
    
    $stmt_usuarios = $con->prepare($query_usuarios);
    $stmt_usuarios->bind_param($types, ...$params);
    $stmt_usuarios->execute();
    $result_usuarios = $stmt_usuarios->get_result();
    
    $usuarios_destinatarios = [];
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios_destinatarios[] = $row['id'];
    }
    
    if (empty($usuarios_destinatarios)) {
        $con->rollback();
        echo json_encode(['error' => 'No hay usuarios para enviar el aviso']);
        exit;
    }
    
    $usuarios_notificados = 0;
    
    // Preparar queries para insertar correos
    $query_sent = "INSERT INTO emails (from_user_id, to_user_id, subject, message, status, folder, created_at) 
                   VALUES (?, ?, ?, ?, 'enviado', 'sent', NOW())";
    $query_inbox = "INSERT INTO emails (from_user_id, to_user_id, subject, message, status, folder, created_at) 
                    VALUES (?, ?, ?, ?, 'pendiente', 'inbox', NOW())";
    
    $stmt_sent = $con->prepare($query_sent);
    $stmt_inbox = $con->prepare($query_inbox);
    
    // Enviar aviso a cada usuario
    foreach ($usuarios_destinatarios as $usuario_id) {
        // Insertar en bandeja de enviados del admin (una copia por cada destinatario)
        $stmt_sent->bind_param("iiss", $admin_id, $usuario_id, $asunto, $mensaje);
        if (!$stmt_sent->execute()) {
            $con->rollback();
            echo json_encode(['error' => 'Error al guardar en bandeja de enviados']);
            exit;
        }
        
        // Insertar en bandeja de entrada del destinatario
        $stmt_inbox->bind_param("iiss", $admin_id, $usuario_id, $asunto, $mensaje);
        if (!$stmt_inbox->execute()) {
            $con->rollback();
            echo json_encode(['error' => 'Error al guardar en bandeja de entrada del destinatario']);
            exit;
        }
        
        $usuarios_notificados++;
    }
    
    // Confirmar transacción
    $con->commit();
    
    echo json_encode([
        'success' => true, 
        'usuarios_notificados' => $usuarios_notificados,
        'message' => "Aviso enviado correctamente a {$usuarios_notificados} usuarios"
    ]);
    
} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
