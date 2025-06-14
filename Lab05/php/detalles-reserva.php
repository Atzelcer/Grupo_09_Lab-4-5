<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'usuario') {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    $user_id = $_SESSION['user_id'];
    $reserva_id = $_POST['reserva_id'] ?? '';
    
    if (empty($reserva_id)) {
        throw new Exception('ID de reserva requerido');
    }
    
    // Verificar que la reserva pertenece al usuario y obtener detalles
    $query_check = "SELECT r.*, h.numero as habitacion_numero, th.nombre as tipo_nombre, 
                           th.superficie, th.nro_de_camas, th.precio_por_noche, th.descripcion
                    FROM reservas r 
                    JOIN habitaciones h ON r.habitacion_id = h.id 
                    JOIN tipo_habitacion th ON h.tipo_habitacion_id = th.id
                    WHERE r.id = ? AND r.usuario_id = ?";
    
    $stmt_check = mysqli_prepare($con, $query_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $reserva_id, $user_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) === 0) {
        throw new Exception('Reserva no encontrada o no tienes permisos para acceder a ella');
    }
    
    $reserva = mysqli_fetch_assoc($result_check);
    
    $response['success'] = true;
    $response['reserva'] = $reserva;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
