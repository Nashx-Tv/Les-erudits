<?php
// =============================================
//  production.php — Dashboard Production Énergie
// =============================================
require_once 'auth_check.php';
requireLogin();

$primaryRole  = getPrimaryRole();
$roleLabel    = getRoleLabel($primaryRole);
$roleColor    = getRoleColor($primaryRole);
$userRoles    = $_SESSION['user_roles'] ?? ['user'];
$userRolesJson = json_encode($userRoles);
$userName     = htmlspecialchars($_SESSION['user_name'] ?? '');

$canEnterData = hasAnyRole('admin', 'ateliers');
$canViewDB    = hasAnyRole('admin');
$canViewUsers = hasAnyRole('admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>InfoProd – Production</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      color: white;
      min-height: 100vh;
      display: flex;
    }

    /* SIDEBAR */
    aside {
      width: 220px;
      background: rgba(0,0,0,0.35);
      backdrop-filter: blur(10px);
      border-right: 1px solid rgba(255,255,255,0.08);
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      min-height: 100vh;
      position: sticky;
      top: 0;
      height: 100vh;
    }

    .brand { padding: 22px 18px 18px; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .brand-name { font-size: 20px; font-weight: 700; color: #fff; }
    .brand-name span { color: #00d084; }
    .brand-sub { font-size: 10px; color: rgba(255,255,255,0.3); margin-top: 3px; text-transform: uppercase; letter-spacing: 0.5px; }

    nav { flex: 1; padding: 12px 8px; display: flex; flex-direction: column; gap: 2px; }

    .nav-section { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.2); padding: 12px 10px 5px; font-weight: 600; }

    nav a {
      display: flex; align-items: center; gap: 10px; padding: 9px 10px;
      border-radius: 7px; color: rgba(255,255,255,0.45); text-decoration: none;
      font-size: 13px; font-weight: 500; transition: background 0.15s, color 0.15s;
    }

    nav a:hover { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.85); }
    nav a.active { background: rgba(0,198,255,0.18); color: #00c6ff; }
    nav a svg { width: 16px; height: 16px; flex-shrink: 0; }

    .sidebar-footer { padding: 14px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 11px; color: rgba(255,255,255,0.2); line-height: 1.5; }

    /* CONTENT */
    .content { flex: 1; display: flex; flex-direction: column; min-width: 0; }

    header {
      background: rgba(0,0,0,0.25);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255,255,255,0.08);
      padding: 0 26px; height: 58px;
      display: flex; align-items: center; justify-content: space-between;
      flex-shrink: 0;
    }

    .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: rgba(255,255,255,0.4); }
    .breadcrumb .current { color: rgba(255,255,255,0.9); font-weight: 600; }

    .header-right { display: flex; align-items: center; gap: 16px; }
    .header-time { font-size: 13px; color: rgba(255,255,255,0.5); }
    .header-time span { color: #00c6ff; font-weight: 600; }
    .btn-logout {
      font-size: 12px; padding: 5px 12px; border-radius: 7px; border: 1px solid rgba(255,255,255,0.2);
      background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.6); text-decoration: none;
      transition: background 0.15s;
    }
    .btn-logout:hover { background: rgba(255,80,80,0.2); color: #ff6b6b; border-color: rgba(255,80,80,0.4); }

    /* PAGE BODY */
    .page-body { flex: 1; padding: 26px; overflow-y: auto; }

    .page-title-row { margin-bottom: 22px; }
    .page-title-row h1 { font-size: 22px; font-weight: 700; }
    .page-title-row p { font-size: 13px; color: rgba(255,255,255,0.45); margin-top: 4px; }

    /* SUMMARY CARDS */
    .summary-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 26px; }

    .summary-card {
      background: rgba(255,255,255,0.07);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 14px;
      padding: 18px 20px;
    }

    .summary-label { font-size: 11px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .summary-value { font-size: 28px; font-weight: 700; line-height: 1; }
    .summary-value.blue   { color: #00c6ff; }
    .summary-value.yellow { color: #f9d423; }
    .summary-value.green  { color: #4cd964; }
    .summary-value.white  { color: #ffffff; }
    .summary-sub { font-size: 11px; color: rgba(255,255,255,0.3); margin-top: 5px; }

    /* GRID */
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; margin-bottom: 18px; }

    .card {
      background: rgba(255,255,255,0.07);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 15px;
      padding: 22px;
    }

    .card h2 { font-size: 16px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .card h2 .icon { font-size: 20px; }

    /* INPUTS */
    .input-row { display: flex; gap: 10px; margin-bottom: 14px; }

    .input-group { flex: 1; }
    .input-group label { display: block; font-size: 11px; color: rgba(255,255,255,0.5); margin-bottom: 5px; }
    .input-group input {
      width: 100%; padding: 9px 12px; border-radius: 9px; border: 1px solid rgba(255,255,255,0.15);
      background: rgba(255,255,255,0.08); color: white; font-size: 14px; font-family: 'Poppins', sans-serif;
    }
    .input-group input::placeholder { color: rgba(255,255,255,0.3); }
    .input-group input:focus { outline: none; border-color: #00c6ff; }

    .btn-update {
      width: 100%; padding: 10px; border-radius: 9px; border: none;
      background: linear-gradient(135deg, #00c6ff, #0072ff);
      color: white; font-weight: 600; font-size: 14px; cursor: pointer;
      font-family: 'Poppins', sans-serif; transition: opacity 0.15s;
    }
    .btn-update:hover { opacity: 0.88; }

    .value-display { font-size: 24px; font-weight: 600; margin-top: 10px; }
    .value-display.blue   { color: #00c6ff; }
    .value-display.yellow { color: #f9d423; }
    .value-display.green  { color: #4cd964; }

    .timestamp { font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 4px; }

    /* CHART */
    canvas { width: 100% !important; height: 220px !important; }

    /* CO2 */
    .co2-big { font-size: 42px; font-weight: 700; color: #4cd964; line-height: 1; margin: 10px 0 6px; }
    .co2-formula { font-size: 12px; color: rgba(255,255,255,0.35); }

    /* HISTORY TABLE */
    .history-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .history-table th { text-align: left; color: rgba(255,255,255,0.4); font-weight: 500; padding: 6px 8px; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .history-table td { padding: 8px; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .history-table tr:last-child td { border-bottom: none; }
    .history-table .td-blue   { color: #00c6ff; font-weight: 600; }
    .history-table .td-yellow { color: #f9d423; font-weight: 600; }
    .history-table .td-green  { color: #4cd964; font-weight: 600; }

    /* FOOTER */
    footer {
      background: rgba(0,0,0,0.25);
      border-top: 1px solid rgba(255,255,255,0.06);
      text-align: center; padding: 11px;
      font-size: 11.5px; color: rgba(255,255,255,0.2);
      flex-shrink: 0;
    }

    @media (max-width: 900px) {
      .grid, .grid-3, .summary-row { grid-template-columns: 1fr; }
      aside { display: none; }
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
    <a href="index.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Tableau de bord
    </a>
    <a href="production.php" class="active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Production
    </a>
    <a href="#">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg>
      Températures
    </a>
    <div class="nav-section">Affichage</div>
    <a href="gabarits.html">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
      Gabarits
    </a>
    <?php if ($canViewDB): ?>
    <a href="http://192.168.0.11/phpmyadmin/index.php?route=/database/structure&db=auth_system">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/></svg>
      Base de données
    </a>
    <?php endif; ?>
    <?php if ($canViewUsers): ?>
    <a href="admin_users.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Utilisateurs
    </a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-footer">
    BTS CIEL IR · Session 2026<br>
    <span style="color:rgba(255,255,255,0.8);font-weight:600"><?= $userName ?></span><br>
    <span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;color:#fff;background:<?= $roleColor ?>;margin-top:4px"><?= $roleLabel ?></span>
  </div>
</aside>

<!-- CONTENT -->
<div class="content">
  <header>
    <div class="breadcrumb">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      <span class="current">Production d'énergie</span>
    </div>
    <div class="header-right">
      <div class="header-time">Mise à jour : <span id="headerTime">--:--</span></div>
      <a href="logout.php" class="btn-logout">Déconnexion</a>
    </div>
  </header>

  <div class="page-body">
    <div class="page-title-row">
      <h1>🌍 Dashboard Production Énergie</h1>
      <p>Suivi en temps réel des sources renouvelables – LGT Joseph Gaillard</p>
    </div>

    <!-- ══ RÉSUMÉ DES ACTIONS DISPONIBLES ══ -->
    <div style="background:linear-gradient(135deg,rgba(0,0,0,0.35),rgba(0,0,0,0.2));border:1px solid rgba(255,255,255,0.1);border-radius:14px;padding:18px 20px;margin-bottom:22px;backdrop-filter:blur(8px)">
      <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:12px;display:flex;align-items:center;gap:8px">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Ce que vous pouvez faire sur cette page
        <span style="background:<?= $roleColor ?>;color:#fff;font-size:9px;padding:2px 8px;border-radius:8px;font-weight:700"><?= $roleLabel ?></span>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px">
        <?php
        $actions = [
          ['📊', 'Consulter les données', 'Historique, graphiques, statistiques', true],
          ['➕', 'Saisir une production', 'Enregistrer éolienne + photovoltaïque', $canEnterData],
          ['🌱', 'Voir le CO₂ économisé', 'Calcul automatique en kg et arbres équivalents', true],
          ['📋', 'Historique de session', 'Tableau des relevés enregistrés', true],
          ['🗄️', 'Base de données', 'Accès direct à phpMyAdmin', $canViewDB],
        ];
        foreach ($actions as [$icon, $title, $desc, $allowed]):
        ?>
        <div style="background:rgba(255,255,255,<?= $allowed ? '0.08' : '0.03' ?>);border:1px solid rgba(255,255,255,<?= $allowed ? '0.12' : '0.06' ?>);border-radius:10px;padding:11px 13px;display:flex;align-items:flex-start;gap:9px;<?= $allowed ? '' : 'opacity:0.35' ?>">
          <span style="font-size:18px;flex-shrink:0;margin-top:1px"><?= $icon ?></span>
          <div>
            <strong style="display:block;font-size:12px;font-weight:700;color:#fff;margin-bottom:2px"><?= $title ?></strong>
            <span style="font-size:11px;color:rgba(255,255,255,0.4);line-height:1.4"><?= $desc ?></span>
            <div style="margin-top:4px">
              <span style="display:inline-block;font-size:9px;padding:1px 7px;border-radius:8px;font-weight:700;<?= $allowed ? 'background:rgba(0,208,132,0.2);color:#00d084' : 'background:rgba(255,77,77,0.15);color:#ff6b6b' ?>">
                <?= $allowed ? '✓ Autorisé' : '✗ Accès restreint' ?>
              </span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- SUMMARY -->
    <div class="summary-row">
      <div class="summary-card">
        <div class="summary-label">Total produit</div>
        <div class="summary-value white" id="sumTotal">0 kWh</div>
        <div class="summary-sub">éolienne + photovoltaïque</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Site 1 – Éolienne</div>
        <div class="summary-value blue" id="sumEolienne">0 kWh</div>
        <div class="summary-sub">Bât. X – Ateliers</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Site 2 – Photovoltaïque</div>
        <div class="summary-value yellow" id="sumPV">0 kWh</div>
        <div class="summary-sub">Bât. S – Physique</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">CO₂ économisé</div>
        <div class="summary-value green" id="sumCO2">0 kg</div>
        <div class="summary-sub">0,056 kg / kWh</div>
      </div>
    </div>

    <!-- SAISIE -->
    <div class="grid">
      <!-- SITE 1 -->
      <div class="card">
        <h2><span class="icon">🌬️</span> Site 1 – Éolienne</h2>
        <?php if ($canEnterData): ?>
        <div class="input-row">
          <div class="input-group">
            <label>Production (kWh)</label>
            <input type="number" id="eolienneInput" placeholder="Ex : 12.5" min="0" step="0.1">
          </div>
        </div>
        <button class="btn-update" onclick="updateData()">Mettre à jour</button>
        <?php else: ?>
        <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:10px 12px;font-size:12px;color:rgba(255,255,255,0.4);margin-bottom:12px">
          🔒 Saisie réservée aux rôles Admin et Ateliers
        </div>
        <?php endif; ?>
        <div class="value-display blue" id="eolienneValue">0 kWh</div>
        <div class="timestamp" id="timeEolienne">Aucune donnée</div>
      </div>

      <!-- SITE 2 -->
      <div class="card">
        <h2><span class="icon">☀️</span> Site 2 – Photovoltaïque</h2>
        <?php if ($canEnterData): ?>
        <div class="input-row">
          <div class="input-group">
            <label>Production (kWh)</label>
            <input type="number" id="pvInput" placeholder="Ex : 8.3" min="0" step="0.1">
          </div>
        </div>
        <button class="btn-update" onclick="updateData()">Mettre à jour</button>
        <?php else: ?>
        <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:10px 12px;font-size:12px;color:rgba(255,255,255,0.4);margin-bottom:12px">
          🔒 Saisie réservée aux rôles Admin et Ateliers
        </div>
        <?php endif; ?>
        <div class="value-display yellow" id="pvValue">0 kWh</div>
        <div class="timestamp" id="timePV">Aucune donnée</div>
      </div>
    </div>

    <div class="grid-3">
      <!-- GRAPHIQUE -->
      <div class="card" style="grid-column: span 2;">
        <h2><span class="icon">📈</span> Historique journalier</h2>
        <canvas id="energyChart"></canvas>
      </div>

      <!-- CO2 -->
      <div class="card">
        <h2><span class="icon">🌱</span> CO₂ économisé</h2>
        <div class="co2-big" id="co2Value">0 kg</div>
        <div class="co2-formula">= (Éolienne + PV) × 0,056 kg/kWh</div>
        <div class="timestamp" id="globalTime" style="margin-top:12px;">Aucune donnée</div>

        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.08);">
          <div style="font-size:12px; color:rgba(255,255,255,0.4); margin-bottom:10px;">Équivalent en arbres</div>
          <div style="font-size:22px; font-weight:700; color:#4cd964;" id="treesValue">🌳 0</div>
          <div style="font-size:11px; color:rgba(255,255,255,0.3); margin-top:4px;">1 arbre ≈ 21 kg CO₂/an</div>
        </div>
      </div>
    </div>

    <!-- HISTORIQUE TABLE -->
    <div class="card">
      <h2><span class="icon">📋</span> Relevés de la session</h2>
      <table class="history-table" id="historyTable">
        <thead>
          <tr>
            <th>Heure</th>
            <th>Éolienne (kWh)</th>
            <th>Photovoltaïque (kWh)</th>
            <th>Total (kWh)</th>
            <th>CO₂ économisé (kg)</th>
          </tr>
        </thead>
        <tbody id="historyBody">
          <tr><td colspan="5" style="color:rgba(255,255,255,0.25); text-align:center; padding:16px;">Aucune donnée enregistrée</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <footer>InfoProd – BTS CIEL IR &nbsp;·&nbsp; LGT Joseph Gaillard &nbsp;·&nbsp; Fort-de-France &nbsp;·&nbsp; Session 2026 &nbsp;·&nbsp; Étudiant 4</footer>
</div>

<script>
  const API_URL = 'api.php';

  /* ── CHART ── */
  const ctx = document.getElementById('energyChart').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        { label: 'Éolienne (kWh)',        data: [], borderColor: '#00c6ff', backgroundColor: 'rgba(0,198,255,0.1)', fill: true, tension: 0.4 },
        { label: 'Photovoltaïque (kWh)',   data: [], borderColor: '#f9d423', backgroundColor: 'rgba(249,212,35,0.1)',  fill: true, tension: 0.4 }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { labels: { color: 'rgba(255,255,255,0.6)', font: { family: 'Poppins' } } } },
      scales: {
        x: { ticks: { color: 'rgba(255,255,255,0.4)' }, grid: { color: 'rgba(255,255,255,0.05)' } },
        y: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,0.4)' }, grid: { color: 'rgba(255,255,255,0.05)' } }
      }
    }
  });

  /* ── CLOCK ── */
  function updateClock() {
    const now = new Date();
    const hh = String(now.getHours()).padStart(2,'0');
    const mm = String(now.getMinutes()).padStart(2,'0');
    const ss = String(now.getSeconds()).padStart(2,'0');
    document.getElementById('headerTime').textContent = `${hh}:${mm}:${ss}`;
  }
  setInterval(updateClock, 1000);
  updateClock();

  /* ── CHARGER HISTORIQUE DEPUIS LA BDD ── */
  async function loadHistory() {
    try {
      const res  = await fetch(`${API_URL}?action=historique&limit=50`);
      const json = await res.json();
      if (!json.success) return;

      const data = json.data;

      let totalEolienne = 0, totalPV = 0;
      data.forEach(row => {
        totalEolienne += parseFloat(row.analog1)      || 0;
        totalPV       += parseFloat(row.moyenne_jour) || 0;
      });
      const total = totalEolienne + totalPV;
      const co2   = (total * 0.056).toFixed(2);
      const trees = (parseFloat(co2) / 21).toFixed(2);

      document.getElementById('sumTotal').textContent    = total.toFixed(2)         + ' kWh';
      document.getElementById('sumEolienne').textContent = totalEolienne.toFixed(2) + ' kWh';
      document.getElementById('sumPV').textContent       = totalPV.toFixed(2)       + ' kWh';
      document.getElementById('sumCO2').textContent      = co2 + ' kg';
      document.getElementById('co2Value').textContent    = co2 + ' kg';
      document.getElementById('treesValue').textContent  = '🌳 ' + trees;

      const chartData = [...data].reverse().slice(-20);
      chart.data.labels           = chartData.map(r => r.heure_mesure.slice(0, 5));
      chart.data.datasets[0].data = chartData.map(r => parseFloat(r.analog1));
      chart.data.datasets[1].data = chartData.map(r => parseFloat(r.moyenne_jour));
      chart.update();

      const tbody = document.getElementById('historyBody');
      tbody.innerHTML = '';
      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="color:rgba(255,255,255,0.25); text-align:center; padding:16px;">Aucune donnée enregistrée</td></tr>';
      } else {
        data.slice(0, 20).forEach(row => {
          const e  = parseFloat(row.analog1);
          const pv = parseFloat(row.moyenne_jour);
          const t  = e + pv;
          const c  = (t * 0.056).toFixed(2);
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${row.heure_mesure.slice(0,5)} <span style="font-size:10px;color:rgba(255,255,255,0.3)">${row.date_mesure}</span></td>
            <td class="td-blue">${e.toFixed(2)}</td>
            <td class="td-yellow">${pv.toFixed(2)}</td>
            <td>${t.toFixed(2)}</td>
            <td class="td-green">${c}</td>`;
          tbody.appendChild(tr);
        });
      }

      if (data.length > 0) {
        const last = data[0];
        const ts   = `${last.date_mesure} à ${last.heure_mesure.slice(0,5)}`;
        document.getElementById('eolienneValue').textContent = parseFloat(last.analog1).toFixed(2)      + ' kWh';
        document.getElementById('pvValue').textContent       = parseFloat(last.moyenne_jour).toFixed(2) + ' kWh';
        document.getElementById('timeEolienne').textContent  = 'Dernière mesure : ' + ts;
        document.getElementById('timePV').textContent        = 'Dernière mesure : ' + ts;
        document.getElementById('globalTime').textContent    = 'Calculé le : ' + ts;
      }

    } catch (e) {
      console.error('Erreur chargement historique :', e);
    }
  }

  /* ── ENREGISTRER EN BDD ── */
  async function updateData() {
    const eolienne = parseFloat(document.getElementById('eolienneInput').value) || 0;
    const pv       = parseFloat(document.getElementById('pvInput').value)       || 0;

    try {
      const res  = await fetch(`${API_URL}?action=ajouter`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ analog1: eolienne, moyenne_jour: pv })
      });
      const json = await res.json();
      if (json.success) {
        document.getElementById('eolienneInput').value = '';
        document.getElementById('pvInput').value       = '';
        await loadHistory();
      } else {
        alert('Erreur BDD : ' + json.error);
      }
    } catch (e) {
      alert('Impossible de joindre le serveur. Vérifiez que Apache/PHP est démarré.');
      console.error(e);
    }
  }

  /* ── INIT ── */
  loadHistory();
  setInterval(loadHistory, 30000);
</script>
</body>
</html>
