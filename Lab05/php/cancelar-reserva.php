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
    
    // Verificar que la reserva pertenece al usuario y puede ser cancelada
    $query_check = "SELECT id, estado, fecha_ingreso 
                    FROM reservas 
                    WHERE id = ? AND usuario_id = ?";
    
    $stmt_check = mysqli_prepare($con, $query_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $reserva_id, $user_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) === 0) {
        throw new Exception('Reserva no encontrada o no tienes permisos para cancelarla');
    }
    
    $reserva = mysqli_fetch_assoc($result_check);
    
    if ($reserva['estado'] !== 'pendiente') {
        throw new Exception('Solo se pueden cancelar reservas en estado pendiente');
    }
    
    // Verificar que la fecha de ingreso no haya pasado
    if (strtotime($reserva['fecha_ingreso']) <= strtotime(date('Y-m-d'))) {
        throw new Exception('No se puede cancelar una reserva cuya fecha de ingreso ya pasó');
    }
    
    // Actualizar el estado de la reserva a cancelada
    $query_update = "UPDATE reservas SET estado = 'cancelada' WHERE id = ?";
    $stmt_update = mysqli_prepare($con, $query_update);
    mysqli_stmt_bind_param($stmt_update, "i", $reserva_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        $response['success'] = true;
        $response['message'] = 'Reserva cancelada exitosamente';
    } else {
        throw new Exception('Error al cancelar la reserva: ' . mysqli_error($con));
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
