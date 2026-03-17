<?php
// =============================================
//  config.php — Connexion PDO à la base MySQL
// =============================================

define('DB_HOST',    '192.168.0.11');
define('DB_NAME',    'auth_system');
define('DB_USER',    'admin');
define('DB_PASS',    'pi');
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Connexion à la base de données échouée.']));
}
