document.addEventListener('DOMContentLoaded', async () => {
    // Obtener el ID del área de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const areaId = urlParams.get('area');

    if (!areaId) {
        alert('No se especificó un área');
        window.location.href = 'areas.html';
        return;
    }

    // Referencias a elementos del DOM
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const asistentesForm = document.getElementById('asistentesForm');
    const newAsistenteForm = document.getElementById('newAsistenteForm');
    const backBtn = document.getElementById('backBtn');
    const tomarAsistenciaBtn = document.getElementById('tomarAsistenciaBtn');
    const guardarAsistenciaBtn = document.getElementById('guardarAsistenciaBtn');

    // Cargar información inicial
    await loadAreaInfo(areaId);
    await loadAsistentes(areaId);
    await cargarHistorialAsistencia(areaId); // Agregar carga inicial del historial

    // Toggle del formulario
    toggleFormBtn.addEventListener('click', () => {
        console.log('Toggle button clicked'); // Para depuración
        
        if (asistentesForm.style.display === 'none' || !asistentesForm.classList.contains('active')) {
            // Mostrar el formulario
            asistentesForm.style.display = 'block';
            // Usar setTimeout para asegurar que el display: block se aplique primero
            setTimeout(() => {
                asistentesForm.style.visibility = 'visible';
                asistentesForm.classList.add('active');
            }, 10);
            toggleFormBtn.textContent = 'Cancelar';
            toggleFormBtn.classList.add('btn-danger');
        } else {
            // Ocultar el formulario
            asistentesForm.classList.remove('active');
            asistentesForm.style.visibility = 'hidden';
            // Esperar a que termine la transición antes de ocultar completamente
            setTimeout(() => {
                asistentesForm.style.display = 'none';
            }, 300);
            toggleFormBtn.textContent = 'Agregar Asistente';
            toggleFormBtn.classList.remove('btn-danger');
            newAsistenteForm.reset(); // Limpiar el formulario
        }
    });

    // Manejar el envío del formulario
    newAsistenteForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevenir el envío del formulario
        
        const formData = {
            nombre: document.getElementById('nombreAsistente').value.trim(),
            email: document.getElementById('emailAsistente').value.trim(),
            area_id: areaId
        };

        try {
            const response = await fetch('../controllers/asistentes/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            console.log('Respuesta del servidor:', result);

            if (result.success) {
                // Solo limpiar el formulario y recargar la lista
                newAsistenteForm.reset();
                await loadAsistentes(areaId);
                alert('Asistente agregado exitosamente');
                
                // Mantener el formulario visible y dar foco al campo nombre
                document.getElementById('nombreAsistente').focus();
            } else {
                alert(result.message || 'Error al agregar asistente');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        }
    });

    // Configurar los demás event listeners
    setupEventListeners(areaId);

    // Manejar cierre del modal con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('asistenciaModal');
            if (modal.style.display === 'block') {
                modal.style.display = 'none';
            }
        }
    });

    // Cerrar modal al hacer clic fuera
    const modal = document.getElementById('asistenciaModal');
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Actualizar el event listener del botón guardar
    guardarAsistenciaBtn.addEventListener('click', async () => {
        try {
            const fecha = document.getElementById('fechaAsistencia').value;
            await guardarAsistenciaGeneral(areaId, fecha);
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            alert('Asistencia guardada exitosamente');
            await cargarHistorialAsistencia(areaId); // Recargar historial después de guardar
        } catch (error) {
            alert('Error al guardar la asistencia: ' + error.message);
        }
    });
});

async function loadAreaInfo(areaId) {
    try {
        const response = await fetch(`../controllers/areas/read_one.php?id=${areaId}`);
        const area = await response.json();
        
        document.getElementById('areaNombre').textContent = area.nombre;
        document.getElementById('areaDescripcion').textContent = area.descripcion || '';
    } catch (error) {
        console.error('Error:', error);
    }
}

