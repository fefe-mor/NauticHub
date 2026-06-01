document.addEventListener('DOMContentLoaded', () => {

    const boxImmatricolazione = document.getElementById('box-immatricolazione');
    const inputImmatricolazione = document.getElementById('boat-immatricolazione');
    const radioImmatricolazione = document.querySelectorAll('.radio-immatricolazione');

    const gestisciImmatricolazione = (valore) => {
        if (!boxImmatricolazione || !inputImmatricolazione) return;
        
        if (valore === 'si') {
            boxImmatricolazione.classList.remove('box-immatricolazione-nascosto');
            boxImmatricolazione.style.display = 'block'; 
            inputImmatricolazione.required = true; 
        } else {
            boxImmatricolazione.style.display = 'none';
            boxImmatricolazione.classList.add('box-immatricolazione-nascosto');
            inputImmatricolazione.required = false; 
            inputImmatricolazione.value = ''; 
        }
    };

    radioImmatricolazione.forEach(radio => {
        ['change', 'click', 'touchstart'].forEach(evento => {
            radio.addEventListener(evento, (e) => {
                gestisciImmatricolazione(e.target.value);
            });
        });
    });
    
   
    const pulsantiSchede = document.querySelectorAll('.tab-btn');
    const contenutiSchede = document.querySelectorAll('.tab-content');
    

    const parametriUrl = new URLSearchParams(window.location.search);
    const schedaDaUrl = parametriUrl.get('tab');
    
    let schedaSalvata = sessionStorage.getItem('schedaAttivaDashboard');
    

    if (schedaDaUrl) {
        schedaSalvata = 'tab-' + schedaDaUrl;
        sessionStorage.setItem('schedaAttivaDashboard', schedaSalvata);
        window.history.replaceState(null, '', window.location.pathname);
    }

    if (schedaSalvata) {
        pulsantiSchede.forEach(btn => btn.classList.remove('active'));
        contenutiSchede.forEach(contenuto => contenuto.classList.remove('active'));
        
        const pulsanteAttivo = document.querySelector(`.tab-btn[data-target="${schedaSalvata}"]`);
        const contenutoAttivo = document.getElementById(schedaSalvata);
        
        if (pulsanteAttivo && contenutoAttivo) {
            pulsanteAttivo.classList.add('active');
            contenutoAttivo.classList.add('active');
        }
    }

    pulsantiSchede.forEach(pulsante => {
        pulsante.addEventListener('click', function() {
            const idTarget = this.getAttribute('data-target');

            pulsantiSchede.forEach(btn => btn.classList.remove('active'));
            contenutiSchede.forEach(contenuto => contenuto.classList.remove('active'));
            
            this.classList.add('active');
            const contenutoTarget = document.getElementById(idTarget);
            if (contenutoTarget) contenutoTarget.classList.add('active');

            sessionStorage.setItem('schedaAttivaDashboard', idTarget);

            if (idTarget === 'tab-prenota' && window.mappaPorti) {
                setTimeout(() => { 
                    window.mappaPorti.invalidateSize(); 
                }, 450);
            }
        });
    });

  
    // INIZIALIZZAZIONE MAPPA 
    if (document.getElementById('mappa-vera')) {
        // Creazione mappa centrata su Genova
        window.mappaPorti = L.map('mappa-vera').setView([44.4056, 8.9463], 6);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
            maxZoom: 18 
        }).addTo(window.mappaPorti);
        
        
        const iconaPortoPremium = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], 
            iconAnchor: [12, 41], 
            popupAnchor: [1, -34], 
            shadowSize: [41, 41]
        });

        const larghezzaPopUp = window.innerWidth <= 768 ? 200 : 260;
        
        // Marker Attivo: Porto di Genova
        L.marker([44.4056, 8.9463], { icon: iconaPortoPremium }).addTo(window.mappaPorti)
            .bindPopup(`
                <div class="custom-popup">
                    <div class="popup-title">Smart Marina Genova</div>
                    <div class="popup-desc">Liguria • 150 Posti • 380V Disponibile</div>
                    <a href="presentazione-genova.php" class="btn-prenota-popup">Vedi e Prenota</a>
                </div>
            `, { 
                 className: 'custom-popup',
                 minWidth: larghezzaPopUp
             })
            .openPopup();
        
        // Marker Futuri: Coming Soon
        L.marker([41.13, 16.85]).addTo(window.mappaPorti)
            .bindPopup('<div class="custom-popup"><div class="popup-title" style="color:#7f8c8d;">Marina di Bari</div><div class="popup-desc">Prossimamente disponibile</div></div>', { className: 'custom-popup' });
            
        L.marker([41.1325, 9.5317]).addTo(window.mappaPorti)
            .bindPopup('<div class="custom-popup"><div class="popup-title" style="color:#7f8c8d;">Porto Cervo</div><div class="popup-desc">Prossimamente disponibile</div></div>', { className: 'custom-popup' });
    }

    // GESTIONE MODALE BARCHE (Aggiungi/Modifica)

    const modaleBarca = document.getElementById('boatModal');
    const btnChiudiModale = document.getElementById('btn-chiudi-modal');
    const btnAggiungiBarca = document.getElementById('btn-aggiungi-barca');
    const pulsantiModifica = document.querySelectorAll('.btn-modifica');

  
    const apriModaleBarca = (id, nome, tipo, lunghezza, larghezza, pescaggio, altezza, immatricolazione) => {
        document.getElementById('modal-title').innerText = (id === 'nuovo') ? "Aggiungi Imbarcazione" : "Modifica Imbarcazione";
        document.getElementById('boat-id').value = id;
        document.getElementById('boat-name').value = nome;
        document.getElementById('boat-type').value = tipo;
        document.getElementById('boat-length').value = lunghezza;
        
        if(document.getElementById('boat-width')) document.getElementById('boat-width').value = larghezza || '';
        if(document.getElementById('boat-draft')) document.getElementById('boat-draft').value = pescaggio || '';
        if(document.getElementById('boat-height')) document.getElementById('boat-height').value = altezza || '';

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

        modaleBarca.classList.add('active');
    };

    if(btnChiudiModale) {
        btnChiudiModale.addEventListener('click', () => modaleBarca.classList.remove('active'));
    }

    if(btnAggiungiBarca) {
        btnAggiungiBarca.addEventListener('click', () => apriModaleBarca('nuovo', '', '', '', '', '', '', ''));
    }

    pulsantiModifica.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const target = e.currentTarget;
            apriModaleBarca(
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

 
    const toastSistema = document.getElementById('toast-sistema');
    if (toastSistema) {
        setTimeout(() => {
            toastSistema.classList.add('nascondi');
            setTimeout(() => toastSistema.remove(), 500);
        }, 4000);
    }

    const toastSuccessoSalvato = sessionStorage.getItem('ajax_toast_success');
    if (toastSuccessoSalvato) {
        sessionStorage.removeItem('ajax_toast_success');
        const nuovoToast = document.createElement('div');
        nuovoToast.className = 'toast-premium successo';
        
        nuovoToast.style.transform = 'translateX(120%)'; 
        
        nuovoToast.innerHTML = `<i class="fa-solid fa-circle-check"></i><span>${toastSuccessoSalvato}</span>`;
        document.body.appendChild(nuovoToast);
        
        setTimeout(() => {
            nuovoToast.style.transform = 'translateX(0)';
        }, 50);
        
        setTimeout(() => {
            nuovoToast.classList.add('nascondi');
            setTimeout(() => nuovoToast.remove(), 500);
        }, 4000);
    }

    const modaleConferma = document.getElementById('modal-conferma');
    const btnConfermaNo = document.getElementById('btn-conferma-no');
    const btnConfermaSi = document.getElementById('btn-conferma-si');
    const testoConferma = document.getElementById('testo-conferma');
    let moduloDaInviare = null;

    document.querySelectorAll('.btn-elimina-custom').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            moduloDaInviare = this.closest('form'); 
            let messaggio = this.getAttribute('data-messaggio');
            if(messaggio) testoConferma.innerText = messaggio;
            modaleConferma.classList.add('active'); 
        });
    });

    if(btnConfermaNo) {
        btnConfermaNo.addEventListener('click', () => {
            modaleConferma.classList.remove('active');
            moduloDaInviare = null;
        });
    }

    if(btnConfermaSi) {
        btnConfermaSi.addEventListener('click', () => {
            if(moduloDaInviare) {
                btnConfermaSi.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Attendere...';
                moduloDaInviare.submit(); 
            }
        });
    }

    const modaleModificaPrenotazione = document.getElementById('editBookingModal');
    const btnChiudiModificaPrenotazione = document.getElementById('btn-chiudi-edit-booking');
    const formModificaPrenotazione = document.getElementById('form-edit-booking');
    const notificaModifica = document.getElementById('notifica-edit-modal');
    const btnSalvaModifica = document.getElementById('btn-salva-edit');

    document.querySelectorAll('.btn-apri-modifica-prenotazione').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit-booking-id').value = this.getAttribute('data-id');
            document.getElementById('lbl-edit-posto').innerText = this.getAttribute('data-posto');
            document.getElementById('lbl-edit-barca').innerText = this.getAttribute('data-barca');
            document.getElementById('edit-booking-dal').value = this.getAttribute('data-dal');
            document.getElementById('edit-booking-al').value = this.getAttribute('data-al');
            document.getElementById('edit-booking-persone').value = this.getAttribute('data-persone');
            
            notificaModifica.classList.add('nascosto'); // Nasconde vecchi avvisi
            modaleModificaPrenotazione.classList.add('active');
        });
    });

    if (btnChiudiModificaPrenotazione) {
        btnChiudiModificaPrenotazione.addEventListener('click', () => modaleModificaPrenotazione.classList.remove('active'));
    }

    if(formModificaPrenotazione) {
        formModificaPrenotazione.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            notificaModifica.classList.add('nascosto');
            btnSalvaModifica.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Attendere...';
            btnSalvaModifica.disabled = true;

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
                    notificaModifica.classList.remove('nascosto');
                    notificaModifica.className = 'sys-msg sys-error'; // Ripristina le classi corrette
                    notificaModifica.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${data.message}`;
                }
            } catch (error) {
                notificaModifica.classList.remove('nascosto');
                notificaModifica.className = 'sys-msg sys-error';
                notificaModifica.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Errore di connessione al server.';
            } finally {
                btnSalvaModifica.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Aggiorna Prenotazione';
                btnSalvaModifica.disabled = false;
            }
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === modaleBarca) modaleBarca.classList.remove('active');
        if (e.target === modaleModificaPrenotazione) modaleModificaPrenotazione.classList.remove('active');
        if (e.target === modaleConferma) modaleConferma.classList.remove('active');
    });
});