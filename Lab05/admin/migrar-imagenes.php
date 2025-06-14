<?php
require("../php/conexion.php");

echo "<h2>Migración de imágenes existentes a la base de datos</h2>";

// Obtener todas las habitaciones
$sql = "SELECT id FROM habitaciones";
$resultado = $con->query($sql);

$contador = 0;

while ($habitacion = $resultado->fetch_assoc()) {
    $habitacion_id = $habitacion['id'];
    $carpeta = "../img/HABITACION{$habitacion_id}";
    
    if (is_dir($carpeta)) {
        echo "<h3>Procesando habitación ID: {$habitacion_id}</h3>";
        
        // Mapeo de archivos a orden
        $archivos = [
            1 => ["habitacion{$habitacion_id}.jpg", "habitacion{$habitacion_id}.avif", "habitacion{$habitacion_id}.png"],
            2 => ["cama.jpg", "cama.avif", "cama.png"],
            3 => ["baño.jpg", "baño.avif", "baño.png"],
            4 => ["sala.jpg", "sala de estar.jpg", "sala.jpeg", "sala de estar.jpeg"]
        ];
        
        foreach ($archivos as $orden => $nombres_posibles) {
            foreach ($nombres_posibles as $nombre_archivo) {
                $ruta_completa = $carpeta . "/" . $nombre_archivo;
                
                if (file_exists($ruta_completa)) {
                    // Verificar si ya existe en la BD
                    $stmt = $con->prepare("SELECT id FROM fotografias WHERE habitacion_id = ? AND orden = ?");
                    $stmt->bind_param("ii", $habitacion_id, $orden);
                    $stmt->execute();
                    $existe = $stmt->get_result()->num_rows > 0;
                    
                    if (!$existe) {
                        // Insertar en la BD
                        $stmt = $con->prepare("INSERT INTO fotografias (habitacion_id, fotografia, orden) VALUES (?, ?, ?)");
                        $stmt->bind_param("isi", $habitacion_id, $nombre_archivo, $orden);
                        
                        if ($stmt->execute()) {
                            echo " Agregado: {$nombre_archivo} (orden {$orden})<br>";
                            $contador++;
                        } else {
                            echo " Error al agregar: {$nombre_archivo}<br>";
                        }
                    } else {
                        echo " Ya existe: orden {$orden} para habitación {$habitacion_id}<br>";
                    }
                    
                    break; // Solo tomar el primer archivo encontrado para este orden
                }
            }
        }
    } else {
        echo "<p> Carpeta no encontrada: {$carpeta}</p>";
    }
}

echo "<h3> Migración completada. {$contador} imágenes agregadas a la base de datos.</h3>";

// Mostrar resumen
echo "<h3>Resumen de imágenes en la base de datos:</h3>";
$sql = "SELECT h.numero, f.fotografia, f.orden 
        FROM fotografias f 
        INNER JOIN habitaciones h ON f.habitacion_id = h.id 
        ORDER BY h.numero, f.orden";
$resultado = $con->query($sql);

echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr><th>Habitación</th><th>Imagen</th><th>Orden</th></tr>";

while ($fila = $resultado->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$fila['numero']}</td>";
    echo "<td>{$fila['fotografia']}</td>";
    echo "<td>{$fila['orden']}</td>";
    echo "</tr>";
}

echo "</table>";
?>