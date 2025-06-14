function cargarCorreos(tipo) {
  console.log("Cargando correos tipo:", tipo);
  
  fetch(`api/get_emails.php?tipo=${tipo}`)
    .then(response => {
      if (!response.ok) {
        throw new Error("Error en la respuesta del servidor");
      }
      return response.json();
    })
    .then(correos => {
      console.log("Correos recibidos:", correos);  
      const tbody = document.getElementById("tbodyCorreos");
      tbody.innerHTML = "";

      correos.forEach(correo => {
        const fila = document.createElement("tr");

        if (correo.status === 'leído') {
            fila.classList.add('correo-leido'); 
        } 
        const correoContacto = tipo === 'inbox' ? correo.correo_origen : correo.correo_destino;
        let contenidoHTML = `
          <td>${correoContacto}</td>
          <td>${correo.subject}</td>
          <td>${correo.status}</td>
        `;
        if (tipo ==='inbox'|| tipo === 'sent') {
          if (correo.status ==='pendiente') {
            contenidoHTML += `<td><button class="ver" onclick='cambiarEstado("${correo.message.replace(/"/g, '&quot;')}", ${correo.id}, "${correo.status}", this)'>Ver</button>`;
          }else{
            contenidoHTML += `
            <td><button onclick='verCorreo("${correo.message.replace(/"/g, '&quot;')}")'>Ver</button>`;
          }
          contenidoHTML += `<button onclick='eliminarCorreo(${correo.id},"${tipo}")'>Eliminar</button></td>`;
        } else {
          contenidoHTML += `<td><button onclick='enviarCorreo(${correo.id})'>Enviar</button></td>`;
        }
        fila.innerHTML = contenidoHTML;
        tbody.appendChild(fila);
      });
    })
    .catch(error => {
      console.error("Error al cargar correos:", error);
    });
}

function verCorreo(texto) {
  console.log("Texto del correo:", texto);
  HTML=`<h2>Mensaje:</h2>(${texto})`;
  document.getElementById("contenidoCorreo").innerHTML = HTML;
  document.getElementById("modalCorreo").style.display = "block";
}

document.addEventListener("DOMContentLoaded", () => {
  console.log("Cargando bandeja de entrada automáticamente...");
  cargarCorreos("inbox");


  document.getElementById("btnEntrada").addEventListener("click", () => cargarCorreos("inbox"));
  document.getElementById("btnSalida").addEventListener("click", () => cargarCorreos("sent"));
  document.getElementById("btnBorrador").addEventListener("click", () => cargarCorreos("drafts"));


  document.getElementById("cerrarModal").addEventListener("click", () => {
    document.getElementById("modalCorreo").style.display = "none";
  });
});

function Redactar() {
  fetch('redactar.html')
  .then(response => response.text())
  .then(data => {

    document.getElementById('contenidoCorreo').innerHTML = data;

    document.getElementById('modalCorreo').style.display = 'block';
  })  
}

function correoEnviado(){
  console.log("Función enviarCorreo llamada");
  const destinatario = document.getElementById("email").value;
  const asunto = document.getElementById("asunto").value;
  const mensaje = document.getElementById("mensajec").value;
  console.log("Enviando correo a:", destinatario, "Asunto:", asunto, "Mensaje:", mensaje);

  fetch("api/enviar.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      destinatario: destinatario,
      asunto: asunto,
      mensaje: mensaje
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Correo enviado correctamente.");
      document.getElementById("email").value = "";
      document.getElementById("asunto").value = "";
      document.getElementById("mensaje").value = "";
      document.getElementById("modalCorreo").style.display = "none";
    }
  })
}

function guardarCorreo() {
  const destinatario = document.getElementById("email").value;
  const asunto = document.getElementById("asunto").value;
  const mensaje = document.getElementById("mensajec").value;
  console.log("Enviando correo a:", destinatario, "Asunto:", asunto, "Mensaje:", mensaje);

  fetch("api/guardar.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      destinatario: destinatario,
      asunto: asunto,
      mensaje: mensaje
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Correo guardado correctamente.");
      document.getElementById("email").value = "";
      document.getElementById("asunto").value = "";
      document.getElementById("mensaje").value = "";
      document.getElementById("modalCorreo").style.display = "none";
    }
  })
}

function eliminarCorreo(id,tipo) {
  if (!confirm("¿Estás seguro de que deseas eliminar este correo?")) return;

  fetch('api/eliminarCorreo.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id: id })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert("Correo eliminado correctamente.");
      tipo === 'inbox' ? cargarCorreos("inbox") : cargarCorreos("sent");
    } else {
      alert("Error al eliminar: " + data.error);
    }
  })
  .catch(error => {
    console.error("Error al eliminar correo:", error);
  });
}

function enviarCorreo(id) {
  fetch('api/enviarCorreo.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id: id })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert("Correo enviado correctamente.");
      cargarCorreos('drafts'); 
    } else {
      alert("Error al enviar: " + data.error);
    }
  })
  .catch(error => {
    console.error("Error al enviar correo:", error);
  });
}

function cambiarEstado(mensaje, idCorreo, status, boton) {
  console.log("Texto del correo:", mensaje);
  HTML=`<h2>Mensaje:</h2>(${mensaje})`;
  document.getElementById("contenidoCorreo").innerHTML = HTML;
  document.getElementById("modalCorreo").style.display = "block";
  if (status === 'pendiente') {
    fetch('api/leido.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: idCorreo })
    })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        cargarCorreos('inbox'); 
      }
    })
    .catch(error => {
      console.error("Error al marcar como leído:", error);
    });
  }
}
