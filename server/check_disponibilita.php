<?php
// server/check_disponibilita.php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

$dal = $_GET['dal'] ?? '';
$al = $_GET['al'] ?? '';

if (empty($dal) || empty($al)) {
    echo json_encode([]);
    exit;
}

try {
    // La logica SQL per la sovrapposizione delle date!
    // Un posto è occupato se la data di inizio di una prenotazione è <= alla data di fine richiesta
    // E la data di fine della prenotazione è >= alla data di inizio richiesta.
    $stmt = $pdo->prepare("
        SELECT posto FROM prenotazioni 
        WHERE data_inizio <= ? AND data_fine >= ?
    ");
    // Passiamo $al (fine richiesta) e $dal (inizio richiesta)
    $stmt->execute([$al, $dal]);
    $occupati = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($occupati);

} catch (PDOException $e) {
    echo json_encode([]);
}
?>