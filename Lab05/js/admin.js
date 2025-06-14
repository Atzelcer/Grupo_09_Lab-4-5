// SISTEMA ADMINISTRATIVO HOTEL - JAVASCRIPT PRINCIPAL
// Inicialización y configuración general del panel administrativo

document.addEventListener("DOMContentLoaded", () => {
    console.log("JS cargado correctamente");
    inicializarMenu();
    inicializarBusqueda();
    
    // Activar "Principal" por defecto
    const btnPrincipal = document.getElementById("btn-principal");
    if (btnPrincipal) {
        btnPrincipal.classList.add("activo");
    }
    
    // Cargar contenido principal por defecto
    const contenedor = document.getElementById("contenido");
    fetch("panel-principal.php")
        .then(res => res.text())
        .then(html => {
            contenedor.innerHTML = html;
        })
        .catch(err => {
            contenedor.innerHTML = "<p>Error al cargar contenido.</p>";
        });
});

// SISTEMA DE NAVEGACIÓN - Manejo del menú lateral
function inicializarMenu() {
    const botones = document.querySelectorAll(".boton-menu");
    const contenedor = document.getElementById("contenido");

    botones.forEach(boton => {
        boton.addEventListener("click", () => {
            botones.forEach(b => b.classList.remove("activo"));
            boton.classList.add("activo");

            let archivo = obtenerArchivoDesdeBoton(boton.id);

            fetch(archivo)
                .then(res => {
                    if (!res.ok) throw new Error("Error HTTP: " + res.status);
                    return res.text();
                })
                .then(html => {
                    contenedor.innerHTML = html;
                    
                    // Inicializar eventos específicos según la sección
                    if (archivo === "usuarios.php") {
                        setTimeout(inicializarEventosUsuarios, 100);
                    } else if (archivo === "habitaciones.php") {
                        setTimeout(inicializarEventosHabitaciones, 100);
                    } else if (archivo === "reservas.php") {
                        setTimeout(inicializarEventosReservas, 100);
                    }
                })
                .catch(err => {
                    console.error("Error al cargar contenido:", err.message);
                    contenedor.innerHTML = "<p>Error al cargar contenido.</p>";
                });
        });
    });
}

// SISTEMA DE NAVEGACIÓN - Mapeo de botones a archivos PHP
function obtenerArchivoDesdeBoton(id) {
    switch (id) {
        case "btn-principal": return "panel-principal.php";
        case "btn-usuarios": return "usuarios.php";
        case "btn-habitaciones": return "habitaciones.php";
        case "btn-reservas": return "reservas.php";
        default: return "panel-principal.php";
    }
}

// SISTEMA DE BÚSQUEDA - Búsqueda global en tiempo real
function inicializarBusqueda() {
    const inputBusqueda = document.getElementById("input-busqueda");
    const contenedor = document.getElementById("contenido");

    inputBusqueda.addEventListener("input", () => {
        const texto = inputBusqueda.value.trim();
        if (texto.length < 3) return;

        fetch(`../php/busqueda-global.php?query=${encodeURIComponent(texto)}`)
            .then(res => {
                if (!res.ok) throw new Error("Error en búsqueda");
                return res.text();
            })
            .then(html => {
                contenedor.innerHTML = html;
            })
            .catch(err => {
                contenedor.innerHTML = "<p>Error al buscar.</p>";
            });
    });
}

