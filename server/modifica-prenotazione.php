<?php
session_start();
require_once 'database.php';
header('Content-Type: application/json');

// Controllo sicurezza
if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']);
    exit;
}

// Recupero i dati inviati in tempo reale tramite JavaScript
$dati = json_decode(file_get_contents('php://input'), true);
$id_prenotazione = intval($dati['id_prenotazione'] ?? 0);
$data_inizio = $dati['data_inizio'] ?? '';
$data_fine = $dati['data_fine'] ?? '';
$numero_persone = intval($dati['numero_persone'] ?? 0);
$utente_id = $_SESSION['utente_id'];

if (empty($id_prenotazione) || empty($data_inizio) || empty($data_fine) || empty($numero_persone)) {
    echo json_encode(['success' => false, 'message' => 'Compila tutti i campi.']);
    exit;
}

if (strtotime($data_fine) <= strtotime($data_inizio)) {
    echo json_encode(['success' => false, 'message' => 'La data di partenza deve essere successiva a quella di arrivo.']);
    exit;
}

try {
    // 1. Recupero barca e posto attuali
    $stmtInfo = $pdo->prepare("SELECT barca_id, posto FROM prenotazioni WHERE id = ? AND utente_id = ?");
    $stmtInfo->execute([$id_prenotazione, $utente_id]);
    $infoPren = $stmtInfo->fetch();

    if(!$infoPren) {
        echo json_encode(['success' => false, 'message' => 'Prenotazione non trovata.']);
        exit;
    }

    $barca_id = $infoPren['barca_id'];
    $posto = $infoPren['posto'];

    // 2. Controllo POSTO: C'è un'altra barca? (Escludo la prenotazione attuale)
    $checkPosto = $pdo->prepare("SELECT id FROM prenotazioni WHERE posto = ? AND id != ? AND (data_inizio <= ? AND data_fine >= ?)");
    $checkPosto->execute([$posto, $id_prenotazione, $data_fine, $data_inizio]);

    // 3. Controllo BARCA: Questa barca è già altrove? (Escludo la prenotazione attuale)
    $checkBarca = $pdo->prepare("SELECT id FROM prenotazioni WHERE barca_id = ? AND id != ? AND (data_inizio <= ? AND data_fine >= ?)");
    $checkBarca->execute([$barca_id, $id_prenotazione, $data_fine, $data_inizio]);

    if ($checkPosto->fetch()) {
        echo json_encode(['success' => false, 'message' => "Il molo è già occupato da un'altra barca per le nuove date richieste!"]);
        exit;
    } elseif ($checkBarca->fetch()) {
        echo json_encode(['success' => false, 'message' => "Hai già un'altra prenotazione attiva per questa barca nelle nuove date!"]);
        exit;
    }

    // 4. Se tutto è libero, salvo le modifiche!
    $stmt = $pdo->prepare("UPDATE prenotazioni SET data_inizio = ?, data_fine = ?, numero_persone = ? WHERE id = ? AND utente_id = ?");
    $stmt->execute([$data_inizio, $data_fine, $numero_persone, $id_prenotazione, $utente_id]);

    echo json_encode(['success' => true, 'message' => 'Prenotazione modificata con successo!']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Errore Server DB.']);
}
?>