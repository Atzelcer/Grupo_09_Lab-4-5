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
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
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
    
    // Si se pide un usuario específico
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $query = "SELECT id, email, name, role, status, created_at FROM users WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
        }
        return;
    }
    
    // Obtener todos los usuarios
    $query = "SELECT id, email, name, role, status, created_at FROM users ORDER BY created_at DESC";
    $result = $con->query($query);
    
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    
    echo json_encode($usuarios);
}

function handlePost() {
    global $con;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = $data['email'] ?? '';
    $name = $data['name'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? 'user';
    $status = $data['status'] ?? 'active';
    
    // Validaciones
    if (empty($email) || empty($name) || empty($password)) {
        echo json_encode(['error' => 'Todos los campos son obligatorios']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Email inválido']);
        return;
    }
    
    // Verificar si el email ya existe
    $checkQuery = "SELECT id FROM users WHERE email = ?";
    $checkStmt = $con->prepare($checkQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode(['error' => 'El email ya está registrado']);
        return;
    }
    
    // Encriptar contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar usuario
    $query = "INSERT INTO users (email, name, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $con->prepare($query);
    $stmt->bind_param("sssss", $email, $name, $hashedPassword, $role, $status);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $con->insert_id]);
    } else {
        echo json_encode(['error' => 'Error al crear usuario']);
    }
}

function handlePut() {
    global $con;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? '';
    $email = $data['email'] ?? '';
    $name = $data['name'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? '';
    $status = $data['status'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    // Si solo se está cambiando el estado
    if (!empty($status) && empty($email) && empty($name)) {
        $query = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Error al actualizar estado del usuario']);
        }
        return;
    }
    
    // Validaciones para actualización completa
    if (empty($email) || empty($name)) {
        echo json_encode(['error' => 'Email y nombre son obligatorios']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Email inválido']);
        return;
    }
    
    // Verificar si el email ya existe en otro usuario
    $checkQuery = "SELECT id FROM users WHERE email = ? AND id != ?";
    $checkStmt = $con->prepare($checkQuery);
    $checkStmt->bind_param("si", $email, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode(['error' => 'El email ya está registrado por otro usuario']);
        return;
    }
    
    // Construir query de actualización
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET email = ?, name = ?, password = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("sssssi", $email, $name, $hashedPassword, $role, $status, $id);
    } else {
        $query = "UPDATE users SET email = ?, name = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ssssi", $email, $name, $role, $status, $id);
    }
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al actualizar usuario']);
    }
}

function handleDelete() {
    global $con;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    // No permitir eliminar al propio administrador
    if ($id == $_SESSION['usuario_id']) {
        echo json_encode(['error' => 'No puedes eliminar tu propia cuenta']);
        return;
    }
    
    // Eliminar usuario (los correos se eliminan en cascada según el diseño de BD)
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al eliminar usuario']);
    }
}
?>
