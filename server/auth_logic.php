<?php

session_start();


require_once 'database.php';

$errore = '';
$messaggio_successo = '';

if (isset($_SESSION['loggato']) && $_SESSION['loggato'] === true) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione'])) {
    
    if ($_POST['azione'] === 'registrazione') {
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']); 
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        // Validazione di sicurezza nel caso in cui il js fallisse
        if (!preg_match('/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/', $password)) {
            $errore = "La password non rispetta i requisiti minimi di sicurezza.";
        } else {
            try {
                // Controllo preliminare per verificare se l'email esiste già
                $stmt_verifica = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
                $stmt_verifica->execute([$email]);
                
                if ($stmt_verifica->fetch()) {
                    $errore = "Indirizzo email già registrato a sistema.";
                } else {
                    // Creazione dell'hash sicuro per la password
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    
                    // Inserimento del nuovo utente con ruolo predefinito 'diportista'
                    $stmt_inserimento = $pdo->prepare("INSERT INTO utenti (nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, 'diportista')");
                    $stmt_inserimento->execute([$nome, $cognome, $email, $password_hash]);
                    
                    $messaggio_successo = "Registrazione completata con successo. È ora possibile accedere.";
                }
            } catch (PDOException $e) {
                $errore = "Errore del server durante la registrazione. Riprovare più tardi.";
            }
        }
    } 
    
    elseif ($_POST['azione'] === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        try {
            $stmt_utente = $pdo->prepare("SELECT * FROM utenti WHERE email = ?");
            $stmt_utente->execute([$email]);
            $dati_utente = $stmt_utente->fetch();
            
            if ($dati_utente && password_verify($password, $dati_utente['password'])) {
                $_SESSION['loggato'] = true;
                $_SESSION['utente_id'] = $dati_utente['id'];
                $_SESSION['nome_utente'] = $dati_utente['nome'];
                $_SESSION['ruolo'] = $dati_utente['ruolo'];
                $_SESSION['email_utente'] = $email;
                
             
                header("Location: dashboard.php");
                exit;
            } else {
                $errore = "Credenziali non valide.";
            }
        } catch (PDOException $e) {
           
            $errore = "Errore di connessione al database durante l'accesso.";
        }
    }
}
?>