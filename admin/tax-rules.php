<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// ==========================================
// ==========================================
if (isset($_GET['toggle_id'])) {
    $tid = intval($_GET['toggle_id']);
    $new_status = intval($_GET['status']);
    mysqli_query($conn, "UPDATE tax_rules SET is_active = $new_status WHERE id = $tid");

    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        echo "ok";
        exit();
    }
    header("Location: tax-rules.php?msg=updated");
    exit();
}

if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM tax_rules WHERE id = $del_id");
    header("Location: tax-rules.php?msg=deleted");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tax_rule'])) {
    $jurisdiction = mysqli_real_escape_string($conn, $_POST['jurisdiction']);
    $region = mysqli_real_escape_string($conn, $_POST['region_detail']);
    $type = mysqli_real_escape_string($conn, $_POST['tax_type']);
    $rate = floatval($_POST['rate']);
    $flag = mysqli_real_escape_string($conn, $_POST['flag_icon']);

    $sql = "INSERT INTO tax_rules (jurisdiction, region_detail, tax_type, rate, is_active, flag_icon)
            VALUES ('$jurisdiction', '$region', '$type', $rate, 1, '$flag')";
    mysqli_query($conn, $sql);
    header("Location: tax-rules.php?msg=added");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = $search ? " WHERE jurisdiction LIKE '%$search%' OR region_detail LIKE '%$search%'" : "";

