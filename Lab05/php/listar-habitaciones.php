<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

$response = ['success' => false, 'message' => '', 'habitaciones' => []];

try {
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
    $fecha_salida = $_POST['fecha_salida'] ?? '';
    $tipo_habitacion = $_POST['tipo_habitacion'] ?? '';
    
    // Validar fechas
    if (empty($fecha_ingreso) || empty($fecha_salida)) {
        throw new Exception('Las fechas son obligatorias');
    }
    
    if (strtotime($fecha_ingreso) >= strtotime($fecha_salida)) {
        throw new Exception('La fecha de salida debe ser posterior a la fecha de ingreso');
    }
    
    if (strtotime($fecha_ingreso) < strtotime(date('Y-m-d'))) {
        throw new Exception('La fecha de ingreso no puede ser anterior a hoy');
    }
    
    // Construir consulta base con todas las imágenes
    $query = "SELECT h.*, th.nombre as tipo_nombre, th.superficie, th.nro_de_camas, 
                     th.precio_por_noche, th.descripcion,
                     f1.fotografia as imagen_principal,
                     f2.fotografia as imagen_cama,
                     f3.fotografia as imagen_bano,
                     f4.fotografia as imagen_sala
              FROM habitaciones h 
              JOIN tipo_habitacion th ON h.tipo_habitacion_id = th.id 
              LEFT JOIN fotografias f1 ON h.id = f1.habitacion_id AND f1.orden = 1
              LEFT JOIN fotografias f2 ON h.id = f2.habitacion_id AND f2.orden = 2
              LEFT JOIN fotografias f3 ON h.id = f3.habitacion_id AND f3.orden = 3
              LEFT JOIN fotografias f4 ON h.id = f4.habitacion_id AND f4.orden = 4
              WHERE h.estado = 'disponible'";
    
    $params = [];
    $types = '';
    
    // Filtro por tipo de habitación
    if (!empty($tipo_habitacion)) {
        $query .= " AND h.tipo_habitacion_id = ?";
        $params[] = $tipo_habitacion;
        $types .= 'i';
    }
    
    // Excluir habitaciones que tienen reservas en las fechas solicitadas
    $query .= " AND h.id NOT IN (
                    SELECT r.habitacion_id 
                    FROM reservas r 
                    WHERE r.estado IN ('pendiente', 'confirmada') 
                    AND (
                        (r.fecha_ingreso <= ? AND r.fecha_salida > ?) OR
                        (r.fecha_ingreso < ? AND r.fecha_salida >= ?) OR
                        (r.fecha_ingreso >= ? AND r.fecha_salida <= ?)
                    )
                )";
    
    $params = array_merge($params, [$fecha_ingreso, $fecha_ingreso, $fecha_salida, $fecha_salida, $fecha_ingreso, $fecha_salida]);
    $types .= 'ssssss';
    
    $query .= " ORDER BY th.precio_por_noche ASC";
    
    $stmt = mysqli_prepare($con, $query);
    if ($types) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $habitaciones = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Preparar información de imágenes
        $imagenes = [
            'principal' => $row['imagen_principal'],
            'cama' => $row['imagen_cama'],
            'bano' => $row['imagen_bano'],
            'sala' => $row['imagen_sala']
        ];
        
        // Verificar qué imágenes existen
        $imagenes_verificadas = [];
        foreach ($imagenes as $tipo => $nombre_archivo) {
            if ($nombre_archivo) {
                $ruta_imagen = "../img/HABITACION{$row['id']}/{$nombre_archivo}";
                if (file_exists($ruta_imagen)) {
                    $imagenes_verificadas[$tipo] = [
                        'nombre' => $nombre_archivo,
                        'url' => "img/HABITACION{$row['id']}/{$nombre_archivo}",
                        'existe' => true
                    ];
                } else {
                    $imagenes_verificadas[$tipo] = [
                        'nombre' => $nombre_archivo,
                        'url' => "img/no-image.svg",
                        'existe' => false
                    ];
                }
            } else {
                $imagenes_verificadas[$tipo] = [
                    'nombre' => null,
                    'url' => "img/no-image.svg",
                    'existe' => false
                ];
            }
        }
        
        // Agregar información de imágenes al resultado
        $row['imagenes'] = $imagenes_verificadas;
        $row['tiene_imagen_principal'] = $imagenes_verificadas['principal']['existe'];
        $row['url_imagen_principal'] = $imagenes_verificadas['principal']['url'];
        
        // Contar cuántas imágenes tiene en total
        $row['total_imagenes'] = count(array_filter($imagenes_verificadas, function($img) {
            return $img['existe'];
        }));
        
        $habitaciones[] = $row;
    }
    
    $response['success'] = true;
    $response['habitaciones'] = $habitaciones;
    $response['message'] = count($habitaciones) > 0 ? 
        'Se encontraron ' . count($habitaciones) . ' habitación(es) disponible(s)' : 
        'No hay habitaciones disponibles para las fechas seleccionadas';
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>