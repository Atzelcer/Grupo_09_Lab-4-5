<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sistema SPA - Hotel KUCHIUYAS</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            min-height: 100vh;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: rgba(255,255,255,0.95);
            color: #333;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .feature-test {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1rem 0;
            border-left: 5px solid #00b894;
        }
        .feature-test h3 {
            color: #2d3436;
            margin-bottom: 1rem;
        }
        .status-check {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            margin: 0.2rem;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .check-pass {
            background: #00b894;
            color: white;
        }
        .check-ajax {
            background: #0984e3;
            color: white;
        }
        .check-dynamic {
            background: #6c5ce7;
            color: white;
        }
        .test-btn {
            background: #fd79a8;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 5px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        h1 { 
            text-align: center; 
            color: #2d3436; 
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .highlight {
            background: #fdcb6e;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
            text-align: center;
        }
        .highlight h2 {
            color: #2d3436;
            margin-bottom: 1rem;
        }
        .emoji { font-size: 2rem; margin: 0 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Sistema SPA - Single Page Application</h1>
        
        <div class="highlight">
            <h2><span class="emoji">⚡</span>Sistema SIN RECARGA DE PÁGINA<span class="emoji">⚡</span></h2>
            <p><strong>Todo funciona dinámicamente usando AJAX/Fetch</strong></p>
        </div>

        <div class="feature-test">
            <h3>🔐 Sistema de Login Mejorado</h3>
            <p>Después del login, la página se transforma dinámicamente sin recargar:</p>
            <span class="status-check check-pass">✅ Login AJAX</span>
            <span class="status-check check-dynamic">🔄 Transformación Dinámica</span>
            <span class="status-check check-ajax">📡 Sin Recarga</span>
        </div>

        <div class="feature-test">
            <h3>🏠 Panel de Usuario Dinámico</h3>
            <p>El hero section y la página se transforman automáticamente mostrando:</p>
            <ul>
                <li><span class="status-check check-dynamic">🎯 Panel de Control</span></li>
                <li><span class="status-check check-dynamic">📋 Nueva Reserva</span></li>
                <li><span class="status-check check-dynamic">📋 Mis Reservas</span></li>
                <li><span class="status-check check-ajax">⚡ Todo via AJAX</span></li>
            </ul>
        </div>

        <div class="feature-test">
            <h3>🔍 Búsqueda de Habitaciones (AJAX)</h3>
            <p>Sistema de búsqueda completamente dinámico:</p>
            <span class="status-check check-ajax">📡 Fetch API</span>
            <span class="status-check check-dynamic">🔄 Carga Dinámica</span>
            <span class="status-check check-pass">✅ Filtros en Tiempo Real</span>
            <span class="status-check check-ajax">🖼️ Imágenes Dinámicas</span>
        </div>

        <div class="feature-test">
            <h3>🛏️ Sistema de Reservas (Sin Recarga)</h3>
            <p>Proceso completo de reserva sin recargar página:</p>
            <span class="status-check check-ajax">📤 Envío AJAX</span>
            <span class="status-check check-dynamic">💫 Confirmación Dinámica</span>
            <span class="status-check check-pass">✅ Actualización en Tiempo Real</span>
        </div>

        <div class="feature-test">
            <h3>📋 Historial de Reservas (Dinámico)</h3>
            <p>Gestión completa de reservas sin recargar:</p>
            <span class="status-check check-ajax">📡 Carga AJAX</span>
            <span class="status-check check-dynamic">🗑️ Cancelación Dinámica</span>
            <span class="status-check check-pass">✅ Estados en Tiempo Real</span>
        </div>

        <div class="feature-test">
            <h3>🎨 Interfaz SPA Moderna</h3>
            <p>Experiencia de usuario mejorada:</p>
            <span class="status-check check-dynamic">🔄 Transiciones Suaves</span>
            <span class="status-check check-pass">⚡ Carga Instantánea</span>
            <span class="status-check check-ajax">📱 Responsive</span>
            <span class="status-check check-dynamic">🎯 Estados de Carga</span>
        </div>

        <div style="text-align: center; margin: 3rem 0;">
            <h2>🧪 Prueba el Sistema</h2>
            <a href="../index.html" class="test-btn">🏠 Ir a Página Principal</a>
            <a href="test-sistema.php" class="test-btn">🔧 Pruebas Técnicas</a>
        </div>

        <div style="background: #00b894; color: white; padding: 2rem; border-radius: 10px; text-align: center; margin-top: 2rem;">
            <h2>🎯 Instrucciones de Prueba SPA</h2>
            <ol style="text-align: left; max-width: 600px; margin: 0 auto;">
                <li><strong>Ve a:</strong> <a href="../index.html" style="color: #fdcb6e;">index.html</a></li>
                <li><strong>Haz login con:</strong> usuario@email.com / hello</li>
                <li><strong>Observa:</strong> La página se transforma SIN recargar</li>
                <li><strong>Prueba "Nueva Reserva":</strong> Todo carga dinámicamente</li>
                <li><strong>Prueba "Mis Reservas":</strong> Se cargan via AJAX</li>
                <li><strong>Haz una reserva:</strong> Todo el proceso sin recarga</li>
            </ol>
            <p style="margin-top: 1.5rem; font-size: 1.2rem;">
                <strong>🚀 ¡Experiencia SPA Completa!</strong>
            </p>
        </div>

        <div style="background: #e17055; color: white; padding: 1.5rem; border-radius: 10px; text-align: center; margin-top: 2rem;">
            <h3>⚠️ Importante: Diferencia con Administradores</h3>
            <p><strong>Usuarios normales:</strong> Interfaz SPA sin recarga</p>
            <p><strong>Administradores:</strong> Redirección al dashboard (como estaba antes)</p>
        </div>
    </div>
</body>
</html>
