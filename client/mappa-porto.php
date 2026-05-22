<?php
/* --- SEZIONE 1: PHP LOGIC --- */
session_start();
require_once '../server/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'diportista') { 
    header("Location: auth.php"); 
    exit; 
}

$nome_utente = $_SESSION['nome_utente'] ?? 'Capitano';
$utente_id = $_SESSION['utente_id'];

$stmtBarche = $pdo->prepare("SELECT * FROM barche WHERE utente_id = ?");
$stmtBarche->execute([$utente_id]);
$mie_barche = $stmtBarche->fetchAll();

$stmtPren = $pdo->query("SELECT posto FROM prenotazioni");
$posti_occupati = $stmtPren->fetchAll(PDO::FETCH_COLUMN) ?: [];

// Reintegrato il molo F come normale molo prenotabile per Maxi Yacht
$struttura_moli = [
    'A' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 6, 'dimensione' => 'posto-piccola'],
    'B' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 4, 'dimensione' => 'posto-media'],
    'C' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 6, 'dimensione' => 'posto-piccola'],
    'D' => ['tipo' => 'Maxi Yacht (Max 30m)', 'posti' => 4, 'dimensione' => 'posto-yacht'],
    'E' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 8, 'dimensione' => 'posto-media'],
    'F' => ['tipo' => 'Maxi Yacht (Max 30m)', 'posti' => 8, 'dimensione' => 'posto-yacht'],
    'G' => ['tipo' => 'Maxi Yacht (Max 30m)', 'posti' => 5, 'dimensione' => 'posto-yacht'],
    'H' => ['tipo' => 'Maxi Yacht (Max 30m)', 'posti' => 5, 'dimensione' => 'posto-yacht'],
    'I' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 6, 'dimensione' => 'posto-media'],
    'J' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 10, 'dimensione' => 'posto-piccola'],
    'K' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 10, 'dimensione' => 'posto-piccola'],
    'L' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 8, 'dimensione' => 'posto-piccola'],
    'M' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 6, 'dimensione' => 'posto-media'],
    'N' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 6, 'dimensione' => 'posto-media']
];

