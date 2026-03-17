<?php
// =============================================
//  api_analog.php — API pour l'éolienne IPX800
//  Actions : enregistrer | liste | stats_jour
// =============================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Répondre immédiatement aux requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/config.php';   // fournit $conn (PDO)

// ── Lecture de l'action ──────────────────────
$action = $_GET['action'] ?? '';

// =============================================
//  ACTION : enregistrer
//  Appelée en POST avec JSON { analog1, moyenne }
//  Peut aussi être appelée directement depuis
//  le serveur (GET ?action=enregistrer) pour
//  déclencher une lecture live de l'IPX800.
// =============================================
if ($action === 'enregistrer') {

    // --- Lire les données ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Appel depuis le dashboard JS ou ipx_auto.py
        $body    = file_get_contents('php://input');
        $payload = json_decode($body, true);

        if (!isset($payload['analog1'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champ analog1 manquant dans le corps JSON.']);
            exit;
        }

        $analog1 = floatval($payload['analog1']);
        $moyenne = isset($payload['moyenne']) ? floatval($payload['moyenne']) : $analog1;

    } else {
        // Appel GET direct : lire l'IPX800 en live depuis le serveur PHP
        $IPX_URL = 'http://192.168.0.131/status.xml';

        $ctx = stream_context_create([
            'http' => ['timeout' => 5, 'ignore_errors' => true]
        ]);
        $xml_raw = @file_get_contents($IPX_URL, false, $ctx);

        if ($xml_raw === false) {
            http_response_code(502);
            echo json_encode(['error' => 'Impossible de joindre l\'IPX800 a ' . $IPX_URL]);
            exit;
        }

        $xml = @simplexml_load_string($xml_raw);
        if (!$xml || !isset($xml->analog1)) {
            http_response_code(502);
            echo json_encode(['error' => 'Reponse XML invalide depuis l\'IPX800.']);
            exit;
        }

        $analog1 = round((float)$xml->analog1, 3);

        // Calcul de la moyenne journalière via la BDD
        $stmtMoy = $conn->prepare("
            SELECT AVG(analog1) AS moy
            FROM   historique_energie
            WHERE  date_mesure = CURDATE()
        ");
        $stmtMoy->execute();
        $rowMoy  = $stmtMoy->fetch();
        $moyenne = $rowMoy['moy'] !== null
                 ? round((float)$rowMoy['moy'], 3)
                 : $analog1;
    }

    // --- Insérer en base ---
    $now  = new DateTime();
    $date = $now->format('Y-m-d');
    $time = $now->format('H:i:s');

    $stmt = $conn->prepare("
        INSERT INTO historique_energie
            (analog1, moyenne_jour, date_mesure, heure_mesure)
        VALUES
            (:analog1, :moyenne, :date, :heure)
    ");
    $stmt->execute([
        ':analog1' => $analog1,
        ':moyenne' => $moyenne,
        ':date'    => $date,
        ':heure'   => $time,
    ]);

    $newId = $conn->lastInsertId();

    echo json_encode([
        'succes'   => true,
        'id'       => (int)$newId,
        'analog1'  => $analog1,
        'moyenne'  => $moyenne,
        'date'     => $date,
        'heure'    => $time,
        'message'  => 'Enregistrement reussi.'
    ]);
    exit;
}

// =============================================
//  ACTION : liste
//  Retourne les N derniers enregistrements
//  GET ?action=liste&limite=20
// =============================================
if ($action === 'liste') {
    $limite = min((int)($_GET['limite'] ?? 20), 200); // plafond a 200
    if ($limite < 1) $limite = 20;

    $stmt = $conn->prepare("
        SELECT   id,
                 analog1,
                 moyenne_jour AS moyenne,
                 date_mesure,
                 heure_mesure
        FROM     historique_energie
        ORDER BY id DESC
        LIMIT    :limite
    ");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll();
    echo json_encode($rows);
    exit;
}

// =============================================
//  ACTION : stats_jour
//  Resume du jour courant (min, max, moyenne)
//  GET ?action=stats_jour
//  GET ?action=stats_jour&date=2026-03-17
// =============================================
if ($action === 'stats_jour') {
    $date = $_GET['date'] ?? date('Y-m-d');

    $stmt = $conn->prepare("
        SELECT
            COUNT(*)          AS nb_releves,
            MIN(analog1)      AS min_kwh,
            MAX(analog1)      AS max_kwh,
            AVG(analog1)      AS moy_kwh,
            SUM(analog1)      AS total_kwh,
            MIN(heure_mesure) AS premiere_mesure,
            MAX(heure_mesure) AS derniere_mesure
        FROM historique_energie
        WHERE date_mesure = :date
    ");
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch();

    echo json_encode([
        'date'            => $date,
        'nb_releves'      => (int)$row['nb_releves'],
        'min_kwh'         => $row['min_kwh']  !== null ? round((float)$row['min_kwh'],  3) : null,
        'max_kwh'         => $row['max_kwh']  !== null ? round((float)$row['max_kwh'],  3) : null,
        'moy_kwh'         => $row['moy_kwh']  !== null ? round((float)$row['moy_kwh'],  3) : null,
        'total_kwh'       => $row['total_kwh'] !== null ? round((float)$row['total_kwh'], 3) : null,
        'premiere_mesure' => $row['premiere_mesure'],
        'derniere_mesure' => $row['derniere_mesure'],
    ]);
    exit;
}

// ── Action inconnue ──────────────────────────
http_response_code(400);
echo json_encode([
    'error'   => 'Action inconnue ou manquante.',
    'actions' => ['enregistrer', 'liste', 'stats_jour']
]);
