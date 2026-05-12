// client/js/auth.js
document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');
    const formRegistrazione = document.getElementById('form-registrazione');
    const btnVaiRegistrazione = document.getElementById('btn-vai-registrazione');
    const btnVaiLogin = document.getElementById('btn-vai-login');
    const btnIndietro = document.getElementById('btn-indietro');
    
    const pwdInput = document.getElementById('reg-password');
    const errorDiv = document.getElementById('pwd-error');
    
    const regexPassword = /^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/;

    // Gestione Form
    btnVaiRegistrazione.addEventListener('click', () => {
        formLogin.style.display = 'none';
        formRegistrazione.style.display = 'block';
    });

    btnVaiLogin.addEventListener('click', () => {
        formRegistrazione.style.display = 'none';
        formLogin.style.display = 'block';
    });

    // API History per tornare indietro
    btnIndietro.addEventListener('click', (e) => {
        e.preventDefault();
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = 'index.php'; // Cambia in base alla tua home vera
        }
    });

    // Validazione Password in tempo reale
    if(pwdInput) {
        pwdInput.addEventListener('input', (e) => {
            const pwd = e.target.value;
            if (pwd.length > 0 && !regexPassword.test(pwd)) {
                errorDiv.style.display = 'block';
                pwdInput.style.borderBottomColor = 'var(--error-red)';
            } else {
                errorDiv.style.display = 'none';
                pwdInput.style.borderBottomColor = 'var(--teak)';
            }
        });

        // Validazione al Submit
        formRegistrazione.addEventListener('submit', (e) => {
            if (!regexPassword.test(pwdInput.value)) {
                e.preventDefault(); 
                errorDiv.style.display = 'block';
                pwdInput.style.borderBottomColor = 'var(--error-red)';
                pwdInput.style.transform = 'translateX(5px)';
                setTimeout(() => pwdInput.style.transform = 'translateX(0)', 100);
            }
        });
    }
});