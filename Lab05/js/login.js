// ===== FUNCIONES BÁSICAS DEL MODAL =====
function abrirModal() {
    console.log('Abriendo modal de login...');
    const modal = document.getElementById("modalLogin");
    if (modal) {
        modal.style.display = "block";
        showLogin(); // Mostrar login por defecto
    } else {
        console.error('Modal de login no encontrado');
    }
}

function cerrarModal() {
    console.log('Cerrando modal de login...');
    const modal = document.getElementById("modalLogin");
    if (modal) {
        modal.style.display = "none";
    }
}

function showLogin() {
    // Cambiar pestañas
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    const firstTab = document.querySelector('.tab-btn');
    if (firstTab) {
        firstTab.classList.add('active');
    }
    
    // Cambiar formularios
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    if (loginForm) loginForm.classList.add('active');
    if (registerForm) registerForm.classList.remove('active');
}

function showRegister() {
    // Cambiar pestañas
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    const secondTab = document.querySelectorAll('.tab-btn')[1];
    if (secondTab) {
        secondTab.classList.add('active');
    }
    
    // Cambiar formularios
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    if (loginForm) loginForm.classList.remove('active');
    if (registerForm) registerForm.classList.add('active');
}

function scrollToFeatures() {
    const featuresSection = document.getElementById('features');
    if (featuresSection) {
        featuresSection.scrollIntoView({
            behavior: 'smooth'
        });
    }
}

// ===== FUNCIONES DE AUTENTICACIÓN =====

// Función para manejar el login con fetch
function handleLogin(event) {
    event.preventDefault();
    console.log('Iniciando login...');
    
    const formData = new FormData(event.target);
    
    fetch('php/login-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(result) {
        console.log('Resultado del login:', result);
        if (result.success) {
            // Si es admin, redirigir al dashboard
            if (result.redirect) {
                console.log('Redirigiendo admin a:', result.redirect);
                window.location.href = result.redirect;
                return;
            }
            
            // Usuario normal - transformar interfaz sin recargar página
            console.log('Cargando interfaz para usuario:', result.user.nombre);
            cerrarModal();
            transformToUserInterface(result.user);
            showSuccessMessage('¡Bienvenido ' + result.user.nombre + '!');
        } else {
            // Error en login
            console.log('Error en login:', result.message);
            showErrorMessage(result.message || 'Error en las credenciales');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showErrorMessage('Error de conexión. Intenta nuevamente.');
    });
}

// Función para manejar el registro con fetch
function handleRegister(event) {
    event.preventDefault();
    console.log('Iniciando registro...');
    
    const formData = new FormData(event.target);
    
    fetch('php/registro.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(result) {
        console.log('Resultado del registro:', result);
        if (result.success) {
            // Registro exitoso
            showSuccessMessage(result.message);
            // Limpiar formulario
            event.target.reset();
            // Cambiar automáticamente a la pestaña de login después de 2 segundos
            setTimeout(function() {
                showLogin();
            }, 2000);
        } else {
            // Error en registro
            console.log('Error en registro:', result.message);
            showErrorMessage(result.message);
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showErrorMessage('Error de conexión. Intenta nuevamente.');
    });
}

// ===== TRANSFORMACIÓN DE INTERFAZ SPA =====

// Función para transformar la interfaz después del login (sin recargar página)
function transformToUserInterface(user) {
    console.log('Transformando interfaz para:', user);
    
    // Actualizar header
    updateHeader(user);
    
    // Transformar hero section
    transformHeroSection(user);
    
    // Agregar sección de panel de usuario
    addUserDashboardSection();
    
    // Agregar sección de habitaciones disponibles
    addAvailableRoomsSection();
    
    // Actualizar CTA section para usuarios logueados
    updateCTASection(user);
    
    console.log('Interfaz transformada completamente');
}

