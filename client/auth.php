<?php
require_once '../server/auth_logic.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Accesso | NauticHub</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/auth.css">
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

            <?php if(isset($errore) && $errore): ?>
                <div class="sys-msg sys-error"><?php echo $errore; ?></div>
            <?php endif; ?>
            
            <?php if(isset($messaggio_successo) && $messaggio_successo): ?>
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

    <script src="js/auth.js"></script>
</body>
</html>