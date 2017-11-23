-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Jeu 23 Novembre 2017 à 09:04
-- Version du serveur :  5.7.20-0ubuntu0.16.04.1
-- Version de PHP :  5.6.32-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `authentication`
--
CREATE DATABASE IF NOT EXISTS `authentication`;
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

--
-- Contenu de la table `lost_password`
--

INSERT INTO `lost_password` (`mail`, `token`, `datetime`, `created_at`, `updated_at`) VALUES
('guiot.anthony@free.fr', 'cf62aa007d554385e0d33dc6922238476c89a2809c470978a924b4f7ff74b500', '2017-11-23 08:52:37', '2017-11-23', '2017-11-23');

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
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`name`, `firstname`, `mail`, `mdp`, `status`, `mail_validation`, `type`, `created_at`, `updated_at`) VALUES
('Guiot', 'Anthony', 'guiot.anthony@free.fr', '$2y$10$wC/V5K8A9DN4Is/yq8KkLeyFQBQhKCkt3bXu9lLFx.LWd1sFSRhN2', 1, 1, 1, '2017-11-20', '2017-11-22'),
('Arnould', 'Pierre-Yves', 'pierre-yves.arnould@univ-lorraine.fr', '$2y$10$wC/V5K8A9DN4Is/yq8KkLeyFQBQhKCkt3bXu9lLFx.LWd1sFSRhN2', 1, 1, 1, '2017-11-20', '2017-11-22');

--
-- Index pour les tables exportées
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
-- Événements
--
CREATE DEFINER=`root`@`localhost` EVENT `remove_mail_token` ON SCHEDULE EVERY 30 MINUTE STARTS '2017-11-21 16:03:14' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM mail_validation WHERE `datetime` < (NOW() - INTERVAL 30 MINUTE)$$

CREATE DEFINER=`root`@`localhost` EVENT `remove_invalid_user` ON SCHEDULE EVERY 1 HOUR STARTS '2017-11-21 16:03:14' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM users WHERE `mail_validation`=0$$

CREATE DEFINER=`root`@`localhost` EVENT `remove_password_token` ON SCHEDULE EVERY 30 MINUTE STARTS '2017-11-21 16:03:14' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM lost_password WHERE `datetime` < (NOW() - INTERVAL 30 MINUTE)$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
