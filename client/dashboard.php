<?php

session_start();

require_once '../server/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'diportista') {
    header("Location: auth.php");
    exit;
}

$nome_utente = $_SESSION['nome_utente'];
$email_utente = $_SESSION['email_utente'];
$utente_id = $_SESSION['utente_id']; 


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione'])) {
    
    // AZIONE: Aggiungi o Modifica Barca
    if ($_POST['azione'] === 'salva_barca') {
        $nome = trim($_POST['nome']);
        $tipo = trim($_POST['tipo']);
        $lunghezza = floatval($_POST['lunghezza']);
        $larghezza = floatval($_POST['larghezza']);
        $pescaggio = floatval($_POST['pescaggio']);
        $altezza = !empty($_POST['altezza']) ? floatval($_POST['altezza']) : null;
        $ha_immatricolazione = $_POST['ha_immatricolazione'] ?? 'no';
        $numero_immatricolazione = ($ha_immatricolazione === 'si') ? strtoupper(trim($_POST['numero_immatricolazione'])) : null;
        $id_barca = trim($_POST['id_barca']);

        // Controllo logico: una barca deve essere almeno di 5 metri per il porto
        if ($lunghezza < 5) {
            $_SESSION['toast_msg'] = "La lunghezza dell'imbarcazione deve essere di almeno 5 metri.";
            $_SESSION['toast_type'] = "errore";
            header("Location: dashboard.php");
            exit;
        }

        if (empty($id_barca) || $id_barca === 'nuovo') {
            // Nuova Barca
            $stmt = $pdo->prepare("INSERT INTO barche (utente_id, nome, tipo, lunghezza, larghezza, pescaggio, altezza, numero_immatricolazione) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$utente_id, $nome, $tipo, $lunghezza, $larghezza, $pescaggio, $altezza, $numero_immatricolazione]);
            $_SESSION['toast_msg'] = "Imbarcazione registrata con successo!";
            $_SESSION['toast_type'] = "successo";
        } else {
            // Aggiornamento Barca Esistente
            $stmt = $pdo->prepare("UPDATE barche SET nome = ?, tipo = ?, lunghezza = ?, larghezza = ?, pescaggio = ?, altezza = ?, numero_immatricolazione = ? WHERE id = ? AND utente_id = ?");
            $stmt->execute([$nome, $tipo, $lunghezza, $larghezza, $pescaggio, $altezza, $numero_immatricolazione, $id_barca, $utente_id]);
            $_SESSION['toast_msg'] = "Dati imbarcazione aggiornati!";
            $_SESSION['toast_type'] = "successo";
        }
    } 
    // AZIONE: Elimina Barca
    elseif ($_POST['azione'] === 'elimina_barca') {
        $id_barca = $_POST['id_barca'];
        $stmt = $pdo->prepare("DELETE FROM barche WHERE id = ? AND utente_id = ?");
        $stmt->execute([$id_barca, $utente_id]);
        $_SESSION['toast_msg'] = "Imbarcazione eliminata dal garage.";
        $_SESSION['toast_type'] = "successo";
    } 
    // AZIONE: Annulla Prenotazione
    elseif ($_POST['azione'] === 'annulla_prenotazione') {
        $posto = $_POST['codice_posto'];
        $stmt = $pdo->prepare("DELETE FROM prenotazioni WHERE posto = ? AND utente_id = ?");
        $stmt->execute([$posto, $utente_id]);
        $_SESSION['toast_msg'] = "Prenotazione annullata definitivamente.";
        $_SESSION['toast_type'] = "successo";
    }
    
    header("Location: dashboard.php");
    exit;
}

$messaggio_toast = '';
$tipo_toast = '';
if (isset($_SESSION['toast_msg'])) {
    $messaggio_toast = $_SESSION['toast_msg'];
    $tipo_toast = $_SESSION['toast_type'];
    unset($_SESSION['toast_msg']);
    unset($_SESSION['toast_type']);
}

