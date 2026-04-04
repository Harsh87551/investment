<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,opsz,wght@0,14..32,400;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900&family=Space+Mono:wght@400;700&display=swap');

        :root {
            --bg: #050507;
            --surface: #0d0d10;
            --card: rgba(255,255,255,0.03);
            --card-hover: rgba(255,255,255,0.05);
            --border: rgba(255,255,255,0.07);
            --border-glow: rgba(99,102,241,0.3);
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --accent-glow: rgba(99,102,241,0.15);
            --success: #34d399;
            --success-bg: rgba(52,211,153,0.08);
            --warning: #fbbf24;
            --warning-bg: rgba(251,191,36,0.08);
            --error: #ef4444;
            --error-bg: rgba(239,68,68,0.08);
            --text-main: #f4f4f5;
            --text-sub: #a1a1aa;
            --text-muted: #52525b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            background-image: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(99,102,241,0.08), transparent);
        }

        #admin-layout {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--surface);
            box-shadow: 0 0 100px rgba(0,0,0,0.8), 0 0 0 1px var(--border);
            position: relative;
            overflow: hidden;
        }

        /* ─── SIDEBAR (Drawer Mode) ─── */
        .admin-sidebar {
            width: 280px;
            background: #09090b;
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: absolute;
            left: -280px;
            top: 0;
            height: 100%;
            z-index: 1000;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 20px 0 50px rgba(0,0,0,0.5);
        }

        .admin-sidebar.open {
            transform: translateX(280px);
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-sidebar {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
        }
        .close-sidebar:hover { background: rgba(255,255,255,0.1); color: #fff; }

        .logo h1 {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #818cf8, #6366f1, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: left;
        }

        .nav-links {
            list-style: none;
            padding: 24px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-item {
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--text-sub);
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-item.active {
            color: #fff;
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.2);
            box-shadow: inset 0 0 20px rgba(99, 102, 241, 0.05);
        }

        .nav-item.active::after {
            content: '';
            position: absolute;
            right: 12px;
            width: 5px;
            height: 5px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--accent);
        }

        .nav-divider {
            height: 1px;
            background: var(--border);
            margin: 12px 16px;
        }

        /* ─── MOBILE TOGGLE & OVERLAY ─── */
        .sidebar-overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 900;
        }
        .sidebar-overlay.active { display: block; }

        .sidebar-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 18px;
            left: 16px;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--card);
            color: var(--text-main);
            z-index: 500;
            border: 1px solid var(--border);
            cursor: pointer;
            font-size: 18px;
            transition: all 0.2s;
        }
        .sidebar-toggle:hover { background: var(--card-hover); border-color: var(--accent); }

        /* ─── STATS GRID (Dashboard) ─── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px 16px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            background: var(--card-hover);
            border-color: var(--border-glow);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }
        .stat-card h3 {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }
        .stat-card .value {
            font-family: 'Space Mono', monospace;
            font-size: 22px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        /* ─── MAIN CONTENT ─── */
        .admin-main { 
            flex: 1; 
            padding: 80px 16px 40px; 
            width: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        .header h2 {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -0.3px;
            background: linear-gradient(135deg, #fff 60%, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            white-space: nowrap;
        }
        .header .user-id {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            color: var(--text-muted);
            padding: 5px 10px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            white-space: nowrap;
        }

        /* ─── SECTION TRANSITIONS ─── */
        .section-container { display: none; width: 100%; }
        .section-container.active { display: block; animation: fadeUp 0.3s cubic-bezier(0.4,0,0.2,1); }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* ─── SECTION HEADER ─── */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .section-header h3 {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-main);
        }
        .add-btn {
            padding: 9px 18px;
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 4px 15px rgba(99,102,241,0.25);
            letter-spacing: 0.2px;
            white-space: nowrap;
        }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99,102,241,0.4); }
        .add-btn:active { transform: translateY(0); }

        /* ─── TABLE ─── */
        .admin-table-wrapper {
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow-x: auto;
            overflow-y: hidden;
        }
        table { width: 100%; border-collapse: collapse; min-width: 460px; }
        thead { background: rgba(255,255,255,0.02); }
        th {
            text-align: left;
            padding: 12px 14px;
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        td {
            padding: 14px;
            font-size: 12.5px;
            color: var(--text-sub);
            border-bottom: 1px solid rgba(255,255,255,0.03);
            vertical-align: middle;
            white-space: nowrap;
        }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: background 0.15s; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }

        .user-id { font-family: 'Space Mono', monospace; color: var(--accent); font-weight: 700; font-size: 11px; }
        .amount-td { font-weight: 800; color: #fff; font-size: 13px; }

        /* ─── BUTTONS ─── */
        .btn-group { display: flex; gap: 5px;}
        .btn {
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            transition: all 0.2s;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }
        .btn:active { transform: scale(0.95); }

        .btn-approve { background: var(--success-bg); color: var(--success); border: 1px solid rgba(52,211,153,0.15); }
        .btn-approve:hover { background: var(--success); color: #022c22; box-shadow: 0 4px 12px rgba(52,211,153,0.3); }

        .btn-reject { background: var(--error-bg); color: var(--error); border: 1px solid rgba(239,68,68,0.15); }
        .btn-reject:hover { background: var(--error); color: #fff; box-shadow: 0 4px 12px rgba(239,68,68,0.3); }

        .btn-view { background: var(--accent-glow); color: #818cf8; border: 1px solid rgba(99,102,241,0.2); }
        .btn-view:hover { background: var(--accent); color: #fff; box-shadow: 0 4px 12px rgba(99,102,241,0.3); }

        .btn-warn { background: var(--warning-bg); color: var(--warning); border: 1px solid rgba(251,191,36,0.15); }
        .btn-warn:hover { background: var(--warning); color: #1a0; box-shadow: 0 4px 12px rgba(251,191,36,0.3); }

        /* ─── STATUS BADGES ─── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; }
        .status-pending { background: var(--warning-bg); color: var(--warning); border: 1px solid rgba(251,191,36,0.15); }
        .status-pending::before { background: var(--warning); box-shadow: 0 0 6px var(--warning); }
        .status-completed { background: var(--success-bg); color: var(--success); border: 1px solid rgba(52,211,153,0.15); }
        .status-completed::before { background: var(--success); box-shadow: 0 0 6px var(--success); }
        .status-rejected { background: var(--error-bg); color: var(--error); border: 1px solid rgba(239,68,68,0.15); }
        .status-rejected::before { background: var(--error); box-shadow: 0 0 6px var(--error); }
        .status-active { background: var(--success-bg); color: var(--success); border: 1px solid rgba(52,211,153,0.15); }
        .status-active::before { background: var(--success); box-shadow: 0 0 6px var(--success); animation: pulse 2s infinite; }
        .status-inactive { background: rgba(255,255,255,0.04); color: var(--text-muted); border: 1px solid var(--border); }
        .status-inactive::before { background: var(--text-muted); }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        /* ─── IMAGE MODAL ─── */
        .admin-modal {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 1000; display: none;
            justify-content: center; align-items: center; padding: 24px;
        }
        .modal-content {
            max-width: 100%; max-height: 85vh;
            background: #111;
            border-radius: 20px; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 40px 100px rgba(0,0,0,0.8);
        }
        .modal-content img { display: block; width: 100%; height: auto; max-height: 80vh; object-fit: contain; }
        .close-modal {
            position: absolute; top: 20px; right: 20px;
            padding: 10px 18px; background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px); color: #fff;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 12px;
            transition: 0.2s; letter-spacing: 0.5px;
        }
        .close-modal:hover { background: rgba(255,255,255,0.2); }

        /* ─── FORM MODAL ─── */
        .form-modal {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 1000; display: none;
            justify-content: center; align-items: flex-end;
            padding: 0;
        }
        .form-box {
            background: #111;
            border: 1px solid rgba(255,255,255,0.08);
            border-bottom: none;
            border-radius: 24px 24px 0 0;
            padding: 28px 20px 36px;
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        @keyframes slideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .form-drag-handle {
            width: 40px; height: 4px;
            background: rgba(255,255,255,0.15);
            border-radius: 2px;
            margin: 0 auto 24px;
        }
        .form-box h3 { margin-bottom: 20px; font-size: 17px; font-weight: 800; }
        .form-input {
            width: 100%; padding: 14px 16px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px; color: #fff;
            font-family: 'Plus Jakarta Sans'; font-size: 14px; margin-bottom: 12px;
            outline: none; transition: all 0.2s;
        }
        .form-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        .form-input::placeholder { color: var(--text-muted); }
        .form-actions { display: flex; gap: 10px; margin-top: 4px; }
        .form-btn {
            flex: 1; padding: 15px; border: none; border-radius: 14px;
            font-weight: 800; font-size: 14px; cursor: pointer; transition: all 0.2s;
        }
        .form-btn-primary { background: linear-gradient(135deg, var(--accent), #7c3aed); color: #fff; box-shadow: 0 4px 20px rgba(99,102,241,0.3); }
        .form-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 30px rgba(99,102,241,0.4); }
        .form-btn-cancel { background: rgba(255,255,255,0.06); color: var(--text-sub); border: 1px solid var(--border); }
        .form-btn-cancel:hover { background: rgba(255,255,255,0.1); color: #fff; }

        /* ─── AUTH ERROR ─── */
        #auth-error {
            display: none; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; gap: 20px; padding: 40px;
            max-width: 500px; margin: 0 auto; min-height: 100vh; background: var(--surface);
            box-shadow: 0 0 80px rgba(0,0,0,0.9);
        }
        .auth-error-icon { font-size: 64px; }
        #auth-error h1 { font-size: 26px; color: var(--text-main); font-weight: 900; }
        .auth-badge { display: inline-block; padding: 4px 12px; background: var(--error-bg); color: var(--error); border: 1px solid rgba(239,68,68,0.2); border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        #auth-error p { color: var(--text-muted); font-size: 14px; line-height: 1.7; max-width: 300px; }
        .re-login-btn { padding: 16px 32px; background: linear-gradient(135deg, var(--accent), #7c3aed); color: #fff; text-decoration: none; border-radius: 16px; font-weight: 800; font-size: 14px; transition: all 0.3s; box-shadow: 0 8px 30px rgba(99,102,241,0.3); }
        .re-login-btn:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(99,102,241,0.5); }
        @media (max-width: 500px) {
            #admin-layout { box-shadow: none; }
        }
    </style>
</head>
<body>

<div id="admin-layout" style="display: none;">
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo"><h1>💹 ADMIN PANEL</h1></div>
            <button class="close-sidebar" onclick="toggleSidebar()">✕</button>
        </div>
        <ul class="nav-links">
            <li class="nav-item active" data-tab="dashboard" onclick="showSection('dashboard')">📊 Analytics</li>
            <li class="nav-item" data-tab="users" onclick="showSection('users')">👥 User Directory</li>
            <div class="nav-divider"></div>
            <li class="nav-item" data-tab="kyc" onclick="showSection('kyc')">🔍 KYC Verification</li>
            <li class="nav-item" data-tab="deposits" onclick="showSection('deposits')">💳 Deposit Proofs</li>
            <li class="nav-item" data-tab="withdrawals" onclick="showSection('withdrawals')">💸 Withdrawal Requests</li>
            <div class="nav-divider"></div>
            <li class="nav-item" data-tab="packages" onclick="showSection('packages')">📦 Plan Packages</li>
            <li class="nav-item" data-tab="plans" onclick="showSection('plans')">📈 Interest Tiers</li>
            <li class="nav-item" data-tab="upi" onclick="showSection('upi')">🏦 Payment Settings</li>
            <div class="nav-divider"></div>
            <li class="nav-item" onclick="logoutAdmin()" style="color: var(--error); margin-top: auto;">🚪 Exit Panel</li>
        </ul>
    </aside>

    <main class="admin-main">
        <div class="header">
            <h2>Investment Management</h2>
        </div>

        <!-- ===== DASHBOARD ===== -->
        <section id="dashboard-section" class="section-container active">
            <div class="stats-grid">
                <div class="stat-card"><h3>Total Users</h3><div id="stat-users" class="value">0</div></div>
                <div class="stat-card"><h3>Pending KYC</h3><div id="stat-kyc" class="value" style="color:var(--warning)">0</div></div>
                <div class="stat-card"><h3>Platform Volume</h3><div id="stat-volume" class="value" style="color:var(--success)">$0</div></div>
                <div class="stat-card"><h3>Active Packages</h3><div id="stat-packages" class="value" style="color:var(--accent)">0</div></div>
                <div class="stat-card"><h3>Pending Withdrawals</h3><div id="stat-withdrawals" class="value" style="color:var(--error)">0</div></div>
            </div>
            <div class="admin-table-wrapper">
                <table><thead><tr><th>ID</th><th>NAME</th><th>EMAIL</th><th>PHONE</th><th>BANK DETAILS</th><th>JOINED</th><th>ACTIONS</th></tr></thead>
                <tbody id="recent-users-list"></tbody></table>
            </div>
        </section>
       

        <!-- ===== USERS ===== -->
        <section id="users-section" class="section-container">
            <div class="section-header"><h3>All Registered Users</h3></div>
            <div class="admin-table-wrapper">
                <table><thead><tr><th>ID</th><th>NAME</th><th>EMAIL</th><th>PHONE</th><th>BANK DETAILS</th><th>JOINED</th><th>ACTIONS</th></tr></thead>
                <tbody id="all-users-list"></tbody></table>
            </div>
        </section>
       

        <!-- ===== KYC ===== -->
        <section id="kyc-section" class="section-container">
            <div class="section-header"><h3>KYC Verification Requests</h3></div>
            <div class="admin-table-wrapper">
                <table><thead><tr><th>USER</th><th>PAN #</th><th>PAN IMG</th><th>AADHAAR #</th><th>AADHAAR IMG</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
                <tbody id="kyc-list"></tbody></table>
            </div>
        </section>
       

        <!-- ===== DEPOSITS ===== -->
        <section id="deposits-section" class="section-container">
            <div class="section-header"><h3>Investment Deposit Proofs</h3></div>
            <div class="admin-table-wrapper">
                <table><thead><tr><th>USER</th><th>AMOUNT</th><th>PACKAGE</th><th>TRIP</th><th>UTR / TXN</th><th>DATE</th><th>STATUS</th><th>SCREENSHOT</th><th>ACTIONS</th></tr></thead>
                <tbody id="deposits-list"></tbody></table>
            </div>
        </section>

        <!-- ===== WITHDRAWALS ===== -->
        <section id="withdrawals-section" class="section-container">
            <div class="section-header"><h3>Withdrawal Requests</h3></div>
            <div class="admin-table-wrapper">
                <table><thead><tr><th>USER</th><th>NAME</th><th>AMOUNT</th><th>PACKAGE</th><th>BANK DETAILS</th><th>METHOD</th><th>REQUESTED</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
                <tbody id="withdrawals-list"></tbody></table>
            </div>
        </section>
       

        <!-- ===== PACKAGES ===== -->
        <section id="packages-section" class="section-container">
            <div class="section-header">
                <h3>Investment Packages</h3>
                <button class="add-btn" onclick="openPackageForm()">+ Add Package</button>
            </div>
            <div class="admin-table-wrapper">
                <table><thead><tr><th>ID</th><th>NAME</th><th>STRATEGY</th><th>TRIP REWARD</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
                <tbody id="packages-list"></tbody></table>
            </div>
        </section>

        <!-- ===== PLANS ===== -->
        <section id="plans-section" class="section-container">
            <div class="section-header">
                <h3>Investment Plans (Amounts)</h3>
                <button class="add-btn" onclick="openPlanForm()">+ Add Plan</button>
            </div>
            <div class="admin-table-wrapper">
                <table><thead><tr><th>ID</th><th>AMOUNT ($)</th><th>RETURN %</th><th>MONTHLY PROFIT</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
                <tbody id="plans-list"></tbody></table>
            </div>
        </section>

        <!-- ===== UPI ===== -->
        <section id="upi-section" class="section-container">
            <div class="section-header">
                <h3>UPI Payment IDs</h3>
                <button class="add-btn" onclick="openUpiForm()">+ Add UPI</button>
            </div>
            <div class="admin-table-wrapper">
                <table><thead><tr><th>ID</th><th>UPI ADDRESS</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
                <tbody id="upi-list"></tbody></table>
            </div>
        </section>
    </main>
</div>

<div id="auth-error">
    <div class="auth-error-icon">🔒</div>
    <span class="auth-badge">Access Denied</span>
    <h1>Admin Only</h1>
    <p>This area is reserved for the account with User ID 1 only.</p>
    <a href="/" class="re-login-btn">← Back to App</a>
</div>

<!-- Image Preview Modal -->
<div class="admin-modal" id="image-modal" onclick="closeModal()">
    <div class="modal-content"><img id="modal-img" src="" alt="Preview"></div>
    <div class="close-modal">✕ CLOSE</div>
</div>

<!-- Form Modal -->
<div class="form-modal" id="form-modal">
    <div class="form-box">
        <div class="form-drag-handle"></div>
        <h3 id="form-title">Add Item</h3>
        <div id="form-fields"></div>
        <div class="form-actions">
            <button class="form-btn form-btn-cancel" onclick="closeFormModal()">Cancel</button>
            <button class="form-btn form-btn-primary" id="form-submit-btn" onclick="submitForm()">Save Changes</button>
        </div>
    </div>
</div>

<script>
const token = localStorage.getItem('token');
const authHeaders = { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' };
let currentFormType = '';
let currentEditId = null;

// ========== AUTH ==========
function checkAuth() {
    if (!token) { document.getElementById('auth-error').style.display = 'flex'; return; }
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        if (payload.id !== 1) { document.getElementById('auth-error').style.display = 'flex'; return; }
        document.getElementById('admin-layout').style.display = 'block';
        loadAll();
    } catch (e) { document.getElementById('auth-error').style.display = 'flex'; }
}

function loadAll() {
    fetchStats(); fetchUsers(); fetchKycRequests(); fetchDepositRequests();
    fetchWithdrawals();
    fetchPackages(); fetchPlans(); fetchUpiList();
}

function showSection(id) {
    document.querySelectorAll('.section-container').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    const section = document.getElementById(id + '-section');
    if (section) section.classList.add('active');
    const navItem = document.querySelector(`.nav-item[data-tab="${id}"]`);
    if (navItem) navItem.classList.add('active');
    
    // Always close sidebar when a section is selected
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('active');
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
}

// ========== API HELPER ==========
async function adminFetch(url, body) {
    const res = await fetch(url, { method: 'POST', headers: authHeaders, body: JSON.stringify(body) });
    return await res.json();
}

// ========== STATS ==========
async function fetchStats() {
    try {
        const data = await adminFetch('api/stats_admin.php', {});
        if (data.success) {
            document.getElementById('stat-users').innerText = data.stats.totalUsers-1;
            document.getElementById('stat-kyc').innerText = data.stats.pendingKyc;
            document.getElementById('stat-volume').innerText = '$' + Number(data.stats.totalVolume).toLocaleString('en-IN');

            const list = document.getElementById('recent-users-list');
            list.innerHTML = '';
            (data.users || []).forEach(u => {
                list.innerHTML += `<tr><td class="user-id">${u.id}</td><td>${u.name}</td><td>${u.email}</td><td>${u.phone || '-'}</td><td>${u.createdon || '-'}</td><td>${u.id != 1 ? `<button class="btn btn-reject" onclick="deleteUser(${u.id})">Delete</button>` : '<span style="color:var(--text-muted);font-size:10px">Admin</span>'}</td></tr>`;
            });
        }
        // Get withdrawals count for stats
        const withData = await adminFetch('api/withdraw_admin.php', { action: 'list' });
        if (withData.success) {
            const pending = withData.withdrawals.filter(w => w.w_status == 1).length;
            document.getElementById('stat-withdrawals').innerText = pending;
        }
        // Also get package count
        const pkgData = await adminFetch('api/package_admin.php', { action: 'list' });
        if (pkgData.success) {
            const active = pkgData.packages.filter(p => p.status == 1 && p.hide == 0).length;
            document.getElementById('stat-packages').innerText = active;
        }
    } catch (e) { console.error('Stats error', e); }
}

// ========== USERS ==========
async function fetchUsers() {
    const list = document.getElementById('all-users-list');
    try {
        const data = await adminFetch('api/users_admin.php', { action: 'list' });
        list.innerHTML = '';
        if (!data.users || data.users.length === 0) { list.innerHTML = '<tr><td colspan="6" style="text-align:center">No users found.</td></tr>'; return; }
        data.users.forEach(u => {
            const bankInfo = u.account ? `
                <div style="font-size:10px;line-height:1.2;color:var(--text-main)">${u.account}</div>
                <div style="font-size:10px;color:var(--text-muted)">${u.bankname} | ${u.ifsc_code}</div>
            ` : '<span style="color:var(--text-muted);font-size:10px">No details</span>';

            list.innerHTML += `<tr>
                <td class="user-id">${u.id}</td>
                <td>${u.name}</td>
                <td>${u.email}</td>
                <td>${u.phone || '-'}</td>
                <td>${bankInfo}</td>
                <td>${u.createdon || '-'}</td>
                <td>${u.id != 1 ? `<button class="btn btn-reject" onclick="deleteUser(${u.id})">Delete</button>` : '<span style="color:var(--text-muted);font-size:10px">Admin</span>'}</td>
            </tr>`;
        });
    } catch (e) { list.innerHTML = '<tr><td colspan="6" style="color:red">Failed to load users.</td></tr>'; }
}

async function deleteUser(id) {
    if (!confirm('Permanently delete this user and all their data? This cannot be undone.')) return;
    try {
        await adminFetch('api/users_admin.php', { action: 'delete', id });
        fetchUsers(); fetchStats();
    } catch (e) { alert('Failed to delete user.'); }
}

// ========== KYC ==========
async function fetchKycRequests() {
    const list = document.getElementById('kyc-list');
    try {
        const data = await adminFetch('api/kyc_admin.php', { action: 'list' });
        list.innerHTML = '';
        if (!data.kyc || data.kyc.length === 0) { list.innerHTML = '<tr><td colspan="7" style="text-align:center">No KYC requests.</td></tr>'; return; }
        data.kyc.forEach(k => {
            const statusClass = k.status == 1 ? 'pending' : (k.status == 2 ? 'completed' : 'rejected');
            const statusText = k.status == 1 ? 'Reviewing' : (k.status == 2 ? 'Verified' : 'Rejected');
            list.innerHTML += `<tr>
                <td class="user-id">U-${k.user_id}</td>
                <td style="font-family:'Space Mono';font-size:12px">${k.pan_number}</td>
                <td><button class="btn btn-view" onclick="openModal('../backend/uploads/kyc/${k.pan_image}')">View PAN</button></td>
                <td style="font-family:'Space Mono';font-size:12px">${k.aadhaar}</td>
                <td><button class="btn btn-view" onclick="openModal('../backend/uploads/kyc/${k.aadhar_image}')">View Aadhaar</button></td>
                <td><span class="status-badge status-${statusClass}">${statusText}</span></td>
                <td class="btn-group">
                    <button class="btn btn-approve" onclick="updateKyc(${k.user_id},2)">Approve</button>
                    <button class="btn btn-reject" onclick="updateKyc(${k.user_id},3)">Reject</button>
                    <button class="btn btn-reject" onclick="deleteKyc(${k.user_id})">Delete</button>
                </td>
            </tr>`;
        });
    } catch (e) { list.innerHTML = '<tr><td colspan="7" style="color:red">Failed to load KYC.</td></tr>'; }
}

async function updateKyc(userId, status) {
    if (!confirm('Update this KYC status?')) return;
    await adminFetch('api/kyc_admin.php', { action: 'update', userId, status });
    fetchKycRequests(); fetchStats();
}

async function deleteKyc(userId) {
    if (!confirm('Permanently delete this KYC record? This cannot be undone.')) return;
    try {
        await adminFetch('api/kyc_admin.php', { action: 'delete', userId });
        fetchKycRequests(); fetchStats();
    } catch (e) { alert('Failed to delete KYC record.'); }
}

// ========== DEPOSITS ==========
async function fetchDepositRequests() {
    const list = document.getElementById('deposits-list');
    try {
        const data = await adminFetch('api/deposit_admin.php', { action: 'list' });
        list.innerHTML = '';
        if (!data.deposits || data.deposits.length === 0) { list.innerHTML = '<tr><td colspan="9" style="text-align:center">No deposits.</td></tr>'; return; }
        data.deposits.forEach(d => {
            const statusClass = d.paystatus == 0 ? 'pending' : (d.paystatus == 1 ? 'completed' : 'rejected');
            const statusText = d.paystatus == 0 ? 'Reviewing' : (d.paystatus == 1 ? 'Active' : 'Rejected');
            const amt = d.amount ? parseFloat(d.amount).toLocaleString('en-IN') : '0';
            const pkgName = d.package_name || d.payment_details || '-';
            const tripName = d.trip || '-';
            const screenshotPath = `../backend/uploads/payments/${d.screenshot}`;
            let actionsHtml = '';
            if (d.paystatus == 0) { // Reviewing
                actionsHtml = `
                    <button class="btn btn-approve" onclick="updateDeposit(${d.id},1)">Approve</button>
                    <button class="btn btn-reject" onclick="updateDeposit(${d.id},2)">Reject</button>`;
            } else if (d.paystatus == 1) { // Approved
                actionsHtml = `
                    <button class="btn btn-view" style="color:#f59e0b;border-color:#f59e0b" onclick="updateDeposit(${d.id},0)">Reviewing</button>
                    <button class="btn btn-reject" onclick="updateDeposit(${d.id},2)">Reject</button>`;
            } else { // Rejected
                actionsHtml = `
                    <button class="btn btn-approve" onclick="updateDeposit(${d.id},1)">Approve</button>
                    <button class="btn btn-view" style="color:#f59e0b;border-color:#f59e0b" onclick="updateDeposit(${d.id},0)">Reviewing</button>`;
            }

            list.innerHTML += `<tr>
                <td class="user-id">U-${d.user_id}</td>
                <td class="amount-td">$${amt}</td>
                <td><span style="font-size:11px;font-weight:700;color:var(--text-main)">${pkgName}</span></td>
                <td><span style="font-size:11px;font-weight:600;color:var(--warning);text-transform:capitalize">${tripName}</span></td>
                <td style="font-family:'Space Mono';font-size:11px;color:var(--accent)">${d.transcation_id || '-'}</td>
                <td>${d.createdon || '-'}</td>
                <td><span class="status-badge status-${statusClass}">${statusText}</span></td>
                <td>${d.screenshot ? `<img src="${screenshotPath}" alt="Proof" style="width:40px;height:40px;object-fit:cover;border-radius:8px;border:1px solid var(--border);cursor:pointer" onclick="openModal('${screenshotPath}')" />` : '-'}</td>
                <td class="btn-group">
                    ${actionsHtml}
                    <button class="btn btn-reject" onclick="deleteDeposit(${d.id})">Delete</button>
                </td>
            </tr>`;
        });
    } catch (e) { list.innerHTML = '<tr><td colspan="9" style="color:red">Failed to load deposits.</td></tr>'; }
}

async function updateDeposit(id, status) {
    if (!confirm('Update this deposit status?')) return;
    await adminFetch('api/deposit_admin.php', { action: 'update', id, status });
    fetchDepositRequests(); fetchStats();
}

async function deleteDeposit(id) {
    if (!confirm('Permanently delete this deposit record? This cannot be undone.')) return;
    try {
        await adminFetch('api/deposit_admin.php', { action: 'delete', id });
        fetchDepositRequests(); fetchStats();
    } catch (e) { alert('Failed to delete deposit.'); }
}

// ========== WITHDRAWALS ==========
async function fetchWithdrawals() {
    const list = document.getElementById('withdrawals-list');
    try {
        const data = await adminFetch('api/withdraw_admin.php', { action: 'list' });
        list.innerHTML = '';
        if (!data.withdrawals || data.withdrawals.length === 0) { list.innerHTML = '<tr><td colspan="8" style="text-align:center">No withdrawal requests found.</td></tr>'; return; }
        data.withdrawals.forEach(w => {
            const statusClass = w.w_status == 1 ? 'pending' : (w.w_status == 2 ? 'completed' : 'rejected');
            const statusText = w.w_status == 1 ? 'Requested' : (w.w_status == 2 ? 'Paid' : 'Rejected');
            const amt = w.amount ? parseFloat(w.amount).toLocaleString('en-IN') : '0';
            const method = w.w_method || 'Bank Transfer';

            let actionsHtml = '';
            if (w.w_status == 1) { // Requested
                actionsHtml = `
                    <button class="btn btn-approve" onclick="updateWithdrawal(${w.id},2)">Approve (Paid)</button>
                    <button class="btn btn-reject" onclick="updateWithdrawal(${w.id},3)">Reject</button>`;
            } else if (w.w_status == 2) { // Approved
                actionsHtml = `<button class="btn btn-reject" onclick="updateWithdrawal(${w.id},3)">Reject</button>`;
            } else { // Rejected
                actionsHtml = `<button class="btn btn-approve" onclick="updateWithdrawal(${w.id},2)">Approve (Paid)</button>`;
            }

            list.innerHTML += `<tr>
                <td class="user-id">U-${w.user_id}</td>
                <td><span style="font-size:11px;font-weight:600">${w.user_name || 'User'}</span></td>
                <td class="amount-td">$${amt}</td>
                <td><span style="font-size:11px;font-weight:700;color:var(--text-main)">${w.package_name || '-'}</span></td>
                <td>
                    <div style="font-size:10px;line-height:1.2;color:var(--text-main)">${w.w_account || '-'}</div>
                    <div style="font-size:10px;color:var(--text-muted)">${w.w_bankname || '-'} | ${w.w_ifsc_code || '-'}</div>
                </td>
                <td><span style="font-size:11px;font-weight:600;color:var(--accent);text-transform:capitalize">${method}</span></td>
                <td>${w.w_requested || '-'}</td>
                <td><span class="status-badge status-${statusClass}">${statusText}</span></td>
                <td class="btn-group">
                    ${actionsHtml}
                    <button class="btn btn-reject" onclick="deleteWithdrawal(${w.id})">Delete</button>
                </td>
            </tr>`;
        });
    } catch (e) { list.innerHTML = '<tr><td colspan="8" style="color:red">Failed to load withdrawals.</td></tr>'; }
}

async function updateWithdrawal(id, status) {
    if (!confirm('Update this withdrawal status?')) return;
    await adminFetch('api/withdraw_admin.php', { action: 'update', id, status });
    fetchWithdrawals(); fetchStats();
}

async function deleteWithdrawal(id) {
    if (!confirm('Remove this withdrawal request? This will reset the withdrawal status for this package.')) return;
    try {
        await adminFetch('api/withdraw_admin.php', { action: 'delete', id });
        fetchWithdrawals(); fetchStats();
    } catch (e) { alert('Failed to delete withdrawal request.'); }
}

// ========== PACKAGES ==========
async function fetchPackages() {
    const list = document.getElementById('packages-list');
    try {
        const data = await adminFetch('api/package_admin.php', { action: 'list' });
        list.innerHTML = '';
        if (!data.packages || data.packages.length === 0) { list.innerHTML = '<tr><td colspan="6" style="text-align:center">No packages.</td></tr>'; return; }
        data.packages.forEach(p => {
            const active = p.status == 1 && p.hide == 0;
            list.innerHTML += `<tr>
                <td class="user-id">${p.id}</td>
                <td>${p.name}</td>
                <td>${p.strategy}</td>
                <td>${p.trip || '-'}</td>
                <td><span class="status-badge ${active ? 'status-active' : 'status-inactive'}">${active ? 'Active' : 'Hidden'}</span></td>
                <td class="btn-group">
                    <button class="btn btn-view" onclick="editPackage(${p.id},'${esc(p.name)}','${esc(p.strategy)}','${esc(p.trip)}',${p.status})">Edit</button>
                    ${active
                        ? `<button class="btn btn-warn" onclick="deletePackage(${p.id})">Hide</button>`
                        : `<button class="btn btn-approve" onclick="showPackage(${p.id},'${esc(p.name)}','${esc(p.strategy)}','${esc(p.trip)}')">Show</button>`
                    }
                    <button class="btn btn-reject" onclick="removePackage(${p.id})">Delete</button>
                </td>
            </tr>`;
        });
    } catch (e) { list.innerHTML = '<tr><td colspan="6" style="color:red">Failed to load packages.</td></tr>'; }
}

function openPackageForm(id, name, strategy, trip, status) {
    currentFormType = 'package';
    currentEditId = id || null;
    document.getElementById('form-title').innerText = id ? 'Edit Package' : 'Add New Package';
    document.getElementById('form-fields').innerHTML = `
        <input class="form-input" id="f-pkg-name" placeholder="Package Name (e.g. Monthly)" value="${name || ''}">
        <input class="form-input" id="f-pkg-strategy" placeholder="Strategy (e.g. aggressive)" value="${strategy || ''}">
        <input class="form-input" id="f-pkg-trip" placeholder="Trip Reward (e.g. Dubai)" value="${trip || ''}">
    `;
    document.getElementById('form-modal').style.display = 'flex';
}

function editPackage(id, name, strategy, trip, status) {
    openPackageForm(id, name, strategy, trip, status);
}

async function deletePackage(id) {
    if (!confirm('Hide this package?')) return;
    await adminFetch('api/package_admin.php', { action: 'delete', id });
    fetchPackages(); fetchStats();
}

async function showPackage(id, name, strategy, trip) {
    if (!confirm('Make this package visible to users again?')) return;
    await adminFetch('api/package_admin.php', { action: 'update', id, hide: 0, status: 1, name, strategy, trip });
    fetchPackages(); fetchStats();
}

async function removePackage(id) {
    if (!confirm('Permanently delete this package? This cannot be undone.')) return;
    await adminFetch('api/package_admin.php', { action: 'remove', id });
    fetchPackages(); fetchStats();
}

// ========== PLANS ==========
async function fetchPlans() {
    const list = document.getElementById('plans-list');
    try {
        const data = await adminFetch('api/plan_admin.php', { action: 'list' });
        list.innerHTML = '';
        if (!data.plans || data.plans.length === 0) { list.innerHTML = '<tr><td colspan="6" style="text-align:center">No plans.</td></tr>'; return; }
        data.plans.forEach(p => {
            const active = p.status == 1 && p.hide == 0;
            const amt = parseFloat(p.amount);
            const pct = parseFloat(p.percentage);
            const monthly = (amt * pct / 100).toFixed(0);
            list.innerHTML += `<tr>
                <td class="user-id">${p.id}</td>
                <td class="amount-td">$${amt.toLocaleString('en-IN')}</td>
                <td style="color:var(--success);font-weight:700">${pct}%</td>
                <td style="color:var(--warning);font-weight:700">$${Number(monthly).toLocaleString('en-IN')}</td>
                <td><span class="status-badge ${active ? 'status-active' : 'status-inactive'}">${active ? 'Active' : 'Hidden'}</span></td>
                <td class="btn-group">
                    <button class="btn btn-view" onclick="editPlan(${p.id},${amt},${pct},${p.status})">Edit</button>
                    ${active
                        ? `<button class="btn btn-warn" onclick="deletePlan(${p.id})">Hide</button>`
                        : `<button class="btn btn-approve" onclick="showPlan(${p.id},${amt},${pct})">Show</button>`
                    }
                    <button class="btn btn-reject" onclick="removePlan(${p.id})">Delete</button>
                </td>
            </tr>`;
        });
    } catch (e) { list.innerHTML = '<tr><td colspan="6" style="color:red">Failed to load plans.</td></tr>'; }
}

function openPlanForm(id, amount, percentage, status) {
    currentFormType = 'plan';
    currentEditId = id || null;
    document.getElementById('form-title').innerText = id ? 'Edit Plan' : 'Add New Plan';
    document.getElementById('form-fields').innerHTML = `
        <input class="form-input" id="f-plan-amount" type="number" placeholder="Investment Amount ($)" value="${amount || ''}">
        <input class="form-input" id="f-plan-pct" type="number" step="0.1" placeholder="Return Percentage (%)" value="${percentage || ''}">
    `;
    document.getElementById('form-modal').style.display = 'flex';
}

function editPlan(id, amount, pct, status) {
    openPlanForm(id, amount, pct, status);
}

async function deletePlan(id) {
    if (!confirm('Hide this plan?')) return;
    await adminFetch('api/plan_admin.php', { action: 'delete', id });
    fetchPlans();
}

async function showPlan(id, amount, percentage) {
    if (!confirm('Make this plan visible to users again?')) return;
    await adminFetch('api/plan_admin.php', { action: 'update', id, amount, percentage, hide: 0, status: 1 });
    fetchPlans();
}

async function removePlan(id) {
    if (!confirm('Permanently delete this plan? This cannot be undone.')) return;
    await adminFetch('api/plan_admin.php', { action: 'remove', id });
    fetchPlans();
}

// ========== UPI ==========
async function fetchUpiList() {
    const list = document.getElementById('upi-list');
    try {
        const data = await adminFetch('api/upi_admin.php', { action: 'list' });
        list.innerHTML = '';
        if (!data.upis || data.upis.length === 0) { list.innerHTML = '<tr><td colspan="4" style="text-align:center">No UPI IDs.</td></tr>'; return; }
        data.upis.forEach(u => {
            const active = u.status == 1 && u.hide == 0;
            list.innerHTML += `<tr>
                <td class="user-id">${u.id}</td>
                <td style="font-family:'Space Mono';color:var(--accent)">${u.upi_id}</td>
                <td><span class="status-badge ${active ? 'status-active' : 'status-inactive'}">${active ? 'Active' : 'Inactive'}</span></td>
                <td class="btn-group">
                    <button class="btn btn-view" onclick="editUpi(${u.id},'${esc(u.upi_id)}')">Edit</button>
                    <button class="btn btn-approve" onclick="activateUpi(${u.id})">Set Active</button>
                    <button class="btn btn-reject" onclick="deleteUpi(${u.id})">Remove</button>
                </td>
            </tr>`;
        });
    } catch (e) { list.innerHTML = '<tr><td colspan="4" style="color:red">Failed to load UPI.</td></tr>'; }
}

function openUpiForm(id, upiId) {
    currentFormType = 'upi';
    currentEditId = id || null;
    document.getElementById('form-title').innerText = id ? 'Edit UPI ID' : 'Add New UPI ID';
    document.getElementById('form-fields').innerHTML = `
        <input class="form-input" id="f-upi-id" placeholder="UPI ID (e.g. name@upi)" value="${upiId || ''}">
    `;
    document.getElementById('form-modal').style.display = 'flex';
}

function editUpi(id, upiId) {
    openUpiForm(id, upiId);
}

async function activateUpi(id) {
    if (!confirm('Set this UPI as active?')) return;
    await adminFetch('api/upi_admin.php', { action: 'activate', id });
    fetchUpiList();
}

async function deleteUpi(id) {
    if (!confirm('Remove this UPI?')) return;
    await adminFetch('api/upi_admin.php', { action: 'delete', id });
    fetchUpiList();
}

// ========== FORM SUBMIT ==========
async function submitForm() {
    try {
        if (currentFormType === 'package') {
            const body = {
                action: currentEditId ? 'update' : 'add',
                name: document.getElementById('f-pkg-name').value,
                strategy: document.getElementById('f-pkg-strategy').value,
                trip: document.getElementById('f-pkg-trip').value,
                status: 1, hide: 0
            };
            if (currentEditId) body.id = currentEditId;
            await adminFetch('api/package_admin.php', body);
            fetchPackages(); fetchStats();

        } else if (currentFormType === 'plan') {
            const body = {
                action: currentEditId ? 'update' : 'add',
                amount: parseFloat(document.getElementById('f-plan-amount').value),
                percentage: parseFloat(document.getElementById('f-plan-pct').value),
                status: 1, hide: 0
            };
            if (currentEditId) body.id = currentEditId;
            await adminFetch('api/plan_admin.php', body);
            fetchPlans();

        } else if (currentFormType === 'upi') {
            const body = {
                action: currentEditId ? 'update' : 'add',
                upi_id: document.getElementById('f-upi-id').value
            };
            if (currentEditId) body.id = currentEditId;
            await adminFetch('api/upi_admin.php', body);
            fetchUpiList();
        }
        closeFormModal();
    } catch (e) { alert('Save failed: ' + e.message); }
}

// ========== MODALS ==========
function openModal(src) { document.getElementById('modal-img').src = src; document.getElementById('image-modal').style.display = 'flex'; }
function closeModal() { document.getElementById('image-modal').style.display = 'none'; }
function closeFormModal() { document.getElementById('form-modal').style.display = 'none'; currentFormType = ''; currentEditId = null; }

function logoutAdmin() { window.location.href = '/dashboard'; }

// Escape quotes for inline onclick attributes
function esc(str) { return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;'); }

checkAuth();
</script>

</body>
</html>
