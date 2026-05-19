<?php

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

/* GESTIONE POST (Salva, Elimina Barca, Annulla Prenotazione) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione'])) {
    
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

        if (empty($id_barca) || $id_barca === 'nuovo') {
            $stmt = $pdo->prepare("INSERT INTO barche (utente_id, nome, tipo, lunghezza, larghezza, pescaggio, altezza, numero_immatricolazione) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$utente_id, $nome, $tipo, $lunghezza, $larghezza, $pescaggio, $altezza, $numero_immatricolazione]);
            $_SESSION['toast_msg'] = "Imbarcazione registrata con successo!";
            $_SESSION['toast_type'] = "successo";
        } else {
            $stmt = $pdo->prepare("UPDATE barche SET nome = ?, tipo = ?, lunghezza = ?, larghezza = ?, pescaggio = ?, altezza = ?, numero_immatricolazione = ? WHERE id = ? AND utente_id = ?");
            $stmt->execute([$nome, $tipo, $lunghezza, $larghezza, $pescaggio, $altezza, $numero_immatricolazione, $id_barca, $utente_id]);
            $_SESSION['toast_msg'] = "Dati imbarcazione aggiornati!";
            $_SESSION['toast_type'] = "successo";
        }
    } 
    elseif ($_POST['azione'] === 'elimina_barca') {
        $id_barca = $_POST['id_barca'];
        $stmt = $pdo->prepare("DELETE FROM barche WHERE id = ? AND utente_id = ?");
        $stmt->execute([$id_barca, $utente_id]);
        $_SESSION['toast_msg'] = "Imbarcazione eliminata dal garage.";
        $_SESSION['toast_type'] = "successo";
    } 
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

/* LETTURA DATI DAL DATABASE */
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
    <title>Area Personale | NauticHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/mappa-porto.css?v=6.5"> 
    <link rel="stylesheet" href="css/dashboard.css?v=6.5">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        .toast-premium {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #ffffff;
            color: #0a192f;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 10000;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            transform: translateX(120%);
            animation: slideInToast 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            border-left: 6px solid;
        }
        .toast-premium.successo { border-left-color: #2ecc71; }
        .toast-premium.errore { border-left-color: #e74c3c; }

        .toast-premium.nascondi {
            animation: slideOutToast 0.5s ease forwards;
        }

        @keyframes slideInToast {
            to { transform: translateX(0); }
        }
        @keyframes slideOutToast {
            to { transform: translateX(120%); opacity: 0; }
        }
    </style>
</head>
<body>

    <?php if(!empty($messaggio_toast)): ?>
    <div id="toast-sistema" class="toast-premium <?php echo $tipo_toast; ?>">
        <span style="font-size: 1.5rem;"><?php echo $tipo_toast === 'successo' ? '' : ''; ?></span>
        <span><?php echo htmlspecialchars($messaggio_toast); ?></span>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast-sistema');
            if(toast) { 
                toast.classList.add('nascondi');
                setTimeout(() => toast.remove(), 500); 
            }
        }, 4000);
    </script>
    <?php endif; ?>

    <header class="navbar-premium" style="position: sticky; top: 0; width: 100%; z-index: 1000;">
        <div class="nav-content">
            <div class="logo">
                <img src="img/logo_nautic.png" alt="Logo" style="height: 80px; vertical-align: middle;">
                NauticHub <span class="gold-text">Genova</span>
            </div>
            <div class="user-info">
                <span style="color: white; margin-right: 15px; font-weight: 600;">Bentornato, <?php echo htmlspecialchars($nome_utente); ?></span>
                <a href="../server/logout.php" class="btn-outline-light">Disconnetti</a>
            </div>
        </div>
    </header>

    <div class="tab-menu" style="margin-top: 0;">
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
                $icona = '⛵'; 
                if($barca['tipo'] == 'Gommone') $icona = '🚤';
                if($barca['tipo'] == 'Barca a Motore') $icona = '🛥️';
                if($barca['tipo'] == 'Yacht') $icona = '🛳️';
                if($barca['tipo'] == 'Catamarano') $icona = '⛵';
            ?>
            <div class="boat-card">
                <div>
                    <div style="font-size: 4rem; margin-bottom: 1rem;"><?php echo $icona; ?></div>
                    <h3><?php echo htmlspecialchars($barca['nome']); ?></h3>
                    <p style="color: #666;"><?php echo htmlspecialchars($barca['tipo']); ?> | L: <?php echo htmlspecialchars($barca['lunghezza']); ?>m</p>
                </div>
                <div class="boat-actions">
                    <button class="btn-small btn-modifica" 
                        data-id="<?php echo $barca['id']; ?>" 
                        data-nome="<?php echo htmlspecialchars($barca['nome']); ?>" 
                        data-tipo="<?php echo htmlspecialchars($barca['tipo']); ?>" 
                        data-lunghezza="<?php echo $barca['lunghezza']; ?>"
                        data-larghezza="<?php echo $barca['larghezza']; ?>"
                        data-pescaggio="<?php echo $barca['pescaggio']; ?>"
                        data-altezza="<?php echo $barca['altezza']; ?>"
                        data-immatricolazione="<?php echo htmlspecialchars($barca['numero_immatricolazione']); ?>">Modifica</button>
                    
                    <form method="POST" action="dashboard.php" style="display:inline;">
                        <input type="hidden" name="azione" value="elimina_barca">
                        <input type="hidden" name="id_barca" value="<?php echo $barca['id']; ?>">
                        <button type="button" class="btn-small btn-danger btn-elimina-custom" data-messaggio="Sei sicuro di voler eliminare <?php echo htmlspecialchars($barca['nome']); ?> dal tuo garage navale?">Elimina</button>
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
            <p style="color: #666;">Non hai ancora effettuato nessuna prenotazione.</p>
        <?php else: ?>
            <?php foreach($mie_prenotazioni as $pren): ?>
            <div class="booking-card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <div class="booking-details">
                    <h3 style="margin-bottom: 0.5rem; color: #0a192f; font-family: 'Playfair Display', serif; font-size: 1.6rem;">
                        ⛵ <?php echo htmlspecialchars($pren['nome_barca']); ?>
                    </h3>
                    <p style="margin-bottom: 4px;"><strong>Porto:</strong> Smart Marina Genova</p>
                    <p style="margin-bottom: 4px;"><strong>Posizione:</strong> Molo <?php echo htmlspecialchars($pren['posto']); ?></p>
                    <p style="margin-bottom: 4px;"><strong>Dal:</strong> <?php echo htmlspecialchars($pren['data_inizio']); ?> <strong>Al:</strong> <?php echo htmlspecialchars($pren['data_fine']); ?></p>
                    <p style="margin-top: 8px; color: #2ecc71; font-weight: 600;">👥 Persone a bordo: <?php echo htmlspecialchars($pren['numero_persone']); ?></p>
                </div>
                
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button type="button" class="btn-apri-modifica-prenotazione"
                        data-id="<?php echo $pren['id']; ?>"
                        data-posto="<?php echo htmlspecialchars($pren['posto']); ?>"
                        data-barca="<?php echo htmlspecialchars($pren['nome_barca']); ?>"
                        data-dal="<?php echo $pren['data_inizio']; ?>"
                        data-al="<?php echo $pren['data_fine']; ?>"
                        data-persone="<?php echo $pren['numero_persone']; ?>"
                        style="border: 2px solid #d4af37; color: #d4af37; background: none; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; cursor: pointer; transition: 0.3s;"
                        onmouseover="this.style.background='#d4af37'; this.style.color='#0a192f';" 
                        onmouseout="this.style.background='none'; this.style.color='#d4af37';">
                        Modifica
                    </button>

                    <form method="POST" action="dashboard.php" style="display:inline;">
                        <input type="hidden" name="azione" value="annulla_prenotazione">
                        <input type="hidden" name="codice_posto" value="<?php echo $pren['posto']; ?>">
                        <button type="button" class="btn-elimina-custom" data-messaggio="Vuoi davvero annullare la prenotazione del posto <?php echo $pren['posto']; ?>? Questa azione è irreversibile." style="border: 2px solid #e74c3c; color: #e74c3c; background: none; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#e74c3c'; this.style.color='white';" onmouseout="this.style.background='none'; this.style.color='#e74c3c';">Annulla</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="modal-overlay" id="boatModal">
        <div class="modal-box">
            <h2 id="modal-title" style="font-family: 'Playfair Display'; margin-bottom: 1.5rem; color: #0a192f;">Aggiungi Barca</h2>
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
                            <option value="Catamarano">Catamarano ⛵</option>
                            <option value="Barca a Motore">Barca a Motore 🛥️</option>
                            <option value="Gommone">Gommone 🚤</option>
                            <option value="Yacht">Yacht 🛳️</option>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label>Lunghezza (m)</label>
                        <input type="number" id="boat-length" name="lunghezza" step="0.01" required min="1">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group-modal">
                        <label>Larghezza (m)</label>
                        <input type="number" id="boat-width" name="larghezza" step="0.01" required min="0.5">
                    </div>
                    <div class="form-group-modal">
                        <label>Pescaggio (m)</label>
                        <input type="number" id="boat-draft" name="pescaggio" step="0.01" required min="0">
                    </div>
                </div>

                <div class="form-group-modal">
                    <label>Altezza (m) - Facoltativa</label>
                    <input type="number" id="boat-height" name="altezza" step="0.01" min="0">
                </div>

                <div class="form-group-modal" style="margin-top: 10px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd;">
                    <label style="margin-bottom: 10px;">Hai un numero di immatricolazione?</label>
                    <div style="display: flex; gap: 20px; margin-bottom: 10px;">
                        <label><input type="radio" name="ha_immatricolazione" value="no" checked onchange="toggleImmatricolazione(this.value)"> No</label>
                        <label><input type="radio" name="ha_immatricolazione" value="si" onchange="toggleImmatricolazione(this.value)"> Sì</label>
                    </div>
                    <div id="box-immatricolazione" style="display: none; margin-top: 15px;">
                        <label>Numero Immatricolazione</label>
                        <input type="text" id="boat-immatricolazione" name="numero_immatricolazione" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" class="btn-gold" style="width: 100%; border: none; padding: 1rem; border-radius: 8px; font-weight: 700; cursor: pointer; background-color: #d4af37; color: #0a192f;">Salva Imbarcazione</button>
                    <button type="button" class="btn-outline-dark" id="btn-chiudi-modal" style="width: 100%; padding: 1rem; border-radius: 8px; cursor: pointer;">Annulla</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editBookingModal">
        <div class="modal-box">
            <h2 style="font-family: 'Playfair Display'; margin-bottom: 1.5rem; color: #0a192f;">Modifica Sosta Ormeggio</h2>
            
            <div id="notifica-edit-modal" style="display: none; margin-bottom: 15px; padding: 12px; border-radius: 8px; font-weight: 600; text-align: center;"></div>
            
            <form id="form-edit-booking">
                <input type="hidden" id="edit-booking-id">
                
                <p style="margin-bottom: 1.5rem; background: #fdfbf7; padding: 10px; border-left: 4px solid #d4af37; border-radius: 4px;">
                    Sosta per il molo: <strong id="lbl-edit-posto">--</strong> con imbarcazione: <strong id="lbl-edit-barca">--</strong>
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group-modal">
                        <label>Data Arrivo</label>
                        <input type="date" id="edit-booking-dal" required>
                    </div>
                    <div class="form-group-modal">
                        <label>Data Partenza</label>
                        <input type="date" id="edit-booking-al" required>
                    </div>
                </div>

                <div class="form-group-modal">
                    <label>Persone a bordo previste</label>
                    <input type="number" id="edit-booking-persone" required min="1" placeholder="Es. 4">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" id="btn-salva-edit" class="btn-gold" style="width: 100%; border: none; padding: 1rem; border-radius: 8px; font-weight: 700; cursor: pointer; background-color: #d4af37; color: #0a192f;">Salva Modifiche</button>
                    <button type="button" id="btn-chiudi-edit-booking" class="btn-outline-dark" style="width: 100%; padding: 1rem; border-radius: 8px; cursor: pointer;">Annulla</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-conferma">
        <div class="modal-box" style="max-width: 400px; text-align: center; padding: 2rem;">
            <h3 style="margin-bottom: 15px; color: #e74c3c; font-family: 'Playfair Display', serif; font-size: 1.8rem;">Attenzione</h3>
            <p id="testo-conferma" style="margin-bottom: 25px; color: #333; line-height: 1.5;"></p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" id="btn-conferma-si" style="background: #e74c3c; color: white; border-radius: 8px; border: none; padding: 0.8rem 1.5rem; cursor: pointer; font-weight: bold; flex: 1;">Sì, Conferma</button>
                <button type="button" id="btn-conferma-no" style="border-radius: 8px; border: 1px solid #ccc; padding: 0.8rem 1.5rem; background: transparent; cursor: pointer; font-weight: bold; flex: 1;">No, Indietro</button>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script>
    function toggleImmatricolazione(valore) {
        const box = document.getElementById('box-immatricolazione');
        const input = document.getElementById('boat-immatricolazione');
        
        if (valore === 'si') {
            box.style.display = 'block';
            input.required = true; 
        } else {
            box.style.display = 'none';
            input.required = false; 
            input.value = ''; 
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        
        // --- 1. MOSTRA TOAST PREMIUM (DA JS) DOPO IL RELOAD AJAX ---
        const savedSuccessToast = sessionStorage.getItem('ajax_toast_success');
        if (savedSuccessToast) {
            sessionStorage.removeItem('ajax_toast_success');
            const toast = document.createElement('div');
            toast.className = 'toast-premium successo';
            toast.innerHTML = `<span style="font-size: 1.5rem;"></span><span>${savedSuccessToast}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('nascondi');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }

        // --- 2. MODALE ELIMINAZIONE ---
        const modalConferma = document.getElementById('modal-conferma');
        const btnConfermaNo = document.getElementById('btn-conferma-no');
        const btnConfermaSi = document.getElementById('btn-conferma-si');
        const testoConferma = document.getElementById('testo-conferma');
        let formDaInviare = null;

        document.querySelectorAll('.btn-elimina-custom').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                formDaInviare = this.closest('form'); 
                let messaggio = this.getAttribute('data-messaggio');
                if(messaggio) testoConferma.innerText = messaggio;
                modalConferma.classList.add('active'); 
            });
        });

        if(btnConfermaNo) {
            btnConfermaNo.addEventListener('click', () => {
                modalConferma.classList.remove('active');
                formDaInviare = null;
            });
        }

        if(btnConfermaSi) {
            btnConfermaSi.addEventListener('click', () => {
                if(formDaInviare) {
                    btnConfermaSi.innerText = "Attendere...";
                    formDaInviare.submit(); 
                }
            });
        }

        // --- 3. MODALE MODIFICA PRENOTAZIONE (AJAX) ---
        const modalEditBooking = document.getElementById('editBookingModal');
        const btnChiudiEditBooking = document.getElementById('btn-chiudi-edit-booking');
        const formEditBooking = document.getElementById('form-edit-booking');
        const notificaEdit = document.getElementById('notifica-edit-modal');
        const btnSalvaEdit = document.getElementById('btn-salva-edit');

        document.querySelectorAll('.btn-apri-modifica-prenotazione').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit-booking-id').value = this.getAttribute('data-id');
                document.getElementById('lbl-edit-posto').innerText = this.getAttribute('data-posto');
                document.getElementById('lbl-edit-barca').innerText = this.getAttribute('data-barca');
                document.getElementById('edit-booking-dal').value = this.getAttribute('data-dal');
                document.getElementById('edit-booking-al').value = this.getAttribute('data-al');
                document.getElementById('edit-booking-persone').value = this.getAttribute('data-persone');
                
                notificaEdit.style.display = 'none';
                modalEditBooking.classList.add('active');
            });
        });

        if (btnChiudiEditBooking) {
            btnChiudiEditBooking.addEventListener('click', () => {
                modalEditBooking.classList.remove('active');
            });
        }

        // --- INVIO AJAX MODIFICA PRENOTAZIONE ---
        if(formEditBooking) {
            formEditBooking.addEventListener('submit', async (e) => {
                e.preventDefault(); 
                
                notificaEdit.style.display = 'none';
                btnSalvaEdit.innerText = 'Attendere...';
                btnSalvaEdit.disabled = true;

                const payload = {
                    id_prenotazione: document.getElementById('edit-booking-id').value,
                    data_inizio: document.getElementById('edit-booking-dal').value,
                    data_fine: document.getElementById('edit-booking-al').value,
                    numero_persone: document.getElementById('edit-booking-persone').value
                };

                try {
                    // PUNTO AL FILE CORRETTO: modifica-prenotazione.php
                    const response = await fetch('../server/modifica-prenotazione.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    
                    const data = await response.json();

                    if(data.success) {
                        sessionStorage.setItem('ajax_toast_success', data.message);
                        window.location.reload(); 
                    } else {
                        notificaEdit.style.display = 'block';
                        notificaEdit.style.backgroundColor = 'rgba(231, 76, 60, 0.1)';
                        notificaEdit.style.color = '#c0392b';
                        notificaEdit.style.border = '1px solid #e74c3c';
                        notificaEdit.innerText = data.message;
                    }

                } catch (error) {
                    // Errore generico pulito, nessun dettaglio tecnico mostrato all'utente
                    notificaEdit.style.display = 'block';
                    notificaEdit.style.backgroundColor = 'rgba(231, 76, 60, 0.1)';
                    notificaEdit.style.color = '#c0392b';
                    notificaEdit.style.border = '1px solid #e74c3c';
                    notificaEdit.innerText = 'Errore di connessione al server.';
                } finally {
                    btnSalvaEdit.innerText = 'Salva Modifiche';
                    btnSalvaEdit.disabled = false;
                }
            });
        }
    });
    </script>
</body>
</html>