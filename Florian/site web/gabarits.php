<?php
// =============================================
//  gabarits.php — Gestion des gabarits (rôles)
// =============================================
require_once 'auth_check.php';
requireLogin();

$primaryRole   = getPrimaryRole();
$roleLabel     = getRoleLabel($primaryRole);
$roleColor     = getRoleColor($primaryRole);
$userRoles     = $_SESSION['user_roles'] ?? ['user'];
$userRolesJson = json_encode($userRoles);
$userName      = htmlspecialchars($_SESSION['user_name'] ?? '');

$canAdd       = hasAnyRole('admin');
$canEdit      = hasAnyRole('admin', 'moderator', 'vie_scolaire_lyceen', 'vie_scolaire_postbac', 'vie_scolaire');
$canDelete    = hasAnyRole('admin');
$canAssign    = hasAnyRole('admin', 'moderator');
$canViewDB    = hasAnyRole('admin');
$canViewUsers = hasAnyRole('admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gabarits – InfoProd</title>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Source+Sans+3:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; }

  :root {
    --sb-w:     220px;
    --sb-min:   56px;
    --transition: .3s ease;
    --bg:       #0b1220;
  }

  body { background: var(--bg); color: #fff; min-height: 100vh; display: flex; }

  /* ══════════════════════════════════════
     SIDEBAR
  ══════════════════════════════════════ */
  .sidebar {
    position: fixed;
    top: 0; left: 0;
    width: var(--sb-w);
    height: 100vh;
    background: #0d1627;
    border-right: 1px solid #1a2c44;
    display: flex;
    flex-direction: column;
    transition: width var(--transition);
    z-index: 200;
    overflow: hidden;
  }
  .sidebar.collapsed { width: var(--sb-min); }

  .sb-header {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 12px;
    border-bottom: 1px solid #1a2c44;
    min-height: 54px; flex-shrink: 0;
  }
  .sb-toggle {
    background: none; border: none;
    color: rgba(255,255,255,0.5);
    cursor: pointer; border-radius: 7px;
    width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: background .15s, color .15s;
  }
  .sb-toggle:hover { background: #172035; color: #fff; }
  .sb-brand {
    font-size: 17px; font-weight: 800; color: #fff;
    white-space: nowrap;
    transition: opacity var(--transition), width var(--transition);
    overflow: hidden;
  }
  .sb-brand span { color: #00d084; }
  .sidebar.collapsed .sb-brand { opacity: 0; width: 0; }

  .sb-nav {
    flex: 1; padding: 10px 8px;
    display: flex; flex-direction: column; gap: 3px;
    overflow: hidden;
  }
  .sb-link {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 11px; border-radius: 9px;
    color: #8899bb; text-decoration: none;
    font-size: 13px; font-weight: 500;
    white-space: nowrap;
    transition: background .15s, color .15s;
  }
  .sb-link:hover  { background: #172035; color: #fff; }
  .sb-link.active { background: rgba(0,208,132,0.15); color: #00d084; }
  .sb-link svg    { flex-shrink: 0; width: 18px; height: 18px; }
  .sb-lbl {
    overflow: hidden;
    transition: opacity var(--transition), max-width var(--transition);
    max-width: 160px;
  }
  .sidebar.collapsed .sb-lbl { opacity: 0; max-width: 0; }

  .sb-sep { height: 1px; background: #1a2c44; margin: 6px 8px; flex-shrink: 0; }

  .sb-footer {
    padding: 10px 8px;
    border-top: 1px solid #1a2c44;
    display: flex; flex-direction: column; gap: 4px;
    flex-shrink: 0; overflow: hidden;
  }
  .sb-user {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 11px;
  }
  .sb-role-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
  .sb-role-label {
    font-size: 11px; font-weight: 700; color: #fff;
    white-space: nowrap;
    transition: opacity var(--transition), max-width var(--transition);
    max-width: 160px; overflow: hidden;
  }
  .sb-username {
    font-size: 11px; color: #8899bb;
    white-space: nowrap;
    transition: opacity var(--transition), max-width var(--transition);
    max-width: 160px; overflow: hidden;
  }
  .sidebar.collapsed .sb-role-label,
  .sidebar.collapsed .sb-username { opacity: 0; max-width: 0; }
  .sb-logout {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 11px; border-radius: 9px;
    color: rgba(255,100,100,0.7); text-decoration: none;
    font-size: 13px; font-weight: 500; white-space: nowrap;
    transition: background .15s, color .15s;
  }
  .sb-logout:hover { background: rgba(255,80,80,0.12); color: #ff6b6b; }
  .sb-logout svg { flex-shrink: 0; width: 18px; height: 18px; }

  /* ══════════════════════════════════════
     MAIN WRAPPER
  ══════════════════════════════════════ */
  .main-wrapper {
    margin-left: var(--sb-w);
    flex: 1;
    transition: margin-left var(--transition);
    min-width: 0;
    display: flex; flex-direction: column;
  }
  .main-wrapper.sb-collapsed { margin-left: var(--sb-min); }

  /* Topbar */
  .topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 24px; height: 54px;
    background: #0d1627; border-bottom: 1px solid #1a2c44;
    flex-wrap: wrap; gap: 8px; flex-shrink: 0;
  }
  .topbar-title { font-size: 16px; font-weight: 700; }
  .btn-add {
    background: #00d084; color: #001b12;
    border: none; padding: 9px 16px; border-radius: 9px;
    font-weight: 700; cursor: pointer; font-size: 13px;
    transition: background .15s;
  }
  .btn-add:hover { background: #00a86b; }

  /* Banner de rôle */
  .role-banner {
    margin: 16px 24px 0;
    padding: 10px 16px; border-radius: 10px;
    font-size: 13px;
    background: rgba(77,166,255,0.08);
    border: 1px solid rgba(77,166,255,0.2);
    color: #9fd0ff;
    display: flex; align-items: center; gap: 10px;
  }

  /* ── Grille de cartes ── */
  .cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
    gap: 20px;
    padding: 20px 24px 30px;
  }
  .card {
    background: #101a2d; border: 1px solid #24324f;
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    transition: transform .2s, box-shadow .2s;
  }
  .card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.35); }

  /* Mini preview */
  .preview { padding: 14px; background: #0d1627; border-bottom: 1px solid #24324f; }
  .mini-screen { background: #efefef; border: 2px solid #222; box-shadow: 4px 4px 0 rgba(0,0,0,0.25); padding: 6px; }
  .mini-topbar { display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: center; font-size: .55rem; font-weight: 600; color: #111; margin-bottom: 8px; }
  .mini-layout { display: grid; grid-template-columns: 90px 1fr 65px; gap: 6px; min-height: 120px; align-items: stretch; }
  .mini-panel { border-radius: 4px; display: flex; align-items: center; justify-content: center; text-align: center; padding: 6px; font-size: .6rem; font-style: italic; font-weight: bold; white-space: pre-line; }
  .mini-zone1 { background: #0818ff; color: white; min-height: 60px; align-self: start; }
  .mini-zone2 { background: transparent; color: #111; }
  .mini-zone3 { background: #2cff20; color: #000; font-style: normal; line-height: 1.05; min-height: 95px; padding: 4px; display: flex; flex-direction: column; justify-content: center; gap: 4px; }
  .mini-zone3-title { font-size: .5rem; font-weight: 800; text-align: center; }
  .mini-meter { height: 7px; background: rgba(0,0,0,0.15); border-radius: 999px; overflow: hidden; }
  .mini-meter-fill { height: 100%; width: 45%; background: #0d4bff; border-radius: 999px; }
  .mini-footer { margin-top: 8px; display: grid; grid-template-columns: 90px 1fr 65px; gap: 6px; align-items: center; }
  .mini-location { background: #dcdcdc; color: #000; padding: 5px 6px; font-size: .55rem; border-radius: 4px; }
  .mini-alert { background: #ff1e1e; color: #000; font-size: .55rem; font-weight: 700; padding: 5px 6px; border-radius: 4px; text-align: center; white-space: nowrap; overflow: hidden; }
  .mini-status { background: #ff1e1e; min-height: 24px; border-radius: 4px; }

  .card-content { padding: 16px; }
  .card-content h2 { font-size: 1.2rem; margin-bottom: 8px; }
  .card-content p { color: #b8c4d9; margin-bottom: 6px; }
  .status-active  { color: #00d084; font-weight: bold; }
  .status-offline { color: #ffb347; font-weight: bold; }
  .assigned-display { color: #9fd0ff; font-weight: bold; }
  .actions { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; }
  .btn { padding: 10px 14px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; font-size: 12px; transition: opacity .15s; }
  .btn:hover      { opacity: .85; }
  .btn-edit       { background: #00d084; color: #001b12; }
  .btn-preview    { background: transparent; color: #cbd5e1; border: 1px solid #3b4d72; }
  .btn-assign     { background: #4da6ff; color: white; }
  .btn-delete     { background: #ff4d4d; color: white; }

  /* ── Modals ── */
  .modal { position: fixed; inset: 0; background: rgba(0,0,0,0.82); display: none; justify-content: center; align-items: center; padding: 20px; z-index: 999; }
  .modal.active { display: flex; }
  .modal-box { width: 100%; max-width: 1250px; max-height: 95vh; overflow: auto; background: #1a2233; border-radius: 14px; padding: 18px; }
  .modal-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; gap: 12px; flex-wrap: wrap; }
  .modal-top h2 { font-size: 1.4rem; }
  .close-btn { background: #ff4d4d; color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: bold; }

  /* Full preview */
  .screen { width: 100%; background: #efefef; border: 3px solid #222; box-shadow: 10px 10px 0 rgba(0,0,0,0.35); padding: 10px; color: #111; }
  .topbar-screen { display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: start; font-size: 1.8rem; font-weight: 600; color: #111; margin-bottom: 14px; }
  .topbar-screen > div { min-width: 0; white-space: normal; overflow-wrap: anywhere; word-break: break-word; }
  .layout { display: grid; grid-template-columns: 260px minmax(0,1fr) 220px; gap: 18px; min-height: 420px; align-items: stretch; }
  .panel { border-radius: 6px; display: flex; align-items: center; justify-content: center; text-align: center; padding: 16px; font-size: 1.3rem; font-style: italic; font-weight: bold; min-width: 0; overflow: hidden; }
  .zone1 { background: #0818ff; color: #fff; min-height: 170px; align-self: start; }
  .zone2 { background: transparent; color: #111; font-size: 1.5rem; min-width: 0; overflow: hidden; word-break: break-word; }
  .zone3 { background: #16A34A; color: #000; min-height: 300px; padding: 0; min-width: 0; overflow: hidden; border-radius: 6px; }
  .footer-screen { margin-top: 18px; display: grid; grid-template-columns: 260px 1fr 220px; gap: 10px; align-items: center; }
  .location { background: #dcdcdc; color: #000; padding: 10px 14px; font-size: 1.1rem; border-radius: 4px; }
  .alert { background: #ff1e1e; color: #000; font-size: 1.05rem; font-weight: 700; padding: 10px 16px; border-radius: 4px; text-align: center; overflow: hidden; white-space: nowrap; position: relative; }
  .alert span { display: inline-block; padding-left: 100%; animation: defilement 14s linear infinite; }
  .status { background: #ff1e1e; height: 100%; min-height: 42px; border-radius: 4px; }

  /* Edit form */
  .edit-form { display: grid; gap: 12px; margin-bottom: 18px; }
  .edit-form label { font-weight: bold; color: #fff; margin-bottom: 4px; display: block; }
  .edit-form input, .edit-form textarea, .edit-form select { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #3b4d72; background: #0f1728; color: white; font-size: 1rem; }
  .edit-form textarea { min-height: 90px; resize: vertical; }
  .save-btn { background: #00d084; color: #001b12; border: none; padding: 12px 18px; border-radius: 8px; font-weight: bold; cursor: pointer; }

  /* Widget énergie */
  .widget-zone3 { width:100%; height:100%; min-height:420px; background:#16A34A; color:white; padding:10px; display:flex; flex-direction:column; gap:5px; overflow:hidden; font-family:'Source Sans 3',sans-serif; }
  .w-title { font-family:'Oswald',sans-serif; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#FDE047; text-shadow:0 0 8px rgba(253,224,71,0.4); border-bottom:2px solid rgba(253,224,71,0.35); padding-bottom:5px; margin-bottom:2px; display:flex; align-items:center; gap:5px; }
  .w-dot { width:6px; height:6px; border-radius:50%; background:#A7F3D0; flex-shrink:0; animation:blink 2s infinite; }
  @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
  .w-section { font-size:9px; text-transform:uppercase; letter-spacing:1.5px; opacity:.65; margin-top:3px; }
  .w-big { font-family:'Oswald',sans-serif; font-size:30px; font-weight:700; line-height:1; }
  .w-big .unit { font-size:13px; font-weight:400; opacity:.65; margin-left:2px; }
  .w-gauge { background:rgba(0,0,0,.2); border-radius:3px; height:4px; overflow:hidden; margin:3px 0 4px; }
  .w-gauge-fill { height:4px; border-radius:3px; transition:width 1s ease; }
  .fill-eol { background:#A7F3D0; }
  .fill-pv  { background:#FDE68A; }
  .w-sep { border:none; border-top:1px solid rgba(255,255,255,.2); margin:2px 0; }
  .w-row { display:flex; justify-content:space-between; align-items:center; font-size:10px; opacity:.88; }
  .w-row .val { font-family:'Oswald',sans-serif; font-size:14px; font-weight:600; }
  .w-co2 { background:rgba(0,0,0,.18); border-radius:6px; padding:6px 8px; text-align:center; margin-top:2px; }
  .w-co2-label { font-size:9px; text-transform:uppercase; letter-spacing:1.5px; opacity:.7; margin-bottom:2px; }
  .w-co2-val { font-family:'Oswald',sans-serif; font-size:22px; font-weight:700; color:#A7F3D0; }
  .w-co2-unit { font-size:11px; opacity:.65; margin-left:2px; }
  .w-update { font-size:8px; opacity:.65; text-align:center; margin-top:2px; }

  @keyframes defilement { from{transform:translateX(0)} to{transform:translateX(-100%)} }

  /* Overlay mobile */
  .sb-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:199; }
  .sb-overlay.active { display:block; }

  @media(max-width:900px) {
    .sidebar { width: var(--sb-min); }
    .sidebar .sb-brand,.sidebar .sb-lbl,.sidebar .sb-role-label,.sidebar .sb-username { opacity:0; max-width:0; }
    .sidebar.mobile-open { width: var(--sb-w); }
    .sidebar.mobile-open .sb-brand { opacity:1; width:auto; }
    .sidebar.mobile-open .sb-lbl,.sidebar.mobile-open .sb-role-label,.sidebar.mobile-open .sb-username { opacity:1; max-width:160px; }
    .main-wrapper { margin-left: var(--sb-min); }
    .main-wrapper.sb-collapsed { margin-left: var(--sb-min); }
    .cards { grid-template-columns:1fr; padding:16px; }
    .topbar-screen,.layout,.footer-screen { grid-template-columns:1fr; }
    .zone1,.zone3 { min-height:140px; }
    .mini-layout,.mini-footer { grid-template-columns:70px 1fr 50px; }
  }
  </style>
</head>
<body>

<!-- Overlay mobile -->
<div class="sb-overlay" id="sbOverlay"></div>

<!-- ══════════════════════════════════════
     SIDEBAR
══════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">

  <div class="sb-header">
    <button class="sb-toggle" id="sbToggle" title="Réduire/Ouvrir le menu">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>
    <div class="sb-brand">Info<span>Prod</span></div>
  </div>

  <nav class="sb-nav">

    <a href="gabarits.php" class="sb-link active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <rect x="14" y="14" width="7" height="7" rx="1"/>
      </svg>
      <span class="sb-lbl">Gabarits</span>
    </a>

    <a href="production.php" class="sb-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
      </svg>
      <span class="sb-lbl">Production</span>
    </a>

    <?php if ($canViewDB): ?>
    <div class="sb-sep"></div>
    <a href="http://192.168.0.11/phpmyadmin/" target="_blank" class="sb-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <ellipse cx="12" cy="5" rx="9" ry="3"/>
        <path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>
        <path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/>
      </svg>
      <span class="sb-lbl">Base de données</span>
    </a>
    <?php endif; ?>

    <?php if ($canViewUsers): ?>
    <a href="admin_users.php" class="sb-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
      <span class="sb-lbl">Utilisateurs</span>
    </a>
    <?php endif; ?>

  </nav>

  <div class="sb-footer">
    <div class="sb-user">
      <span class="sb-role-dot" style="background:<?= $roleColor ?>"></span>
      <div style="display:flex;flex-direction:column;gap:2px;overflow:hidden;">
        <span class="sb-role-label" style="color:<?= $roleColor ?>"><?= $roleLabel ?></span>
        <span class="sb-username"><?= $userName ?></span>
      </div>
    </div>
    <a href="logout.php" class="sb-logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      <span class="sb-lbl">Déconnexion</span>
    </a>
  </div>

</aside>

<!-- ══════════════════════════════════════
     MAIN WRAPPER
══════════════════════════════════════ -->
<div class="main-wrapper" id="mainWrapper">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-title">🗂 Gestion des gabarits d'affichage</div>
    <?php if ($canAdd): ?>
    <button class="btn-add" id="addTemplateBtn">+ Nouveau gabarit</button>
    <?php endif; ?>
  </div>

  <!-- Banner de rôle -->
  <?php if ($primaryRole !== 'admin'): ?>
  <div class="role-banner">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>
      <strong>Accès <?= $roleLabel ?> :</strong>
      <?php if ($primaryRole === 'vie_scolaire_lyceen'): ?>
        Vous voyez uniquement le gabarit <strong>Vie scolaire – Lycée</strong>. Vous pouvez le modifier.
      <?php elseif ($primaryRole === 'vie_scolaire_postbac'): ?>
        Vous voyez uniquement le gabarit <strong>Vie scolaire – Post-bac</strong>. Vous pouvez le modifier.
      <?php elseif ($primaryRole === 'ateliers'): ?>
        Vous voyez les gabarits Ateliers et Énergie.
      <?php elseif ($primaryRole === 'moderator'): ?>
        Vous pouvez modifier et affecter des gabarits. Seul l'admin peut créer ou supprimer.
      <?php else: ?>
        Accès en lecture seule.
      <?php endif; ?>
    </span>
  </div>
  <?php endif; ?>

  <!-- Grille de cartes -->
  <div class="cards" id="cardsContainer"></div>

</div><!-- /main-wrapper -->

<!-- ── Modal prévisualisation ── -->
<div class="modal" id="templateModal">
  <div class="modal-box">
    <div class="modal-top">
      <h2 id="modalTitle">Prévisualisation du gabarit</h2>
      <button class="close-btn" id="closeModal">Fermer</button>
    </div>
    <div class="screen">
      <div class="topbar-screen">
        <div id="siteName">LGT Joseph GAILLARD</div>
        <div id="dateHeure"> le Lundi 13 Novembre 2023. Il est 07h30</div>
      </div>
      <div class="layout">
        <div class="panel zone1" id="zone1">Zone d'information 1</div>
        <div class="panel zone2" id="zone2">Zone d'information 2</div>
        <div class="zone3" id="zone3"></div>
      </div>
      <div class="footer-screen">
        <div class="location" id="location">Bâtiment</div>
        <div class="alert"><span id="alerteText">Urgences, rappels</span></div>
        <div class="status"></div>
      </div>
    </div>
  </div>
</div>

<!-- ── Modal modification ── -->
<div class="modal" id="editModal">
  <div class="modal-box" style="max-width:700px;">
    <div class="modal-top">
      <h2>Modifier le gabarit</h2>
      <button class="close-btn" id="closeEditModal">Fermer</button>
    </div>
    <form class="edit-form" id="editForm">
      <div><label>Nom du gabarit</label><input type="text" id="editTitle" required></div>
      <div><label>Nom de l'établissement</label><input type="text" id="editSite" required></div>
      <div><label>Zone 1 (gauche – bleue)</label><textarea id="editZone1" required></textarea></div>
      <div><label>Zone 2 (centre)</label><textarea id="editZone2" required></textarea></div>
      <div><label>Bâtiment / emplacement</label><input type="text" id="editLocation" required></div>
      <div><label>Texte de l'alerte rouge</label><input type="text" id="editAlert" required></div>
      <div>
        <label>Statut</label>
        <select id="editStatus">
          <option value="Actif">Actif</option>
          <option value="Hors ligne">Hors ligne</option>
        </select>
      </div>
      <button type="submit" class="save-btn">Enregistrer les modifications</button>
    </form>
  </div>
</div>

<!-- ── Modal affectation ── -->
<div class="modal" id="assignModal">
  <div class="modal-box" style="max-width:600px;">
    <div class="modal-top">
      <h2>Affecter à un afficheur</h2>
      <button class="close-btn" id="closeAssignModal">Fermer</button>
    </div>
    <form class="edit-form" id="assignForm">
      <div>
        <label>Choisir un afficheur</label>
        <select id="assignDisplay" required>
          <option value="">-- Sélectionner --</option>
          <option value="hall_admin">Hall administration</option>
          <option value="vie_scolaire_cse">Vie scolaire C.S.E</option>
          <option value="ateliers_w">Ateliers W</option>
          <option value="physique_s">Physique S</option>
          <option value="refectoire">Réfectoire</option>
        </select>
      </div>
      <button type="submit" class="save-btn">Valider l'affectation</button>
    </form>
  </div>
</div>

<script>
// ══════════════════════════════════════════
//  Sidebar toggle
// ══════════════════════════════════════════
const sidebar     = document.getElementById('sidebar');
const mainWrapper = document.getElementById('mainWrapper');
const sbOverlay   = document.getElementById('sbOverlay');
const isMobile    = () => window.innerWidth <= 900;

document.getElementById('sbToggle').addEventListener('click', () => {
  if (isMobile()) {
    sidebar.classList.toggle('mobile-open');
    sbOverlay.classList.toggle('active');
  } else {
    sidebar.classList.toggle('collapsed');
    mainWrapper.classList.toggle('sb-collapsed');
    localStorage.setItem('sb-collapsed', sidebar.classList.contains('collapsed'));
  }
});
sbOverlay.addEventListener('click', () => {
  sidebar.classList.remove('mobile-open');
  sbOverlay.classList.remove('active');
});
if (!isMobile() && localStorage.getItem('sb-collapsed') === 'true') {
  sidebar.classList.add('collapsed');
  mainWrapper.classList.add('sb-collapsed');
}

// ══════════════════════════════════════════
//  Permissions depuis PHP
// ══════════════════════════════════════════
const USER_ROLES = <?= $userRolesJson ?>;
const CAN_ADD    = <?= $canAdd    ? 'true' : 'false' ?>;
const CAN_EDIT   = <?= $canEdit   ? 'true' : 'false' ?>;
const CAN_DELETE = <?= $canDelete ? 'true' : 'false' ?>;
const CAN_ASSIGN = <?= $canAssign ? 'true' : 'false' ?>;

// ══════════════════════════════════════════
//  Gabarits disponibles
// ══════════════════════════════════════════
const ALL_GABARITS = [
  { title:"Gabarit Administration",          site:"LGT Joseph GAILLARD", zone1:"Infos admin",             zone2:"Annonces générales",             location:"Administration",    alert:"Urgences / Rappels", status:"Actif", assigned:"" },
  { title:"Gabarit Vie scolaire – Lycée",    site:"LGT Joseph GAILLARD", zone1:"Absences Lycée",          zone2:"Professeurs absents – Lycée",    location:"Bâtiment C – VSL",  alert:"Retards / Rappels",  status:"Actif", assigned:"" },
  { title:"Gabarit Vie scolaire – Post-bac", site:"LGT Joseph GAILLARD", zone1:"Absences Post-bac",       zone2:"Professeurs absents – Post-bac", location:"Bâtiment C – VSP",  alert:"Retards / Rappels",  status:"Actif", assigned:"" },
  { title:"Gabarit Ateliers",                site:"LGT Joseph GAILLARD", zone1:"Production Éolienne",     zone2:"Production Solaire + Stats",     location:"Bât. W – Ateliers", alert:"Urgences / Rappels", status:"Actif", assigned:"" },
  { title:"Gabarit Énergie",                 site:"LGT Joseph GAILLARD", zone1:"Graphiques Production",   zone2:"Stats & Historique",             location:"Bât. S – Physique", alert:"Urgences / Rappels", status:"Actif", assigned:"" }
];

const ROLE_FILTER = {
  'vie_scolaire_lyceen':  ['Gabarit Vie scolaire – Lycée'],
  'vie_scolaire_postbac': ['Gabarit Vie scolaire – Post-bac'],
  'vie_scolaire':         ['Gabarit Vie scolaire – Lycée', 'Gabarit Vie scolaire – Post-bac'],
  'ateliers':             ['Gabarit Ateliers', 'Gabarit Énergie'],
};

function getVisibleGabarits() {
  for (const role of Object.keys(ROLE_FILTER)) {
    if (USER_ROLES.includes(role)) {
      return ALL_GABARITS.filter(g => ROLE_FILTER[role].includes(g.title));
    }
  }
  return ALL_GABARITS;
}

// ══════════════════════════════════════════
//  Variables globales
// ══════════════════════════════════════════
const cardsContainer = document.getElementById('cardsContainer');
let currentCard = null, cardBeingEdited = null, cardBeingAssigned = null;
let widgetInterval = null, journalEol = [], journalPv = [], nbReleves = 0;
const XML_URL    = "http://192.168.0.131/status.xml";
const REFRESH_MS = 2000;
const CO2_FACTOR = 0.75;
const MAX_REF    = 500;

// ── Date/heure ─────────────────────────────
function mettreAJourDateHeure() {
  const now   = new Date();
  const jours = ["Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"];
  const mois  = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
  const hh = String(now.getHours()).padStart(2,'0');
  const mm = String(now.getMinutes()).padStart(2,'0');
  const dh = document.getElementById('dateHeure');
  if (dh) dh.textContent = ` ${jours[now.getDay()]} ${now.getDate()} ${mois[now.getMonth()]} ${now.getFullYear()}. Il est ${hh}h${mm}`;
  document.querySelectorAll('.mini-date').forEach(el => el.textContent = `${jours[now.getDay()]} ${hh}h${mm}`);
}

function avg(arr) { return arr.length ? arr.reduce((a,b) => a+b, 0)/arr.length : 0; }
function setGauge(id, val, max) {
  const el = document.getElementById(id);
  if (el) el.style.width = Math.min((val/max)*100, 100)+'%';
}

// ── Widget énergie ─────────────────────────
function construireWidgetEnergie() {
  document.getElementById('zone3').innerHTML = `
    <div class="widget-zone3">
      <div class="w-title"><span class="w-dot" id="wDot"></span>Production d'énergie renouvelable</div>
      <div class="w-section">🌬 Éolienne — Site 1</div>
      <div class="w-big" id="wEol">—<span class="unit">kWh</span></div>
      <div class="w-gauge"><div class="w-gauge-fill fill-eol" id="wEolG" style="width:0%"></div></div>
      <hr class="w-sep">
      <div class="w-section">☀ Photovoltaïque — Site 2</div>
      <div class="w-big" id="wPV">—<span class="unit">kWh</span></div>
      <div class="w-gauge"><div class="w-gauge-fill fill-pv" id="wPVG" style="width:0%"></div></div>
      <hr class="w-sep">
      <div class="w-row"><span>⚡ Total</span><span class="val" id="wTotal">—</span></div>
      <div class="w-row"><span>📊 Moy. jour</span><span class="val" id="wMoy">—</span></div>
      <div class="w-row"><span>🔢 Relevés</span><span class="val" id="wReleves">0</span></div>
      <hr class="w-sep">
      <div class="w-co2">
        <div class="w-co2-label">🌱 CO₂ économisé</div>
        <div><span class="w-co2-val" id="wCO2">—</span><span class="w-co2-unit">kg</span></div>
      </div>
      <div class="w-update" id="wUpdate">En attente IPX800...</div>
    </div>`;
}

async function fetchWidgetEnergie() {
  try {
    const res     = await fetch(XML_URL, { cache:"no-store" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const xmlText = await res.text();
    const xml     = new DOMParser().parseFromString(xmlText, "application/xml");
    if (xml.querySelector("parsererror")) throw new Error("XML invalide");

    const eol   = parseFloat(xml.querySelector("analog1")?.textContent) || 0;
    const pv    = parseFloat(xml.querySelector("analog2")?.textContent) || 0;
    const total = parseFloat((eol+pv).toFixed(2));
    const co2   = parseFloat((total*CO2_FACTOR).toFixed(2));
    journalEol.push(eol); journalPv.push(pv); nbReleves++;
    const moy = parseFloat(avg([...journalEol,...journalPv]).toFixed(2));
    const t = new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit',second:'2-digit'});

    document.getElementById('wEol').innerHTML   = `${eol}<span class="unit">kWh</span>`;
    document.getElementById('wPV').innerHTML    = `${pv}<span class="unit">kWh</span>`;
    document.getElementById('wTotal').textContent   = total+' kWh';
    document.getElementById('wMoy').textContent     = moy+' kWh';
    document.getElementById('wReleves').textContent = nbReleves;
    document.getElementById('wCO2').textContent     = co2;
    document.getElementById('wUpdate').textContent  = 'Mis à jour : '+t;
    setGauge('wEolG', eol, MAX_REF);
    setGauge('wPVG',  pv,  MAX_REF);
    const dot = document.getElementById('wDot');
    if (dot) dot.style.background = '#A7F3D0';
  } catch(e) {
    const dot = document.getElementById('wDot');
    if (dot) dot.style.background = '#FCA5A5';
    const u = document.getElementById('wUpdate');
    if (u) u.textContent = '⚠ Erreur : '+e.message;
  }
}

function stopWidgetEnergie() {
  if (widgetInterval) { clearInterval(widgetInterval); widgetInterval = null; }
}
function lancerWidgetEnergie() {
  stopWidgetEnergie();
  journalEol = []; journalPv = []; nbReleves = 0;
  construireWidgetEnergie();
  fetchWidgetEnergie();
  widgetInterval = setInterval(fetchWidgetEnergie, REFRESH_MS);
}

// ── Création HTML d'une carte ──────────────
function createCardHTML(d) {
  return `
    <div class="preview">
      <div class="mini-screen">
        <div class="mini-topbar"><div>${d.site}</div><div class="mini-date"></div></div>
        <div class="mini-layout">
          <div class="mini-panel mini-zone1">${d.zone1}</div>
          <div class="mini-panel mini-zone2">${d.zone2}</div>
          <div class="mini-panel mini-zone3">
            <div class="mini-zone3-title">Prod. énergie</div>
            <div class="mini-meter"><div class="mini-meter-fill" style="width:45%"></div></div>
            <div class="mini-meter"><div class="mini-meter-fill" style="width:58%"></div></div>
          </div>
        </div>
        <div class="mini-footer">
          <div class="mini-location">${d.location}</div>
          <div class="mini-alert">${d.alert}</div>
          <div class="mini-status"></div>
        </div>
      </div>
    </div>
    <div class="card-content">
      <h2>${d.title}</h2>
      <p>Bâtiment : ${d.location}</p>
      <p class="${d.status==='Actif'?'status-active':'status-offline'}">● ${d.status}</p>
      <p class="assigned-display">Afficheur : ${d.assigned||'Non affecté'}</p>
      <div class="actions">
        ${CAN_EDIT   ? '<button class="btn btn-edit">Modifier</button>'    : ''}
        <button class="btn btn-preview">Prévisualiser</button>
        ${CAN_ASSIGN ? '<button class="btn btn-assign">Affecter</button>'  : ''}
        ${CAN_DELETE ? '<button class="btn btn-delete">Supprimer</button>' : ''}
      </div>
    </div>`;
}

function updateCardVisual(card) {
  card.querySelector('.card-content h2').textContent = card.dataset.title;
  card.querySelector('.card-content p:nth-of-type(1)').textContent = 'Bâtiment : '+card.dataset.location;
  const s = card.querySelector('.card-content p:nth-of-type(2)');
  s.textContent = card.dataset.status==='Actif' ? '● Actif' : '⚠ Hors ligne';
  s.className   = card.dataset.status==='Actif' ? 'status-active' : 'status-offline';
  card.querySelector('.assigned-display').textContent = 'Afficheur : '+(card.dataset.assigned||'Non affecté');
  card.querySelector('.mini-topbar div:first-child').textContent = card.dataset.site;
  card.querySelector('.mini-zone1').textContent    = card.dataset.zone1;
  card.querySelector('.mini-zone2').textContent    = card.dataset.zone2;
  card.querySelector('.mini-location').textContent = card.dataset.location;
  card.querySelector('.mini-alert').textContent    = card.dataset.alert;
}

function openTemplate(card) {
  currentCard = card;
  document.getElementById('modalTitle').textContent = card.dataset.title;
  document.getElementById('siteName').textContent   = card.dataset.site;
  document.getElementById('zone1').textContent      = card.dataset.zone1;
  document.getElementById('zone2').textContent      = card.dataset.zone2;
  document.getElementById('location').textContent   = card.dataset.location;
  document.getElementById('alerteText').textContent = card.dataset.alert;
  lancerWidgetEnergie();
  document.getElementById('templateModal').classList.add('active');
  mettreAJourDateHeure();
}

function openEditModal(card) {
  cardBeingEdited = card;
  document.getElementById('editTitle').value    = card.dataset.title;
  document.getElementById('editSite').value     = card.dataset.site;
  document.getElementById('editZone1').value    = card.dataset.zone1;
  document.getElementById('editZone2').value    = card.dataset.zone2;
  document.getElementById('editLocation').value = card.dataset.location;
  document.getElementById('editAlert').value    = card.dataset.alert;
  document.getElementById('editStatus').value   = card.dataset.status;
  document.getElementById('editModal').classList.add('active');
}

function openAssignModal(card) {
  cardBeingAssigned = card;
  document.getElementById('assignDisplay').value = card.dataset.assigned||'';
  document.getElementById('assignModal').classList.add('active');
}

function attachCardEvents(card) {
  const previewBtn = card.querySelector('.btn-preview');
  const editBtn    = card.querySelector('.btn-edit');
  const assignBtn  = card.querySelector('.btn-assign');
  const deleteBtn  = card.querySelector('.btn-delete');

  if (previewBtn) previewBtn.addEventListener('click', e => { e.stopPropagation(); openTemplate(card); });
  if (editBtn)    editBtn.addEventListener('click',    e => { e.stopPropagation(); openEditModal(card); });
  if (assignBtn)  assignBtn.addEventListener('click',  e => { e.stopPropagation(); openAssignModal(card); });
  if (deleteBtn)  deleteBtn.addEventListener('click',  e => {
    e.stopPropagation();
    if (!confirm(`Supprimer "${card.dataset.title}" ?`)) return;
    if (currentCard===card)       { document.getElementById('templateModal').classList.remove('active'); stopWidgetEnergie(); currentCard=null; }
    if (cardBeingEdited===card)   { document.getElementById('editModal').classList.remove('active'); cardBeingEdited=null; }
    if (cardBeingAssigned===card) { document.getElementById('assignModal').classList.remove('active'); cardBeingAssigned=null; }
    card.remove();
  });
}

function addCard(d) {
  const card = document.createElement('div');
  card.className = 'card';
  Object.assign(card.dataset, { title:d.title, site:d.site, zone1:d.zone1, zone2:d.zone2, location:d.location, alert:d.alert, status:d.status, assigned:d.assigned||'' });
  card.innerHTML = createCardHTML(d);
  cardsContainer.appendChild(card);
  attachCardEvents(card);
  mettreAJourDateHeure();
}

// Bouton ajouter
const addBtn = document.getElementById('addTemplateBtn');
if (addBtn) {
  addBtn.addEventListener('click', () => {
    addCard({ title:'Nouveau gabarit', site:'LGT Joseph GAILLARD', zone1:'Zone 1', zone2:'Zone 2', location:'Nouveau bâtiment', alert:'Nouvelle alerte', status:'Actif', assigned:'' });
  });
}

// Formulaires
document.getElementById('editForm').addEventListener('submit', function(e) {
  e.preventDefault();
  if (!cardBeingEdited) return;
  Object.assign(cardBeingEdited.dataset, {
    title:    document.getElementById('editTitle').value,
    site:     document.getElementById('editSite').value,
    zone1:    document.getElementById('editZone1').value,
    zone2:    document.getElementById('editZone2').value,
    location: document.getElementById('editLocation').value,
    alert:    document.getElementById('editAlert').value,
    status:   document.getElementById('editStatus').value,
  });
  updateCardVisual(cardBeingEdited);
  if (currentCard===cardBeingEdited && document.getElementById('templateModal').classList.contains('active')) openTemplate(cardBeingEdited);
  document.getElementById('editModal').classList.remove('active');
});

document.getElementById('assignForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  if (!cardBeingAssigned) return;

  const select = document.getElementById('assignDisplay');
  const screenCode = select.value;
  const screenName = select.options[select.selectedIndex].text;
  const templateTitle = cardBeingAssigned.dataset.title;

  try {
    const res = await fetch('save_assignment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        screen_code: screenCode,
        screen_name: screenName,
        template_title: templateTitle
      })
    });

    const data = await res.json();

    if (!data.success) {
      alert("Erreur : " + (data.error || "Affectation impossible"));
      return;
    }

    cardBeingAssigned.dataset.assigned = screenName;
    updateCardVisual(cardBeingAssigned);
    document.getElementById('assignModal').classList.remove('active');
    alert('Gabarit affecté à ' + screenName);

  } catch (err) {
    alert("Erreur réseau : " + err.message);
  }
});

// Fermeture modals
document.getElementById('closeModal').addEventListener('click', () => {
  document.getElementById('templateModal').classList.remove('active');
  stopWidgetEnergie();
});
document.getElementById('closeEditModal').addEventListener('click',   () => document.getElementById('editModal').classList.remove('active'));
document.getElementById('closeAssignModal').addEventListener('click', () => document.getElementById('assignModal').classList.remove('active'));

['templateModal','editModal','assignModal'].forEach(id => {
  document.getElementById(id).addEventListener('click', e => {
    if (e.target === document.getElementById(id)) {
      document.getElementById(id).classList.remove('active');
      if (id==='templateModal') stopWidgetEnergie();
    }
  });
});

document.addEventListener('keydown', e => {
  if (e.key==='Escape') {
    ['templateModal','editModal','assignModal'].forEach(id => document.getElementById(id).classList.remove('active'));
    stopWidgetEnergie();
  }
});

// Init
getVisibleGabarits().forEach(d => addCard(d));
mettreAJourDateHeure();
setInterval(mettreAJourDateHeure, 1000);
</script>
</body>
</html>
