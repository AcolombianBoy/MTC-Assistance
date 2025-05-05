document.addEventListener('DOMContentLoaded', () => {
    checkSession();
    loadAreas();
    
    const modal = document.getElementById('areaModal');
    const addBtn = document.getElementById('addAreaBtn');
    const closeBtn = document.querySelector('.close');
    const areaForm = document.getElementById('areaForm');
    const logoutBtn = document.getElementById('logoutBtn');

    addBtn.onclick = () => {
        document.getElementById('modalTitle').textContent = 'Nueva Área';
        document.getElementById('areaId').value = '';
        areaForm.reset();
        modal.style.display = 'block';
    }

    closeBtn.onclick = () => modal.style.display = 'none';

    window.onclick = (e) => {
        if (e.target === modal) modal.style.display = 'none';
    }

    areaForm.onsubmit = async (e) => {
        e.preventDefault();
        const areaId = document.getElementById('areaId').value;
        const data = {
            nombre: document.getElementById('areaNombre').value,
            descripcion: document.getElementById('areaDescripcion').value
        };

        try {
            const url = areaId 
                ? '../controllers/areas/update.php'
                : '../controllers/areas/create.php';
            
            if (areaId) data.id = areaId;

            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                modal.style.display = 'none';
                loadAreas();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        }
    }

    logoutBtn.onclick = async () => {
        try {
            await fetch('../controllers/logout.php');
            window.location.href = 'auth.html';
        } catch (error) {
            console.error('Error:', error);
        }
    }
});

async function checkSession() {
    try {
        const response = await fetch('../controllers/check_session.php');
        const data = await response.json();
        
        if (!data.authenticated) {
            window.location.href = 'auth.html';
        } else {
            document.getElementById('userName').textContent = data.user_name;
        }
    } catch (error) {
        console.error('Error:', error);
        window.location.href = 'auth.html';
    }
}

async function loadAreas() {
    try {
        const response = await fetch('../controllers/areas/read.php');
        const data = await response.json();
        
        const container = document.getElementById('areasContainer');
        container.innerHTML = '';
        
        data.forEach(area => {
            container.appendChild(createAreaCard(area));
        });
    } catch (error) {
        console.error('Error:', error);
    }
}

function createAreaCard(area) {
    const div = document.createElement('div');
    div.className = 'area-card';
    div.innerHTML = `
        <h3>${area.nombre}</h3>
        <p>${area.descripcion || 'Sin descripción'}</p>
        <div class="area-actions">
            <button onclick="editArea(${area.id})" class="btn-secondary">Editar</button>
            <button onclick="deleteArea(${area.id})" class="btn-secondary">Eliminar</button>
            <button onclick="viewAssistance(${area.id})" class="btn-primary">Ver Asistencia</button>
        </div>
    `;
    return div;
}

async function editArea(id) {
    try {
        const response = await fetch(`../controllers/areas/read_one.php?id=${id}`);
        const area = await response.json();
        
        document.getElementById('modalTitle').textContent = 'Editar Área';
        document.getElementById('areaId').value = area.id;
        document.getElementById('areaNombre').value = area.nombre;
        document.getElementById('areaDescripcion').value = area.descripcion || '';
        
        document.getElementById('areaModal').style.display = 'block';
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar el área');
    }
}

async function deleteArea(id) {
    if (!confirm('¿Estás seguro de eliminar esta área?')) return;
    
    try {
        const response = await fetch('/mtca/MTC-Assistance/MTC-Assistance/controllers/areas/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadAreas();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar el área');
    }
}

function viewAssistance(id) {
    window.location.href = `asistencia.html?area=${id}`;
}