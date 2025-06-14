<?php
require_once "conexion.php";

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$query = "%" . $query . "%";

$html = "<h2>Resultados de búsqueda</h2>";

if ($query === "%%") {
    echo "<p>No se ingresó ninguna búsqueda.</p>";
    exit;
}

$stmt = $con->prepare("SELECT nombre, correo FROM usuarios WHERE nombre LIKE ? OR correo LIKE ?");
$stmt->bind_param("ss", $query, $query);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $html .= "<h3>Usuarios</h3><ul>";
    while ($row = $res->fetch_assoc()) {
        $html .= "<li>{$row['nombre']} ({$row['correo']})</li>";
    }
    $html .= "</ul>";
}

$stmt = $con->prepare("SELECT numero, piso FROM habitaciones WHERE numero LIKE ?");
$stmt->bind_param("s", $query);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $html .= "<h3>Habitaciones</h3><ul>";
    while ($row = $res->fetch_assoc()) {
        $html .= "<li>Habitación {$row['numero']} - Piso {$row['piso']}</li>";
    }
    $html .= "</ul>";
}

$stmt = $con->prepare("
    SELECT r.id, u.nombre, r.fecha_ingreso, r.estado
    FROM reservas r
    INNER JOIN usuarios u ON r.usuario_id = u.id
    WHERE u.nombre LIKE ? OR r.estado LIKE ?
");
$stmt->bind_param("ss", $query, $query);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $html .= "<h3>Reservas</h3><ul>";
    while ($row = $res->fetch_assoc()) {
        $html .= "<li>Reserva #{$row['id']} de {$row['nombre']} ({$row['estado']}) - Ingreso: {$row['fecha_ingreso']}</li>";
    }
    $html .= "</ul>";
}

echo $html ?: "<p>No se encontraron resultados.</p>";