// GESTIÓN DE RESERVAS - Inicialización de eventos para el módulo de reservas
function inicializarEventosReservas() {
    console.log('Inicializando eventos de reservas');
    
    // Configurar fecha mínima
    const hoy = new Date().toISOString().split('T')[0];
    const fechaIngreso = document.getElementById('fecha_ingreso');
    const fechaSalida = document.getElementById('fecha_salida');
    
    if (fechaIngreso) {
        fechaIngreso.min = hoy;
        console.log('Fecha mínima de ingreso configurada');
    }
    if (fechaSalida) {
        fechaSalida.min = hoy;
        console.log('Fecha mínima de salida configurada');
    }
    
    // Configurar el envío AJAX del formulario de reservas
    const formReserva = document.getElementById('formReserva');
    if (formReserva) {
        formReserva.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Enviando formulario de reserva via AJAX');
            
            const formData = new FormData(this);
            
            // Mostrar loading
            const btnGuardar = document.querySelector('#formReserva .btn-guardar');
            const textoOriginal = btnGuardar.textContent;
            btnGuardar.textContent = 'Guardando...';
            btnGuardar.disabled = true;
            
            fetch('reservas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                console.log('Reserva guardada correctamente');
                
                // Cerrar modal
                cerrarModalReserva();
                
                // Recargar la tabla de reservas
                const contenedor = document.getElementById("contenido");
                contenedor.innerHTML = html;
                setTimeout(inicializarEventosReservas, 100);
                
                // Mostrar mensaje de éxito
                alert('Reserva guardada correctamente');
            })
            .catch(error => {
                console.error('Error al guardar reserva:', error);
                alert('Error al guardar la reserva');
            })
            .finally(() => {
                // Restaurar botón
                btnGuardar.textContent = textoOriginal;
                btnGuardar.disabled = false;
            });
        });
        
        console.log('Evento de formulario de reserva configurado');
    }
    
    // Configurar el envío AJAX del formulario de eliminación
    const formEliminar = document.getElementById('formEliminar');
    if (formEliminar) {
        formEliminar.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Eliminando reserva via AJAX');
            
            const formData = new FormData(this);
            
            fetch('reservas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                console.log('Reserva eliminada correctamente');
                
                // Cerrar modal
                cerrarModalEliminar();
                
                // Recargar la tabla de reservas
                const contenedor = document.getElementById("contenido");
                contenedor.innerHTML = html;
                setTimeout(inicializarEventosReservas, 100);
                
                // Mostrar mensaje de éxito
                alert('Reserva eliminada correctamente');
            })
            .catch(error => {
                console.error('Error al eliminar reserva:', error);
                alert('Error al eliminar la reserva');
            });
        });
        
        console.log('Evento de formulario de eliminación configurado');
    }
    
    console.log('Sistema de reservas configurado correctamente');
}

// GESTIÓN DE RESERVAS - Abrir modal para nueva reserva
function abrirModalReserva() {
    console.log('Abriendo modal nueva reserva');
    document.getElementById('modalTitulo').textContent = 'Nueva Reserva';
    document.getElementById('reservaId').value = '';
    document.getElementById('usuario_id').value = '';
    document.getElementById('habitacion_id').value = '';
    document.getElementById('fecha_ingreso').value = '';
    document.getElementById('fecha_salida').value = '';
    document.getElementById('estado').value = 'pendiente';
    
    document.getElementById('modalReserva').style.display = 'block';
}

// GESTIÓN DE RESERVAS - Cargar datos de reserva para edición
function editarReserva(id, usuarioId, habitacionId, fechaIngreso, fechaSalida, estado) {
    console.log('Editando reserva:', id);
    document.getElementById('modalTitulo').textContent = 'Editar Reserva';
    
    // Verificar que los elementos existen antes de asignar valores
    const reservaIdField = document.getElementById('reservaId');
    const usuarioIdField = document.getElementById('usuario_id');
    const habitacionIdField = document.getElementById('habitacion_id');
    const fechaIngresoField = document.getElementById('fecha_ingreso');
    const fechaSalidaField = document.getElementById('fecha_salida');
    const estadoField = document.getElementById('estado');
    
    if (reservaIdField) reservaIdField.value = id;
    if (usuarioIdField) usuarioIdField.value = usuarioId;
    if (habitacionIdField) habitacionIdField.value = habitacionId;
    if (fechaIngresoField) fechaIngresoField.value = fechaIngreso;
    if (fechaSalidaField) fechaSalidaField.value = fechaSalida;
    if (estadoField) estadoField.value = estado;
    
    document.getElementById('modalReserva').style.display = 'block';
}

// GESTIÓN DE RESERVAS - Cerrar modal de reserva
function cerrarModalReserva() {
    console.log('Cerrando modal de reserva');
    const modal = document.getElementById('modalReserva');
    if (modal) {
        modal.style.display = 'none';
    }
}

