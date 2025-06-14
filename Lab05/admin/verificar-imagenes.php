<?php
require_once '../php/conexion.php';

// Script para verificar y corregir las fotografías en la base de datos
echo "<h2>Verificación de fotografías en la base de datos</h2>";

// Obtener todas las fotografías
$query = "SELECT f.*, h.id as habitacion_id FROM fotografias f 
          JOIN habitaciones h ON f.habitacion_id = h.id 
          ORDER BY f.habitacion_id, f.orden";

$result = mysqli_query($con, $query);

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID Foto</th><th>Habitación ID</th><th>Fotografía</th><th>Orden</th><th>Existe</th><th>Ruta Completa</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $ruta_completa = "../img/HABITACION" . $row['habitacion_id'] . "/" . $row['fotografia'];
        $existe = file_exists($ruta_completa) ? "✅ SÍ" : "❌ NO";
        $color = file_exists($ruta_completa) ? "#d4edda" : "#f8d7da";
        
        echo "<tr style='background-color: $color;'>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['habitacion_id'] . "</td>";
        echo "<td>" . $row['fotografia'] . "</td>";
        echo "<td>" . $row['orden'] . "</td>";
        echo "<td>" . $existe . "</td>";
        echo "<td>" . $ruta_completa . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error al consultar las fotografías: " . mysqli_error($con);
}

// Mostrar habitaciones disponibles
echo "<br><h2>Habitaciones disponibles</h2>";
$query_hab = "SELECT h.*, th.nombre as tipo_nombre, th.precio_por_noche, f.fotografia as foto_principal
              FROM habitaciones h 
              JOIN tipo_habitacion th ON h.tipo_habitacion_id = th.id
              LEFT JOIN fotografias f ON h.id = f.habitacion_id AND f.orden = 1
              WHERE h.estado = 'disponible'
              ORDER BY h.id";

$result_hab = mysqli_query($con, $query_hab);

if ($result_hab) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Número</th><th>Tipo</th><th>Precio/Noche</th><th>Foto Principal</th><th>Imagen Existe</th></tr>";
    
    while ($hab = mysqli_fetch_assoc($result_hab)) {
        $ruta_imagen = $hab['foto_principal'] ? "../img/HABITACION" . $hab['id'] . "/" . $hab['foto_principal'] : "";
        $imagen_existe = $ruta_imagen && file_exists($ruta_imagen) ? "✅ SÍ" : "❌ NO";
        $color = ($ruta_imagen && file_exists($ruta_imagen)) ? "#d4edda" : "#f8d7da";
        
        echo "<tr style='background-color: $color;'>";
        echo "<td>" . $hab['id'] . "</td>";
        echo "<td>" . $hab['numero'] . "</td>";
        echo "<td>" . $hab['tipo_nombre'] . "</td>";
        echo "<td>$" . number_format($hab['precio_por_noche'], 2) . "</td>";
        echo "<td>" . ($hab['foto_principal'] ?: 'Sin foto') . "</td>";
        echo "<td>" . $imagen_existe . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error al consultar las habitaciones: " . mysqli_error($con);
}

echo "<br><p><a href='../cliente/panel.php?section=nueva-reserva'>← Volver al panel de reservas</a></p>";
?>