// Función para actualizar el header cuando el usuario está logueado
function updateHeader(user) {
    const navButtons = document.querySelector('.nav-buttons');
    if (navButtons) {
        navButtons.innerHTML = `
            <div class="user-menu">
                <span class="user-welcome">¡Hola, ${user.nombre}!</span>
                <button class="btn-user-menu" onclick="toggleUserDropdown()">
                    <i class="fas fa-user-circle"></i>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <a href="#" onclick="showUserProfile()">
                        <i class="fas fa-user"></i> Mi Perfil
                    </a>
                    <a href="#" onclick="showUserReservations()">
                        <i class="fas fa-calendar"></i> Mis Reservas
                    </a>
                    <a href="#" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        `;
    }
}

// Función para transformar el hero section para usuarios logueados
function transformHeroSection(user) {
    const heroContent = document.querySelector('.hero-content');
    if (heroContent) {
        heroContent.innerHTML = `
            <h1 class="hero-title">¡Bienvenido de vuelta, <span class="hotel-name">${user.nombre}</span>!</h1>
            <p class="hero-subtitle">Tu experiencia personalizada en KUCHIUYAS te espera</p>
            <p class="hero-description">Disfruta de servicios exclusivos para huéspedes registrados y gestiona tus reservas fácilmente.</p>
            <div class="hero-buttons">
                <button class="btn-primary" onclick="showReservationSection()">
                    <i class="fas fa-calendar-plus"></i> Nueva Reserva
                </button>
                <button class="btn-secondary" onclick="showMyReservations()">
                    <i class="fas fa-history"></i> Mis Reservas
                </button>
            </div>
        `;
    }
}

// Función para agregar sección de panel de usuario
function addUserDashboardSection() {
    const featuresSection = document.querySelector('.features');
    
    // Verificar si ya existe para evitar duplicados
    const existingSection = document.getElementById('userDashboard');
    if (existingSection) {
        return;
    }
    
    // Crear nueva sección de panel de usuario
    const userSection = document.createElement('section');
    userSection.className = 'user-dashboard';
    userSection.id = 'userDashboard';
    
    userSection.innerHTML = `
        <div class="container">
            <h2>Panel de Control</h2>
            <div class="dashboard-grid">
                <div class="dashboard-card" onclick="showReservationSection()">
                    <i class="fas fa-plus-circle"></i>
                    <h3>Nueva Reserva</h3>
                    <p>Busca y reserva habitaciones disponibles</p>
                </div>
                <div class="dashboard-card" onclick="showMyReservations()">
                    <i class="fas fa-list"></i>
                    <h3>Mis Reservas</h3>
                    <p>Ve y gestiona tus reservas actuales</p>
                </div>
                <div class="dashboard-card" onclick="showUserProfile()">
                    <i class="fas fa-user-edit"></i>
                    <h3>Mi Perfil</h3>
                    <p>Actualiza tu información personal</p>
                </div>
                <div class="dashboard-card" onclick="showSpecialOffers()">
                    <i class="fas fa-gift"></i>
                    <h3>Ofertas Especiales</h3>
                    <p>Descuentos exclusivos para ti</p>
                </div>
            </div>
        </div>
    `;
    
    // Insertar antes de la sección features
    if (featuresSection) {
        featuresSection.parentNode.insertBefore(userSection, featuresSection);
    }
}

