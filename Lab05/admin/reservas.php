<?php
require("../php/conexion.php");

// Procesar formularios
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'guardar') {
        $id = $_POST['id'] ?? null;
        $usuario_id = $_POST['usuario_id'];
        $habitacion_id = $_POST['habitacion_id'];
        $fecha_ingreso = $_POST['fecha_ingreso'];
        $fecha_salida = $_POST['fecha_salida'];
        $estado = $_POST['estado'];
        
        // Validar fechas
        if (strtotime($fecha_ingreso) >= strtotime($fecha_salida)) {
            $mensaje = 'La fecha de salida debe ser posterior a la fecha de ingreso';
            $tipo_mensaje = 'error';
        } else {
            if ($id) {
                // Actualizar reserva
                $stmt = $con->prepare("UPDATE reservas SET usuario_id=?, habitacion_id=?, fecha_ingreso=?, fecha_salida=?, estado=? WHERE id=?");
                $stmt->bind_param("iisssi", $usuario_id, $habitacion_id, $fecha_ingreso, $fecha_salida, $estado, $id);
            } else {
                // Crear nueva reserva
                $stmt = $con->prepare("INSERT INTO reservas (usuario_id, habitacion_id, fecha_ingreso, fecha_salida, estado) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $usuario_id, $habitacion_id, $fecha_ingreso, $fecha_salida, $estado);
            }
            
            if ($stmt->execute()) {
                $mensaje = $id ? 'Reserva actualizada correctamente' : 'Reserva creada correctamente';
                $tipo_mensaje = 'exito';
            } else {
                $mensaje = 'Error al guardar reserva';
                $tipo_mensaje = 'error';
            }
        }
    }
    
    if ($action === 'eliminar') {
        $id = $_POST['id'];
        
        $stmt = $con->prepare("DELETE FROM reservas WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $mensaje = 'Reserva eliminada correctamente';
            $tipo_mensaje = 'exito';
        } else {
            $mensaje = 'Error al eliminar reserva';
            $tipo_mensaje = 'error';
        }
    }
}

// Consultar reservas con información de usuarios y habitaciones
$sqlReservas = "SELECT r.id, r.usuario_id, r.habitacion_id, r.fecha_ingreso, r.fecha_salida, r.estado,
                       u.nombre as usuario_nombre, u.correo as usuario_correo,
                       h.numero as habitacion_numero, th.nombre as tipo_habitacion
                FROM reservas r
                INNER JOIN usuarios u ON r.usuario_id = u.id
                INNER JOIN habitaciones h ON r.habitacion_id = h.id
                INNER JOIN tipo_habitacion th ON h.tipo_habitacion_id = th.id
                ORDER BY r.created_at DESC";
$resultadoReservas = $con->query($sqlReservas);

// Obtener usuarios para el formulario
$sqlUsuarios = "SELECT id, nombre, correo FROM usuarios ORDER BY nombre";
$usuarios = $con->query($sqlUsuarios);

// Obtener habitaciones para el formulario
$sqlHabitaciones = "SELECT h.id, h.numero, th.nombre as tipo_nombre
                    FROM habitaciones h
                    INNER JOIN tipo_habitacion th ON h.tipo_habitacion_id = th.id
                    ORDER BY h.numero";