function statoPosto($codice, $occupati, $classe_dimensione) {
    return in_array($codice, $occupati) ? "occupato $classe_dimensione" : 'libero'; 
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
</head>
<body class="booking-premium-theme">

    <header class="dash-header">
        <div class="nav-content container">
            <div class="logo-area">
                <a href="index.php" class="text-logo" style="display:flex; align-items:baseline;">
                    Nautic<span class="gradient-logo-accent">Hub</span> <span class="port-location" style="color:var(--text-muted); font-size:1.1rem; margin-left:10px; font-style:normal;">Genova</span>
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
                <h2><i class="fa-solid fa-compass cyan-text"></i> Prepara la Rotta</h2>
                <button id="btn-close-drawer" class="btn-close-icon"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div id="notifica-sidebar" class="sys-msg" style="display: none;"></div>

            <div class="sidebar-content-scroll">
                
                <div class="filter-section">
                    <h3 class="filter-title">1. Periodo di Sosta</h3>
                    <div class="date-grid">
                        <div class="input-glass-premium">
                            <label>Arrivo</label>
                            <div class="input-with-icon">
                                <i class="fa-regular fa-calendar-check cyan-text"></i>
                                <input type="date" id="filtro-dal" required>
                            </div>
                        </div>
                        <div class="input-glass-premium">
                            <label>Partenza</label>
                            <div class="input-with-icon">
                                <i class="fa-regular fa-calendar-xmark cyan-text"></i>
                                <input type="date" id="filtro-al" required>
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
                                $img_mini = 'img/vela.jpeg';
                                if($barca['tipo'] == 'Gommone') $img_mini = 'img/gommone.jpeg';
                                if($barca['tipo'] == 'Barca a Motore') $img_mini = 'img/motore.jpeg';
                                if($barca['tipo'] == 'Yacht') $img_mini = 'img/yacht.jpeg';
                                if($barca['tipo'] == 'Catamarano') $img_mini = 'img/catamarano.jpeg';
                            ?>

                            <label class="boat-radio-item">
                                <input type="radio" class="radio-barca" name="seleziona_barca" value="<?php echo $barca['id']; ?>" data-lunghezza="<?php echo $barca['lunghezza']; ?>" data-nome="<?php echo htmlspecialchars($barca['nome']); ?>">
                                <div class="boat-card-mini" style="display:flex; align-items:center; gap: 15px;">
                                    <div style="width: 50px; height: 50px; border-radius: 8px; background-image: url('<?php echo $img_mini; ?>'); background-size: cover; background-position: center; border: 1px solid rgba(255,255,255,0.1);"></div>
                                    <div class="boat-info-mini" style="flex: 1;">
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
                    <h2>Planimetria <span class="cyan-text">Darsena</span></h2>
                    <p>Seleziona i parametri a sinistra e clicca sui moli illuminati per visualizzare i posti disponibili.</p>
                </div>
            </div>

            <div class="map-container-glass">
                <div id="vista-immagine-porto" class="interactive-map-wrapper">
                    <div class="responsive-map-container">
                        <img src="img/mappa-porto.webp" alt="Planimetria Porto NauticHub" class="porto-base-image" onerror="this.src='img/mappa-porto.jpeg'">
                        
                        <div class="letter-pin" id="lettera-A" data-max="8"  data-380v="false" data-acqua="true"  data-lavaggio="false" data-target="Molo-A">A</div>
                        <div class="letter-pin" id="lettera-B" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="true"  data-target="Molo-B">B</div>
                        <div class="letter-pin" id="lettera-C" data-max="8"  data-380v="false" data-acqua="false" data-lavaggio="false" data-target="Molo-C">C</div>
                        <div class="letter-pin" id="lettera-D" data-max="30" data-380v="true"  data-acqua="true"  data-lavaggio="false" data-target="Molo-D">D</div>
                        <div class="letter-pin" id="lettera-E" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="false" data-target="Molo-E">E</div>
                        <div class="letter-pin" id="lettera-F" data-max="30" data-380v="true"  data-acqua="true"  data-lavaggio="true"  data-target="Molo-F">F</div>
                        <div class="letter-pin" id="lettera-G" data-max="30" data-380v="true"  data-acqua="true"  data-lavaggio="false" data-target="Molo-G">G</div>
                        <div class="letter-pin" id="lettera-H" data-max="30" data-380v="true"  data-acqua="false" data-lavaggio="false" data-target="Molo-H">H</div>
                        <div class="letter-pin" id="lettera-I" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="false" data-target="Molo-I">I</div>
                        <div class="letter-pin" id="lettera-J" data-max="8"  data-380v="false" data-acqua="false" data-lavaggio="false" data-target="Molo-J">J</div>
                        <div class="letter-pin" id="lettera-K" data-max="8"  data-380v="false" data-acqua="false" data-lavaggio="false" data-target="Molo-K">K</div>
                        <div class="letter-pin" id="lettera-L" data-max="8"  data-380v="false" data-acqua="false" data-lavaggio="false" data-target="Molo-L">L</div>
                        <div class="letter-pin" id="lettera-M" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="true"  data-target="Molo-M">M</div>
                        <div class="letter-pin" id="lettera-N" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="false" data-target="Molo-N">N</div>
                    </div>
                </div>

                <div id="dettaglio-molo-container" class="pier-board" style="display: none;">
                    <div class="pier-header">
                        <h2>Esplora <span class="cyan-text">Banchina</span></h2>
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
                <h2>Riepilogo <span class="cyan-text">Ormeggio</span></h2>
                <button type="button" class="btn-close-icon" id="btn-annulla"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div id="notifica-modal" class="sys-msg sys-error" style="display: none;"></div>

            <div class="booking-summary-glass" style="text-align: left;">
                <p style="margin-bottom: 10px; font-size: 1.05rem; color: #E2E8F0;">Confermi di voler prenotare presso <strong>Smart Marina di Genova</strong>?</p>
                <ul style="list-style: none; padding: 0; color: var(--text-soft); font-size: 0.95rem; line-height: 1.8;">
                    <li><i class="fa-solid fa-ship cyan-text" style="width:20px;"></i> Barca: <strong id="recap-barca" class="text-pure">--</strong></li>
                    <li><i class="fa-solid fa-location-dot cyan-text" style="width:20px;"></i> Molo assegnato: <strong id="posto-selezionato-id" class="cyan-text">--</strong></li>
                    <li><i class="fa-regular fa-calendar cyan-text" style="width:20px;"></i> Dal: <strong id="recap-dal" class="text-pure">--</strong> al <strong id="recap-al" class="text-pure">--</strong></li>
                </ul>
            </div>
            
            <form id="form-prenotazione" class="modal-form">
                <input type="hidden" name="posto" id="input-posto-prenotato">
                <input type="hidden" name="barca_id" id="input-barca-id">
                
                <div class="form-group-modal full-width" style="margin-bottom: 2rem;">
                    <label style="font-family: 'Montserrat', sans-serif;">Equipaggio a Bordo (Facoltativo)</label>
                    <div class="input-glass-premium">
                        <div class="input-with-icon">
                            <i class="fa-solid fa-users cyan-text"></i>
                            <input type="number" id="input-numero-persone" placeholder="Comunicabile all'arrivo">
                        </div>
                    </div>
                </div>

                <button type="submit" id="btn-conferma" class="btn-premium-solid" style="width: 100%;"><i class="fa-solid fa-anchor"></i> Autorizza Attracco</button>
            </form>
        </div>
    </div>

    <div id="toast-sistema" class="toast-premium nascondi">
        <i class="fa-solid" id="toast-icona"></i>
        <span id="toast-testo"></span>
    </div>

    <script src="js/mappa-porto.js"></script>
</body>
</html>