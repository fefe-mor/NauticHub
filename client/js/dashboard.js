// js/dashboard.js
// Riscritto interamente in Vanilla JS (JS Puro) per un codice accademico impeccabile ed efficiente


document.addEventListener('DOMContentLoaded', () => {
    // =========================================
    // 0. GESTIONE DINAMICA IMMATRICOLAZIONE
    // =========================================
    const boxImmatricolazione = document.getElementById('box-immatricolazione');
    const inputImmatricolazione = document.getElementById('boat-immatricolazione');
    const radioImmatricolazione = document.querySelectorAll('.radio-immatricolazione');

    const gestisciImmatricolazione = (valore) => {
        if (!boxImmatricolazione || !inputImmatricolazione) return;
        if (valore === 'si') {
            boxImmatricolazione.style.display = 'block';
            inputImmatricolazione.required = true; 
        } else {
            boxImmatricolazione.style.display = 'none';
            inputImmatricolazione.required = false; 
            inputImmatricolazione.value = ''; 
        }
    };

    // Ascolto il cambio di selezione dall'utente (FIX iPhone/iOS Safari)
    radioImmatricolazione.forEach(radio => {
        // Ascoltiamo sia 'change' che 'click' per aggirare il blocco di iOS
        ['change', 'click', 'touchstart'].forEach(evento => {
            radio.addEventListener(evento, (e) => {
                gestisciImmatricolazione(e.target.value);
            });
        });
    });
    
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
                }, 450);
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

        // Calcola una larghezza minima dinamica: più piccola sui telefoni
        const larghezzaPopUp = window.innerWidth <= 768 ? 200 : 260;
        
        // Marker Porto di Genova (Attivo)
        L.marker([44.4056, 8.9463], { icon: iconaPorto }).addTo(window.mappaPorti)
            .bindPopup(`
                <div class="custom-popup">
                    <div class="popup-title">Smart Marina Genova</div>
                    <div class="popup-desc">Liguria • 150 Posti • 380V Disponibile</div>
                    <a href="presentazione-genova.php" class="btn-prenota-popup">Vedi e Prenota</a>
                </div>
            `, { className: 'custom-popup',
                 minWidth: larghezzaPopUp
             })
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
        

        // Gestione Radio Button Immatricolazione (Fix affidabile)
        const radioNo = document.querySelector('input[name="ha_immatricolazione"][value="no"]');
        const radioSi = document.querySelector('input[name="ha_immatricolazione"][value="si"]');
        
        if (immatricolazione && immatricolazione !== '') {
            if(radioSi) { 
                radioSi.checked = true; 
                gestisciImmatricolazione('si'); 
            }
            if(document.getElementById('boat-immatricolazione')) {
                document.getElementById('boat-immatricolazione').value = immatricolazione;
            }
        } else {
            if(radioNo) { 
                radioNo.checked = true; 
                gestisciImmatricolazione('no'); 
            }
            if(document.getElementById('boat-immatricolazione')) {
                document.getElementById('boat-immatricolazione').value = '';
            }
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

    // =========================================
    // 4. GESTIONE TOAST NOTIFICHE
    // =========================================
    // Toast da PHP (Salvataggio/Eliminazione base)
    const toastSistema = document.getElementById('toast-sistema');
    if (toastSistema) {
        setTimeout(() => {
            toastSistema.classList.add('nascondi');
            setTimeout(() => toastSistema.remove(), 500);
        }, 4000);
    }

    // Toast da AJAX (Salvato in cache dalla mappa)
    const savedSuccessToast = sessionStorage.getItem('ajax_toast_success');
    if (savedSuccessToast) {
        sessionStorage.removeItem('ajax_toast_success');
        const toast = document.createElement('div');
        toast.className = 'toast-premium successo';
        toast.innerHTML = `<i class="fa-solid fa-circle-check"></i><span>${savedSuccessToast}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('nascondi');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    // =========================================
    // 5. MODALE ELIMINAZIONE CONFERMA
    // =========================================
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
                btnConfermaSi.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Attendere...';
                formDaInviare.submit(); 
            }
        });
    }

    // =========================================
    // 6. AJAX MODIFICA PRENOTAZIONE
    // =========================================
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

    if (btnChiudiEditBooking) btnChiudiEditBooking.addEventListener('click', () => modalEditBooking.classList.remove('active'));

    if(formEditBooking) {
        formEditBooking.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            notificaEdit.style.display = 'none';
            btnSalvaEdit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Attendere...';
            btnSalvaEdit.disabled = true;

            const payload = {
                id_prenotazione: document.getElementById('edit-booking-id').value,
                data_inizio: document.getElementById('edit-booking-dal').value,
                data_fine: document.getElementById('edit-booking-al').value,
                numero_persone: document.getElementById('edit-booking-persone').value || "1" 
            };

            try {
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
                    notificaEdit.className = 'sys-msg sys-error';
                    notificaEdit.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${data.message}`;
                }
            } catch (error) {
                notificaEdit.style.display = 'block';
                notificaEdit.className = 'sys-msg sys-error';
                notificaEdit.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Errore di connessione al server.';
            } finally {
                btnSalvaEdit.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Aggiorna Prenotazione';
                btnSalvaEdit.disabled = false;
            }
        });
    }

    // Chiusura di tutti i modali cliccando all'esterno del box di vetro
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('active');
        if (e.target === modalEditBooking) modalEditBooking.classList.remove('active');
        if (e.target === modalConferma) modalConferma.classList.remove('active');
    });
});