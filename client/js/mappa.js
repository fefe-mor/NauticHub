// client/js/mappa.js
document.addEventListener('DOMContentLoaded', () => {

    const radiosBarca = document.querySelectorAll('.radio-barca');
    const checkboxServizi = document.querySelectorAll('.checkbox-servizio');
    const letterePontili = document.querySelectorAll('.lettera-pontile');
    const btnIndietro = document.getElementById('btn-indietro');
    
    // Nuovi input date
    const inputDal = document.getElementById('filtro-dal');
    const inputAl = document.getElementById('filtro-al');
    
    const modal = document.getElementById('prenotazioneModal');
    const btnAnnulla = document.getElementById('btn-annulla');
    const formPrenotazione = document.getElementById('form-prenotazione');

    // Funzione per impostare la data di oggi come minimo
    const oggi = new Date().toISOString().split('T')[0];
    inputDal.min = oggi;
    inputAl.min = oggi;

    // --- MAGIA AJAX PER LE DATE ---
    const aggiornaDisponibilitaMappa = async () => {
        let dal = inputDal.value;
        let al = inputAl.value;
        
        // Se mancano le date, non facciamo nulla
        if (!dal || !al) return;

        try {
            const response = await fetch(`../server/check_disponibilita.php?dal=${dal}&al=${al}`);
            const occupati = await response.json();

            // Aggiorniamo tutti i posti della mappa in tempo reale
            document.querySelectorAll('.posto-ui').forEach(posto => {
                let codice = posto.getAttribute('data-codice');
                
                // Resettiamo a libero
                posto.classList.remove('occupato');
                posto.classList.add('libero', 'posto-cliccabile');
                
                // Se il server dice che è occupato in quelle date, lo rendiamo rosso
                if (occupati.includes(codice)) {
                    posto.classList.remove('libero', 'posto-cliccabile');
                    posto.classList.add('occupato');
                }
            });
        } catch (error) {
            console.error('Errore durante la verifica della disponibilità:', error);
        }
    };

    // Ascoltiamo i cambiamenti sulle date
    inputDal.addEventListener('change', () => {
        if(inputAl.value && inputAl.value < inputDal.value) inputAl.value = inputDal.value;
        aggiornaDisponibilitaMappa();
    });
    inputAl.addEventListener('change', aggiornaDisponibilitaMappa);

    // --- FILTRO BARCHE E SERVIZI (Identico a prima) ---
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

    // --- NAVIGAZIONE MAPPA ---
    letterePontili.forEach(lettera => {
        lettera.addEventListener('click', function() {
            if(!inputDal.value || !inputAl.value) {
                alert("Per favore, seleziona le date di Arrivo e Partenza prima di scegliere il molo.");
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

    // --- APERTURA MODALE PRENOTAZIONE ---
    // Usiamo event delegation perché i posti si aggiornano dinamicamente
    document.addEventListener('click', function(e) {
        // Cerca se l'elemento cliccato (o i suoi genitori) ha la classe 'posto-cliccabile'
        let postoDiv = e.target.closest('.posto-cliccabile');
        
        if (postoDiv) {
            let barcaSelezionata = document.querySelector('input[name="seleziona_barca"]:checked');
            if (!barcaSelezionata) { 
                alert("Seleziona prima la barca dalla tendina a sinistra!"); 
                return; 
            }
            
            let idPosto = postoDiv.getAttribute('data-codice');
            let idBarca = barcaSelezionata.value;

            document.getElementById('input-barca-id').value = idBarca;
            document.getElementById('posto-selezionato-id').innerText = idPosto;
            document.getElementById('input-posto-prenotato').value = idPosto;
            
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
        let btnConferma = document.getElementById('btn-conferma');
        
        btnConferma.innerText = "Attendere...";
        btnConferma.disabled = true;

        try {
            const response = await fetch('../server/prenota.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ posto: posto, barca_id: idBarca, dal: dal, al: al })
            });

            if (!response.ok) throw new Error(`Errore di rete: HTTP ${response.status}`);

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                modal.classList.add('nascosto');
                // Aggiorniamo subito la mappa visivamente ricaricando le disponibilità per le date attuali
                aggiornaDisponibilitaMappa();
            } else {
                alert("Errore: " + data.message);
            }

        } catch (error) {
            alert("Si è verificato un errore di connessione: " + error.message);
        } finally {
            btnConferma.innerText = "Conferma";
            btnConferma.disabled = false;
        }
    });
});