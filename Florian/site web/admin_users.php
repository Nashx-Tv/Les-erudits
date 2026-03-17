<?php
// =============================================
//  admin_users.php — Gestion des utilisateurs
//  Réservé aux administrateurs
// =============================================
require_once 'auth_check.php';
requireLogin();
requireRole('admin');

require_once 'config.php';

$msg     = '';
$msgType = '';

// ── Mise à jour du rôle ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Changer le rôle d'un utilisateur
    if ($action === 'update_role' && isset($_POST['user_id'], $_POST['role_id'])) {
        $userId = (int)$_POST['user_id'];
        $roleId = (int)$_POST['role_id'];

        // Empêcher l'admin de se rétrograder lui-même
        if ($userId === (int)$_SESSION['user_id'] && $roleId !== 2) {
            $msg = "⚠️ Vous ne pouvez pas modifier votre propre rôle admin.";
            $msgType = 'warn';
        } else {
            $conn->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$userId]);
            $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $roleId]);
            $msg = "✅ Rôle mis à jour avec succès.";
            $msgType = 'ok';
        }
    }

    // Supprimer un utilisateur
    if ($action === 'delete_user' && isset($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];
        if ($userId !== (int)$_SESSION['user_id']) {
            $conn->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$userId]);
            $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            $msg = "🗑️ Utilisateur supprimé définitivement.";
            $msgType = 'warn';
        } else {
            $msg = "⚠️ Vous ne pouvez pas supprimer votre propre compte.";
            $msgType = 'warn';
        }
    }

    // Activer / désactiver un compte
    if ($action === 'toggle_active' && isset($_POST['user_id'], $_POST['is_active'])) {
        $userId   = (int)$_POST['user_id'];
        $isActive = (int)$_POST['is_active'];
        if ($userId !== (int)$_SESSION['user_id']) {
            $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$isActive, $userId]);
            $msg = $isActive ? "✅ Compte activé." : "⛔ Compte désactivé.";
            $msgType = $isActive ? 'ok' : 'warn';
        } else {
            $msg = "⚠️ Vous ne pouvez pas désactiver votre propre compte.";
            $msgType = 'warn';
        }
    }
}

// ── Chargement des données ───────────────────────────────────
$users = $conn->query(
    "SELECT u.id, u.name, u.email, u.created_at, u.last_login, u.is_active,
            GROUP_CONCAT(r.name SEPARATOR ',') AS roles,
            MIN(ur.role_id) AS primary_role_id
     FROM users u
     LEFT JOIN user_roles ur ON u.id = ur.user_id
     LEFT JOIN roles r ON ur.role_id = r.id
     GROUP BY u.id ORDER BY u.id"
)->fetchAll();

$roles = $conn->query("SELECT * FROM roles ORDER BY id")->fetchAll();

