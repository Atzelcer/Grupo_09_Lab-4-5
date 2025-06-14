<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Login Administrador - Hotel KUCHIUYAS</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255,255,255,0.95);
            color: #333;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .status { 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 5px; 
            border-left: 4px solid;
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            border-color: #28a745;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            border-color: #dc3545;
        }
        .info { 
            background: #d1ecf1; 
            color: #0c5460; 
            border-color: #17a2b8;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffc107;
        }
        .test-section { 
            margin-bottom: 30px; 
            border: 1px solid #ddd; 
            padding: 15px; 
            border-radius: 8px; 
            background: #f8f9fa;
        }
        .credentials {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 2rem; }
        h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Prueba de Login de Administrador</h1>
        
        <div class="test-section">
            <h2>📋 Credenciales de Administrador</h2>
            <div class="info status">
                <strong>⚠️ Importante:</strong> Usa estas credenciales para probar el login de administrador
            </div>
            
            <div class="credentials">
                <strong>Email:</strong> admin@hotel.com<br>
                <strong>Contraseña:</strong> admin123<br>
                <strong>Rol:</strong> admin
            </div>
            
            <div class="warning status">
                <strong>Nota:</strong> Al hacer login como administrador, serás redirigido automáticamente al dashboard de administración.
            </div>
        </div>

        <div class="test-section">
            <h2>🧪 Verificación del Sistema</h2>
            <?php
            require_once '../php/conexion.php';
            
            echo "<div class='status info'><strong>🔍 Verificando usuario administrador en la base de datos...</strong></div>";
            
            try {
                // Verificar usuario admin
                $query = "SELECT id, correo, nombre, rol FROM usuarios WHERE rol = 'admin'";
                $result = mysqli_query($con, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($admin = mysqli_fetch_assoc($result)) {
                        echo "<div class='status success'>";
                        echo "✅ <strong>Usuario admin encontrado:</strong><br>";
                        echo "ID: {$admin['id']}<br>";
                        echo "Email: {$admin['correo']}<br>";
                        echo "Nombre: {$admin['nombre']}<br>";
                        echo "Rol: {$admin['rol']}";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='status error'>❌ No se encontraron usuarios administrador</div>";
                }
                
                // Verificar hash de contraseña
                $test_password = "admin123";
                $hash = md5($test_password);
                $query_hash = "SELECT correo FROM usuarios WHERE password = ? AND rol = 'admin'";
                $stmt = mysqli_prepare($con, $query_hash);
                mysqli_stmt_bind_param($stmt, "s", $hash);
                mysqli_stmt_execute($stmt);
                $result_hash = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result_hash) > 0) {
                    echo "<div class='status success'>✅ <strong>Hash de contraseña verificado:</strong> La contraseña 'admin123' es correcta</div>";
                } else {
                    echo "<div class='status error'>❌ Hash de contraseña no coincide</div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='status error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
            }
            ?>
        </div>

        <div class="test-section">
            <h2>🔗 Enlaces de Prueba</h2>
            <a href="../index.html" class="btn">🏠 Ir a Página Principal</a>
            <a href="../admin/dashboard.php" class="btn btn-success">🚀 Ir Directamente al Dashboard</a>
            <a href="test-sistema.php" class="btn">🧪 Pruebas del Sistema</a>
        </div>

        <div class="test-section">
            <h2>📝 Instrucciones de Prueba</h2>
            <ol>
                <li><strong>Ve a la página principal:</strong> <a href="../index.html">index.html</a></li>
                <li><strong>Haz clic en "Acceder"</strong> para abrir el modal de login</li>
                <li><strong>Ingresa las credenciales de administrador:</strong>
                    <div class="credentials">
                        Email: admin@hotel.com<br>
                        Contraseña: admin123
                    </div>
                </li>
                <li><strong>Haz clic en "Entrar"</strong></li>
                <li><strong>Deberías ser redirigido automáticamente</strong> al dashboard de administración</li>
            </ol>
            
            <div class="success status">
                <strong>✅ Si todo funciona correctamente:</strong><br>
                • El login te llevará automáticamente al dashboard<br>
                • Verás el panel de administración con opciones para gestionar usuarios, habitaciones y reservas<br>
                • Podrás acceder a todas las funciones administrativas
            </div>
        </div>

        <div class="test-section">
            <h2>🔄 Usuarios de Prueba Disponibles</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                <div class="credentials">
                    <strong>👨‍💼 ADMINISTRADOR</strong><br>
                    Email: admin@hotel.com<br>
                    Contraseña: admin123<br>
                    <em>→ Redirige al dashboard admin</em>
                </div>
                
                <div class="credentials">
                    <strong>👤 USUARIO NORMAL</strong><br>
                    Email: usuario@email.com<br>
                    Contraseña: hello<br>
                    <em>→ Redirige al panel de reservas</em>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
