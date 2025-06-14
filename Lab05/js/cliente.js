// cliente.js

// Variables globales
let habitacionSeleccionada = null;
let currentCarruselRoom = null;

// Al cargar el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Si venimos de "nueva-reserva", prellenar fechas y buscar
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('section') === 'nueva-reserva') {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const dayAfterTomorrow = new Date(today);
        dayAfterTomorrow.setDate(dayAfterTomorrow.getDate() + 2);
        document.getElementById('fecha_ingreso').value = tomorrow.toISOString().split('T')[0];
        document.getElementById('fecha_salida').value = dayAfterTomorrow.toISOString().split('T')[0];
        buscarHabitaciones();
    }

    // Listener para cerrar el modal del carrusel
    const cerrarBtn = document.querySelector('.cerrar-carrusel-cliente');
    if (cerrarBtn) {
        cerrarBtn.addEventListener('click', cerrarCarruselCliente);
    }
});

// Función para abrir el modal de la galería
function abrirCarruselCliente(habitacionId) {
    currentCarruselRoom = habitacionId;

    // Ajustar título del modal
    const habitacionCard = document.querySelector(`[onclick*="seleccionarParaCarrusel(${habitacionId})"]`);
    if (habitacionCard) {
        const titulo = habitacionCard.querySelector('h3').textContent;
        document.getElementById('carruselClienteTitulo').textContent = `Galería - ${titulo}`;
    }

    // Cargar imágenes
    cargarImagenesCarruselCliente(habitacionId);

    // Mostrar modal y bloquear scroll
    const modal = document.getElementById('carruselClienteModal');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Función para cerrar el modal de la galería
function cerrarCarruselCliente() {
    const modal = document.getElementById('carruselClienteModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
    currentCarruselRoom = null;
}

// Función para obtener y mostrar las imágenes en el modal
function cargarImagenesCarruselCliente(habitacionId) {
    fetch(`../admin/obtener-imagenes.php?habitacion_id=${habitacionId}`)
    .then(res => res.json())
    .then(imagenes => {
        const tiposPorOrden = {
            1: { tipo: 'principal', nombre: 'Imagen Principal' },
            2: { tipo: 'cama', nombre: 'Cama' },
            3: { tipo: 'bano', nombre: 'Baño' },
            4: { tipo: 'sala', nombre: 'Sala de Estar' }
        };
        const container = document.getElementById('carruselClienteImagenes');
        container.innerHTML = '';

        Object.keys(tiposPorOrden).forEach(orden => {
            const { nombre } = tiposPorOrden[orden];
            const filename = imagenes[orden];
            const wrapper = document.createElement('div');
            wrapper.className = filename ? 'carrusel-cliente-imagen' : 'carrusel-cliente-imagen no-disponible';

            if (filename) {
                const ruta = `../img/HABITACION${habitacionId}/${filename}`;
                wrapper.innerHTML = `
                    <img src="${ruta}" alt="${nombre}" onclick="ampliarImagen('${ruta}','${nombre}')">
                    <div class="imagen-label">${nombre}</div>
                `;
            } else {
                wrapper.innerHTML = `
                    <div class="no-imagen-placeholder">
                        <i class="fas fa-image"></i>
                        <span>No disponible</span>
                    </div>
                    <div class="imagen-label">${nombre}</div>
                `;
            }
            container.appendChild(wrapper);
        });
    })
    .catch(err => {
        console.error('Error al cargar imágenes:', err);
        document.getElementById('carruselClienteImagenes').innerHTML = `
            <div class="error-carga">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error al cargar las imágenes</p>
            </div>
        `;
    });
}

// Función para mostrar la imagen ampliada (puedes ajustarla según tu implementación)
function ampliarImagen(src, alt) {
    // Por ejemplo, podrías reutilizar otro modal o ventana emergente
    const modalImg = document.getElementById('imagenAmpliada');
    const etiquetaAlt = document.getElementById('imagenAmpliadaAlt');
    modalImg.src = src;
    etiquetaAlt.textContent = alt;
    // Mostrar tu modal de ampliación aquí...
}

// Resto de funciones originales: buscarHabitaciones, mostrarHabitaciones, seleccionarParaCarrusel, etc.
// Asegúrate de que existan en este mismo archivo o estén correctamente importadas.