async function loadAsistentes(areaId) {
    try {
        console.log('Cargando asistentes para el área:', areaId); // Debug

        const response = await fetch(`../controllers/asistentes/list.php?area=${areaId}`);
        const result = await response.json();
        
        console.log('Respuesta del servidor:', result); // Debug
        
        const container = document.getElementById('asistentesList');
        container.innerHTML = ''; // Limpiar el contenedor

        if (!result.success) {
            container.innerHTML = `<p class="error-message">${result.message || 'Error al cargar asistentes'}</p>`;
            return;
        }

        if (!result.asistentes || result.asistentes.length === 0) {
            container.innerHTML = '<p class="no-data">No hay asistentes registrados</p>';
            return;
        }

        result.asistentes.forEach(asistente => {
            const div = document.createElement('div');
            div.className = 'asistente-item';
            div.setAttribute('data-id', asistente.id);
            div.innerHTML = `
                <div class="asistente-info">
                    <span class="asistente-nombre">${asistente.nombre}</span>
                    <span class="asistente-email">${asistente.email}</span>
                </div>
                <div class="asistente-actions">
                    <button onclick="editAsistente(${asistente.id})" class="btn-secondary">Editar</button>
                    <button onclick="deleteAsistente(${asistente.id}, ${areaId})" class="btn-danger">Eliminar</button>
                </div>
            `;
            container.appendChild(div);
        });
    } catch (error) {
        console.error('Error al cargar asistentes:', error);
        const container = document.getElementById('asistentesList');
        container.innerHTML = '<p class="error-message">Error al cargar los asistentes</p>';
    }
}

async function deleteAsistente(id, areaId) {
    if (!confirm('¿Estás seguro de eliminar este asistente?')) return;
    
    try {
        const response = await fetch('/mtca/MTC-Assistance/MTC-Assistance/controllers/asistentes/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const data = await response.json();
        if (data.success) {
            loadAsistentes(areaId);
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar el asistente');
    }
}

function editAsistente(id) {
    // Buscar el elemento asistente por su data-id
    const asistenteItem = document.querySelector(`.asistente-item[data-id="${id}"]`);
    if (!asistenteItem) {
        console.error('No se encontró el asistente');
        return;
    }

    const nombre = asistenteItem.querySelector('.asistente-nombre').textContent;
    const email = asistenteItem.querySelector('.asistente-email').textContent;

    // Convertir el elemento en un formulario de edición
    asistenteItem.innerHTML = `
        <form class="edit-form" onsubmit="updateAsistente(event, ${id})">
            <div class="form-row">
                <input type="text" value="${nombre}" required>
                <input type="email" value="${email}" required>
                <button type="submit" class="btn-success">Guardar</button>
                <button type="button" onclick="cancelEdit(${id})" class="btn-secondary">Cancelar</button>
            </div>
        </form>
    `;
}

async function updateAsistente(event, id) {
    event.preventDefault();
    const form = event.target;
    const [nombre, email] = form.querySelectorAll('input');

    try {
        const response = await fetch('/mtca/MTC-Assistance/MTC-Assistance/controllers/asistentes/update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id,
                nombre: nombre.value,
                email: email.value
            })
        });

        const data = await response.json();
        if (data.success) {
            const areaId = new URLSearchParams(window.location.search).get('area');
            loadAsistentes(areaId);
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al actualizar el asistente');
    }
}

function cancelEdit(id) {
    const areaId = new URLSearchParams(window.location.search).get('area');
    loadAsistentes(areaId);
}

function createAsistenteCard(asistente) {
    const div = document.createElement('div');
    div.className = 'asistente-card';
    div.innerHTML = `
        <h3>${asistente.nombre}</h3>
        <p>${asistente.email}</p>
        <p>${asistente.telefono || 'Sin teléfono'}</p>
        <div class="asistente-actions">
            <button onclick="editAsistente(${asistente.id})" class="btn-secondary">Editar</button>
            <button onclick="deleteAsistente(${asistente.id})" class="btn-secondary">Eliminar</button>
        </div>
    `;
    return div;
}

