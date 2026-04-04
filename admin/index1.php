<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Investment</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #06060a;
            --surface: #0e0e14;
            --card: #13131a;
            --card-hover: #17171f;
            --border: rgba(255,255,255,0.07);
            --accent: #7c6fff;
            --accent2: #a78bfa;
            --success: #34d399;
            --warning: #fbbf24;
            --error: #f87171;
            --text: #e4e4f0;
            --sub: #8888a0;
            --muted: #44445a;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        /* ── LAYOUT ── */
        #admin-layout {
            width: 100%; max-width: 500px;
            min-height: 100vh;
            display: flex; flex-direction: column;
            background: var(--surface);
            box-shadow: 0 0 0 1px var(--border), 0 0 80px rgba(0,0,0,0.8);
        }

        /* ── TOP NAV ── */
        .top-nav {
            position: sticky; top: 0; z-index: 200;
            background: rgba(14,14,20,0.85);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid var(--border);
            padding: 14px 16px 12px;
        }
        .nav-brand {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 12px;
        }
        .nav-brand-icon {
            width: 28px; height: 28px; border-radius: 8px;
            background: linear-gradient(135deg, var(--accent), #5b21b6);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }
        .nav-brand-name {
            font-size: 13px; font-weight: 800; letter-spacing: 0.5px;
            color: var(--text);
        }
        .nav-brand-badge {
            margin-left: auto;
            font-size: 9px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1px; color: var(--accent);
            background: rgba(124,111,255,0.1); border: 1px solid rgba(124,111,255,0.2);
            padding: 3px 8px; border-radius: 20px;
            font-family: 'Space Mono', monospace;
        }
        .nav-tabs {
            display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none;
        }
        .nav-tabs::-webkit-scrollbar { display: none; }
        .nav-tab {
            flex-shrink: 0;
            padding: 7px 14px; border-radius: 10px;
            font-size: 12px; font-weight: 700; letter-spacing: 0.2px;
            cursor: pointer; transition: all 0.2s;
            color: var(--sub); border: 1px solid transparent;
            background: none;
        }
        .nav-tab:hover { color: var(--text); background: rgba(255,255,255,0.04); }
        .nav-tab.active {
            color: #fff;
            background: linear-gradient(135deg, var(--accent), #5b21b6);
            box-shadow: 0 4px 16px rgba(124,111,255,0.3);
        }
        .nav-tab.danger { color: var(--error); }
        .nav-tab.danger:hover { background: rgba(248,113,113,0.08); }

        /* ── MAIN ── */
        .admin-main { flex: 1; padding: 20px 16px 48px; }

        /* ── PAGE HEADER ── */
        .page-header { margin-bottom: 20px; }
        .page-header h2 {
            font-size: 20px; font-weight: 900; letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 50%, var(--accent2));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .page-header p { font-size: 12px; color: var(--sub); margin-top: 3px; }

        /* ── SECTION ── */
        .section-container { display: none; }
        .section-container.active { display: block; animation: fadeUp 0.25s ease; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

        .section-top {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 14px;
        }
        .section-top h3 { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--sub); }
        .add-btn {
            display: flex; align-items: center; gap: 6px;
            padding: 9px 16px;
            background: linear-gradient(135deg, var(--accent), #5b21b6);
            color: #fff; border: none; border-radius: 12px;
            font-size: 12px; font-weight: 800; cursor: pointer;
            box-shadow: 0 4px 16px rgba(124,111,255,0.25);
            transition: all 0.2s; letter-spacing: 0.3px;
        }
        .add-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(124,111,255,0.4); }

        /* ── CARD LIST ── */
        .card-list { display: flex; flex-direction: column; gap: 10px; }

        .item-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        .item-card::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
            background: linear-gradient(to bottom, var(--accent), #5b21b6);
            opacity: 0; transition: opacity 0.2s;
        }
        .item-card:hover { background: var(--card-hover); border-color: rgba(255,255,255,0.1); }
        .item-card:hover::before { opacity: 1; }

        /* card: top row */
        .card-header {
            display: flex; align-items: flex-start; justify-content: space-between;
            margin-bottom: 10px;
        }
        .card-title { font-size: 15px; font-weight: 800; color: var(--text); }
        .card-id {
            font-family: 'Space Mono', monospace;
            font-size: 10px; font-weight: 700; color: var(--accent);
            background: rgba(124,111,255,0.08); border: 1px solid rgba(124,111,255,0.15);
            padding: 3px 8px; border-radius: 6px;
        }

        /* card: meta tags */
        .card-meta {
            display: flex; flex-wrap: wrap; gap: 6px;
            margin-bottom: 12px;
        }
        .meta-tag {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 8px;
            font-size: 11px; font-weight: 700;
            background: rgba(255,255,255,0.04); color: var(--sub);
            border: 1px solid var(--border);
        }
        .meta-tag.green { background: rgba(52,211,153,0.07); color: var(--success); border-color: rgba(52,211,153,0.15); }
        .meta-tag.yellow { background: rgba(251,191,36,0.07); color: var(--warning); border-color: rgba(251,191,36,0.15); }
        .meta-tag.purple { background: rgba(124,111,255,0.08); color: var(--accent2); border-color: rgba(124,111,255,0.15); }

        /* card: action row */
        .card-actions {
            display: flex; align-items: center; gap: 6px;
            flex-wrap: wrap;
        }
        .card-actions .spacer { flex: 1; }

        /* Status dot */
        .status-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px 4px 8px; border-radius: 20px;
            font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .status-pill .dot { width: 6px; height: 6px; border-radius: 50%; }
        .pill-active { background: rgba(52,211,153,0.08); color: var(--success); border: 1px solid rgba(52,211,153,0.2); }
        .pill-active .dot { background: var(--success); box-shadow: 0 0 6px var(--success); animation: blink 2s infinite; }
        .pill-hidden { background: rgba(255,255,255,0.04); color: var(--sub); border: 1px solid var(--border); }
        .pill-hidden .dot { background: var(--muted); }
        @keyframes blink { 0%,100%{ opacity:1; } 50%{ opacity:0.3; } }

        /* action buttons */
        .act-btn {
            padding: 7px 14px; border-radius: 10px; border: none;
            font-size: 11px; font-weight: 800; cursor: pointer; transition: all 0.2s;
        }
        .act-btn:active { transform: scale(0.95); }
        .act-edit { background: rgba(124,111,255,0.1); color: var(--accent2); border: 1px solid rgba(124,111,255,0.2); }
        .act-edit:hover { background: var(--accent); color: #fff; box-shadow: 0 4px 12px rgba(124,111,255,0.3); }
        .act-hide { background: rgba(251,191,36,0.07); color: var(--warning); border: 1px solid rgba(251,191,36,0.15); }
        .act-hide:hover { background: var(--warning); color: #000; }
        .act-del { background: rgba(248,113,113,0.07); color: var(--error); border: 1px solid rgba(248,113,113,0.15); }
        .act-del:hover { background: var(--error); color: #fff; box-shadow: 0 4px 12px rgba(248,113,113,0.3); }
        .act-activate { background: rgba(52,211,153,0.07); color: var(--success); border: 1px solid rgba(52,211,153,0.15); }
        .act-activate:hover { background: var(--success); color: #022c22; }

        /* ── UPI CARD SPECIAL ── */
        .upi-address {
            font-family: 'Space Mono', monospace;
            font-size: 13px; color: var(--text); font-weight: 700;
            letter-spacing: 0.3px;
        }

        /* ── PLAN CARD SPECIAL ── */
        .plan-amount {
            font-size: 22px; font-weight: 900; letter-spacing: -0.5px;
            color: #fff;
        }
        .plan-amount span { font-size: 13px; font-weight: 600; color: var(--sub); }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center; padding: 48px 24px;
            color: var(--muted); border: 1px dashed var(--border);
            border-radius: 16px;
        }
        .empty-state .ei { font-size: 40px; margin-bottom: 12px; }
        .empty-state p { font-size: 13px; }

        /* ── AUTH ERROR ── */
        #auth-error {
            display: none; flex-direction: column; align-items: center; justify-content: center;
            gap: 16px; text-align: center; padding: 40px;
            min-height: 100vh; max-width: 500px; margin: 0 auto;
            background: var(--surface); box-shadow: 0 0 0 1px var(--border);
        }
        #auth-error .err-icon { font-size: 56px; }
        #auth-error .err-badge { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--error); background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.2); padding: 4px 12px; border-radius: 20px; }
        #auth-error h1 { font-size: 22px; font-weight: 900; color: var(--text); }
        #auth-error p { font-size: 13px; color: var(--sub); max-width: 280px; line-height: 1.6; }
        .re-login-btn { padding: 14px 28px; background: linear-gradient(135deg, var(--accent), #5b21b6); color: #fff; text-decoration: none; border-radius: 14px; font-weight: 800; font-size: 13px; box-shadow: 0 6px 20px rgba(124,111,255,0.3); transition: all 0.2s; }
        .re-login-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(124,111,255,0.5); }

        /* ── IMAGE MODAL ── */
        .img-modal {
            position: fixed; inset: 0; z-index: 999;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(20px);
            display: none; align-items: center; justify-content: center;
            padding: 20px;
        }
        .img-modal img { max-width: 100%; max-height: 80vh; border-radius: 16px; border: 1px solid var(--border); }
        .img-close {
            position: absolute; top: 16px; right: 16px;
            background: rgba(255,255,255,0.1); color: #fff; border: none;
            border-radius: 50%; width: 36px; height: 36px;
            font-size: 14px; cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; justify-content: center;
        }
        .img-close:hover { background: rgba(255,255,255,0.2); }

        /* ── FORM SHEET ── */
        .form-overlay {
            position: fixed; inset: 0; z-index: 999;
            background: rgba(0,0,0,0.75); backdrop-filter: blur(16px);
            display: none; align-items: flex-end; justify-content: center;
        }
        .form-sheet {
            width: 100%; max-width: 500px;
            background: #111118; border-radius: 24px 24px 0 0;
            border: 1px solid rgba(255,255,255,0.08); border-bottom: none;
            padding: 0 20px 36px;
            animation: sheetUp 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        @keyframes sheetUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
        .sheet-handle {
            width: 36px; height: 4px;
            background: rgba(255,255,255,0.12); border-radius: 2px;
            margin: 14px auto 24px;
        }
        .sheet-title { font-size: 18px; font-weight: 900; margin-bottom: 20px; }
        .form-field {
            width: 100%; padding: 14px 16px; margin-bottom: 10px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;
            color: var(--text); font-family: inherit; font-size: 14px; outline: none;
            transition: all 0.2s;
        }
        .form-field:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(124,111,255,0.1); }
        .form-field::placeholder { color: var(--muted); }
        .sheet-actions { display: flex; gap: 10px; margin-top: 6px; }
        .sheet-btn { flex: 1; padding: 15px; border: none; border-radius: 14px; font-weight: 800; font-size: 14px; cursor: pointer; transition: all 0.2s; }
        .sheet-primary { background: linear-gradient(135deg, var(--accent), #5b21b6); color: #fff; box-shadow: 0 4px 20px rgba(124,111,255,0.3); }
        .sheet-primary:hover { transform: translateY(-1px); }
        .sheet-cancel { background: rgba(255,255,255,0.05); color: var(--sub); border: 1px solid var(--border); }
        .sheet-cancel:hover { background: rgba(255,255,255,0.08); color: var(--text); }
    </style>
</head>
<body>

<div id="admin-layout" style="display:none">
    <nav class="top-nav">
        <div class="nav-brand">
            <div class="nav-brand-icon">💹</div>
            <span class="nav-brand-name">INVESTMENT</span>
            <span class="nav-brand-badge">ADMIN</span>
        </div>
        <div class="nav-tabs">
            <!-- <button class="nav-tab" data-tab="dashboard" onclick="showSection('dashboard')">📊 Analytics</button> -->
            <!-- <button class="nav-tab" data-tab="users" onclick="showSection('users')">👥 Users</button> -->
            <!-- <button class="nav-tab" data-tab="kyc" onclick="showSection('kyc')">🔍 KYC</button> -->
            <!-- <button class="nav-tab" data-tab="deposits" onclick="showSection('deposits')">💳 Deposits</button> -->
            <button class="nav-tab active" data-tab="packages" onclick="showSection('packages')">📦 Packages</button>
            <button class="nav-tab" data-tab="plans" onclick="showSection('plans')">📈 Plans</button>
            <button class="nav-tab" data-tab="upi" onclick="showSection('upi')">🏦 UPI</button>
            <button class="nav-tab danger" onclick="logoutAdmin()">Exit →</button>
        </div>
    </nav>

    <main class="admin-main">

        <!-- ===== PACKAGES ===== -->
        <section id="packages-section" class="section-container active">
            <div class="page-header">
                <h2>Packages</h2>
                <p>Manage investment package types</p>
            </div>
            <div class="section-top">
                <h3>All Packages</h3>
                <button class="add-btn" onclick="openPackageForm()">＋ Add Package</button>
            </div>
            <div class="card-list" id="packages-list"></div>
        </section>

        <!-- ===== PLANS ===== -->
        <section id="plans-section" class="section-container">
            <div class="page-header">
                <h2>Plans</h2>
                <p>Investment amounts &amp; returns</p>
            </div>
            <div class="section-top">
                <h3>All Plans</h3>
                <button class="add-btn" onclick="openPlanForm()">＋ Add Plan</button>
            </div>
            <div class="card-list" id="plans-list"></div>
        </section>

        <!-- ===== UPI ===== -->
        <section id="upi-section" class="section-container">
            <div class="page-header">
                <h2>UPI Settings</h2>
                <p>Payment collection addresses</p>
            </div>
            <div class="section-top">
                <h3>All UPI IDs</h3>
                <button class="add-btn" onclick="openUpiForm()">＋ Add UPI</button>
            </div>
            <div class="card-list" id="upi-list"></div>
        </section>

    </main>
</div>

<div id="auth-error">
    <div class="err-icon">🔒</div>
    <span class="err-badge">Access Denied</span>
    <h1>Admin Only</h1>
    <p>This section is restricted to the master account (User ID 1).</p>
    <a href="/" class="re-login-btn">← Back to App</a>
</div>

<!-- Image Modal -->
<div class="img-modal" id="img-modal" onclick="closeModal()">
    <img id="modal-img" src="" alt="Preview">
    <button class="img-close" onclick="closeModal()">✕</button>
</div>

<!-- Form Sheet -->
<div class="form-overlay" id="form-overlay" onclick="closeFormOnBg(event)">
    <div class="form-sheet">
        <div class="sheet-handle"></div>
        <div class="sheet-title" id="sheet-title">Add Item</div>
        <div id="sheet-fields"></div>
        <div class="sheet-actions">
            <button class="sheet-btn sheet-cancel" onclick="closeFormModal()">Cancel</button>
            <button class="sheet-btn sheet-primary" onclick="submitForm()">Save Changes</button>
        </div>
    </div>
</div>

<script>
const token = localStorage.getItem('token');
const authH = { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' };
let fType = '', fEditId = null;

/* ─── AUTH ─── */
function checkAuth() {
    if (!token) { showErr(); return; }
    try {
        const p = JSON.parse(atob(token.split('.')[1]));
        if (p.id !== 1) { showErr(); return; }
        document.getElementById('admin-layout').style.display = 'flex';
        fetchPackages(); fetchPlans(); fetchUpiList();
    } catch(e) { showErr(); }
}
function showErr() { document.getElementById('auth-error').style.display = 'flex'; }

/* ─── NAV ─── */
function showSection(id) {
    document.querySelectorAll('.section-container').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
    document.getElementById(id + '-section').classList.add('active');
    const tab = document.querySelector(`.nav-tab[data-tab="${id}"]`);
    if (tab) tab.classList.add('active');
}

/* ─── API ─── */
async function api(url, body) {
    const r = await fetch(url, { method:'POST', headers: authH, body: JSON.stringify(body) });
    return r.json();
}

/* ─── PACKAGES ─── */
async function fetchPackages() {
    const el = document.getElementById('packages-list');
    const data = await api('api/package_admin.php', { action:'list' });
    if (!data.packages?.length) {
        el.innerHTML = emptyState('📦', 'No packages yet. Add one!'); return;
    }
    el.innerHTML = data.packages.map(p => {
        const active = p.status == 1 && p.hide == 0;
        const tripTag = p.trip ? `<span class="meta-tag purple">✈ ${p.trip}</span>` : '';
        return `
        <div class="item-card">
            <div class="card-header">
                <div class="card-title">${capitalize(p.name)}</div>
                <span class="card-id">#${p.id}</span>
            </div>
            <div class="card-meta">
                <span class="meta-tag">⚙ ${capitalize(p.strategy)}</span>
                ${tripTag}
                <span class="status-pill ${active ? 'pill-active' : 'pill-hidden'}">
                    <span class="dot"></span>${active ? 'Active' : 'Hidden'}
                </span>
            </div>
            <div class="card-actions">
                <button class="act-btn act-edit" onclick="editPackage(${p.id},'${esc(p.name)}','${esc(p.strategy)}','${esc(p.trip)}')">Edit</button>
                <button class="act-btn act-hide" onclick="hidePackage(${p.id})">Hide</button>
                <button class="act-btn act-del" onclick="delPackage(${p.id})">Delete</button>
            </div>
        </div>`;
    }).join('');
}

function openPackageForm(id, name, strategy, trip) {
    fType = 'package'; fEditId = id || null;
    document.getElementById('sheet-title').innerText = id ? 'Edit Package' : 'New Package';
    document.getElementById('sheet-fields').innerHTML = `
        <input class="form-field" id="f-name" placeholder="Package name (e.g. Monthly)" value="${name||''}">
        <input class="form-field" id="f-strat" placeholder="Strategy (e.g. starter)" value="${strategy||''}">
        <input class="form-field" id="f-trip" placeholder="Trip reward (optional, e.g. Dubai)" value="${trip||''}">
    `;
    openSheet();
}
function editPackage(id, name, strategy, trip) { openPackageForm(id, name, strategy, trip); }
async function hidePackage(id) {
    if (!confirm('Hide this package from users?')) return;
    await api('api/package_admin.php', { action:'delete', id }); fetchPackages();
}
async function delPackage(id) {
    if (!confirm('Permanently delete? This cannot be undone.')) return;
    await api('api/package_admin.php', { action:'remove', id }); fetchPackages();
}

/* ─── PLANS ─── */
async function fetchPlans() {
    const el = document.getElementById('plans-list');
    const data = await api('api/plan_admin.php', { action:'list' });
    if (!data.plans?.length) {
        el.innerHTML = emptyState('📈', 'No plans yet. Add one!'); return;
    }
    el.innerHTML = data.plans.map(p => {
        const active = p.status == 1 && p.hide == 0;
        const amt = parseFloat(p.amount);
        const pct = parseFloat(p.percentage);
        const monthly = Math.round(amt * pct / 100);
        return `
        <div class="item-card">
            <div class="card-header">
                <div class="plan-amount">₹${amt.toLocaleString('en-IN')} <span>investment</span></div>
                <span class="card-id">#${p.id}</span>
            </div>
            <div class="card-meta">
                <span class="meta-tag green">↑ ${pct}% monthly</span>
                <span class="meta-tag yellow">₹${monthly.toLocaleString('en-IN')} profit</span>
                <span class="status-pill ${active ? 'pill-active' : 'pill-hidden'}">
                    <span class="dot"></span>${active ? 'Active' : 'Hidden'}
                </span>
            </div>
            <div class="card-actions">
                <button class="act-btn act-edit" onclick="editPlan(${p.id},${amt},${pct})">Edit</button>
                <button class="act-btn act-hide" onclick="hidePlan(${p.id})">Hide</button>
                <button class="act-btn act-del" onclick="delPlan(${p.id})">Delete</button>
            </div>
        </div>`;
    }).join('');
}

function openPlanForm(id, amount, percentage) {
    fType = 'plan'; fEditId = id || null;
    document.getElementById('sheet-title').innerText = id ? 'Edit Plan' : 'New Plan';
    document.getElementById('sheet-fields').innerHTML = `
        <input class="form-field" id="f-amount" type="number" placeholder="Investment amount (e.g. 5000)" value="${amount||''}">
        <input class="form-field" id="f-pct" type="number" step="0.1" placeholder="Monthly return % (e.g. 3)" value="${percentage||''}">
    `;
    openSheet();
}
function editPlan(id, amount, pct) { openPlanForm(id, amount, pct); }
async function hidePlan(id) {
    if (!confirm('Hide this plan from users?')) return;
    await api('api/plan_admin.php', { action:'delete', id }); fetchPlans();
}
async function delPlan(id) {
    if (!confirm('Permanently delete? This cannot be undone.')) return;
    await api('api/plan_admin.php', { action:'remove', id }); fetchPlans();
}

/* ─── UPI ─── */
async function fetchUpiList() {
    const el = document.getElementById('upi-list');
    const data = await api('api/upi_admin.php', { action:'list' });
    if (!data.upis?.length) {
        el.innerHTML = emptyState('🏦', 'No UPI IDs added yet.'); return;
    }
    el.innerHTML = data.upis.map(u => {
        const active = u.status == 1 && u.hide == 0;
        return `
        <div class="item-card">
            <div class="card-header">
                <div class="upi-address">${u.upi_id}</div>
                <span class="card-id">#${u.id}</span>
            </div>
            <div class="card-meta">
                <span class="status-pill ${active ? 'pill-active' : 'pill-hidden'}">
                    <span class="dot"></span>${active ? 'Active (Live)' : 'Inactive'}
                </span>
            </div>
            <div class="card-actions">
                <button class="act-btn act-edit" onclick="editUpi(${u.id},'${esc(u.upi_id)}')">Edit</button>
                <button class="act-btn act-activate" onclick="activateUpi(${u.id})">Set Active</button>
                <button class="act-btn act-del" onclick="delUpi(${u.id})">Remove</button>
            </div>
        </div>`;
    }).join('');
}

function openUpiForm(id, upiId) {
    fType = 'upi'; fEditId = id || null;
    document.getElementById('sheet-title').innerText = id ? 'Edit UPI ID' : 'New UPI ID';
    document.getElementById('sheet-fields').innerHTML = `
        <input class="form-field" id="f-upi" placeholder="UPI address (e.g. name@upi)" value="${upiId||''}">
    `;
    openSheet();
}
function editUpi(id, upiId) { openUpiForm(id, upiId); }
async function activateUpi(id) {
    if (!confirm('Set this as the active UPI?')) return;
    await api('api/upi_admin.php', { action:'activate', id }); fetchUpiList();
}
async function delUpi(id) {
    if (!confirm('Remove this UPI ID?')) return;
    await api('api/upi_admin.php', { action:'delete', id }); fetchUpiList();
}

/* ─── FORM SUBMIT ─── */
async function submitForm() {
    try {
        if (fType === 'package') {
            const body = {
                action: fEditId ? 'update' : 'add',
                name: document.getElementById('f-name').value,
                strategy: document.getElementById('f-strat').value,
                trip: document.getElementById('f-trip').value,
                status: 1, hide: 0
            };
            if (fEditId) body.id = fEditId;
            await api('api/package_admin.php', body); fetchPackages();

        } else if (fType === 'plan') {
            const body = {
                action: fEditId ? 'update' : 'add',
                amount: parseFloat(document.getElementById('f-amount').value),
                percentage: parseFloat(document.getElementById('f-pct').value),
                status: 1, hide: 0
            };
            if (fEditId) body.id = fEditId;
            await api('api/plan_admin.php', body); fetchPlans();

        } else if (fType === 'upi') {
            const body = { action: fEditId ? 'update' : 'add', upi_id: document.getElementById('f-upi').value };
            if (fEditId) body.id = fEditId;
            await api('api/upi_admin.php', body); fetchUpiList();
        }
        closeFormModal();
    } catch(e) { alert('Error: ' + e.message); }
}

/* ─── MODALS ─── */
function openModal(src) { document.getElementById('modal-img').src = src; document.getElementById('img-modal').style.display = 'flex'; }
function closeModal() { document.getElementById('img-modal').style.display = 'none'; }
function openSheet() { document.getElementById('form-overlay').style.display = 'flex'; }
function closeFormModal() { document.getElementById('form-overlay').style.display = 'none'; fType = ''; fEditId = null; }
function closeFormOnBg(e) { if (e.target === document.getElementById('form-overlay')) closeFormModal(); }

/* ─── HELPERS ─── */
function logoutAdmin() { localStorage.removeItem('token'); window.location.href = '/'; }
function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
function esc(s) { return (s||'').replace(/'/g, "\\'"); }
function emptyState(icon, msg) {
    return `<div class="empty-state"><div class="ei">${icon}</div><p>${msg}</p></div>`;
}

checkAuth();
</script>
</body>
</html>
