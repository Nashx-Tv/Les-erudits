-- =============================================
--  roles_update.sql
--  Mise à jour du système de rôles InfoProd
--  À exécuter UNE SEULE FOIS dans phpMyAdmin
-- =============================================

-- 1. Ajouter les nouveaux rôles (si non existants)
INSERT IGNORE INTO `roles` (`name`, `description`) VALUES
  ('vie_scolaire', 'Vie scolaire – gabarits vie scolaire uniquement, pas d accès admin'),
  ('ateliers',     'Ateliers / Technique – gabarits ateliers + saisie production énergie');

-- 2. Ajouter la colonne "role" dans users pour stocker le rôle principal (optionnel, pour perf)
--    (non obligatoire, les rôles sont lus via user_roles)

-- 3. Vérifier les rôles disponibles après insertion :
-- SELECT * FROM roles;
-- Résultat attendu :
--   id=1  user          Utilisateur standard
--   id=2  admin         Administrateur
--   id=3  moderator     Modérateur
--   id=4  vie_scolaire  Vie scolaire ...
--   id=5  ateliers      Ateliers / Technique ...

-- ─────────────────────────────────────────────────────────────
--  EXEMPLES D'AFFECTATION DE RÔLES
--  Remplacer USER_ID et ROLE_ID par les vraies valeurs
-- ─────────────────────────────────────────────────────────────

-- Promouvoir un utilisateur en admin :
-- UPDATE user_roles SET role_id = 2 WHERE user_id = USER_ID;

-- Affecter le rôle vie_scolaire à un utilisateur :
-- DELETE FROM user_roles WHERE user_id = USER_ID;
-- INSERT INTO user_roles (user_id, role_id) VALUES (USER_ID, 4);

-- Affecter le rôle ateliers à un utilisateur :
-- DELETE FROM user_roles WHERE user_id = USER_ID;
-- INSERT INTO user_roles (user_id, role_id) VALUES (USER_ID, 5);

-- ─────────────────────────────────────────────────────────────
--  RÉSUMÉ DES DROITS PAR RÔLE
-- ─────────────────────────────────────────────────────────────
--  RÔLE          | DB phpMyAdmin | Gabarits visibles         | Production (saisie) | Gestion users
--  admin         |     OUI       | Tous                      |       OUI           |     OUI
--  vie_scolaire  |     NON       | Vie scolaire uniquement   |       NON (lecture) |     NON
--  ateliers      |     NON       | Ateliers + Énergie        |       OUI           |     NON
--  moderator     |     NON       | Tous                      |       NON (lecture) |     NON
--  user          |     NON       | Tous (lecture seule)      |       NON (lecture) |     NON
-- ─────────────────────────────────────────────────────────────
