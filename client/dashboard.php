<?php
/* sessione */
session_start();

/* Includiamo il database */
require_once '../server/database.php';

/* controllo accesso */
if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'diportista') {
    header("Location: auth.php");
    exit;
}

$nome_utente = $_SESSION['nome_utente'];
$email_utente = $_SESSION['email_utente'];
$utente_id = $_SESSION['utente_id']; // Recuperato dal login

/* GESTIONE POST (Salva, Modifica, Elimina) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione'])) {
    
    // Salva / Modifica Barca
    if ($_POST['azione'] === 'salva_barca') {
        $nome = trim($_POST['nome']);
        $tipo = trim($_POST['tipo']);
        $lunghezza = floatval($_POST['lunghezza']);
        $id_barca = trim($_POST['id_barca']);

        if (empty($id_barca) || $id_barca === 'nuovo') {
            // INSERIMENTO [Slide 09]
            $stmt = $pdo->prepare("INSERT INTO barche (utente_id, nome, tipo, lunghezza) VALUES (?, ?, ?, ?)");
            $stmt->execute([$utente_id, $nome, $tipo, $lunghezza]);
        } else {
            // AGGIORNAMENTO [Slide 09]
            $stmt = $pdo->prepare("UPDATE barche SET nome = ?, tipo = ?, lunghezza = ? WHERE id = ? AND utente_id = ?");
            $stmt->execute([$nome, $tipo, $lunghezza, $id_barca, $utente_id]);
        }
    } 
    // Elimina Barca
    elseif ($_POST['azione'] === 'elimina_barca') {
        $id_barca = $_POST['id_barca'];
        $stmt = $pdo->prepare("DELETE FROM barche WHERE id = ? AND utente_id = ?");
        $stmt->execute([$id_barca, $utente_id]);
    } 
    // Annulla Prenotazione
    elseif ($_POST['azione'] === 'annulla_prenotazione') {
        // Presupponendo che 'codice_posto' sia in realtà l'ID della prenotazione nel database o il 'posto'
        $posto = $_POST['codice_posto'];
        $stmt = $pdo->prepare("DELETE FROM prenotazioni WHERE posto = ? AND utente_id = ?");
        $stmt->execute([$posto, $utente_id]);
    }
    
    header("Location: dashboard.php");
    exit;
}

/* LETTURA DATI DAL DATABASE (Sostituisce il vecchio JSON) */
// 1. Recupero Barche
$stmt = $pdo->prepare("SELECT * FROM barche WHERE utente_id = ?");
$stmt->execute([$utente_id]);
$mie_barche = $stmt->fetchAll();

