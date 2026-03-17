<?php
// =============================================
//  access_denied.php — Page 403 Accès refusé
// =============================================
if (session_status() === PHP_SESSION_NONE) session_start();
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Visiteur');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accès refusé – InfoProd</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Montserrat', sans-serif;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      display: flex; align-items: center; justify-content: center;
      height: 100vh; overflow: hidden; color: #fff;
    }

    .card {
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 20px;
      padding: 50px 44px;
      text-align: center;
      max-width: 460px;
      width: 90%;
      animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
      from { opacity:0; transform: translateY(20px); }
      to   { opacity:1; transform: translateY(0); }
    }

    .icon {
      font-size: 4rem;
      display: block;
      margin-bottom: 18px;
      filter: drop-shadow(0 0 12px rgba(255,80,80,0.5));
    }

    .code {
      font-size: 5rem;
      font-weight: 800;
      color: #ff4d4d;
      line-height: 1;
      margin-bottom: 8px;
      text-shadow: 0 0 20px rgba(255,77,77,0.4);
    }

    h1 {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 12px;
      color: #fff;
    }

    p {
      font-size: 13.5px;
      color: rgba(255,255,255,0.55);
      line-height: 1.65;
      margin-bottom: 28px;
    }

    .name {
      color: #00c6ff;
      font-weight: 600;
    }

    .btn-back {
      display: inline-block;
      padding: 11px 28px;
      background: linear-gradient(135deg, #00c6ff, #0072ff);
      color: white;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      transition: opacity 0.2s;
      margin: 6px;
    }
    .btn-back:hover { opacity: 0.85; }

    .btn-logout {
      display: inline-block;
      padding: 11px 28px;
      background: rgba(255,77,77,0.15);
      border: 1px solid rgba(255,77,77,0.3);
      color: #ff6b6b;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      transition: background 0.2s;
      margin: 6px;
    }
    .btn-logout:hover { background: rgba(255,77,77,0.28); }

    .divider {
      height: 1px;
      background: rgba(255,255,255,0.1);
      margin: 24px 0;
    }

    .brand {
      font-size: 11px;
      color: rgba(255,255,255,0.2);
      letter-spacing: 0.5px;
    }
  </style>
</head>
<body>
  <div class="card">
    <span class="icon">🔒</span>
    <div class="code">403</div>
    <h1>Accès refusé</h1>
    <p>
      Bonjour <span class="name"><?= $userName ?></span>,<br>
      vous n'avez pas les droits nécessaires pour accéder à cette page.<br>
      Contactez un administrateur si vous pensez qu'il s'agit d'une erreur.
    </p>
    <div>
      <a href="gabarits.php" class="btn-back">⬅ Retour au tableau de bord</a>
      <a href="logout.php" class="btn-logout">Déconnexion</a>
    </div>
    <div class="divider"></div>
    <div class="brand">InfoProd &mdash; LGT Joseph Gaillard &mdash; BTS CIEL IR 2026</div>
  </div>
</body>
</html>