function setupEventListeners(areaId) {
    const backBtn = document.getElementById('backBtn');
    const tomarAsistenciaBtn = document.getElementById('tomarAsistenciaBtn');
    
    backBtn.onclick = () => window.location.href = 'areas.html';
    tomarAsistenciaBtn.onclick = () => showAsistenciaModal(areaId);
}

async function showAsistenciaModal(areaId) {
    try {
        const fecha = document.getElementById('fechaAsistencia').value;
        if (!fecha) {
            alert('Por favor seleccione una fecha');
            return;
        }

        const modal = document.getElementById('asistenciaModal');
        const listaAsistencia = document.getElementById('listaAsistencia');
        const fechaDisplay = modal.querySelector('.fecha-asistencia');
        
        // Mostrar spinner y fecha
        listaAsistencia.innerHTML = '<div class="spinner"></div>';
        fechaDisplay.textContent = `Fecha: ${new Date(fecha).toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        })}`;
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevenir scroll del body

        // Cargar asistentes
        const response = await fetch(`../controllers/asistentes/list.php?area=${areaId}`);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Error al cargar asistentes');
        }

        // Actualizar lista
        actualizarListaAsistencia(result.asistentes, fecha);

        // Configurar botón de guardar
        const guardarBtn = document.getElementById('guardarAsistenciaBtn');
        guardarBtn.onclick = async () => {
            try {
                await guardarAsistenciaGeneral(areaId, fecha);
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                alert('Asistencia guardada exitosamente');
            } catch (error) {
                alert('Error al guardar la asistencia: ' + error.message);
            }
        };

    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar la asistencia: ' + error.message);
    }
}

// Agregar después de showAsistenciaModal
function actualizarListaAsistencia(asistentes, fecha) {
    const listaAsistencia = document.getElementById('listaAsistencia');
    
    if (!asistentes || asistentes.length === 0) {
        listaAsistencia.innerHTML = '<p class="no-data">No hay asistentes registrados para esta fecha</p>';
        return;
    }

    const asistenciaHTML = asistentes.map(asistente => `
        <div class="asistencia-row" data-id="${asistente.id}">
            <div class="asistente-info">
                <span class="asistente-nombre">${asistente.nombre}</span>
                <span class="asistente-email">${asistente.email}</span>
            </div>
            <div class="estado-buttons">
                <label class="radio-container">
                    <input type="radio" 
                           name="asistencia_${asistente.id}" 
                           value="presente" 
                           onchange="marcarAsistencia(${asistente.id}, 'presente')">
                    <span class="radio-label presente">Presente</span>
                </label>
                <label class="radio-container">
                    <input type="radio" 
                           name="asistencia_${asistente.id}" 
                           value="ausente" 
                           onchange="marcarAsistencia(${asistente.id}, 'ausente')">
                    <span class="radio-label ausente">Ausente</span>
                </label>
            </div>
        </div>
    `).join('');

    listaAsistencia.innerHTML = `
        <div class="asistencia-header">
            <h3>Lista de Asistencia</h3>
        </div>
        <div class="asistencia-grid">
            ${asistenciaHTML}
        </div>
    `;
}

// Función para marcar asistencia
async function marcarAsistencia(asistenteId, estado) {
    const fecha = document.getElementById('fechaAsistencia').value;
    const row = document.querySelector(`.asistencia-row[data-id="${asistenteId}"]`);
    if (!row) return;

    try {
        const response = await fetch('../controllers/asistencia/marcar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                asistente_id: asistenteId,
                estado: estado,
                fecha: fecha
            })
        });

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }

    } catch (error) {
        console.error('Error:', error);
        // Desmarcar el radio en caso de error
        const radio = row.querySelector(`input[type="radio"][value="${estado}"]`);
        if (radio) radio.checked = false;
        alert('Error al marcar asistencia: ' + error.message);
    }
}

