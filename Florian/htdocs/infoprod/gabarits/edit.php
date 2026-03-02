<?php
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE gabarits SET 
        nom = ?, titre = ?, contenu = ?, image_url = ?, 
        couleur_fond = ?, couleur_texte = ?, duree_affichage = ?, actif = ?
        WHERE id = ?");
    $stmt->execute([
        $_POST['nom'],
        $_POST['titre'],
        $_POST['contenu'],
        $_POST['image_url'] ?? '',
        $_POST['couleur_fond'],
        $_POST['couleur_texte'],
        (int)$_POST['duree_affichage'],
        isset($_POST['actif']) ? 1 : 0,
        $id
    ]);
    header('Location: index.php');
    exit;
} else {
    $stmt = $pdo->prepare("SELECT * FROM gabarits WHERE id = ?");
    $stmt->execute([$id]);
    $g = $stmt->fetch();
    if (!$g) {
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un gabarit</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h1>✏️ Modifier le gabarit</h1>
    <form method="POST" class="form-gabarit">
        <div class="form-group">
            <label>Nom interne</label>
            <input type="text" name="nom" required value="<?= htmlspecialchars($g['nom']) ?>">
        </div>
        <div class="form-group">
            <label>Titre affiché</label>
            <input type="text" name="titre" required value="<?= htmlspecialchars($g['titre']) ?>">
        </div>
        <div class="form-group">
            <label>Contenu</label>
            <textarea name="contenu" rows="5" required><?= htmlspecialchars($g['contenu']) ?></textarea>
        </div>
        <div class="form-group">
            <label>URL de l'image (optionnel)</label>
            <input type="url" name="image_url" value="<?= htmlspecialchars($g['image_url']) ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Couleur de fond</label>
                <input type="color" name="couleur_fond" value="<?= htmlspecialchars($g['couleur_fond']) ?>">
            </div>
            <div class="form-group">
                <label>Couleur du texte</label>
                <input type="color" name="couleur_texte" value="<?= htmlspecialchars($g['couleur_texte']) ?>">
            </div>
            <div class="form-group">
                <label>Durée (secondes)</label>
                <input type="number" name="duree_affichage" value="<?= (int)$g['duree_affichage'] ?>" min="3" max="300">
            </div>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="actif" <?= $g['actif'] ? 'checked' : '' ?>> Gabarit actif</label>
        </div>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        <a href="index.php" class="btn">Annuler</a>
    </form>
</div>
</body>
</html>
