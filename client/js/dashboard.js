// js/dashboard.js

// Uso di jQuery per la gestione delle Tab (Slide 11)
$(document).ready(function() {
    
    // --- 1. GESTIONE TAB CON SESSION STORAGE ---
    // Controlliamo se in memoria c'è un tab salvato dall'ultima visita
    let savedTab = sessionStorage.getItem('activeTab');
    
    if (savedTab) {
        // Togliamo l'active a tutti
        $('.tab-btn').removeClass('active');
        $('.tab-content').removeClass('active');
        // Lo diamo solo a quello salvato
        $(`.tab-btn[data-target="${savedTab}"]`).addClass('active');
        $(`#${savedTab}`).addClass('active');
    }

    // Gestione del click sulle Tab usando jQuery
    $('.tab-btn').click(function() {
        let targetId = $(this).data('target');

        // Aggiorna UI
        $('.tab-btn').removeClass('active');
        $('.tab-content').removeClass('active');
        
        $(this).addClass('active');
        $(`#${targetId}`).addClass('active');

        // Salviamo la scelta in memoria [Slide 07 - Web Storage]
        sessionStorage.setItem('activeTab', targetId);

        // Fix per il ridimensionamento della mappa Leaflet se la tab è quella della mappa
        if(targetId === 'tab-prenota' && window.mappaPorti) {
            setTimeout(() => { window.mappaPorti.invalidateSize(); }, 100);
        }
    });

    // --- 2. GESTIONE MAPPA LEAFLET (Vanilla JS) ---
    // Inizializziamo la mappa solo se l'elemento esiste
    if (document.getElementById('mappa-vera')) {
        window.mappaPorti = L.map('mappa-vera').setView([41.8719, 12.5674], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(window.mappaPorti);
        
        const iconaPorto = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        // Porto Attivo
        L.marker([41.13, 16.85], {icon: iconaPorto}).addTo(window.mappaPorti)
            .bindPopup(`<div class="popup-title">Smart Marina Bari</div><div class="popup-desc">Puglia • 150 Posti • 380V Disponibile</div><a href="mappa-porto.php" class="btn-prenota-popup">Vedi e Prenota</a>`, {className: 'custom-popup'})
            .openPopup();
        
        // Porti futuri
        L.marker([44.4056, 8.9463]).addTo(window.mappaPorti).bindPopup(`<div class="popup-title" style="color:#7f8c8d;">Marina di Genova</div><div class="popup-desc">Prossimamente disponibile</div>`, {className: 'custom-popup'});
        L.marker([41.1325, 9.5317]).addTo(window.mappaPorti).bindPopup(`<div class="popup-title" style="color:#7f8c8d;">Porto Cervo</div><div class="popup-desc">Prossimamente disponibile</div>`, {className: 'custom-popup'});
    }

    // --- 3. GESTIONE MODALE BARCHE (Vanilla JS Moderno) ---
    const modal = document.getElementById('boatModal');
    const btnChiudi = document.getElementById('btn-chiudi-modal');
    const btnAggiungi = document.getElementById('btn-aggiungi-barca');
    const btnModifiche = document.querySelectorAll('.btn-modifica');

    // Funzione per aprire il modale dinamico
    const apriModalBarca = (id, nome, tipo, lunghezza) => {
        document.getElementById('modal-title').innerText = (id === 'nuovo') ? "Aggiungi Imbarcazione" : "Modifica Imbarcazione";
        document.getElementById('boat-id').value = id;
        document.getElementById('boat-name').value = nome;
        document.getElementById('boat-type').value = tipo;
        document.getElementById('boat-length').value = lunghezza;
        modal.classList.add('active');
    };

    // Chiude il modale
    if(btnChiudi) {
        btnChiudi.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }

    // Apre modale per NUOVA barca
    if(btnAggiungi) {
        btnAggiungi.addEventListener('click', () => {
            apriModalBarca('nuovo', '', '', '');
        });
    }

    // Apre modale per MODIFICARE barca (usa i data-attributes creati nel PHP)
    // Usiamo for...of come richiesto dalla checklist JS
    for (const btn of btnModifiche) {
        btn.addEventListener('click', (e) => {
            const id = e.target.getAttribute('data-id');
            const nome = e.target.getAttribute('data-nome');
            const tipo = e.target.getAttribute('data-tipo');
            const lunghezza = e.target.getAttribute('data-lunghezza');
            apriModalBarca(id, nome, tipo, lunghezza);
        });
    }
});