// Función para agregar sección de habitaciones disponibles
function addAvailableRoomsSection() {
    const gallerySection = document.querySelector('.gallery');
    
    // Verificar si ya existen para evitar duplicados
    const existingReservation = document.getElementById('reservationSection');
    const existingReservations = document.getElementById('myReservationsSection');
    
    if (existingReservation && existingReservations) {
        return;
    }
    
    // Crear sección de reservas
    const reservationSection = document.createElement('section');
    reservationSection.className = 'reservation-section';
    reservationSection.id = 'reservationSection';
    reservationSection.style.display = 'none'; // Oculto por defecto
    
    reservationSection.innerHTML = `
        <div class="container">
            <h2><i class="fas fa-search"></i> Buscar Habitaciones Disponibles</h2>
            <div class="filtros-reserva">
                <div class="filtro-item">
                    <label for="fecha_ingreso_main">Fecha de Ingreso:</label>
                    <input type="date" id="fecha_ingreso_main" min="${new Date().toISOString().split('T')[0]}" required>
                </div>
                <div class="filtro-item">
                    <label for="fecha_salida_main">Fecha de Salida:</label>
                    <input type="date" id="fecha_salida_main" min="${new Date(Date.now() + 86400000).toISOString().split('T')[0]}" required>
                </div>
                <div class="filtro-item">
                    <label for="tipo_habitacion_main">Tipo de Habitación:</label>
                    <select id="tipo_habitacion_main">
                        <option value="">Todos los tipos</option>
                        <option value="1">Simple - $80/noche</option>
                        <option value="2">Doble - $120/noche</option>
                        <option value="3">Suite - $200/noche</option>
                    </select>
                </div>
                <button class="btn-buscar-main" onclick="buscarHabitacionesMain()">
                    <i class="fas fa-search"></i> Buscar Habitaciones
                </button>
            </div>
            <div id="habitaciones-resultado" class="habitaciones-grid">
                <div class="info-inicial">
                    <i class="fas fa-search"></i>
                    <h3>Busca habitaciones disponibles</h3>
                    <p>Selecciona las fechas de tu estadía para ver habitaciones disponibles</p>
                </div>
            </div>
        </div>
    `;
    
    // Insertar antes de la galería
    if (gallerySection) {
        gallerySection.parentNode.insertBefore(reservationSection, gallerySection);
    }
    
    // También crear sección de mis reservas
    const myReservationsSection = document.createElement('section');
    myReservationsSection.className = 'my-reservations-section';
    myReservationsSection.id = 'myReservationsSection';
    myReservationsSection.style.display = 'none';
    
    myReservationsSection.innerHTML = `
        <div class="container">
            <h2><i class="fas fa-list"></i> Mis Reservas</h2>
            <div id="mis-reservas-container" class="reservas-container">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando tus reservas...</p>
                </div>
            </div>
        </div>
    `;
    
    if (reservationSection.parentNode) {
        reservationSection.parentNode.insertBefore(myReservationsSection, reservationSection.nextSibling);
    }
    
    // Agregar modal para carrusel de imágenes en la interfaz SPA
    addCarruselModal();
}

