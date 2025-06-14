/**
 * JavaScript para Gestión de Reservas
 * Sistema de gestión hotelera
 */

// Funciones principales del modal
function abrirModal() {
    console.log(' Abriendo modal nueva reserva');
    document.getElementById('modalTitulo').textContent = 'Nueva Reserva';
    document.getElementById('reservaId').value = '';
    document.getElementById('usuario_id').value = '';
    document.getElementById('habitacion_id').value = '';
    document.getElementById('fecha_ingreso').value = '';
    document.getElementById('fecha_salida').value = '';
    document.getElementById('estado').value = 'pendiente';
    
    document.getElementById('modalReserva').style.display = 'block';
}

function editarReserva(id, usuarioId, habitacionId, fechaIngreso, fechaSalida, estado) {
    console.log('Editando reserva:', id);
    document.getElementById('modalTitulo').textContent = 'Editar Reserva';
    document.getElementById('reservaId').value = id;
    document.getElementById('usuario_id').value = usuarioId;
    document.getElementById('habitacion_id').value = habitacionId;
    document.getElementById('fecha_ingreso').value = fechaIngreso;
    document.getElementById('fecha_salida').value = fechaSalida;
    document.getElementById('estado').value = estado;
    
    document.getElementById('modalReserva').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalReserva').style.display = 'none';
}

function eliminarReserva(id) {
    console.log('Eliminando reserva:', id);
    document.getElementById('eliminarId').value = id;
    document.getElementById('modalEliminar').style.display = 'block';
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminar').style.display = 'none';
}

// Event listeners y configuración inicial
document.addEventListener('DOMContentLoaded', function() {
    console.log(' Inicializando sistema de gestión de reservas...');
    
    // Configurar fecha mínima
    const hoy = new Date().toISOString().split('T')[0];
    const fechaIngreso = document.getElementById('fecha_ingreso');
    const fechaSalida = document.getElementById('fecha_salida');
    
    if (fechaIngreso) {
        fechaIngreso.min = hoy;
    }
    if (fechaSalida) {
        fechaSalida.min = hoy;
    }
    
    // Configurar cierre de modales al hacer clic fuera
    window.onclick = function(event) {
        const modales = ['modalReserva', 'modalEliminar'];
        modales.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    console.log(' Sistema de gestión de reservas cargado correctamente');
});

// Exportar funciones para uso global
window.reservasJS = {
    abrirModal,
    editarReserva,
    eliminarReserva,
    cerrarModal,
    cerrarModalEliminar
};