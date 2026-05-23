<?php
/**
 * File: server/logout.php
 * Gestisce la distruzione della sessione e il reindirizzamento corretto alla cartella client.
 */
session_start();

// 1. Svuota l'array della sessione
$_SESSION = array();

// 2. Distrugge il cookie di sessione (Sicurezza extra)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Distrugge la sessione sul server
session_destroy();

// 4. Reindirizza alla rotta originale corretta all'interno della cartella client
header("Location: ../client/index.php");
exit;
?>