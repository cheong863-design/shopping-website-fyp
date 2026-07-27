<?php
// 1. 初始化与数据库连接
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

// 2. 核心安全拦截
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = "";

// ==========================================
// ✨ 核心功能：处理订单状态更新 & ETA 引擎
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    
    $check_sql = "SELECT status FROM orders WHERE id = '$order_id'";
    $check_res = mysqli_query($conn, $check_sql);
    $current = mysqli_fetch_assoc($check_res);
    
    if ($current && $current['status'] !== 'Delivered' && $current['status'] !== 'Refunded') {
        if ($new_status === 'Shipped') {
            $eta_date = date('Y-m-d', strtotime('+5 days')); 
            $update_sql = "UPDATE orders SET status = '$new_status', estimated_delivery = '$eta_date' WHERE id = '$order_id'";
        } else {
            $update_sql = "UPDATE orders SET status = '$new_status' WHERE id = '$order_id'";
        }
        
        if(mysqli_query($conn, $update_sql)) {
            $msg = "Order timeline securely updated.";
        }
    }
}

// 3. 获取订单主表信息
$order_query = mysqli_query($conn, "SELECT o.*, u.full_name as u_name, u.email, u.phone as u_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = $order_id");
$order = mysqli_fetch_assoc($order_query);

if (!$order) { 
    header("Location: orders-mgt.php"); 
    exit(); 
}

// 4. 地址回退逻辑
if (empty($order['address_line'])) {
    $addr_query = mysqli_query($conn, "SELECT * FROM user_addresses WHERE user_id = '{$order['user_id']}' AND is_default = 1 LIMIT 1");
    $default_addr = mysqli_fetch_assoc($addr_query);
    if ($default_addr) {
        $display_name = $default_addr['receiver_name'];
        $display_address = $default_addr['address_line'];
        $display_phone = $default_addr['phone'];
    } else {
        $display_name = $order['u_name'];
        $display_address = "NO COORDINATES RECORDED IN SYSTEM.";
        $display_phone = $order['u_phone'] ?? 'N/A';
    }
} else {
    $display_name = $order['full_name'] ?? $order['u_name'];
    $display_address = $order['address_line'];
    $display_phone = $order['phone'];
}

// 5. 获取商品明细
$items_query = mysqli_query($conn, "SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");
$items_array = [];
$real_subtotal = 0;

if ($items_query) {
    while($item = mysqli_fetch_assoc($items_query)) {
        $items_array[] = $item;
        $real_subtotal += ((float)$item['price'] * (int)$item['quantity']);
    }
}

$total_price    = (float)($order['total_price'] ?? 0.00); 
$shipping_fee   = (float)($order['shipping_fee'] ?? 0.00); 
$subtotal       = $real_subtotal; 
$calc_tax       = $total_price - $subtotal - $shipping_fee;
$tax_amount     = $calc_tax > 0 ? $calc_tax : (float)($order['tax_amount'] ?? 0.00);

