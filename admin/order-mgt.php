<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $order_id = intval($_GET['id']);

    if ($_GET['action'] === 'update_status') {
        $new_status = mysqli_real_escape_string($conn, $_GET['status']);

        if (in_array($new_status, ['Processing', 'Packaging', 'Shipped'])) {
            $check_sql = "SELECT status FROM orders WHERE id = '$order_id'";
            $check_res = mysqli_query($conn, $check_sql);
            $current_order = mysqli_fetch_assoc($check_res);

            if ($current_order && $current_order['status'] !== 'Refunded' && $current_order['status'] !== 'Delivered') {

                // ==========================================
                // ==========================================
                if ($new_status === 'Shipped') {
                    $eta_date = date('Y-m-d', strtotime('+5 days'));

                    $update_sql = "UPDATE orders SET
                                   status = '$new_status',
                                   estimated_delivery = '$eta_date'
                                   WHERE id = '$order_id'";
                } else {
                    $update_sql = "UPDATE orders SET status = '$new_status' WHERE id = '$order_id'";
                }

                mysqli_query($conn, $update_sql);
                header("Location: orders-mgt.php?msg=updated");
                exit();
            } else {
                header("Location: orders-mgt.php?error=locked");
                exit();
            }
        }
    }

    if ($_GET['action'] === 'request_delivery') {
        mysqli_query($conn, "UPDATE orders SET delivery_request = 1 WHERE id = '$order_id'");
        header("Location: orders-mgt.php?msg=req_sent");
        exit();
    }
}

$search_keyword = "";
$where_clause = "";
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_keyword = mysqli_real_escape_string($conn, trim($_GET['search']));
    $clean_id = str_ireplace('ORD-', '', $search_keyword);
    $where_clause = " WHERE o.id LIKE '%$clean_id%' OR u.full_name LIKE '%$search_keyword%' OR u.email LIKE '%$search_keyword%' ";
}

$sql = "SELECT o.*, u.full_name, u.email,
        GROUP_CONCAT(CONCAT_WS(':', IFNULL(p.category, 'item'), p.id) SEPARATOR ',') as product_codes
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        $where_clause
        GROUP BY o.id
        ORDER BY o.created_at DESC";

