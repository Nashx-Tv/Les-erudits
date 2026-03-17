-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+deb13u1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 16 mars 2026 à 12:10
-- Version du serveur : 11.8.3-MariaDB-0+deb13u1 from Debian
-- Version de PHP : 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `auth_system`
--

DELIMITER $$
--
-- Procédures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `create_user` (IN `p_name` VARCHAR(100), IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255))   BEGIN
    INSERT INTO users (name, email, password) 
    VALUES (p_name, p_email, p_password);
    
    -- Assigner automatiquement le rôle "user"
    INSERT INTO user_roles (user_id, role_id) 
    VALUES (LAST_INSERT_ID(), 1);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `log_login_attempt` (IN `p_email` VARCHAR(255), IN `p_ip_address` VARCHAR(45), IN `p_success` TINYINT(1))   BEGIN
    INSERT INTO login_attempts (email, ip_address, success) 
    VALUES (p_email, p_ip_address, p_success);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_last_login` (IN `p_user_id` INT)   BEGIN
    UPDATE users 
    SET last_login = CURRENT_TIMESTAMP 
    WHERE id = p_user_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `historique_energie`
--

CREATE TABLE `historique_energie` (
  `id` int(11) NOT NULL,
  `analog1` float NOT NULL COMMENT 'Valeur éolienne lue depuis IPX800 (kWh)',
  `moyenne_jour` float NOT NULL COMMENT 'Moyenne journalière calculée au moment de l enregistrement',
  `date_mesure` date NOT NULL COMMENT 'Date de la mesure (YYYY-MM-DD)',
  `heure_mesure` time NOT NULL COMMENT 'Heure de la mesure (HH:MM:SS)',
  `horodatage` datetime DEFAULT current_timestamp() COMMENT 'Inséré automatiquement par le serveur'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `success` tinyint(1) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `attempted_at`) VALUES
(1, 'bryhann811@gmail.com', '192.168.1.78', 0, '2026-03-09 20:46:52'),
(2, 'bryhann811@gmail.com', '192.168.1.78', 0, '2026-03-09 20:48:40'),
(3, 'bryhann811@gmail.com', '192.168.1.78', 1, '2026-03-09 20:49:45'),
(4, 'bryhann811@gmail.com', '192.168.1.78', 1, '2026-03-10 12:13:12'),
(5, 'bryhann811@gmail.com', '192.168.1.78', 1, '2026-03-10 12:13:41'),
(6, 'bryhann811@gmail.com', '192.168.1.78', 1, '2026-03-10 12:17:00'),
(7, 'bryhann811@gmail.com', '192.168.1.78', 0, '2026-03-10 16:02:11'),
(8, 'bryhann811@gmail.com', '192.168.1.78', 1, '2026-03-10 18:29:47'),
(9, 'bellerophon.bryhann26@gmail.com', '192.168.1.3', 0, '2026-03-16 12:07:29'),
(10, 'bellerophon.bryhann26@gmail.com', '192.168.1.3', 0, '2026-03-16 12:07:51');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'user', 'Utilisateur standard'),
(2, 'admin', 'Administrateur'),
(3, 'moderator', 'Modérateur');

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(7, 'juzh', 'zcb@azsas.com', '$2y$12$AS.LZYF6z/eeG6U8l3NNK.HAsiWt5Nu0ram8Cq.WnVdJBeyyqVd0S', '2026-03-09 20:39:00', '2026-03-09 20:39:00', NULL, 1),
(9, 'nion', 'bryhann811@gmail.com', '$2y$12$magZJhiR30lF2gTzNOShceoZ6Rg8c64Efsm1.iyVcpR2AIKcswY2W', '2026-03-09 20:49:19', '2026-03-10 18:29:47', '2026-03-10 18:29:47', 1);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `users_with_roles`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `users_with_roles` (
`id` int(11)
,`name` varchar(100)
,`email` varchar(255)
,`created_at` timestamp
,`last_login` timestamp
,`is_active` tinyint(1)
,`roles` mediumtext
);

-- --------------------------------------------------------

--
-- Structure de la table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `assigned_at`) VALUES
(3, 7, 1, '2026-03-09 20:39:00'),
(5, 9, 1, '2026-03-09 20:49:19');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `historique_energie`
--
ALTER TABLE `historique_energie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_attempted_at` (`attempted_at`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_token` (`session_token`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Index pour la table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_role_unique` (`user_id`,`role_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `historique_energie`
--
ALTER TABLE `historique_energie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------

--
-- Structure de la vue `users_with_roles`
--
DROP TABLE IF EXISTS `users_with_roles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `users_with_roles`  AS SELECT `u`.`id` AS `id`, `u`.`name` AS `name`, `u`.`email` AS `email`, `u`.`created_at` AS `created_at`, `u`.`last_login` AS `last_login`, `u`.`is_active` AS `is_active`, group_concat(`r`.`name` separator ', ') AS `roles` FROM ((`users` `u` left join `user_roles` `ur` on(`u`.`id` = `ur`.`user_id`)) left join `roles` `r` on(`ur`.`role_id` = `r`.`id`)) GROUP BY `u`.`id` ;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