// Función para agregar el modal del carrusel a la interfaz SPA
function addCarruselModal() {
    // Verificar si ya existe
    const existingModal = document.getElementById('carruselClienteModalSPA');
    if (existingModal) {
        return;
    }
    
    const carruselModal = document.createElement('div');
    carruselModal.id = 'carruselClienteModalSPA';
    carruselModal.className = 'carrusel-cliente-modal';
    carruselModal.innerHTML = `
        <div class="carrusel-cliente-content">
            <div class="carrusel-cliente-header">
                <h3 id="carruselClienteTituloSPA">Galería de Habitación</h3>
                <span class="cerrar-carrusel-cliente" onclick="cerrarCarruselClienteSPA()">&times;</span>
            </div>
            
            <div id="carruselClienteImagenesSPA" class="carrusel-cliente-imagenes">
                <!-- Las imágenes se cargarán aquí via JavaScript -->
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="cerrarCarruselClienteSPA()">Cerrar</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(carruselModal);
}

// Función para actualizar la sección CTA para usuarios logueados
function updateCTASection(user) {
    const ctaSection = document.querySelector('.cta .cta-content');
    if (ctaSection) {
        ctaSection.innerHTML = `
            <h2>¡Tu experiencia KUCHIUYAS está a un clic!</h2>
            <p>Explora nuestras habitaciones y haz tu próxima reserva</p>
            <button class="btn-cta" onclick="showReservationSection()">
                <i class="fas fa-search"></i> Explorar Habitaciones
            </button>
        `;
    }
}

// ===== FUNCIONES DE NAVEGACIÓN =====

// Funciones para el menú de usuario
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Funciones de navegación para usuarios logueados (interfaz dinámica)
function showReservationSection() {
    // Ocultar otras secciones
    hideAllUserSections();
    
    // Mostrar sección de reservas
    const reservationSection = document.getElementById('reservationSection');
    if (reservationSection) {
        reservationSection.style.display = 'block';
        reservationSection.scrollIntoView({ behavior: 'smooth' });
        
        // Establecer fechas por defecto
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const dayAfter = new Date(today);
        dayAfter.setDate(dayAfter.getDate() + 2);
        
        const fechaIngresoInput = document.getElementById('fecha_ingreso_main');
        const fechaSalidaInput = document.getElementById('fecha_salida_main');
        
        if (fechaIngresoInput) {
            fechaIngresoInput.value = tomorrow.toISOString().split('T')[0];
        }
        if (fechaSalidaInput) {
            fechaSalidaInput.value = dayAfter.toISOString().split('T')[0];
        }
    }
}

function showMyReservations() {
    // Ocultar otras secciones
    hideAllUserSections();
    
    // Mostrar sección de mis reservas
    const myReservationsSection = document.getElementById('myReservationsSection');
    if (myReservationsSection) {
        myReservationsSection.style.display = 'block';
        myReservationsSection.scrollIntoView({ behavior: 'smooth' });
        
        // Cargar reservas del usuario
        loadUserReservations();
    }
}

function hideAllUserSections() {
    const reservationSection = document.getElementById('reservationSection');
    const myReservationsSection = document.getElementById('myReservationsSection');
    
    if (reservationSection) reservationSection.style.display = 'none';
    if (myReservationsSection) myReservationsSection.style.display = 'none';
}

// ===== FUNCIONES DE BÚSQUEDA Y RESERVAS =====

// Función para buscar habitaciones en la interfaz principal
function buscarHabitacionesMain() {
    const fechaIngreso = document.getElementById('fecha_ingreso_main')?.value;
    const fechaSalida = document.getElementById('fecha_salida_main')?.value;
    const tipoHabitacion = document.getElementById('tipo_habitacion_main')?.value;
    
    // Validaciones
    if (!fechaIngreso || !fechaSalida) {
        showErrorMessage('Por favor selecciona las fechas de ingreso y salida');
        return;
    }
    
    if (new Date(fechaIngreso) >= new Date(fechaSalida)) {
        showErrorMessage('La fecha de salida debe ser posterior a la fecha de ingreso');
        return;
    }
    
    const container = document.getElementById('habitaciones-resultado');
    if (container) {
        container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i><p>Buscando habitaciones disponibles...</p></div>';
    }
    
    // Preparar datos para enviar
    const formData = new FormData();
    formData.append('fecha_ingreso', fechaIngreso);
    formData.append('fecha_salida', fechaSalida);
    formData.append('tipo_habitacion', tipoHabitacion || '');
    
    fetch('php/listar-habitaciones.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarHabitacionesMain(data.habitaciones, fechaIngreso, fechaSalida);
            showSuccessMessage(data.message);
        } else {
            if (container) {
                container.innerHTML = `
                    <div class="no-habitaciones">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>No hay habitaciones disponibles</h3>
                        <p>${data.message}</p>
                        <button class="btn-primary" onclick="showReservationSection()">
                            <i class="fas fa-redo"></i> Buscar otras fechas
                        </button>
                    </div>
                `;
            }
            showErrorMessage(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (container) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-wifi"></i>
                    <h3>Error de conexión</h3>
                    <p>No se pudo conectar con el servidor</p>
                    <button class="btn-secondary" onclick="buscarHabitacionesMain()">
                        <i class="fas fa-redo"></i> Reintentar
                    </button>
                </div>
            `;
        }
        showErrorMessage('Error de conexión. Intenta nuevamente.');
    });
}

