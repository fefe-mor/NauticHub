<?php
/**
 * File: mappa-porto.php
 * Gestione interattiva della mappa, filtri disponibilità e prenotazione posti barca.
 */
session_start();
require_once '../server/database.php';

/* Controllo di sicurezza: accesso consentito solo ai diportisti loggati */
if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'diportista') { 
    header("Location: auth.php"); 
    exit; 
}

$nome_utente = $_SESSION['nome_utente'] ?? 'Capitano';
$utente_id = $_SESSION['utente_id'];

/* Recupero delle barche dell'utente dal database */
$stmtBarche = $pdo->prepare("SELECT * FROM barche WHERE utente_id = ?");
$stmtBarche->execute([$utente_id]);
$mie_barche = $stmtBarche->fetchAll();

// I posti occupati vengono calcolati dinamicamente in base alle date da JS
$posti_occupati = [];

/* * Struttura e classificazione dei moli.
 * Reintegrato il molo F come normale molo prenotabile per Maxi Yacht/Barche Medie
 */
$struttura_moli = [
    // --- GRANDI (Dai 21m in poi) ---
    'A' => ['tipo' => 'Maxi Yacht (21m+)', 'posti' => 6, 'dimensione' => 'posto-yacht'],
    'B' => ['tipo' => 'Maxi Yacht (21m+)', 'posti' => 4, 'dimensione' => 'posto-yacht'],
    'D' => ['tipo' => 'Maxi Yacht (21m+)', 'posti' => 4, 'dimensione' => 'posto-yacht'],
    'G' => ['tipo' => 'Maxi Yacht (21m+)', 'posti' => 5, 'dimensione' => 'posto-yacht'],
    
    // --- MEDI (Dai 13 ai 20m) ---
    'C' => ['tipo' => 'Barche Medie (13-20m)', 'posti' => 6, 'dimensione' => 'posto-media'],
    'E' => ['tipo' => 'Barche Medie (13-20m)', 'posti' => 8, 'dimensione' => 'posto-media'],
    'F' => ['tipo' => 'Barche Medie (13-20m)', 'posti' => 8, 'dimensione' => 'posto-media'],
    'H' => ['tipo' => 'Barche Medie (13-20m)', 'posti' => 5, 'dimensione' => 'posto-media'],
    
    // --- PICCOLI (Dai 5 ai 12m) ---
    'I' => ['tipo' => 'Natanti (5-12m)', 'posti' => 6, 'dimensione' => 'posto-piccola'],
    'J' => ['tipo' => 'Natanti (5-12m)', 'posti' => 10, 'dimensione' => 'posto-piccola'],
    'K' => ['tipo' => 'Natanti (5-12m)', 'posti' => 10, 'dimensione' => 'posto-piccola'],
    'L' => ['tipo' => 'Natanti (5-12m)', 'posti' => 8, 'dimensione' => 'posto-piccola'],
    'M' => ['tipo' => 'Natanti (5-12m)', 'posti' => 6, 'dimensione' => 'posto-piccola'],
    'N' => ['tipo' => 'Natanti (5-12m)', 'posti' => 6, 'dimensione' => 'posto-piccola']
];

/**
 * Determina le classi CSS per lo stato visivo di un posto barca.
 */
