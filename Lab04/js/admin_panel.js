document.addEventListener("DOMContentLoaded", () => {
  cargarUsuarios();
  cargarUsuariosEnSelect();

  document.getElementById("btnGestionUsuarios").addEventListener("click", () => mostrarSeccion("seccionUsuarios"));
  document.getElementById("btnRevisarCorreos").addEventListener("click", () => {
    mostrarSeccion("seccionCorreos");
    cargarCorreosAdmin();
  });
  document.getElementById("btnEnviarAviso").addEventListener("click", () => mostrarSeccion("seccionAviso"));

  document.getElementById("cerrarModalUsuario").addEventListener("click", () => cerrarModal("modalUsuario"));
  document.getElementById("cerrarModalCorreo").addEventListener("click", () => cerrarModal("modalCorreo"));
  document.getElementById("cancelarUsuario").addEventListener("click", () => cerrarModal("modalUsuario"));

  document.getElementById("btnAgregarUsuario").addEventListener("click", () => abrirModalUsuario());
  document.getElementById("formUsuario").addEventListener("submit", guardarUsuario);
  document.getElementById("btnEnviarAvisoGeneral").addEventListener("click", enviarAvisoGeneral);
  document.getElementById("btnFiltrar").addEventListener("click", cargarCorreosAdmin);


  mostrarSeccion("seccionUsuarios");
});

function mostrarSeccion(seccionId) {
  document.querySelectorAll('.seccion').forEach(seccion => {
    seccion.classList.add('oculto');
  });
  
  document.getElementById(seccionId).classList.remove('oculto');
  
  document.querySelectorAll('.menu-lateral button').forEach(btn => {
    btn.classList.remove('activo');
  });
  
  if (seccionId === 'seccionUsuarios') {
    document.getElementById('btnGestionUsuarios').classList.add('activo');
  } else if (seccionId === 'seccionCorreos') {
    document.getElementById('btnRevisarCorreos').classList.add('activo');
  } else if (seccionId === 'seccionAviso') {
    document.getElementById('btnEnviarAviso').classList.add('activo');
  }
}

function cargarUsuarios() {
  fetch('api/admin_usuarios.php')
    .then(response => response.json())
    .then(usuarios => {
      const tbody = document.getElementById('tbodyUsuarios');
      tbody.innerHTML = '';
      
      usuarios.forEach(usuario => {
        const fila = document.createElement('tr');
        const estadoClass = usuario.status === 'active' ? 'estado-activo' : 'estado-suspendido';
        
        fila.innerHTML = `
          <td>${usuario.id}</td>
          <td>${usuario.email}</td>
          <td>${usuario.name}</td>
          <td>${usuario.role}</td>
          <td class="${estadoClass}">${usuario.status === 'active' ? 'Activo' : 'Suspendido'}</td>
          <td>${formatearFecha(usuario.created_at)}</td>
          <td>
            <button onclick="editarUsuario(${usuario.id})">Editar</button>
            ${usuario.status === 'active' ? 
              `<button class="btn-suspender" onclick="cambiarEstadoUsuario(${usuario.id}, 'suspended')">Suspender</button>` :
              `<button class="btn-activar" onclick="cambiarEstadoUsuario(${usuario.id}, 'active')">Activar</button>`
            }
            <button class="btn-eliminar" onclick="eliminarUsuario(${usuario.id})">Eliminar</button>
          </td>
        `;
        tbody.appendChild(fila);
      });
    })
    .catch(error => console.error('Error al cargar usuarios:', error));
}

function cargarUsuariosEnSelect() {
  fetch('api/admin_usuarios.php')
    .then(response => response.json())
    .then(usuarios => {
      const select = document.getElementById('filtroUsuario');
      select.innerHTML = '<option value="">Todos los usuarios</option>';
      
      usuarios.forEach(usuario => {
        const option = document.createElement('option');
        option.value = usuario.id;
        option.textContent = `${usuario.name} (${usuario.email})`;
        select.appendChild(option);
      });
    })
    .catch(error => console.error('Error al cargar usuarios en select:', error));
}

