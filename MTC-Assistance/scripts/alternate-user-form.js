const LoginForm = document.getElementById('login-container');
const RegisterForm = document.getElementById('register-container');

function showLoginForm() {
    LoginForm.style.transition = 'opacity 0.5s ease';
    RegisterForm.style.transition = 'opacity 0.5s ease';

    LoginForm.style.opacity = '0'; // Fade out del login
    setTimeout(() => {
        LoginForm.style.visibility = 'hidden'; // Oculta después del fade out
        RegisterForm.style.visibility = 'visible'; // Muestra el registro
        RegisterForm.style.opacity = '1'; // Fade in del registro
     }, 500); // Tiempo igual a la duración de la transición
}
function showRegisterForm() {
    RegisterForm.style.transition = 'opacity 0.5s ease';
    LoginForm.style.transition = 'opacity 0.5s ease';

    RegisterForm.style.opacity = '0'; // Fade out del registro
    setTimeout(() => {
        RegisterForm.style.visibility = 'hidden'; // Oculta después del fade out
        LoginForm.style.visibility = 'visible'; // Muestra el login
        LoginForm.style.opacity = '1'; // Fade in del login
     }, 500); // Tiempo igual a la duración de la transición
}