$habitaciones = $con->query($sqlHabitaciones);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservas</title>
    <link rel="stylesheet" href="../css/reservas.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Gestión de Reservas</h1>
        <button class="btn btn-nuevo" onclick="abrirModalReserva()">Nueva Reserva</button>
    </div>

    <?php if ($mensaje): ?>
    <div class="mensaje <?= $tipo_mensaje ?>">
        <?= htmlspecialchars($mensaje) ?>
    </div>
    <?php endif; ?>

    <!-- Tabla de Reservas -->
    <table class="tabla-reservas">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Habitación</th>
                <th>Fecha Ingreso</th>
                <th>Fecha Salida</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($reserva = $resultadoReservas->fetch_assoc()): ?>
            <tr>
                <td><?= $reserva['id'] ?></td>
                <td>
                    <?= htmlspecialchars($reserva['usuario_nombre']) ?>
                    <div class="info-usuario"><?= htmlspecialchars($reserva['usuario_correo']) ?></div>
                </td>
                <td>
                    Habitación <?= htmlspecialchars($reserva['habitacion_numero']) ?>
                    <div class="info-habitacion"><?= htmlspecialchars($reserva['tipo_habitacion']) ?></div>
                </td>
                <td><?= date('d/m/Y', strtotime($reserva['fecha_ingreso'])) ?></td>
                <td><?= date('d/m/Y', strtotime($reserva['fecha_salida'])) ?></td>
                <td>
                    <span class="badge badge-<?= $reserva['estado'] ?>">
                        <?= ucfirst($reserva['estado']) ?>
                    </span>
                </td>
                <td>
                    <div class="acciones">
                        <button class="btn btn-editar" onclick="editarReserva(<?= $reserva['id'] ?>, <?= $reserva['usuario_id'] ?>, <?= $reserva['habitacion_id'] ?>, '<?= $reserva['fecha_ingreso'] ?>', '<?= $reserva['fecha_salida'] ?>', '<?= $reserva['estado'] ?>')">
                            Editar
                        </button>
                        <button class="btn btn-eliminar" onclick="eliminarReserva(<?= $reserva['id'] ?>)">
                            Eliminar
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal para crear/editar reserva -->
<div id="modalReserva" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Nueva Reserva</h3>
            <span class="close" onclick="cerrarModalReserva()">&times;</span>
        </div>
        
        <form method="POST" action="" id="formReserva">
            <input type="hidden" name="action" value="guardar">
            <input type="hidden" name="id" id="reservaId">
            
            <div class="form-group">
                <label for="usuario_id">Usuario</label>
                <select id="usuario_id" name="usuario_id" required>
                    <option value="">Seleccionar usuario</option>
                    <?php 
                    $usuarios->data_seek(0);
                    while($usuario = $usuarios->fetch_assoc()): 
                    ?>
                        <option value="<?= $usuario['id'] ?>">
                            <?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['correo']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="habitacion_id">Habitación</label>
                <select id="habitacion_id" name="habitacion_id" required>
                    <option value="">Seleccionar habitación</option>
                    <?php 
                    $habitaciones->data_seek(0);
                    while($habitacion = $habitaciones->fetch_assoc()): 
                    ?>
                        <option value="<?= $habitacion['id'] ?>">
                            Habitación <?= htmlspecialchars($habitacion['numero']) ?> - <?= htmlspecialchars($habitacion['tipo_nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fecha_ingreso">Fecha de Ingreso</label>
                <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>
            </div>
            
            <div class="form-group">
                <label for="fecha_salida">Fecha de Salida</label>
                <input type="date" id="fecha_salida" name="fecha_salida" required>
            </div>
            
            <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="confirmada">Confirmada</option>
                    <option value="cancelada">Cancelada</option>
                    <option value="completada">Completada</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-cancelar" onclick="cerrarModalReserva()">Cancelar</button>
                <button type="submit" class="btn btn-guardar">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div id="modalEliminar" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar Eliminación</h3>
            <span class="close" onclick="cerrarModalEliminar()">&times;</span>
        </div>
        
        <p>¿Estás seguro de que deseas eliminar esta reserva?</p>
        <p style="color: #ff6b6b;">Esta acción no se puede deshacer.</p>
        
        <form method="POST" action="" id="formEliminar">
            <input type="hidden" name="action" value="eliminar">
            <input type="hidden" name="id" id="eliminarId">
            
            <div class="form-actions">
                <button type="button" class="btn btn-cancelar" onclick="cerrarModalEliminar()">Cancelar</button>
                <button type="submit" class="btn btn-eliminar">Eliminar</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>