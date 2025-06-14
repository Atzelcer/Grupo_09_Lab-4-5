/**
 * JavaScript para Gestión de Habitaciones
 * Sistema de gestión hotelera
 */

// Funciones principales del modal
function abrirModal() {
    console.log(' Abriendo modal nueva habitación');
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

function editarHabitacion(id, numero, piso, tipoId, estado) {
    console.log('Editando habitación:', id);
    document.getElementById('modalTitulo').textContent = 'Editar Habitación';
    document.getElementById('habitacionId').value = id;
    document.getElementById('numero').value = numero;
    document.getElementById('piso').value = piso;
    document.getElementById('tipo_habitacion_id').value = tipoId;
    document.getElementById('estado').value = estado;
    
    // Cargar imágenes existentes
    cargarImagenesExistentes(id);
    
    // Mostrar tab de información
    cambiarTab('info');
    
    document.getElementById('modalHabitacion').style.display = 'block';
}

function eliminarHabitacion(id, numero) {
    console.log('Eliminando habitación:', id);
    document.getElementById('habitacionEliminar').textContent = numero;
    document.getElementById('eliminarId').value = id;
    document.getElementById('modalEliminar').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalHabitacion').style.display = 'none';
    limpiarImagenes();
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminar').style.display = 'none';
}

// Funciones de tabs
function cambiarTab(tab) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Mostrar tab seleccionado
    document.getElementById('tab-' + tab).classList.add('active');
    event.target.classList.add('active');
}

// Funciones de gestión de imágenes
function seleccionarImagen(tipo) {
    document.getElementById('file-' + tipo).click();
}

function previewImagen(input, tipo) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview-' + tipo);
            const container = input.parentElement;
            
            preview.src = e.target.result;
            preview.style.display = 'block';
            container.classList.add('has-image');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function limpiarImagenes() {
    const tipos = ['principal', 'cama', 'bano', 'sala'];
    tipos.forEach(tipo => {
        const preview = document.getElementById('preview-' + tipo);
        const input = document.getElementById('file-' + tipo);
        const container = input.parentElement;
        
        preview.style.display = 'none';
        preview.src = '';
        input.value = '';
        container.classList.remove('has-image');
    });
}

function cargarImagenesExistentes(habitacionId) {
    const carpeta = `../img/HABITACION${habitacionId}`;
    const imagenes = {
        principal: [`habitacion${habitacionId}.jpg`, `habitacion${habitacionId}.avif`],
        cama: ['cama.jpg', 'cama.avif'],
        bano: ['baño.jpg', 'baño.avif'],
        sala: ['sala de estar.jpg', 'sala de estar.jpeg']
    };
    
    Object.keys(imagenes).forEach(tipo => {
        const preview = document.getElementById('preview-' + tipo);
        const container = document.getElementById('file-' + tipo).parentElement;
        
        // Intentar cargar cada posible imagen
        imagenes[tipo].forEach(nombreArchivo => {
            const img = new Image();
            img.onload = function() {
                preview.src = `${carpeta}/${nombreArchivo}`;
                preview.style.display = 'block';
                container.classList.add('has-image');
            };
            img.onerror = function() {
                // No hacer nada si la imagen no existe
            };
            img.src = `${carpeta}/${nombreArchivo}`;
        });
    });
}

// Funciones del carrusel de imágenes
function abrirCarrusel(habitacionId) {
    console.log(' Abriendo carrusel para habitación:', habitacionId);
    
    const titulo = document.getElementById('carruselTitulo');
    titulo.textContent = `Galería - Habitación ${habitacionId}`;
    
    cargarCarruselImagenes(habitacionId);
    document.getElementById('carruselModal').style.display = 'block';
}

