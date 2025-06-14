<?php include("../php/modales-usuarios.html");
session_start();
require("../php/verificarsesion.php");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.html");
    exit;
}

$nombre = $_SESSION['nombre'];
$correo = $_SESSION['correo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel del Administrador</title>
    <link rel="stylesheet" href="../css/admin2.css" />
</head>
<body>

    <!-- Barra superior -->
    <header class="barra-superior">
        <div class="titulo"></div>

        <div class="buscador-global">
            <input type="text" id="input-busqueda" placeholder="Buscar usuarios, reservas, habitaciones...">
        </div>

        <div class="usuario">
            <?php echo htmlspecialchars($nombre); ?> |
            <a href="../php/logout.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- Contenedor principal -->
    <div class="panel-container">
        <!-- Menú lateral -->
        <div class="menu-lateral">
            <div class="boton-menu activo" id="btn-principal">Principal</div>
            <div class="boton-menu" id="btn-usuarios">Usuarios</div>
            <div class="boton-menu" id="btn-habitaciones">Habitaciones</div>
            <div class="boton-menu" id="btn-reservas">Reservas</div>
        </div>

        <!-- Contenido dinámico -->
        <main id="contenido" class="contenido">
            <h1>Bienvenido al panel del administrador</h1>
            <p>Selecciona una opción del menú lateral para comenzar</p>
        </main>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
