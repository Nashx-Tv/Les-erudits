<?php
// =============================================
//  api.php — API REST sécurisée par rôles
// =============================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── Chargement des helpers de rôle ──────────────────────────────────────────
require_once 'auth_check.php';
require_once 'config.php';

$action = $_GET['action'] ?? '';

// ── Vérification : utilisateur connecté pour toutes les actions ─────────────
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié. Veuillez vous connecter.']);
    exit;
}

// ── Helper : répondre 403 si rôle insuffisant ────────────────────────────────
function apiRequireRole(string ...$roles): void {
    if (!hasAnyRole(...$roles)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Accès refusé. Rôle insuffisant.']);
        exit;
    }
}

try {
    $pdo = $conn;

    // ── GET historique — tout utilisateur connecté ───────────────────────────
    if ($action === 'historique' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $limit = max(1, min(500, intval($_GET['limit'] ?? 100)));
        $stmt = $pdo->prepare(
            "SELECT id, analog1, moyenne_jour, date_mesure, heure_mesure, horodatage
             FROM historique_energie ORDER BY horodatage DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    // ── GET stats — tout utilisateur connecté ───────────────────────────────
    } elseif ($action === 'stats' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $nb   = $pdo->query("SELECT COUNT(*) FROM historique_energie")->fetchColumn();
        $sum  = $pdo->query("SELECT SUM(analog1) FROM historique_energie")->fetchColumn();
        $moy  = $pdo->query("SELECT AVG(analog1) FROM historique_energie")->fetchColumn();
        $last = $pdo->query("SELECT horodatage FROM historique_energie ORDER BY horodatage DESC LIMIT 1")->fetchColumn();
        echo json_encode([
            'success' => true,
            'nb_mesures' => (int)$nb,
            'total_kwh'  => round((float)$sum, 2),
            'moy_kwh'    => round((float)$moy, 2),
            'derniere'   => $last ?: null,
        ]);

    // ── POST ajouter mesure — admin ou ateliers uniquement ──────────────────
    } elseif ($action === 'ajouter' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        apiRequireRole('admin', 'ateliers');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $analog1      = isset($body['analog1'])      ? (float)$body['analog1']      : null;
        $moyenne_jour = isset($body['moyenne_jour']) ? (float)$body['moyenne_jour'] : null;
        $date_mesure  = $body['date_mesure']  ?? date('Y-m-d');
        $heure_mesure = $body['heure_mesure'] ?? date('H:i:s');

        if ($analog1 === null || $moyenne_jour === null) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Champs analog1 et moyenne_jour requis']);
            exit;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO historique_energie (analog1, moyenne_jour, date_mesure, heure_mesure)
             VALUES (:a, :m, :d, :h)"
        );
        $stmt->execute([':a' => $analog1, ':m' => $moyenne_jour, ':d' => $date_mesure, ':h' => $heure_mesure]);
        echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);

    // ── GET utilisateurs — admin uniquement ─────────────────────────────────
    } elseif ($action === 'users' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        apiRequireRole('admin');
        $stmt = $pdo->query(
            "SELECT id, name, email, created_at, last_login, is_active, roles FROM users_with_roles ORDER BY id"
        );
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur BDD : ' . $e->getMessage()]);
}
