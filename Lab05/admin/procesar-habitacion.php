<?php
require("../php/conexion.php");

header('Content-Type: application/json');

// Solo procesar si es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$response = ['success' => false, 'message' => '', 'errors' => []];

try {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'guardar') {
        $id = $_POST['id'] ?? null;
        $numero = $_POST['numero'] ?? '';
        $piso = $_POST['piso'] ?? '';
        $tipo_habitacion_id = $_POST['tipo_habitacion_id'] ?? '';
        $estado = $_POST['estado'] ?? '';
        
        // Validaciones básicas
        if (empty($numero) || empty($piso) || empty($tipo_habitacion_id) || empty($estado)) {
            $response['message'] = 'Todos los campos son obligatorios';
            echo json_encode($response);
            exit;
        }
        
        if ($id) {
            // Actualizar habitación existente
            $stmt = $con->prepare("UPDATE habitaciones SET numero=?, piso=?, tipo_habitacion_id=?, estado=? WHERE id=?");
            $stmt->bind_param("siisi", $numero, $piso, $tipo_habitacion_id, $estado, $id);
        } else {
            // Crear nueva habitación
            $stmt = $con->prepare("INSERT INTO habitaciones (numero, piso, tipo_habitacion_id, estado) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siis", $numero, $piso, $tipo_habitacion_id, $estado);
        }
        
        if ($stmt->execute()) {
            $habitacion_id = $id ?: $con->insert_id;
            
            // Procesar las 4 imágenes específicas
            $resultado_imagenes = procesarImagenes($habitacion_id, $_FILES, $con);
            
            if ($resultado_imagenes['exito']) {
                $response['success'] = true;
                $response['message'] = 'Habitación guardada correctamente';
            } else {
                $response['success'] = true; // Habitación guardada, pero con problemas en imágenes
                $response['message'] = 'Habitación guardada, pero hubo problemas con algunas imágenes';
                $response['errors'] = [$resultado_imagenes['errores']];
            }
        } else {
            $response['message'] = 'Error al guardar habitación: ' . $stmt->error;
        }
    }
    
    if ($action === 'eliminar') {
        $id = $_POST['id'] ?? '';
        
        if (empty($id)) {
            $response['message'] = 'ID de habitación requerido';
            echo json_encode($response);
            exit;
        }
        
        // Eliminar imágenes físicas y de la base de datos
        eliminarImagenesHabitacion($id, $con);
        
        $stmt = $con->prepare("DELETE FROM habitaciones WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Habitación eliminada correctamente';
        } else {
            $response['message'] = 'Error al eliminar habitación: ' . $stmt->error;
        }
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error interno: ' . $e->getMessage();
}

echo json_encode($response);

// Funciones auxiliares (copiadas de habitaciones.php)
function procesarImagenes($habitacion_id, $archivos, $con) {
    $carpeta = "../img/HABITACION{$habitacion_id}";
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }
    
    $tipos_imagen = [
        'principal' => ['nombre' => "habitacion{$habitacion_id}", 'orden' => 1],
        'cama' => ['nombre' => 'cama', 'orden' => 2],
        'bano' => ['nombre' => 'baño', 'orden' => 3],
        'sala' => ['nombre' => 'sala', 'orden' => 4]
    ];
    
    $errores = [];
    $exito = true;
    
    foreach ($tipos_imagen as $tipo => $config) {
        if (isset($_FILES["imagen_{$tipo}"]) && $_FILES["imagen_{$tipo}"]['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES["imagen_{$tipo}"];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            
            // Validar extensión
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'avif'])) {
                $errores[] = "Extensión no válida para {$tipo}";
                continue;
            }
            
            $nombre_archivo = $config['nombre'] . "." . $extension;
            $ruta_destino = $carpeta . "/" . $nombre_archivo;
            
            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                // Eliminar imagen anterior de este tipo en la BD
                $stmt = $con->prepare("DELETE FROM fotografias WHERE habitacion_id = ? AND orden = ?");
                $stmt->bind_param("ii", $habitacion_id, $config['orden']);
                $stmt->execute();
                
                // Insertar nueva imagen en la BD
                $stmt = $con->prepare("INSERT INTO fotografias (habitacion_id, fotografia, orden) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $habitacion_id, $nombre_archivo, $config['orden']);
                
                if (!$stmt->execute()) {
                    $errores[] = "Error al guardar {$tipo} en base de datos";
                    $exito = false;
                }
                
                // Si es imagen principal, crear también habitacion{id}.jpg para compatibilidad
                if ($tipo === 'principal' && $extension !== 'jpg') {
                    $ruta_jpg = $carpeta . "/habitacion{$habitacion_id}.jpg";
                    copy($ruta_destino, $ruta_jpg);
                }
            } else {
                $errores[] = "Error al subir archivo {$tipo}";
                $exito = false;
            }
        }
    }
    
    return ['exito' => $exito, 'errores' => implode(', ', $errores)];
}

function eliminarImagenesHabitacion($habitacion_id, $con) {
    // Obtener las imágenes de la BD antes de eliminarlas
    $stmt = $con->prepare("SELECT fotografia FROM fotografias WHERE habitacion_id = ?");
    $stmt->bind_param("i", $habitacion_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $carpeta = "../img/HABITACION{$habitacion_id}";
    
    // Eliminar archivos físicos
    while ($fila = $resultado->fetch_assoc()) {
        $archivo = $carpeta . "/" . $fila['fotografia'];
        if (file_exists($archivo)) {
            unlink($archivo);
        }
    }
    
    // Eliminar carpeta si está vacía
    if (is_dir($carpeta)) {
        $archivos = glob($carpeta . "/*");
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) unlink($archivo);
        }
        rmdir($carpeta);
    }
    
    // Eliminar registros de la BD
    $stmt = $con->prepare("DELETE FROM fotografias WHERE habitacion_id = ?");
    $stmt->bind_param("i", $habitacion_id);
    $stmt->execute();
}
?>