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
$utente_id = $_SESSION['utente_id'];

if (empty($posto) || empty($barca_id) || empty($dal) || empty($al)) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti. Seleziona date e barca.']);
    exit;
}

// Controllo logico: la data di partenza non può essere prima di quella d'arrivo
if (strtotime($al) < strtotime($dal)) {
    echo json_encode(['success' => false, 'message' => 'La data di partenza deve essere successiva a quella di arrivo.']);
    exit;
}

try {
    // Doppio controllo di sicurezza: verifichiamo che nessuno abbia prenotato negli ultimi secondi
    $check = $pdo->prepare("SELECT id FROM prenotazioni WHERE posto = ? AND (data_inizio <= ? AND data_fine >= ?)");
    $check->execute([$posto, $al, $dal]);
    
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Spiacenti, posto appena occupato per quelle date!']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO prenotazioni (utente_id, barca_id, posto, data_inizio, data_fine) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$utente_id, $barca_id, $posto, $dal, $al]);

    echo json_encode(['success' => true, 'message' => 'Prenotazione confermata per le date selezionate!']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Errore server.']);
}
?>