<?php
// Richiamiamo la logica isolata nel server
require_once '../server/auth_logic.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Accesso | NauticHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* MANTIENI IL TUO FANTASTICO CSS ESATTAMENTE COME LO AVEVI */
        :root { --navy: #0a192f; --teak: #a67c52; --teak-dark: #8b6540; --white: #ffffff; --text-dark: #1a1a1a; --gray-light: #e5e7eb; --gray-text: #6b7280; --error-red: #cc0000; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { display: flex; min-height: 100vh; width: 100%; background-color: var(--white); overflow-x: hidden; }
        .split-brand { width: 50%; background-color: var(--navy); display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .brand-content { text-align: center; color: var(--white); padding: 3rem; max-width: 600px; }
        .brand-content h1 { font-family: 'Playfair Display', serif; font-size: 3.5rem; letter-spacing: 2px; margin-bottom: 1.5rem; font-weight: 400; }
        .brand-divider { width: 60px; height: 2px; background-color: var(--teak); margin: 0 auto 2rem auto; }
        .brand-content p { font-size: 1.1rem; font-weight: 300; line-height: 1.8; letter-spacing: 0.5px; color: #e2e8f0; }
        .split-form { width: 50%; background: var(--white); display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 4rem; position: relative; }
        .form-container { width: 100%; max-width: 420px; animation: fadeIn 0.8s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .back-link { display: inline-block; margin-bottom: 2rem; color: var(--gray-text); text-decoration: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; transition: color 0.3s; cursor: pointer; }
        .back-link:hover { color: var(--navy); }
        .form-container h2 { font-family: 'Playfair Display', serif; color: var(--navy); font-size: 2.2rem; font-weight: 600; margin-bottom: 0.5rem; }
        .form-container > p { color: var(--gray-text); font-size: 0.95rem; margin-bottom: 3rem; font-weight: 400; }
        .form-group { margin-bottom: 2.5rem; position: relative; }
        .form-group label { display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--navy); margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input { width: 100%; padding: 0.5rem 0; border: none; border-bottom: 1px solid var(--gray-light); background: transparent; color: var(--text-dark); font-size: 1.1rem; transition: 0.3s; }
        .form-group input:focus { outline: none; border-bottom-color: var(--teak); }
        .pwd-error-text { color: var(--error-red); font-size: 0.75rem; margin-top: 8px; display: none; font-weight: 500; }
        .btn-submit { width: 100%; background-color: var(--navy); color: var(--white); border: none; padding: 1.2rem; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; cursor: pointer; transition: background 0.3s; margin-top: 1rem; border-radius: 2px; }
        .btn-submit:hover { background-color: var(--teak); }
        .form-switch { margin-top: 2.5rem; text-align: center; font-size: 0.85rem; color: var(--gray-text); }
        .form-switch span { color: var(--navy); font-weight: 600; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; margin-left: 10px; border-bottom: 1px solid transparent; transition: 0.3s; padding-bottom: 2px; }
        .form-switch span:hover { border-bottom-color: var(--teak); color: var(--teak); }
        .sys-msg { padding: 1rem; margin-bottom: 2rem; font-size: 0.85rem; border-left: 4px solid; }
        .sys-error { background-color: #fef2f2; border-color: var(--error-red); color: var(--error-red); }
        .sys-success { background-color: #fdfbf7; border-color: var(--teak); color: var(--teak-dark); }
        #form-registrazione { display: none; }
        @media (max-width: 900px) { body { flex-direction: column; } .split-brand { width: 100%; min-height: 35vh; padding: 2rem 1rem; } .brand-content { padding: 0; } .brand-content h1 { font-size: 2.8rem; } .brand-content p { font-size: 1rem; } .split-form { width: 100%; padding: 3rem 2rem; min-height: 65vh; justify-content: flex-start; } .form-container { margin-top: 1rem; } }
    </style>
</head>
<body>

    <div class="split-brand">
        <div class="brand-content">
            <h1>NauticHub</h1>
            <div class="brand-divider"></div>
            <p>L'eleganza della navigazione incontra l'eccellenza della gestione digitale. L'accesso riservato alla tua flotta.</p>
        </div>
    </div>

    <div class="split-form">
        <div class="form-container">
            <a id="btn-indietro" class="back-link">← Torna Indietro</a>

            <?php if($errore): ?>
                <div class="sys-msg sys-error"><?php echo $errore; ?></div>
            <?php endif; ?>
            <?php if($messaggio_successo): ?>
                <div class="sys-msg sys-success"><?php echo $messaggio_successo; ?></div>
                <script>
                    // Piccolo helper per far aprire subito il login in caso di successo
                    document.addEventListener('DOMContentLoaded', () => {
                        document.getElementById('form-registrazione').style.display = 'none';
                        document.getElementById('form-login').style.display = 'block';
                    });
                </script>
            <?php endif; ?>

            <form id="form-login" method="POST" action="auth.php">
                <h2>Accesso</h2>
                <p>Inserisci le tue credenziali per proseguire.</p>
                <input type="hidden" name="azione" value="login">
                
                <div class="form-group">
                    <label>Indirizzo Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-submit">Entra nel Porto</button>
                
                <div class="form-switch">
                    Non sei ancora registrato? <span id="btn-vai-registrazione">Richiedi Accesso</span>
                </div>
            </form>

            <form id="form-registrazione" method="POST" action="auth.php">
                <h2>Registrazione</h2>
                <p>Crea il tuo profilo da armatore in pochi istanti.</p>
                <input type="hidden" name="azione" value="registrazione">
                
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" required>
                </div>
                <div class="form-group">
                    <label>Cognome</label>
                    <input type="text" name="cognome" required>
                </div>
                <div class="form-group">
                    <label>Indirizzo Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" id="reg-password" required>
                    <div id="pwd-error" class="pwd-error-text">
                        La password deve contenere almeno 8 caratteri, una lettera Maiuscola e un carattere speciale (es. ! @ #).
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Registra Profilo</button>
                
                <div class="form-switch">
                    Sei già registrato? <span id="btn-vai-login">Torna al Login</span>
                </div>
            </form>

        </div>
    </div>

    <script src="client/js/auth.js"></script>
</body>
</html>