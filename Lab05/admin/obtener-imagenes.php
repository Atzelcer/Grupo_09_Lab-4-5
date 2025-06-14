<?php
require("../php/conexion.php");

header('Content-Type: application/json');

if (!isset($_GET['habitacion_id'])) {
    echo json_encode(['error' => 'habitacion_id requerido']);
    exit;
}

$habitacion_id = intval($_GET['habitacion_id']);

$stmt = $con->prepare("SELECT fotografia, orden FROM fotografias WHERE habitacion_id = ? ORDER BY orden");
$stmt->bind_param("i", $habitacion_id);
$stmt->execute();
$resultado = $stmt->get_result();

$imagenes = [];
while ($fila = $resultado->fetch_assoc()) {
    $imagenes[$fila['orden']] = $fila['fotografia'];
}

echo json_encode($imagenes);
?>