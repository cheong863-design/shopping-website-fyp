<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

$current_zone = isset($_GET['zone']) ? mysqli_real_escape_string($conn, trim($_GET['zone'])) : 'Domestic';

// ==========================================
// ==========================================
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM shipping_rules WHERE id = $del_id");
    header("Location: shipping-rules.php?zone=$current_zone&msg=deleted");
    exit();
}

if (isset($_GET['delete_zone'])) {
    $del_zone = mysqli_real_escape_string($conn, trim($_GET['delete_zone']));
    mysqli_query($conn, "DELETE FROM shipping_rules WHERE zone = '$del_zone'");
    if (isset($_SESSION['custom_zones'])) {
        $key = array_search($del_zone, $_SESSION['custom_zones']);
        if ($key !== false) unset($_SESSION['custom_zones'][$key]);
    }
    header("Location: shipping-rules.php?zone=Domestic&msg=zone_deleted");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_changes'])) {
    mysqli_query($conn, "UPDATE shipping_rules SET is_active = 0 WHERE zone = '$current_zone'");
    if (isset($_POST['active_status']) && is_array($_POST['active_status'])) {
        foreach ($_POST['active_status'] as $id => $val) {
            $clean_id = intval($id);
            mysqli_query($conn, "UPDATE shipping_rules SET is_active = 1 WHERE id = $clean_id");
        }
    }
    header("Location: shipping-rules.php?zone=$current_zone&msg=saved");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_rule'])) {
    $name = mysqli_real_escape_string($conn, $_POST['rule_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $cond = mysqli_real_escape_string($conn, $_POST['condition_label']);
    $rate = floatval($_POST['rate']);
    $target_zone = mysqli_real_escape_string($conn, $_POST['target_zone']);
    mysqli_query($conn, "INSERT INTO shipping_rules (zone, rule_name, description, condition_label, rate, is_active) VALUES ('$target_zone', '$name', '$desc', '$cond', $rate, 1)");
    header("Location: shipping-rules.php?zone=$target_zone&msg=added");
    exit();
}

if (!isset($_SESSION['custom_zones'])) { $_SESSION['custom_zones'] = []; }
if (!empty($current_zone) && !in_array($current_zone, $_SESSION['custom_zones'])) { $_SESSION['custom_zones'][] = $current_zone; }
$available_zones = ['Domestic', 'International', 'Express'];
$tabs_res = mysqli_query($conn, "SELECT DISTINCT zone FROM shipping_rules ORDER BY zone ASC");
if ($tabs_res) { while($row = mysqli_fetch_assoc($tabs_res)) { if (!in_array($row['zone'], $available_zones)) $available_zones[] = $row['zone']; } }
foreach ($_SESSION['custom_zones'] as $cz) { if (!in_array($cz, $available_zones)) $available_zones[] = $cz; }

$rules_res = mysqli_query($conn, "SELECT * FROM shipping_rules WHERE zone = '$current_zone' ORDER BY id ASC");
$rules = [];
if ($rules_res) { while($row = mysqli_fetch_assoc($rules_res)) { $rules[] = $row; } }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logistics Intelligence - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <style>

        @keyframes fadeUp { 0% { opacity: 0; transform: translateY(20px); } 100% { opacity: 1; transform: translateY(0); } }
        @keyframes cascadeIn { 0% { opacity: 0; transform: translateX(-10px); } 100% { opacity: 1; transform: translateX(0); } }
        @keyframes islandPop { 0% { opacity: 0; transform: translate(-50%, 40px) scale(0.9); } 70% { transform: translate(-50%, -5px) scale(1.02); } 100% { opacity: 1; transform: translate(-50%, 0) scale(1); } }
        @keyframes pulseGlow { 0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 50% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); } }

        body { background: #f8fafc; font-family: 'Inter', sans-serif; overflow-x: hidden; margin: 0; color: #0f172a; }

        .admin-main { margin-left: 220px !important; padding: 40px 60px 120px 60px !important; box-sizing: border-box; }

        .dash-header-top { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .page-title h1 { margin: 0; font-size: 34px; font-weight: 900; letter-spacing: -1.5px; color: #0f172a; }
        .page-title p { margin: 6px 0 0 0; color: #64748b; font-size: 15px; font-weight: 500; }

        .btn-add-zone {
            background: #fff; color: #0f172a; border: 1px solid #e2e8f0; padding: 12px 24px; border-radius: 12px;
            font-weight: 800; font-size: 13px; cursor: pointer; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-add-zone:hover { border-color: #cbd5e1; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); }

        .segmented-control {
            display: inline-flex; background: #e2e8f0; padding: 4px; border-radius: 14px;
            margin-bottom: 40px; animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }
        .segment-item {
            padding: 10px 28px; color: #475569; font-weight: 700; font-size: 13px; text-decoration: none;
            border-radius: 10px; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .segment-item:hover:not(.active) { color: #0f172a; }
        .segment-item.active { background: #fff; color: #0f172a; box-shadow: 0 2px 8px rgba(0,0,0,0.1); font-weight: 800; }

        .zone-card {
            background: #fff; border-radius: 24px; border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.03); overflow: hidden; position: relative;
            animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }

        .zone-header {
            padding: 35px 45px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fafbfc; position: relative;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;
        }
        .zone-header::before { content: ''; position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(250,251,252,0.6), #fafbfc); pointer-events: none; }

        .zone-title-group { display: flex; align-items: center; gap: 20px; position: relative; z-index: 2; }
        .zone-icon { width: 56px; height: 56px; background: #fff; color: #ff8002; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid #e2e8f0; box-shadow: 0 8px 16px rgba(0,0,0,0.03); }
        .zone-name { font-size: 22px; font-weight: 900; color: #0f172a; margin: 0 0 4px 0; letter-spacing: -0.5px; }
        .zone-meta { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 6px; }
        .zone-meta::before { content: ''; width: 6px; height: 6px; background: #10b981; border-radius: 50%; display: inline-block; box-shadow: 0 0 8px #10b981; }

        .btn-add-rate {
            background: #0f172a; color: #fff; padding: 14px 28px; border-radius: 12px; font-weight: 800; font-size: 13px; border: none; cursor: pointer; transition: 0.3s; position: relative; z-index: 2;
        }
        .btn-add-rate:hover { background: #1e293b; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15); }

        .rules-table { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
        .rules-table th:nth-child(1) { width: 35%; } .rules-table th:nth-child(2) { width: 25%; } .rules-table th:nth-child(3) { width: 15%; } .rules-table th:nth-child(4) { width: 15%; } .rules-table th:nth-child(5) { width: 10%; text-align: right; }

        .rules-table th { text-align: left; padding: 20px 45px; font-size: 11px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid #f1f5f9; }
        .rules-table td { padding: 28px 45px; border-bottom: 1px solid #f8fafc; vertical-align: middle; transition: all 0.3s ease; background: #fff; }

        .rules-row { opacity: 0; animation: cascadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; cursor: default; }

        .rules-table:hover .rules-row:not(:hover) td { opacity: 0.4; filter: grayscale(100%); background: transparent; border-bottom-color: transparent; }
        .rules-row:hover td { background: #fff; border-bottom-color: transparent; }
        .rules-row:hover td:first-child { border-left: 4px solid #ff8002; padding-left: 41px; }

        .rule-name { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; transition: 0.3s; letter-spacing: -0.3px;}
        .rules-row:hover .rule-name { color: #ff8002; }
        .rule-desc { font-size: 13px; color: #64748b; font-weight: 500; margin: 0; }

        .condition-pill { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px; }

        .rate-value { font-size: 18px; font-weight: 900; color: #0f172a; font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; display: inline-block; transition: 0.3s; }
        .rules-row:hover .rate-value { transform: scale(1.05); color: #ff8002; }
        .rate-free { color: #10b981; font-weight: 900; font-size: 14px; padding: 4px 10px; background: #ecfdf5; border-radius: 6px; border: 1px solid #a7f3d0; display: inline-block; transition: 0.3s; }
        .rules-row:hover .rate-free { transform: scale(1.05); box-shadow: 0 4px 10px rgba(16,185,129,0.2); }

        .switch { position: relative; display: inline-block; width: 46px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s cubic-bezier(0.34, 1.56, 0.64, 1); border-radius: 34px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1); }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .3s cubic-bezier(0.34, 1.56, 0.64, 1); border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        input:checked + .slider { background-color: #10b981; }
        input:checked + .slider:before { transform: translateX(20px); }
        .switch-active-glow { animation: pulseGlow 2s infinite; border-radius: 34px; }

        .command-island {
            position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%) translateY(150px);
            background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 12px 12px 12px 25px; border-radius: 100px; border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex; justify-content: space-between; align-items: center; gap: 30px; z-index: 1000;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); opacity: 0;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); margin-left: 110px;
        }
        .command-island.visible { transform: translateX(-50%) translateY(0); opacity: 1; animation: islandPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

        .island-text { color: #fff; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .island-text::before { content: ''; width: 8px; height: 8px; background: #ff8002; border-radius: 50%; display: block; box-shadow: 0 0 10px #ff8002; animation: pulseGlow 1.5s infinite; }

        .island-actions { display: flex; gap: 10px; }
        .btn-discard { background: rgba(255,255,255,0.1); color: #fff; border: none; padding: 12px 24px; border-radius: 100px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.3s; }
        .btn-discard:hover { background: rgba(255,255,255,0.2); }
        .btn-save { background: #fff; color: #0f172a; border: none; padding: 12px 30px; border-radius: 100px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(255,255,255,0.2); }
        .btn-save:hover { transform: scale(1.05); }

        .empty-state { text-align: center; padding: 80px 20px; animation: fadeUp 0.6s both; }
        .empty-state h2 { font-size: 24px; color: #0f172a; font-weight: 900; margin: 15px 0 5px 0; letter-spacing: -0.5px; }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(8px); z-index: 2000; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-card { background: #fff; padding: 40px; border-radius: 28px; width: 480px; box-shadow: 0 40px 80px rgba(0,0,0,0.2); animation: fadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .form-input { width: 100%; padding: 14px 18px; border: 2px solid #f1f5f9; border-radius: 12px; font-size: 14px; font-weight: 600; outline: none; transition: 0.3s; box-sizing: border-box; }
        .form-input:focus { border-color: #ff8002; box-shadow: 0 4px 15px rgba(255,128,2,0.1); }
        .form-label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block; }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">

        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="dash-header-top">
                <div class="page-title">
                    <h1>Shipping Intelligence</h1>
                    <p>Orchestrate global delivery zones and dynamic rate algorithms.</p>
                </div>
                <button class="btn-add-zone" onclick="createNewZone()">
                    <span>🌍</span> Configure New Region
                </button>
            </div>

            <div class="segmented-control">
                <?php foreach($available_zones as $zone): ?>
                    <a href="shipping-rules.php?zone=<?php echo urlencode($zone); ?>" class="segment-item <?php echo ($current_zone == $zone) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($zone); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div style="background: #ecfdf5; color: #047857; padding: 16px 25px; border-radius: 14px; margin-bottom: 30px; font-weight: 700; border: 1px solid #a7f3d0; animation: fadeUp 0.5s both; font-size: 14px; display: flex; align-items: center; gap: 10px;">
                    <span>✓</span> <?php
                        if($_GET['msg'] == 'saved') echo "Configuration synced to server.";
                        if($_GET['msg'] == 'added') echo "New rate deployed successfully.";
                        if($_GET['msg'] == 'zone_deleted') echo "Zone purged from database.";
                        if($_GET['msg'] == 'deleted') echo "Rule permanently removed.";
                    ?>
                </div>
            <?php endif; ?>

            <?php if(count($rules) > 0): ?>
                <div class="zone-card">
                    <div class="zone-header">
                        <div class="zone-title-group">
                            <div class="zone-icon"><?php echo ($current_zone == 'International' || $current_zone == 'Express') ? '✈️' : '🚚'; ?></div>
                            <div>
                                <h3 class="zone-name"><?php echo htmlspecialchars($current_zone); ?> Operations</h3>
                                <p class="zone-meta">Routing Active</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 20px; align-items: center; position: relative; z-index: 2;">
                            <a href="shipping-rules.php?delete_zone=<?php echo urlencode($current_zone); ?>" onclick="return confirm('🚨 DANGER: This will purge all rates for this region. Proceed?');" style="color: #ef4444; font-weight: 700; font-size: 13px; text-decoration: none; transition: 0.2s;" onmouseover="this.style.opacity='0.7'">Purge Zone</a>
                            <button class="btn-add-rate" onclick="document.getElementById('addModal').classList.add('active');">＋ Add Rule</button>
                        </div>
                    </div>

                    <form id="rulesForm" method="POST" action="shipping-rules.php?zone=<?php echo urlencode($current_zone); ?>">
                        <table class="rules-table">
                            <thead>
                                <tr>
                                    <th>Delivery Protocol</th>
                                    <th>Trigger Criteria</th>
                                    <th>Calculated Rate</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach($rules as $rule): ?>
                                <tr class="rules-row" style="animation-delay: <?php echo 0.2 + ($i * 0.05); ?>s;">
                                    <td>
                                        <h4 class="rule-name"><?php echo htmlspecialchars($rule['rule_name']); ?></h4>
                                        <p class="rule-desc"><?php echo htmlspecialchars($rule['description']); ?></p>
                                    </td>
                                    <td><span class="condition-pill"><?php echo htmlspecialchars($rule['condition_label']); ?></span></td>
                                    <td>
                                        <?php if($rule['rate'] == 0.00): ?>
                                            <span class="rate-free">COMPLIMENTARY</span>
                                        <?php else: ?>
                                            <span class="rate-value">MYR <?php echo number_format($rule['rate'], 2); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <label class="switch <?php echo $rule['is_active'] ? 'switch-active-glow' : ''; ?>">
                                            <input type="checkbox" class="sync-trigger" name="active_status[<?php echo $rule['id']; ?>]" value="1" <?php echo $rule['is_active'] ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="shipping-rules.php?zone=<?php echo urlencode($current_zone); ?>&delete=<?php echo $rule['id']; ?>" onclick="return confirm('Remove this specific rule?');" style="color: #cbd5e1; font-size: 20px; text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='#ef4444'">×</a>
                                    </td>
                                </tr>
                                <?php $i++; endforeach; ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 50px; opacity: 0.4;">🧭</div>
                    <h2>Unmapped Territory</h2>
                    <p style="color: #64748b; margin-bottom: 25px;">No delivery protocols exist for <strong><?php echo htmlspecialchars($current_zone); ?></strong>.</p>

                    <div style="display: flex; justify-content: center; gap: 15px; align-items: center;">
                        <button class="btn-add-rate" onclick="document.getElementById('addModal').classList.add('active');" style="margin: 0; padding: 14px 30px;">Initialize Routing</button>

                        <?php if(!in_array($current_zone, ['Domestic', 'International', 'Express'])): ?>
                            <a href="shipping-rules.php?delete_zone=<?php echo urlencode($current_zone); ?>"
                               onclick="return confirm('Delete this empty region?');"
                               style="background: #fff; color: #ef4444; border: 1px solid #fecaca; padding: 13px 24px; border-radius: 12px; font-weight: 800; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; transition: 0.3s; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.05);"
                               onmouseover="this.style.background='#fef2f2'; this.style.transform='translateY(-2px)';"
                               onmouseout="this.style.background='#fff'; this.style.transform='translateY(0)';">
                                🗑️ Remove Empty Region
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="command-island" id="actionBar">
                <div class="island-text">Unsaved Configuration</div>
                <div class="island-actions">
                    <button type="button" class="btn-discard" onclick="window.location.reload();">Discard</button>
                    <button type="submit" name="save_changes" class="btn-save" form="rulesForm">Sync Changes</button>
                </div>
            </div>
        </main>
    </div>

    <div id="addModal" class="modal-overlay">
        <div class="modal-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:30px;">
                <div>
                    <h3 style="margin:0; font-size:24px; font-weight:900; letter-spacing: -1px; color: #0f172a;">Deploy New Protocol</h3>
                    <p style="margin:4px 0 0 0; font-size:13px; color:#64748b;">Add a delivery rule for <?php echo htmlspecialchars($current_zone); ?>.</p>
                </div>
                <button onclick="document.getElementById('addModal').classList.remove('active')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#94a3b8;">✕</button>
            </div>

            <form method="POST" action="shipping-rules.php">
                <input type="hidden" name="target_zone" value="<?php echo htmlspecialchars($current_zone); ?>">
                <div style="display:grid; gap:18px;">
                    <div>
                        <label class="form-label">Protocol Name</label>
                        <input type="text" class="form-input" name="rule_name" placeholder="e.g. Next-Day Air" required>
                    </div>
                    <div>
                        <label class="form-label">SLA Description</label>
                        <input type="text" class="form-input" name="description" placeholder="e.g. Delivered within 24 hours" required>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div>
                            <label class="form-label">Trigger Condition</label>
                            <input type="text" class="form-input" name="condition_label" placeholder="e.g. All Orders" required>
                        </div>
                        <div>
                            <label class="form-label">Base Rate (MYR)</label>
                            <input type="number" step="0.01" class="form-input" name="rate" placeholder="0.00" required>
                        </div>
                    </div>
                </div>
                <button type="submit" name="add_rule" style="width:100%; margin-top:35px; background: #0f172a; color: #fff; border: none; padding: 16px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s;">
                    Commit Protocol
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const syncTriggers = document.querySelectorAll('.sync-trigger');
            const actionBar = document.getElementById('actionBar');

            syncTriggers.forEach(trigger => {
                trigger.addEventListener('change', () => {
                    actionBar.classList.add('visible');
                    if(trigger.checked) {
                        trigger.parentElement.classList.add('switch-active-glow');
                    } else {
                        trigger.parentElement.classList.remove('switch-active-glow');
                    }
                });
            });
        });

        function createNewZone() {
            let newZone = prompt("Enter the name of the new routing zone (e.g., East Asia):");
            if (newZone && newZone.trim() !== "") {
                window.location.href = "shipping-rules.php?zone=" + encodeURIComponent(newZone.trim());
            }
        }

        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    </script>
</body>
</html>
