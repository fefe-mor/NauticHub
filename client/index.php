<?php
/* sessione */
session_start();

/* check login */
$is_logged = isset($_SESSION['loggato']) && $_SESSION['loggato'] === true;

/* link */
$link_auth = $is_logged ? 'dashboard.php' : 'auth.php';
$testo_auth = $is_logged ? 'Area Personale' : 'Accedi / Registrati';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NauticHub | Il Tuo Ormeggio Smart</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;500;600;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <header class="navbar-landing">
        <div class="nav-content">
            <a href="index.php" class="logo-container">
                <img src="img/logo_nautic.png" alt="Logo NauticHub" class="logo-img">
            </a>
            <nav class="main-nav">
                <a href="#caratteristiche">Funzionalità</a>
                <a href="#chi-siamo">La Nostra Storia</a>
            </nav>
            <div class="auth-buttons">
                <a href="<?php echo $link_auth; ?>" class="btn-outline-dark"><?php echo $testo_auth; ?></a>
            </div>
        </div>
    </header>

    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Il tuo prossimo ormeggio, <br><span class="gold-text">a portata di clic.</span></h1>
            <p>Esplora le darsene, filtra per servizi e prenota il posto perfetto per la tua barca in totale autonomia.</p>
            <div class="hero-actions">
                <a href="<?php echo $link_auth; ?>" class="btn-gold btn-large">Inizia a Navigare</a>
            </div>
        </div>
    </section>

    <section id="caratteristiche" class="features-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Tutto ciò che ti serve <span class="gold-text">in mare</span></h2>
                <p class="section-subtitle">Abbiamo progettato NauticHub pensando esclusivamente alle esigenze dei capitani moderni.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"></div>
                    <h3>Garage Navale</h3>
                    <p>Registra la tua flotta inserendo tipo e dimensioni. Il sistema ricorderà le misure delle tue barche per proporti solo ormeggi 100% compatibili.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"></div>
                    <h3>Mappe Interattive</h3>
                    <p>Dimentica le lunghe telefonate. Esplora le planimetrie dei porti e visualizza in tempo reale quali posti sono liberi e quali occupati.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"></div>
                    <h3>Smart Booking</h3>
                    <p>Hai bisogno di corrente 380V, acqua dolce o servizio di lavaggio? Imposta i filtri e prenota istantaneamente l'ormeggio con i servizi che desideri.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="chi-siamo" class="chi-siamo-section">
        <div class="container">
            <div class="chi-siamo-grid">
                <div class="chi-siamo-text">
                    <h2 class="section-title">Nati sulla <span class="gold-text">banchina</span></h2>
                    <p>Tutto è iniziato in una calda giornata d'agosto. Da diportisti appassionati, eravamo stanchi dell'incertezza, delle attese al VHF e delle comunicazioni frammentarie con le capitanerie per trovare un semplice posto barca.</p>
                    <p>Così è nata <strong>NauticHub</strong>. La nostra missione è digitalizzare l'esperienza di ormeggio, offrendo a chi naviga uno strumento elegante, visivo e immediato per vivere il mare senza stress.</p>
                    <a href="<?php echo $link_auth; ?>" class="btn-outline-dark" style="margin-top: 1.5rem;">Unisciti a Noi</a>
                </div>
                <div class="chi-siamo-image">
                    <div class="image-placeholder">
                        <span style="font-size: 4rem;">⚓</span>
                        <h3>Passione e Tecnologia</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container text-center">
            <h2>Pronto a mollare gli ormeggi?</h2>
            <p>Crea il tuo account gratuito e aggiungi la tua prima barca al garage.</p>
            <a href="<?php echo $link_auth; ?>" class="btn-gold btn-large" style="margin-top: 2rem;">Accedi a NauticHub</a>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container footer-content">
            <div class="footer-brand">
                <img src="img/logo_nautic.png" alt="Logo NauticHub" class="footer-logo-img">
                <p>La rivoluzione digitale dell'ormeggio.</p>
            </div>
            <div class="footer-links">
                <a href="#caratteristiche">Funzionalità</a>
                <a href="#chi-siamo">La Nostra Storia</a>
                <a href="<?php echo $link_auth; ?>">Area Personale</a>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 NauticHub. Tutti i diritti riservati.
        </div>
    </footer>

    <script src="js/index.js"></script>
</body>
</html>