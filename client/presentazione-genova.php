<?php
session_start();

/* Controllo accesso: se non è loggato torna al login */
if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'diportista') {
    header("Location: auth.php");
    exit;
}

$nome_utente = $_SESSION['nome_utente'];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Marina Genova | NauticHub</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/presentazione-genova.css?v=<?php echo time(); ?>"> 
</head>
<body class="presentazione-theme">

    <header class="dash-header">
        <div class="nav-content container">
            <div class="logo-area">
                <a href="index.php" class="text-logo">
                    Nautic<span class="gradient-logo-accent">Hub</span> <span class="port-location">Genova</span>
                </a>
            </div>
            <div class="user-info">
                <a href="dashboard.php" class="btn-outline-premium"><i class="fa-solid fa-arrow-left"></i> <span class="logout-text-hide">Torna alla Flotta</span></a>
                <span class="welcome-text"><i class="fa-regular fa-user"></i> Cap. <strong><?php echo htmlspecialchars($nome_utente); ?></strong></span>
            </div>
        </div>
    </header>

    <main class="main-presentazione">
        <section class="port-hero">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h4 class="hero-subtitle">La perla del Tirreno</h4>
                <h1>Smart Marina Genova</h1>
                <p>Un porto turistico all'avanguardia dove la tradizione marinara millenaria incontra le più moderne tecnologie di gestione dell'ormeggio.</p>
                <a href="mappa-porto.php" class="btn-premium-large"><i class="fa-solid fa-map-location-dot"></i> Esplora la Mappa e Prenota</a>
            </div>
        </section>

        <section class="port-details-container">
            <div class="port-details glass-panel">
                <div class="detail-text">
                    <h2>L'approdo perfetto per la tua imbarcazione.</h2>
                    <p>Situato nel cuore pulsante del capoluogo ligure, la <strong class="cyan-text">Smart Marina di Genova</strong> offre un rifugio sicuro e protetto da tutti i venti del quadrante settentrionale. I nostri fondali, con pescaggio fino a 15 metri, ci permettono di ospitare agilmente sia piccoli motoscafi che imponenti Superyacht.</p>
                    <p>A pochi passi dal Molo, ti ritroverai immerso nella storia: i celebri caruggi, l'Acquario di Genova e i migliori ristoranti di pesce della riviera sono letteralmente a portata di passeggiata. La nostra darsena è sorvegliata h24 per garantirti sonni tranquilli e una totale sicurezza per il tuo gioiello navale.</p>
                </div>
                <div class="detail-gallery">
                    <img src="img/barca.jpeg" alt="Barche al porto" class="gallery-img">
                    <img src="img/mare.jpeg" alt="Mare" class="gallery-img">
                    <img src="img/vista.jpeg" alt="Vista aerea molo" class="gallery-img large">
                </div>
            </div>
        </section>

        <section class="services-section">
            <div class="services-header">
                <h2>Servizi Inclusi nell'Ormeggio</h2>
                <p>Progettati per soddisfare le esigenze dei veri amanti del mare.</p>
            </div>

            <div class="services-grid">
                <div class="service-card glass-panel">
                    <div class="service-icon-wrapper"><i class="fa-solid fa-bolt"></i></div>
                    <h3>Corrente 380V</h3>
                    <p>Colonnine di ultima generazione con allaccio trifase per yacht e catamarani ad alto assorbimento. Gestione smart dei consumi.</p>
                </div>
                <div class="service-card glass-panel">
                    <div class="service-icon-wrapper"><i class="fa-solid fa-droplet"></i></div>
                    <h3>Acqua Potabile</h3>
                    <p>Erogazione di acqua dolce microfiltrata direttamente in banchina per ricaricare le cisterne di bordo in totale sicurezza.</p>
                </div>
                <div class="service-card glass-panel">
                    <div class="service-icon-wrapper"><i class="fa-solid fa-shield-halved"></i></div>
                    <h3>Sicurezza H24</h3>
                    <p>Sistema di telecamere a circuito chiuso e vigilanza armata notturna. Dormi tranquillo, alla tua barca ci pensiamo noi.</p>
                </div>
                <div class="service-card glass-panel">
                    <div class="service-icon-wrapper"><i class="fa-solid fa-wifi"></i></div>
                    <h3>Wi-Fi Fibra Molo</h3>
                    <p>Connessione internet ad altissima velocità dedicata ai diportisti. Lavora in smart working direttamente dal pozzetto.</p>
                </div>
            </div>

            <div class="cta-bottom">
                <a href="mappa-porto.php" class="btn-premium-large"><i class="fa-solid fa-anchor"></i> Scegli il tuo Posto in Banchina</a>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>