// Actualizar la función de verificación
function verificarAsistenciasMarcadas() {
    const rows = document.querySelectorAll('.asistencia-row');
    let asistenciasMarcadas = false;

    rows.forEach(row => {
        const radioChecked = row.querySelector('input[type="radio"]:checked');
        if (radioChecked) {
            asistenciasMarcadas = true;
        }
    });

    return asistenciasMarcadas;
}

// Actualizar la función de guardar asistencia
async function guardarAsistenciaGeneral(areaId, fecha) {
    if (!verificarAsistenciasMarcadas()) {
        alert('Debe marcar al menos una asistencia antes de guardar');
        return;
    }

    const asistencias = [];
    const rows = document.querySelectorAll('.asistencia-row');

    rows.forEach(row => {
        const asistenteId = row.getAttribute('data-id');
        const radioChecked = row.querySelector('input[type="radio"]:checked');
        
        if (radioChecked) {
            asistencias.push({
                asistente_id: asistenteId,
                estado: radioChecked.value // 'presente' o 'ausente'
            });
        }
    });

    try {
        const response = await fetch('../controllers/asistencia/guardar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                area_id: areaId,
                fecha: fecha,
                asistencias: asistencias
            })
        });

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }

        // Actualizar el historial después de guardar exitosamente
        await cargarHistorialAsistencia(areaId);
        return result;
    } catch (error) {
        console.error('Error:', error);
        throw new Error('Error al guardar las asistencias: ' + error.message);
    }
}

// Actualizar la función cargarHistorialAsistencia
async function cargarHistorialAsistencia(areaId) {
    try {
        const response = await fetch(`../controllers/asistencia/historial.php?area=${areaId}`);
        const result = await response.json();

        const historialContainer = document.getElementById('historialContainer');
        
        if (!result.success) {
            throw new Error(result.message);
        }

        if (!result.historial || result.historial.length === 0) {
            historialContainer.innerHTML = '<p class="no-data">No hay registros de asistencia</p>';
            return;
        }

        const historialHTML = result.historial.map(registro => {
            const fecha = new Date(registro.fecha).toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            return `
                <div class="historial-item">
                    <div class="fecha">${fecha}</div>
                    <div class="stats">
                        <span class="presentes">
                            <i class="fas fa-check"></i> 
                            Presentes: ${registro.presentes}
                        </span>
                        <span class="ausentes">
                            <i class="fas fa-times"></i> 
                            Ausentes: ${registro.ausentes}
                        </span>
                    </div>
                    <button onclick="mostrarDetallesAsistencia('${registro.fecha}')" class="btn-secondary detalles-btn">
                        Ver Detalles
                    </button>
                </div>
            `;
        }).join('');

        historialContainer.innerHTML = historialHTML;

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('historialContainer').innerHTML = 
            '<p class="error-message">Error al cargar el historial: ' + error.message + '</p>';
    }
}

// Función para mostrar el modal de detalles
async function mostrarDetallesAsistencia(fecha) {
    const areaId = new URLSearchParams(window.location.search).get('area');
    try {
        const response = await fetch(`../controllers/asistencia/detalles.php?area=${areaId}&fecha=${fecha}`);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        // Mostrar modal de detalles
        const detallesModal = document.getElementById('detallesModal');
        const listaDetalles = document.getElementById('listaDetalles');
        
        const detallesHTML = result.asistencias.map(asistencia => `
            <div class="asistencia-row" data-id="${asistencia.asistente_id}">
                <div class="asistente-info">
                    <span class="asistente-nombre">${asistencia.nombre}</span>
                    <span class="asistente-email">${asistencia.email}</span>
                </div>
                <div class="estado-buttons">
                    <label class="radio-container">
                        <input type="radio" 
                               name="asistencia_${asistencia.asistente_id}" 
                               value="presente" 
                               ${asistencia.estado === 'presente' ? 'checked' : ''}
                               onchange="actualizarAsistencia(${asistencia.asistente_id}, 'presente', '${fecha}')">
                        <span class="radio-label presente">Presente</span>
                    </label>
                    <label class="radio-container">
                        <input type="radio" 
                               name="asistencia_${asistencia.asistente_id}" 
                               value="ausente" 
                               ${asistencia.estado === 'ausente' ? 'checked' : ''}
                               onchange="actualizarAsistencia(${asistencia.asistente_id}, 'ausente', '${fecha}')">
                        <span class="radio-label ausente">Ausente</span>
                    </label>
                </div>
            </div>
        `).join('');

        listaDetalles.innerHTML = detallesHTML;
        detallesModal.style.display = 'block';

    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar los detalles: ' + error.message);
    }
}

