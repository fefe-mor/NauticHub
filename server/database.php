<?php
/**
 * File: server/database.php
 * Gestisce la connessione sicura al database MySQL utilizzando PDO.
 * Viene richiamato da tutti gli script server-side che necessitano di accesso ai dati.
 */

// Parametri di configurazione del database
$host_db = '127.0.0.1'; 
$nome_db = 'nautichub_db';
$utente_db = 'root'; // Utente predefinito in ambienti di sviluppo (XAMPP/MAMP)
$password_db = '';   // Password predefinita vuota su XAMPP ('root' su MAMP)

try {
    // Inizializzazione della connessione PDO con supporto nativo ai caratteri UTF-8
    $pdo = new PDO("mysql:host=$host_db;dbname=$nome_db;charset=utf8mb4", $utente_db, $password_db);
    
    // Impostazione degli attributi PDO per una gestione rigorosa di errori e risultati
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $eccezione_db) {
    // Intercettazione e blocco di sicurezza dell'esecuzione in caso di fallimento
    die("Errore critico di connessione al database: " . $eccezione_db->getMessage());
}
?>