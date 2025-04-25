const LoginForm = document.getElementById("login-container");
const RegisterForm = document.getElementById("register-container");
function showLoginForm() {
    RegisterForm.style.visibility = "visible";
    LoginForm.style.visibility = "hidden";
}
function showRegisterForm() {
    RegisterForm.style.visibility = "hidden";
    LoginForm.style.visibility = "visible";
}