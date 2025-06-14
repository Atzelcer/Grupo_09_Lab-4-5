<?php 
include 'sesiones/session.php'; 
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard_user.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="css/admin.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@600&family=Rubik&display=swap" rel="stylesheet">
</head>
<body>

  <header class="header-admin">
    <div class="titulo-header">PANEL DE ADMINISTRACIÓN</div>
    <div class="usuario-header">
      <span><?php echo $_SESSION['email']; ?></span>
      <button onclick="location.href='api/logout.php'">Cerrar Sesión</button>
    </div>
  </header>

  <div class="barra-separadora"></div>

  <div class="contenedor-main">
    <div class="menu-lateral">
      <button id="btnGestionUsuarios">Gestión de Usuarios</button>
      <button id="btnRevisarCorreos">Revisar Correos</button>
      <button id="btnEnviarAviso">Enviar Aviso General</button>
    </div>

    <div class="contenido-principal">
      <div id="seccionUsuarios" class="seccion">
        <h3>Gestión de Usuarios</h3>
        <button id="btnAgregarUsuario" class="btn-agregar">Agregar Usuario</button>
        
        <table id="tablaUsuarios">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Nombre</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Fecha Creación</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyUsuarios">
          </tbody>
        </table>
      </div>

      <div id="seccionCorreos" class="seccion oculto">
        <h3>Revisar Correos del Sistema</h3>
        <div class="filtros">
          <select id="filtroUsuario">
            <option value="">Todos los usuarios</option>
          </select>
          <select id="filtroCarpeta">
            <option value="">Todas las carpetas</option>
            <option value="inbox">Bandeja de Entrada</option>
            <option value="sent">Bandeja de Salida</option>
            <option value="drafts">Borradores</option>
          </select>
          <button id="btnFiltrar">Filtrar</button>
        </div>
        
        <table id="tablaCorreosAdmin">
          <thead>
            <tr>
              <th>De</th>
              <th>Para</th>
              <th>Asunto</th>
              <th>Estado</th>
              <th>Carpeta</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyCorreosAdmin">
          </tbody>
        </table>
      </div>

      <div id="seccionAviso" class="seccion oculto">
        <h3>Enviar Aviso General</h3>
        <div class="form-aviso">
          <div class="campo">
            <label for="asuntoAviso">Asunto:</label>
            <input type="text" id="asuntoAviso" placeholder="Asunto del aviso">
          </div>
          <div class="campo">
            <label for="mensajeAviso">Mensaje:</label>
            <textarea id="mensajeAviso" rows="8" placeholder="Contenido del aviso"></textarea>
          </div>
          <div class="campo">
            <label><input type="checkbox" id="soloActivos" checked> Solo usuarios activos</label>
          </div>
          <button id="btnEnviarAvisoGeneral" class="btn-enviar">Enviar Aviso a Todos</button>
        </div>
      </div>
    </div>
  </div>

  <div id="modalUsuario" class="modal">
    <div class="modal-content">
      <span class="close" id="cerrarModalUsuario">&times;</span>
      <h3 id="tituloModalUsuario">Agregar Usuario</h3>
      <form id="formUsuario">
        <input type="hidden" id="usuarioId">
        <div class="campo">
          <label for="usuarioEmail">Email:</label>
          <input type="email" id="usuarioEmail" required>
        </div>
        <div class="campo">
          <label for="usuarioNombre">Nombre:</label>
          <input type="text" id="usuarioNombre" required>
        </div>
        <div class="campo">
          <label for="usuarioPassword">Contraseña:</label>
          <input type="password" id="usuarioPassword">
          <small id="passwordHelp">Dejar vacío para mantener contraseña actual (solo edición)</small>
        </div>
        <div class="campo">
          <label for="usuarioRol">Rol:</label>
          <select id="usuarioRol" required>
            <option value="user">Usuario</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <div class="campo">
          <label for="usuarioEstado">Estado:</label>
          <select id="usuarioEstado" required>
            <option value="active">Activo</option>
            <option value="suspended">Suspendido</option>
          </select>
        </div>
        <div class="botones-modal">
          <button type="submit">Guardar</button>
          <button type="button" id="cancelarUsuario">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <div id="modalCorreo" class="modal">
    <div class="modal-content">
      <span class="close" id="cerrarModalCorreo">&times;</span>
      <div id="contenidoCorreo"></div>
    </div>
  </div>

  <script src="js/admin_panel.js"></script>
</body>
</html>
