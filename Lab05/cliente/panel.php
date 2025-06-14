<?php
session_start();
require_once '../php/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'usuario') {
    header('Location: ../index.html');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$section = isset($_GET['section']) ? $_GET['section'] : 'reservas';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Hotel KUCHIUYAS</title>
    <link rel="stylesheet" href="../css/cliente.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <h2>KUCHIUYAS</h2>
                <span>Hotel & Resort</span>
            </div>
            <div class="nav-buttons">
                <div class="user-menu">
                    <span class="user-welcome">¡Hola, <?php echo htmlspecialchars($user_name); ?>!</span>
                    <div class="user-dropdown">
                        <a href="panel.php?section=nueva-reserva" class="<?php echo $section === 'nueva-reserva' ? 'active' : ''; ?>">
                            <i class="fas fa-plus-circle"></i> Nueva Reserva
                        </a>
                        <a href="panel.php?section=reservas" class="<?php echo $section === 'reservas' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> Mis Reservas
                        </a>
                        <a href="../php/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php if ($section === 'nueva-reserva'): ?>
                <!-- Nueva Reserva Section -->
                <section class="reserva-section">
                    <h1><i class="fas fa-plus-circle"></i> Nueva Reserva</h1>
                    <p>Selecciona las fechas de tu estadía y encuentra la habitación perfecta para ti</p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>¡Bienvenido <?php echo htmlspecialchars($user_name); ?>!</strong> 
                        Encuentra entre nuestras habitaciones disponibles la que mejor se adapte a tus necesidades.
                    </div>
                    
                    <!-- Filtros -->
                    <div class="filtros">
                        <div class="filtro-item">
                            <label for="fecha_ingreso">Fecha de Ingreso:</label>
                            <input type="date" id="fecha_ingreso" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="filtro-item">
                            <label for="fecha_salida">Fecha de Salida:</label>
                            <input type="date" id="fecha_salida" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                        <div class="filtro-item">
                            <label for="tipo_habitacion">Tipo de Habitación:</label>
                            <select id="tipo_habitacion">
                                <option value="">Todos los tipos</option>
                                <?php
                                $tipos_query = "SELECT * FROM tipo_habitacion ORDER BY precio_por_noche ASC";
                                $tipos_result = mysqli_query($con, $tipos_query);
                                while ($tipo = mysqli_fetch_assoc($tipos_result)):
                                ?>
                                    <option value="<?php echo $tipo['id']; ?>">
                                        <?php echo $tipo['nombre']; ?> - $<?php echo number_format($tipo['precio_por_noche'], 2); ?>/noche
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button class="btn-buscar" onclick="buscarHabitaciones()">
                            <i class="fas fa-search"></i> Buscar Habitaciones
                        </button>
                    </div>

                    <!-- Habitaciones Disponibles -->
                    <div id="habitaciones-container" class="habitaciones-grid">
                        <!-- Las habitaciones se cargarán aquí via AJAX -->
                    </div>
                </section>

            <?php else: ?>
                <!-- Mis Reservas Section -->
                <section class="reservas-section">
                    <h1><i class="fas fa-list"></i> Mis Reservas</h1>
                    <p>Gestiona todas tus reservas desde aquí</p>
                    
                    <div class="reservas-container">
                        <?php
                        // Obtener reservas del usuario
                        $query = "SELECT r.*, h.numero as habitacion_numero, th.nombre as tipo_nombre, th.precio_por_noche,
                                         f.fotografia as foto_principal
                                  FROM reservas r 
                                  JOIN habitaciones h ON r.habitacion_id = h.id 
                                  JOIN tipo_habitacion th ON h.tipo_habitacion_id = th.id
                                  LEFT JOIN fotografias f ON h.id = f.habitacion_id AND f.orden = 1
                                  WHERE r.usuario_id = ? 
                                  ORDER BY r.created_at DESC";
                        
                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_bind_param($stmt, "i", $user_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        
                        if (mysqli_num_rows($result) > 0):
                            while ($reserva = mysqli_fetch_assoc($result)):
                        ?>
                            <div class="reserva-card">
                                <div class="reserva-imagen">
                                    <?php if ($reserva['foto_principal']): ?>
                                        <img src="../img/HABITACION<?php echo $reserva['habitacion_id']; ?>/<?php echo $reserva['foto_principal']; ?>" 
                                             alt="Habitación <?php echo $reserva['habitacion_numero']; ?>"
                                             onerror="this.onerror=null; this.src='../img/no-image.svg';">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-bed"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="reserva-info">
                                    <h3>Habitación <?php echo $reserva['habitacion_numero']; ?> - <?php echo $reserva['tipo_nombre']; ?></h3>
                                    <div class="reserva-detalles">
                                        <p><i class="fas fa-calendar-check"></i> Check-in: <?php echo date('d/m/Y', strtotime($reserva['fecha_ingreso'])); ?></p>
                                        <p><i class="fas fa-calendar-times"></i> Check-out: <?php echo date('d/m/Y', strtotime($reserva['fecha_salida'])); ?></p>
                                        <p><i class="fas fa-dollar-sign"></i> Total: $<?php echo number_format($reserva['precio_total'], 2); ?></p>
                                    </div>
                                    <div class="reserva-estado">
                                        <span class="estado-badge estado-<?php echo $reserva['estado']; ?>">
                                            <?php echo ucfirst($reserva['estado']); ?>
                                        </span>
                                    </div>
                                    <?php if ($reserva['observaciones']): ?>
                                        <div class="reserva-observaciones">
                                            <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($reserva['observaciones']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="reserva-acciones">
                                    <?php if ($reserva['estado'] === 'pendiente'): ?>
                                        <button class="btn-cancelar" onclick="cancelarReserva(<?php echo $reserva['id']; ?>)">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-detalles" onclick="verDetalles(<?php echo $reserva['id']; ?>)">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </button>
                                    <!-- Nuevo botón para ver galería de la habitación -->
                                    <button class="btn-galeria-reserva" onclick="abrirCarruselCliente(<?php echo $reserva['habitacion_id']; ?>)">
                                        <i class="fas fa-images"></i> Ver Fotos
                                    </button>
                                </div>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <div class="no-reservas">
                                <i class="fas fa-calendar-times"></i>
                                <h3>No tienes reservas aún</h3>
                                <p>¡Haz tu primera reserva y disfruta de nuestro hotel!</p>
                                <a href="panel.php?section=nueva-reserva" class="btn-primary">
                                    <i class="fas fa-plus-circle"></i> Hacer Reserva
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal para detalles de reserva -->
    <div id="modalDetalles" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModalDetalles()">&times;</span>
            <div id="detalles-content">
                <!-- Contenido se carga via AJAX -->
            </div>
        </div>
    </div>

    <!-- Modal para confirmar reserva -->
    <div id="modalConfirmar" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModalConfirmar()">&times;</span>
            <div class="modal-header">
                <h2>Confirmar Reserva</h2>
            </div>
            <form id="formReserva" onsubmit="confirmarReserva(event)">
                <div id="resumen-reserva">
                    <!-- Se llena via JavaScript -->
                </div>
                <div class="form-group">
                    <label for="observaciones">Observaciones (opcional):</label>
                    <textarea id="observaciones" name="observaciones" rows="3" placeholder="Peticiones especiales, comentarios..."></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-secondary" onclick="cerrarModalConfirmar()">Cancelar</button>
                    <button type="submit" class="btn-primary">Confirmar Reserva</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Carrusel de Imágenes para Cliente -->
    <div id="carruselClienteModal" class="carrusel-cliente-modal">
        <div class="carrusel-cliente-content">
            <div class="carrusel-cliente-header">
                <h3 id="carruselClienteTitulo">Galería de Habitación</h3>
                <span class="cerrar-carrusel-cliente" onclick="cerrarCarruselCliente()">&times;</span>
            </div>
            
            <div id="carruselClienteImagenes" class="carrusel-cliente-imagenes">
                <!-- Las imágenes se cargarán aquí via JavaScript -->
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="cerrarCarruselCliente()">Cerrar</button>
            </div>
        </div>
    </div>

    <script src="../js/cliente.js"></script>
</body>
</html>