$stmt = $pdo->prepare("SELECT * FROM barche WHERE utente_id = ?");
$stmt->execute([$utente_id]);
$mie_barche = $stmt->fetchAll();

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
    <title>Dashboard | NauticHub</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    
    <link rel="stylesheet" href="css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/footer.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-theme">

    <?php if(!empty($messaggio_toast)): ?>
    <div id="toast-sistema" class="toast-premium <?php echo $tipo_toast; ?>">
        <i class="fa-solid <?php echo $tipo_toast === 'successo' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
        <span><?php echo htmlspecialchars($messaggio_toast); ?></span>
    </div>
    <?php endif; ?>

    <header class="dash-header">
        <div class="nav-content container">
            <div class="logo-area">
                <a href="index.php" class="text-logo">
                    Nautic<span class="gradient-logo-accent">Hub</span>
                </a>
            </div>
            <div class="user-info">
                <span class="welcome-text"><i class="fa-regular fa-user"></i> Bentornato, <strong><?php echo htmlspecialchars($nome_utente); ?></strong></span>
                <a href="../server/logout.php" class="btn-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span class="logout-text">Disconnetti</span></a>
            </div>
        </div>
    </header>

    <nav class="tab-menu-container">
        <div class="tab-menu">
            <button class="tab-btn active" data-target="tab-prenota"><i class="fa-solid fa-map-location-dot"></i> Esplora e Prenota</button>
            <button class="tab-btn" data-target="tab-barche"><i class="fa-solid fa-anchor"></i> Il Mio Garage</button>
            <button class="tab-btn" data-target="tab-prenotazioni"><i class="fa-solid fa-book-bookmark"></i> Prenotazioni</button>
        </div>
    </nav>

    <main class="dash-main-content">
        
        <section id="tab-prenota" class="tab-content active">
            <div class="tab-header">
                <h2>Trova il tuo prossimo Ormeggio</h2>
                <p>Trascina la mappa, usa lo zoom e clicca sui porti partner per esplorare la darsena e prenotare.</p>
            </div>
            <div class="map-container-glass">
                <div id="mappa-vera"></div>
            </div>
        </section>

        <section id="tab-barche" class="tab-content">
            <div class="tab-header">
                <h2>Il tuo Garage Navale</h2>
                <p>Gestisci la tua flotta per visualizzare solo i posti barca compatibili.</p>
            </div>
            <div class="griglia-barche">
                <?php foreach($mie_barche as $barca): 
                    // Gestione dinamica immagine barca
                    $img_barca = 'img/vela.jpeg'; 
                    if($barca['tipo'] == 'Gommone') $img_barca = 'img/gommone.jpeg';
                    if($barca['tipo'] == 'Barca a Motore') $img_barca = 'img/motore.jpeg';
                    if($barca['tipo'] == 'Yacht') $img_barca = 'img/yacht.jpeg'; 
                    if($barca['tipo'] == 'Catamarano') $img_barca = 'img/catamarano.jpeg';
                ?>
                <div class="boat-card-premium">
                    <div class="boat-card-image" style="background-image: url('<?php echo $img_barca; ?>');">
                        <div class="boat-card-overlay"></div>
                        <h3 class="boat-name-display"><?php echo htmlspecialchars($barca['nome']); ?></h3>
                    </div>
                    <div class="boat-card-details">
                        <div class="boat-specs-grid">
                            <div class="spec-block"><span class="spec-label">Tipo</span><span class="spec-value gold-text"><?php echo htmlspecialchars($barca['tipo']); ?></span></div>
                            <div class="spec-block"><span class="spec-label">Lunghezza</span><span class="spec-value"><i class="fa-solid fa-ruler-horizontal"></i> <?php echo htmlspecialchars($barca['lunghezza']); ?>m</span></div>
                            <div class="spec-block"><span class="spec-label">Larghezza</span><span class="spec-value"><i class="fa-solid fa-expand"></i> <?php echo htmlspecialchars($barca['larghezza']); ?>m</span></div>
                            <div class="spec-block"><span class="spec-label">Pescaggio</span><span class="spec-value"><i class="fa-solid fa-arrow-down"></i> <?php echo htmlspecialchars($barca['pescaggio']); ?>m</span></div>
                            
                            <?php if(!empty($barca['numero_immatricolazione'])): ?>
                            <div class="spec-block blocco-immatricolazione-evidenza">
                                <span class="spec-label testo-ciano">Immatricolazione</span>
                                <span class="spec-value testo-spaziato"><i class="fa-solid fa-hashtag"></i> <?php echo htmlspecialchars($barca['numero_immatricolazione']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="boat-actions-panel">
                            <button class="btn-panel btn-modifica" 
                                data-id="<?php echo $barca['id']; ?>" 
                                data-nome="<?php echo htmlspecialchars($barca['nome']); ?>" 
                                data-tipo="<?php echo htmlspecialchars($barca['tipo']); ?>" 
                                data-lunghezza="<?php echo $barca['lunghezza']; ?>"
                                data-larghezza="<?php echo $barca['larghezza']; ?>"
                                data-pescaggio="<?php echo $barca['pescaggio']; ?>"
                                data-altezza="<?php echo $barca['altezza']; ?>"
                                data-immatricolazione="<?php echo htmlspecialchars($barca['numero_immatricolazione']); ?>">
                                <i class="fa-solid fa-pen"></i> Modifica
                            </button>
                            
                            <form method="POST" action="dashboard.php" class="form-azione-barca">
                                <input type="hidden" name="azione" value="elimina_barca">
                                <input type="hidden" name="id_barca" value="<?php echo $barca['id']; ?>">
                                <button type="button" class="btn-panel btn-delete btn-elimina-custom" data-messaggio="Sei sicuro di voler eliminare <?php echo htmlspecialchars($barca['nome']); ?> dal tuo garage navale?"><i class="fa-solid fa-trash-can"></i> Rimuovi</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="boat-card-premium add-boat-card-new" id="btn-aggiungi-barca">
                    <div class="add-container-inner">
                        <div class="add-icon-pulsing"><i class="fa-solid fa-plus"></i></div>
                        <h3>Aggiungi Imbarcazione</h3>
                        <p>Inserisci un nuovo scafo nella flotta</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="tab-prenotazioni" class="tab-content">
            <div class="tab-header">
                <h2>Le mie Prenotazioni</h2>
                <p>Tieni traccia dei tuoi ormeggi futuri e passati.</p>
            </div>
            
            <?php if(empty($mie_prenotazioni)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-anchor-circle-exclamation"></i>
                    <p>Non hai ancora effettuato nessuna prenotazione.</p>
                </div>
            <?php else: ?>
                <div class="booking-list">
                    <?php foreach($mie_prenotazioni as $pren): ?>
                    <div class="booking-card">
                        <div class="booking-details">
                            <h3><i class="fa-solid fa-ship gold-text margine-superiore-piccolo"></i> <?php echo htmlspecialchars($pren['nome_barca']); ?></h3>
                            <div class="booking-info-grid">
                                <div><i class="fa-solid fa-location-dot"></i> <strong>Porto:</strong> Smart Marina Genova</div>
                                <div><i class="fa-solid fa-dharmachakra"></i> <strong>Molo:</strong> <?php echo htmlspecialchars($pren['posto']); ?></div>
                                <div><i class="fa-regular fa-calendar-check"></i> <strong>Dal:</strong> <?php echo htmlspecialchars($pren['data_inizio']); ?></div>
                                <div><i class="fa-regular fa-calendar-xmark"></i> <strong>Al:</strong> <?php echo htmlspecialchars($pren['data_fine']); ?></div>
                                <div class="people-count"><i class="fa-solid fa-users"></i> Persone a bordo: <?php echo htmlspecialchars($pren['numero_persone']); ?></div>
                            </div>
                        </div>
                        
                        <div class="booking-actions">
                            <button type="button" class="btn-azione btn-apri-modifica-prenotazione"
                                data-id="<?php echo $pren['id']; ?>"
                                data-posto="<?php echo htmlspecialchars($pren['posto']); ?>"
                                data-barca="<?php echo htmlspecialchars($pren['nome_barca']); ?>"
                                data-dal="<?php echo $pren['data_inizio']; ?>"
                                data-al="<?php echo $pren['data_fine']; ?>"
                                data-persone="<?php echo $pren['numero_persone']; ?>">
                                <i class="fa-solid fa-pen-to-square"></i> Gestisci
                            </button>

                            <form method="POST" action="dashboard.php" class="form-azione-base">
                                <input type="hidden" name="azione" value="annulla_prenotazione">
                                <input type="hidden" name="codice_posto" value="<?php echo $pren['posto']; ?>">
                                <button type="button" class="btn-azione btn-danger btn-elimina-custom" data-messaggio="Vuoi davvero annullare la prenotazione del posto <?php echo $pren['posto']; ?>? Questa azione è irreversibile."><i class="fa-solid fa-ban"></i> Annulla</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <div class="modal-overlay" id="boatModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2 id="modal-title">Aggiungi Barca</h2>
                <button type="button" class="btn-close-icon" id="btn-chiudi-modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form method="POST" action="dashboard.php" class="modal-form">
                <input type="hidden" name="azione" value="salva_barca">
                <input type="hidden" id="boat-id" name="id_barca" value="nuovo">
                
                <div class="form-group-modal full-width">
                    <label>Nome dell'imbarcazione</label>
                    <div class="input-glass">
                        <i class="fa-solid fa-signature"></i>
                        <input type="text" id="boat-name" name="nome" required placeholder="Es. Mare Mosso">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group-modal">
                        <label>Tipologia</label>
                        <div class="input-glass select-glass">
                            <i class="fa-solid fa-ship"></i>
                            <select id="boat-type" name="tipo" required>
                                <option value="">Seleziona...</option>
                                <option value="Barca a Vela">Barca a Vela</option>
                                <option value="Catamarano">Catamarano</option>
                                <option value="Barca a Motore">Barca a Motore</option>
                                <option value="Gommone">Gommone</option>
                                <option value="Yacht">Yacht</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group-modal">
                        <label>Lunghezza (m)</label>
                        <div class="input-glass">
                            <i class="fa-solid fa-arrows-left-right"></i>
                            <input type="number" id="boat-length" name="lunghezza" step="0.01" required min="5" placeholder="Es. 12.5">
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group-modal">
                        <label>Larghezza (m)</label>
                        <div class="input-glass">
                            <i class="fa-solid fa-expand"></i>
                            <input type="number" id="boat-width" name="larghezza" step="0.01" required min="0.5">
                        </div>
                    </div>
                    <div class="form-group-modal">
                        <label>Pescaggio (m)</label>
                        <div class="input-glass">
                            <i class="fa-solid fa-arrow-down-up-across-line"></i>
                            <input type="number" id="boat-draft" name="pescaggio" step="0.01" required min="0">
                        </div>
                    </div>
                </div>

                <div class="form-group-modal full-width">
                    <label>Altezza (m) - Facoltativa</label>
                    <div class="input-glass">
                        <i class="fa-solid fa-up-long"></i>
                        <input type="number" id="boat-height" name="altezza" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-group-modal full-width box-immatricolazione-container">
                    <label class="toggle-label">Hai un numero di immatricolazione?</label>
                    <div class="radio-group">
                        <label class="radio-glass"><input type="radio" name="ha_immatricolazione" class="radio-immatricolazione" value="no" checked> <span>No</span></label>
                        <label class="radio-glass"><input type="radio" name="ha_immatricolazione" class="radio-immatricolazione" value="si"> <span>Sì</span></label>
                    </div>
                    <div id="box-immatricolazione" class="box-immatricolazione-nascosto">
                        <label>Numero Immatricolazione</label>
                        <div class="input-glass">
                            <i class="fa-solid fa-hashtag"></i>
                            <input type="text" id="boat-immatricolazione" name="numero_immatricolazione" class="testo-maiuscolo">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-gold-modal"><i class="fa-solid fa-floppy-disk"></i> Salva Imbarcazione</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editBookingModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Gestisci Sosta</h2>
                <button type="button" class="btn-close-icon" id="btn-chiudi-edit-booking"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div id="notifica-edit-modal" class="sys-msg nascosto"></div>
            
            <form id="form-edit-booking" class="modal-form">
                <input type="hidden" id="edit-booking-id">
                
                <div class="booking-summary-glass">
                    <i class="fa-solid fa-map-pin gold-text"></i> Molo: <strong id="lbl-edit-posto" class="testo-ciano">--</strong><br>
                    <i class="fa-solid fa-ship gold-text margine-superiore-piccolo"></i> Barca: <strong id="lbl-edit-barca">--</strong>
                </div>

                <div class="form-grid">
                    <div class="form-group-modal">
                        <label>Data Arrivo</label>
                        <div class="input-glass">
                            <i class="fa-regular fa-calendar-check"></i>
                            <input type="date" id="edit-booking-dal" required>
                        </div>
                    </div>
                    <div class="form-group-modal">
                        <label>Data Partenza</label>
                        <div class="input-glass">
                            <i class="fa-regular fa-calendar-xmark"></i>
                            <input type="date" id="edit-booking-al" required>
                        </div>
                    </div>
                </div>

                <div class="form-group-modal full-width">
                    <label>Persone a bordo previste</label>
                    <div class="input-glass">
                        <i class="fa-solid fa-users"></i>
                        <input type="number" id="edit-booking-persone" placeholder="Comunicabile all'arrivo">
                    </div>
                </div>

                <button type="submit" id="btn-salva-edit" class="btn-gold-modal"><i class="fa-solid fa-cloud-arrow-up"></i> Aggiorna Prenotazione</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-conferma">
        <div class="modal-box warning-box">
            <div class="warning-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <h3>Attenzione</h3>
            <p id="testo-conferma"></p>
            <div class="modal-actions">
                <button type="button" id="btn-conferma-no" class="btn-outline-light-modal">Annulla</button>
                <button type="button" id="btn-conferma-si" class="btn-danger-modal">Conferma</button>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>