// Función para actualizar la asistencia individual
async function actualizarAsistencia(asistenteId, estado, fecha) {
    const row = document.querySelector(`.asistencia-row[data-id="${asistenteId}"]`);
    if (!row) return;

    const radioButtons = row.querySelectorAll('input[type="radio"]');
    const selectedRadio = row.querySelector(`input[type="radio"][value="${estado}"]`);

    try {
        const response = await fetch('../controllers/asistencia/actualizar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                asistente_id: asistenteId,
                estado: estado,
                fecha: fecha
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Error al actualizar asistencia');
        }

        // Actualizar UI
        radioButtons.forEach(radio => {
            radio.checked = (radio === selectedRadio);
        });

        // Actualizar el historial
        const areaId = new URLSearchParams(window.location.search).get('area');
        await cargarHistorialAsistencia(areaId);

    } catch (error) {
        console.error('Error:', error);
        // Revertir selección en caso de error
        radioButtons.forEach(radio => {
            radio.checked = false;
        });
        alert('Error al actualizar la asistencia: ' + error.message);
    }
}

// Función para cerrar el modal
function closeModal() {
    const modal = document.getElementById('asistenciaModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Event listeners para cerrar el modal
document.querySelector('.close').addEventListener('click', closeModal);
window.addEventListener('click', (e) => {
    const modal = document.getElementById('asistenciaModal');
    if (e.target === modal) {
        closeModal();
    }
});

function cerrarModalDetalles() {
    const detallesModal = document.getElementById('detallesModal');
    detallesModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Agregar event listener para cerrar con Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const detallesModal = document.getElementById('detallesModal');
        if (detallesModal.style.display === 'block') {
            cerrarModalDetalles();
        }
    }
});

// Cerrar al hacer clic fuera del modal
document.getElementById('detallesModal').addEventListener('click', (e) => {
    if (e.target.id === 'detallesModal') {
        cerrarModalDetalles();
    }
});

// Remover estas funciones del HTML y asegurarse que estén en asistencia.js
async function cargarAsistentes() {
    try {
        const response = await fetch(`../controllers/asistentes/list.php?area=${areaId}`);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        const lista = document.getElementById('asistentesList');
        lista.innerHTML = ''; // Limpiar lista existente

        result.asistentes.forEach(asistente => {
            const div = document.createElement('div');
            div.classList.add('asistencia-row');
            div.setAttribute('data-id', asistente.id);

            div.innerHTML = `
                <div class="asistente-info">
                    <span class="asistente-nombre">${asistente.nombre}</span>
                    <span class="asistente-email">${asistente.email}</span>
                </div>
                <div class="estado-buttons">
                    <label class="radio-container">
                        <input type="radio" 
                               name="asistencia_${asistente.id}" 
                               value="presente" 
                               onchange="marcarAsistencia(${asistente.id}, 'presente')">
                        <span class="radio-label presente">Presente</span>
                    </label>
                    <label class="radio-container">
                        <input type="radio" 
                               name="asistencia_${asistente.id}" 
                               value="ausente" 
                               onchange="marcarAsistencia(${asistente.id}, 'ausente')">
                        <span class="radio-label ausente">Ausente</span>
                    </label>
                </div>
            `;

            lista.appendChild(div);
        });
    } catch (error) {
        console.error('Error:', error);
        const lista = document.getElementById('asistentesList');
        lista.innerHTML = '<p class="error-message">Error al cargar los asistentes</p>';
    }
}