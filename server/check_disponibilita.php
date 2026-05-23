<?php
/**
 * File: server/check_disponibilita.php
 * API Endpoint che restituisce un array JSON con i posti barca già occupati
 * per l'intervallo di date richiesto.
 */
session_start();

// Inclusione della connessione sicura al database
require_once 'database.php';

// Impostazione dell'intestazione per far capire al browser che riceverà dati JSON
header('Content-Type: application/json');

// Recupero delle date inviate dalla mappa interattiva (mappa-porto.js) tramite GET
$data_arrivo = $_GET['dal'] ?? '';
$data_partenza = $_GET['al'] ?? '';

// Sicurezza: se mancano i parametri o sono vuoti, restituiamo un array vuoto per non bloccare la mappa
if (empty($data_arrivo) || empty($data_partenza)) {
    echo json_encode([]);
    exit;
}

try {
    /*
     * Logica SQL per intercettare la sovrapposizione delle date:
     * Un posto risulta occupato se la data di inizio della prenotazione salvata 
     * è <= alla data di partenza richiesta dall'utente, E contemporaneamente 
     * la data di fine salvata è >= alla data di arrivo richiesta.
     */
    $query_occupazione = $pdo->prepare("
        SELECT posto FROM prenotazioni 
        WHERE data_inizio <= ? AND data_fine >= ?
    ");
    
    // Passiamo i parametri nell'ordine esatto richiesto dalla query
    $query_occupazione->execute([$data_partenza, $data_arrivo]);
    
    // Estraiamo solo il nome del posto (es. "A01", "B02") in un array semplice
    $posti_occupati = $query_occupazione->fetchAll(PDO::FETCH_COLUMN);
    
    // Inviamo l'array al JavaScript che si occuperà di aggiornare i colori
    echo json_encode($posti_occupati);

} catch (PDOException $eccezione_db) {
    // In caso di errore del server MySQL, non mostriamo errori strani ma restituiamo un array vuoto
    echo json_encode([]);
}
?>