<?php
/**
 * File: server/auth_logic.php
 * Gestisce la logica di backend per l'autenticazione (Login e Registrazione).
 */
session_start();

// Inclusione della connessione al database
require_once 'database.php';

// Inizializzazione variabili per i messaggi di feedback passati al frontend
$errore = '';
$messaggio_successo = '';

// Se l'utente è già autenticato, reindirizzamento immediato alla Dashboard
if (isset($_SESSION['loggato']) && $_SESSION['loggato'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Intercettazione delle richieste POST inviate dal modulo di autenticazione
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione'])) {
    
    // =========================================
    // 1. LOGICA DI REGISTRAZIONE NUOVO ARMATORE
    // =========================================
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
                // Gestione elegante dell'errore di connessione al database
                $errore = "Errore del server durante la registrazione. Riprovare più tardi.";
            }
        }
    } 
    
    // =========================================
    // 2. LOGICA DI ACCESSO (LOGIN)
    // =========================================
    elseif ($_POST['azione'] === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        try {
            // Recupero dei dati utente tramite email
            $stmt_utente = $pdo->prepare("SELECT * FROM utenti WHERE email = ?");
            $stmt_utente->execute([$email]);
            $dati_utente = $stmt_utente->fetch();
            
            // Verifica della password inserita in chiaro con l'hash salvato nel database
            if ($dati_utente && password_verify($password, $dati_utente['password'])) {
                // Creazione della sessione utente sicura
                $_SESSION['loggato'] = true;
                $_SESSION['utente_id'] = $dati_utente['id'];
                $_SESSION['nome_utente'] = $dati_utente['nome'];
                $_SESSION['ruolo'] = $dati_utente['ruolo'];
                $_SESSION['email_utente'] = $email;
                
                // Reindirizzamento alla pagina personale (Dashboard)
                header("Location: dashboard.php");
                exit;
            } else {
                $errore = "Credenziali non valide.";
            }
        } catch (PDOException $e) {
            // Gestione elegante dell'errore di query
            $errore = "Errore di connessione al database durante l'accesso.";
        }
    }
}
?>