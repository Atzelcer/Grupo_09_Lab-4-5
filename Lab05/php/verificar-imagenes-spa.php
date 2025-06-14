<?php
// Verificador de imágenes para el sistema SPA
header('Content-Type: application/json');

$imagenes = [
    'Fondo principal' => '../img/ENTRADA HOTEL/NDMHOtel.jpg',
    'Hero section' => '../img/ENTRADA HOTEL/Imagen1.jpg',
    'Características' => '../img/ENTRADA HOTEL/Imagen2.jpg',
    'Galería' => '../img/ENTRADA HOTEL/Imagen3.jpg',
    'Reservas' => '../img/HABITACION1/habitacion1.jpg',
    'Mis reservas' => '../img/HABITACION2/habitacion2.jpg'
];

$resultado = [
    'status' => 'success',
    'message' => 'Verificación de imágenes completada',
    'imagenes' => [],
    'resumen' => [
        'total' => count($imagenes),
        'encontradas' => 0,
        'faltantes' => 0
    ]
];

foreach ($imagenes as $nombre => $ruta) {
    $existe = file_exists($ruta);
    $tamaño = $existe ? filesize($ruta) : 0;
    
    $resultado['imagenes'][] = [
        'nombre' => $nombre,
        'ruta' => $ruta,
        'existe' => $existe,
        'tamaño' => $tamaño,
        'tamaño_legible' => $existe ? number_format($tamaño / 1024, 2) . ' KB' : 'N/A'
    ];
    
    if ($existe) {
        $resultado['resumen']['encontradas']++;
    } else {
        $resultado['resumen']['faltantes']++;
    }
}

if ($resultado['resumen']['faltantes'] > 0) {
    $resultado['status'] = 'warning';
    $resultado['message'] = 'Algunas imágenes no se encontraron';
}

echo json_encode($resultado, JSON_PRETTY_PRINT);
?>
