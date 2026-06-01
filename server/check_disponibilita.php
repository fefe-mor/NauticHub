<?php

session_start();


require_once 'database.php';


header('Content-Type: application/json');


$data_arrivo = $_GET['dal'] ?? '';
$data_partenza = $_GET['al'] ?? '';


if (empty($data_arrivo) || empty($data_partenza)) {
    echo json_encode([]);
    exit;
}

try {
   
    $query_occupazione = $pdo->prepare("
        SELECT posto FROM prenotazioni 
        WHERE data_inizio <= ? AND data_fine >= ?
    ");
    
    $query_occupazione->execute([$data_partenza, $data_arrivo]);
    
    $posti_occupati = $query_occupazione->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($posti_occupati);

} catch (PDOException $eccezione_db) {
    echo json_encode([]);
}
?>