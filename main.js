// Scripts personalizados aqui

document.addEventListener('DOMContentLoaded', function () {
    // Lógica de navegação simulada ou outras interações JS
    console.log('Protótipo SGCM carregado.');

    // Exemplo de navegação simulada para o login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const userType = document.getElementById('userType').value;
            if (userType === 'admin') {
                window.location.href = 'admin-dashboard.html';
            } else if (userType === 'medico') {
                window.location.href = 'medico-dashboard.html';
            } else if (userType === 'paciente') {
                window.location.href = 'paciente-dashboard.html';
            } else {
                alert('Selecione um tipo de usuário.');
            }
        });
    }
});
