<?php
// =============================================
//  login.php — Connexion utilisateur
// =============================================
session_start();

// Si déjà connecté, rediriger vers le tableau de bord
if (isset($_SESSION['user_id'])) {
    header("Location: gabarits.php");
    exit;
}

include('config.php');

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Mettre à jour last_login via la procédure stockée
            $conn->prepare("CALL update_last_login(?)")->execute([$user['id']]);

            // Enregistrer la tentative réussie
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $conn->prepare("CALL log_login_attempt(?, ?, 1)")->execute([$email, $ip]);

            // Régénérer l'ID de session (sécurité anti-fixation)
            session_regenerate_id(true);

            // ── Charger les rôles réels depuis la base de données ──────────
            $roleStmt = $conn->prepare(
                "SELECT r.name FROM roles r
                 JOIN user_roles ur ON r.id = ur.role_id
                 WHERE ur.user_id = ?"
            );
            $roleStmt->execute([$user['id']]);
            $userRoles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);

            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_roles'] = !empty($userRoles) ? $userRoles : ['user'];

            header("Location: gabarits.php");
            exit;
        } else {
            // Enregistrer la tentative échouée
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $conn->prepare("CALL log_login_attempt(?, ?, 0)")->execute([$email, $ip]);
            $error = "❌ Email ou mot de passe incorrect.";
        }
    } else {
        $error = "❌ Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion - Infoprod</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Montserrat', sans-serif;
      background: linear-gradient(135deg, #e3f2fd, #90caf9, #42a5f5);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      overflow: hidden;
    }

    .login-wrapper {
      position: relative;
      width: 380px;
      padding: 40px 35px;
      background: rgba(255,255,255,0.2);
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(31,38,135,0.37);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      text-align: center;
      animation: fadeIn 0.7s ease forwards;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    h2 {
      color: #111;
      font-weight: 600;
      margin-bottom: 30px;
      font-size: 1.8rem;
    }

    label {
      display: block;
      text-align: left;
      color: #333;
      font-weight: 500;
      margin-bottom: 8px;
    }

    input {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 10px;
      margin-bottom: 20px;
      background: rgba(255,255,255,0.8);
      font-size: 14px;
      outline: none;
      transition: all .3s ease;
    }

    input:focus {
      background: white;
      box-shadow: 0 0 6px rgba(66,165,245,0.6);
    }

    button {
      width: 100%;
      background: #42a5f5;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all .3s ease;
    }

    button:hover {
      background: #1e88e5;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(66,165,245,0.4);
    }

    p { margin-top: 20px; font-size: 14px; color: #333; }

    a { color: #1e88e5; text-decoration: none; font-weight: 600; }
    a:hover { text-decoration: underline; }

    .error {
      background: rgba(255,0,0,0.1);
      color: #b71c1c;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .brand {
      font-size: 1.2rem;
      font-weight: 600;
      color: #0d47a1;
      letter-spacing: 1px;
      margin-bottom: 15px;
    }

    .circle {
      position: absolute;
      border-radius: 50%;
      filter: blur(100px);
      opacity: 0.6;
      animation: float 6s ease-in-out infinite;
    }
    .circle1 { width:200px; height:200px; background:#42a5f5; top:-50px; right:-80px; }
    .circle2 { width:180px; height:180px; background:#90caf9; bottom:-60px; left:-70px; }

    @keyframes float {
      0%,100% { transform: translateY(0); }
      50%      { transform: translateY(15px); }
    }

    @media (max-width: 450px) {
      .login-wrapper { width: 90%; padding: 30px; }
    }
  </style>
</head>
<body>

  <div class="circle circle1"></div>
  <div class="circle circle2"></div>

  <div class="login-wrapper">
    <div style="position:absolute;top:14px;right:14px;">
      <button id="lang-toggle" onclick="toggleLang()"
        style="font-size:11px;font-weight:700;background:rgba(66,165,245,.12);color:#1e88e5;border:1px solid rgba(66,165,245,.3);border-radius:7px;padding:4px 10px;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .2s;"
        onmouseover="this.style.background='rgba(66,165,245,.25)'"
        onmouseout="this.style.background='rgba(66,165,245,.12)'">🌐 EN</button>
    </div>
    <div class="brand">Infoprod</div>
    <h2 data-i18n="title">Connexion</h2>

    <?php if (!empty($error)) : ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <label data-i18n="email-label">Email</label>
      <input type="email" name="email" placeholder="Entrez votre email" data-i18n-ph="email-ph" required>

      <label data-i18n="pwd-label">Mot de passe</label>
      <input type="password" name="password" placeholder="Entrez votre mot de passe" data-i18n-ph="pwd-ph" required>

      <button type="submit" data-i18n="btn-login">Se connecter</button>
    </form>

    <p data-i18n="no-account-html">Pas encore de compte ? <a href="inscrip.php">Créer un compte</a></p>
  </div>

<script>
const loginTranslations = {
  fr: {
    'title': 'Connexion',
    'email-label': 'Email',
    'email-ph': 'Entrez votre email',
    'pwd-label': 'Mot de passe',
    'pwd-ph': 'Entrez votre mot de passe',
    'btn-login': 'Se connecter',
    'no-account': "Pas encore de compte ?",
    'create-account': 'Créer un compte',
  },
  en: {
    'title': 'Login',
    'email-label': 'Email',
    'email-ph': 'Enter your email',
    'pwd-label': 'Password',
    'pwd-ph': 'Enter your password',
    'btn-login': 'Sign in',
    'no-account': 'No account yet?',
    'create-account': 'Create an account',
  }
};

let loginLang = 'fr';

function applyLoginLang(lang) {
  const t = loginTranslations[lang];
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (t[key] !== undefined) el.textContent = t[key];
  });
  document.querySelectorAll('[data-i18n-ph]').forEach(el => {
    const key = el.getAttribute('data-i18n-ph');
    if (t[key] !== undefined) el.placeholder = t[key];
  });
  // Rebuild the "no account" paragraph with proper link
  const p = document.querySelector('p[data-i18n="no-account-html"]');
  if (p) p.innerHTML = `${t['no-account']} <a href="inscrip.php">${t['create-account']}</a>`;
  const btn = document.getElementById('lang-toggle');
  if (btn) btn.textContent = lang === 'fr' ? '🌐 EN' : '🌐 FR';
  document.documentElement.lang = lang;
}

function toggleLang() {
  loginLang = loginLang === 'fr' ? 'en' : 'fr';
  applyLoginLang(loginLang);
}
applyLoginLang('fr');
</script>

</body>
</html>
