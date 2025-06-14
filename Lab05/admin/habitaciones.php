<?php
require("../php/conexion.php");

// Procesar formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'guardar') {
        $id = $_POST['id'] ?? null;
        $numero = $_POST['numero'];
        $piso = $_POST['piso'];
        $tipo_habitacion_id = $_POST['tipo_habitacion_id'];
        $estado = $_POST['estado'];
        
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
                $mensaje = 'Habitación guardada correctamente';
                $tipo_mensaje = 'exito';
            } else {
                $mensaje = 'Habitación guardada, pero hubo problemas con algunas imágenes: ' . $resultado_imagenes['errores'];
                $tipo_mensaje = 'exito';
            }
        } else {
            $mensaje = 'Error al guardar habitación';
            $tipo_mensaje = 'error';
        }
    }
    
    if ($action === 'eliminar') {
        $id = $_POST['id'];
        
        // Eliminar imágenes físicas y de la base de datos
        eliminarImagenesHabitacion($id, $con);
        
        $stmt = $con->prepare("DELETE FROM habitaciones WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $mensaje = 'Habitación eliminada correctamente';
            $tipo_mensaje = 'exito';
        } else {
            $mensaje = 'Error al eliminar habitación';
            $tipo_mensaje = 'error';
        }
    }
}

