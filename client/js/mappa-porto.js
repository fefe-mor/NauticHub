// js/mappa-porto.js
document.addEventListener('DOMContentLoaded', () => {

    const radiosBarca = document.querySelectorAll('.radio-barca');
    const checkboxServizi = document.querySelectorAll('.checkbox-servizio');
    const letterePontili = document.querySelectorAll('.letter-pin'); 
    const btnIndietro = document.getElementById('btn-indietro');
    
    const inputDal = document.getElementById('filtro-dal');
    const inputAl = document.getElementById('filtro-al');
    
    const modal = document.getElementById('prenotazioneModal');
    const btnAnnulla = document.getElementById('btn-annulla');
    const formPrenotazione = document.getElementById('form-prenotazione');

    // --- GESTIONE MOBILE DRAWER CON BLOCCO SCORRIMENTO ---
    const btnOpenDrawer = document.getElementById('fab-open-filters');
    const btnCloseDrawer = document.getElementById('btn-close-drawer');
    const drawer = document.getElementById('mobile-filter-drawer');
    const btnApplyFilters = document.getElementById('btn-apply-filters');

    if(btnOpenDrawer) {
        btnOpenDrawer.addEventListener('click', () => {
            drawer.classList.add('drawer-open');
            document.body.classList.add('no-scroll'); // Blocca lo sfondo
        });
    }
    if(btnCloseDrawer) {
        btnCloseDrawer.addEventListener('click', () => {
            drawer.classList.remove('drawer-open');
            document.body.classList.remove('no-scroll'); // Sblocca lo sfondo
        });
    }
    if(btnApplyFilters) {
        btnApplyFilters.addEventListener('click', () => {
            drawer.classList.remove('drawer-open');
            document.body.classList.remove('no-scroll'); // Sblocca lo sfondo
        });
    }

    // Controlli Date e attivazione dinamica moli
    inputDal.addEventListener('change', () => {
        if(inputDal.value) {
            // FIX MOBILE: Se il browser del telefono forza una data passata, la azzeriamo a oggi
            if (inputDal.value < oggiStr) {
                mostraNotifica("Non puoi selezionare una data passata.", "errore");
                inputDal.value = oggiStr;
            }
            let giornoDopo = getGiornoDopo(inputDal.value);
            inputAl.min = giornoDopo;
            if(inputAl.value && inputAl.value <= inputDal.value) {
                inputAl.value = giornoDopo;
            }
        }
        aggiornaDisponibilitaMappa();
        eseguiFiltroIntelligente(); // Attiva le lettere solo se i dati sono completi
    });
    
    inputAl.addEventListener('change', () => {
        if(inputDal.value && inputAl.value && inputAl.value <= inputDal.value) {
            mostraNotifica("Il pernottamento minimo è di 1 notte.", "errore");
            inputAl.value = getGiornoDopo(inputDal.value);
        }
        aggiornaDisponibilitaMappa();
        eseguiFiltroIntelligente(); // Attiva le lettere solo se i dati sono completi
    });


    // --- FUNZIONE PER LE NOTIFICHE ---
    const mostraNotifica = (messaggio, tipo = 'errore', targetId = 'notifica-sidebar') => {
        const box = document.getElementById(targetId);
        if(!box) return;
        
        box.className = `sys-msg sys-${tipo}`;
        const icona = tipo === 'errore' ? '<i class="fa-solid fa-triangle-exclamation"></i> ' : '<i class="fa-solid fa-circle-check"></i> ';
        box.innerHTML = icona + messaggio;
        box.style.display = 'flex';
        box.style.opacity = '1';
        
        setTimeout(() => {
            box.style.opacity = '0';
            box.style.transition = 'opacity 0.3s ease';
            setTimeout(() => { box.style.display = 'none'; }, 300); 
        }, 4000);
    };

    // Helper Date (Timezone sicuro per fuso orario locale, evita il bug UTC su iOS)
    const formattaData = (date) => {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    const oggiObj = new Date();
    const oggiStr = formattaData(oggiObj);
    
    const getGiornoDopo = (dataStr) => {
        let data = new Date(dataStr);
        data.setDate(data.getDate() + 1);
        return formattaData(data);
    };

    // Formattatore da YYYY-MM-DD a DD/MM/YYYY per il Modale (Riepilogo)
    const formatDataIT = (dataStr) => {
        if(!dataStr) return '--';
        const parti = dataStr.split('-');
        return `${parti[2]}/${parti[1]}/${parti[0]}`;
    };

    inputDal.min = oggiStr;
    inputAl.min = getGiornoDopo(oggiStr); 

    // --- AJAX DATE (Aggiorna disponibilità) ---
    const aggiornaDisponibilitaMappa = async () => {
        let dal = inputDal.value;
        let al = inputAl.value;
        if (!dal || !al) return;

        try {
            const response = await fetch(`../server/check_disponibilita.php?dal=${dal}&al=${al}`);
            const occupati = await response.json();

            document.querySelectorAll('.posto-ui').forEach(posto => {
                let codice = posto.getAttribute('data-codice');
                posto.classList.remove('occupato');
                posto.classList.add('libero', 'posto-cliccabile');
                
                if (occupati.includes(codice)) {
                    posto.classList.remove('libero', 'posto-cliccabile');
                    posto.classList.add('occupato');
                }
            });
        } catch (error) {
            console.error('Errore mappa:', error);
        }
    };

    inputDal.addEventListener('change', () => {
        if(inputDal.value) {
            let giornoDopo = getGiornoDopo(inputDal.value);
            inputAl.min = giornoDopo;
            if(inputAl.value && inputAl.value <= inputDal.value) {
                inputAl.value = giornoDopo;
            }
        }
        aggiornaDisponibilitaMappa();
    });
    
    inputAl.addEventListener('change', () => {
        if(inputDal.value && inputAl.value && inputAl.value <= inputDal.value) {
            mostraNotifica("Il pernottamento minimo è di 1 notte.", "errore");
            inputAl.value = getGiornoDopo(inputDal.value);
        }
        aggiornaDisponibilitaMappa();
    });

   // --- FILTRI INTELLIGENTI CON ATTIVAZIONE DOPO LE DATE ---
    const eseguiFiltroIntelligente = () => {
        let dal = inputDal.value;
        let al = inputAl.value;
        
        // Se le date non sono impostate, spegni preventivamente tutti i moli
        if (!dal || !al) {
            letterePontili.forEach(lettera => {
                lettera.classList.remove('compatibile');
                lettera.classList.add('incompatibile');
            });
            return;
        }

        let barcaSelezionata = document.querySelector('input[name="seleziona_barca"]:checked');
        if (!barcaSelezionata) {
            letterePontili.forEach(lettera => {
                lettera.classList.remove('compatibile');
                lettera.classList.add('incompatibile');
            });
            return;
        }
        
        let lunghezzaBarca = parseFloat(barcaSelezionata.getAttribute('data-lunghezza'));
        let vuole380v = document.getElementById('filtro-corrente').checked;
        let vuoleAcqua = document.getElementById('filtro-acqua').checked;
        let vuoleLavaggio = document.getElementById('filtro-lavaggio').checked;
        
        letterePontili.forEach(lettera => {
            let minLenMolo = parseFloat(lettera.getAttribute('data-min'));
            let maxLenMolo = parseFloat(lettera.getAttribute('data-max'));
            
            let moloHa380v = lettera.getAttribute('data-380v') === 'true';
            let moloHaAcqua = lettera.getAttribute('data-acqua') === 'true';
            let moloHaLavaggio = lettera.getAttribute('data-lavaggio') === 'true';
            
            let compatibile = true;
            
            // Verifica le fasce di dimensione impostate (Piccoli, Medi, Grandi)
            if (lunghezzaBarca < minLenMolo || lunghezzaBarca > maxLenMolo) compatibile = false;
            
            // Verifica i servizi accessori richiesti
            if (vuole380v && !moloHa380v) compatibile = false;
            if (vuoleAcqua && !moloHaAcqua) compatibile = false;
            if (vuoleLavaggio && !moloHaLavaggio) compatibile = false;

            if (compatibile) {
                lettera.classList.remove('incompatibile');
                lettera.classList.add('compatibile');
            } else {
                lettera.classList.remove('compatibile');
                lettera.classList.add('incompatibile');
            }
        });
    };

    radiosBarca.forEach(radio => radio.addEventListener('change', eseguiFiltroIntelligente));
    checkboxServizi.forEach(chk => chk.addEventListener('change', eseguiFiltroIntelligente));
    eseguiFiltroIntelligente(); // Esegui all'avvio

    // --- CLIC SULLA MAPPA ---
    letterePontili.forEach(lettera => {
        lettera.addEventListener('click', function() {
            if(!inputDal.value || !inputAl.value) {
                mostraNotifica("Seleziona prima le date di Arrivo e Partenza.", "errore");
                if(window.innerWidth <= 992) drawer.classList.add('drawer-open');
                return;
            }
            if (this.classList.contains('compatibile')) {
                document.getElementById('vista-immagine-porto').style.display = 'none';
                document.getElementById('dettaglio-molo-container').style.display = 'flex';
                document.querySelectorAll('.molo-scene').forEach(molo => molo.style.display = 'none');
                
                let targetId = this.getAttribute('data-target');
                let moloTarget = document.getElementById(targetId);
                if(moloTarget) moloTarget.style.display = 'block';
            }
        });
    });

    if(btnIndietro) {
        btnIndietro.addEventListener('click', () => {
            document.getElementById('dettaglio-molo-container').style.display = 'none';
            document.getElementById('vista-immagine-porto').style.display = 'block';
        });
    }
// --- APERTURA MODALE PRENOTAZIONE ---
    document.addEventListener('click', function(e) {
        let postoDiv = e.target.closest('.posto-cliccabile');
        if (postoDiv) {
            let barcaSelezionata = document.querySelector('input[name="seleziona_barca"]:checked');
            
            // FIX CRITICO: Se nessuna barca è selezionata, blocca l'esecuzione ed evita il crash del codice
            if (!barcaSelezionata) {
                mostraNotifica("Devi prima selezionare un'imbarcazione dal menu laterale.", "errore");
                // Apre il menu laterale da mobile se l'utente sbaglia
                const drawer = document.getElementById('mobile-filter-drawer');
                if(window.innerWidth <= 992 && drawer) drawer.classList.add('drawer-open');
                return;
            }

            let idPosto = postoDiv.getAttribute('data-codice');
            let idBarca = barcaSelezionata.value;
            let nomeBarca = barcaSelezionata.getAttribute('data-nome');

            // Passa i dati ai campi nascosti
            document.getElementById('input-barca-id').value = idBarca;
            document.getElementById('input-posto-prenotato').value = idPosto;
            
            // Popolamento dinamico dei campi di riepilogo
            document.getElementById('recap-barca').innerText = nomeBarca;
            document.getElementById('posto-selezionato-id').innerText = idPosto;
            document.getElementById('recap-dal').innerText = formatDataIT(inputDal.value);
            document.getElementById('recap-al').innerText = formatDataIT(inputAl.value);
            
            document.getElementById('notifica-modal').style.display = 'none';
            
            // Mostra il modale con effetto fluido
            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.remove('nascosto'); }, 10);
        }
    });

    // --- CHIUSURA MODALE ---
    const chiudiModale = () => {
        modal.classList.add('nascosto');
        setTimeout(() => { modal.style.display = 'none'; }, 300);
    };
    
    if(btnAnnulla) btnAnnulla.addEventListener('click', chiudiModale);

    window.addEventListener('click', (e) => {
        if (e.target === modal) chiudiModale();
    });

    // --- INVIO PRENOTAZIONE (AJAX) ---
    if(formPrenotazione) {
        formPrenotazione.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            let posto = document.getElementById('input-posto-prenotato').value;
            let idBarca = document.getElementById('input-barca-id').value;
            let dal = inputDal.value;
            let al = inputAl.value;
            let numeroPersone = document.getElementById('input-numero-persone').value || "1"; // Modificato il fallback a 1
            
            let btnConferma = document.getElementById('btn-conferma');
            btnConferma.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Elaborazione...';
            btnConferma.disabled = true;

            try {
                const response = await fetch('../server/prenota.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ posto: posto, barca_id: idBarca, dal: dal, al: al, numero_persone: numeroPersone })
                });

                const data = await response.json();

                if (data.success) {
                    // 1. Salva il messaggio di successo nel sessionStorage (la dashboard lo leggerà in automatico all'avvio)
                    sessionStorage.setItem('ajax_toast_success', data.message);
                    
                    // 2. Forza la tab "Prenotazioni" ad aprirsi come attiva nella dashboard
                    sessionStorage.setItem('activeTab', 'tab-prenotazioni');
                    
                    // 3. Reindirizza l'utente alla pagina dashboard.php
                    window.location.href = 'dashboard.php';
                } else {
                    mostraNotifica(data.message, "errore", "notifica-modal");
                }

            } catch (error) {
                mostraNotifica("Si è verificato un errore di connessione.", "errore", "notifica-modal");
            } finally {
                btnConferma.innerHTML = '<i class="fa-solid fa-anchor"></i> Autorizza Attracco';
                btnConferma.disabled = false;
            }
        });
    }
});