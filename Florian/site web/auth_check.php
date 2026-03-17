<?php
// =============================================
//  auth_check.php — Vérification des rôles
//  Inclure en haut de chaque page protégée
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirige vers login.php si non connecté
 */
function requireLogin(string $redirect = 'login.php'): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Exige un ou plusieurs rôles (OR) — redirige vers access_denied.php si refus
 */
function requireRole(string ...$roles): void {
    requireLogin();
    if (!hasAnyRole(...$roles)) {
        http_response_code(403);
        include __DIR__ . '/access_denied.php';
        exit;
    }
}

/**
 * Vérifie si l'utilisateur possède un rôle précis
 */
function hasRole(string $role): bool {
    return in_array($role, $_SESSION['user_roles'] ?? [], true);
}

/**
 * Vérifie si l'utilisateur possède au moins un des rôles listés
 */
function hasAnyRole(string ...$roles): bool {
    $userRoles = $_SESSION['user_roles'] ?? [];
    foreach ($roles as $r) {
        if (in_array($r, $userRoles, true)) return true;
    }
    return false;
}

/**
 * Raccourci : l'utilisateur est-il admin ?
 */
function isAdmin(): bool {
    return hasRole('admin');
}

/**
 * Retourne le rôle principal selon la priorité
 */
function getPrimaryRole(): string {
    $priority  = ['admin', 'vie_scolaire_lyceen', 'vie_scolaire_postbac', 'vie_scolaire', 'ateliers', 'moderator', 'user'];
    $userRoles = $_SESSION['user_roles'] ?? ['user'];
    foreach ($priority as $r) {
        if (in_array($r, $userRoles, true)) return $r;
    }
    return 'user';
}

/**
 * Libellé français du rôle
 */
function getRoleLabel(string $role): string {
    return match ($role) {
        'admin'                => 'Administrateur',
        'vie_scolaire_lyceen'  => 'Vie scolaire – Lycée',
        'vie_scolaire_postbac' => 'Vie scolaire – Post-bac',
        'vie_scolaire'         => 'Vie scolaire',
        'ateliers'             => 'Ateliers / Technique',
        'moderator'            => 'Modérateur',
        default                => 'Utilisateur',
    };
}

/**
 * Couleur badge du rôle
 */
function getRoleColor(string $role): string {
    return match ($role) {
        'admin'                => '#ff6b35',
        'vie_scolaire_lyceen'  => '#4da6ff',
        'vie_scolaire_postbac' => '#06b6d4',
        'vie_scolaire'         => '#4da6ff',
        'ateliers'             => '#f9d423',
        'moderator'            => '#a855f7',
        default                => '#6b7280',
    };
}
