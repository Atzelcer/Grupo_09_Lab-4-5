<?php
require_once '../php/conexion.php';

header('Content-Type: application/json');

try {
    $query = "SELECT h.*, th.nombre as tipo_nombre, th.superficie, th.nro_de_camas, 
                     th.precio_por_noche, th.descripcion, f.fotografia as foto_principal
              FROM habitaciones h 
              JOIN tipo_habitacion th ON h.tipo_habitacion_id = th.id 
              LEFT JOIN fotografias f ON h.id = f.habitacion_id AND f.orden = 1
              WHERE h.estado = 'disponible'
              ORDER BY h.id";
    
    $result = mysqli_query($con, $query);
    $habitaciones = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row['imagen_existe'] = false;
        if ($row['foto_principal']) {
            $ruta_imagen = "../img/HABITACION" . $row['id'] . "/" . $row['foto_principal'];
            $row['imagen_existe'] = file_exists($ruta_imagen);
            $row['ruta_imagen'] = $ruta_imagen;
        }
        $habitaciones[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($habitaciones),
        'habitaciones' => $habitaciones
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
