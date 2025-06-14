<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

$response = ['success' => false, 'message' => '', 'reservas' => []];

try {
    $user_id = $_SESSION['user_id'];
    
    // Obtener reservas del usuario
    $query = "SELECT r.*, h.numero as habitacion_numero, h.id as habitacion_id, 
                     th.nombre as tipo_nombre, th.precio_por_noche,
                     f.fotografia as foto_principal
              FROM reservas r 
              JOIN habitaciones h ON r.habitacion_id = h.id 
              JOIN tipo_habitacion th ON h.tipo_habitacion_id = th.id
              LEFT JOIN fotografias f ON h.id = f.habitacion_id AND f.orden = 1
              WHERE r.usuario_id = ? 
              ORDER BY r.created_at DESC";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $reservas = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reservas[] = $row;
    }
    
    $response['success'] = true;
    $response['reservas'] = $reservas;
    $response['message'] = count($reservas) > 0 ? 
        'Se encontraron ' . count($reservas) . ' reserva(s)' : 
        'No tienes reservas registradas';
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
