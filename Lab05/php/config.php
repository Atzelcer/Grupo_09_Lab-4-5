<?php
// Configuración de rutas para el sistema de hotel

// Rutas base
define('BASE_PATH', dirname(dirname(__FILE__)));
define('BASE_URL', '/Laboratorio_05');
define('IMG_PATH', BASE_PATH . '/img');
define('IMG_URL', BASE_URL . '/img');

// Configuración de imágenes
define('DEFAULT_ROOM_IMAGE', 'habitacion-default.jpg');
define('NO_IMAGE_PLACEHOLDER', 'no-image.jpg');

// Estados de habitaciones
define('ROOM_AVAILABLE', 'disponible');
define('ROOM_OCCUPIED', 'ocupada');
define('ROOM_MAINTENANCE', 'mantenimiento');

// Estados de reservas
define('RESERVATION_PENDING', 'pendiente');
define('RESERVATION_CONFIRMED', 'confirmada');
define('RESERVATION_CANCELLED', 'cancelada');
define('RESERVATION_COMPLETED', 'completada');

// Función para obtener la ruta de imagen de una habitación
function getRoomImagePath($habitacion_id, $imagen_nombre) {
    return IMG_PATH . "/HABITACION{$habitacion_id}/{$imagen_nombre}";
}

// Función para obtener la URL de imagen de una habitación
function getRoomImageUrl($habitacion_id, $imagen_nombre) {
    return IMG_URL . "/HABITACION{$habitacion_id}/{$imagen_nombre}";
}

// Función para verificar si una imagen existe
function roomImageExists($habitacion_id, $imagen_nombre) {
    return file_exists(getRoomImagePath($habitacion_id, $imagen_nombre));
}

// Función para obtener imagen por defecto si no existe
function getRoomImageUrlSafe($habitacion_id, $imagen_nombre) {
    if (roomImageExists($habitacion_id, $imagen_nombre)) {
        return getRoomImageUrl($habitacion_id, $imagen_nombre);
    }
    return IMG_URL . '/' . NO_IMAGE_PLACEHOLDER;
}
?>