function cargarCarruselImagenes(habitacionId) {
    const carpeta = `../img/HABITACION${habitacionId}`;
    const imagenes = {
        principal: [`habitacion${habitacionId}.jpg`, `habitacion${habitacionId}.avif`],
        cama: ['cama.jpg', 'cama.avif'],
        bano: ['baño.jpg', 'baño.avif'],
        sala: ['sala de estar.jpg', 'sala de estar.jpeg']
    };
    
    Object.keys(imagenes).forEach(tipo => {
        const imgElement = document.getElementById('carrusel-' + tipo);
        const noImagenElement = imgElement.nextElementSibling;
        
        // Resetear estado
        imgElement.style.display = 'block';
        noImagenElement.style.display = 'none';
        
        // Intentar cargar imagen
        let imagenEncontrada = false;
        imagenes[tipo].forEach(nombreArchivo => {
            if (!imagenEncontrada) {
                const img = new Image();
                img.onload = function() {
                    imgElement.src = `${carpeta}/${nombreArchivo}`;
                    imagenEncontrada = true;
                };
                img.onerror = function() {
                    // Continuar con la siguiente imagen
                };
                img.src = `${carpeta}/${nombreArchivo}`;
            }
        });
    });
}

function cerrarCarrusel() {
    document.getElementById('carruselModal').style.display = 'none';
}

// Funciones de integración con base de datos
function cargarImagenesDesdeDB(habitacionId) {
    console.log(' Cargando imágenes desde BD para habitación:', habitacionId);
    
    // Hacer petición para obtener las imágenes de la BD
    fetch(`obtener-imagenes.php?habitacion_id=${habitacionId}`)
        .then(response => response.json())
        .then(imagenes => {
            console.log('📷 Imágenes obtenidas:', imagenes);
            
            // Mapeo de orden a tipo
            const tiposPorOrden = {
                1: 'principal',
                2: 'cama', 
                3: 'bano',
                4: 'sala'
            };
            
            // Cargar cada imagen
            Object.keys(imagenes).forEach(orden => {
                const tipo = tiposPorOrden[orden];
                if (tipo) {
                    const preview = document.getElementById('preview-' + tipo);
                    const input = document.getElementById('file-' + tipo);
                    
                    if (preview && input) {
                        const container = input.parentElement;
                        const rutaImagen = `../img/HABITACION${habitacionId}/${imagenes[orden]}`;
                        
                        // Verificar si la imagen existe y cargarla
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

function cargarCarruselDesdeDB(habitacionId) {
    console.log(' Cargando carrusel desde BD para habitación:', habitacionId);
    
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

// Event listeners y configuración inicial
document.addEventListener('DOMContentLoaded', function() {
    console.log(' Inicializando sistema de gestión de habitaciones...');
    
    // Preparar formulario para envío con nombres correctos de archivos
    const form = document.querySelector('form[enctype="multipart/form-data"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const tipos = ['principal', 'cama', 'bano', 'sala'];
            
            tipos.forEach(tipo => {
                const input = document.getElementById('file-' + tipo);
                if (input && input.files && input.files[0]) {
                    // Cambiar el nombre del input para que PHP lo procese correctamente
                    input.name = `imagen_${tipo}`;
                }
            });
        });
    }
    
    // Configurar cierre de modales al hacer clic fuera
    window.onclick = function(event) {
        const modales = ['modalHabitacion', 'modalEliminar', 'carruselModal'];
        modales.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && (event.target === modal || event.target.classList.contains('modal') || event.target.classList.contains('carrusel-modal'))) {
                modal.style.display = 'none';
            }
        });
    }
    
    console.log(' Sistema de gestión de habitaciones con 4 imágenes fijas cargado correctamente');
});

// Funciones auxiliares
function mostrarMensaje(texto, tipo = 'info') {
    console.log(`${tipo.toUpperCase()}: ${texto}`);
    // Aquí podrías agregar lógica para mostrar notificaciones en la UI
}

function validarFormulario() {
    const numero = document.getElementById('numero').value.trim();
    const piso = document.getElementById('piso').value;
    const tipoId = document.getElementById('tipo_habitacion_id').value;
    
    if (!numero || !piso || !tipoId) {
        mostrarMensaje('Por favor complete todos los campos obligatorios', 'error');
        return false;
    }
    
    return true;
}

// Exportar funciones para uso global (si es necesario)
window.habitacionesJS = {
    abrirModal,
    editarHabitacion,
    eliminarHabitacion,
    cerrarModal,
    cerrarModalEliminar,
    cambiarTab,
    seleccionarImagen,
    previewImagen,
    abrirCarrusel,
    cerrarCarrusel,
    cargarImagenesDesdeDB,
    cargarCarruselDesdeDB
};