function procesarImagenes($habitacion_id, $archivos, $con) {
    $carpeta = "../img/HABITACION{$habitacion_id}";
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }
    
    $tipos_imagen = [
        'principal' => ['nombre' => "habitacion{$habitacion_id}", 'orden' => 1, 'descripcion' => 'Imagen principal'],
        'cama' => ['nombre' => 'cama', 'orden' => 2, 'descripcion' => 'Cama'],
        'bano' => ['nombre' => 'baño', 'orden' => 3, 'descripcion' => 'Baño'],
        'sala' => ['nombre' => 'sala', 'orden' => 4, 'descripcion' => 'Sala de estar']
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

function obtenerImagenesHabitacion($habitacion_id, $con) {
    $stmt = $con->prepare("SELECT fotografia, orden FROM fotografias WHERE habitacion_id = ? ORDER BY orden");
    $stmt->bind_param("i", $habitacion_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $imagenes = [];
    while ($fila = $resultado->fetch_assoc()) {
        $imagenes[$fila['orden']] = $fila['fotografia'];
    }
    
    return $imagenes;
}

// Consultas principales
$sql = "SELECT h.id, h.numero, h.piso, h.estado, h.tipo_habitacion_id, 
               th.nombre as tipo_nombre, th.precio_por_noche, th.superficie, th.nro_de_camas, th.descripcion,
               (SELECT fotografia FROM fotografias WHERE habitacion_id = h.id AND orden = 1 LIMIT 1) as imagen_principal
        FROM habitaciones h 
        INNER JOIN tipo_habitacion th ON h.tipo_habitacion_id = th.id 
        ORDER BY h.numero";
$resultado = $con->query($sql);

$sqlTipos = "SELECT id, nombre FROM tipo_habitacion";
$tiposHabitacion = $con->query($sqlTipos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Habitaciones</title>
    <link rel="stylesheet" href="../css/habitaciones.css">
</head>
<body>

<div class="header">
    <h1>Gestión de Habitaciones</h1>
    <button class="btn-nuevo" onclick="abrirModal()">Nueva Habitación</button>
</div>

<?php if ($mensaje): ?>
<div class="mensaje <?= $tipo_mensaje ?>">
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<table class="tabla-habitaciones">
    <thead>
        <tr>
            <th class="col-imagen">Vista</th>
            <th>Información de la Habitación</th>
            <th class="col-acciones">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while($habitacion = $resultado->fetch_assoc()): ?>
        <tr>
            <td class="col-imagen">
                <div class="imagen-habitacion" onclick="abrirCarrusel(<?= $habitacion['id'] ?>)">
                    <?php
                    // Mostrar imagen principal desde la base de datos
                    if ($habitacion['imagen_principal']) {
                        $rutaImagen = "../img/HABITACION{$habitacion['id']}/{$habitacion['imagen_principal']}";
                        if (file_exists($rutaImagen)) {
                            echo "<img src='$rutaImagen' alt='Habitación {$habitacion['numero']}' style='width: 100%; height: 100%; object-fit: cover; border-radius: 12px;'>";
                        } else {
                            echo "<span>Imagen no encontrada</span>";
                        }
                    } else {
                        echo "<span>Sin imagen</span>";
                    }
                    ?>
                    <div class="numero-badge">#<?= htmlspecialchars($habitacion['numero']) ?></div>
                    <div class="estado-badge estado-<?= $habitacion['estado'] ?>">
                        <?= ucfirst($habitacion['estado']) ?>
                    </div>
                </div>
            </td>

            <td>
                <div class="info-habitacion">
                    <h3>Habitación <?= htmlspecialchars($habitacion['numero']) ?></h3>
                    <div class="tipo-habitacion"><?= htmlspecialchars($habitacion['tipo_nombre']) ?></div>
                    
                    <div class="detalles-grid">
                        <div class="detalle-item">
                            <span class="detalle-label">Piso</span>
                            <span class="detalle-valor"><?= $habitacion['piso'] ?>°</span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-label">Precio por Noche</span>
                            <span class="detalle-valor precio-valor">$<?= number_format($habitacion['precio_por_noche'], 0) ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-label">Superficie</span>
                            <span class="detalle-valor"><?= $habitacion['superficie'] ?> m²</span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-label">Número de Camas</span>
                            <span class="detalle-valor"><?= $habitacion['nro_de_camas'] ?></span>
                        </div>
                    </div>
                    
                    <?php if($habitacion['descripcion']): ?>
                    <div class="descripcion">
                        <?= htmlspecialchars($habitacion['descripcion']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </td>

            <td class="col-acciones">
                <div class="acciones">
                    <button class="btn-editar" onclick="editarHabitacion(<?= $habitacion['id'] ?>, '<?= htmlspecialchars($habitacion['numero']) ?>', <?= $habitacion['piso'] ?>, <?= $habitacion['tipo_habitacion_id'] ?>, '<?= $habitacion['estado'] ?>')">
                        Editar
                    </button>
                    <button class="btn-eliminar" onclick="eliminarHabitacion(<?= $habitacion['id'] ?>, '<?= htmlspecialchars($habitacion['numero']) ?>')">
                        Eliminar
                    </button>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Modal para crear/editar habitación -->
<div id="modalHabitacion" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Nueva Habitación</h3>
            <span class="close" onclick="cerrarModal()">&times;</span>
        </div>
        
        <div class="modal-tabs">
            <button class="tab-btn active" onclick="cambiarTab('info')">Información</button>
            <button class="tab-btn" onclick="cambiarTab('imagenes')">Imágenes</button>
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="guardar">
            <input type="hidden" name="id" id="habitacionId">
            
            <!-- Tab Información -->
            <div id="tab-info" class="tab-content active">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="numero">Número de Habitación</label>
                        <input type="text" id="numero" name="numero" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="piso">Piso</label>
                        <input type="number" id="piso" name="piso" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_habitacion_id">Tipo de Habitación</label>
                        <select id="tipo_habitacion_id" name="tipo_habitacion_id" required>
                            <option value="">Seleccionar tipo</option>
                            <?php 
                            $tiposHabitacion->data_seek(0);
                            while($tipo = $tiposHabitacion->fetch_assoc()): 
                            ?>
                                <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="disponible">Disponible</option>
                            <option value="ocupada">Ocupada</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Tab Imágenes -->
            <div id="tab-imagenes" class="tab-content">
                <h4 style="color: #00ffe5; margin-bottom: 15px;">Imágenes de la Habitación</h4>
                <p style="color: #888; margin-bottom: 20px;">Sube las 4 imágenes requeridas. La primera será la imagen principal que aparece en la tabla.</p>
                
                <div class="imagenes-grid">
                    <div class="imagen-slot">
                        <label>Imagen Principal</label>
                        <div class="imagen-upload" onclick="seleccionarImagen('principal')">
                            <div class="upload-text">Click para seleccionar<br>imagen principal</div>
                            <img id="preview-principal" style="display: none;">
                            <button type="button" class="change-btn" onclick="event.stopPropagation(); seleccionarImagen('principal')">Cambiar</button>
                            <input type="file" id="file-principal" accept="image/*" onchange="previewImagen(this, 'principal')">
                        </div>
                    </div>
                    
                    <div class="imagen-slot">
                        <label>Cama</label>
                        <div class="imagen-upload" onclick="seleccionarImagen('cama')">
                            <div class="upload-text">Click para seleccionar<br>imagen de cama</div>
                            <img id="preview-cama" style="display: none;">
                            <button type="button" class="change-btn" onclick="event.stopPropagation(); seleccionarImagen('cama')">Cambiar</button>
                            <input type="file" id="file-cama" accept="image/*" onchange="previewImagen(this, 'cama')">
                        </div>
                    </div>
                    
                    <div class="imagen-slot">
                        <label>Baño</label>
                        <div class="imagen-upload" onclick="seleccionarImagen('bano')">
                            <div class="upload-text">Click para seleccionar<br>imagen de baño</div>
                            <img id="preview-bano" style="display: none;">
                            <button type="button" class="change-btn" onclick="event.stopPropagation(); seleccionarImagen('bano')">Cambiar</button>
                            <input type="file" id="file-bano" accept="image/*" onchange="previewImagen(this, 'bano')">
                        </div>
                    </div>
                    
                    <div class="imagen-slot">
                        <label>Sala de Estar</label>
                        <div class="imagen-upload" onclick="seleccionarImagen('sala')">
                            <div class="upload-text">Click para seleccionar<br>imagen de sala</div>
                            <img id="preview-sala" style="display: none;">
                            <button type="button" class="change-btn" onclick="event.stopPropagation(); seleccionarImagen('sala')">Cambiar</button>
                            <input type="file" id="file-sala" accept="image/*" onchange="previewImagen(this, 'sala')">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-guardar">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div id="modalEliminar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar Eliminación</h3>
            <span class="close" onclick="cerrarModalEliminar()">&times;</span>
        </div>
        
        <p>¿Estás seguro de que deseas eliminar la habitación <strong id="habitacionEliminar"></strong>?</p>
        <p style="color: #ff6b6b;">Esta acción no se puede deshacer.</p>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="eliminar">
            <input type="hidden" name="id" id="eliminarId">
            
            <div class="form-actions">
                <button type="button" class="btn-cancelar" onclick="cerrarModalEliminar()">Cancelar</button>
                <button type="submit" class="btn-eliminar">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Carrusel de Imágenes -->
<div id="carruselModal" class="carrusel-modal">
    <div class="carrusel-content">
        <div class="carrusel-header">
            <h3 id="carruselTitulo">Galería de Habitación</h3>
            <span class="close" onclick="cerrarCarrusel()">&times;</span>
        </div>
        
        <div class="carrusel-imagenes">
            <div class="carrusel-imagen">
                <img id="carrusel-principal" src="" alt="Imagen Principal" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="no-imagen" style="display: none;">No hay imagen principal</div>
                <div class="imagen-label">Imagen Principal</div>
            </div>
            
            <div class="carrusel-imagen">
                <img id="carrusel-cama" src="" alt="Cama" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="no-imagen" style="display: none;">No hay imagen de cama</div>
                <div class="imagen-label">Cama</div>
            </div>
            
            <div class="carrusel-imagen">
                <img id="carrusel-bano" src="" alt="Baño" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="no-imagen" style="display: none;">No hay imagen de baño</div>
                <div class="imagen-label">Baño</div>
            </div>
            
            <div class="carrusel-imagen">
                <img id="carrusel-sala" src="" alt="Sala de Estar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="no-imagen" style="display: none;">No hay imagen de sala</div>
                <div class="imagen-label">Sala de Estar</div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" class="btn-cancelar" onclick="cerrarCarrusel()">Cerrar</button>
        </div>
    </div>
</div>

<script src="../js/habitaciones.js"></script>
</body>
</html>