<?php include 'sesiones/session.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Usuario</title>
  <link rel="stylesheet" href="css/users.css">
</head>
<body>

  <div class="header-user">
    <h2 class="titulo-header">Panel del Usuario</h2>
    <div class="usuario-header">
      <span><?php echo $_SESSION['email']; ?></span>
      <button onclick="location.href='api/logout.php'">Cerrar Sesión</button>
    </div>
  </div>

  <div class="barra-separadora"></div>

  <div class="contenedor-main">
    <div class="menu-lateral">
      <button id="btnRedactar" onclick="Redactar()">Redactar</button>
      <button id="btnEntrada">Bandeja de Entrada</button>
      <button id="btnSalida">Bandeja de Salida</button>
      <button id="btnBorrador">Borradores</button>
    </div>

    <div class="tabla-container">
      <table id="tablaCorreos">
        <thead>
          <tr>
            <th>Correo</th>
            <th>Asunto</th>
            <th>Estado</th>
            <th>Operación</th>
          </tr>
        </thead>
        <tbody id="tbodyCorreos">
        </tbody>
      </table>
    </div>
  </div>

  <div id="modalCorreo" class="modal">
    <div class="modal-content">
      <span class="close" id="cerrarModal">&times;</span>
      <div id="contenidoCorreo"></div>
    </div>
  </div>

  <script src="js/bandeja_usuario.js"></script>
</body>
</html>
