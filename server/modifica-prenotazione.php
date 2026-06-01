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

$id_prenotazione = intval($payload_json['id_prenotazione'] ?? 0);
$data_arrivo = $payload_json['data_inizio'] ?? '';
$data_partenza = $payload_json['data_fine'] ?? '';
$numero_equipaggio = intval($payload_json['numero_persone'] ?? 1);

if (empty($id_prenotazione) || empty($data_arrivo) || empty($data_partenza)) {
    echo json_encode(['success' => false, 'message' => 'Dati incompleti. Compila le date richieste.']);
    exit;
}

if (strtotime($data_partenza) <= strtotime($data_arrivo)) {
    echo json_encode(['success' => false, 'message' => 'La data di partenza deve essere successiva a quella di arrivo.']);
    exit;
}

try {
    $query_info = $pdo->prepare("SELECT barca_id, posto FROM prenotazioni WHERE id = ? AND utente_id = ?");
    $query_info->execute([$id_prenotazione, $id_utente]);
    $info_prenotazione = $query_info->fetch();

    if (!$info_prenotazione) {
        echo json_encode(['success' => false, 'message' => 'Prenotazione non trovata o non autorizzata.']);
        exit;
    }

    $id_barca = $info_prenotazione['barca_id'];
    $codice_posto = $info_prenotazione['posto'];

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

    $query_aggiornamento = $pdo->prepare("
        UPDATE prenotazioni 
        SET data_inizio = ?, data_fine = ?, numero_persone = ? 
        WHERE id = ? AND utente_id = ?
    ");
    $query_aggiornamento->execute([$data_arrivo, $data_partenza, $numero_equipaggio, $id_prenotazione, $id_utente]);

    echo json_encode(['success' => true, 'message' => 'Prenotazione aggiornata con successo!']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Errore interno del server durante la comunicazione col database.']);
}
?>