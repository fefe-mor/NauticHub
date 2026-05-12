<?php
// server/auth_logic.php
session_start();

// Includiamo il database
require_once 'database.php';

$errore = '';
$messaggio_successo = '';

// Se l'utente è già loggato, via alla dashboard
if (isset($_SESSION['loggato']) && $_SESSION['loggato'] === true) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione'])) {
    
    /* LOGICA REGISTRAZIONE */
    if ($_POST['azione'] === 'registrazione') {
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']); 
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        // Validazione Regex server-side (sicurezza extra)
        if (!preg_match('/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/', $password)) {
            $errore = "La password non rispetta i requisiti minimi di sicurezza.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errore = "Indirizzo email già registrato a sistema.";
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins = $pdo->prepare("INSERT INTO utenti (nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, 'diportista')");
                $ins->execute([$nome, $cognome, $email, $hash]);
                
                $messaggio_successo = "Registrazione completata con successo. È ora possibile accedere.";
            }
        }
    } 
    /* LOGICA LOGIN */
    elseif ($_POST['azione'] === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        $stmt = $pdo->prepare("SELECT * FROM utenti WHERE email = ?");
        $stmt->execute([$email]);
        $utente = $stmt->fetch();
        
        if ($utente && password_verify($password, $utente['password'])) {
            $_SESSION['loggato'] = true;
            $_SESSION['utente_id'] = $utente['id'];
            $_SESSION['nome_utente'] = $utente['nome'];
            $_SESSION['ruolo'] = $utente['ruolo'];
            $_SESSION['email_utente'] = $email;
            
            header("Location: dashboard.php");
            exit;
        } else {
            $errore = "Credenziali non valide.";
        }
    }
}
?>