// Función para mostrar habitaciones en la interfaz principal
function mostrarHabitacionesMain(habitaciones, fechaIngreso, fechaSalida) {
    const container = document.getElementById('habitaciones-resultado');
    if (!container) return;
    
    if (habitaciones.length === 0) {
        container.innerHTML = `
            <div class="no-habitaciones">
                <i class="fas fa-bed"></i>
                <h3>No hay habitaciones disponibles</h3>
                <p>No encontramos habitaciones para las fechas seleccionadas</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    habitaciones.forEach(habitacion => {
        const dias = Math.ceil((new Date(fechaSalida) - new Date(fechaIngreso)) / (1000 * 60 * 60 * 24));
        const precioTotal = parseFloat(habitacion.precio_por_noche) * dias;
        
        // Crear badge de galería si tiene más de una imagen
        const galeriaButton = habitacion.total_imagenes > 1 ? 
            `<button class="btn-galeria" onclick="event.stopPropagation(); abrirCarruselClienteSPA(${habitacion.id})">
                <i class="fas fa-images"></i> Ver ${habitacion.total_imagenes} fotos
            </button>` : '';
        
        html += `
            <div class="habitacion-card habitacion-disponible" onclick="abrirCarruselClienteSPA(${habitacion.id})">
                <div class="disponible-badge">Disponible</div>
                ${galeriaButton}
                <div class="habitacion-imagen">
                    ${habitacion.tiene_imagen_principal ? 
                        `<img src="${habitacion.url_imagen_principal}" 
                             alt="Habitación ${habitacion.numero}"
                             onerror="this.onerror=null; this.src='img/no-image.svg';">` :
                        '<div class="no-image"><i class="fas fa-bed"></i><span>Sin imagen</span></div>'
                    }
                </div>
                <div class="habitacion-info">
                    <h3>Habitación ${habitacion.numero} - ${habitacion.tipo_nombre}</h3>
                    <div class="habitacion-precio">
                        $${parseFloat(habitacion.precio_por_noche).toFixed(2)} / noche
                    </div>
                    <div class="habitacion-amenities">
                        <div class="amenity">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span>${habitacion.superficie}m²</span>
                        </div>
                        <div class="amenity">
                            <i class="fas fa-bed"></i>
                            <span>${habitacion.nro_de_camas} cama${habitacion.nro_de_camas > 1 ? 's' : ''}</span>
                        </div>
                        <div class="amenity">
                            <i class="fas fa-building"></i>
                            <span>Piso ${habitacion.piso}</span>
                        </div>
                        <div class="amenity">
                            <i class="fas fa-camera"></i>
                            <span>${habitacion.total_imagenes} foto${habitacion.total_imagenes !== 1 ? 's' : ''}</span>
                        </div>
                    </div>
                    ${habitacion.descripcion ? `
                    <div class="habitacion-descripcion">
                        <p><i class="fas fa-info-circle"></i> ${habitacion.descripcion}</p>
                    </div>` : ''}
                    <div class="precio-total">
                        <strong>Total por ${dias} noche${dias > 1 ? 's' : ''}: $${precioTotal.toFixed(2)}</strong>
                    </div>
                    <button class="btn-reservar" onclick="event.stopPropagation(); confirmarReservaMain(${habitacion.id}, '${habitacion.numero}', '${habitacion.tipo_nombre}', ${habitacion.precio_por_noche}, '${fechaIngreso}', '${fechaSalida}', ${precioTotal})">
                        <i class="fas fa-calendar-check"></i> Reservar Ahora
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ===== FUNCIONES DEL CARRUSEL =====

// Función para abrir carrusel del cliente en interfaz SPA
function abrirCarruselClienteSPA(habitacionId) {
    const habitacionCard = document.querySelector(`[onclick*="${habitacionId}"]`);
    const titulo = habitacionCard ? habitacionCard.querySelector('h3')?.textContent : `Habitación ${habitacionId}`;
    
    const tituloElement = document.getElementById('carruselClienteTituloSPA');
    if (tituloElement) {
        tituloElement.textContent = `Galería - ${titulo}`;
    }
    
    // Cargar imágenes de la habitación
    cargarImagenesCarruselClienteSPA(habitacionId);
    
    // Mostrar modal
    const modal = document.getElementById('carruselClienteModalSPA');
    if (modal) {
        modal.style.display = 'block';
    }
}

// Función para cargar imágenes en el carrusel del cliente SPA
function cargarImagenesCarruselClienteSPA(habitacionId) {
    const container = document.getElementById('carruselClienteImagenesSPA');
    if (!container) return;
    
    fetch(`admin/obtener-imagenes.php?habitacion_id=${habitacionId}`)
        .then(response => response.json())
        .then(imagenes => {
            const tiposPorOrden = {
                1: { tipo: 'principal', nombre: 'Imagen Principal' },
                2: { tipo: 'cama', nombre: 'Cama' },
                3: { tipo: 'bano', nombre: 'Baño' },
                4: { tipo: 'sala', nombre: 'Sala de Estar' }
            };
            
            // Limpiar contenedor de imágenes
            container.innerHTML = '';
            
            Object.keys(tiposPorOrden).forEach(orden => {
                const config = tiposPorOrden[orden];
                
                if (imagenes[orden]) {
                    const rutaImagen = `img/HABITACION${habitacionId}/${imagenes[orden]}`;
                    
                    // Verificar si la imagen existe
                    const img = new Image();
                    img.onload = function() {
                        const imageElement = document.createElement('div');
                        imageElement.className = 'carrusel-cliente-imagen';
                        imageElement.innerHTML = `
                            <img src="${rutaImagen}" alt="${config.nombre}" onclick="ampliarImagenSPA('${rutaImagen}', '${config.nombre}')">
                            <div class="imagen-label">${config.nombre}</div>
                        `;
                        container.appendChild(imageElement);
                    };
                    img.onerror = function() {
                        // Mostrar placeholder si la imagen no existe
                        const imageElement = document.createElement('div');
                        imageElement.className = 'carrusel-cliente-imagen no-disponible';
                        imageElement.innerHTML = `
                            <div class="no-imagen-placeholder">
                                <i class="fas fa-image"></i>
                                <span>No disponible</span>
                            </div>
                            <div class="imagen-label">${config.nombre}</div>
                        `;
                        container.appendChild(imageElement);
                    };
                    img.src = rutaImagen;
                } else {
                    // No hay imagen para este tipo
                    const imageElement = document.createElement('div');
                    imageElement.className = 'carrusel-cliente-imagen no-disponible';
                    imageElement.innerHTML = `
                        <div class="no-imagen-placeholder">
                            <i class="fas fa-image"></i>
                            <span>No disponible</span>
                        </div>
                        <div class="imagen-label">${config.nombre}</div>
                    `;
                    container.appendChild(imageElement);
                }
            });
        })
        .catch(error => {
            console.error('Error al cargar imágenes:', error);
            container.innerHTML = `
                <div class="error-carga">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error al cargar las imágenes</p>
                </div>
            `;
        });
}

// Función para ampliar imagen en SPA
function ampliarImagenSPA(src, titulo) {
    const modal = document.createElement('div');
    modal.className = 'modal-imagen-ampliada';
    modal.innerHTML = `
        <div class="modal-imagen-content">
            <span class="cerrar-ampliada" onclick="cerrarImagenAmpliadaSPA(this)">&times;</span>
            <h3>${titulo}</h3>
            <img src="${src}" alt="${titulo}">
        </div>
    `;
    document.body.appendChild(modal);
    
    // Mostrar modal
    setTimeout(() => modal.classList.add('show'), 10);
}

// Función para cerrar imagen ampliada en SPA
function cerrarImagenAmpliadaSPA(element) {
    const modal = element ? element.closest('.modal-imagen-ampliada') : document.querySelector('.modal-imagen-ampliada');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.remove(), 300);
    }
}

