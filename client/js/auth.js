document.addEventListener('DOMContentLoaded', () => {
    // AZZERA LA MEMORIA DELLE SCHEDE AL LOGIN (Risolve il problema del reindirizzamento alla scheda errata)
    sessionStorage.removeItem('schedaAttivaDashboard');

    // Selezione degli elementi del DOM
    const formLogin = document.getElementById('form-login');
    const formRegistrazione = document.getElementById('form-registrazione');
    const btnVaiRegistrazione = document.getElementById('btn-vai-registrazione');
    const btnVaiLogin = document.getElementById('btn-vai-login');
    const btnIndietro = document.getElementById('btn-indietro');
    
    const inputPassword = document.getElementById('reg-password');
    const messaggioErrorePassword = document.getElementById('pwd-error');
    
    // Regola per la password: Minimo 8 caratteri, 1 Maiuscola, 1 Carattere Speciale
    const regexPassword = /^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/;

    // ==========================================
    // GESTIONE TRANSIZIONI FORM (Login <-> Registrazione)
    // ==========================================
    if (btnVaiRegistrazione && formLogin && formRegistrazione) {
        btnVaiRegistrazione.addEventListener('click', () => {
            formLogin.style.display = 'none';
            formRegistrazione.style.display = 'block';
        });
    }

    if (btnVaiLogin && formLogin && formRegistrazione) {
        btnVaiLogin.addEventListener('click', () => {
            formRegistrazione.style.display = 'none';
            formLogin.style.display = 'block';
        });
    }

    // ==========================================
    // GESTIONE RITORNO ALLA HOME IN SICUREZZA
    // ==========================================
    if (btnIndietro) {
        btnIndietro.addEventListener('click', (e) => {
            e.preventDefault();
            // Utilizza l'API History se c'è una pagina precedente, altrimenti forza la home
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = 'index.php'; 
            }
        });
    }

    // ==========================================
    // VALIDAZIONE PASSWORD IN TEMPO REALE
    // ==========================================
    if (inputPassword && messaggioErrorePassword) {
        inputPassword.addEventListener('input', (e) => {
            const passwordDigitata = e.target.value;
            
            // Se l'utente sta scrivendo ma la password non rispetta le regole
            if (passwordDigitata.length > 0 && !regexPassword.test(passwordDigitata)) {
                messaggioErrorePassword.style.display = 'block';
                // Utilizza la nuova variabile CSS in italiano
                inputPassword.style.borderColor = 'var(--rosso-errore)';
            } 
            // Se la password è corretta o il campo è vuoto
            else {
                messaggioErrorePassword.style.display = 'none';
                // Utilizza la nuova variabile CSS in italiano
                inputPassword.style.borderColor = 'var(--ombra-luce-ciano)';
            }
        });

        // Validazione al Submit con animazione "shake" (tremolio) in caso di errore
        if (formRegistrazione) {
            formRegistrazione.addEventListener('submit', (e) => {
                if (!regexPassword.test(inputPassword.value)) {
                    e.preventDefault(); // Blocca l'invio del modulo al server PHP
                    messaggioErrorePassword.style.display = 'block';
                    inputPassword.style.borderColor = 'var(--rosso-errore)';
                    
                    // Effetto tremolio per attirare visivamente l'attenzione dell'utente
                    inputPassword.style.transform = 'translateX(10px)';
                    setTimeout(() => inputPassword.style.transform = 'translateX(-10px)', 100);
                    setTimeout(() => inputPassword.style.transform = 'translateX(10px)', 200);
                    setTimeout(() => inputPassword.style.transform = 'translateX(0)', 300);
                }
            });
        }
    }
});