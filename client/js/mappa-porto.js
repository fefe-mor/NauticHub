document.addEventListener('DOMContentLoaded', () => {


    const pulsantiRadioBarca = document.querySelectorAll('.radio-barca');
    const checkboxServiziBanchina = document.querySelectorAll('.checkbox-servizio');
    const lettereMoli = document.querySelectorAll('.letter-pin'); 
    const pulsanteIndietro = document.getElementById('btn-indietro');
    
    const campoDataArrivo = document.getElementById('filtro-dal');
    const campoDataPartenza = document.getElementById('filtro-al');
    
    const modalePrenotazione = document.getElementById('prenotazioneModal');
    const pulsanteAnnullaModale = document.getElementById('btn-annulla');
    const moduloPrenotazione = document.getElementById('form-prenotazione');

    const pulsanteApriFiltriMobile = document.getElementById('fab-open-filters');
    const pulsanteChiudiFiltriMobile = document.getElementById('btn-close-drawer');
    const cassettoFiltriMobile = document.getElementById('mobile-filter-drawer');
    const pulsanteApplicaFiltri = document.getElementById('btn-apply-filters');


    // GESTIONE MENU LATERALE SU SMARTPHONE
   
    if(pulsanteApriFiltriMobile) {
        pulsanteApriFiltriMobile.addEventListener('click', () => {
            cassettoFiltriMobile.classList.add('drawer-open');
            document.body.classList.add('no-scroll'); 
        });
    }
    
    if(pulsanteChiudiFiltriMobile) {
        pulsanteChiudiFiltriMobile.addEventListener('click', () => {
            cassettoFiltriMobile.classList.remove('drawer-open');
            document.body.classList.remove('no-scroll');
        });
    }
    
    if(pulsanteApplicaFiltri) {
        pulsanteApplicaFiltri.addEventListener('click', () => {
            cassettoFiltriMobile.classList.remove('drawer-open');
            document.body.classList.remove('no-scroll');
        });
    }


    
    const mostraAvvisoDiSistema = (messaggio, tipo = 'error', targetId = 'notifica-sidebar') => {
        const contenitoreAvviso = document.getElementById(targetId);
        if(!contenitoreAvviso) return;
        
        contenitoreAvviso.className = `sys-msg sys-${tipo}`;
        const icona = tipo === 'error' ? '<i class="fa-solid fa-triangle-exclamation"></i> ' : '<i class="fa-solid fa-circle-check"></i> ';
        
        contenitoreAvviso.innerHTML = icona + messaggio;
        contenitoreAvviso.classList.remove('nascosto');
        contenitoreAvviso.style.display = 'flex';
        contenitoreAvviso.style.opacity = '1';
        
        setTimeout(() => {
            contenitoreAvviso.style.opacity = '0';
            contenitoreAvviso.style.transition = 'opacity 0.3s ease';
            setTimeout(() => { 
                contenitoreAvviso.style.display = 'none'; 
                contenitoreAvviso.classList.add('nascosto');
            }, 300); 
        }, 4000);
    };

    const formattaDataTestuale = (dataInserita) => {
        const d = new Date(dataInserita);
        const anno = d.getFullYear();
        const mese = String(d.getMonth() + 1).padStart(2, '0');
        const giorno = String(d.getDate()).padStart(2, '0');
        return `${anno}-${mese}-${giorno}`;
    };
    
    const oggettoOggi = new Date();
    const stringaOggi = formattaDataTestuale(oggettoOggi);
    
    const ottieniGiornoSuccessivo = (stringaData) => {
        let data = new Date(stringaData);
        data.setDate(data.getDate() + 1);
        return formattaDataTestuale(data);
    };

    const convertiDataItaliana = (stringaData) => {
        if(!stringaData) return '--';
        const parti = stringaData.split('-');
        return `${parti[2]}/${parti[1]}/${parti[0]}`;
    };

    campoDataArrivo.min = stringaOggi;
    campoDataPartenza.min = ottieniGiornoSuccessivo(stringaOggi); 

    const aggiornaStatoPostiMappa = async () => {
        let dataArrivo = campoDataArrivo.value;
        let dataPartenza = campoDataPartenza.value;
        if (!dataArrivo || !dataPartenza) return;

        try {
            const risposta = await fetch(`../server/check_disponibilita.php?dal=${dataArrivo}&al=${dataPartenza}`);
            const postiOccupati = await risposta.json();

            document.querySelectorAll('.posto-ui').forEach(posto => {
                let codicePosto = posto.getAttribute('data-codice');
               
                posto.classList.remove('occupato');
                posto.classList.add('libero', 'posto-cliccabile');
                
                if (postiOccupati.includes(codicePosto)) {
                    posto.classList.remove('libero', 'posto-cliccabile');
                    posto.classList.add('occupato');
                }
            });
        } catch (errore) {
            console.error('NauticHub Errore Server:', errore);
        }
    };

    campoDataArrivo.addEventListener('change', () => {
        if(campoDataArrivo.value) {
            if (campoDataArrivo.value < stringaOggi) {
                mostraAvvisoDiSistema("Non puoi selezionare una data passata.", "error");
                campoDataArrivo.value = stringaOggi;
            }
            let giornoDopo = ottieniGiornoSuccessivo(campoDataArrivo.value);
            campoDataPartenza.min = giornoDopo;
            
            if(campoDataPartenza.value && campoDataPartenza.value <= campoDataArrivo.value) {
                campoDataPartenza.value = giornoDopo;
            }
        }
        aggiornaStatoPostiMappa();
        applicaFiltriIntelligenti(); 
    });
    
    campoDataPartenza.addEventListener('change', () => {
        if(campoDataArrivo.value && campoDataPartenza.value && campoDataPartenza.value <= campoDataArrivo.value) {
            mostraAvvisoDiSistema("Il pernottamento minimo è di 1 notte.", "error");
            campoDataPartenza.value = ottieniGiornoSuccessivo(campoDataArrivo.value);
        }
        aggiornaStatoPostiMappa();
        applicaFiltriIntelligenti(); 
    });

    const applicaFiltriIntelligenti = () => {
        let dataArrivo = campoDataArrivo.value;
        let dataPartenza = campoDataPartenza.value;
        
        if (!dataArrivo || !dataPartenza) {
            lettereMoli.forEach(lettera => {
                lettera.classList.remove('compatibile');
                lettera.classList.add('incompatibile');
            });
            return;
        }

        let barcaSelezionata = document.querySelector('input[name="seleziona_barca"]:checked');
        
        if (!barcaSelezionata) {
            lettereMoli.forEach(lettera => {
                lettera.classList.remove('compatibile');
                lettera.classList.add('incompatibile');
            });
            return;
        }
        
        let lunghezzaBarca = parseFloat(barcaSelezionata.getAttribute('data-lunghezza'));
        let richiedeCorrente = document.getElementById('filtro-corrente').checked;
        let richiedeAcqua = document.getElementById('filtro-acqua').checked;
        let richiedeLavaggio = document.getElementById('filtro-lavaggio').checked;
        
        lettereMoli.forEach(lettera => {
            let lunghezzaMinima = parseFloat(lettera.getAttribute('data-min'));
            let lunghezzaMassima = parseFloat(lettera.getAttribute('data-max'));
            
            let moloHaCorrente = lettera.getAttribute('data-380v') === 'true';
            let moloHaAcqua = lettera.getAttribute('data-acqua') === 'true';
            let moloHaLavaggio = lettera.getAttribute('data-lavaggio') === 'true';
            
            let risultaCompatibile = true;
            
            if (lunghezzaBarca < lunghezzaMinima || lunghezzaBarca > lunghezzaMassima) risultaCompatibile = false;
            if (richiedeCorrente && !moloHaCorrente) risultaCompatibile = false;
            if (richiedeAcqua && !moloHaAcqua) risultaCompatibile = false;
            if (richiedeLavaggio && !moloHaLavaggio) risultaCompatibile = false;

            if (risultaCompatibile) {
                lettera.classList.remove('incompatibile');
                lettera.classList.add('compatibile');
            } else {
                lettera.classList.remove('compatibile');
                lettera.classList.add('incompatibile');
            }
        });
    };

    pulsantiRadioBarca.forEach(radio => radio.addEventListener('change', applicaFiltriIntelligenti));
    checkboxServiziBanchina.forEach(chk => chk.addEventListener('change', applicaFiltriIntelligenti));
    
    applicaFiltriIntelligenti(); 

    lettereMoli.forEach(lettera => {
        lettera.addEventListener('click', function() {
            if(!campoDataArrivo.value || !campoDataPartenza.value) {
                mostraAvvisoDiSistema("Seleziona prima le date di Arrivo e Partenza.", "error");
                if(window.innerWidth <= 992) cassettoFiltriMobile.classList.add('drawer-open');
                return;
            }
            if (this.classList.contains('compatibile')) {
                document.getElementById('vista-immagine-porto').style.display = 'none';
                document.getElementById('dettaglio-molo-container').style.display = 'flex';
                document.querySelectorAll('.molo-scene').forEach(molo => molo.style.display = 'none');
                
                let idDestinazione = this.getAttribute('data-target');
                let moloBersaglio = document.getElementById(idDestinazione);
                if(moloBersaglio) moloBersaglio.style.display = 'block';
            }
        });
    });

    if(pulsanteIndietro) {
        pulsanteIndietro.addEventListener('click', () => {
            document.getElementById('dettaglio-molo-container').style.display = 'none';
            document.getElementById('vista-immagine-porto').style.display = 'block';
        });
    }

    document.addEventListener('click', function(evento) {
        let elementoPosto = evento.target.closest('.posto-cliccabile');
        
        if (elementoPosto) {
            let barcaSelezionata = document.querySelector('input[name="seleziona_barca"]:checked');
            
            if (!barcaSelezionata) {
                mostraAvvisoDiSistema("Devi prima selezionare un'imbarcazione dal menu laterale.", "error");
                if(window.innerWidth <= 992 && cassettoFiltriMobile) cassettoFiltriMobile.classList.add('drawer-open');
                return;
            }

            let idPostoSelezionato = elementoPosto.getAttribute('data-codice');
            let idBarcaSelezionata = barcaSelezionata.value;
            let nomeBarcaScelta = barcaSelezionata.getAttribute('data-nome');

            document.getElementById('input-barca-id').value = idBarcaSelezionata;
            document.getElementById('input-posto-prenotato').value = idPostoSelezionato;
            
            document.getElementById('recap-barca').innerText = nomeBarcaScelta;
            document.getElementById('posto-selezionato-id').innerText = idPostoSelezionato;
            document.getElementById('recap-dal').innerText = convertiDataItaliana(campoDataArrivo.value);
            document.getElementById('recap-al').innerText = convertiDataItaliana(campoDataPartenza.value);
            
            document.getElementById('notifica-modal').style.display = 'none';
            
            modalePrenotazione.style.display = 'flex';
            setTimeout(() => { modalePrenotazione.classList.remove('nascosto'); }, 10);
        }
    });

    const chiudiModalePrenotazione = () => {
        modalePrenotazione.classList.add('nascosto');
        setTimeout(() => { modalePrenotazione.style.display = 'none'; }, 300);
    };
    
    if(pulsanteAnnullaModale) pulsanteAnnullaModale.addEventListener('click', chiudiModalePrenotazione);

    window.addEventListener('click', (evento) => {
        if (evento.target === modalePrenotazione) chiudiModalePrenotazione();
    });


    
    if(moduloPrenotazione) {
        moduloPrenotazione.addEventListener('submit', async (evento) => {
            evento.preventDefault();
            
            let postoPrenotato = document.getElementById('input-posto-prenotato').value;
            let idBarcaAssegnata = document.getElementById('input-barca-id').value;
            let dataArrivo = campoDataArrivo.value;
            let dataPartenza = campoDataPartenza.value;
            let numeroMembriEquipaggio = document.getElementById('input-numero-persone').value || "1"; 
            
            let pulsanteConfermaDefinitiva = document.getElementById('btn-conferma');
            pulsanteConfermaDefinitiva.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Elaborazione...';
            pulsanteConfermaDefinitiva.disabled = true;

            try {
                const rispostaServer = await fetch('../server/prenota.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        posto: postoPrenotato, 
                        barca_id: idBarcaAssegnata, 
                        dal: dataArrivo, 
                        al: dataPartenza, 
                        numero_persone: numeroMembriEquipaggio 
                    })
                });

                const datiDecodificati = await rispostaServer.json();

                if (datiDecodificati.success) {
                    sessionStorage.setItem('ajax_toast_success', datiDecodificati.message);
                    window.location.href = 'dashboard.php?tab=prenotazioni';
                } else {
                    mostraAvvisoDiSistema(datiDecodificati.message, "error", "notifica-modal");
                }

            } catch (erroreDiRete) {
                mostraAvvisoDiSistema("Si è verificato un errore di connessione con il porto.", "error", "notifica-modal");
            } finally {
                pulsanteConfermaDefinitiva.innerHTML = '<i class="fa-solid fa-anchor"></i> Autorizza Attracco';
                pulsanteConfermaDefinitiva.disabled = false;
            }
        });
    }
});