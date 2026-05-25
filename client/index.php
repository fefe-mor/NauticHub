<?php

session_start();
/*controllo se l'utente è loggato*/
$utente_autenticato = isset($_SESSION['loggato']) && $_SESSION['loggato'] === true;

/* se l'utente è loggato lo manda alla pagina dash,s enno all'auth */
$url_autenticazione = $utente_autenticato ? 'dashboard.php' : 'auth.php';
$etichetta_pulsante_autenticazione = $utente_autenticato ? 'Area Personale' : 'Accedi / Registrati';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NauticHub | Il Tuo Ormeggio Smart</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <link rel="stylesheet" href="css/index.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/footer.css?v=<?php echo time(); ?>">
    
</head>



<body class="nautic-theme">
    
    <header class="navbar-landing" data-aos="fade-down" data-aos-duration="800">
        <div class="nav-content container">
            <a href="index.php" class="text-logo">
                Nautic<span class="gradient-logo-accent">Hub</span>
            </a>
            <nav class="main-nav">
                <a href="#come-funziona">Come Funziona</a>
                <a href="#il-progetto">Il Progetto</a>
            </nav>
            <div class="auth-buttons">
                <a href="<?php echo $url_autenticazione; ?>" class="btn-primary-glow"><?php echo $etichetta_pulsante_autenticazione; ?></a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero-section">
            <div class="hero-video-bg" style="background-image: url('img/foto-porto.jpeg?v=<?php echo time(); ?>');">
                <div class="hero-image-overlay"></div>
            </div>
            
            <div class="hero-content container" data-aos="fade-up" data-aos-duration="1000">
                <div class="badge-tech"><i class="fa-solid fa-location-dot"></i> Nuova apertura: Porto di Genova</div>
                <h1>Il tuo ormeggio, <br><span class="gold-gradient-text">a portata di rotta.</span></h1>
                <p>Esplora le darsene, filtra per dimensioni della barca e servizi, e blocca il posto perfetto. Zero stress via radio, pagamento comodamente in capitaneria.</p>
                <div class="hero-actions">
                    <a href="<?php echo $url_autenticazione; ?>" class="btn-gold btn-large">Inizia a Navigare <i class="fa-solid fa-arrow-right"></i></a>
                    <a href="#come-funziona" class="btn-outline-light btn-large">Scopri come funziona</a>
                </div>
            </div>
        </section>

       <section class="partners-section">
            <p class="partners-title">Il network in espansione nei principali porti italiani</p>
            <div class="partners-ticker">
                <div class="ticker-track">
                    <span>📍 Marina di Genova <strong style="color: #00F2FE;">(Attivo)</strong></span>
                    <span>📍 Porto Antico di Genova <strong style="color: #888;">(Coming Soon)</strong></span>
                    <span>📍 Marina di Portofino <em style="color: #888;">(Coming Soon)</em></span>
                    <span>📍 Porto di Livorno <em style="color: #888;">(Coming Soon)</em></span>
                    <span>📍 Porto Mirabello <em style="color: #888;">(Coming Soon)</em></span>
                </div>
                <div class="ticker-track">
                    <span>📍 Marina di Genova <strong style="color: #00F2FE;">(Attivo)</strong></span>
                    <span>📍 Porto Antico di Genova <strong style="color:  #888;">(Coming Soon)</strong></span>
                    <span>📍 Marina di Portofino <em style="color: #888;">(Coming Soon)</em></span>
                    <span>📍 Porto di Livorno <em style="color: #888;">(Coming Soon)</em></span>
                    <span>📍 Porto Mirabello <em style="color: #888;">(Coming Soon)</em></span>
                </div>
            </div>
        </section>

        <section id="come-funziona" class="features-section">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2 class="section-title">Tecnologia al servizio <span class="gold-text">del capitano</span></h2>
                    <p class="section-subtitle">Abbiamo digitalizzato l'intera esperienza di attracco. Prenota dal telefono, paga comodamente al tuo arrivo in banchina.</p>
                </div>

                <div class="features-bento-grid">
                    <div class="bento-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="icon-wrapper"><i class="fa-solid fa-anchor"></i></div>
                        <h3>Garage Navale Intelligente</h3>
                        <p>Inserisci Lunghezza, Baglio e Pescaggio della tua barca. Il nostro sistema filtrerà automaticamente le banchine mostrandoti solo i posti 100% compatibili.</p>
                    </div>
                    <div class="bento-card highlight-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="icon-wrapper"><i class="fa-solid fa-map-location-dot"></i></div>
                        <h3>Mappe Interattive Live</h3>
                        <p>Dimentica le lunghe attese sul canale VHF. Visualizza le planimetrie vettoriali del porto e controlla la disponibilità dei posti in tempo reale.</p>
                    </div>
                    <div class="bento-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="icon-wrapper"><i class="fa-solid fa-plug-circle-bolt"></i></div>
                        <h3>Filtri Servizi Avanzati</h3>
                        <p>Hai bisogno di colonnina 380V, erogazione d'acqua dolce o assistenza all'ormeggio? Trova la darsena che offre esattamente i servizi che cerchi.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="il-progetto" class="about-section">
            <div class="container about-grid">
                <div class="about-image-wrapper" data-aos="fade-right">
                    <div class="about-image-placeholder" style="background-image: url('img/tramonto.jpeg');">
                        <div class="floating-badge">
                            <i class="fa-solid fa-handshake"></i>
                            <span>Paga direttamente all'arrivo</span>
                        </div>
                    </div>
                </div>
                <div class="about-text" data-aos="fade-left">
                    <h2 class="section-title">L'innovazione che <span class="gold-text">mancava.</span></h2>
                    <p class="lead-text">Ci siamo resi conto di un paradosso evidente nel panorama attuale.</p>
                    <p>Mentre in ogni settore la digitalizzazione ha semplificato i processi, per assicurarsi un posto barca bisogna ancora affidarsi a telefonate o chiamate radio all'ultimo minuto.</p>
                    <p>NauticHub nasce per colmare questo vuoto. Tu blocchi il posto tramite l'app, la capitaneria riceve la notifica e prepara il tuo arrivo. Il pagamento? Lo salderai comodamente al desk una volta ormeggiato in sicurezza.</p>
                    <ul class="about-list">
                        <li><i class="fa-solid fa-check"></i> Zero attese sul canale VHF 16</li>
                        <li><i class="fa-solid fa-check"></i> Posto compatibile garantito</li>
                        <li><i class="fa-solid fa-check"></i> Pagamento flessibile in struttura</li>
                    </ul>
                    <a href="<?php echo $url_autenticazione; ?>" class="btn-outline-light mt-4" style="margin-top: 1.5rem; display: inline-block;">Accedi al sistema</a>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="cta-overlay"></div>
            <div class="container text-center cta-content" data-aos="zoom-in">
                <h2>Pronto a tracciare la rotta?</h2>
                <p>Crea il tuo account gratuito, inserisci la tua barca nel garage navale e scopri quanto è facile prenotare il tuo prossimo ormeggio a Genova.</p>
                <a href="<?php echo $url_autenticazione; ?>" class="btn-gold btn-large glow-effect">Accedi a NauticHub</a>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ once: true, offset: 50 });
    </script>
    <script src="js/index.js"></script>
</body>
</html>