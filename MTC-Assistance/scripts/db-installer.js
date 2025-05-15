document.addEventListener('DOMContentLoaded', function() {
    // Verificar si es la primera vez que se ejecuta
    const dbInstalled = localStorage.getItem('db_installed');
    
    if (!dbInstalled) {
        // Mostrar modal de instalación
        const modal = document.createElement('div');
        modal.className = 'modal installer-modal';
        modal.style.display = 'block';
        
        modal.innerHTML = `
            <div class="modal-content">
                <h2>Instalación inicial</h2>
                <p>Parece que es la primera vez que ejecutas esta aplicación. Vamos a configurar la base de datos para ti.</p>
                <div id="installProgress">
                    <div class="progress-bar">
                        <div class="progress" style="width: 0%"></div>
                    </div>
                    <div class="status">Preparando instalación...</div>
                </div>
                <div class="buttons" style="display: none;">
                    <button id="continueBtn" class="btn-primary">Continuar</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Iniciar la instalación
        setTimeout(() => {
            installDatabase();
        }, 1000);
    }
    
    async function installDatabase() {
        const progressBar = document.querySelector('.progress');
        const status = document.querySelector('.status');
        const buttons = document.querySelector('.buttons');
        
        // Paso 1: Crear la base de datos inicial
        updateProgress(20, 'Creando tablas básicas...');
        
        try {
            const step1 = await fetch('install/create_db.php');
            const result1 = await step1.json();
            
            if (!result1.success) {
                showError('Error al crear la base de datos: ' + result1.message);
                return;
            }
            
            // Paso 2: Actualizar para añadir organizaciones
            updateProgress(60, 'Configurando sistema de organizaciones...');
            
            const step2 = await fetch('install/update_db.php');
            const result2 = await step2.json();
            
            if (!result2.success) {
                showError('Error al actualizar la base de datos: ' + result2.message);
                return;
            }
            
            // Finalizando
            updateProgress(100, 'Instalación completada exitosamente');
            
            // Marcar como instalada
            localStorage.setItem('db_installed', 'true');
            
            // Mostrar botón de continuar
            buttons.style.display = 'block';
            document.getElementById('continueBtn').addEventListener('click', function() {
                window.location.reload();
            });
            
        } catch (error) {
            showError('Error durante la instalación: ' + error.message);
        }
    }
    
    function updateProgress(percentage, message) {
        const progressBar = document.querySelector('.progress');
        const status = document.querySelector('.status');
        
        progressBar.style.width = percentage + '%';
        status.textContent = message;
    }
    
    function showError(message) {
        const status = document.querySelector('.status');
        const progressBar = document.querySelector('.progress');
        
        status.textContent = message;
        status.style.color = 'red';
        progressBar.style.backgroundColor = '#ff4444';
    }
});
