<?php

session_start();
require_once 'database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Utente non autorizzato.']);
    exit;
}

$id_utente = $_SESSION['utente_id'];

$payload_json = json_decode(file_get_contents('php://input'), true);

$codice_posto = $payload_json['posto'] ?? '';
$id_barca = $payload_json['barca_id'] ?? '';
$data_arrivo = $payload_json['dal'] ?? '';
$data_partenza = $payload_json['al'] ?? '';
$numero_equipaggio = $payload_json['numero_persone'] ?? ''; 

if (empty($codice_posto) || empty($id_barca) || empty($data_arrivo) || empty($data_partenza) || empty($numero_equipaggio)) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti. Verifica di aver compilato tutti i campi richiesti.']);
    exit;
}

if (strtotime($data_partenza) <= strtotime($data_arrivo)) {
    echo json_encode(['success' => false, 'message' => 'La data di partenza deve essere successiva a quella di arrivo.']);
    exit;
}

try {
    $query_controllo_posto = $pdo->prepare("
        SELECT id FROM prenotazioni 
        WHERE posto = ? AND (data_inizio <= ? AND data_fine >= ?)
    ");
    $query_controllo_posto->execute([$codice_posto, $data_partenza, $data_arrivo]);
    
    if ($query_controllo_posto->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Spiacenti, il molo selezionato è già stato occupato per le date richieste.']);
        exit;
    }

    $query_controllo_barca = $pdo->prepare("
        SELECT id FROM prenotazioni 
        WHERE barca_id = ? AND (data_inizio <= ? AND data_fine >= ?)
    ");
    $query_controllo_barca->execute([$id_barca, $data_partenza, $data_arrivo]);
    
    if ($query_controllo_barca->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Errore: Hai già una prenotazione attiva per questa specifica imbarcazione nelle date selezionate.']);
        exit;
    }

    $query_inserimento = $pdo->prepare("
        INSERT INTO prenotazioni (utente_id, barca_id, posto, data_inizio, data_fine, numero_persone) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $query_inserimento->execute([$id_utente, $id_barca, $codice_posto, $data_arrivo, $data_partenza, $numero_equipaggio]);

    echo json_encode(['success' => true, 'message' => 'Prenotazione confermata con successo! Ti aspettiamo in banchina.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Errore interno del server durante la registrazione della prenotazione.']);
}
?>