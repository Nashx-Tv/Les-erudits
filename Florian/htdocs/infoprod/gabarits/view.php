<?php
require_once '../config/db.php';
$stmt = $pdo->query("SELECT * FROM gabarits WHERE actif = 1 ORDER BY id ASC");
$gabarits = $stmt->fetchAll();
$gabarits_json = json_encode($gabarits);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>InfoProd - Affichage</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; overflow: hidden; }
        #panneau {
            width: 100vw; height: 100vh;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            text-align: center;
            transition: background 0.8s ease;
            padding: 40px;
        }
        #panneau h1 { font-size: 4rem; margin-bottom: 30px; }
        #panneau p  { font-size: 2rem; line-height: 1.6; white-space: pre-line; }
        #panneau img { max-height: 40vh; margin-bottom: 20px; border-radius: 12px; }
        #barre-progression {
            position: fixed; bottom: 0; left: 0; height: 6px;
            background: rgba(255,255,255,0.6);
            transition: width linear;
        }
        #indicateur {
            position: fixed; top: 15px; right: 20px;
            color: rgba(255,255,255,0.5); font-size: 0.9rem;
        }
        #btn-retour {
            position: fixed; top: 15px; left: 20px;
            background: rgba(0,0,0,0.3); color: white;
            border: none; padding: 8px 15px; cursor: pointer;
            border-radius: 6px; font-size: 0.9rem; text-decoration: none;
        }
    </style>
</head>
<body>
<div id="panneau">
    <img id="img" src="" alt="" style="display:none">
    <h1 id="titre"></h1>
    <p id="contenu"></p>
</div>
<div id="barre-progression"></div>
<span id="indicateur"></span>
<a id="btn-retour" href="index.php">⬅ Gestion</a>

<script>
const gabarits = <?= $gabarits_json ?>;
let index = 0;

function afficher(i) {
    const g = gabarits[i];
    const panneau = document.getElementById('panneau');
    const img = document.getElementById('img');

    panneau.style.backgroundColor = g.couleur_fond;
    panneau.style.color = g.couleur_texte;
    document.getElementById('titre').textContent = g.titre;
    document.getElementById('contenu').textContent = g.contenu;
    document.getElementById('indicateur').textContent = (i + 1) + ' / ' + gabarits.length;

    if (g.image_url) {
        img.src = g.image_url;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }

    const barre = document.getElementById('barre-progression');
    barre.style.transition = 'none';
    barre.style.width = '0%';
    const duree = g.duree_affichage * 1000;
    setTimeout(() => {
        barre.style.transition = `width ${duree}ms linear`;
        barre.style.width = '100%';
    }, 50);

    setTimeout(() => {
        index = (index + 1) % gabarits.length;
        afficher(index);
    }, duree);
}

if (gabarits.length > 0) {
    afficher(0);
} else {
    document.getElementById('panneau').innerHTML =
        '<h1 style="color:white">Aucun gabarit actif</h1>';
    document.getElementById('panneau').style.background = '#333';
}
</script>
</body>
</html>
