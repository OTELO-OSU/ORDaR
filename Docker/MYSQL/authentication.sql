-- phpMyAdmin SQL Dump
-- version 4.7.5
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  mar. 12 déc. 2017 à 13:46
-- Version du serveur :  5.7.20-0ubuntu0.16.04.1
-- Version de PHP :  5.6.32-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `authentication`
--
CREATE DATABASE IF NOT EXISTS `authentication` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `authentication`;
-- --------------------------------------------------------

--
-- Structure de la table `lost_password`
--

CREATE TABLE `lost_password` (
  `mail` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `mail_validation`
--

CREATE TABLE `mail_validation` (
  `mail` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `name` varchar(40) NOT NULL,
  `firstname` varchar(40) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `mdp` varchar(256) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `mail_validation` tinyint(1) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL,
  `ORCID_ID` varchar(20) DEFAULT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`name`, `firstname`, `mail`, `mdp`, `status`, `mail_validation`, `type`, `ORCID_ID`, `created_at`, `updated_at`) VALUES
('Admin', 'Admin', 'admin@admin.fr', '$2y$10$jPFYnh8ShDAYEsVxxXm8WuTJO61/.8932ssqDEHhy.3jruX63xI6G', 1, 1, 1, '0000-0000-0000-0001', '2017-11-30', '2017-12-12');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `lost_password`
--
ALTER TABLE `lost_password`
  ADD PRIMARY KEY (`mail`);

--
-- Index pour la table `mail_validation`
--
ALTER TABLE `mail_validation`
  ADD PRIMARY KEY (`mail`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`mail`);

DELIMITER $$
--
-- Évènements
--
CREATE DEFINER=`root`@`localhost` EVENT `remove_password_token` ON SCHEDULE EVERY 30 MINUTE STARTS '2017-11-21 16:03:14' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM lost_password WHERE `datetime` < (NOW() - INTERVAL 30 MINUTE)$$

CREATE DEFINER=`root`@`localhost` EVENT `remove_invalid_user` ON SCHEDULE EVERY 1 HOUR STARTS '2017-11-21 16:03:14' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM users WHERE `mail_validation`=0$$

CREATE DEFINER=`root`@`localhost` EVENT `remove_mail_token` ON SCHEDULE EVERY 30 MINUTE STARTS '2017-11-21 16:03:14' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM mail_validation WHERE `datetime` < (NOW() - INTERVAL 30 MINUTE)$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