// GESTIÓN DE RESERVAS - Abrir modal de confirmación para eliminar reserva
function eliminarReserva(id) {
    console.log('Eliminando reserva:', id);
    document.getElementById('eliminarId').value = id;
    document.getElementById('modalEliminar').style.display = 'block';
}

// GESTIÓN DE RESERVAS - Cerrar modal de eliminación
function cerrarModalEliminar() {
    console.log('Cerrando modal de eliminar');
    const modal = document.getElementById('modalEliminar');
    if (modal) {
        modal.style.display = 'none';
    }
}

// GESTIÓN DE HABITACIONES - Inicialización de eventos para el módulo de habitaciones
function inicializarEventosHabitaciones() {
    console.log('Inicializando eventos de habitaciones');
    
    // Configurar el envío AJAX del formulario de habitaciones
    const formHabitacion = document.querySelector('form[enctype="multipart/form-data"]');
    if (formHabitacion) {
        formHabitacion.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Enviando formulario de habitación via AJAX');
            
            const formData = new FormData(this);
            
            // Asegurar nombres correctos de archivos
            const tipos = ['principal', 'cama', 'bano', 'sala'];
            tipos.forEach(tipo => {
                const input = document.getElementById('file-' + tipo);
                if (input && input.files && input.files[0]) {
                    formData.delete('file-' + tipo);
                    formData.append(`imagen_${tipo}`, input.files[0]);
                }
            });
            
            // Mostrar loading
            const btnGuardar = document.querySelector('.btn-guardar');
            const textoOriginal = btnGuardar.textContent;
            btnGuardar.textContent = 'Guardando...';
            btnGuardar.disabled = true;
            
            fetch('procesar-habitacion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta recibida:', data);
                
                if (data.success) {
                    // Cerrar modal
                    cerrarModal();
                    
                    // Recargar la tabla de habitaciones
                    fetch('habitaciones.php')
                        .then(res => res.text())
                        .then(html => {
                            const contenedor = document.getElementById("contenido");
                            contenedor.innerHTML = html;
                            setTimeout(inicializarEventosHabitaciones, 100);
                        });
                    
                    // Mostrar mensaje de éxito
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                    if (data.errors && data.errors.length > 0) {
                        console.error('Errores adicionales:', data.errors);
                    }
                }
            })
            .catch(error => {
                console.error('Error al guardar:', error);
                alert('Error al guardar la habitación');
            })
            .finally(() => {
                // Restaurar botón
                btnGuardar.textContent = textoOriginal;
                btnGuardar.disabled = false;
            });
        });
        
        console.log('Evento de formulario configurado');
    }
    
    // Verificar elementos del modal
    if (document.getElementById('modalHabitacion')) {
        console.log('Modal de habitaciones encontrado');
    }
}

// GESTIÓN DE HABITACIONES - Abrir modal para nueva habitación
function abrirModal() {
    console.log('Abriendo modal nueva habitación');
    document.getElementById('modalTitulo').textContent = 'Nueva Habitación';
    document.getElementById('habitacionId').value = '';
    document.getElementById('numero').value = '';
    document.getElementById('piso').value = '';
    document.getElementById('tipo_habitacion_id').value = '';
    document.getElementById('estado').value = 'disponible';
    
    // Limpiar imágenes
    limpiarImagenes();
    
    // Mostrar tab de información
    cambiarTab('info');
    
    document.getElementById('modalHabitacion').style.display = 'block';
}

// GESTIÓN DE HABITACIONES - Cargar datos de habitación para edición
function editarHabitacion(id, numero, piso, tipoId, estado) {
    console.log('Editando habitación:', id);
    document.getElementById('modalTitulo').textContent = 'Editar Habitación';
    document.getElementById('habitacionId').value = id;
    document.getElementById('numero').value = numero;
    document.getElementById('piso').value = piso;
    document.getElementById('tipo_habitacion_id').value = tipoId;
    document.getElementById('estado').value = estado;
    
    // Cargar imágenes existentes desde la base de datos
    cargarImagenesDesdeDB(id);
    
    // Mostrar tab de información
    cambiarTab('info');
    
    document.getElementById('modalHabitacion').style.display = 'block';
}

