document.addEventListener('DOMContentLoaded', () => {
    const areaId = new URLSearchParams(window.location.search).get('area');
    if (!areaId) {
        window.location.href = 'areas.html';
        return;
    }

    setupEventListeners(areaId);
    loadAreaInfo(areaId);
    loadAsistentes(areaId);
    loadHistorialAsistencias(areaId); // Cargar historial al inicio
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
        const response = await fetch(`../controllers/asistencias/read-as.php?area_id=${areaId}`);
        const asistentes = await response.json();
        
        const container = document.getElementById('asistentesList');
        container.innerHTML = '';
        
        asistentes.forEach(asistente => {
            const div = document.createElement('div');
            div.className = 'asistente-item';
            // Agregamos el data-id al div principal
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
        console.error('Error:', error);
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
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const asistentesForm = document.getElementById('asistentesForm');
    const newAsistenteForm = document.getElementById('newAsistenteForm');
    const backBtn = document.getElementById('backBtn');
    const tomarAsistenciaBtn = document.getElementById('tomarAsistenciaBtn');
    const guardarAsistenciaBtn = document.getElementById('guardarAsistenciaBtn');

    toggleFormBtn.addEventListener('click', () => {
        asistentesForm.style.display = asistentesForm.style.display === 'none' ? 'block' : 'none';
    });

    newAsistenteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const nombre = document.getElementById('nombreAsistente').value;
        const email = document.getElementById('emailAsistente').value;

        try {
            const response = await fetch('/mtca/MTC-Assistance/MTC-Assistance/controllers/asistentes/create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre, email, area_id: areaId })
            });

            const data = await response.json();
            if (data.success) {
                newAsistenteForm.reset();
                asistentesForm.style.display = 'none';
                loadAsistentes(areaId);
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al agregar asistente');
        }
    });

    backBtn.onclick = () => window.location.href = 'areas.html';
    tomarAsistenciaBtn.onclick = () => showAsistenciaModal(areaId);
}

async function showAsistenciaModal(areaId) {
    const fecha = document.getElementById('fechaAsistencia').value;
    if (!fecha) {
        alert('Por favor seleccione una fecha');
        return;
    }

    try {
        const response = await fetch(`../controllers/asistencias/read.php?area_id=${areaId}&fecha=${fecha}`);
        const asistentes = await response.json();
        
        const listaAsistencia = document.getElementById('listaAsistencia');
        listaAsistencia.innerHTML = `
            <h3>Fecha: ${new Date(fecha).toLocaleDateString()}</h3>
            <div class="asistencia-grid">
                ${asistentes.map(asistente => `
                    <div class="asistencia-row" data-id="${asistente.id}" data-estado="${asistente.estado || 'ausente'}">
                        <span class="asistente-nombre">${asistente.nombre}</span>
                        <div class="estado-buttons">
                            <button type="button" 
                                    class="estado-btn presente ${asistente.estado === 'presente' ? 'active' : ''}"
                                    onclick="marcarAsistencia(${asistente.id}, 'presente', this)">
                                Presente
                            </button>
                            <button type="button" 
                                    class="estado-btn ausente ${asistente.estado === 'ausente' ? 'active' : ''}"
                                    onclick="marcarAsistencia(${asistente.id}, 'ausente', this)">
                                Ausente
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        // Configurar el botón de guardar
        const guardarBtn = document.getElementById('guardarAsistenciaBtn');
        guardarBtn.onclick = () => guardarAsistencia(areaId);

        // Mostrar el modal
        const modal = document.getElementById('asistenciaModal');
        modal.style.display = 'block';

        // Configurar el botón de cerrar
        const closeBtn = modal.querySelector('.close');
        closeBtn.onclick = () => modal.style.display = 'none';
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar la asistencia');
    }
}

function marcarAsistencia(asistenteId, estado, btn) {
    const row = btn.closest('.asistencia-row');
    
    // Remover clase active de todos los botones en este contenedor
    row.querySelectorAll('.estado-btn').forEach(b => b.classList.remove('active'));
    
    // Agregar clase active al botón clickeado
    btn.classList.add('active');
    
    // Guardar el estado en el dataset de la fila
    row.dataset.estado = estado;
}

async function guardarAsistencia(areaId) {
    const fecha = document.getElementById('fechaAsistencia').value;
    if (!fecha) {
        alert('Seleccione una fecha');
        return;
    }

    const asistencias = [];
    document.querySelectorAll('.asistencia-row').forEach(row => {
        asistencias.push({
            asistente_id: row.dataset.id,
            estado: row.dataset.estado || 'ausente'
        });
    });

    try {
        const response = await fetch('../controllers/asistencias/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                area_id: areaId,
                fecha: fecha,
                asistencias: asistencias
            })
        });
        
        const result = await response.json();
        if (result.success) {
            alert('Asistencia guardada correctamente');
            document.getElementById('asistenciaModal').style.display = 'none';
            await loadHistorialAsistencias(areaId); // Recargar el historial
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar la asistencia');
    }
}

async function loadHistorialAsistencias(areaId) {
    try {
        const response = await fetch(`../controllers/asistencias/historial.php?area_id=${areaId}`);
        const historial = await response.json();
        
        const container = document.getElementById('historialContainer');
        container.innerHTML = '';

        if (historial.length === 0) {
            container.innerHTML = '<p class="no-data">No hay registros de asistencia</p>';
            return;
        }
        
        historial.forEach(registro => {
            const div = document.createElement('div');
            div.className = 'historial-item';
            div.innerHTML = `
                <span class="historial-fecha">
                    ${new Date(registro.fecha).toLocaleDateString()}
                </span>
                <div class="historial-stats">
                    <span class="presentes">
                        ${registro.presentes} presentes
                    </span>
                    <span class="ausentes">
                        ${registro.ausentes} ausentes
                    </span>
                </div>
                <button onclick="verDetalleAsistencia('${registro.fecha}', ${areaId})" 
                        class="btn-secondary">
                    Ver Detalle
                </button>
            `;
            container.appendChild(div);
        });
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('historialContainer').innerHTML = 
            '<p class="error">Error al cargar el historial</p>';
    }
}

async function verDetalleAsistencia(fecha, areaId) {
    document.getElementById('fechaAsistencia').value = fecha;
    await showAsistenciaModal(areaId);
}