<?php
// server/database.php

$host = '127.0.0.1'; // o 'localhost'
$dbname = 'nautichub_db';
$username = 'root'; // Utente di default su XAMPP
$password = ''; // Nessuna password di default su XAMPP (su MAMP di solito è 'root')

try {
    // Creazione dell'istanza PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Impostiamo l'attributo per intercettare gli errori lanciando un'eccezione
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Impostiamo il fetch di default come array associativo
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Catturiamo eventuali errori di connessione
    die("Errore di connessione al database: " . $e->getMessage());
}
?>