$rules_res = mysqli_query($conn, "SELECT * FROM tax_rules $where ORDER BY jurisdiction ASC");
$total_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tax_rules WHERE is_active = 1"))['c'];
$has_rules = mysqli_num_rows($rules_res) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Tax Governance Nexus - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@600;800&family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>

        @keyframes elasticUp { 0% { opacity: 0; transform: translateY(40px) scale(0.96); } 100% { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes slideInRow { 0% { opacity: 0; transform: translateX(-20px); } 100% { opacity: 1; transform: translateX(0); } }
        @keyframes pulseDot { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.5); opacity: 0.5; } 100% { transform: scale(1); opacity: 1; } }
        @keyframes pulseLine { 0% { stroke-dashoffset: 150; } 100% { stroke-dashoffset: 0; } }

        body { background: #f8fafc; font-family: 'Inter', sans-serif; margin: 0; overflow-x: hidden; color: #0f172a; }
        .admin-main { margin-left: 220px !important; padding: 40px 45px 80px 45px !important; box-sizing: border-box; }

        .dynamic-island {
            position: fixed; top: -100px; left: calc(50% + 110px); transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            color: #fff; padding: 14px 28px; border-radius: 100px; font-size: 13px; font-weight: 700;
            display: flex; align-items: center; gap: 14px; z-index: 9999;
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.1);
        }
        .dynamic-island.show { top: 35px; }
        .di-spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.2); border-top-color: #10b981; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .dash-header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        .page-title h1 { margin: 0; font-size: 36px; font-weight: 900; letter-spacing: -2px; }
        .page-title p { margin: 5px 0 0 0; color: #64748b; font-size: 15px; font-weight: 500; }

        .live-monitor {
            display: flex; align-items: center; gap: 20px; background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px); padding: 12px 25px; border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .clock-time { font-family: 'JetBrains Mono', monospace; font-size: 17px; font-weight: 800; color: #ff8002; line-height: 1; }
        .heartbeat-svg { width: 60px; height: 25px; }
        .pulse-path { stroke: #10b981; stroke-width: 2.5; fill: none; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 150; animation: pulseLine 2s linear infinite; }

        .kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both; }
        .kpi-card { background: #fff; padding: 30px; border-radius: 24px; border: 1px solid rgba(226, 232, 240, 0.8); position: relative; transition: all 0.4s; }
        .kpi-card:hover { transform: translateY(-6px); border-color: #ff8002; box-shadow: 0 25px 50px -12px rgba(255, 128, 2, 0.1); }
        .kpi-card h3 { margin: 0 0 12px 0; font-size: 12px; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; }
        .kpi-value { font-size: 42px; font-weight: 900; color: #0f172a; letter-spacing: -2px; font-family: 'JetBrains Mono', monospace; display: flex; align-items: baseline; }
        .kpi-suffix { font-size: 22px; color: #cbd5e1; margin-left: 5px; font-family: 'Inter', sans-serif; }

        .table-nexus { background: #fff; border-radius: 28px; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 10px 40px rgba(0,0,0,0.02); overflow: hidden; position: relative; animation: elasticUp 0.6s both 0.2s; }
        .empty-state { text-align: center; padding: 80px 20px; animation: elasticUp 0.6s both 0.2s; background: #fff; border-radius: 28px; border: 2px dashed #e2e8f0; }

        .nexus-toolbar { padding: 25px 35px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fafbfc; }

        .search-container { position: relative; width: 350px; }
        .search-input { width: 100%; padding: 14px 20px 14px 45px; border: 2px solid #e2e8f0; border-radius: 14px; font-size: 14px; font-weight: 600; outline: none; transition: 0.3s; background: #fff; }
        .search-input:focus { border-color: #ff8002; box-shadow: 0 0 0 4px rgba(255, 128, 2, 0.05); }
        .search-container::before { content: '🔎'; position: absolute; left: 16px; top: 14px; z-index: 2; font-size: 16px; color: #94a3b8; }

        .tax-table { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
        .tax-table th:nth-child(1) { width: 35%; } .tax-table th:nth-child(2) { width: 20%; } .tax-table th:nth-child(3) { width: 20%; } .tax-table th:nth-child(4) { width: 15%; } .tax-table th:nth-child(5) { width: 10%; text-align: right; }

        .tax-table th { text-align: left; padding: 20px 35px; font-size: 11px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; }
        .tax-table td { padding: 25px 35px; border-bottom: 1px solid #f8fafc; vertical-align: middle; transition: 0.3s; position: relative; z-index: 2; background: rgba(255,255,255,0.7); }

        .tax-table:hover .tax-row:not(:hover) td { opacity: 0.3; filter: blur(1px) grayscale(1); }
        .tax-row:hover td { background: rgba(255, 255, 255, 1); border-bottom-color: transparent; }
        .tax-row:hover td:first-child { border-left: 5px solid #ff8002; padding-left: 30px; }

        .juris-cell { display: flex; align-items: center; gap: 20px; }
        .flag-card { width: 48px; height: 48px; background: #f8fafc; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid #e2e8f0; transition: 0.5s; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .tax-row:hover .flag-card { transform: scale(1.2) rotate(-8deg); border-color: #ff8002; box-shadow: 0 10px 20px rgba(255,128,2,0.15); }

        .rate-badge { font-family: 'JetBrains Mono', monospace; font-size: 17px; font-weight: 800; color: #0f172a; padding: 8px 15px; background: #f1f5f9; border-radius: 10px; transition: 0.3s; display: inline-block; font-variant-numeric: tabular-nums;}
        .tax-row:hover .rate-badge { background: #0f172a; color: #fff; transform: scale(1.1); box-shadow: 0 8px 20px rgba(0,0,0,0.2); }

        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);}
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        input:checked + .slider { background-color: #10b981; }
        input:checked + .slider:before { transform: translateX(24px); }
        .switch-active-glow { box-shadow: 0 0 15px rgba(16, 185, 129, 0.4); border-radius: 34px; }

        .spotlight-layer { position: absolute; top: 0; left: 0; width: 800px; height: 800px; transform: translate3d(calc(var(--mouse-x, -1000px) - 50%), calc(var(--mouse-y, -1000px) - 50%), 0); background: radial-gradient(circle, rgba(255, 128, 2, 0.08) 0%, transparent 65%); pointer-events: none; z-index: 1; opacity: 0; transition: opacity 0.5s; will-change: transform; }
        .table-nexus:hover .spotlight-layer { opacity: 1; }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(15px); z-index: 2000; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-card { background: #fff; padding: 45px; border-radius: 32px; width: 480px; box-shadow: 0 40px 80px rgba(0,0,0,0.3); animation: elasticUp 0.5s both; position: relative; }
        .modal-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: linear-gradient(90deg, #ff8002, #ea580c); border-radius: 32px 32px 0 0; }

        .btn-primary { background: linear-gradient(135deg, #ff8002, #ea580c); color: #fff; padding: 14px 28px; border-radius: 14px; border: none; font-weight: 800; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(255,128,2,0.2); }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(255,128,2,0.3); }
        .btn-secondary { background: #f1f5f9; color: #0f172a; padding: 14px 28px; border-radius: 14px; border: none; font-weight: 800; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block;}
        .btn-secondary:hover { background: #e2e8f0; }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <div id="dynamic-island" class="dynamic-island">
            <span id="di-icon"></span>
            <span id="di-msg">System Ready</span>
        </div>

        <main class="admin-main">
            <header class="dash-header-top">
                <div class="page-title">
                    <h1>Tax Governance</h1>
                    <p>Real-time financial compliance and algorithmic fiscal rules.</p>
                </div>

                <div class="live-monitor">
                    <div class="clock-box">
                        <div class="clock-time" id="digitalClock">00:00:00</div>
                    </div>
                    <svg class="heartbeat-svg" viewBox="0 0 100 30">
                        <path class="pulse-path" d="M0,15 L30,15 L35,5 L45,25 L50,15 L100,15" />
                    </svg>
                    <div style="color: #10b981; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
                        <span class="dot" style="display:inline-block; width:6px; height:6px; background:#10b981; border-radius:50%; margin-right:5px; animation:pulseDot 1.5s infinite;"></span>
                        Engine Active
                    </div>
                </div>
            </header>

            <div class="kpi-row">
                <div class="kpi-card">
                    <h3>Jurisdiction Assets</h3>
                    <div class="kpi-value">
                        <span id="activeCount" class="premium-counter" data-target="<?php echo $total_active; ?>" data-is-float="false">0</span>
                    </div>
                </div>
                <div class="kpi-card">
                    <h3>Default Baseline</h3>
                    <div class="kpi-value">
                        <span class="premium-counter" data-target="7.500" data-is-float="true">0.000</span>
                        <span class="kpi-suffix">%</span>
                    </div>
                </div>
                <div class="kpi-card" style="background: #0f172a;">
                    <h3 style="color: #94a3b8;">Automation Integrity</h3>
                    <div class="value" style="color: #10b981; font-size: 24px; font-weight: 900; letter-spacing: -1px; font-family: 'JetBrains Mono';">FULLY SYNCED</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 10px;">GLOBAL FISCAL ENGINE v4.2</div>
                </div>
            </div>

            <?php if($has_rules): ?>
                <div class="table-nexus" id="nexusContainer">
                    <div class="spotlight-layer"></div>
                    <div class="nexus-toolbar">
                        <form method="GET" class="search-container">
                            <input type="text" name="search" class="search-input" value="<?php echo htmlspecialchars($search); ?>" placeholder="Filter jurisdictions...">
                        </form>
                        <button class="btn-primary" onclick="document.getElementById('addTaxModal').classList.add('active')">
                            ＋ Deploy New Rule
                        </button>
                    </div>

                    <table class="tax-table">
                        <thead>
                            <tr>
                                <th>Jurisdiction Detail</th>
                                <th>Category</th>
                                <th>Tax Rate</th>
                                <th>Live Status</th>
                                <th style="text-align: right; padding-right: 35px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $delay = 0.3;
                            while($row = mysqli_fetch_assoc($rules_res)):
                            ?>
                            <tr class="tax-row" style="animation: slideInRow 0.5s forwards <?php echo $delay; ?>s;">
                                <td>
                                    <div class="juris-cell">
                                        <div class="flag-card"><?php echo $row['flag_icon']; ?></div>
                                        <div>
                                            <div style="font-weight: 900; color: #0f172a; font-size: 15px;"><?php echo htmlspecialchars($row['jurisdiction']); ?></div>
                                            <div style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($row['region_detail']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span style="background:#f1f5f9; color:#475569; padding:6px 12px; border-radius:8px; font-size:11px; font-weight:800; border:1px solid #e2e8f0;"><?php echo strtoupper($row['tax_type']); ?></span></td>
                                <td><span class="rate-badge"><?php echo number_format($row['rate'], 3); ?>%</span></td>
                                <td>
                                    <label class="switch <?php echo $row['is_active'] ? 'switch-active-glow' : ''; ?>">
                                        <input type="checkbox" class="async-toggle" data-id="<?php echo $row['id']; ?>" <?php echo $row['is_active'] ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td style="text-align: right; padding-right: 25px;">
                                    <a href="tax-rules.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('🚨 Purge jurisdiction?')" style="color:#94a3b8; font-size:22px; text-decoration:none;">🗑️</a>
                                </td>
                            </tr>
                            <?php $delay += 0.05; endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 60px; margin-bottom: 25px; filter: grayscale(100%); opacity: 0.5;">⚖️</div>
                    <h2 style="font-weight: 900; color: #0f172a; margin-bottom: 10px; letter-spacing: -1px;">No Jurisdictions Found</h2>
                    <p style="color: #64748b; margin-bottom: 30px; font-size: 16px;">The tax governance engine is currently dormant in this sector.</p>

                    <?php if($search): ?>
                        <a href="tax-rules.php" class="btn-secondary">Clear Search Filter</a>
                    <?php else: ?>
                        <button class="btn-primary" onclick="document.getElementById('addTaxModal').classList.add('active');" style="padding: 16px 35px; font-size: 15px;">Initialize First Rule</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div id="addTaxModal" class="modal-overlay">
        <div class="modal-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:35px;">
                <h2 style="margin:0; font-size:24px; font-weight:900;">Deploy Tax Rule</h2>
                <button onclick="document.getElementById('addTaxModal').classList.remove('active')" style="background:none; border:none; font-size:24px; cursor:pointer; color:#94a3b8;">✕</button>
            </div>
            <form method="POST">
                <div style="display:grid; gap:20px;">
                    <div style="display:grid; grid-template-columns: 1fr 80px; gap:15px;">
                        <div class="form-group"><label style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Country</label><input type="text" style="width: 100%; padding: 14px; border: 2px solid #f1f5f9; border-radius: 12px; outline: none; font-weight: 700; box-sizing: border-box;" name="jurisdiction" placeholder="e.g. Canada" required></div>
                        <div class="form-group"><label style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Flag</label><input type="text" style="width: 100%; padding: 14px; border: 2px solid #f1f5f9; border-radius: 12px; outline: none; font-weight: 700; box-sizing: border-box;" name="flag_icon" placeholder="🇨🇦" required></div>
                    </div>
                    <div class="form-group"><label style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Region</label><input type="text" style="width: 100%; padding: 14px; border: 2px solid #f1f5f9; border-radius: 12px; outline: none; font-weight: 700; box-sizing: border-box;" name="region_detail" placeholder="e.g. Ontario / All States" required></div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Type</label>
                            <select name="tax_type" style="width: 100%; padding: 14px; border: 2px solid #f1f5f9; border-radius: 12px; outline: none; font-weight: 700; box-sizing: border-box;">
                                <option value="Sales Tax">Sales Tax</option>
                                <option value="VAT">VAT</option>
                                <option value="HST">HST</option>
                                <option value="SST">SST / GST</option>
                            </select>
                        </div>
                        <div class="form-group"><label style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Rate (%)</label><input type="number" step="0.001" style="width: 100%; padding: 14px; border: 2px solid #f1f5f9; border-radius: 12px; outline: none; font-weight: 700; box-sizing: border-box;" name="rate" placeholder="13.000" required></div>
                    </div>
                </div>
                <button type="submit" name="add_tax_rule" class="btn-primary" style="width:100%; margin-top:35px; padding:18px; font-size: 16px;">🚀 Confirm Deployment</button>
            </form>
        </div>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <?php
            $php_msg = "";
            if($_GET['msg'] == 'added') $php_msg = "Jurisdiction deployed successfully.";
            if($_GET['msg'] == 'updated') $php_msg = "Rule synchronization complete.";
            if($_GET['msg'] == 'deleted') $php_msg = "Jurisdiction record purged.";
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showToast("<?php echo $php_msg; ?>", 'success');
            });
        </script>
    <?php endif; ?>

    <script>
        // ----------------------------------------------------
        // ----------------------------------------------------
        let toastTimeout;
        function showToast(msg, type) {
            const island = document.getElementById('dynamic-island');
            const icon = document.getElementById('di-icon');
            document.getElementById('di-msg').innerText = msg;

            if(type === 'sync') icon.innerHTML = '<div class="di-spinner"></div>';
            else if(type === 'success') icon.innerHTML = '✅';
            else if(type === 'error') icon.innerHTML = '❌';

            island.classList.add('show');
            clearTimeout(toastTimeout);

            if(type !== 'sync') {
                toastTimeout = setTimeout(() => island.classList.remove('show'), 3000);
            }
        }

        // ----------------------------------------------------
        // ----------------------------------------------------
        function rollNumber(element, start, end, isFloat) {
            const duration = 1500;
            const startTime = performance.now();
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const ease = 1 - Math.pow(1 - progress, 4);
                const val = start + (end - start) * ease;
                element.innerText = isFloat ? val.toFixed(3) : Math.floor(val);
                if (progress < 1) requestAnimationFrame(update);
                else element.innerText = isFloat ? end.toFixed(3) : end;
            }
            requestAnimationFrame(update);
        }

        document.addEventListener("DOMContentLoaded", () => {
            const counters = document.querySelectorAll('.premium-counter');
            counters.forEach(counter => {
                const target = parseFloat(counter.getAttribute('data-target'));
                const isFloat = counter.getAttribute('data-is-float') === 'true';
                rollNumber(counter, 0, target, isFloat);
            });

            // ----------------------------------------------------
            // ----------------------------------------------------
            document.querySelectorAll('.async-toggle').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const id = this.getAttribute('data-id');
                    const newStatus = this.checked ? 1 : 0;
                    const parentLabel = this.parentElement;

                    if(this.checked) parentLabel.classList.add('switch-active-glow');
                    else parentLabel.classList.remove('switch-active-glow');

                    const kpiEl = document.getElementById('activeCount');
                    const currentKpi = parseInt(kpiEl.innerText);
                    const targetKpi = this.checked ? currentKpi + 1 : currentKpi - 1;
                    rollNumber(kpiEl, currentKpi, targetKpi, false);

                    showToast('Syncing rule to global engine...', 'sync');

                    fetch(`tax-rules.php?toggle_id=${id}&status=${newStatus}&ajax=1`)
                        .then(res => res.text())
                        .then(text => {
                            if(text.trim() === 'ok') {
                                setTimeout(() => showToast('Engine synchronized successfully.', 'success'), 500);
                            }
                        }).catch(err => {
                            showToast('Connection unstable.', 'error');
                        });
                });
            });

            const nexus = document.getElementById('nexusContainer');
            if(nexus) {
                const spotlight = nexus.querySelector('.spotlight-layer');
                nexus.addEventListener('mousemove', e => {
                    requestAnimationFrame(() => {
                        const rect = nexus.getBoundingClientRect();
                        nexus.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
                        nexus.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
                    });
                });
            }

            function updateClock() {
                const now = new Date();
                document.getElementById('digitalClock').innerText = now.toTimeString().split(' ')[0];
            }
            setInterval(updateClock, 1000);
            updateClock();

            document.getElementById('addTaxModal').addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('active');
            });
        });
    </script>
</body>
</html>