// GESTIÓN DE HABITACIONES - Configurar eliminación de habitación
function eliminarHabitacion(id, numero) {
    console.log('Eliminando habitación:', id);
    document.getElementById('habitacionEliminar').textContent = numero;
    document.getElementById('eliminarId').value = id;
    
    // Configurar evento de confirmación
    const formEliminar = document.querySelector('#modalEliminar form');
    if (formEliminar) {
        formEliminar.onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('procesar-habitacion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cerrarModalEliminar();
                    
                    // Recargar la tabla
                    fetch('habitaciones.php')
                        .then(res => res.text())
                        .then(html => {
                            const contenedor = document.getElementById("contenido");
                            contenedor.innerHTML = html;
                            setTimeout(inicializarEventosHabitaciones, 100);
                        });
                    
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar la habitación');
            });
        };
    }
    
    document.getElementById('modalEliminar').style.display = 'block';
}

// GESTIÓN DE HABITACIONES - Cerrar modal de habitación
function cerrarModal() {
    if (document.getElementById('modalHabitacion')) {
        document.getElementById('modalHabitacion').style.display = 'none';
        limpiarImagenes();
    }
}

// GESTIÓN DE HABITACIONES - Cerrar modal de eliminación
function cerrarModalEliminar() {
    if (document.getElementById('modalEliminar')) {
        document.getElementById('modalEliminar').style.display = 'none';
    }
}

// GESTIÓN DE HABITACIONES - Cambiar entre pestañas del modal
function cambiarTab(tab) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Mostrar tab seleccionado
    const tabElement = document.getElementById('tab-' + tab);
    if (tabElement) {
        tabElement.classList.add('active');
    }
    
    // Marcar botón activo
    if (event && event.target) {
        event.target.classList.add('active');
    }
}

// GESTIÓN DE HABITACIONES - Activar selector de imagen
function seleccionarImagen(tipo) {
    const fileInput = document.getElementById('file-' + tipo);
    if (fileInput) {
        fileInput.click();
    }
}

// GESTIÓN DE HABITACIONES - Mostrar preview de imagen seleccionada
function previewImagen(input, tipo) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview-' + tipo);
            const container = input.parentElement;
            
            if (preview && container) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                container.classList.add('has-image');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// GESTIÓN DE HABITACIONES - Limpiar todas las imágenes del modal
function limpiarImagenes() {
    const tipos = ['principal', 'cama', 'bano', 'sala'];
    tipos.forEach(tipo => {
        const preview = document.getElementById('preview-' + tipo);
        const input = document.getElementById('file-' + tipo);
        
        if (preview && input) {
            const container = input.parentElement;
            preview.style.display = 'none';
            preview.src = '';
            input.value = '';
            if (container) {
                container.classList.remove('has-image');
            }
        }
    });
}

