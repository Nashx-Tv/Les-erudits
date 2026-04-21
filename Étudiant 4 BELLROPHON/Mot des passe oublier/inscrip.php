<?php
session_start();
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($name) && !empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "⚠️ Cet email est déjà utilisé.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert->execute([$name, $email, $hashedPassword]);
            $success = "✅ Inscription réussie ! <a href='login.php'>Connecte-toi ici</a>";
        }
    } else {
        $error = "❌ Tous les champs sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription - Infoprod</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      background: radial-gradient(circle at top left, #1b2735, #090a0f);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      overflow: hidden;
      color: #e0e0e0;
    }

    .register-container {
      position: relative;
      width: 400px;
      padding: 40px 35px;
      background: rgba(25, 25, 25, 0.75);
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.6);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      text-align: center;
      animation: fadeIn 0.8s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h2 {
      color: #fff;
      font-weight: 600;
      margin-bottom: 25px;
    }

    label {
      display: block;
      text-align: left;
      color: #ccc;
      font-weight: 500;
      margin-bottom: 8px;
    }

    input {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 10px;
      margin-bottom: 20px;
      background: rgba(255,255,255,0.1);
      color: #fff;
      font-size: 14px;
      outline: none;
      transition: all 0.3s ease;
    }

    input::placeholder {
      color: #aaa;
    }

    input:focus {
      background: rgba(255,255,255,0.15);
      box-shadow: 0 0 5px #2196f3;
    }

    button {
      width: 100%;
      background: linear-gradient(135deg, #1e88e5, #0d47a1);
      color: white;
      border: none;
      padding: 12px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    button:hover {
      background: linear-gradient(135deg, #2196f3, #1565c0);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(33,150,243,0.4);
    }

    p {
      margin-top: 20px;
      font-size: 14px;
    }

    a {
      color: #64b5f6;
      text-decoration: none;
      font-weight: 600;
    }

    a:hover {
      text-decoration: underline;
    }

    .error, .success {
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 14px;
      text-align: center;
    }

    .error {
      background: rgba(255,0,0,0.1);
      color: #ef5350;
    }

    .success {
      background: rgba(0,255,0,0.1);
      color: #81c784;
    }

    .brand {
      font-size: 1.3rem;
      font-weight: 600;
      color: #64b5f6;
      margin-bottom: 15px;
      letter-spacing: 1px;
    }

    .circle {
      position: absolute;
      border-radius: 50%;
      filter: blur(100px);
      opacity: 0.3;
      animation: float 8s ease-in-out infinite;
    }

    .circle1 {
      width: 200px;
      height: 200px;
      background: #1e88e5;
      top: -80px;
      right: -100px;
    }

    .circle2 {
      width: 180px;
      height: 180px;
      background: #0d47a1;
      bottom: -60px;
      left: -70px;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(20px); }
    }

    @media (max-width: 450px) {
      .register-container {
        width: 90%;
        padding: 30px;
      }
    }
  </style>
</head>
<body>

  <div class="circle circle1"></div>
  <div class="circle circle2"></div>

  <div class="register-container">
    <div class="brand">Infoprod</div>
    <h2>Créer un compte</h2>

    <?php if (!empty($error)) : ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)) : ?>
      <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <label>Nom</label>
      <input type="text" name="name" placeholder="Votre nom complet" required>

      <label>Email</label>
      <input type="email" name="email" placeholder="Adresse e-mail" required>

      <label>Mot de passe</label>
      <input type="password" name="password" placeholder="Mot de passe" required>

      <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
  </div>

</body>
</html>
