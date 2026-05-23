<?php
/**
 * File: server/prenota.php
 * API Endpoint per la creazione di una nuova prenotazione posto barca (AJAX).
 */
session_start();
require_once 'database.php';

// Forza l'intestazione della risposta in formato JSON
header('Content-Type: application/json');

// Controllo di sicurezza: verifica che l'utente sia autorizzato
if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Utente non autorizzato.']);
    exit;
}

$id_utente = $_SESSION['utente_id'];

// Recupero e decodifica del payload JSON inviato dallo script JavaScript
$payload_json = json_decode(file_get_contents('php://input'), true);

$codice_posto = $payload_json['posto'] ?? '';
$id_barca = $payload_json['barca_id'] ?? '';
$data_arrivo = $payload_json['dal'] ?? '';
$data_partenza = $payload_json['al'] ?? '';
$numero_equipaggio = $payload_json['numero_persone'] ?? ''; 

// 1. Validazione base: controllo che tutti i campi siano presenti
if (empty($codice_posto) || empty($id_barca) || empty($data_arrivo) || empty($data_partenza) || empty($numero_equipaggio)) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti. Verifica di aver compilato tutti i campi richiesti.']);
    exit;
}

// 2. Validazione temporale: la partenza non può venire prima dell'arrivo
if (strtotime($data_partenza) <= strtotime($data_arrivo)) {
    echo json_encode(['success' => false, 'message' => 'La data di partenza deve essere successiva a quella di arrivo.']);
    exit;
}

try {
    // 3. CONTROLLO CONFLITTO POSTO: Il molo è libero in queste date?
    $query_controllo_posto = $pdo->prepare("
        SELECT id FROM prenotazioni 
        WHERE posto = ? AND (data_inizio <= ? AND data_fine >= ?)
    ");
    $query_controllo_posto->execute([$codice_posto, $data_partenza, $data_arrivo]);
    
    if ($query_controllo_posto->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Spiacenti, il molo selezionato è già stato occupato per le date richieste.']);
        exit;
    }

    // 4. CONTROLLO CONFLITTO BARCA: L'imbarcazione è già in mare o in un altro molo per queste date?
    $query_controllo_barca = $pdo->prepare("
        SELECT id FROM prenotazioni 
        WHERE barca_id = ? AND (data_inizio <= ? AND data_fine >= ?)
    ");
    $query_controllo_barca->execute([$id_barca, $data_partenza, $data_arrivo]);
    
    if ($query_controllo_barca->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Errore: Hai già una prenotazione attiva per questa specifica imbarcazione nelle date selezionate.']);
        exit;
    }

    // 5. Nessun conflitto rilevato: inserimento della nuova prenotazione
    $query_inserimento = $pdo->prepare("
        INSERT INTO prenotazioni (utente_id, barca_id, posto, data_inizio, data_fine, numero_persone) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $query_inserimento->execute([$id_utente, $id_barca, $codice_posto, $data_arrivo, $data_partenza, $numero_equipaggio]);

    echo json_encode(['success' => true, 'message' => 'Prenotazione confermata con successo! Ti aspettiamo in banchina.']);

} catch (PDOException $e) {
    // Ritorno dell'errore al client in formato JSON, evitando crash o esposizione di query SQL
    echo json_encode(['success' => false, 'message' => 'Errore interno del server durante la registrazione della prenotazione.']);
}
?>