// 2. Recupero Prenotazioni (con JOIN per prendere il nome della barca)
$stmt = $pdo->prepare("
    SELECT p.*, b.nome as nome_barca 
    FROM prenotazioni p 
    JOIN barche b ON p.barca_id = b.id 
    WHERE p.utente_id = ?
");
$stmt->execute([$utente_id]);
$mie_prenotazioni = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Personale | NauticHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style-landing.css?v=6.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        /* [Il tuo fantastico CSS rimane inalterato, lo copio qui identico] */
        body { background-color: #f0f2f5; color: var(--navy-blue); }
        .dash-header { background: var(--navy-blue); color: white; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; }
        .tab-menu { display: flex; justify-content: center; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .tab-btn { background: none; border: none; padding: 1.5rem 2rem; font-size: 1.1rem; font-weight: 600; color: #666; cursor: pointer; border-bottom: 3px solid transparent; transition: 0.3s;}
        .tab-btn.active { color: var(--navy-blue); border-bottom-color: var(--gold); }
        .tab-content { display: none; padding: 3rem 5%; max-width: 1200px; margin: 0 auto; animation: fadeIn 0.4s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .griglia-barche { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; }
        .boat-card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.05); text-align: center; border-top: 5px solid var(--navy-blue); margin-bottom: 1rem; display: flex; flex-direction: column; justify-content: space-between;}
        .add-boat-card { border: 2px dashed #ccc; border-top: none; background: transparent; cursor: pointer; transition: 0.3s; justify-content: center; align-items: center; min-height: 250px; }
        .add-boat-card:hover { border-color: var(--gold); background: rgba(212, 175, 55, 0.05); }
        .boat-actions { display: flex; justify-content: center; gap: 10px; margin-top: 1.5rem; }
        .btn-small { background: transparent; border: 2px solid var(--navy-blue); color: var(--navy-blue); padding: 0.4rem 1rem; border-radius: 20px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: 0.3s; }
        .btn-small:hover { background: var(--navy-blue); color: white; }
        .btn-small.btn-danger { border-color: #e74c3c; color: #e74c3c; }
        .btn-small.btn-danger:hover { background: #e74c3c; color: white; }

        .booking-card { background: white; border-radius: 12px; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-left: 5px solid #2ecc71; margin-bottom: 1rem;}
        .booking-details p { margin-bottom: 0.3rem; color: #555; }
        .booking-details strong { color: var(--navy-blue); }

        #mappa-vera { width: 100%; height: 550px; border-radius: 16px; border: 4px solid white; box-shadow: 0 10px 30px rgba(0,0,0,0.1); z-index: 1; }
        .custom-popup .leaflet-popup-content-wrapper { border-radius: 12px; }
        .custom-popup .leaflet-popup-content { text-align: center; font-family: 'Montserrat', sans-serif; margin: 15px; }
        .popup-title { color: var(--navy-blue); font-size: 1.1rem; font-weight: 700; margin-bottom: 5px; font-family: 'Playfair Display', serif; }
        .popup-desc { color: #666; font-size: 0.85rem; margin-bottom: 10px; }
        .btn-prenota-popup { display: inline-block; background: var(--gold); color: var(--navy-blue); padding: 8px 15px; border-radius: 20px; font-weight: 600; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .btn-prenota-popup:hover { background: #b5952f; color: white; }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 25, 47, 0.8); display: flex; justify-content: center; align-items: center; z-index: 2000; opacity: 0; pointer-events: none; transition: 0.3s; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-box { background: white; padding: 2.5rem; border-radius: 16px; width: 90%; max-width: 500px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); transform: translateY(-20px); transition: 0.3s; }
        .modal-overlay.active .modal-box { transform: translateY(0); }
        .form-group-modal { margin-bottom: 1.2rem; text-align: left; }
        .form-group-modal label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--navy-blue); font-size: 0.9rem; }
        .form-group-modal input, .form-group-modal select { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }

        @media (max-width: 768px) {
            .dash-header { flex-direction: column; gap: 15px; text-align: center; padding: 1.5rem 1rem; }
            .dash-header div { flex-direction: column; }
            .dash-header img { height: 60px !important; }
            .dash-header h2 { font-size: 1.5rem !important; }
            .tab-menu { flex-wrap: wrap; }
            .tab-btn { padding: 1rem; font-size: 0.9rem; flex: 1 1 100%; text-align: center; border-bottom: 1px solid #eee; }
            .tab-content { padding: 2rem 1rem; }
            .booking-card { flex-direction: column; text-align: center; gap: 15px; }
            #mappa-vera { height: 400px; }
        }
    </style>
</head>
<body>

    <header class="dash-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="img/logo_nautic.png" alt="Logo" style="height: 150px;">
            <h2 style="font-family: 'Playfair Display'; margin: 0;">Bentornato, <?php echo htmlspecialchars($nome_utente); ?></h2>
        </div>
        <a href="../server/logout.php" class="btn-outline-dark" style="color: white; border-color: white;">Disconnettiti</a>
    </header>

    <div class="tab-menu">
        <button class="tab-btn active" data-target="tab-prenota"> Esplora e Prenota</button>
        <button class="tab-btn" data-target="tab-barche"> Le Mie Barche</button>
        <button class="tab-btn" data-target="tab-prenotazioni"> Prenotazioni</button>
    </div>

    <div id="tab-prenota" class="tab-content active">
        <h2 style="font-family: 'Playfair Display'; font-size: 2rem; margin-bottom: 0.5rem;">Trova il tuo prossimo Ormeggio</h2>
        <p style="color: #666; margin-bottom: 2rem;">Trascina la mappa, usa lo zoom e clicca sui porti partner per esplorare la darsena.</p>
        <div id="mappa-vera"></div>
    </div>

    <div id="tab-barche" class="tab-content">
        <h2 style="font-family: 'Playfair Display'; font-size: 2rem; margin-bottom: 1rem;">Il tuo Garage Navale</h2>
        <div class="griglia-barche">
            <?php foreach($mie_barche as $barca): 
                // Generiamo l'icona in base al tipo salvato nel DB
                $icona = '⛵'; 
                if($barca['tipo'] == 'Gommone') $icona = '🚤';
                if($barca['tipo'] == 'Barca a Motore') $icona = '🛥️';
                if($barca['tipo'] == 'Yacht') $icona = '🛳️';
            ?>
            <div class="boat-card">
                <div>
                    <div style="font-size: 4rem; margin-bottom: 1rem;"><?php echo $icona; ?></div>
                    <h3><?php echo htmlspecialchars($barca['nome']); ?></h3>
                    <p style="color: #666;"><?php echo htmlspecialchars($barca['tipo']); ?> | L: <?php echo htmlspecialchars($barca['lunghezza']); ?>m</p>
                </div>
                <div class="boat-actions">
                    <button class="btn-small btn-modifica" data-id="<?php echo $barca['id']; ?>" data-nome="<?php echo htmlspecialchars($barca['nome']); ?>" data-tipo="<?php echo htmlspecialchars($barca['tipo']); ?>" data-lunghezza="<?php echo $barca['lunghezza']; ?>">Modifica</button>
                    
                    <form method="POST" action="dashboard.php" style="display:inline;" onsubmit="return window.confirm('Sicuro di voler eliminare questa barca?');">
                        <input type="hidden" name="azione" value="elimina_barca">
                        <input type="hidden" name="id_barca" value="<?php echo $barca['id']; ?>">
                        <button type="submit" class="btn-small btn-danger">Elimina</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="boat-card add-boat-card" id="btn-aggiungi-barca">
                <div style="font-size: 3.5rem; color: #ccc; margin-bottom: 0.5rem;">➕</div>
                <h3 style="color: #999;">Aggiungi Imbarcazione</h3>
            </div>
        </div>
    </div>

    <div id="tab-prenotazioni" class="tab-content">
        <h2 style="font-family: 'Playfair Display'; font-size: 2rem; margin-bottom: 1rem;">Le tue Prenotazioni</h2>
        <?php if(empty($mie_prenotazioni)): ?>
            <p style="color: #666;">Non hai ancora effettuato nessuna prenotazione. Vai sulla mappa e scegli un porto!</p>
        <?php else: ?>
            <?php foreach($mie_prenotazioni as $pren): ?>
            <div class="booking-card">
                <div class="booking-details">
                    <h3 style="margin-bottom: 0.5rem; color: var(--navy-blue); font-family: 'Playfair Display', serif; font-size: 1.6rem;">
                        ⛵ <?php echo htmlspecialchars($pren['nome_barca']); ?>
                    </h3>
                    <p><strong>Porto:</strong> Smart Marina Bari</p>
                    <p><strong>Posizione:</strong> Molo <?php echo htmlspecialchars($pren['posto']); ?></p>
                    <p><strong>Dal:</strong> <?php echo htmlspecialchars($pren['data_inizio']); ?> <strong>Al:</strong> <?php echo htmlspecialchars($pren['data_fine']); ?></p>
                </div>
                
                <form method="POST" action="dashboard.php" style="display:inline;" onsubmit="return window.confirm('Vuoi annullare la prenotazione del posto <?php echo $pren['posto']; ?>?');">
                    <input type="hidden" name="azione" value="annulla_prenotazione">
                    <input type="hidden" name="codice_posto" value="<?php echo $pren['posto']; ?>">
                    <button type="submit" style="border: 2px solid #e74c3c; color: #e74c3c; background: none; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#e74c3c'; this.style.color='white';" onmouseout="this.style.background='none'; this.style.color='#e74c3c';">Annulla</button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="modal-overlay" id="boatModal">
        <div class="modal-box">
            <h2 id="modal-title" style="font-family: 'Playfair Display'; margin-bottom: 1.5rem; color: var(--navy-blue);">Aggiungi Barca</h2>
            
            <form method="POST" action="dashboard.php">
                <input type="hidden" name="azione" value="salva_barca">
                <input type="hidden" id="boat-id" name="id_barca" value="nuovo">
                
                <div class="form-group-modal">
                    <label>Nome dell'imbarcazione</label>
                    <input type="text" id="boat-name" name="nome" required placeholder="Es. Mare Mosso">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group-modal">
                        <label>Tipologia</label>
                        <select id="boat-type" name="tipo" required>
                            <option value="">Seleziona...</option>
                            <option value="Barca a Vela">Barca a Vela ⛵</option>
                            <option value="Barca a Motore">Barca a Motore 🛥️</option>
                            <option value="Gommone">Gommone 🚤</option>
                            <option value="Yacht">Yacht 🛳️</option>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label>Lunghezza (metri)</label>
                        <input type="number" id="boat-length" name="lunghezza" step="0.1" required placeholder="Es. 12.5">
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" class="btn-gold" style="width: 100%; border: none; padding: 1rem; border-radius: 8px; font-weight: 700; cursor: pointer;">Salva Imbarcazione</button>
                    <button type="button" class="btn-outline-dark" id="btn-chiudi-modal" style="width: 100%; padding: 1rem; border-radius: 8px; text-align: center;">Annulla</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>