function statoPosto($codice, $occupati, $classe_dimensione) {
    $stato = in_array($codice, $occupati) ? 'occupato' : 'libero';
    return "$stato $classe_dimensione"; 
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mappa Porto | NauticHub Genova</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/mappa-porto.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/footer.css?v=<?php echo time(); ?>">
</head>
<body class="booking-premium-theme">

    <header class="dash-header">
        <div class="nav-content container">
            <div class="logo-area">
                <a href="index.php" class="text-logo logo-flessibile">
                    Nautic<span class="gradient-logo-accent">Hub</span> 
                    <span class="port-location testo-luogo-porto">Genova</span>
                </a>
            </div>
            <div class="user-info">
                <a href="presentazione-genova.php" class="btn-outline-premium"><i class="fa-solid fa-arrow-left"></i> <span class="logout-text-hide">Dettagli Porto</span></a>
                <span class="welcome-text"><i class="fa-regular fa-user"></i> Cap. <strong><?php echo htmlspecialchars($nome_utente); ?></strong></span>
            </div>
        </div>
    </header>

    <main class="booking-main-layout">
        
        <aside class="booking-sidebar" id="mobile-filter-drawer">
            <div class="sidebar-drag-indicator"></div>
            
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-compass testo-ciano"></i> Prepara la Rotta</h2>
                <button id="btn-close-drawer" class="btn-close-icon"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div id="notifica-sidebar" class="sys-msg nascosto"></div>

            <div class="sidebar-content-scroll">
                
                <div class="filter-section">
                    <h3 class="filter-title">1. Periodo di Sosta</h3>
                    <div class="date-grid">
                        <div class="input-glass-premium">
                            <label>Arrivo</label>
                            <div class="input-with-icon">
                                <i class="fa-regular fa-calendar-check testo-ciano"></i>
                                <input type="date" id="filtro-dal" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="input-glass-premium">
                            <label>Partenza</label>
                            <div class="input-with-icon">
                                <i class="fa-regular fa-calendar-xmark testo-ciano"></i>
                                <input type="date" id="filtro-al" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-section">
                    <h3 class="filter-title">2. Seleziona Imbarcazione</h3>
                    <div class="boat-list">
                        <?php if(empty($mie_barche)): ?>
                            <div class="sys-msg sys-error"><i class="fa-solid fa-triangle-exclamation"></i> Nessuna barca registrata nel garage.</div>
                        <?php else: ?>
                            <?php foreach($mie_barche as $barca): 
                                // Gestione immagini miniatura
                                $img_mini = 'img/vela.jpeg';
                                if($barca['tipo'] == 'Gommone') $img_mini = 'img/gommone.jpeg';
                                if($barca['tipo'] == 'Barca a Motore') $img_mini = 'img/motore.jpeg';
                                if($barca['tipo'] == 'Yacht') $img_mini = 'img/yacht.jpeg';
                                if($barca['tipo'] == 'Catamarano') $img_mini = 'img/catamarano.jpeg';
                            ?>

                            <label class="boat-radio-item">
                                <input type="radio" class="radio-barca" name="seleziona_barca" value="<?php echo $barca['id']; ?>" data-lunghezza="<?php echo $barca['lunghezza']; ?>" data-nome="<?php echo htmlspecialchars($barca['nome']); ?>">
                                <div class="boat-card-mini riga-miniatura-barca">
                                    <div class="box-immagine-barca" style="background-image: url('<?php echo $img_mini; ?>');"></div>
                                    <div class="boat-info-mini dettagli-barca-flex">
                                        <strong class="boat-name"><?php echo htmlspecialchars($barca['nome']); ?></strong>
                                        <span class="boat-specs"><?php echo $barca['lunghezza']; ?>m &bull; <?php echo $barca['tipo']; ?></span>
                                    </div>
                                    <div class="check-indicator"><i class="fa-solid fa-circle-check"></i></div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="filter-section">
                    <h3 class="filter-title">3. Servizi in Banchina</h3>
                    <div class="services-flex">
                        <label class="service-chip"><input type="checkbox" id="filtro-corrente" class="checkbox-servizio"><span><i class="fa-solid fa-bolt"></i> 380V</span></label>
                        <label class="service-chip"><input type="checkbox" id="filtro-acqua" class="checkbox-servizio"><span><i class="fa-solid fa-droplet"></i> Acqua</span></label>
                        <label class="service-chip"><input type="checkbox" id="filtro-lavaggio" class="checkbox-servizio"><span><i class="fa-solid fa-soap"></i> Lavaggio</span></label>
                    </div>
                </div>
            </div>
            
            <div class="mobile-apply-wrapper">
                <button id="btn-apply-filters" class="btn-premium-solid"><i class="fa-solid fa-map-location-dot"></i> Vedi Disponibilità</button>
            </div>
        </aside>

        <section class="booking-map-area">
            
            <div class="scenic-banner" style="background-image: url('img/vista.jpeg');">
                <div class="banner-overlay"></div>
                <div class="banner-text">
                    <h2>Planimetria <span class="testo-ciano">Darsena</span></h2>
                    <p>Seleziona i parametri a sinistra e clicca sui moli illuminati per visualizzare i posti disponibili.</p>
                </div>
            </div>

            <div class="map-container-glass">
                
                <div id="vista-immagine-porto" class="interactive-map-wrapper">
                    <div class="responsive-map-container">
                        <img src="img/mappa-porto.jpeg" alt="Planimetria Porto NauticHub" class="porto-base-image">
                        
                        <div class="letter-pin" id="lettera-A" data-min="21" data-max="99" data-380v="true" data-acqua="true"  data-lavaggio="false" data-target="Molo-A">A</div>
                        <div class="letter-pin" id="lettera-B" data-min="21" data-max="99" data-380v="true" data-acqua="true"  data-lavaggio="true"  data-target="Molo-B">B</div>
                        <div class="letter-pin" id="lettera-D" data-min="21" data-max="99" data-380v="true"  data-acqua="true"  data-lavaggio="true" data-target="Molo-D">D</div>
                        <div class="letter-pin" id="lettera-G" data-min="21" data-max="99" data-380v="true"  data-acqua="true"  data-lavaggio="false" data-target="Molo-G">G</div>

                        <div class="letter-pin" id="lettera-C" data-min="13" data-max="20" data-380v="true" data-acqua="false" data-lavaggio="false" data-target="Molo-C">C</div>
                        <div class="letter-pin" id="lettera-E" data-min="13" data-max="20" data-380v="true" data-acqua="true"  data-lavaggio="true" data-target="Molo-E">E</div>
                        <div class="letter-pin" id="lettera-F" data-min="13" data-max="20" data-380v="true"  data-acqua="true"  data-lavaggio="true"  data-target="Molo-F">F</div>
                        <div class="letter-pin" id="lettera-H" data-min="13" data-max="20" data-380v="true"  data-acqua="true" data-lavaggio="false" data-target="Molo-H">H</div>

                        <div class="letter-pin" id="lettera-I" data-min="5" data-max="12" data-380v="true" data-acqua="true"  data-lavaggio="false" data-target="Molo-I">I</div>
                        <div class="letter-pin" id="lettera-J" data-min="5" data-max="12" data-380v="true" data-acqua="false" data-lavaggio="false" data-target="Molo-J">J</div>
                        <div class="letter-pin" id="lettera-K" data-min="5" data-max="12" data-380v="true" data-acqua="true" data-lavaggio="false" data-target="Molo-K">K</div>
                        <div class="letter-pin" id="lettera-L" data-min="5" data-max="12" data-380v="true" data-acqua="false" data-lavaggio="true" data-target="Molo-L">L</div>
                        <div class="letter-pin" id="lettera-M" data-min="5" data-max="12" data-380v="true" data-acqua="true"  data-lavaggio="true"  data-target="Molo-M">M</div>
                        <div class="letter-pin" id="lettera-N" data-min="5" data-max="12" data-380v="true" data-acqua="true"  data-lavaggio="false" data-target="Molo-N">N</div>                                              
                    </div>
                </div>

                <div id="dettaglio-molo-container" class="pier-board" style="display: none;">
                    <div class="pier-header">
                        <h2>Esplora <span class="testo-ciano">Banchina</span></h2>
                        <button id="btn-indietro" class="btn-outline-premium"><i class="fa-solid fa-arrow-left-long"></i> Visione Aerea</button>
                    </div>
                    
                    <div class="pier-scroll-track">
                        <?php foreach($struttura_moli as $lettera => $dati): ?>
                        <div class="molo-scene" id="Molo-<?php echo $lettera; ?>" style="display: none;">
                            
                            <div class="molo-title-wrapper">
                                <h3>Pontile <?php echo $lettera; ?></h3>
                                <span class="molo-badge"><?php echo $dati['tipo']; ?></span>
                            </div>
                            
                            <div class="water-basin">
                                <div class="wood-dock">
                                    <div class="dock-texture"></div>
                                </div>
                                
                                <div class="mooring-slots">
                                    <?php 
                                    for($i = 1; $i <= $dati['posti']; $i++): 
                                        $codice = $lettera . str_pad($i, 2, '0', STR_PAD_LEFT); 
                                        $isLibero = !in_array($codice, $posti_occupati);
                                    ?>
                                        <div class="slot-wrapper">
                                            <div class="cleat"></div>
                                            <div class="posto-ui <?php echo statoPosto($codice, $posti_occupati, $dati['dimensione']); ?> <?php echo $isLibero ? 'posto-cliccabile' : ''; ?>" data-codice="<?php echo $codice; ?>">
                                                <span class="badge-posto"><?php echo $codice; ?></span>
                                                <div class="water-ripple"></div>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        
        <button id="fab-open-filters" class="fab-mobile-filters">
            <i class="fa-solid fa-sliders"></i>
        </button>

    </main>

    <?php include 'footer.php'; ?>

    <div id="prenotazioneModal" class="modal-overlay nascosto" style="display: none;">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Riepilogo <span class="testo-ciano">Ormeggio</span></h2>
                <button type="button" class="btn-close-icon" id="btn-annulla"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div id="notifica-modal" class="sys-msg sys-error" style="display: none;"></div>

            <div class="booking-summary-glass testo-sinistra">
                <p class="testo-riepilogo-conferma">Confermi di voler prenotare presso <strong>Smart Marina di Genova</strong>?</p>
                <ul class="lista-riepilogo-prenotazione">
                    <li><i class="fa-solid fa-ship testo-ciano icona-fissa"></i> Barca: <strong id="recap-barca" class="testo-primario">--</strong></li>
                    <li><i class="fa-solid fa-location-dot testo-ciano icona-fissa"></i> Molo assegnato: <strong id="posto-selezionato-id" class="testo-ciano">--</strong></li>
                    <li><i class="fa-regular fa-calendar testo-ciano icona-fissa"></i> Dal: <strong id="recap-dal" class="testo-primario">--</strong> al <strong id="recap-al" class="testo-primario">--</strong></li>
                </ul>
            </div>
            
            <form id="form-prenotazione" class="modal-form">
                <input type="hidden" name="posto" id="input-posto-prenotato">
                <input type="hidden" name="barca_id" id="input-barca-id">
                
                <div class="form-group-modal full-width margine-inferiore-medio">
                    <label class="etichetta-secondaria">Equipaggio a Bordo (Facoltativo)</label>
                    <div class="input-glass-premium">
                        <div class="input-with-icon">
                            <i class="fa-solid fa-users testo-ciano"></i>
                            <input type="number" id="input-numero-persone" placeholder="Comunicabile all'arrivo">
                        </div>
                    </div>
                </div>

                <button type="submit" id="btn-conferma" class="btn-premium-solid larghezza-totale"><i class="fa-solid fa-anchor"></i> Autorizza Attracco</button>
            </form>
        </div>
    </div>

    <script src="js/mappa-porto.js"></script>
</body>
</html>