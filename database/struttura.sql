-- Schema completo del Database NauticHub
-- Salva questo file come struttura.sql

-- 1. CREAZIONE TABELLA UTENTI
CREATE TABLE IF NOT EXISTS `utenti` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome_utente` VARCHAR(100) NOT NULL,
  `email_utente` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `ruolo` VARCHAR(50) NOT NULL DEFAULT 'diportista'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. CREAZIONE TABELLA BARCHE (Con tutti i parametri avanzati inclusi)
CREATE TABLE IF NOT EXISTS `barche` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `utente_id` INT NOT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `tipo` VARCHAR(100) NOT NULL,
  `lunghezza` DECIMAL(5,2) NOT NULL,
  `larghezza` DECIMAL(5,2) NOT NULL,
  `pescaggio` DECIMAL(5,2) NOT NULL,
  `altezza` DECIMAL(5,2) DEFAULT NULL,
  `numero_immatricolazione` VARCHAR(50) DEFAULT NULL,
  FOREIGN KEY (`utente_id`) REFERENCES `utenti`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. CREAZIONE TABELLA PRENOTAZIONI (Con il numero di persone richiesto al check-out)
CREATE TABLE IF NOT EXISTS `prenotazioni` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `utente_id` INT NOT NULL,
  `barca_id` INT NOT NULL,
  `posto` VARCHAR(10) NOT NULL,
  `data_inizio` DATE NOT NULL,
  `data_fine` DATE NOT NULL,
  `numero_persone` INT NOT NULL,
  FOREIGN KEY (`utente_id`) REFERENCES `utenti`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`barca_id`) REFERENCES `barche`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;