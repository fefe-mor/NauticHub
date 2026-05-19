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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenota | NauticHub Genova</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/mappa-porto.css">
</head>
<body>

    <header class="navbar-premium">
        <div class="nav-content">
            <div class="logo">
                <img src="img/logo_nautic.png" alt="Logo" style="height: 80px; vertical-align: middle;">
                NauticHub <span class="gold-text">Genova</span>
            </div>
            <div class="user-info">
                <a href="dashboard.php" class="btn-outline-light" style="margin-right: 15px; border:none; text-decoration:underline;">← Area Personale</a>
                <span style="color: white; margin-right: 15px; font-weight: 600;">Benvenuto, <?php echo htmlspecialchars($nome_utente); ?></span>
                <a href="../server/logout.php" class="btn-outline-light">Disconnetti</a>
            </div>
        </div>
    </header>

    <main class="dashboard-main">
        <aside class="pannello-filtri">
            <h2 style="font-family: 'Playfair Display'; margin-bottom: 0.5rem;">Cerca Ormeggio</h2>
            
            <div id="notifica-sidebar" style="display: none;"></div>

            <p style="font-size: 0.85rem; color: #666; margin-bottom: 1.5rem;"></p>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="font-weight: 600;">Periodo di Sosta:</label>
                <div style="display: flex; gap: 10px; margin-top: 5px;">
                    <div style="flex: 1;">
                        <span style="font-size: 0.7rem; color: #666;">Arrivo</span>
                        <input type="date" id="filtro-dal" required style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <div style="flex: 1;">
                        <span style="font-size: 0.7rem; color: #666;">Partenza</span>
                        <input type="date" id="filtro-al" required style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label style="font-weight: 600;">La mia Flotta:</label>
                <div class="boat-selector" id="lista-barche" style="margin-top: 10px;">
                    <?php if(empty($mie_barche)): ?>
                        <p style="color: #e74c3c; font-size: 0.9rem;">Nessuna barca nel garage!</p>
                    <?php else: ?>
                        <?php foreach($mie_barche as $barca): 
                            $icona = '⛵'; 
                            if($barca['tipo'] == 'Gommone') $icona = '🚤';
                            if($barca['tipo'] == 'Barca a Motore') $icona = '🛥️';
                            if($barca['tipo'] == 'Yacht') $icona = '🛳️';
                        ?>
                        <label class="boat-card-radio" style="display: block; margin-bottom: 12px;">
                            <input type="radio" class="radio-barca" name="seleziona_barca" value="<?php echo $barca['id']; ?>" data-lunghezza="<?php echo $barca['lunghezza']; ?>" data-nome="<?php echo htmlspecialchars($barca['nome']); ?>">
                            <div class="boat-card-content">
                                <span class="boat-icon" style="font-size: 1.8rem;"><?php echo $icona; ?></span>
                                <div class="boat-info" style="display: flex; flex-direction: column; margin-left: 10px;">
                                    <strong style="font-size: 0.9rem;"><?php echo htmlspecialchars($barca['nome']); ?></strong>
                                    <span style="font-size: 0.75rem; color: #666;"><?php echo $barca['lunghezza']; ?>m</span>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group" style="margin-top: 2rem;">
                <label style="font-weight: 600;">Servizi:</label>
                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
                    <label class="toggle-pill"><input type="checkbox" id="filtro-corrente" class="checkbox-servizio"><span> 380V</span></label>
                    <label class="toggle-pill"><input type="checkbox" id="filtro-acqua" class="checkbox-servizio"><span> Acqua</span></label>
                    <label class="toggle-pill"><input type="checkbox" id="filtro-lavaggio" class="checkbox-servizio"><span> Lavaggio</span></label>
                </div>
            </div>
        </aside>

        <section class="mappa-section">
            <div class="mappa-header">
                <h2 style="font-family: 'Playfair Display';">Planimetria Darsena</h2>
                <p>Seleziona barca e date per visualizzare i moli compatibili.</p>
            </div>

            <div class="contenitore-mappa-immagine" id="vista-immagine-porto">
                <img src="img/mappa-porto.jpeg" alt="Mappa" class="immagine-sfondo-porto">
                
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
                <button id="btn-indietro" class="btn-outline-light" style="color:var(--navy-blue); border-color:var(--navy-blue); margin-bottom:1.5rem; cursor:pointer;">← Torna alla Mappa</button>
                
                <div id="mappa-container">
                    <?php foreach($struttura_moli as $lettera => $dati): ?>
                    <div class="pagina-molo" id="Molo-<?php echo $lettera; ?>" style="display: none;">
                        <div class="molo-header-page">
                            <h3>Molo <?php echo $lettera; ?></h3>
                        </div>
                        
                        <div class="area-mare-pontile" style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                            <div style="display: inline-flex; flex-direction: column; width: max-content; align-items: center;">
                                <div class="pontile-legno-lungo" style="width: 100%;"></div>
                                
                                <div class="linea-posti-barca" style="display:flex; flex-wrap:wrap; gap:12px; justify-content:center; margin-top: 15px;">
                                    <?php 
                                    for($i = 1; $i <= $dati['posti']; $i++): 
                                        $codice = $lettera . str_pad($i, 2, '0', STR_PAD_LEFT); 
                                        $isLibero = !in_array($codice, $posti_occupati);
                                    ?>
                                        <div class="posto-wrapper" style="position: relative; display: flex; flex-direction: column; align-items: center;">
                                            <div class="lampioncino"></div>
                                            <div class="posto-ui <?php echo statoPosto($codice, $posti_occupati, $dati['dimensione']); ?> <?php echo $isLibero ? 'posto-cliccabile' : ''; ?>" data-codice="<?php echo $codice; ?>">
                                                <span class="posto-numero"><?php echo $codice; ?></span>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <div id="prenotazioneModal" class="modal nascosto">
        <div class="modal-content">
            <h3>Conferma Ormeggio</h3>

            <div id="notifica-modal" style="display: none;"></div>

            <p style="margin-bottom: 20px;">Prenota il posto <strong id="posto-selezionato-id">--</strong>?</p>
            <form id="form-prenotazione">
                <input type="hidden" name="posto" id="input-posto-prenotato">
                <input type="hidden" name="barca_id" id="input-barca-id">
                
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px; color: var(--navy-blue);">Persone a bordo previste:</label>
                    <input type="number" id="input-numero-persone" required min="1" placeholder="Es. 4" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-gold" id="btn-conferma">Conferma</button>
                    <button type="button" id="btn-annulla" class="btn-outline-light" style="color:var(--navy-blue); border-color:var(--navy-blue);">Annulla</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/mappa.js"></script>
</body>
</html>