<?php
/**
 * File: server/modifica-prenotazione.php
 * API Endpoint per l'aggiornamento asincrono (AJAX) delle date di una prenotazione.
 */
session_start();
require_once 'database.php';

// Forza l'intestazione della risposta in formato JSON
header('Content-Type: application/json');

// Controllo di sicurezza: verifica che l'utente sia loggato
if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Utente non autorizzato.']);
    exit;
}

$id_utente = $_SESSION['utente_id'];

// Recupero e decodifica del payload JSON inviato dallo script JavaScript
$payload_json = json_decode(file_get_contents('php://input'), true);

$id_prenotazione = intval($payload_json['id_prenotazione'] ?? 0);
$data_arrivo = $payload_json['data_inizio'] ?? '';
$data_partenza = $payload_json['data_fine'] ?? '';
$numero_equipaggio = intval($payload_json['numero_persone'] ?? 1);

// Validazione per assicurarsi che nessun campo fondamentale sia vuoto
if (empty($id_prenotazione) || empty($data_arrivo) || empty($data_partenza)) {
    echo json_encode(['success' => false, 'message' => 'Dati incompleti. Compila le date richieste.']);
    exit;
}

// Validazione temporale logica (la partenza non può venire prima dell'arrivo)
if (strtotime($data_partenza) <= strtotime($data_arrivo)) {
    echo json_encode(['success' => false, 'message' => 'La data di partenza deve essere successiva a quella di arrivo.']);
    exit;
}

try {
    // 1. Recupero informazioni attuali della prenotazione dal DB
    $query_info = $pdo->prepare("SELECT barca_id, posto FROM prenotazioni WHERE id = ? AND utente_id = ?");
    $query_info->execute([$id_prenotazione, $id_utente]);
    $info_prenotazione = $query_info->fetch();

    if (!$info_prenotazione) {
        echo json_encode(['success' => false, 'message' => 'Prenotazione non trovata o non autorizzata.']);
        exit;
    }

    $id_barca = $info_prenotazione['barca_id'];
    $codice_posto = $info_prenotazione['posto'];

    // 2. Controllo CONFLITTI MOLO: Il posto è stato nel frattempo occupato da un'altra barca per le nuove date?
    $query_conflitto_posto = $pdo->prepare("
        SELECT id FROM prenotazioni 
        WHERE posto = ? AND id != ? 
        AND (data_inizio <= ? AND data_fine >= ?)
    ");
    $query_conflitto_posto->execute([$codice_posto, $id_prenotazione, $data_partenza, $data_arrivo]);

    if ($query_conflitto_posto->fetch()) {
        echo json_encode(['success' => false, 'message' => "Il molo selezionato è già stato occupato per le nuove date richieste."]);
        exit;
    }

    // 3. Controllo CONFLITTI BARCA: L'utente ha già prenotato un altro molo per questa stessa barca in quelle date?
    $query_conflitto_barca = $pdo->prepare("
        SELECT id FROM prenotazioni 
        WHERE barca_id = ? AND id != ? 
        AND (data_inizio <= ? AND data_fine >= ?)
    ");
    $query_conflitto_barca->execute([$id_barca, $id_prenotazione, $data_partenza, $data_arrivo]);

    if ($query_conflitto_barca->fetch()) {
        echo json_encode(['success' => false, 'message' => "L'imbarcazione risulta già impegnata in un'altra prenotazione per le date richieste."]);
        exit;
    }

    // 4. Nessun conflitto rilevato: si procede all'aggiornamento sicuro
    $query_aggiornamento = $pdo->prepare("
        UPDATE prenotazioni 
        SET data_inizio = ?, data_fine = ?, numero_persone = ? 
        WHERE id = ? AND utente_id = ?
    ");
    $query_aggiornamento->execute([$data_arrivo, $data_partenza, $numero_equipaggio, $id_prenotazione, $id_utente]);

    echo json_encode(['success' => true, 'message' => 'Prenotazione aggiornata con successo!']);

} catch (PDOException $e) {
    // Ritorno dell'errore al client in formato JSON, evitando crash della pagina
    echo json_encode(['success' => false, 'message' => 'Errore interno del server durante la comunicazione col database.']);
}
?>