// Función para cerrar carrusel del cliente en SPA
function cerrarCarruselClienteSPA() {
    const modal = document.getElementById('carruselClienteModalSPA');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ===== FUNCIONES DE RESERVAS =====

// Función para confirmar reserva desde la interfaz principal
function confirmarReservaMain(habitacionId, numero, tipo, precioNoche, fechaIngreso, fechaSalida, precioTotal) {
    const dias = Math.ceil((new Date(fechaSalida) - new Date(fechaIngreso)) / (1000 * 60 * 60 * 24));
    
    const confirmacion = confirm(`
¿Confirmar reserva?

Habitación: ${numero} - ${tipo}
Fechas: ${fechaIngreso} al ${fechaSalida}
Duración: ${dias} noche${dias > 1 ? 's' : ''}
Precio por noche: ${precioNoche}
Total: ${precioTotal.toFixed(2)}

¿Deseas continuar con la reserva?
    `);
    
    if (confirmacion) {
        realizarReservaMain(habitacionId, fechaIngreso, fechaSalida, precioTotal);
    }
}

// Función para realizar la reserva
function realizarReservaMain(habitacionId, fechaIngreso, fechaSalida, precioTotal) {
    const observaciones = prompt('Observaciones adicionales (opcional):') || '';
    
    const formData = new FormData();
    formData.append('habitacion_id', habitacionId);
    formData.append('fecha_ingreso', fechaIngreso);
    formData.append('fecha_salida', fechaSalida);
    formData.append('precio_total', precioTotal);
    formData.append('observaciones', observaciones);
    
    // Mostrar loading
    showSuccessMessage('Procesando reserva...');
    
    fetch('php/insertar-reserva.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('¡Reserva creada exitosamente!');
            // Actualizar la búsqueda para reflejar la nueva disponibilidad
            setTimeout(() => {
                buscarHabitacionesMain();
            }, 2000);
        } else {
            showErrorMessage(data.message || 'Error al crear la reserva');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('Error de conexión al crear la reserva');
    });
}

