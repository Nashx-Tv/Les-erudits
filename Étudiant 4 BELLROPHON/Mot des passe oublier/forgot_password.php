<?php
session_start();
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/Exception.php';
require __DIR__ . '/PHPMailer.php';
require __DIR__ . '/SMTP.php';

define('MAIL_USER',      'bellerophon.bryhann26@gmail.com');
define('MAIL_PASS',      'fsky ebev smfq cpll');
define('MAIL_FROM_NAME', 'Infoprod');

define('BASE_URL',  'http://192.168.0.11');
define('BASE_PATH', ''); // ← mets '/monprojet' si tes fichiers sont dans un sous-dossier

$message   = '';
$sendError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $conn->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0")
             ->execute([$user['id']]);

        $token      = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
             ->execute([$user['id'], $token, $expires_at]);

        $resetLink = BASE_URL . BASE_PATH . '/reset_password.php?token=' . $token;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
            $mail->addAddress($email, $user['name']);
            $mail->Subject = "Réinitialisation de votre mot de passe - Infoprod";
            $mail->isHTML(true);
            $mail->Body = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#090a0f;margin:0;padding:30px;">
  <div style="max-width:500px;margin:auto;background:#181818;border-radius:16px;padding:40px;color:#e0e0e0;">
    <div style="text-align:center;margin-bottom:30px;">
      <span style="font-size:1.4rem;font-weight:700;color:#64b5f6;letter-spacing:1px;">Infoprod</span>
    </div>
    <h2 style="color:#fff;font-size:1.2rem;margin-bottom:15px;">Réinitialisation de mot de passe</h2>
    <p style="color:#aaa;line-height:1.7;margin-bottom:25px;">
      Bonjour <strong style="color:#fff;">' . htmlspecialchars($user['name']) . '</strong>,<br><br>
      Vous avez demandé à réinitialiser votre mot de passe.<br>
      Ce lien est valable <strong style="color:#64b5f6;">1 heure</strong>.
    </p>
    <div style="text-align:center;margin:30px 0;">
      <a href="' . $resetLink . '" style="background:linear-gradient(135deg,#1e88e5,#0d47a1);color:#fff;text-decoration:none;padding:14px 32px;border-radius:10px;font-weight:600;font-size:15px;display:inline-block;">
        Réinitialiser mon mot de passe
      </a>
    </div>
    <p style="color:#555;font-size:12px;line-height:1.6;border-top:1px solid rgba(255,255,255,0.08);padding-top:20px;margin-top:25px;">
      Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
      <a href="' . $resetLink . '" style="color:#64b5f6;word-break:break-all;">' . $resetLink . '</a><br><br>
      Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.
    </p>
  </div>
</body>
</html>';
            $mail->AltBody = "Bonjour " . $user['name'] . ",\r\n\r\n"
                           . "Réinitialisez votre mot de passe (valable 1h) :\r\n"
                           . $resetLink . "\r\n\r\n— L'équipe Infoprod";

            $mail->send();

        } catch (Exception $e) {
            // Erreur loguée côté serveur uniquement, pas affichée à l'utilisateur
            error_log("[forgot_password] Erreur envoi : " . $mail->ErrorInfo);
        }
    }

    // Même message dans tous les cas (sécurité anti-énumération)
    $message = "Si cet email est associé à un compte actif, vous recevrez un lien de réinitialisation dans quelques minutes.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mot de passe oublié - Infoprod</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Montserrat', sans-serif;
      background: radial-gradient(circle at top left, #1b2735, #090a0f);
      display: flex; align-items: center; justify-content: center;
      min-height: 100vh; padding: 20px; color: #e0e0e0;
    }
    .container {
      position: relative; width: 100%; max-width: 420px; padding: 40px 35px;
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
    h2 { color: #fff; font-weight: 600; margin-bottom: 10px; font-size: 1.5rem; }
    .subtitle { color: #aaa; font-size: 13px; margin-bottom: 25px; line-height: 1.6; }
    label { display: block; text-align: left; color: #ccc; font-weight: 500; margin-bottom: 8px; }
    input {
      width: 100%; padding: 12px; border: none; border-radius: 10px;
      margin-bottom: 20px; background: rgba(255,255,255,0.1);
      color: #fff; font-size: 14px; outline: none; transition: all 0.3s ease;
    }
    input::placeholder { color: #aaa; }
    input:focus { background: rgba(255,255,255,0.15); box-shadow: 0 0 5px #2196f3; }
    button {
      width: 100%; background: linear-gradient(135deg, #1e88e5, #0d47a1);
      color: white; border: none; padding: 12px; border-radius: 10px;
      font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;
    }
    button:hover {
      background: linear-gradient(135deg, #2196f3, #1565c0);
      transform: translateY(-2px); box-shadow: 0 5px 15px rgba(33,150,243,0.4);
    }
    .success-box {
      background: rgba(0,200,100,0.1); color: #81c784;
      border: 1px solid rgba(129,199,132,0.3);
      padding: 14px; border-radius: 10px; margin-bottom: 20px;
      font-size: 13px; line-height: 1.6;
    }
    .success-box .icon { font-size: 1.8rem; display: block; margin-bottom: 8px; }
    nav { margin-top: 22px; font-size: 14px; }
    a { color: #64b5f6; text-decoration: none; font-weight: 600; }
    a:hover { text-decoration: underline; }
    .circle {
      position: absolute; border-radius: 50%; filter: blur(100px);
      opacity: 0.3; animation: float 8s ease-in-out infinite; pointer-events: none;
    }
    .circle1 { width: 200px; height: 200px; background: #1e88e5; top: -80px; right: -100px; }
    .circle2 { width: 180px; height: 180px; background: #0d47a1; bottom: -60px; left: -70px; }
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50%       { transform: translateY(20px); }
    }
  </style>
</head>
<body>
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>

  <div class="container">
    <div class="brand">Infoprod</div>
    <h2>Mot de passe oublié</h2>

    <?php if (!empty($message)): ?>
      <div class="success-box">
        <span class="icon">✉️</span>
        <?= htmlspecialchars($message) ?>
      </div>
      <nav><a href="login.php">← Retour à la connexion</a></nav>

    <?php else: ?>
      <p class="subtitle">Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
      <form method="post" action="">
        <label>Email</label>
        <input type="email" name="email" placeholder="Votre adresse e-mail" required autofocus>
        <button type="submit">Envoyer le lien</button>
      </form>
      <nav><a href="login.php">← Retour à la connexion</a></nav>
    <?php endif; ?>
  </div>
</body>
</html>