$roleLabels = [
    'admin'                => 'Administrateur',
    'vie_scolaire_lyceen'  => 'Vie scolaire – Lycée',
    'vie_scolaire_postbac' => 'Vie scolaire – Post-bac',
    'vie_scolaire'         => 'Vie scolaire',
    'ateliers'             => 'Ateliers / Technique',
    'moderator'            => 'Modérateur',
    'user'                 => 'Utilisateur',
];
$roleColors = [
    'admin'                => '#ff6b35',
    'vie_scolaire_lyceen'  => '#4da6ff',
    'vie_scolaire_postbac' => '#06b6d4',
    'vie_scolaire'         => '#4da6ff',
    'ateliers'             => '#f9d423',
    'moderator'            => '#a855f7',
    'user'                 => '#6b7280',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>InfoProd – Gestion des utilisateurs</title>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --bg:      #f0f2f7;
      --sidebar: #111827;
      --card:    #ffffff;
      --border:  #e2e8f0;
      --accent:  #00d084;
      --text:    #111827;
      --sub:     #6b7280;
    }

    body { background: var(--bg); color: var(--text); font-family: Arial, Helvetica, sans-serif; min-height: 100vh; display: flex; }

    /* SIDEBAR */
    aside { width: 220px; background: var(--sidebar); display: flex; flex-direction: column; flex-shrink: 0; min-height: 100vh; position: sticky; top: 0; height: 100vh; }
    .brand { padding: 20px 18px 16px; border-bottom: 1px solid #1f2937; }
    .brand-name { font-size: 20px; font-weight: 800; color: #fff; }
    .brand-name span { color: var(--accent); }
    .brand-sub { font-size: 10px; color: #4b5563; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.5px; }
    nav { flex: 1; padding: 12px 8px; display: flex; flex-direction: column; gap: 2px; }
    .nav-section { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #374151; padding: 12px 10px 5px; font-weight: 600; }
    nav a { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 7px; color: #9ca3af; text-decoration: none; font-size: 13px; font-weight: 500; transition: background 0.15s, color 0.15s; }
    nav a:hover { background: #1f2937; color: #e5e7eb; }
    nav a.active { background: rgba(0,208,132,0.15); color: var(--accent); }
    nav a svg { width: 16px; height: 16px; flex-shrink: 0; }
    .sidebar-footer { padding: 14px; border-top: 1px solid #1f2937; font-size: 11px; color: #374151; line-height: 1.6; }
    .user-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; margin-top: 4px; }

    /* CONTENT */
    .content { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    header { background: #fff; border-bottom: 1px solid var(--border); padding: 0 26px; height: 58px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
    .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--sub); }
    .breadcrumb .current { color: var(--text); font-weight: 600; }
    .btn-logout { font-size: 12px; padding: 7px 14px; border-radius: 7px; border: 1px solid #e2e8f0; background: white; color: #6b7280; text-decoration: none; cursor: pointer; transition: all 0.15s; }
    .btn-logout:hover { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }

    /* PAGE */
    .page-body { flex: 1; padding: 26px; overflow-y: auto; }
    .page-title-row { margin-bottom: 24px; }
    .page-title-row h1 { font-size: 22px; font-weight: 700; }
    .page-title-row p { font-size: 13px; color: var(--sub); margin-top: 4px; }

    /* STATS ROW */
    .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 26px; }
    .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 16px 18px; }
    .stat-label { font-size: 11px; color: var(--sub); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }
    .stat-value { font-size: 26px; font-weight: 800; line-height: 1; }
    .stat-sub   { font-size: 11px; color: #9ca3af; margin-top: 4px; }

    /* MESSAGE */
    .msg { padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13.5px; font-weight: 500; }
    .msg.ok   { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .msg.warn { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }

    /* TABLE CARD */
    .table-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-bottom: 24px; }
    .table-card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .table-card-header h2 { font-size: 15px; font-weight: 700; }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { text-align: left; padding: 10px 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px; color: var(--sub); background: #f8fafc; border-bottom: 1px solid var(--border); font-weight: 600; }
    td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f8fafc; }

    .role-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; color: #fff; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    .active-yes { background: #10b981; }
    .active-no  { background: #ef4444; }

    select.role-select { padding: 6px 10px; border: 1px solid var(--border); border-radius: 7px; font-size: 12px; background: #f8fafc; color: var(--text); cursor: pointer; outline: none; }
    select.role-select:focus { border-color: var(--accent); }

    .btn-sm { padding: 6px 12px; border-radius: 6px; border: none; font-size: 12px; font-weight: 600; cursor: pointer; transition: opacity 0.15s; }
    .btn-sm:hover { opacity: 0.8; }
    .btn-save    { background: var(--accent); color: #001b12; }
    .btn-disable { background: #fee2e2; color: #991b1b; }
    .btn-enable  { background: #d1fae5; color: #065f46; }

    .me-badge { background: #dbeafe; color: #1e40af; font-size: 10px; padding: 2px 7px; border-radius: 10px; font-weight: 600; margin-left: 6px; }

    /* LEGEND */
    .legend { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 18px 20px; }
    .legend h3 { font-size: 14px; font-weight: 700; margin-bottom: 14px; }
    .legend-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
    .legend-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 8px; background: #f8fafc; border: 1px solid var(--border); }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; margin-top: 3px; }
    .legend-text strong { display: block; font-size: 12px; font-weight: 700; }
    .legend-text span { font-size: 11px; color: var(--sub); line-height: 1.4; }

    footer { background: #fff; border-top: 1px solid var(--border); text-align: center; padding: 11px; font-size: 11.5px; color: #9ca3af; flex-shrink: 0; }

    @media (max-width: 900px) {
      .stats-row { grid-template-columns: repeat(2, 1fr); }
      aside { display: none; }
      table { font-size: 12px; }
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside>
  <div class="brand">
    <div class="brand-name">Info<span>Prod</span></div>
    <div class="brand-sub">LGT Joseph Gaillard</div>
  </div>
  <nav>
    <div class="nav-section">Principal</div>
    <a href="gabarits.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Gabarits
    </a>
    <a href="production.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Production
    </a>
    <div class="nav-section">Administration</div>
    <a href="admin_users.php" class="active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Utilisateurs
    </a>
    <a href="http://192.168.0.11/phpmyadmin/index.php?route=/database/structure&db=auth_system">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/></svg>
      Base de données
    </a>
  </nav>
  <div class="sidebar-footer">
    BTS CIEL IR · Session 2026<br>
    <span style="color:#e5e7eb;font-weight:600"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span><br>
    <span class="user-badge" style="background:#ff6b35;color:#fff">Administrateur</span>
  </div>
</aside>

<!-- CONTENT -->
<div class="content">
  <header>
    <div class="breadcrumb">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Administration &nbsp;/&nbsp; <span class="current">Gestion des utilisateurs</span>
    </div>
    <a href="logout.php" class="btn-logout">Déconnexion</a>
  </header>

  <div class="page-body">
    <div class="page-title-row">
      <h1>👥 Gestion des utilisateurs</h1>
      <p>Assignez des rôles pour contrôler les accès à chaque interface</p>
    </div>

    <?php if ($msg): ?>
      <div class="msg <?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- STATS -->
    <?php
      $total   = count($users);
      $actifs  = count(array_filter($users, fn($u) => $u['is_active']));
      $admins  = count(array_filter($users, fn($u) => str_contains($u['roles'] ?? '', 'admin')));
      $others  = $total - $admins;
    ?>
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-label">Total comptes</div>
        <div class="stat-value" style="color:#00d084"><?= $total ?></div>
        <div class="stat-sub">enregistrés</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Comptes actifs</div>
        <div class="stat-value" style="color:#10b981"><?= $actifs ?></div>
        <div class="stat-sub">connectables</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Administrateurs</div>
        <div class="stat-value" style="color:#ff6b35"><?= $admins ?></div>
        <div class="stat-sub">accès complet</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Autres rôles</div>
        <div class="stat-value" style="color:#6b7280"><?= $others ?></div>
        <div class="stat-sub">accès limités</div>
      </div>
    </div>

    <!-- TABLE DES UTILISATEURS -->
    <div class="table-card">
      <div class="table-card-header">
        <h2>Liste des comptes</h2>
        <span style="font-size:12px;color:#6b7280"><?= $total ?> utilisateur(s)</span>
      </div>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle actuel</th>
            <th>Changer le rôle</th>
            <th>Statut</th>
            <th>Dernière connexion</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u):
            $isMe         = ((int)$u['id'] === (int)$_SESSION['user_id']);
            $userRoleArr  = $u['roles'] ? explode(',', $u['roles']) : ['user'];
            $primaryRole  = $userRoleArr[0];
            $badgeColor   = $roleColors[$primaryRole] ?? '#6b7280';
            $badgeLabel   = $roleLabels[$primaryRole]  ?? 'Utilisateur';
          ?>
          <tr>
            <td style="color:#9ca3af;font-size:12px">#<?= $u['id'] ?></td>
            <td>
              <strong><?= htmlspecialchars($u['name']) ?></strong>
              <?php if ($isMe): ?><span class="me-badge">Vous</span><?php endif; ?>
            </td>
            <td style="color:#6b7280;font-size:12px"><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <span class="role-badge" style="background:<?= $badgeColor ?>">
                <?= $badgeLabel ?>
              </span>
            </td>
            <td>
              <form method="post" style="display:flex;gap:6px;align-items:center">
                <input type="hidden" name="action"  value="update_role">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <select name="role_id" class="role-select" <?= $isMe ? 'disabled' : '' ?>>
                  <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>"
                      <?= ($u['primary_role_id'] == $r['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($roleLabels[$r['name']] ?? $r['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if (!$isMe): ?>
                  <button type="submit" class="btn-sm btn-save">Enregistrer</button>
                <?php endif; ?>
              </form>
            </td>
            <td>
              <span class="status-dot <?= $u['is_active'] ? 'active-yes' : 'active-no' ?>"></span>
              <?= $u['is_active'] ? 'Actif' : 'Inactif' ?>
            </td>
            <td style="font-size:12px;color:#9ca3af">
              <?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : 'Jamais' ?>
            </td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
              <?php if (!$isMe): ?>
                <form method="post">
                  <input type="hidden" name="action"    value="toggle_active">
                  <input type="hidden" name="user_id"   value="<?= $u['id'] ?>">
                  <input type="hidden" name="is_active" value="<?= $u['is_active'] ? 0 : 1 ?>">
                  <button type="submit" class="btn-sm <?= $u['is_active'] ? 'btn-disable' : 'btn-enable' ?>">
                    <?= $u['is_active'] ? '⛔ Désactiver' : '✅ Activer' ?>
                  </button>
                </form>
                <form method="post" onsubmit="return confirm('⚠️ Supprimer définitivement « <?= htmlspecialchars($u['name']) ?> » ? Cette action est irréversible.');">
                  <input type="hidden" name="action"  value="delete_user">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn-sm" style="background:#fee2e2;color:#991b1b;">
                    🗑️ Supprimer
                  </button>
                </form>
              <?php else: ?>
                <span style="font-size:11px;color:#9ca3af">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- LÉGENDE DES RÔLES -->
    <div class="legend">
      <h3>📋 Récapitulatif des droits par rôle</h3>
      <div class="legend-grid">
        <div class="legend-item">
          <div class="legend-dot" style="background:#ff6b35"></div>
          <div class="legend-text">
            <strong>Administrateur</strong>
            <span>Accès complet : gabarits, production, phpMyAdmin, gestion des utilisateurs</span>
          </div>
        </div>
        <div class="legend-item">
          <div class="legend-dot" style="background:#4da6ff"></div>
          <div class="legend-text">
            <strong>Vie scolaire</strong>
            <span>Gabarit vie scolaire uniquement, consultation production (lecture), pas d'accès BDD admin</span>
          </div>
        </div>
        <div class="legend-item">
          <div class="legend-dot" style="background:#f9d423"></div>
          <div class="legend-text">
            <strong>Ateliers / Technique</strong>
            <span>Gabarits ateliers + énergie, saisie des données de production, pas d'accès BDD</span>
          </div>
        </div>
        <div class="legend-item">
          <div class="legend-dot" style="background:#a855f7"></div>
          <div class="legend-text">
            <strong>Modérateur</strong>
            <span>Accès à tous les gabarits, lecture production, pas de BDD ni gestion users</span>
          </div>
        </div>
        <div class="legend-item">
          <div class="legend-dot" style="background:#6b7280"></div>
          <div class="legend-text">
            <strong>Utilisateur</strong>
            <span>Lecture seule : prévisualisation des gabarits, consultation production</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer>InfoProd – BTS CIEL IR &nbsp;·&nbsp; LGT Joseph Gaillard &nbsp;·&nbsp; Fort-de-France &nbsp;·&nbsp; Session 2026</footer>
</div>

</body>
</html>
