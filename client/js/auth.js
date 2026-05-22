// client/js/auth.js
document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');
    const formRegistrazione = document.getElementById('form-registrazione');
    const btnVaiRegistrazione = document.getElementById('btn-vai-registrazione');
    const btnVaiLogin = document.getElementById('btn-vai-login');
    const btnIndietro = document.getElementById('btn-indietro');
    
    const pwdInput = document.getElementById('reg-password');
    const errorDiv = document.getElementById('pwd-error');
    
    // Regola per la password: Minimo 8 caratteri, 1 Maiuscola, 1 Carattere Speciale
    const regexPassword = /^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/;

    // Gestione transizione form (Da Login a Registrazione e viceversa)
    btnVaiRegistrazione.addEventListener('click', () => {
        formLogin.style.display = 'none';
        formRegistrazione.style.display = 'block';
    });

    btnVaiLogin.addEventListener('click', () => {
        formRegistrazione.style.display = 'none';
        formLogin.style.display = 'block';
    });

    // API History per tornare indietro in sicurezza
    btnIndietro.addEventListener('click', (e) => {
        e.preventDefault();
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = 'index.php'; // Ritorno sicuro alla home
        }
    });

    // Validazione Password in tempo reale con i colori del nuovo tema (Deep Ocean)
    if(pwdInput) {
        pwdInput.addEventListener('input', (e) => {
            const pwd = e.target.value;
            // Se l'utente sta scrivendo ma la password non rispetta le regole
            if (pwd.length > 0 && !regexPassword.test(pwd)) {
                errorDiv.style.display = 'block';
                pwdInput.style.borderColor = 'var(--error-red)';
            } 
            // Se la password è corretta o il campo è vuoto
            else {
                errorDiv.style.display = 'none';
                pwdInput.style.borderColor = 'var(--cyan-glow)'; // Si illumina di azzurro
            }
        });

        // Validazione al Submit con animazione "shake" (tremolio)
        formRegistrazione.addEventListener('submit', (e) => {
            if (!regexPassword.test(pwdInput.value)) {
                e.preventDefault(); // Blocca l'invio del modulo
                errorDiv.style.display = 'block';
                pwdInput.style.borderColor = 'var(--error-red)';
                
                // Effetto tremolio in caso di errore per attirare l'attenzione
                pwdInput.style.transform = 'translateX(10px)';
                setTimeout(() => pwdInput.style.transform = 'translateX(-10px)', 100);
                setTimeout(() => pwdInput.style.transform = 'translateX(10px)', 200);
                setTimeout(() => pwdInput.style.transform = 'translateX(0)', 300);
            }
        });
    }
});