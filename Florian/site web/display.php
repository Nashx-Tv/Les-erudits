<?php
require_once 'db_config.php';

$screen = $_GET['screen'] ?? '';

if ($screen === '') {
    die('Écran non défini');
}

$pdo = getDB();

$stmt = $pdo->prepare("
    SELECT template_title, screen_name
    FROM display_assignments
    WHERE screen_code = :screen
    LIMIT 1
");
$stmt->execute([':screen' => $screen]);
$row = $stmt->fetch();

$templateTitle = $row['template_title'] ?? 'Aucun gabarit';
$screenName    = $row['screen_name'] ?? $screen;

$templateMap = [
    'Gabarit Administration' => 'templates/gabarit_administration.php',
    'Gabarit Vie scolaire – Lycée' => 'templates/gabarit_vie_scolaire_lycee.php',
    'Gabarit Vie scolaire – Post-bac' => 'templates/gabarit_vie_scolaire_postbac.php',
    'Gabarit Ateliers' => 'templates/gabarit_ateliers.php',
    'Gabarit Énergie' => 'templates/gabarit_energie.php',
];

if (isset($templateMap[$templateTitle]) && file_exists($templateMap[$templateTitle])) {
    include $templateMap[$templateTitle];
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="refresh" content="5">
  <title>Affichage dynamique</title>
  <style>
    body {
      margin: 0;
      background: #111;
      color: white;
      font-family: Arial, sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
    }
    .box {
      width: 90%;
      max-width: 1200px;
      padding: 40px;
      border-radius: 20px;
      background: #1c1c1c;
      box-shadow: 0 0 20px rgba(0,0,0,0.4);
    }
    h1 { font-size: 3rem; margin-bottom: 20px; }
    h2 { font-size: 2rem; color: #4da6ff; margin-bottom: 30px; }
    p  { font-size: 1.4rem; }
  </style>
</head>
<body>
  <div class="box">
    <h1><?php echo htmlspecialchars($screenName); ?></h1>
    <h2><?php echo htmlspecialchars($templateTitle); ?></h2>
    <p>Affichage dynamique en cours</p>
  </div>
</body>
</html>