function cargarCorreosAdmin() {
  const usuarioId = document.getElementById('filtroUsuario').value;
  const carpeta = document.getElementById('filtroCarpeta').value;
  
  let url = 'api/admin_correos.php?';
  if (usuarioId) url += `usuario_id=${usuarioId}&`;
  if (carpeta) url += `carpeta=${carpeta}&`;
  
  fetch(url)
    .then(response => response.json())
    .then(correos => {
      const tbody = document.getElementById('tbodyCorreosAdmin');
      tbody.innerHTML = '';
      
      correos.forEach(correo => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
          <td>${correo.from_email}</td>
          <td>${correo.to_email}</td>
          <td>${correo.subject}</td>
          <td>${correo.status}</td>
          <td>${correo.folder}</td>
          <td>${formatearFecha(correo.created_at)}</td>
          <td>
            <button onclick="verCorreoAdmin('${correo.message.replace(/"/g, '&quot;')}', '${correo.subject}')">Ver</button>
            <button class="btn-eliminar" onclick="eliminarCorreoAdmin(${correo.id})">Eliminar</button>
          </td>
        `;
        tbody.appendChild(fila);
      });
    })
    .catch(error => console.error('Error al cargar correos:', error));
}

function abrirModalUsuario(usuario = null) {
  document.getElementById('tituloModalUsuario').textContent = usuario ? 'Editar Usuario' : 'Agregar Usuario';
  document.getElementById('passwordHelp').style.display = usuario ? 'block' : 'none';
  
  if (usuario) {
    document.getElementById('usuarioId').value = usuario.id;
    document.getElementById('usuarioEmail').value = usuario.email;
    document.getElementById('usuarioNombre').value = usuario.name;
    document.getElementById('usuarioPassword').value = '';
    document.getElementById('usuarioRol').value = usuario.role;
    document.getElementById('usuarioEstado').value = usuario.status;
    document.getElementById('usuarioPassword').required = false;
  } else {
    document.getElementById('formUsuario').reset();
    document.getElementById('usuarioPassword').required = true;
  }
  
  document.getElementById('modalUsuario').style.display = 'block';
}

function editarUsuario(id) {
  fetch(`api/admin_usuarios.php?id=${id}`)
    .then(response => response.json())
    .then(usuario => {
      abrirModalUsuario(usuario);
    })
    .catch(error => console.error('Error al obtener usuario:', error));
}

function guardarUsuario(e) {
  e.preventDefault();
  
  const id = document.getElementById('usuarioId').value;
  const email = document.getElementById('usuarioEmail').value;
  const name = document.getElementById('usuarioNombre').value;
  const password = document.getElementById('usuarioPassword').value;
  const role = document.getElementById('usuarioRol').value;
  const status = document.getElementById('usuarioEstado').value;
  
  const data = { email, name, role, status };
  if (password) data.password = password;
  if (id) data.id = id;
  
  const method = id ? 'PUT' : 'POST';
  
  fetch('api/admin_usuarios.php', {
    method: method,
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(id ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente');
      cerrarModal('modalUsuario');
      cargarUsuarios();
      cargarUsuariosEnSelect();
    } else {
      alert('Error: ' + result.error);
    }
  })
  .catch(error => {
    console.error('Error al guardar usuario:', error);
    alert('Error al guardar usuario');
  });
}

function cambiarEstadoUsuario(id, nuevoEstado) {
  const accion = nuevoEstado === 'active' ? 'activar' : 'suspender';
  if (!confirm(`¿Está seguro de que desea ${accion} este usuario?`)) return;
  
  fetch('api/admin_usuarios.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id, status: nuevoEstado })
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(`Usuario ${accion === 'activar' ? 'activado' : 'suspendido'} correctamente`);
      cargarUsuarios();
    } else {
      alert('Error: ' + result.error);
    }
  })
  .catch(error => {
    console.error('Error al cambiar estado:', error);
    alert('Error al cambiar estado del usuario');
  });
}

function eliminarUsuario(id) {
  if (!confirm('¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.')) return;
  
  fetch('api/admin_usuarios.php', {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id })
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert('Usuario eliminado correctamente');
      cargarUsuarios();
      cargarUsuariosEnSelect();
    } else {
      alert('Error: ' + result.error);
    }
  })
  .catch(error => {
    console.error('Error al eliminar usuario:', error);
    alert('Error al eliminar usuario');
  });
}

function verCorreoAdmin(mensaje, asunto) {
  document.getElementById('contenidoCorreo').innerHTML = `
    <h3>Asunto: ${asunto}</h3>
    <hr>
    <p>${mensaje}</p>
  `;
  document.getElementById('modalCorreo').style.display = 'block';
}

function eliminarCorreoAdmin(id) {
  if (!confirm('¿Está seguro de que desea eliminar este correo?')) return;
  
  fetch('api/admin_correos.php', {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id })
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert('Correo eliminado correctamente');
      cargarCorreosAdmin();
    } else {
      alert('Error: ' + result.error);
    }
  })
  .catch(error => {
    console.error('Error al eliminar correo:', error);
    alert('Error al eliminar correo');
  });
}

function enviarAvisoGeneral() {
  const asunto = document.getElementById('asuntoAviso').value;
  const mensaje = document.getElementById('mensajeAviso').value;
  const soloActivos = document.getElementById('soloActivos').checked;
  
  if (!asunto || !mensaje) {
    alert('Por favor complete todos los campos');
    return;
  }
  
  if (!confirm('¿Está seguro de que desea enviar este aviso a todos los usuarios?')) return;
  
  fetch('api/enviar_aviso_general.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      asunto: asunto,
      mensaje: mensaje,
      solo_activos: soloActivos
    })
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(`Aviso enviado correctamente a ${result.usuarios_notificados} usuarios`);
      document.getElementById('asuntoAviso').value = '';
      document.getElementById('mensajeAviso').value = '';
    } else {
      alert('Error: ' + result.error);
    }
  })
  .catch(error => {
    console.error('Error al enviar aviso:', error);
    alert('Error al enviar aviso');
  });
}

function cerrarModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

function formatearFecha(fecha) {
  const date = new Date(fecha);
  return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES');
}

window.onclick = function(event) {
  const modals = document.querySelectorAll('.modal');
  modals.forEach(modal => {
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  });
}

