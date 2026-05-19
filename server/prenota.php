<?php
// server/prenota.php
session_start();
require_once 'database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['utente_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']);
    exit;
}

$dati = json_decode(file_get_contents('php://input'), true);

$posto = $dati['posto'] ?? '';
$barca_id = $dati['barca_id'] ?? '';
$dal = $dati['dal'] ?? '';
$al = $dati['al'] ?? '';
$numero_persone = $dati['numero_persone'] ?? ''; 
$utente_id = $_SESSION['utente_id'];

if (empty($posto) || empty($barca_id) || empty($dal) || empty($al) || empty($numero_persone)) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti. Compila tutti i campi.']);
    exit;
}

// Controllo logico: la data di partenza non può essere prima di quella d'arrivo
if (strtotime($al) <= strtotime($dal)) {
    echo json_encode(['success' => false, 'message' => 'La data di partenza deve essere successiva a quella di arrivo.']);
    exit;
}

try {
    // 1. CONTROLLO POSTO: Il posto in banchina è libero per queste date?
    $checkPosto = $pdo->prepare("SELECT id FROM prenotazioni WHERE posto = ? AND (data_inizio <= ? AND data_fine >= ?)");
    $checkPosto->execute([$posto, $al, $dal]);
    
    if ($checkPosto->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Spiacenti, questo posto è già occupato per le date selezionate.']);
        exit;
    }

    // 2. CONTROLLO BARCA: Questa specifica barca è già in mare o in un altro molo per queste date?
    $checkBarca = $pdo->prepare("SELECT id FROM prenotazioni WHERE barca_id = ? AND (data_inizio <= ? AND data_fine >= ?)");
    $checkBarca->execute([$barca_id, $al, $dal]);
    
    if ($checkBarca->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Errore: Hai già prenotato un posto per questa specifica barca in queste date!']);
        exit;
    }

    // Se entrambi i controlli passano, la barca non è un fantasma e il posto è libero! Inseriamo:
    $stmt = $pdo->prepare("INSERT INTO prenotazioni (utente_id, barca_id, posto, data_inizio, data_fine, numero_persone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$utente_id, $barca_id, $posto, $dal, $al, $numero_persone]);

    echo json_encode(['success' => true, 'message' => 'Prenotazione confermata per le date selezionate!']);

} catch (PDOException $e) {
    // In caso di problemi di struttura del database, ti stampa l'errore esatto
    echo json_encode(['success' => false, 'message' => 'Errore DB: ' . $e->getMessage()]);
}
?>