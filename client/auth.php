<?php
/**
 * File: auth.php
 * Gestisce l'interfaccia utente per il Login e la Registrazione.
 * La logica di autenticazione e le variabili di stato ($errore, $messaggio_successo)
 * vengono elaborate e importate dal file auth_logic.php.
 */
require_once '../server/auth_logic.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Accesso | NauticHub</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/auth.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="auth-bg-image" style="background-image: url('img/foto-porto.jpeg');" aria-hidden="true"></div>
    <div class="auth-bg-overlay" aria-hidden="true"></div>

    <main class="split-layout">
        
        <aside class="split-brand">
            <div class="brand-content">
                <h1>NauticHub</h1>
                <div class="brand-divider"></div>
                <p>L'eleganza della navigazione incontra l'eccellenza della gestione digitale.<br><span class="cyan-text">L'accesso riservato alla tua flotta.</span></p>
            </div>
        </aside>

        <section class="split-form">
            <div class="form-container">
                <a id="btn-indietro" class="back-link" href="index.php"><i class="fa-solid fa-arrow-left"></i> Torna alla rotta</a>

                <?php if(isset($errore) && $errore): ?>
                    <div class="sys-msg sys-error"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $errore; ?></div>
                <?php endif; ?>
                
                <?php if(isset($messaggio_successo) && $messaggio_successo): ?>
                    <div class="sys-msg sys-success"><i class="fa-solid fa-circle-check"></i> <?php echo $messaggio_successo; ?></div>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            document.getElementById('form-registrazione').style.display = 'none';
                            document.getElementById('form-login').style.display = 'block';
                        });
                    </script>
                <?php endif; ?>

                <form id="form-login" method="POST" action="auth.php">
                    <h2>Accedi </h2>
                    <p class="form-subtitle">Inserisci le tue credenziali per proseguire.</p>
                    <input type="hidden" name="azione" value="login">
                    
                    <div class="form-group">
                        <label for="login-email">Indirizzo Email</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="login-email" name="email" required placeholder="capitano@email.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="login-password" name="password" required placeholder="••••••••">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-gold">Entra nel Porto <i class="fa-solid fa-arrow-right-to-bracket"></i></button>
                    
                    <div class="form-switch">
                        Non sei ancora registrato? <span id="btn-vai-registrazione" class="switch-link">Richiedi Accesso</span>
                    </div>
                </form>

                <form id="form-registrazione" method="POST" action="auth.php">
                    <h2>Nuovo <span class="gold-text">Armatore</span></h2>
                    <p class="form-subtitle">Crea il tuo profilo da armatore in pochi istanti.</p>
                    <input type="hidden" name="azione" value="registrazione">
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="reg-nome">Nome</label>
                            <input type="text" id="reg-nome" name="nome" required placeholder="Il tuo nome">
                        </div>
                        <div class="form-group half">
                            <label for="reg-cognome">Cognome</label>
                            <input type="text" id="reg-cognome" name="cognome" required placeholder="Il tuo cognome">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reg-email">Indirizzo Email</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="reg-email" name="email" required placeholder="capitano@email.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-password">Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-shield-halved"></i>
                            <input type="password" name="password" id="reg-password" required placeholder="Crea una password sicura">
                        </div>
                        <div id="pwd-error" class="pwd-error-text">
                            <i class="fa-solid fa-circle-exclamation"></i> Deve contenere 8 caratteri, una Maiuscola e un simbolo (!@#).
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-gold">Registra Profilo <i class="fa-solid fa-user-plus"></i></button>
                    
                    <div class="form-switch">
                        Sei già registrato? <span id="btn-vai-login" class="switch-link">Torna al Login</span>
                    </div>
                </form>

            </div>
        </section>

    </main>

    <script src="js/auth.js"></script>
</body>
</html>