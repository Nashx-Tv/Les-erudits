<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO gabarits 
        (nom, titre, contenu, image_url, couleur_fond, couleur_texte, duree_affichage, actif)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nom'],
        $_POST['titre'],
        $_POST['contenu'],
        $_POST['image_url'] ?? '',
        $_POST['couleur_fond'],
        $_POST['couleur_texte'],
        (int)$_POST['duree_affichage'],
        isset($_POST['actif']) ? 1 : 0
    ]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un gabarit</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h1>➕ Nouveau Gabarit</h1>
    <form method="POST" class="form-gabarit">
        <div class="form-group">
            <label>Nom interne</label>
            <input type="text" name="nom" required>
        </div>
        <div class="form-group">
            <label>Titre affiché</label>
            <input type="text" name="titre" required>
        </div>
        <div class="form-group">
            <label>Contenu</label>
            <textarea name="contenu" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label>URL de l'image (optionnel)</label>
            <input type="url" name="image_url">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Couleur de fond</label>
                <input type="color" name="couleur_fond" value="#1a1a2e">
            </div>
            <div class="form-group">
                <label>Couleur du texte</label>
                <input type="color" name="couleur_texte" value="#ffffff">
            </div>
            <div class="form-group">
                <label>Durée (secondes)</label>
                <input type="number" name="duree_affichage" value="10" min="3" max="300">
            </div>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="actif" checked> Gabarit actif</label>
        </div>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        <a href="index.php" class="btn">Annuler</a>
    </form>
</div>
</body>
</html>
