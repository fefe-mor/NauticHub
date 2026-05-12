<?php
/* sessione */
session_start();

require_once '../server/database.php';

/* check ruolo */
if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'diportista') { 
    header("Location: auth.php"); 
    exit; 
}

$nome_utente = $_SESSION['nome_utente'] ?? 'Capitano';
$utente_id = $_SESSION['utente_id'];

/* 1. Prendo le barche dal DB */
$stmtBarche = $pdo->prepare("SELECT * FROM barche WHERE utente_id = ?");
$stmtBarche->execute([$utente_id]);
$mie_barche = $stmtBarche->fetchAll();

/* 2. Prendo tutti i posti occupati nel porto (da tutti gli utenti) */
$stmtPren = $pdo->query("SELECT posto FROM prenotazioni");
$posti_occupati = $stmtPren->fetchAll(PDO::FETCH_COLUMN);

if (!$posti_occupati) $posti_occupati = [];

/* struttura porto originale */
$struttura_moli = [
    'A' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 6, 'dimensione' => 'posto-piccola', 'yacht' => false, 'servizi' => []],
    'B' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 8, 'dimensione' => 'posto-media', 'yacht' => false, 'servizi' => []],
    'C' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 6, 'dimensione' => 'posto-piccola', 'yacht' => false, 'servizi' => []],
    'D' => ['tipo' => 'Maxi Yacht (Max 30m)', 'posti' => 4, 'dimensione' => 'posto-yacht', 'yacht' => true, 'servizi' => []],
    'E' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 8, 'dimensione' => 'posto-media', 'yacht' => false, 'servizi' => []],
    'F' => ['tipo' => 'Maxi Yacht (Max 30m)', 'posti' => 4, 'dimensione' => 'posto-yacht', 'yacht' => true, 'servizi' => []],
    'G' => ['tipo' => 'Maxi Yacht (Max 30m)', 'posti' => 5, 'dimensione' => 'posto-yacht', 'yacht' => true, 'servizi' => []],
    'H' => ['tipo' => 'Maxi Yacht (Max 30m)', 'posti' => 5, 'dimensione' => 'posto-yacht', 'yacht' => true, 'servizi' => []],
    'I' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 6, 'dimensione' => 'posto-media', 'yacht' => false, 'servizi' => []],
    'J' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 10, 'dimensione' => 'posto-piccola', 'yacht' => false, 'servizi'=> []],
    'K' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 10, 'dimensione' => 'posto-piccola', 'yacht' => false, 'servizi'=> []],
    'L' => ['tipo' => 'Transito Veloce (Max 8m)', 'posti' => 8, 'dimensione' => 'posto-piccola', 'yacht' => false, 'servizi'=> []],
    'M' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 6, 'dimensione' => 'posto-media', 'yacht' => false, 'servizi'=> []],
    'N' => ['tipo' => 'Barche Medie (Max 15m)', 'posti' => 6, 'dimensione' => 'posto-media', 'yacht' => false, 'servizi' => []]
];

