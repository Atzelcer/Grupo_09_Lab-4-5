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
    $habitacion_id = $_POST['habitacion_id'] ?? '';
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
    $fecha_salida = $_POST['fecha_salida'] ?? '';
    $precio_total = $_POST['precio_total'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    
    // Validaciones
    if (empty($habitacion_id) || empty($fecha_ingreso) || empty($fecha_salida) || empty($precio_total)) {
        throw new Exception('Todos los campos obligatorios deben estar completos');
    }
    
    if (strtotime($fecha_ingreso) >= strtotime($fecha_salida)) {
        throw new Exception('La fecha de salida debe ser posterior a la fecha de ingreso');
    }
    
    if (strtotime($fecha_ingreso) < strtotime(date('Y-m-d'))) {
        throw new Exception('La fecha de ingreso no puede ser anterior a hoy');
    }
    
    // Verificar que la habitación esté disponible
    $query_check = "SELECT h.id, h.estado 
                    FROM habitaciones h 
                    WHERE h.id = ? AND h.estado = 'disponible'";
    
    $stmt_check = mysqli_prepare($con, $query_check);
    mysqli_stmt_bind_param($stmt_check, "i", $habitacion_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) === 0) {
        throw new Exception('La habitación seleccionada no está disponible');
    }
    
    // Verificar que no haya conflictos de fechas
    $query_conflict = "SELECT id FROM reservas 
                       WHERE habitacion_id = ? 
                       AND estado IN ('pendiente', 'confirmada') 
                       AND (
                           (fecha_ingreso <= ? AND fecha_salida > ?) OR
                           (fecha_ingreso < ? AND fecha_salida >= ?) OR
                           (fecha_ingreso >= ? AND fecha_salida <= ?)
                       )";
    
    $stmt_conflict = mysqli_prepare($con, $query_conflict);
    mysqli_stmt_bind_param($stmt_conflict, "issssss", 
        $habitacion_id, $fecha_ingreso, $fecha_ingreso, 
        $fecha_salida, $fecha_salida, $fecha_ingreso, $fecha_salida);
    mysqli_stmt_execute($stmt_conflict);
    $result_conflict = mysqli_stmt_get_result($stmt_conflict);
    
    if (mysqli_num_rows($result_conflict) > 0) {
        throw new Exception('La habitación ya está reservada para las fechas seleccionadas');
    }
    
    // Insertar la reserva
    $query_insert = "INSERT INTO reservas (usuario_id, habitacion_id, fecha_ingreso, fecha_salida, precio_total, observaciones, estado, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, 'pendiente', NOW())";
    
    $stmt_insert = mysqli_prepare($con, $query_insert);
    mysqli_stmt_bind_param($stmt_insert, "iissds", 
        $user_id, $habitacion_id, $fecha_ingreso, $fecha_salida, $precio_total, $observaciones);
    
    if (mysqli_stmt_execute($stmt_insert)) {
        $reserva_id = mysqli_insert_id($con);
        
        $response['success'] = true;
        $response['message'] = 'Reserva creada exitosamente';
        $response['reserva_id'] = $reserva_id;
        
        // Opcional: Cambiar estado de habitación a ocupada en las fechas
        // (esto depende de la lógica de negocio que quieras implementar)
        
    } else {
        throw new Exception('Error al crear la reserva: ' . mysqli_error($con));
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>