$status_lower = strtolower($order['status']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Order Dossier - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        /* ========== 顶级 SaaS 动画与排版系统 ========== */
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #0f172a; }
        
        /* 1. 核心入场动画 */
        @keyframes elasticUp {
            0% { opacity: 0; transform: translateY(30px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes fadeFloat {
            0% { opacity: 0; transform: translateY(15px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .dash-header-top { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        .back-link { font-family: monospace; font-size: 11px; font-weight: 800; color: #64748b; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 15px; }
        .back-link:hover { color: #0f172a; transform: translateX(-5px); }
        
        .page-title h1 { margin: 0; font-family: 'Playfair Display', serif; font-size: 38px; font-style: italic; color: #0f172a; letter-spacing: -1px; }
        
        /* 2. 卷宗主容器 */
        .dossier-card { 
            background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); 
            border: 1px solid rgba(226, 232, 240, 0.8); overflow: hidden;
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.15s both;
            position: relative;
        }

        /* 盖章动画 (更新成功时触发) */
        .dossier-card.update-flash { animation: stampFlash 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes stampFlash { 0% { box-shadow: 0 0 0 4px #10b981, 0 20px 40px rgba(16,185,129,0.2); border-color: #10b981; } 100% { box-shadow: 0 10px 40px rgba(0,0,0,0.03); border-color: rgba(226, 232, 240, 0.8); } }

        .dossier-header { padding: 40px 50px; border-bottom: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fafafa; }
        .dh-left { display: flex; flex-direction: column; gap: 8px; }
        .mono-tag { font-family: monospace; font-size: 10px; font-weight: 800; color: #ff8002; letter-spacing: 2px; }
        .order-ref { font-size: 28px; font-weight: 800; letter-spacing: -0.5px; margin: 0; font-variant-numeric: tabular-nums; }
        .mono-date { font-family: monospace; font-size: 11px; color: #64748b; font-weight: 600; letter-spacing: 1px; }

        /* 状态呼吸灯 */
        .status-badge { display: inline-flex; align-items: center; gap: 8px; font-family: monospace; font-size: 12px; font-weight: 800; letter-spacing: 1.5px; padding: 10px 20px; border-radius: 8px; border: 1px solid currentColor; transition: 0.4s; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; }
        .status-pending { color: #d97706; background: #fffbeb; } .status-pending .status-dot { background: #d97706; animation: pulse 2s infinite; }
        .status-processing { color: #2563eb; background: #eff6ff; } .status-processing .status-dot { background: #2563eb; animation: pulse 2s infinite; }
        .status-packaging { color: #9333ea; background: #faf5ff; } .status-packaging .status-dot { background: #9333ea; animation: pulse 2s infinite; }
        .status-shipped { color: #ff8002; background: #fff7ed; } .status-shipped .status-dot { background: #ff8002; box-shadow: 0 0 10px #ff8002; }
        .status-delivered { color: #10b981; background: #ecfdf5; border-color: #10b981;} .status-delivered .status-dot { background: #10b981; }
        @keyframes pulse { 50% { opacity: 0.4; } }

        /* 3. 级联信息栅格 */
        .dossier-grid { display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px dashed #e2e8f0; }
        .grid-panel { padding: 40px 50px; opacity: 0; animation: fadeFloat 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .grid-panel.left { border-right: 1px dashed #e2e8f0; animation-delay: 0.2s; }
        .grid-panel.right { animation-delay: 0.3s; }
        
        .panel-title { font-family: monospace; font-size: 11px; font-weight: 800; color: #94a3b8; letter-spacing: 2px; margin-bottom: 25px; display: block; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; width: fit-content; }
        
        .info-block { display: flex; flex-direction: column; gap: 8px; }
        .ib-name { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }
        .ib-email { font-size: 13px; color: #3b82f6; font-weight: 600; margin: 0; text-decoration: none; transition: 0.2s;}
        .ib-email:hover { color: #2563eb; text-decoration: underline; }
        .ib-address { font-size: 13px; color: #475569; line-height: 1.6; margin: 15px 0 0 0; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9; }
        .ib-phone { font-family: monospace; font-size: 12px; font-weight: 700; color: #0f172a; margin-top: 10px; }

        /* 4. 状态控制台与微动效 */
        .control-console { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; display: flex; flex-direction: column; gap: 20px; transition: 0.3s; }
        .control-console:focus-within { border-color: #cbd5e1; box-shadow: 0 10px 30px rgba(0,0,0,0.03); background: #fff; }
        .cc-warning { font-size: 11px; color: #64748b; line-height: 1.5; margin: 0; }
        
        .custom-select { width: 100%; padding: 14px 20px; border: 2px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-weight: 700; color: #0f172a; outline: none; appearance: none; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 15px center; cursor: pointer; transition: 0.3s; }
        .custom-select:focus { border-color: #ff8002; box-shadow: 0 0 0 4px rgba(255,128,2,0.1); }
        .custom-select:disabled { background-color: #f1f5f9; cursor: not-allowed; color: #94a3b8; }

        /* 沉浸式提交按钮 */
        .btn-update { position: relative; background: #0f172a; color: #fff; border: none; width: 100%; padding: 16px; border-radius: 8px; font-size: 12px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; cursor: pointer; transition: 0.3s; overflow: hidden; }
        .btn-update:hover:not(:disabled) { background: #ff8002; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(255,128,2,0.2); }
        .btn-update:disabled { background: #cbd5e1; cursor: not-allowed; }
        
        /* 按钮 Loading 状态 */
        .btn-update.is-loading { color: transparent; background: #cbd5e1; pointer-events: none; transform: translateY(0); box-shadow: none; }
        .btn-update.is-loading::after {
            content: ""; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: loadSweep 1s infinite;
        }
        @keyframes loadSweep { 100% { left: 200%; } }

        /* ETA 提示块 */
        .eta-block { margin-top: 15px; padding: 12px 15px; background: #ecfdf5; border-left: 3px solid #10b981; border-radius: 0 8px 8px 0; animation: fadeFloat 0.5s ease forwards; }
        .eta-label { display: block; font-family: monospace; font-size: 9px; font-weight: 800; color: #047857; letter-spacing: 1px; margin-bottom: 4px; }
        .eta-value { font-size: 14px; font-weight: 700; color: #064e3b; }

        /* 5. 级联物品清单 */
        .dossier-items { padding: 40px 50px; }
        .dossier-items .panel-title { opacity: 0; animation: fadeFloat 0.5s 0.4s forwards; }
        
        .item-row { display: grid; grid-template-columns: 60px 1fr 100px 120px; align-items: center; padding: 15px 0; border-bottom: 1px solid #f1f5f9; transition: 0.3s; opacity: 0; animation: fadeFloat 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .item-row:nth-child(2) { animation-delay: 0.5s; }
        .item-row:nth-child(3) { animation-delay: 0.6s; }
        .item-row:nth-child(n+4) { animation-delay: 0.7s; }
        .item-row:last-child { border-bottom: none; }
        .item-row:hover { background: #f8fafc; padding-left: 10px; padding-right: 10px; border-radius: 8px; border-color: transparent; }
        
        /* 图片悬浮微距 */
        .item-img-wrap { width: 50px; height: 65px; background: #f8fafc; border-radius: 6px; padding: 5px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden; perspective: 100px; transition: 0.4s; }
        .item-img { width: 100%; height: 100%; object-fit: contain; filter: grayscale(100%) contrast(1.1); transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
        .item-row:hover .item-img-wrap { border-color: #cbd5e1; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .item-row:hover .item-img { filter: grayscale(0%) contrast(1); transform: scale(1.15) translateZ(10px); }

        .item-name { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0; }
        .item-qty { font-family: monospace; font-size: 12px; font-weight: 700; color: #64748b; text-align: center; }
        .item-price { font-family: monospace; font-size: 14px; font-weight: 800; color: #0f172a; text-align: right; }

        /* 6. 财务核算 */
        .fin-ledger { background: #f8fafc; padding: 30px 50px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: flex-end; opacity: 0; animation: fadeFloat 0.6s 0.6s forwards; }
        .fin-box { width: 100%; max-width: 320px; display: flex; flex-direction: column; gap: 12px; }
        .fin-row { display: flex; justify-content: space-between; font-family: monospace; font-size: 12px; font-weight: 600; color: #64748b; }
        .fin-row.total { margin-top: 15px; padding-top: 20px; border-top: 2px solid #e2e8f0; color: #0f172a; font-weight: 800; font-size: 14px; align-items: center; }
        .fin-row.total .total-val { font-family: 'Playfair Display', serif; font-size: 28px; font-style: italic; color: #ff8002; }

        .alert-toast { background: #ecfdf5; color: #047857; padding: 15px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; border: 1px solid #a7f3d0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; animation: elasticUp 0.5s forwards; }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <a href="orders-mgt.php" class="back-link">← Return to Ledger</a>
            
            <div class="dash-header-top">
                <div class="page-title">
                    <h1>Order Dossier</h1>
                </div>
            </div>

            <?php if($msg): ?>
                <div class="alert-toast" id="sys-toast"><span>🛡️</span> <?php echo $msg; ?></div>
            <?php endif; ?>

            <div class="dossier-card <?php echo $msg ? 'update-flash' : ''; ?>">
                
                <div class="dossier-header">
                    <div class="dh-left">
                        <span class="mono-tag">MANIFEST REFERENCE</span>
                        <h2 class="order-ref js-quantum-decrypt" data-final="ORD-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>">
                            SYS-CALC...
                        </h2>
                        <span class="mono-date"><?php echo date('F d, Y - H:i A', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="status-badge status-<?php echo $status_lower; ?>">
                        <span class="status-dot"></span>
                        <?php echo strtoupper($order['status']); ?>
                    </div>
                </div>

                <div class="dossier-grid">
                    <div class="grid-panel left">
                        <span class="panel-title">DISPATCH COORDINATES</span>
                        <div class="info-block">
                            <h3 class="ib-name"><?php echo htmlspecialchars($display_name); ?></h3>
                            <a href="mailto:<?php echo htmlspecialchars($order['email']); ?>" class="ib-email"><?php echo htmlspecialchars($order['email']); ?></a>
                            <p class="ib-address"><?php echo nl2br(htmlspecialchars($display_address)); ?></p>
                            <span class="ib-phone">📞 <?php echo htmlspecialchars($display_phone); ?></span>
                        </div>
                    </div>

                    <div class="grid-panel right">
                        <span class="panel-title">OPERATIONAL CONTROL</span>
                        
                        <div class="control-console">
                            <?php if($order['status'] == 'Delivered' || $order['status'] == 'Refunded'): ?>
                                <p class="cc-warning">This order's lifecycle has concluded. Status modification is securely locked.</p>
                                <button class="btn-update" disabled>CYCLE CLOSED</button>
                            <?php else: ?>
                                <form method="POST" action="" id="statusForm">
                                    <p class="cc-warning" style="margin-bottom: 15px;">Modify the fulfillment stage. The customer will be able to track this change.</p>
                                    
                                    <select name="new_status" class="custom-select" style="margin-bottom: 15px;">
                                        <?php if($order['status'] == 'Paid'): ?><option value="Paid" selected disabled>Awaiting Action...</option><?php endif; ?>
                                        <option value="Processing" <?php if($order['status']=='Processing') echo 'selected'; ?>>Stage 1: Processing</option>
                                        <option value="Packaging" <?php if($order['status']=='Packaging') echo 'selected'; ?>>Stage 2: Packaging</option>
                                        <option value="Shipped" <?php if($order['status']=='Shipped') echo 'selected'; ?>>Stage 3: Shipped (Dispatch)</option>
                                    </select>

                                    <button type="submit" name="update_status" class="btn-update" id="btnUpdate">AUTHORIZE UPDATE</button>
                                </form>
                            <?php endif; ?>

                            <?php if(!empty($order['estimated_delivery'])): ?>
                                <div class="eta-block">
                                    <span class="eta-label">SYSTEM ETA CALCULATION</span>
                                    <span class="eta-value"><?php echo date('l, F d, Y', strtotime($order['estimated_delivery'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="dossier-items">
                    <span class="panel-title">SECURED ARTIFACTS</span>
                    <?php foreach($items_array as $item): ?>
                        <div class="item-row">
                            <div class="item-img-wrap">
                                <img src="../assets/images/<?php echo $item['image']; ?>" class="item-img" alt="Product" onerror="this.style.display='none'">
                            </div>
                            <p class="item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                            <span class="item-qty">x<?php echo str_pad($item['quantity'], 2, '0', STR_PAD_LEFT); ?></span>
                            <span class="item-price">MYR <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="fin-ledger">
                    <div class="fin-box">
                        <div class="fin-row">
                            <span>SUBTOTAL</span>
                            <span>MYR <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="fin-row">
                            <span>SHIPPING FEE</span>
                            <span>MYR <?php echo number_format($shipping_fee, 2); ?></span>
                        </div>
                        <div class="fin-row">
                            <span>TAX (SST/GST)</span>
                            <span>MYR <?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                        <div class="fin-row total">
                            <span>NET AUTHORIZED</span>
                            <span class="total-val"><?php echo number_format($total_price, 2); ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // ✨ 1. 量子解密单号动效
            const decryptEl = document.querySelector('.js-quantum-decrypt');
            if (decryptEl) {
                const finalStr = decryptEl.getAttribute('data-final');
                const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
                let iterations = 0;
                const maxIterations = 15;
                
                const interval = setInterval(() => {
                    decryptEl.innerText = finalStr.split('').map((char, i) => {
                        if (char === '-' || char === 'O' || char === 'R' || char === 'D') return char;
                        if (i < iterations / 2) return finalStr[i];
                        return chars[Math.floor(Math.random() * chars.length)];
                    }).join('');
                    
                    iterations++;
                    if (iterations >= maxIterations * 2) {
                        clearInterval(interval);
                        decryptEl.innerText = finalStr;
                    }
                }, 30);
            }

            // ✨ 2. 按钮 Loading 拦截
            const statusForm = document.getElementById('statusForm');
            if (statusForm) {
                statusForm.addEventListener('submit', function() {
                    const btn = document.getElementById('btnUpdate');
                    btn.classList.add('is-loading');
                });
            }

            // ✨ 3. Toast 自动消失
            const toast = document.getElementById('sys-toast');
            if(toast) {
                setTimeout(() => {
                    toast.style.transition = '0.5s';
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-10px)';
                    setTimeout(() => toast.remove(), 500);
                }, 3000);
            }
        });
    </script>
</body>
</html>