$orders_res = mysqli_query($conn, $sql);
$total_orders = 0;
if ($orders_res) { $total_orders = mysqli_num_rows($orders_res); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Manage Orders - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>

        @keyframes elasticUp {
            0% { opacity: 0; transform: translateY(30px) scale(0.97); }
            60% { opacity: 1; transform: translateY(-3px) scale(1.01); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes slideInRow {
            0% { opacity: 0; transform: translateX(-20px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        @keyframes tooltipPop {
            0% { opacity: 0; transform: translate(-50%, 10px) scale(0.8); }
            50% { transform: translate(-50%, -15px) scale(1.1); }
            100% { opacity: 1; transform: translate(-50%, -10px) scale(1); }
        }

        .dash-header-top { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        .page-title h1 { margin: 0 0 5px 0; font-size: 26px; color: #0f172a; letter-spacing: -0.5px; font-weight: 800;}
        .page-title p { margin: 0; color: #64748b; font-size: 14px; }

        .toolbar-card {
            background: #fff; padding: 20px 25px; border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.15s both;
        }

        .search-box { position: relative; width: 450px; }
        .search-box input {
            width: 100%; padding: 14px 15px 14px 45px; border: 2px solid #f1f5f9; border-radius: 12px;
            background: #f8fafc; color: #1e293b; outline: none; font-size: 14px; transition: all 0.3s ease;
        }
        .search-box input:focus { border-color: #ff8002; background: #fff; box-shadow: 0 8px 20px rgba(255,128,2,0.08), 0 0 0 4px rgba(255,128,2,0.1); transform: translateY(-2px); }
        .search-box span { position: absolute; left: 18px; top: 15px; color: #94a3b8; font-size: 16px; transition: color 0.3s; }
        .search-box:focus-within span { color: #ff8002; }

        .table-container { animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both; }
        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 14px; margin-top: -14px; text-align: left; }
        .admin-table th { padding: 10px 25px; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; border: none; }

        .admin-table td {
            background: #fff; padding: 22px 25px; border-top: 1px solid rgba(226, 232, 240, 0.5); border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            vertical-align: middle; position: relative; z-index: 2; transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .admin-table td:first-child { border-left: 1px solid rgba(226, 232, 240, 0.5); border-radius: 16px 0 0 16px; }
        .admin-table td:last-child { border-right: 1px solid rgba(226, 232, 240, 0.5); border-radius: 0 16px 16px 0; }

        .order-row { opacity: 0; animation: slideInRow 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards; box-shadow: 0 4px 10px rgba(0,0,0,0.01); transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); position: relative; }
        .order-row:nth-child(1) { animation-delay: 0.3s; }
        .order-row:nth-child(2) { animation-delay: 0.38s; }
        .order-row:nth-child(3) { animation-delay: 0.46s; }
        .order-row:nth-child(n+4) { animation-delay: 0.54s; }

        .admin-table:hover .order-row:not(:hover) { opacity: 0.3; transform: scale(0.98); filter: grayscale(35%); }
        .order-row:hover { transform: scale(1.02); box-shadow: 0 20px 40px rgba(0,0,0,0.08); z-index: 10; }
        .order-row:hover td { border-color: transparent; }

        .code-wrapper { position: relative; display: inline-block; cursor: pointer; }
        .code-box {
            background: #fff7ed; color: #ea580c; border: 2px dashed #fed7aa;
            padding: 8px 12px; border-radius: 10px; font-weight: 900; font-size: 13px;
            display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .code-icon { font-size: 12px; opacity: 0.6; transition: opacity 0.2s; }
        .code-wrapper:hover .code-box { transform: scale(1.05) rotate(-2deg); background: #ffedd5; border-color: #ff8002; box-shadow: 0 4px 15px rgba(234, 88, 12, 0.2); }
        .code-wrapper:hover .code-icon { opacity: 1; }
        .code-wrapper:active .code-box { transform: scale(0.95); }

        .copy-tooltip {
            position: absolute; top: -35px; left: 50%; transform: translateX(-50%);
            background: #10b981; color: white; font-size: 11px; font-weight: 800;
            padding: 6px 12px; border-radius: 8px; white-space: nowrap; pointer-events: none;
            opacity: 0; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }
        .copy-tooltip::after { content:''; position:absolute; bottom:-4px; left:50%; transform:translateX(-50%); border-width:4px 4px 0; border-style:solid; border-color:#10b981 transparent transparent transparent; }
        .code-wrapper.copied .code-box { background: #dcfce7; border-color: #10b981; color: #047857; }
        .code-wrapper.copied .copy-tooltip { animation: tooltipPop 2s forwards cubic-bezier(0.34, 1.56, 0.64, 1); }

        .status-cell { display: flex; flex-direction: column; gap: 6px; }
        .status-text { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .stepper-track { display: flex; gap: 4px; width: 100%; max-width: 120px; }
        .step-dash { height: 6px; flex: 1; border-radius: 3px; background: #f1f5f9; transition: all 0.4s ease; }

        .status-paid .status-text { color: #ea580c; }
        .status-paid .step-dash:nth-child(1) { background: #ff8002; }

        .status-processing .status-text { color: #d97706; }
        .status-processing .step-dash:nth-child(1), .status-processing .step-dash:nth-child(2) { background: #facc15; }

        .status-processing .step-dash:nth-child(2) { animation: pulseBg 1.5s infinite alternate; }
        @keyframes pulseBg { 0% { opacity: 0.5; } 100% { opacity: 1; } }

        .status-packaging .status-text { color: #9333ea; }
        .status-packaging .step-dash:nth-child(1), .status-packaging .step-dash:nth-child(2) { background: #c026d3; }
        .status-packaging .step-dash:nth-child(2) { animation: pulseBg 1.5s infinite alternate; }

        .status-shipped .status-text { color: #2563eb; }
        .status-shipped .step-dash:nth-child(1), .status-shipped .step-dash:nth-child(2), .status-shipped .step-dash:nth-child(3) { background: #3b82f6; }

        .status-delivered .status-text { color: #059669; }
        .status-delivered .step-dash { background: #10b981; }

        .status-refunded .status-text { color: #dc2626; }
        .status-refunded .step-dash { background: #fca5a5; }

        .action-drawer {
            display: flex; align-items: center; justify-content: flex-end; gap: 12px;
            opacity: 0.3; filter: grayscale(80%); transform: translateX(10px);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
        }
        .order-row:hover .action-drawer {
            opacity: 1; filter: grayscale(0%); transform: translateX(0); pointer-events: auto;
        }

        .btn-view-order {
            color: #0f172a; font-size: 12px; font-weight: 800; text-decoration: none;
            padding: 9px 16px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0;
            transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-view-order:hover { background: #f1f5f9; transform: translateY(-2px); border-color: #cbd5e1; }

        .custom-select-wrapper { position: relative; }
        .status-select {
            appearance: none; -webkit-appearance: none;
            padding: 8px 30px 8px 14px; border: 2px solid #e2e8f0; border-radius: 10px;
            background: #fff; color: #0f172a; font-size: 12px; font-weight: 700;
            cursor: pointer; transition: all 0.3s; outline: none; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .status-select:hover:not(:disabled) { border-color: #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15); transform: translateY(-2px); }
        .status-select:disabled { background: #f8fafc; color: #94a3b8; border-color: #f1f5f9; cursor: not-allowed; }
        .custom-select-wrapper::after {
            content: '▼'; position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            font-size: 10px; color: #94a3b8; pointer-events: none; transition: 0.3s;
        }
        .custom-select-wrapper:hover::after { color: #3b82f6; }

        .btn-req-del { background: #10b981; color: #fff; border: none; padding: 9px 16px; border-radius: 10px; font-size: 12px; font-weight: 800; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 6px; }
        .btn-req-del:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3); background: #059669; }

        .req-waiting { font-size: 11px; color: #d97706; background: #fefce8; padding: 8px 12px; border-radius: 10px; font-weight: 800; border: 1px solid #fde047; display: inline-flex; align-items: center; gap: 6px; }
        .req-waiting::before { content: '⏳'; animation: spin 2s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        .btn-refund { color: #ef4444; font-size: 12px; font-weight: 800; text-decoration: none; padding: 8px 16px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; transition: all 0.3s ease; }
        .btn-refund:hover { background: #fee2e2; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); }

        .avatar { width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #f1f5f9, #e2e8f0); color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; box-shadow: inset 0 2px 4px rgba(255,255,255,0.8); border: 1px solid #cbd5e1; transition: transform 0.3s; }
        .order-row:hover .avatar { transform: scale(1.1) rotate(5deg); border-color: #94a3b8; }
        .prod-tag { font-size: 10px; color: #475569; background: #f8fafc; padding: 4px 8px; border-radius: 6px; font-weight: 800; white-space: nowrap; border: 1px solid #e2e8f0; transition: 0.2s; }
        .order-row:hover .prod-tag { background: #e2e8f0; color: #0f172a; }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">

        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="dash-header-top">
                <div class="page-title">
                    <div style="font-size: 11px; color: #64748b; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 8px; text-transform: uppercase;">DASHBOARD > FULFILLMENT</div>
                    <h1>Order Operations Center</h1>
                    <p>Track lifecycles, fulfill customer orders, and manage shipping statuses.</p>
                </div>
            </div>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="alert-msg alert-success" style="padding: 16px 24px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 12px; animation: elasticUp 0.4s both; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);">
                    <span style="font-size: 20px;">✨</span> Order timeline updated successfully.
                </div>
            <?php endif; ?>

            <div class="toolbar-card">
                <form method="GET" action="orders-mgt.php" style="margin: 0; display: flex; align-items: center; gap: 15px;">
                    <div class="search-box">
                        <span>🔍</span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Search by Order ID or Customer Name...">
                    </div>
                    <button type="submit" style="background: #0f172a; color: #fff; border: none; padding: 14px 24px; border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.2s;">Filter</button>

                    <?php if(!empty($search_keyword)): ?>
                        <a href="orders-mgt.php" style="color: #ef4444; font-size: 13px; font-weight: 800; text-decoration: none; padding: 12px 20px; background: #fef2f2; border-radius: 12px; transition: 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fef2f2'">Clear</a>
                    <?php endif; ?>
                </form>
                <div style="font-size: 13px; font-weight: 800; color: #475569; background: #f8fafc; padding: 10px 20px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    Total Queue: <strong style="color: #0f172a; font-size: 18px;"><?php echo $total_orders; ?></strong>
                </div>
            </div>

            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ORDER ID</th>
                            <th>CUSTOMER</th>
                            <th>PRODUCTS</th>
                            <th>TOTAL</th>
                            <th>LIFECYCLE STATUS</th>
                            <th style="text-align: right; padding-right: 35px;">COMMANDS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_orders > 0): ?>
                            <?php while($order = mysqli_fetch_assoc($orders_res)):
                                $status_lower = strtolower($order['status']);
                                $initials = strtoupper(substr($order['full_name'], 0, 2));

                                $tags_html = '';
                                if (!empty($order['product_codes'])) {
                                    $code_pairs = explode(',', $order['product_codes']);
                                    $display_limit = 2;
                                    $shown_count = 0;

                                    foreach ($code_pairs as $pair) {
                                        if (empty(trim($pair))) continue;
                                        if ($shown_count >= $display_limit) {
                                            $remaining = count($code_pairs) - $display_limit;
                                            $tags_html .= "<span class='prod-tag' style='background:#cbd5e1; color:#0f172a;'>+$remaining</span>";
                                            break;
                                        }
                                        list($cat, $pid) = explode(':', $pair);
                                        $cat_prefix = strtoupper(substr(str_replace(' ', '', $cat), 0, 3));
                                        $tags_html .= "<span class='prod-tag'>#{$cat_prefix}-{$pid}</span>";
                                        $shown_count++;
                                    }
                                }
                            ?>
                            <tr class="order-row">
                                <td>
                                    <div class="code-wrapper" onclick="copyCode(this, 'ORD-<?php echo $order['id']; ?>')">
                                        <span class="code-box">
                                            #ORD-<?php echo $order['id']; ?>
                                            <span class="code-icon">📄</span>
                                        </span>
                                        <span class="copy-tooltip">✅ Copied!</span>
                                    </div>
                                    <div style="color: #94a3b8; font-weight: 600; font-size: 11px; margin-top: 6px; padding-left: 5px;">
                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <div class="avatar"><?php echo $initials; ?></div>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="color: #0f172a; font-weight: 800; font-size: 14.5px;"><?php echo htmlspecialchars($order['full_name']); ?></span>
                                            <span style="color: #64748b; font-size: 12px; font-weight: 500;"><?php echo htmlspecialchars($order['email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><div style="display: flex; flex-wrap: wrap; gap: 6px; max-width: 180px;"><?php echo $tags_html; ?></div></td>
                                <td><strong style="color: #0f172a; font-size: 16px; font-weight: 900;">MYR <?php echo number_format($order['total_price'], 2); ?></strong></td>

                                <td>
                                    <div class="status-cell status-<?php echo $status_lower; ?>">
                                        <span class="status-text">
                                            <?php echo $order['status']; ?>
                                            <?php if($order['status'] === 'Shipped' && !empty($order['estimated_delivery'])): ?>
                                                <span style="font-size: 9px; color: #64748b; margin-left: 5px;">(ETA: <?php echo date('M d', strtotime($order['estimated_delivery'])); ?>)</span>
                                            <?php endif; ?>
                                        </span>
                                        <div class="stepper-track">
                                            <div class="step-dash"></div>
                                            <div class="step-dash"></div>
                                            <div class="step-dash"></div>
                                            <div class="step-dash"></div>
                                        </div>
                                    </div>
                                </td>

                                <td style="text-align: right; padding-right: 25px;">
                                    <div class="action-drawer">
                                        <a href="check-order-details.php?id=<?php echo $order['id']; ?>" class="btn-view-order">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            View
                                        </a>

                                        <?php if($order['status'] == 'Delivered' || $order['status'] == 'Refunded'): ?>
                                            <span style="font-size: 12px; color: #94a3b8; font-weight: 800; background: #f8fafc; padding: 8px 14px; border-radius: 10px; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 6px;">
                                                <span>🔒</span> CYCLE CLOSED
                                            </span>
                                        <?php else: ?>
                                            <div class="custom-select-wrapper">
                                                <select class="status-select" onchange="location.href='orders-mgt.php?action=update_status&id=<?php echo $order['id']; ?>&status=' + this.value">
                                                    <?php if($order['status'] == 'Paid'): ?><option value="Paid" selected disabled>Set Next Stage</option><?php endif; ?>
                                                    <option value="Processing" <?php if($order['status']=='Processing') echo 'selected'; ?>>[2] Processing</option>
                                                    <option value="Packaging" <?php if($order['status']=='Packaging') echo 'selected'; ?>>[2] Packaging</option>
                                                    <option value="Shipped" <?php if($order['status']=='Shipped') echo 'selected'; ?>>[3] Shipped</option>
                                                </select>
                                            </div>

                                            <?php if($order['status'] == 'Shipped'): ?>
                                                <?php if(isset($order['delivery_request']) && $order['delivery_request'] == 1): ?>
                                                    <span class="req-waiting">Waiting Confirm</span>
                                                <?php else: ?>
                                                    <button onclick="location.href='orders-mgt.php?action=request_delivery&id=<?php echo $order['id']; ?>'" class="btn-req-del">
                                                        <span>🔔</span> PING USER
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="process-refund.php?id=<?php echo $order['id']; ?>" class="btn-refund">Cancel</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="order-row">
                                <td colspan="6" style="text-align: center; padding: 80px 20px; color: #64748b; background: #fff; border-radius: 16px; border: 2px dashed #e2e8f0;">
                                    <div style="font-size: 50px; margin-bottom: 20px; opacity: 0.5;">📦</div>
                                    <p style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a;">Order Queue is Empty</p>
                                    <p style="margin: 8px 0 0 0; font-size: 14px;">Incoming customer orders will appear here for processing.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function copyCode(element, code) {
            navigator.clipboard.writeText(code).then(() => {
                element.classList.add('copied');
                const icon = element.querySelector('.code-icon');
                const originalIcon = icon.innerText;
                icon.innerText = '✅';
                setTimeout(() => {
                    element.classList.remove('copied');
                    icon.innerText = originalIcon;
                }, 2000);
            });
        }
    </script>
</body>
</html>