// Función para cargar reservas del usuario
function loadUserReservations() {
    const container = document.getElementById('mis-reservas-container');
    if (!container) return;
    
    fetch('php/obtener-mis-reservas.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarMisReservas(data.reservas);
        } else {
            container.innerHTML = `
                <div class="no-reservas">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No tienes reservas aún</h3>
                    <p>¡Haz tu primera reserva y disfruta de nuestro hotel!</p>
                    <button class="btn-primary" onclick="showReservationSection()">
                        <i class="fas fa-plus-circle"></i> Hacer Reserva
                    </button>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="error-state">
                <i class="fas fa-wifi"></i>
                <h3>Error al cargar reservas</h3>
                <p>No se pudieron cargar tus reservas</p>
                <button class="btn-secondary" onclick="loadUserReservations()">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `;
    });
}

// Función para mostrar las reservas del usuario
function mostrarMisReservas(reservas) {
    const container = document.getElementById('mis-reservas-container');
    if (!container) return;
    
    if (reservas.length === 0) {
        container.innerHTML = `
            <div class="no-reservas">
                <i class="fas fa-calendar-times"></i>
                <h3>No tienes reservas aún</h3>
                <p>¡Haz tu primera reserva y disfruta de nuestro hotel!</p>
                <button class="btn-primary" onclick="showReservationSection()">
                    <i class="fas fa-plus-circle"></i> Hacer Reserva
                </button>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    reservas.forEach(reserva => {
        html += `
            <div class="reserva-card">
                <div class="reserva-imagen">
                    ${reserva.foto_principal ? 
                        `<img src="img/HABITACION${reserva.habitacion_id}/${reserva.foto_principal}" 
                             alt="Habitación ${reserva.habitacion_numero}"
                             onerror="this.onerror=null; this.src='img/no-image.svg';">` :
                        '<div class="no-image"><i class="fas fa-bed"></i></div>'
                    }
                </div>
                <div class="reserva-info">
                    <h3>Habitación ${reserva.habitacion_numero} - ${reserva.tipo_nombre}</h3>
                    <div class="reserva-detalles">
                        <p><i class="fas fa-calendar-check"></i> Check-in: ${formatDate(reserva.fecha_ingreso)}</p>
                        <p><i class="fas fa-calendar-times"></i> Check-out: ${formatDate(reserva.fecha_salida)}</p>
                        <p><i class="fas fa-dollar-sign"></i> Total: ${parseFloat(reserva.precio_total).toFixed(2)}</p>
                    </div>
                    <div class="reserva-estado">
                        <span class="estado-badge estado-${reserva.estado}">
                            ${reserva.estado.charAt(0).toUpperCase() + reserva.estado.slice(1)}
                        </span>
                    </div>
                    ${reserva.observaciones ? `
                        <div class="reserva-observaciones">
                            <p><strong>Observaciones:</strong> ${reserva.observaciones}</p>
                        </div>
                    ` : ''}
                </div>
                <div class="reserva-acciones">
                    ${reserva.estado === 'pendiente' ? `
                        <button class="btn-cancelar" onclick="cancelarReservaMain(${reserva.id})">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    ` : ''}
                    <button class="btn-detalles" onclick="verDetallesReserva(${reserva.id})">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </button>
                    <button class="btn-galeria-reserva" onclick="abrirCarruselClienteSPA(${reserva.habitacion_id})">
                        <i class="fas fa-images"></i> Ver Fotos
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Función para formatear fechas
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES');
}

// Función para cancelar reserva
function cancelarReservaMain(reservaId) {
    if (confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
        const formData = new FormData();
        formData.append('reserva_id', reservaId);
        
        fetch('php/cancelar-reserva.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('Reserva cancelada exitosamente');
                loadUserReservations(); // Recargar las reservas
            } else {
                showErrorMessage(data.message || 'Error al cancelar la reserva');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('Error de conexión al cancelar la reserva');
        });
    }
}

