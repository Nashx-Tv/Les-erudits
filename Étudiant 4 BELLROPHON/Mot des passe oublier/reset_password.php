<?php
session_start();
include('config.php');

$error      = '';
$success    = '';
$validToken = false;
$reset      = null;
$token      = trim($_GET['token'] ?? '');

// ── 1. Vérifier le token : non utilisé, non expiré ──────────────────────────
if (!empty($token)) {
    $stmt = $conn->prepare(
        "SELECT pr.*, u.email, u.name
         FROM password_resets pr
         JOIN users u ON u.id = pr.user_id
         WHERE pr.token = ?
           AND pr.used = 0
           AND pr.expires_at > NOW()"
    );
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reset) {
        $validToken = true;
    } else {
        $error = "Ce lien est invalide ou a expiré.";
    }
} else {
    $error = "Lien invalide.";
}

// ── 2. Traitement du formulaire ──────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST" && $validToken) {
    $newPassword     = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (strlen($newPassword) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?")
             ->execute([$hashed, $reset['user_id']]);

        $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")
             ->execute([$token]);

        $success    = "Mot de passe mis à jour avec succès !";
        $validToken = false;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nouveau mot de passe - Infoprod</title>
  <?php if (!empty($success)): ?>
    <!-- ✅ Redirection automatique vers login.php après 3 secondes -->
    <meta http-equiv="refresh" content="3;url=login.php">
  <?php endif; ?>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Montserrat', sans-serif;
      background: radial-gradient(circle at top left, #1b2735, #090a0f);
      display: flex; align-items: center; justify-content: center;
      height: 100vh; overflow: hidden; color: #e0e0e0;
    }
    .container {
      position: relative; width: 400px; padding: 40px 35px;
      background: rgba(25,25,25,0.75); border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.6);
      backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
      text-align: center; animation: fadeIn 0.8s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .brand { font-size: 1.3rem; font-weight: 600; color: #64b5f6; margin-bottom: 15px; letter-spacing: 1px; }
    h2 { color: #fff; font-weight: 600; margin-bottom: 25px; font-size: 1.5rem; }
    label { display: block; text-align: left; color: #ccc; font-weight: 500; margin-bottom: 8px; }
    input {
      width: 100%; padding: 12px; border: none; border-radius: 10px;
      margin-bottom: 5px; background: rgba(255,255,255,0.1);
      color: #fff; font-size: 14px; outline: none; transition: all 0.3s ease;
    }
    input::placeholder { color: #aaa; }
    input:focus { background: rgba(255,255,255,0.15); box-shadow: 0 0 5px #2196f3; }
    .strength-wrap { margin-bottom: 20px; }
    .strength-bar { height: 5px; border-radius: 5px; margin-top: 8px; background: rgba(255,255,255,0.1); }
    .strength-bar .fill { height: 100%; border-radius: 5px; width: 0%; transition: width 0.4s ease, background 0.4s ease; }
    .strength-label { text-align: right; font-size: 11px; color: #aaa; margin-top: 4px; min-height: 16px; }
    button {
      width: 100%; background: linear-gradient(135deg, #1e88e5, #0d47a1);
      color: white; border: none; padding: 12px; border-radius: 10px;
      font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;
      margin-top: 10px;
    }
    button:hover {
      background: linear-gradient(135deg, #2196f3, #1565c0);
      transform: translateY(-2px); box-shadow: 0 5px 15px rgba(33,150,243,0.4);
    }
    .error, .success {
      padding: 12px; border-radius: 10px; margin-bottom: 20px;
      font-size: 13px; line-height: 1.6;
    }
    .error   { background: rgba(255,0,0,0.1);   color: #ef5350; border: 1px solid rgba(239,83,80,0.3); }
    .success { background: rgba(0,200,100,0.1); color: #81c784; border: 1px solid rgba(129,199,132,0.3); }
    .success .icon { font-size: 1.8rem; display: block; margin-bottom: 8px; }

    /* Barre de progression de la redirection */
    .redirect-bar-wrap {
      margin-top: 16px;
      background: rgba(255,255,255,0.08);
      border-radius: 6px;
      height: 5px;
      overflow: hidden;
    }
    .redirect-bar {
      height: 100%;
      width: 100%;
      background: #64b5f6;
      border-radius: 6px;
      animation: shrink 3s linear forwards;
    }
    @keyframes shrink {
      from { width: 100%; }
      to   { width: 0%; }
    }
    .redirect-info {
      font-size: 12px;
      color: #aaa;
      margin-top: 8px;
    }
    .redirect-info span { color: #64b5f6; font-weight: 600; }

    nav { margin-top: 22px; font-size: 14px; }
    a { color: #64b5f6; text-decoration: none; font-weight: 600; }
    a:hover { text-decoration: underline; }
    .circle {
      position: absolute; border-radius: 50%; filter: blur(100px);
      opacity: 0.3; animation: float 8s ease-in-out infinite;
    }
    .circle1 { width: 200px; height: 200px; background: #1e88e5; top: -80px; right: -100px; }
    .circle2 { width: 180px; height: 180px; background: #0d47a1; bottom: -60px; left: -70px; }
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50%       { transform: translateY(20px); }
    }
    @media (max-width: 450px) { .container { width: 90%; padding: 30px; } }
  </style>
</head>
<body>
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>

  <div class="container">
    <div class="brand">Infoprod</div>
    <h2>Nouveau mot de passe</h2>

    <?php if (!empty($success)): ?>
      <div class="success">
        <span class="icon">✅</span>
        <?= htmlspecialchars($success) ?>
      </div>

      <!-- Barre de progression + compte à rebours -->
      <div class="redirect-bar-wrap">
        <div class="redirect-bar"></div>
      </div>
      <p class="redirect-info">Redirection vers la connexion dans <span id="countdown">3</span>s…</p>
      <nav><a href="login.php">→ Aller à la connexion maintenant</a></nav>

    <?php elseif (!empty($error) && !$validToken): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
      <nav><a href="forgot_password.php">← Faire une nouvelle demande</a></nav>

    <?php else: ?>
      <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <label>Nouveau mot de passe</label>
        <input type="password" name="password" id="password"
               placeholder="Minimum 8 caractères" required autofocus>
        <div class="strength-wrap">
          <div class="strength-bar"><div class="fill" id="strengthFill"></div></div>
          <div class="strength-label" id="strengthLabel"></div>
        </div>

        <label>Confirmer le mot de passe</label>
        <input type="password" name="confirm_password"
               placeholder="Répétez le mot de passe" required style="margin-bottom:20px;">

        <button type="submit">Mettre à jour le mot de passe</button>
      </form>
    <?php endif; ?>
  </div>

  <script>
    // ── Compte à rebours ─────────────────────────────────────────────────────
    const countdownEl = document.getElementById('countdown');
    if (countdownEl) {
      let seconds = 3;
      const timer = setInterval(() => {
        seconds--;
        countdownEl.textContent = seconds;
        if (seconds <= 0) {
          clearInterval(timer);
          window.location.href = 'login.php';
        }
      }, 1000);
    }

    // ── Barre de force du mot de passe ───────────────────────────────────────
    const pwInput = document.getElementById('password');
    const fill    = document.getElementById('strengthFill');
    const label   = document.getElementById('strengthLabel');

    if (pwInput) {
      pwInput.addEventListener('input', function () {
        const v = this.value;
        let score = 0;
        if (v.length >= 8)          score++;
        if (/[A-Z]/.test(v))        score++;
        if (/[0-9]/.test(v))        score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;

        const levels = [
          { w: '0%',   bg: 'transparent', text: '' },
          { w: '25%',  bg: '#ef5350',     text: 'Faible' },
          { w: '50%',  bg: '#ffa726',     text: 'Moyen' },
          { w: '75%',  bg: '#29b6f6',     text: 'Bon' },
          { w: '100%', bg: '#66bb6a',     text: 'Excellent' },
        ];

        fill.style.width      = levels[score].w;
        fill.style.background = levels[score].bg;
        label.textContent     = levels[score].text;
        label.style.color     = levels[score].bg;
      });
    }
  </script>
</body>
</html>
