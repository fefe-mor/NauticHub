<?php

$host_db = '127.0.0.1'; 
$nome_db = 'nautichub_db';
$utente_db = 'root'; 
$password_db = '';  

try {
    $pdo = new PDO("mysql:host=$host_db;dbname=$nome_db;charset=utf8mb4", $utente_db, $password_db);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $eccezione_db) {
    die("Errore critico di connessione al database: " . $eccezione_db->getMessage());
}
?>