// Función para ver detalles de reserva
function verDetallesReserva(reservaId) {
    alert('Función de detalles - próximamente');
}

// ===== FUNCIONES DE USUARIO =====

// Función para cerrar sesión
function logout() {
    if (confirm('¿Estás seguro que deseas cerrar sesión?')) {
        showSuccessMessage('Cerrando sesión...');
        
        fetch('php/logout.php', {
            method: 'POST'
        })
        .then(response => {
            if (response.ok) {
                setTimeout(() => {
                    location.reload(); // Recargar página para volver al estado no logueado
                }, 1500);
            } else {
                showErrorMessage('Error al cerrar sesión');
            }
        })
        .catch(error => {
            console.error('Error al cerrar sesión:', error);
            showErrorMessage('Error de conexión al cerrar sesión');
        });
    }
}

function showUserProfile() {
    alert('Función de perfil - próximamente');
}

function showSpecialOffers() {
    alert('Ofertas especiales - próximamente');
}

function showUserReservations() {
    showMyReservations();
}

// ===== FUNCIONES DE MENSAJES =====

// Función para mostrar mensajes de éxito
function showSuccessMessage(message) {
    removeExistingMessages();
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message success';
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #27ae60;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 5px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        max-width: 400px;
        animation: slideIn 0.3s ease;
    `;
    messageDiv.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }, 4000);
}

// Función para mostrar mensajes de error
function showErrorMessage(message) {
    removeExistingMessages();
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message error';
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #e74c3c;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 5px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        max-width: 400px;
        animation: slideIn 0.3s ease;
    `;
    messageDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }, 5000);
}

// Función para remover mensajes existentes
function removeExistingMessages() {
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => {
        if (msg.parentNode) {
            msg.parentNode.removeChild(msg);
        }
    });
}

// ===== EVENT LISTENERS =====

// Manejar clicks fuera de los modales
window.onclick = function(event) {
    // Modal de login
    const modal = document.getElementById("modalLogin");
    if (event.target === modal) {
        cerrarModal();
    }
    
    // Cerrar dropdown del usuario al hacer clic fuera
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && !event.target.matches('.btn-user-menu') && !event.target.matches('.btn-user-menu *')) {
        dropdown.classList.remove('show');
    }
    
    // Cerrar carrusel del cliente al hacer clic fuera
    const carruselModal = document.getElementById('carruselClienteModalSPA');
    if (carruselModal && event.target === carruselModal) {
        cerrarCarruselClienteSPA();
    }
    
    // Cerrar imagen ampliada al hacer clic fuera
    if (event.target.classList.contains('modal-imagen-ampliada')) {
        cerrarImagenAmpliadaSPA();
    }
};

// Agregar estilos CSS para las animaciones de mensajes
const messageStyles = document.createElement('style');
messageStyles.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .message {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-weight: 500;
        border-radius: 8px !important;
    }
    
    .message i {
        font-size: 1.1em;
        flex-shrink: 0;
    }
`;

// Agregar estilos al documento cuando se carga
document.addEventListener('DOMContentLoaded', function() {
    document.head.appendChild(messageStyles);
    console.log('Sistema SPA Hotel KUCHIUYAS con carrusel de imágenes cargado correctamente ✨');
});

// ===== LOG DE INICIALIZACIÓN =====
console.log('Login.js cargado - Sistema SPA Hotel KUCHIUYAS con carrusel de imágenes ✨');