// GESTIÓN DE HABITACIONES - Cargar imágenes existentes desde base de datos
function cargarImagenesDesdeDB(habitacionId) {
    console.log('Cargando imágenes desde BD para habitación:', habitacionId);
    
    fetch(`obtener-imagenes.php?habitacion_id=${habitacionId}`)
        .then(response => response.json())
        .then(imagenes => {
            console.log('Imágenes obtenidas:', imagenes);
            
            const tiposPorOrden = {
                1: 'principal',
                2: 'cama', 
                3: 'bano',
                4: 'sala'
            };
            
            Object.keys(imagenes).forEach(orden => {
                const tipo = tiposPorOrden[orden];
                if (tipo) {
                    const preview = document.getElementById('preview-' + tipo);
                    const input = document.getElementById('file-' + tipo);
                    
                    if (preview && input) {
                        const container = input.parentElement;
                        const rutaImagen = `../img/HABITACION${habitacionId}/${imagenes[orden]}`;
                        
                        const img = new Image();
                        img.onload = function() {
                            preview.src = rutaImagen;
                            preview.style.display = 'block';
                            if (container) {
                                container.classList.add('has-image');
                            }
                        };
                        img.onerror = function() {
                            console.warn(`Imagen no encontrada: ${rutaImagen}`);
                        };
                        img.src = rutaImagen;
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error al cargar imágenes desde BD:', error);
        });
}

// GESTIÓN DE HABITACIONES - Cargar imágenes para carrusel desde base de datos
function cargarCarruselDesdeDB(habitacionId) {
    console.log('Cargando carrusel desde BD para habitación:', habitacionId);
    
    fetch(`obtener-imagenes.php?habitacion_id=${habitacionId}`)
        .then(response => response.json())
        .then(imagenes => {
            const tiposPorOrden = {
                1: 'principal',
                2: 'cama',
                3: 'bano', 
                4: 'sala'
            };
            
            Object.keys(imagenes).forEach(orden => {
                const tipo = tiposPorOrden[orden];
                if (tipo) {
                    const imgElement = document.getElementById('carrusel-' + tipo);
                    if (imgElement) {
                        const noImagenElement = imgElement.nextElementSibling;
                        const rutaImagen = `../img/HABITACION${habitacionId}/${imagenes[orden]}`;
                        
                        const img = new Image();
                        img.onload = function() {
                            imgElement.src = rutaImagen;
                            imgElement.style.display = 'block';
                            if (noImagenElement) {
                                noImagenElement.style.display = 'none';
                            }
                        };
                        img.onerror = function() {
                            imgElement.style.display = 'none';
                            if (noImagenElement) {
                                noImagenElement.style.display = 'flex';
                            }
                        };
                        img.src = rutaImagen;
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error al cargar carrusel desde BD:', error);
        });
}

// GESTIÓN DE HABITACIONES - Abrir carrusel de imágenes
function abrirCarrusel(habitacionId) {
    console.log('Abriendo carrusel para habitación:', habitacionId);
    
    const titulo = document.getElementById('carruselTitulo');
    if (titulo) {
        titulo.textContent = `Galería - Habitación ${habitacionId}`;
    }
    
    cargarCarruselDesdeDB(habitacionId);
    const carruselModal = document.getElementById('carruselModal');
    if (carruselModal) {
        carruselModal.style.display = 'block';
    }
}

// GESTIÓN DE HABITACIONES - Función obsoleta mantenida por compatibilidad
function cargarCarruselImagenes(habitacionId) {
    console.log('Función obsoleta, usando cargarCarruselDesdeDB');
    cargarCarruselDesdeDB(habitacionId);
}

// GESTIÓN DE HABITACIONES - Cerrar carrusel de imágenes
function cerrarCarrusel() {
    const carruselModal = document.getElementById('carruselModal');
    if (carruselModal) {
        carruselModal.style.display = 'none';
    }
}

// GESTIÓN DE USUARIOS - Inicialización de eventos para el módulo de usuarios
function inicializarEventosUsuarios() {
    const modal = document.getElementById("modalUsuario");
    const form = document.getElementById("formUsuario");
    const titulo = document.getElementById("titulo-modal");

    document.querySelectorAll(".fila-usuario").forEach(fila => {
        fila.querySelectorAll("td").forEach((celda, index) => {
            if (index < fila.cells.length - 1) {
                celda.addEventListener("click", () => mostrarVistaUsuario(fila));
            }
        });
    });

    document.getElementById("cerrarVista").onclick = () => ocultarModal("modalVistaUsuario");

    document.querySelectorAll(".eliminar").forEach(btn => {
        btn.addEventListener("click", () => confirmarEliminacion(btn));
    });

    document.getElementById("btnConfirmarEliminar").onclick = ejecutarEliminacion;
    document.getElementById("btnCancelarEliminar").onclick = () => ocultarModal("modalConfirmarEliminar");
    document.getElementById("cerrarConfirmar").onclick = () => ocultarModal("modalConfirmarEliminar");

    document.getElementById("btnCancelar").onclick = () => ocultarModal("modalUsuario");
    document.getElementById("cerrarModal").onclick = () => ocultarModal("modalUsuario");

    document.querySelectorAll(".editar").forEach(btn => {
        btn.addEventListener("click", () => cargarUsuarioParaEdicion(btn.dataset.id));
    });

    document.getElementById("btn-nuevo-usuario").onclick = () => {
        titulo.textContent = "Nuevo Usuario";
        form.reset();
        form.id.value = "";
        mostrarModal("modalUsuario");
    };

    form.onsubmit = (e) => {
        e.preventDefault();
        const datos = new FormData(form);
        fetch("../php/guardar-usuario.php", {
            method: "POST",
            body: datos
        })
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            ocultarModal("modalUsuario");
            fetch("usuarios.php")
                .then(res => res.text())
                .then(html => {
                    document.getElementById("contenido").innerHTML = html;
                    inicializarEventosUsuarios();
                });
        });
    };
}

// GESTIÓN DE USUARIOS - Mostrar datos detallados de usuario
function mostrarVistaUsuario(fila) {
    document.getElementById("vista-nombre").textContent = fila.dataset.nombre;
    document.getElementById("vista-correo").textContent = fila.dataset.correo;
    document.getElementById("vista-telefono").textContent = fila.dataset.telefono;
    document.getElementById("vista-rol").textContent = fila.dataset.rol;
    document.getElementById("vista-fecha").textContent = fila.dataset.fecha;
    mostrarModal("modalVistaUsuario");
}

// GESTIÓN DE USUARIOS - Mostrar modal
function mostrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove("oculto");
    }
}

// GESTIÓN DE USUARIOS - Ocultar modal
function ocultarModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add("oculto");
    }
}

// GESTIÓN DE USUARIOS - Variables globales para eliminación
let idEliminar = null;
let nombreEliminar = "";

// GESTIÓN DE USUARIOS - Confirmar eliminación de usuario
function confirmarEliminacion(btn) {
    const fila = btn.closest("tr");

    if (!fila) {
        console.error("Error: No se encontró la fila correspondiente al botón.");
        return;
    }

    idEliminar = btn.dataset.id;
    nombreEliminar = fila.dataset.nombre;

    if (!idEliminar || !nombreEliminar) {
        console.error("Error: No se encontró el ID o el nombre del usuario.");
        console.log("ID encontrado:", idEliminar);
        console.log("Nombre encontrado:", nombreEliminar);
        return;
    }

    console.log("Preparando eliminación - ID:", idEliminar, "Nombre:", nombreEliminar);

    const textoConfirmacion = document.getElementById("texto-confirmacion");
    if (textoConfirmacion) {
        textoConfirmacion.textContent = `¿Estás seguro de que deseas eliminar al usuario "${nombreEliminar}"?`;
    }

    mostrarModal("modalConfirmarEliminar");
}

// GESTIÓN DE USUARIOS - Ejecutar eliminación de usuario
function ejecutarEliminacion() {
    if (!idEliminar) {
        console.error("Error: ID de usuario no definido.");
        return;
    }

    console.log("Ejecutando eliminación del usuario ID:", idEliminar);

    fetch("../php/eliminar-usuario.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id=${encodeURIComponent(idEliminar)}`
    })
    .then(res => res.text())
    .then(msg => {
        console.log("Respuesta del servidor:", msg);
        
        alert(msg);
        
        ocultarModal("modalConfirmarEliminar");

        fetch("usuarios.php")
            .then(res => res.text())
            .then(html => {
                document.getElementById("contenido").innerHTML = html;
                inicializarEventosUsuarios();
            });
    })
    .catch(error => {
        console.error("Error en eliminación:", error);
        alert("Ocurrió un error al eliminar el usuario.");
    });
}

// GESTIÓN DE USUARIOS - Cargar usuario para edición
function cargarUsuarioParaEdicion(id) {
    const modal = document.getElementById("modalUsuario");
    const form = document.getElementById("formUsuario");
    const titulo = document.getElementById("titulo-modal");

    fetch(`../php/obtener-usuario.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                titulo.textContent = "Editar Usuario";
                form.id.value = data.id;
                form.nombre.value = data.nombre;
                form.correo.value = data.correo;
                form.telefono.value = data.telefono;
                form.clave.value = data.password || "";
                mostrarModal("modalUsuario");
            }
        });
}

// SISTEMA GENERAL - Cerrar modales al hacer clic fuera
window.onclick = function(event) {
    if (event.target.classList.contains('modal') || event.target.classList.contains('carrusel-modal')) {
        event.target.style.display = 'none';
    }
}

console.log('Sistema administrativo completo cargado (usuarios, habitaciones y reservas)');