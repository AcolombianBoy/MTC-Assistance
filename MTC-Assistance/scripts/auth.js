document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login');
    const registerForm = document.getElementById('register');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        try {
            const response = await fetch('/mtca/MTC-Assistance/MTC-Assistance/controllers/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();
            
            if (data.success) {
                // Ruta absoluta desde la raíz del servidor
                window.location.href = '/mtca/MTC-Assistance/MTC-Assistance/public/areas.html';
            } else {
                showError(loginForm, data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showError(loginForm, 'Error al iniciar sesión. Intente nuevamente.');
        }
    });

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const name = document.getElementById('registerName').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (password !== confirmPassword) {
            showError(registerForm, 'Las contraseñas no coinciden');
            return;
        }

        try {
            const response = await fetch('/mtca/MTC-Assistance/MTC-Assistance/controllers/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name, email, password })
            });

            const data = await response.json();
            
            if (data.success) {
                alert('Registro exitoso. Por favor inicie sesión.');
                toggleForms();
            } else {
                showError(registerForm, data.message);
            }
        } catch (error) {
            showError(registerForm, 'Error al registrarse. Intente nuevamente.');
        }
    });
});

function toggleForms() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    loginForm.style.display = loginForm.style.display === 'none' ? 'block' : 'none';
    registerForm.style.display = registerForm.style.display === 'none' ? 'block' : 'none';
}

function showError(form, message) {
    const errorDiv = form.querySelector('.error') || document.createElement('div');
    errorDiv.className = 'error';
    errorDiv.textContent = message;
    
    if (!form.querySelector('.error')) {
        form.appendChild(errorDiv);
    }
}