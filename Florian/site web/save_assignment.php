<?php
require_once 'auth_check.php';
requireLogin();

if (!hasAnyRole('admin', 'moderator')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
    exit;
}

require_once 'db_config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $body = json_decode(file_get_contents('php://input'), true);

    $screenCode    = trim($body['screen_code'] ?? '');
    $screenName    = trim($body['screen_name'] ?? '');
    $templateTitle = trim($body['template_title'] ?? '');

    if ($screenCode === '' || $screenName === '' || $templateTitle === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Champs manquants']);
        exit;
    }

    $pdo = getDB();

    $stmt = $pdo->prepare("
        INSERT INTO display_assignments (screen_code, screen_name, template_title)
        VALUES (:screen_code, :screen_name, :template_title)
        ON DUPLICATE KEY UPDATE
            screen_name = VALUES(screen_name),
            template_title = VALUES(template_title)
    ");

    $stmt->execute([
        ':screen_code'    => $screenCode,
        ':screen_name'    => $screenName,
        ':template_title' => $templateTitle
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Affectation enregistrée'
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
