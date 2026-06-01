

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- 1. CREAZIONE TABELLA UTENTI
CREATE TABLE IF NOT EXISTS `utenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ruolo` varchar(20) DEFAULT 'diportista',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. CREAZIONE TABELLA BARCHE
CREATE TABLE IF NOT EXISTS `barche` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utente_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `lunghezza` decimal(5,2) NOT NULL,
  `larghezza` decimal(5,2) NOT NULL DEFAULT 0.00,
  `pescaggio` decimal(5,2) NOT NULL DEFAULT 0.00,
  `altezza` decimal(5,2) DEFAULT NULL,
  `numero_immatricolazione` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `utente_id` (`utente_id`),
  CONSTRAINT `barche_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. CREAZIONE TABELLA PRENOTAZIONI
CREATE TABLE IF NOT EXISTS `prenotazioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utente_id` int(11) NOT NULL,
  `barca_id` int(11) NOT NULL,
  `posto` varchar(10) NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `numero_persone` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `utente_id` (`utente_id`),
  KEY `barca_id` (`barca_id`),
  CONSTRAINT `prenotazioni_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prenotazioni_ibfk_2` FOREIGN KEY (`barca_id`) REFERENCES `barche` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;