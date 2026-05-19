// client/js/mappa.js
document.addEventListener('DOMContentLoaded', () => {

    const radiosBarca = document.querySelectorAll('.radio-barca');
    const checkboxServizi = document.querySelectorAll('.checkbox-servizio');
    const letterePontili = document.querySelectorAll('.lettera-pontile');
    const btnIndietro = document.getElementById('btn-indietro');
    
    const inputDal = document.getElementById('filtro-dal');
    const inputAl = document.getElementById('filtro-al');
    
    const modal = document.getElementById('prenotazioneModal');
    const btnAnnulla = document.getElementById('btn-annulla');
    const formPrenotazione = document.getElementById('form-prenotazione');

    // --- FUNZIONE PER LE NOTIFICHE (Addio Alert!) ---
    const mostraNotifica = (messaggio, tipo = 'errore', targetId = 'notifica-sidebar') => {
        const box = document.getElementById(targetId);
        if(!box) return;
        
        box.className = `notifica-toast ${tipo}`;
        box.innerText = messaggio;
        box.style.display = 'block';
        
        // Sparisce da solo dopo 4 secondi
        setTimeout(() => {
            box.style.opacity = '0';
            setTimeout(() => { 
                box.style.display = 'none'; 
                box.style.opacity = '1'; 
            }, 300); // Tempo dell'animazione
        }, 4000);
    };

    // Helper Date
    const formattaData = (date) => date.toISOString().split('T')[0];
    const oggiObj = new Date();
    const oggiStr = formattaData(oggiObj);
    
    const getGiornoDopo = (dataStr) => {
        let data = new Date(dataStr);
        data.setDate(data.getDate() + 1);
        return formattaData(data);
    }

    inputDal.min = oggiStr;
    inputAl.min = getGiornoDopo(oggiStr); 

    // --- AJAX DATE ---
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
            console.error('Errore aggiornamento mappa:', error);
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

    // --- FILTRI INTELLIGENTI ---
    const eseguiFiltroIntelligente = () => {
        let barcaSelezionata = document.querySelector('input[name="seleziona_barca"]:checked');
        if (!barcaSelezionata) return;
        
        let lunghezzaBarca = parseFloat(barcaSelezionata.getAttribute('data-lunghezza'));
        let vuole380v = document.getElementById('filtro-corrente').checked;
        let vuoleAcqua = document.getElementById('filtro-acqua').checked;
        let vuoleLavaggio = document.getElementById('filtro-lavaggio').checked;
        
        letterePontili.forEach(lettera => {
            let maxLenMolo = parseFloat(lettera.getAttribute('data-max'));
            let moloHa380v = lettera.getAttribute('data-380v') === 'true';
            let moloHaAcqua = lettera.getAttribute('data-acqua') === 'true';
            let moloHaLavaggio = lettera.getAttribute('data-lavaggio') === 'true';
            
            let compatibile = true;
            if (lunghezzaBarca > maxLenMolo) compatibile = false;
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

    // --- CLIC SULLA MAPPA ---
    letterePontili.forEach(lettera => {
        lettera.addEventListener('click', function() {
            if(!inputDal.value || !inputAl.value) {
                mostraNotifica("Per favore, seleziona le date di Arrivo e Partenza prima di scegliere il molo.", "errore");
                return;
            }
            if (this.classList.contains('compatibile')) {
                document.getElementById('vista-immagine-porto').style.display = 'none';
                document.getElementById('dettaglio-molo-container').style.display = 'block';
                document.querySelectorAll('.pagina-molo').forEach(molo => molo.style.display = 'none');
                let moloTarget = document.getElementById(this.getAttribute('data-target'));
                if(moloTarget) moloTarget.style.display = 'block';
            }
        });
    });

    btnIndietro.addEventListener('click', () => {
        document.getElementById('dettaglio-molo-container').style.display = 'none';
        document.getElementById('vista-immagine-porto').style.display = 'block';
    });

    // --- APERTURA MODALE ---
    document.addEventListener('click', function(e) {
        let postoDiv = e.target.closest('.posto-cliccabile');
        if (postoDiv) {
            let barcaSelezionata = document.querySelector('input[name="seleziona_barca"]:checked');
            if (!barcaSelezionata) { 
                mostraNotifica("Seleziona prima la barca dalla tendina a sinistra!", "errore"); 
                return; 
            }
            
            let idPosto = postoDiv.getAttribute('data-codice');
            let idBarca = barcaSelezionata.value;

            document.getElementById('input-barca-id').value = idBarca;
            document.getElementById('posto-selezionato-id').innerText = idPosto;
            document.getElementById('input-posto-prenotato').value = idPosto;
            
            // Svuota il campo persone ogni volta che apri il modale
            document.getElementById('input-numero-persone').value = '';
            
            // Resetta eventuali notifiche rimaste nel modale
            document.getElementById('notifica-modal').style.display = 'none';
            modal.classList.remove('nascosto');
        }
    });

    btnAnnulla.addEventListener('click', () => {
        modal.classList.add('nascosto');
    });

    // --- SALVATAGGIO AJAX ---
    formPrenotazione.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        let posto = document.getElementById('input-posto-prenotato').value;
        let idBarca = document.getElementById('input-barca-id').value;
        let dal = inputDal.value;
        let al = inputAl.value;
        let numeroPersone = document.getElementById('input-numero-persone').value; // <-- RECUPERO DATO
        let btnConferma = document.getElementById('btn-conferma');
        
        if(new Date(al) <= new Date(dal)) {
             mostraNotifica("Errore: Il pernottamento minimo è di una notte.", "errore", "notifica-modal");
             return;
        }

        btnConferma.innerText = "Attendere...";
        btnConferma.disabled = true;

        try {
            const response = await fetch('../server/prenota.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                // AGGIUNTO IL NUMERO DI PERSONE NEL PAYLOAD JSON
                body: JSON.stringify({ posto: posto, barca_id: idBarca, dal: dal, al: al, numero_persone: numeroPersone })
            });

            const data = await response.json();

            if (data.success) {
                // Modale chiuso e successo verde sulla sidebar
                modal.classList.add('nascosto');
                mostraNotifica(data.message, "successo", "notifica-sidebar");
                aggiornaDisponibilitaMappa();
            } else {
                // Errore server mostrato DENTRO il modale in rosso
                mostraNotifica(data.message, "errore", "notifica-modal");
            }

        } catch (error) {
            mostraNotifica("Si è verificato un errore di connessione.", "errore", "notifica-modal");
        } finally {
            btnConferma.innerText = "Conferma";
            btnConferma.disabled = false;
        }
    });
});