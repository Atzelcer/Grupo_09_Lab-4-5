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

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

function handleGet() {
    global $con;
    
    // Obtener filtros
    $usuario_id = $_GET['usuario_id'] ?? '';
    $carpeta = $_GET['carpeta'] ?? '';
    
    // Construir query base
    $query = "
        SELECT e.*, 
               u_from.email as from_email, 
               u_to.email as to_email,
               u_from.name as from_name,
               u_to.name as to_name
        FROM emails e
        JOIN users u_from ON e.from_user_id = u_from.id
        JOIN users u_to ON e.to_user_id = u_to.id
        WHERE 1=1
    ";
    
    $params = [];
    $types = '';
    
    // Aplicar filtros
    if (!empty($usuario_id)) {
        $query .= " AND (e.from_user_id = ? OR e.to_user_id = ?)";
        $params[] = $usuario_id;
        $params[] = $usuario_id;
        $types .= 'ii';
    }
    
    if (!empty($carpeta)) {
        $query .= " AND e.folder = ?";
        $params[] = $carpeta;
        $types .= 's';
    }
    
    $query .= " ORDER BY e.created_at DESC LIMIT 100";
    
    $stmt = $con->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $correos = [];
    while ($row = $result->fetch_assoc()) {
        $correos[] = $row;
    }
    
    echo json_encode($correos);
}

function handleDelete() {
    global $con;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['error' => 'ID de correo requerido']);
        return;
    }
    
    // Eliminar correo
    $query = "DELETE FROM emails WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al eliminar correo o correo no encontrado']);
    }
}
?>
