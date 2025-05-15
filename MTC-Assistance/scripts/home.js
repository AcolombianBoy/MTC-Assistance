document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const organizationsContainer = document.getElementById('organizationsContainer');
    const userNameElement = document.getElementById('userName');
    const addOrgBtn = document.getElementById('addOrgBtn');
    const joinOrgBtn = document.getElementById('joinOrgBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    
    // Referencias a modales
    const orgModal = document.getElementById('orgModal');
    const joinModal = document.getElementById('joinModal');
    const closeButtons = document.querySelectorAll('.close');
    
    // Referencias a formularios
    const orgForm = document.getElementById('orgForm');
    const joinForm = document.getElementById('joinForm');
    const generateCodeBtn = document.getElementById('generateCodeBtn');
    
    // Verificar la sesión al cargar
    checkSession();
    
    // Cargar organizaciones
    loadOrganizations();
    
    // Event Listeners
    addOrgBtn.addEventListener('click', openOrgModal);
    joinOrgBtn.addEventListener('click', openJoinModal);
    logoutBtn.addEventListener('click', logout);
    generateCodeBtn.addEventListener('click', generateRandomCode);
    
    // Cerrar modales
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            orgModal.style.display = 'none';
            joinModal.style.display = 'none';
        });
    });
    
    // Cuando se hace clic fuera del modal
    window.addEventListener('click', function(event) {
        if (event.target == orgModal) {
            orgModal.style.display = 'none';
        }
        if (event.target == joinModal) {
            joinModal.style.display = 'none';
        }
    });
    
    // Manejar envío del formulario de organización
    orgForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('orgId').value;
        const nombre = document.getElementById('orgNombre').value;
        const descripcion = document.getElementById('orgDescripcion').value;
        const codigo = document.getElementById('orgCodigo').value;
        
        if (id) {
            updateOrganization(id, nombre, descripcion);
        } else {
            createOrganization(nombre, descripcion, codigo);
        }
    });
    
    // Manejar envío del formulario para unirse a organización
    joinForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const codigo = document.getElementById('joinCodigo').value;
        joinOrganization(codigo);
    });
    
    // Función para verificar la sesión
    function checkSession() {
        fetch('../controllers/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.logged_in) {
                userNameElement.textContent = data.user_name;
            } else {
                window.location.href = 'auth.html';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al verificar la sesión', 'error');
        });
    }
    
    // Función para cargar organizaciones
    function loadOrganizations() {
        // Primero intentamos con el endpoint normal
        fetch('../controllers/organizaciones/read.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text(); // Obtenemos texto primero para verificar
        })
        .then(text => {
            try {
                // Intentamos parsear como JSON
                const data = JSON.parse(text);
                if (data.success) {
                    displayOrganizations(data.data);
                } else {
                    // Si hay un mensaje de error en el JSON, lo mostramos
                    organizationsContainer.innerHTML = `
                        <div class="empty-state">
                            <h2>No tienes organizaciones</h2>
                            <p>Crea una nueva organización o únete a una existente utilizando un código de invitación.</p>
                            <button class="btn-primary" onclick="document.getElementById('addOrgBtn').click()">
                                Crear Organización
                            </button>
                            <div class="error-message">${data.message || 'Error desconocido'}</div>
                        </div>
                    `;
                    console.error('Error del servidor:', data.message);
                }
            } catch (e) {
                // Si no es JSON válido, intentamos con el endpoint de depuración
                console.error('Error al parsear JSON:', e);
                console.log('Respuesta del servidor:', text);
                
                // Intentamos con el endpoint de depuración
                return fetch('../controllers/organizaciones/debug-read.php')
                    .then(response => response.json())
                    .then(debugData => {
                        console.log('Datos de depuración:', debugData);
                        
                        if (debugData.success) {
                            displayOrganizations(debugData.data);
                        } else {
                            organizationsContainer.innerHTML = `
                                <div class="empty-state">
                                    <h2>Problema detectado</h2>
                                    <p>${debugData.message}</p>
                                    <p>Ve a <a href="../public/debug-org.php" target="_blank">la página de depuración</a> para más información.</p>
                                </div>
                            `;
                        }
                    });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            organizationsContainer.innerHTML = `
                <div class="empty-state">
                    <h2>Error al cargar organizaciones</h2>
                    <p>${error.message}</p>
                    <p>Ve a <a href="../public/debug-org.php" target="_blank">la página de depuración</a> para más información.</p>
                </div>
            `;
            showToast('Error al cargar las organizaciones: ' + error.message, 'error');
        });
    }
    
    // Función para mostrar las organizaciones
    function displayOrganizations(organizations) {
        organizationsContainer.innerHTML = '';
        
        if (organizations.length === 0) {
            organizationsContainer.innerHTML = `
                <div class="empty-state">
                    <h2>No tienes organizaciones</h2>
                    <p>Crea una nueva organización o únete a una existente utilizando un código de invitación.</p>
                    <button class="btn-primary" onclick="document.getElementById('addOrgBtn').click()">
                        Crear Organización
                    </button>
                </div>
            `;
            return;
        }
          organizations.forEach(org => {
            const adminControls = org.rol === 'admin' ? `
                <button class="btn-secondary" onclick="editOrganization(${org.id})">Editar</button>
                <button class="btn-danger" onclick="deleteOrganization(${org.id})">Eliminar</button>
            ` : (org.rol === 'editor' ? `
                <button class="btn-secondary" onclick="editOrganization(${org.id})">Editar</button>
            ` : '');
            
            const card = document.createElement('div');
            card.className = 'organization-card';
            card.innerHTML = `
                <div class="organization-header">
                    <h3 class="organization-title">${org.nombre}</h3>
                    <span class="organization-badge">${org.rol}</span>
                </div>
                <p class="organization-description">${org.descripcion || 'Sin descripción'}</p>
                <div class="organization-code">
                    <span>${org.codigo_unico}</span>
                    <button class="copy-btn" onclick="copyToClipboard('${org.codigo_unico}')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>                <div class="organization-actions">
                    <button class="btn-primary" onclick="enterOrganization(${org.id})">Entrar</button>
                    <button class="btn-secondary" onclick="showOrganizationStats(${org.id})">Estadísticas</button>
                    ${adminControls}
                </div>
            `;
            organizationsContainer.appendChild(card);
        });
    }
    
    // Función para crear una organización
    function createOrganization(nombre, descripcion, codigo) {
        const data = {
            nombre: nombre,
            descripcion: descripcion
        };
        
        if (codigo.trim() !== '') {
            data.codigo_unico = codigo;
        }
        
        fetch('../controllers/organizaciones/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Organización creada con éxito', 'success');
                orgModal.style.display = 'none';
                orgForm.reset();
                loadOrganizations();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al crear la organización', 'error');
        });
    }
    
    // Función para actualizar una organización
    function updateOrganization(id, nombre, descripcion) {
        fetch('../controllers/organizaciones/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                nombre: nombre,
                descripcion: descripcion
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Organización actualizada con éxito', 'success');
                orgModal.style.display = 'none';
                orgForm.reset();
                document.getElementById('orgId').value = '';
                document.getElementById('modalTitle').textContent = 'Nueva Organización';
                loadOrganizations();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al actualizar la organización', 'error');
        });
    }
    
    // Función para editar una organización
    window.editOrganization = function(id) {
        fetch(`../controllers/organizaciones/read_one.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const org = data.data;
                document.getElementById('orgId').value = org.id;
                document.getElementById('orgNombre').value = org.nombre;
                document.getElementById('orgDescripcion').value = org.descripcion;
                document.getElementById('orgCodigo').value = org.codigo_unico;
                document.getElementById('orgCodigo').disabled = true; // No permitir cambiar el código
                document.getElementById('generateCodeBtn').disabled = true;
                document.getElementById('modalTitle').textContent = 'Editar Organización';
                
                orgModal.style.display = 'block';
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al cargar los datos de la organización', 'error');
        });
    };
    
    // Función para entrar a una organización
    window.enterOrganization = function(id) {
        // Guardar ID en localStorage para uso futuro
        localStorage.setItem('currentOrganizationId', id);
        
        // Redirigir a la página de áreas
        window.location.href = 'areas.html';
    };
    
    // Función para unirse a una organización
    function joinOrganization(codigo) {
        fetch('../controllers/organizaciones/join.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                codigo_unico: codigo
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                joinModal.style.display = 'none';
                joinForm.reset();
                loadOrganizations();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al unirse a la organización', 'error');
        });
    }
    
    // Función para generar código aleatorio
    function generateRandomCode() {
        // Generar un código aleatorio de 8 caracteres
        const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        let code = '';
        for (let i = 0; i < 8; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        document.getElementById('orgCodigo').value = code;
    }
    
    // Función para abrir el modal de nueva organización
    function openOrgModal() {
        // Restablecer formulario
        orgForm.reset();
        document.getElementById('orgId').value = '';
        document.getElementById('orgCodigo').disabled = false;
        document.getElementById('generateCodeBtn').disabled = false;
        document.getElementById('modalTitle').textContent = 'Nueva Organización';
        
        // Mostrar modal
        orgModal.style.display = 'block';
    }
    
    // Función para abrir el modal de unirse a organización
    function openJoinModal() {
        joinForm.reset();
        joinModal.style.display = 'block';
    }
    
    // Función para cerrar sesión
    function logout() {
        fetch('../controllers/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'auth.html';
            } else {
                showToast('Error al cerrar sesión', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al cerrar sesión', 'error');
        });
    }
    
    // Función para copiar al portapapeles
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text)
        .then(() => {
            showToast('Código copiado al portapapeles', 'success');
        })
        .catch(err => {
            console.error('Error al copiar: ', err);
            showToast('Error al copiar el código', 'error');
        });
    };
    
    // Función para mostrar notificaciones toast
    function showToast(message, type = 'success') {
        // Eliminar toasts existentes
        const existingToast = document.querySelector('.toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Crear nuevo toast
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${type === 'success' ? '✓' : '✕'}</span>
            <span class="toast-message">${message}</span>
        `;
        
        // Añadir al cuerpo del documento
        document.body.appendChild(toast);
        
        // Mostrar con animación
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
    // Función para eliminar una organización
    window.deleteOrganization = function(id) {
        if (!confirm('¿Estás seguro de eliminar esta organización? Esta acción no se puede deshacer y se eliminarán todos los datos asociados.')) {
            return;
        }
        
        fetch('../controllers/organizaciones/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Organización eliminada con éxito', 'success');
                loadOrganizations();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al eliminar la organización', 'error');
        });
    };
    
    // Función para mostrar estadísticas de la organización
    window.showOrganizationStats = function(id) {
        fetch(`../controllers/organizaciones/stats.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Crear modal de estadísticas
                const statsModal = document.createElement('div');
                statsModal.className = 'modal';
                statsModal.id = 'statsModal';
                
                // Preparar datos de asistencia
                const asistencias = data.data.asistencias_semana;
                const presentes = parseInt(asistencias.presentes) || 0;
                const ausentes = parseInt(asistencias.ausentes) || 0;
                const justificados = parseInt(asistencias.justificados) || 0;
                const total = presentes + ausentes + justificados;
                
                // Calcular porcentajes
                const porcentajePresente = total > 0 ? Math.round((presentes / total) * 100) : 0;
                const porcentajeAusente = total > 0 ? Math.round((ausentes / total) * 100) : 0;
                const porcentajeJustificado = total > 0 ? Math.round((justificados / total) * 100) : 0;
                
                // Crear contenido
                let ultimasAsistencias = '';
                if (data.data.ultimas_asistencias.length > 0) {
                    data.data.ultimas_asistencias.forEach(a => {
                        ultimasAsistencias += `
                            <li>
                                <span class="bold">${a.asistente}</span> - 
                                <span class="${a.estado}">${a.estado}</span> - 
                                ${a.area} (${new Date(a.fecha).toLocaleDateString()})
                            </li>
                        `;
                    });
                } else {
                    ultimasAsistencias = '<li>No hay registros de asistencia recientes</li>';
                }
                
                statsModal.innerHTML = `
                    <div class="modal-content stats-modal">
                        <span class="close">&times;</span>
                        <h2>Estadísticas de la Organización</h2>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">${data.data.total_areas}</div>
                                <div class="stat-label">Áreas</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-value">${data.data.total_asistentes}</div>
                                <div class="stat-label">Asistentes</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-value">${total}</div>
                                <div class="stat-label">Asistencias registradas (7 días)</div>
                            </div>
                        </div>
                        
                        <div class="attendance-chart">
                            <h3>Asistencia de la última semana</h3>
                            <div class="chart-bars">
                                <div class="chart-bar presente" style="width: ${porcentajePresente}%">
                                    <span>${presentes} (${porcentajePresente}%)</span>
                                </div>
                                <div class="chart-bar ausente" style="width: ${porcentajeAusente}%">
                                    <span>${ausentes} (${porcentajeAusente}%)</span>
                                </div>
                                <div class="chart-bar justificado" style="width: ${porcentajeJustificado}%">
                                    <span>${justificados} (${porcentajeJustificado}%)</span>
                                </div>
                            </div>
                            <div class="chart-legend">
                                <span class="legend-item presente">Presentes</span>
                                <span class="legend-item ausente">Ausentes</span>
                                <span class="legend-item justificado">Justificados</span>
                            </div>
                        </div>
                        
                        <div class="recent-activity">
                            <h3>Actividad reciente</h3>
                            <ul class="activity-list">
                                ${ultimasAsistencias}
                            </ul>
                        </div>
                        
                        <div class="modal-footer">
                            <button class="btn-primary" onclick="enterOrganization(${id})">Acceder a la organización</button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(statsModal);
                statsModal.style.display = 'block';
                
                // Cerrar modal con X
                const closeBtn = statsModal.querySelector('.close');
                closeBtn.addEventListener('click', () => {
                    statsModal.style.display = 'none';
                    setTimeout(() => {
                        statsModal.remove();
                    }, 300);
                });
                
                // Cerrar modal haciendo click afuera
                window.addEventListener('click', function(event) {
                    if (event.target == statsModal) {
                        statsModal.style.display = 'none';
                        setTimeout(() => {
                            statsModal.remove();
                        }, 300);
                    }
                });
            } else {
                showToast(data.message || 'Error al cargar estadísticas', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al cargar estadísticas', 'error');
        });
    };
});
