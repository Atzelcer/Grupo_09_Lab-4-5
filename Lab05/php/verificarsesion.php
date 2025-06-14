<?php
// Inicia la sesión si no ha sido iniciada aún
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica si el usuario tiene una sesión activa
if (!isset($_SESSION["correo"]) || !isset($_SESSION["usuario_id"])) {
    echo "Acceso no autorizado. Redirigiendo al inicio...";
    ?>
    <script>
        alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        window.location.href = '../index.html';
    </script>
    <?php
    exit;
}

// Verificación adicional de que los datos de sesión estén completos
if (empty($_SESSION["nombre"]) || empty($_SESSION["rol"])) {
    echo "Datos de sesión incompletos. Por favor, inicia sesión nuevamente.";
    session_destroy();
    ?>
    <script>
        alert('Error en los datos de sesión. Por favor, inicia sesión nuevamente.');
        window.location.href = '../index.html';
    </script>
    <?php
    exit;
}
?>