function statoPosto($codice, $occupati, $classe_dimensione) {
    return in_array($codice, $occupati) ? "occupato $classe_dimensione" : 'libero'; 
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenota | NauticHub Bari</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=5000">
    <style>
        /* IL TUO CSS RIMANE IDENTICO. L'ho tagliato per brevità nella risposta, 
           ma TU DEVI LASCIARE IL CSS CHE AVEVI NEL TUO FILE ORIGINALE QUI! */
        .boat-selector { display: flex; flex-direction: column; gap: 12px; margin-top: 1rem; }
        .boat-card-radio input { display: none; } 
        .boat-card-content { display: flex; align-items: center; gap: 15px; padding: 12px 15px; border: 2px solid #eee; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; background: white; }
        .boat-card-content:hover { border-color: var(--gold); background: #fdfbf7; transform: translateY(-2px); }
        .boat-card-radio input:checked + .boat-card-content { border-color: var(--navy-blue); background: var(--navy-blue); color: white; }
        .boat-card-radio input:checked + .boat-card-content .boat-info span { color: #cbd5e1; }
        .boat-icon { font-size: 1.8rem; }
        .boat-info { display: flex; flex-direction: column; }
        .boat-info strong { font-size: 1rem; }
        .boat-info span { font-size: 0.8rem; color: #666; font-weight: 600; }
        .servizi-toggles { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 1rem; }
        .toggle-pill input { display: none; } 
        .toggle-pill span { display: inline-block; padding: 8px 16px; border: 2px solid #eee; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: 0.3s; color: var(--navy-blue); background: white; }
        .toggle-pill span:hover { border-color: var(--gold); }
        .toggle-pill input:checked + span { background: var(--navy-blue); color: white; border-color: var(--navy-blue); }
       /* ... qui sopra c'è il codice di .toggle-pill ecc. che lasci intatto ... */

       /* ==========================================
           STILE LETTERE - PULIZIA TOTALE
           ========================================== */
        .lettera-pontile { 
            position: absolute; 
            color: #ef4444 !important; 
            font-weight: 800; 
            font-size: 1.6rem; 
            background: transparent !important; 
            background-color: transparent !important;
            border: none !important; 
            box-shadow: none !important; 
            transition: transform 0.3s ease !important; /* Facciamo animare SOLO la grandezza, nient'altro */
            cursor: pointer; 
            transform: translate(-50%, -50%); 
            z-index: 5; 
            
            /* RIMOSSO IL TEXT-SHADOW! Era lui a creare l'alone scuro */
            text-shadow: none !important; 
            
            outline: none !important; 
            -webkit-tap-highlight-color: transparent !important; 
        }

        /* IL KILLER DELLO SFONDO NERO:
           Blocchiamo qualsiasi colore quando ci passi sopra col mouse 
        */
        .lettera-pontile:hover, 
        .lettera-pontile:focus {
            background: transparent !important;
            background-color: transparent !important;
            box-shadow: none !important;
        }

        /* Disattiva eventuali "elementi fantasma" creati per sbaglio nel file style.css */
        .lettera-pontile::before, 
        .lettera-pontile::after { 
            display: none !important; 
        }

        /* STATO COMPATIBILE E INCOMPATIBILE */
        .lettera-pontile.compatibile { 
            color: #08e258 !important; 
            background: transparent !important; 
            border: none !important; 
            box-shadow: none !important; 
            transform: translate(-50%, -50%)  !important; 
            z-index: 10; 
        }
        
        .lettera-pontile.compatibile:hover { 
            transform: translate(-50%, -50%) scale(1.2) !important; 
            /* Riaffermiamo che anche da ingrandita non deve avere sfondo */
            background: transparent !important;
            background-color: transparent !important;
        }
        
        .lettera-pontile.incompatibile { 
            color: #94a3b8 !important; 
            background: transparent !important; 
            border: none !important; 
            box-shadow: none !important; 
            pointer-events: none; 
            opacity: 0.7; 
        }
    </style>
</head>
<body>

    <header class="navbar-premium">
        <div class="nav-content">
            <div class="logo">
                <img src="img/logo_nautic.png" alt="Logo NauticHub" style="height: 80px; vertical-align: middle; margin-right: 10px;">
                NauticHub <span class="gold-text">Bari</span>
            </div>
            <div class="user-info">
                <a href="dashboard.php" class="btn-outline-light" style="margin-right: 15px; border: none; text-decoration: underline;">← Area Personale</a>
                <span style="color: white; margin-right: 15px; font-weight: 600;">Benvenuto, <?php echo htmlspecialchars($nome_utente); ?></span>
                <a href="../server/logout.php" class="btn-outline-light">Disconnetti</a>
            </div>
        </div>
    </header>

    <main class="dashboard-main">
        <aside class="pannello-filtri" style="padding: 0.9rem; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-top: 5px solid var(--gold);">
            <h2 style="color: var(--navy-blue); font-family: 'Playfair Display', serif; margin-bottom: 0.5rem;">Cerca Ormeggio</h2>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 1.5rem;">Seleziona la barca per illuminare la mappa.</p> 
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="font-weight: 600; font-size: 0.95rem; color: var(--navy-blue);"> Periodo di Sosta:</label>
                <div style="display: flex; gap: 10px; margin-top: 0.5rem;">
                    <div style="flex: 1;">
                        <span style="font-size: 0.75rem; color: #666; display: block; margin-bottom: 2px;">Arrivo</span>
                        <input type="date" id="filtro-dal" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit;">
                    </div>
                    <div style="flex: 1;">
                        <span style="font-size: 0.75rem; color: #666; display: block; margin-bottom: 2px;">Partenza</span>
                        <input type="date" id="filtro-al" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit;">
                    </div>
                </div>
            </div>

            
            <div class="form-group">
                <label style="font-weight: 600; font-size: 0.95rem; color: var(--navy-blue);"> La mia Flotta:</label>
                <div class="boat-selector" id="lista-barche">
                    <?php if(empty($mie_barche)): ?>
                        <p style="color: #e74c3c; font-size: 0.9rem;">Nessuna barca nel garage! Torna all'Area Personale per aggiungerne una.</p>
                    <?php else: ?>
                        <?php foreach($mie_barche as $barca): 
                            $icona = '⛵'; 
                            if($barca['tipo'] == 'Gommone') $icona = '🚤';
                            if($barca['tipo'] == 'Barca a Motore') $icona = '🛥️';
                            if($barca['tipo'] == 'Yacht') $icona = '🛳️';
                        ?>
                        <label class="boat-card-radio">
                            <input type="radio" class="radio-barca" name="seleziona_barca" value="<?php echo $barca['id']; ?>" data-lunghezza="<?php echo $barca['lunghezza']; ?>" data-nome="<?php echo htmlspecialchars($barca['nome']); ?>">
                            <div class="boat-card-content">
                                <span class="boat-icon"><?php echo $icona; ?></span>
                                <div class="boat-info">
                                    <strong><?php echo htmlspecialchars($barca['nome']); ?></strong>
                                    <span>Lunghezza: <?php echo $barca['lunghezza']; ?>m</span>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group" style="margin-top: 2rem;">
                <label style="font-weight: 600; font-size: 0.95rem; color: var(--navy-blue);"> Servizi Aggiuntivi:</label>
                <div class="servizi-toggles">
                    <label class="toggle-pill"><input type="checkbox" id="filtro-corrente" class="checkbox-servizio"><span> 380V</span></label>
                    <label class="toggle-pill"><input type="checkbox" id="filtro-acqua" class="checkbox-servizio"><span> Acqua</span></label>
                    <label class="toggle-pill"><input type="checkbox" id="filtro-lavaggio" class="checkbox-servizio"><span> Lavaggio</span></label>
                </div>
            </div>
        </aside>

        <section class="mappa-section">
            <div class="mappa-header">
                <h2 style="color: var(--navy-blue); font-family: 'Playfair Display', serif;">Planimetria Darsena</h2>
                <p>Clicca su una lettera verde per vedere i posti barca disponibili in quel molo.</p>
            </div>

            <div class="contenitore-mappa-immagine" id="vista-immagine-porto">
                <img src="img/mappa-porto.jpeg" alt="Mappa del Porto" class="immagine-sfondo-porto">
                
                <div class="lettera-pontile" id="lettera-A" data-max="8"  data-380v="false" data-acqua="true"  data-lavaggio="false" data-target="Molo-A">A</div>
                <div class="lettera-pontile" id="lettera-B" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="true"  data-target="Molo-B">B</div>
                <div class="lettera-pontile" id="lettera-C" data-max="8"  data-380v="false" data-acqua="false" data-lavaggio="false" data-target="Molo-C">C</div>
                <div class="lettera-pontile" id="lettera-D" data-max="30" data-380v="true"  data-acqua="true"  data-lavaggio="false" data-target="Molo-D">D</div>
                <div class="lettera-pontile" id="lettera-E" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="false" data-target="Molo-E">E</div>
                <div class="lettera-pontile" id="lettera-F" data-max="30" data-380v="true"  data-acqua="true"  data-lavaggio="true"  data-target="Molo-F">F</div>
                <div class="lettera-pontile" id="lettera-G" data-max="30" data-380v="true"  data-acqua="true"  data-lavaggio="false" data-target="Molo-G">G</div>
                <div class="lettera-pontile" id="lettera-H" data-max="30" data-380v="true"  data-acqua="false" data-lavaggio="false" data-target="Molo-H">H</div>
                <div class="lettera-pontile" id="lettera-I" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="false" data-target="Molo-I">I</div>
                <div class="lettera-pontile" id="lettera-J" data-max="8"  data-380v="false" data-acqua="false" data-lavaggio="false" data-target="Molo-J">J</div>
                <div class="lettera-pontile" id="lettera-K" data-max="8"  data-380v="false" data-acqua="false" data-lavaggio="false" data-target="Molo-K">K</div>
                <div class="lettera-pontile" id="lettera-L" data-max="8"  data-380v="false" data-acqua="false" data-lavaggio="false" data-target="Molo-L">L</div>
                <div class="lettera-pontile" id="lettera-M" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="true"  data-target="Molo-M">M</div>
                <div class="lettera-pontile" id="lettera-N" data-max="15" data-380v="false" data-acqua="true"  data-lavaggio="false" data-target="Molo-N">N</div>
            </div>

            <div id="dettaglio-molo-container" style="display: none;">
                <button id="btn-indietro" class="btn-outline-light" style="color:var(--navy-blue); border-color:var(--navy-blue); margin-bottom:1rem; background:white; cursor:pointer;">← Torna alla Mappa Principale</button>
                
                <div id="mappa-container">
                    <?php foreach($struttura_moli as $lettera => $dati): ?>
                    <div class="pagina-molo" id="Molo-<?php echo $lettera; ?>" style="display: none;">
                        <div class="molo-header-page">
                            <h3>Molo <?php echo $lettera; ?> - <?php echo $dati['tipo']; ?></h3>
                            <p>Posti: <?php echo $dati['posti']; ?></p>
                        </div>
                        
                        <div class="posti-grid-moderna" <?php if(isset($dati['yacht']) && $dati['yacht']) echo 'style="grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));"'; ?>>
                            <?php 
                            for($i = 1; $i <= $dati['posti']; $i++): 
                                $codice = $lettera . str_pad($i, 2, '0', STR_PAD_LEFT); 
                                $stileExtra = (isset($dati['yacht']) && $dati['yacht']) ? 'style="width: 100px; height: 200px;"' : '';
                                
                                // Se è libero, aggiungiamo la classe "posto-cliccabile" e il data-attribute (niente onclick!)
                                $isLibero = !in_array($codice, $posti_occupati);
                                $classeExtra = $isLibero ? 'posto-cliccabile' : '';
                            ?>
                                <div class="posto-ui <?php echo statoPosto($codice, $posti_occupati, $dati['dimensione']); ?> <?php echo $classeExtra; ?>" data-codice="<?php echo $codice; ?>" <?php echo $stileExtra; ?>>
                                    <span class="posto-numero"><?php echo $codice; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <div id="prenotazioneModal" class="modal nascosto">
        <div class="modal-content">
            <h3 style="color: var(--navy-blue); font-family: 'Playfair Display', serif; margin-bottom: 1rem;">Conferma Ormeggio</h3>
            <p>Confermi di voler prenotare il posto <strong id="posto-selezionato-id" style="color: var(--gold); font-size: 1.2rem;">--</strong>?</p>
            
            <form id="form-prenotazione" style="margin-top: 20px;">
                <input type="hidden" name="posto" id="input-posto-prenotato">
                <input type="hidden" name="barca_id" id="input-barca-id">
                
                <div class="modal-actions">
                    <button type="submit" class="btn-gold" style="width: 100px;" id="btn-conferma">Conferma</button>
                    <button type="button" class="btn-outline-light" id="btn-annulla" style="color: var(--navy-blue); border-color: var(--navy-blue); width: 100px;">Annulla</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/mappa.js"></script>
</body>
</html>