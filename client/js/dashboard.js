// js/dashboard.js
// Riscritto interamente in Vanilla JS (JS Puro) per un codice accademico impeccabile ed efficiente

document.addEventListener('DOMContentLoaded', () => {
    
    // =========================================
    // 1. GESTIONE TAB CON SESSION STORAGE
    // =========================================
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    let savedTab = sessionStorage.getItem('activeTab');
    
    // Ripristina l'ultima tab attiva salvata nella sessione
    if (savedTab) {
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));
        
        const activeTabBtn = document.querySelector(`.tab-btn[data-target="${savedTab}"]`);
        const activeContent = document.getElementById(savedTab);
        
        if (activeTabBtn && activeContent) {
            activeTabBtn.classList.add('active');
            activeContent.classList.add('active');
        }
    }

    // Cambiamento della Tab al Click
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');

            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            this.classList.add('active');
            const targetContent = document.getElementById(targetId);
            if (targetContent) targetContent.classList.add('active');

            sessionStorage.setItem('activeTab', targetId);

            // Bug-Fix Leaflet: Forza la mappa a ridisegnare i bordi se la tab diventa visibile
            if (targetId === 'tab-prenota' && window.mappaPorti) {
                setTimeout(() => { 
                    window.mappaPorti.invalidateSize(); 
                }, 150);
            }
        });
    });

    // =========================================
    // 2. GESTIONE MAPPA LEAFLET (TEMA TECH)
    // =========================================
    if (document.getElementById('mappa-vera')) {
        // Inizializzazione della mappa centrata su Genova
        window.mappaPorti = L.map('mappa-vera').setView([44.4056, 8.9463], 6);
        
        // Mappa OpenStreetMap standard
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
            maxZoom: 18 
        }).addTo(window.mappaPorti);
        
        // Icona personalizzata Oro Premium per il Marker
        const iconaPorto = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], 
            iconAnchor: [12, 41], 
            popupAnchor: [1, -34], 
            shadowSize: [41, 41]
        });

        // Marker Porto di Genova (Attivo)
        L.marker([44.4056, 8.9463], { icon: iconaPorto }).addTo(window.mappaPorti)
            .bindPopup(`
                <div class="custom-popup">
                    <div class="popup-title">Smart Marina Genova</div>
                    <div class="popup-desc">Liguria • 150 Posti • 380V Disponibile</div>
                    <a href="presentazione-genova.php" class="btn-prenota-popup">Vedi e Prenota</a>
                </div>
            `, { className: 'custom-popup' })
            .openPopup();
        
        // Porti futuri (Coming Soon)
        L.marker([41.13, 16.85]).addTo(window.mappaPorti)
            .bindPopup('<div class="custom-popup"><div class="popup-title" style="color:#7f8c8d;">Marina di Bari</div><div class="popup-desc">Prossimamente disponibile</div></div>', { className: 'custom-popup' });
            
        L.marker([41.1325, 9.5317]).addTo(window.mappaPorti)
            .bindPopup('<div class="custom-popup"><div class="popup-title" style="color:#7f8c8d;">Porto Cervo</div><div class="popup-desc">Prossimamente disponibile</div></div>', { className: 'custom-popup' });
    }

    // =========================================
    // 3. GESTIONE MODALE BARCHE
    // =========================================
    const modal = document.getElementById('boatModal');
    const btnChiudi = document.getElementById('btn-chiudi-modal');
    const btnAggiungi = document.getElementById('btn-aggiungi-barca');
    const btnModifiche = document.querySelectorAll('.btn-modifica');

    const apriModalBarca = (id, nome, tipo, lunghezza, larghezza, pescaggio, altezza, immatricolazione) => {
        document.getElementById('modal-title').innerText = (id === 'nuovo') ? "Aggiungi Imbarcazione" : "Modifica Imbarcazione";
        document.getElementById('boat-id').value = id;
        document.getElementById('boat-name').value = nome;
        document.getElementById('boat-type').value = tipo;
        document.getElementById('boat-length').value = lunghezza;
        
        // Campi aggiuntivi mappati per evitare svuotamenti accidentali
        if(document.getElementById('boat-width')) document.getElementById('boat-width').value = larghezza || '';
        if(document.getElementById('boat-draft')) document.getElementById('boat-draft').value = pescaggio || '';
        if(document.getElementById('boat-height')) document.getElementById('boat-height').value = altezza || '';
        
        // Gestione Radio Button Immatricolazione
        const radioNo = document.querySelector('input[name="ha_immatricolazione"][value="no"]');
        const radioSi = document.querySelector('input[name="ha_immatricolazione"][value="si"]');
        
        if (immatricolazione && immatricolazione !== '') {
            if(radioSi) radioSi.click();
            if(document.getElementById('boat-immatricolazione')) document.getElementById('boat-immatricolazione').value = immatricolazione;
        } else {
            if(radioNo) radioNo.click();
            if(document.getElementById('boat-immatricolazione')) document.getElementById('boat-immatricolazione').value = '';
        }

        modal.classList.add('active');
    };

    if(btnChiudi) {
        btnChiudi.addEventListener('click', () => modal.classList.remove('active'));
    }

    if(btnAggiungi) {
        btnAggiungi.addEventListener('click', () => apriModalBarca('nuovo', '', '', '', '', '', '', ''));
    }

    btnModifiche.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const target = e.currentTarget;
            apriModalBarca(
                target.getAttribute('data-id'),
                target.getAttribute('data-nome'),
                target.getAttribute('data-tipo'),
                target.getAttribute('data-lunghezza'),
                target.getAttribute('data-larghezza'),
                target.getAttribute('data-pescaggio'),
                target.getAttribute('data-altezza'),
                target.getAttribute('data-immatricolazione')
            );
        });
    });

    // Chiusura modali cliccando all'esterno del box di vetro
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('active');
        const modalEdit = document.getElementById('editBookingModal');
        if (e.target === modalEdit) modalEdit.classList.remove('active');
        const modalConf = document.getElementById('modal-conferma');
        if (e.target === modalConf) modalConf.classList.remove('active');
    });
});