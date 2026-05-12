CREATE DATABASE IF NOT EXISTS nautichub_db;
USE nautichub_db;

-- Tabella Utenti
CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo VARCHAR(20) DEFAULT 'diportista'
);

-- Tabella Barche (Collegata agli utenti)
CREATE TABLE barche (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    lunghezza DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
);

-- Tabella Prenotazioni (Collegata a utenti e barche)
CREATE TABLE prenotazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    barca_id INT NOT NULL,
    posto VARCHAR(10) NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (barca_id) REFERENCES barche(id) ON DELETE CASCADE
);