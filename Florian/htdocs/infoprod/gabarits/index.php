<?php
require_once '../config/db.php';
$stmt = $pdo->query("SELECT * FROM gabarits ORDER BY date_modification DESC");
$gabarits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>InfoProd - Gestion des Gabarits</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h1>🖥️ Gestion des Gabarits</h1>
    <a href="create.php" class="btn btn-primary">+ Nouveau gabarit</a>
    <a href="view.php" class="btn btn-display">▶ Mode Affichage</a>

    <table class="table">
        <thead>
            <tr>
                <th>Aperçu</th>
                <th>Nom</th>
                <th>Titre</th>
                <th>Durée</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($gabarits as $g): ?>
            <tr>
                <td>
                    <div class="mini-preview"
                         style="background:<?= htmlspecialchars($g['couleur_fond']) ?>;
                                color:<?= htmlspecialchars($g['couleur_texte']) ?>">
                        <?= htmlspecialchars($g['titre']) ?>
                    </div>
                </td>
                <td><?= htmlspecialchars($g['nom']) ?></td>
                <td><?= htmlspecialchars($g['titre']) ?></td>
                <td><?= (int)$g['duree_affichage'] ?>s</td>
                <td>
                    <span class="badge <?= $g['actif'] ? 'badge-actif' : 'badge-inactif' ?>">
                        <?= $g['actif'] ? 'Actif' : 'Inactif' ?>
                    </span>
                </td>
                <td>
                    <a href="edit.php?id=<?= $g['id'] ?>" class="btn btn-sm">✏️ Modifier</a>
                    <a href="delete.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Supprimer ce gabarit ?')">🗑️ Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
