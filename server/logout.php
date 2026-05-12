<?php
// server/logout.php
session_start(); // Recupera la sessione attuale

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

// 3. Distrugge definitivamente la sessione sul server
session_destroy();

// 4. Reindirizza alla pagina di login/home
header("Location: ../client/index.php");
exit;
?>