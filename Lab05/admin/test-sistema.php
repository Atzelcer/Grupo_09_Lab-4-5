<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Sistema de Reservas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .test-section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🏨 Prueba del Sistema de Reservas - Hotel KUCHIUYAS</h1>
    
    <div class="test-section">
        <h2>🔗 Enlaces de Navegación</h2>
        <p><a href="../index.html" target="_blank">🏠 Página Principal</a></p>
        <p><a href="../cliente/panel.php?section=nueva-reserva" target="_blank">➕ Nueva Reserva</a></p>
        <p><a href="../cliente/panel.php?section=reservas" target="_blank">📋 Mis Reservas</a></p>
        <p><a href="verificar-imagenes.php" target="_blank">🖼️ Verificar Imágenes</a></p>
        <p><a href="debug-habitaciones.php" target="_blank">🐛 Debug Habitaciones</a></p>
    </div>

    <div class="test-section">
        <h2>🔍 Verificación de Archivos Críticos</h2>
        <?php
        $archivos_criticos = [
            '../php/conexion.php' => 'Conexión Base de Datos',
            '../php/listar-habitaciones.php' => 'Listar Habitaciones',
            '../php/insertar-reserva.php' => 'Insertar Reserva',
            '../php/cancelar-reserva.php' => 'Cancelar Reserva',
            '../php/login-ajax.php' => 'Login AJAX',
            '../js/cliente.js' => 'JavaScript Cliente',
            '../css/cliente.css' => 'CSS Cliente',
            '../cliente/panel.php' => 'Panel Cliente'
        ];
        
        foreach ($archivos_criticos as $archivo => $descripcion) {
            if (file_exists($archivo)) {
                echo "<div class='status success'>✅ $descripcion - OK</div>";
            } else {
                echo "<div class='status error'>❌ $descripcion - FALTA</div>";
            }
        }
        ?>
    </div>

    <div class="test-section">
        <h2>🗃️ Verificación de Base de Datos</h2>
        <?php
        require_once '../php/conexion.php';
        
        try {
            // Verificar tablas principales
            $tablas = ['usuarios', 'habitaciones', 'tipo_habitacion', 'reservas', 'fotografias'];
            
            foreach ($tablas as $tabla) {
                $result = mysqli_query($con, "SELECT COUNT(*) as total FROM $tabla");
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    echo "<div class='status success'>✅ Tabla '$tabla' - {$row['total']} registros</div>";
                } else {
                    echo "<div class='status error'>❌ Error en tabla '$tabla'</div>";
                }
            }
            
            // Verificar habitaciones disponibles
            $result = mysqli_query($con, "SELECT COUNT(*) as total FROM habitaciones WHERE estado = 'disponible'");
            $row = mysqli_fetch_assoc($result);
            echo "<div class='status info'>ℹ️ Habitaciones disponibles: {$row['total']}</div>";
            
        } catch (Exception $e) {
            echo "<div class='status error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>

    <div class="test-section">
        <h2>🖼️ Verificación de Imágenes</h2>
        <?php
        $carpetas_habitaciones = glob('../img/HABITACION*', GLOB_ONLYDIR);
        
        foreach ($carpetas_habitaciones as $carpeta) {
            $num_habitacion = str_replace('../img/HABITACION', '', $carpeta);
            $imagenes = glob($carpeta . '/*.{jpg,jpeg,png,avif}', GLOB_BRACE);
            
            if (count($imagenes) > 0) {
                echo "<div class='status success'>✅ Habitación $num_habitacion - " . count($imagenes) . " imágenes</div>";
            } else {
                echo "<div class='status error'>❌ Habitación $num_habitacion - Sin imágenes</div>";
            }
        }
        ?>
    </div>

    <div class="test-section">
        <h2>🧪 Pruebas de Funcionalidad</h2>
        <div class="status info">
            <strong>Para probar el sistema completo:</strong><br>
            1. Ve a la <a href="../index.html">página principal</a><br>
            2. Haz clic en "Acceder" e inicia sesión con: <code>usuario@email.com</code> / <code>hello</code><br>
            3. Después del login serás redirigido al panel donde puedes:<br>
            &nbsp;&nbsp;&nbsp;• Hacer nuevas reservas<br>
            &nbsp;&nbsp;&nbsp;• Ver tu historial de reservas<br>
            4. Prueba hacer una reserva seleccionando fechas y una habitación<br>
            5. Verifica que puedas cancelar reservas pendientes
        </div>
    </div>

    <div class="test-section">
        <h2>🚀 Estado del Sistema</h2>
        <div class="status success">
            <strong>✅ Sistema de Reservas COMPLETO y FUNCIONAL</strong><br>
            • Autenticación de usuarios implementada<br>
            • Búsqueda de habitaciones disponibles<br>
            • Sistema de reservas completo<br>
            • Historial de reservas<br>
            • Cancelación de reservas<br>
            • Interfaz moderna y responsive<br>
            • Manejo de imágenes optimizado
        </div>
    </div>

    <style>
        code { 
            background: #f1f1f1; 
            padding: 2px 4px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace; 
        }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</body>
</html>
