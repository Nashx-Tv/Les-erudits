<?php
// =============================================
//  production.php — Dashboard Production Énergie
// =============================================
require_once 'auth_check.php';
requireLogin();

$userName     = htmlspecialchars($_SESSION['user_name'] ?? '');
$primaryRole  = getPrimaryRole();
$roleLabel    = getRoleLabel($primaryRole);
$roleColor    = getRoleColor($primaryRole);
$canViewDB    = hasAnyRole('admin');
$canViewUsers = hasAnyRole('admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Production Énergie – InfoProd</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --sb-w:     220px;
  --sb-min:   56px;
  --transition: .3s ease;
}

body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
  color: white;
  min-height: 100vh;
  display: flex;
}

/* ══════════════════════════════════════
   SIDEBAR
══════════════════════════════════════ */
.sidebar {
  position: fixed;
  top: 0; left: 0;
  width: var(--sb-w);
  height: 100vh;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(16px);
  border-right: 1px solid rgba(255,255,255,0.07);
  display: flex;
  flex-direction: column;
  transition: width var(--transition);
  z-index: 200;
  overflow: hidden;
}
.sidebar.collapsed { width: var(--sb-min); }

/* Header sidebar */
.sb-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 12px;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  min-height: 54px;
  flex-shrink: 0;
}
.sb-toggle {
  background: none; border: none;
  color: rgba(255,255,255,0.6);
  cursor: pointer; border-radius: 7px;
  width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: background .15s, color .15s;
}
.sb-toggle:hover { background: rgba(255,255,255,0.1); color: #fff; }
.sb-brand {
  font-size: 17px; font-weight: 800; color: #fff;
  white-space: nowrap;
  transition: opacity var(--transition), width var(--transition);
  overflow: hidden;
}
.sb-brand span { color: #00d084; }
.sidebar.collapsed .sb-brand { opacity: 0; width: 0; }

/* Nav links */
.sb-nav {
  flex: 1;
  padding: 10px 8px;
  display: flex;
  flex-direction: column;
  gap: 3px;
  overflow: hidden;
}
.sb-link {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 11px; border-radius: 9px;
  color: rgba(255,255,255,0.55);
  text-decoration: none;
  font-size: 13px; font-weight: 500;
  white-space: nowrap;
  transition: background .15s, color .15s;
}
.sb-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
.sb-link.active { background: rgba(0,198,255,0.18); color: #00c6ff; }
.sb-link svg { flex-shrink: 0; width: 18px; height: 18px; }
.sb-lbl {
  overflow: hidden;
  transition: opacity var(--transition), max-width var(--transition);
  max-width: 160px;
}
.sidebar.collapsed .sb-lbl { opacity: 0; max-width: 0; }

/* Séparateur */
.sb-sep {
  height: 1px;
  background: rgba(255,255,255,0.07);
  margin: 6px 8px;
  flex-shrink: 0;
}

/* Footer sidebar */
.sb-footer {
  padding: 10px 8px;
  border-top: 1px solid rgba(255,255,255,0.07);
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex-shrink: 0;
  overflow: hidden;
}
.sb-user {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 11px;
}
.sb-role-dot {
  width: 10px; height: 10px; border-radius: 50%;
  flex-shrink: 0;
}
.sb-role-label {
  font-size: 11px; font-weight: 700; color: #fff;
  white-space: nowrap;
  transition: opacity var(--transition), max-width var(--transition);
  max-width: 160px; overflow: hidden;
}
.sb-username {
  font-size: 11px; color: rgba(255,255,255,0.45);
  white-space: nowrap;
  transition: opacity var(--transition), max-width var(--transition);
  max-width: 160px; overflow: hidden;
}
.sidebar.collapsed .sb-role-label,
.sidebar.collapsed .sb-username { opacity: 0; max-width: 0; }
.sb-logout {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 11px; border-radius: 9px;
  color: rgba(255,100,100,0.7);
  text-decoration: none;
  font-size: 13px; font-weight: 500;
  white-space: nowrap;
  transition: background .15s, color .15s;
}
.sb-logout:hover { background: rgba(255,80,80,0.15); color: #ff6b6b; }
.sb-logout svg { flex-shrink: 0; width: 18px; height: 18px; }

/* ══════════════════════════════════════
   MAIN WRAPPER
══════════════════════════════════════ */
.main-wrapper {
  margin-left: var(--sb-w);
  flex: 1;
  transition: margin-left var(--transition);
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.main-wrapper.sb-collapsed { margin-left: var(--sb-min); }

/* Topbar */
.topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 20px; height: 54px;
  background: rgba(0,0,0,0.25);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255,255,255,0.07);
  flex-wrap: wrap; gap: 8px;
  flex-shrink: 0;
}
.topbar-title {
  font-size: 16px; font-weight: 600;
}
#statusBadge {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 12px; font-weight: 400;
  background: rgba(255,255,255,0.1); border-radius: 20px;
  padding: 5px 14px; border: 1px solid rgba(255,255,255,0.1);
}
#statusDot {
  width: 8px; height: 8px; border-radius: 50%;
  background: #aaa; transition: background .3s;
}
#statusDot.ok      { background: #4cd964; box-shadow: 0 0 6px #4cd964; }
#statusDot.err     { background: #ff3b30; box-shadow: 0 0 6px #ff3b30; }
#statusDot.loading { background: #f9d423; box-shadow: 0 0 6px #f9d423; }

/* ── Grid ── */
.container {
  padding: 16px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

/* ── Cards ── */
.card {
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(12px);
  border-radius: 18px; padding: 22px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.35);
  border: 1px solid rgba(255,255,255,0.07);
  position: relative; overflow: hidden;
}
.source-tag {
  display: inline-block; font-size: 10px;
  background: rgba(0,198,255,0.15); color: #00c6ff;
  border: 1px solid rgba(0,198,255,0.25); border-radius: 20px;
  padding: 3px 10px; margin-bottom: 10px;
  letter-spacing: 1.5px; font-weight: 600;
}
.card h2 { font-size: 18px; font-weight: 600; margin-bottom: 12px; opacity: .95; }
.value { font-size: 36px; font-weight: 700; letter-spacing: -1px; margin: 4px 0 6px; color: white; }
.co2-val { font-size: 36px; font-weight: 700; color: #4cd964; letter-spacing: -1px; margin: 4px 0 6px; }
.timestamp { font-size: 12px; opacity: 0.5; margin-top: 4px; }
.refresh-bar-wrap { background: rgba(255,255,255,0.07); border-radius: 4px; height: 3px; margin-top: 14px; overflow: hidden; }
.refresh-bar { height: 3px; background: linear-gradient(90deg,#00c6ff,#4cd964); border-radius: 4px; animation: cd 2s linear infinite; }
@keyframes cd { from{width:100%} to{width:0%} }

.moy-card { display: flex; flex-direction: column; justify-content: space-between; }
.moy-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }
.moy-box { background: rgba(255,255,255,0.06); border-radius: 12px; padding: 12px 14px; border: 1px solid rgba(255,255,255,0.07); }
.moy-box .lbl { font-size: 10px; opacity: .5; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 5px; }
.moy-box .moy-val { font-size: 22px; font-weight: 700; }
.moy-box .moy-val.eol   { color: #00c6ff; }
.moy-box .moy-val.pv    { color: #f9d423; }
.moy-box .moy-val.co2   { color: #4cd964; }
.moy-box .moy-val.total { color: white; }
.moy-box .moy-sub { font-size: 10px; opacity: .4; margin-top: 2px; }
canvas { width: 100% !important; height: 260px !important; }

.bdd-card { grid-column: 1 / -1; }
.countdown-wrap { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
.countdown-badge { display: inline-flex; align-items: center; gap: 7px; background: rgba(0,198,255,0.1); border: 1px solid rgba(0,198,255,0.25); border-radius: 10px; padding: 7px 14px; font-size: 13px; font-weight: 600; color: #00c6ff; }
.progress-wrap { flex: 1; min-width: 120px; background: rgba(255,255,255,0.07); border-radius: 6px; height: 8px; overflow: hidden; }
#progressBar { height: 8px; background: linear-gradient(90deg,#00c6ff,#4cd964); border-radius: 6px; width: 0%; transition: width 1s linear; }
#saveNotif { display: none; padding: 8px 14px; border-radius: 10px; font-size: 12px; margin-bottom: 10px; }
#saveNotif.ok  { background: rgba(76,217,100,.12); border: 1px solid rgba(76,217,100,.3); color: #4cd964; }
#saveNotif.err { background: rgba(255,59,48,.12);  border: 1px solid rgba(255,59,48,.3);  color: #ff6b6b; }
.hist-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 14px; }
.hist-table th { text-align: left; padding: 9px 12px; background: rgba(255,255,255,0.07); border-bottom: 1px solid rgba(255,255,255,0.08); font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; opacity: .6; }
.hist-table td { padding: 9px 12px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 12px; }
.hist-table tr:hover td { background: rgba(255,255,255,0.03); }
.c-eol  { color: #00c6ff; font-weight: 600; }
.c-pv   { color: #f9d423; font-weight: 600; }
.c-moy  { color: #a78bfa; font-weight: 600; }
.c-date { opacity: .45; font-size: 11px; }
#errBDD { display: none; margin-top: 10px; padding: 10px 14px; border-radius: 10px; font-size: 12px; color: #ff6b6b; background: rgba(255,59,48,.12); border: 1px solid rgba(255,59,48,.3); white-space: pre-line; }

/* ── Overlay mobile ── */
.sb-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.5); z-index: 199;
}
.sb-overlay.active { display: block; }

@media(max-width: 768px) {
  .sidebar { width: var(--sb-min); }
  .sidebar .sb-brand, .sidebar .sb-lbl,
  .sidebar .sb-role-label, .sidebar .sb-username { opacity: 0; max-width: 0; }
  .sidebar.mobile-open { width: var(--sb-w); }
  .sidebar.mobile-open .sb-brand { opacity: 1; width: auto; }
  .sidebar.mobile-open .sb-lbl,
  .sidebar.mobile-open .sb-role-label,
  .sidebar.mobile-open .sb-username { opacity: 1; max-width: 160px; }
  .main-wrapper { margin-left: var(--sb-min); }
  .main-wrapper.sb-collapsed { margin-left: var(--sb-min); }
  .container { grid-template-columns: 1fr; }
  .bdd-card { grid-column: 1; }
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

    <a href="gabarits.php" class="sb-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <rect x="14" y="14" width="7" height="7" rx="1"/>
      </svg>
      <span class="sb-lbl">Gabarits</span>
    </a>

    <a href="production.php" class="sb-link active">
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
    <div class="topbar-title">🌍 Dashboard Production Énergie – Lycée</div>
    <span id="statusBadge">
      <span id="statusDot" class="loading"></span>
      <span id="statusText">Connexion IPX800…</span>
    </span>
  </div>

  <!-- Cards -->
  <div class="container">

    <!-- Éolienne -->
    <div class="card">
      <div class="source-tag">IPX800 · STATUS.XML</div>
      <h2>🌬 Site 1 – Éolienne</h2>
      <div class="value" id="eolienneValue">— kWh</div>
      <div class="timestamp" id="timeEolienne">En attente…</div>
      <div class="refresh-bar-wrap"><div class="refresh-bar"></div></div>
    </div>

    <!-- Photovoltaïque -->
    <div class="card">
      <div class="source-tag">IPX800 · STATUS.XML</div>
      <h2>☀️ Site 2 – Photovoltaïque</h2>
      <div class="value" id="pvValue">— kWh</div>
      <div class="timestamp" id="timePV">En attente…</div>
      <div class="refresh-bar-wrap"><div class="refresh-bar"></div></div>
    </div>

    <!-- Graphique -->
    <div class="card">
      <h2>📈 Historique Journalier</h2>
      <canvas id="energyChart"></canvas>
    </div>

    <!-- CO₂ + Moyenne journalière -->
    <div class="card moy-card">
      <div>
        <h2>🌱 CO₂ Économisé</h2>
        <div class="co2-val" id="co2Value">— kg</div>
        <div class="timestamp" id="globalTime">Calculé depuis IPX800</div>
      </div>
      <div>
        <h2 style="margin-top:18px;font-size:15px;opacity:.8">📊 Moyenne journalière</h2>
        <div class="moy-grid">
          <div class="moy-box">
            <div class="lbl">Éolienne moy.</div>
            <div class="moy-val eol" id="moyEol">—</div>
            <div class="moy-sub">kWh / relevé</div>
          </div>
          <div class="moy-box">
            <div class="lbl">PV moy.</div>
            <div class="moy-val pv" id="moyPV">—</div>
            <div class="moy-sub">kWh / relevé</div>
          </div>
          <div class="moy-box">
            <div class="lbl">CO₂ moy.</div>
            <div class="moy-val co2" id="moyCO2">—</div>
            <div class="moy-sub">kg / relevé</div>
          </div>
          <div class="moy-box">
            <div class="lbl">Relevés aujourd'hui</div>
            <div class="moy-val total" id="nbReleves">0</div>
            <div class="moy-sub">enregistrements</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Auto-save BDD -->
    <div class="card bdd-card">
      <h2>🗄️ Enregistrement automatique — toutes les 10 minutes</h2>
      <div class="countdown-wrap" style="margin-top:12px;">
        <div class="countdown-badge">
          ⏱ Prochain enregistrement dans <span id="countdown">10:00</span>
        </div>
        <div class="progress-wrap"><div id="progressBar"></div></div>
      </div>
      <div id="saveNotif"></div>
      <div id="errBDD"></div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:8px;">
        <h3 style="margin:0;font-size:14px;opacity:.7;font-weight:500;">📋 Historique BDD — 20 derniers enregistrements</h3>
        <button onclick="chargerHistorique()" style="padding:6px 14px;border-radius:8px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.08);color:white;font-family:Poppins,sans-serif;font-size:12px;cursor:pointer;">
          🔄 Actualiser
        </button>
      </div>
      <table class="hist-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Analog1 / Éolienne (kWh)</th>
            <th>Moyenne journalière</th>
            <th>Date</th>
            <th>Heure</th>
          </tr>
        </thead>
        <tbody id="histBody">
          <tr><td colspan="5" style="text-align:center;opacity:.35;padding:24px">
            En attente du premier enregistrement automatique…
          </td></tr>
        </tbody>
      </table>
    </div>

  </div><!-- /container -->
</div><!-- /main-wrapper -->

<script>
// ══════════════════════════════════════════
//  Sidebar toggle
// ══════════════════════════════════════════
const sidebar     = document.getElementById('sidebar');
const mainWrapper = document.getElementById('mainWrapper');
const sbOverlay   = document.getElementById('sbOverlay');
const isMobile    = () => window.innerWidth <= 768;

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

// Restaurer l'état au chargement (desktop seulement)
if (!isMobile() && localStorage.getItem('sb-collapsed') === 'true') {
  sidebar.classList.add('collapsed');
  mainWrapper.classList.add('sb-collapsed');
}

// ══════════════════════════════════════════
//  Dashboard Production Énergie
// ══════════════════════════════════════════
const XML_URL    = "http://192.168.0.131/status.xml";
const API_URL    = "api.php";
const REFRESH_MS = 2000;
const SAVE_MS    = 10 * 60 * 1000;
const MOY_WINDOW = 10;
const MAX_GRAPH  = 60;

const bufferEol  = [];
const bufferPV   = [];
let   tempsRestant = SAVE_MS;
const journalEol = [];
const journalPV  = [];

const chart = new Chart(
  document.getElementById('energyChart').getContext('2d'), {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        { label:'Éolienne (kWh)',               data:[], borderColor:'#00c6ff', backgroundColor:'rgba(0,198,255,0.08)', fill:true, tension:0.4, pointRadius:1.5 },
        { label:'Photovoltaïque (kWh)',          data:[], borderColor:'#f9d423', backgroundColor:'rgba(249,212,35,0.06)', fill:true, tension:0.4, pointRadius:1.5 },
        { label:'Moyenne journalière Éolienne',  data:[], borderColor:'rgba(0,198,255,0.4)', borderDash:[6,3], fill:false, tension:0, pointRadius:0, borderWidth:1.5 },
        { label:'Moyenne journalière PV',        data:[], borderColor:'rgba(249,212,35,0.4)', borderDash:[6,3], fill:false, tension:0, pointRadius:0, borderWidth:1.5 }
      ]
    },
    options: {
      responsive: true,
      animation: { duration: 200 },
      plugins: { legend: { labels: { color:'rgba(255,255,255,0.7)', font:{ family:'Poppins', size:11 }, boxWidth:14 } } },
      scales: {
        x: { ticks:{ color:'rgba(255,255,255,0.4)', maxTicksLimit:8, font:{size:10} }, grid:{ color:'rgba(255,255,255,0.04)' } },
        y: { beginAtZero:true, ticks:{ color:'rgba(255,255,255,0.4)', font:{size:10} }, grid:{ color:'rgba(255,255,255,0.04)' } }
      }
    }
  }
);

function setStatus(state, txt) {
  document.getElementById('statusDot').className  = state;
  document.getElementById('statusText').innerText = txt;
}
function nowStr() {
  return new Date().toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
}
function avg(arr) {
  return arr.length ? arr.reduce((a,b) => a+b, 0) / arr.length : 0;
}

async function fetchXML() {
  try {
    const res    = await fetch(XML_URL, { cache:"no-store" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const txt    = await res.text();
    const xmlDoc = new DOMParser().parseFromString(txt, "application/xml");
    if (xmlDoc.querySelector("parsererror")) throw new Error("XML invalide");

    const eolNode = xmlDoc.querySelector("analog1");
    const pvNode  = xmlDoc.querySelector("analog2");
    if (!eolNode) throw new Error("<analog1> introuvable");

    const eol = parseFloat(eolNode.textContent) || 0;
    const pv  = pvNode ? (parseFloat(pvNode.textContent) || 0) : 0;
    const co2 = parseFloat(((eol + pv) * 0.75).toFixed(2));
    const t   = nowStr();

    document.getElementById('eolienneValue').innerText = eol + " kWh";
    document.getElementById('pvValue').innerText       = pv  + " kWh";
    document.getElementById('co2Value').innerText      = co2 + " kg";
    document.getElementById('timeEolienne').innerText  = "Mis à jour : " + t;
    document.getElementById('timePV').innerText        = "Mis à jour : " + t;
    document.getElementById('globalTime').innerText    = "Calculé à " + t;

    bufferEol.push(eol); if (bufferEol.length > MOY_WINDOW) bufferEol.shift();
    bufferPV.push(pv);   if (bufferPV.length  > MOY_WINDOW) bufferPV.shift();
    journalEol.push(eol);
    journalPV.push(pv);

    const moyEol = parseFloat(avg(journalEol).toFixed(2));
    const moyPV  = parseFloat(avg(journalPV).toFixed(2));
    const moyCO2 = parseFloat(((moyEol + moyPV) * 0.75).toFixed(2));

    document.getElementById('moyEol').innerText    = moyEol + " kWh";
    document.getElementById('moyPV').innerText     = moyPV  + " kWh";
    document.getElementById('moyCO2').innerText    = moyCO2 + " kg";
    document.getElementById('nbReleves').innerText = journalEol.length;

    chart.data.labels.push(t);
    chart.data.datasets[0].data.push(eol);
    chart.data.datasets[1].data.push(pv);
    chart.data.datasets[2].data.push(moyEol);
    chart.data.datasets[3].data.push(moyPV);
    if (chart.data.labels.length > MAX_GRAPH) {
      chart.data.labels.shift();
      chart.data.datasets.forEach(d => d.data.shift());
    }
    chart.update('none');

    setStatus('ok', `IPX800 connecté · ${t}`);
    document.getElementById('errBDD').style.display = 'none';

  } catch (err) {
    setStatus('err', 'Erreur IPX800');
    console.warn("Erreur XML :", err.message);
  }
}

setInterval(() => {
  tempsRestant -= 1000;
  if (tempsRestant <= 0) tempsRestant = SAVE_MS;
  const min = Math.floor(tempsRestant / 60000);
  const sec = Math.floor((tempsRestant % 60000) / 1000);
  document.getElementById('countdown').innerText = String(min).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
  const pct = ((SAVE_MS - tempsRestant) / SAVE_MS) * 100;
  document.getElementById('progressBar').style.width = pct + '%';
}, 1000);

setInterval(sauvegarderBDD, SAVE_MS);

async function sauvegarderBDD() {
  const eol     = parseFloat(document.getElementById('eolienneValue').innerText) || 0;
  const moyJour = parseFloat(avg(journalEol).toFixed(3));
  try {
    const res  = await fetch(API_URL + "?action=enregistrer", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ analog1: eol, moyenne: moyJour })
    });
    const data = await res.json();
    if (data.succes) {
      const notif = document.getElementById('saveNotif');
      notif.className     = 'ok';
      notif.innerText     = `✅ Enregistrement automatique — Analog1 : ${eol} kWh · Moyenne jour : ${moyJour} · ${data.date} à ${data.heure}`;
      notif.style.display = 'block';
      setTimeout(() => notif.style.display = 'none', 8000);
      chargerHistorique();
    }
  } catch (err) {
    const el = document.getElementById('errBDD');
    el.style.display = 'block';
    el.innerText = "⚠️ Sauvegarde BDD échouée\n→ Vérifie que Apache tourne sur le PC étudiant 4\nErreur : " + err.message;
  }
}

async function chargerHistorique() {
  try {
    const res  = await fetch(API_URL + "?action=liste&limite=20");
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const rows = await res.json();
    if (!rows.length) {
      document.getElementById('histBody').innerHTML =
        `<tr><td colspan="5" style="text-align:center;opacity:.35;padding:24px">En attente du premier enregistrement automatique…</td></tr>`;
      return;
    }
    document.getElementById('histBody').innerHTML = rows.map(r => `
      <tr>
        <td style="opacity:.35">#${r.id}</td>
        <td class="c-eol">${parseFloat(r.analog1).toFixed(3)} kWh</td>
        <td class="c-moy">${parseFloat(r.moyenne).toFixed(3)} kWh</td>
        <td class="c-date">${r.date_mesure}</td>
        <td class="c-date">${r.heure_mesure}</td>
      </tr>
    `).join('');
  } catch (err) {
    console.warn("Historique non dispo :", err.message);
  }
}

fetchXML();
setInterval(fetchXML, REFRESH_MS);
chargerHistorique();
</script>

</body>
</html>
