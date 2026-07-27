<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

$cleanup_sql = "DELETE FROM coupons WHERE expiry_date IS NOT NULL AND expiry_date < NOW()";
mysqli_query($conn, $cleanup_sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_reward'])) {
    $target_user_id = intval($_POST['request_user_id']); 
    
    $unique_code = 'REWARD-' . strtoupper(substr(md5(time() . $target_user_id), 0, 6));
    
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+1 day'));
    $expiry_date = date('Y-m-d H:i:s', strtotime('+24 hours')); 
    
    $insert_sql = "INSERT INTO coupons (code, discount_type, discount_value, usage_limit, start_date, end_date, expiry_date) 
                   VALUES ('$unique_code', 'percentage', 10, 1, '$start_date', '$end_date', '$expiry_date')";
                   
    if (mysqli_query($conn, $insert_sql)) {
        $msg = "Great news! Your 10% daily check-in reward is ready. Use code: " . $unique_code . " (Valid for 24 hours!)";
        mysqli_query($conn, "INSERT INTO notifications (user_id, message, is_read, created_at) 
                             VALUES ('$target_user_id', '$msg', 0, NOW())");
                             
        mysqli_query($conn, "DELETE FROM reward_requests WHERE user_id = $target_user_id");

        header("Location: manage-coupons.php?msg=reward_generated");
        exit();
    }
}

if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM coupons WHERE id = $del_id");
    header("Location: manage-coupons.php?msg=deleted");
    exit();
}

$search_keyword = "";
$where_clause = "";
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_keyword = mysqli_real_escape_string($conn, trim($_GET['search']));
    $where_clause = " WHERE code LIKE '%$search_keyword%' ";
}

$sql = "SELECT * FROM coupons $where_clause ORDER BY id DESC";
$coupons_res = mysqli_query($conn, $sql);
$total_coupons = mysqli_num_rows($coupons_res);

$today = date('Y-m-d');

$pending_requests_res = mysqli_query($conn, "SELECT r.*, u.full_name FROM reward_requests r JOIN users u ON r.user_id = u.id WHERE r.status = 'Pending' ORDER BY r.id ASC");
$pending_count = $pending_requests_res ? mysqli_num_rows($pending_requests_res) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Manage Coupons - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        
        @keyframes elasticUp {
            0% { opacity: 0; transform: translateY(40px) scale(0.96); }
            60% { opacity: 1; transform: translateY(-5px) scale(1.01); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes slideInRow {
            0% { opacity: 0; transform: translateX(-30px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        @keyframes radarPulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @keyframes btnShine {
            0% { left: -100%; opacity: 0; }
            20% { left: 100%; opacity: 1; }
            100% { left: 100%; opacity: 0; }
        }

        @keyframes tooltipPop {
            0% { opacity: 0; transform: translate(-50%, 10px) scale(0.8); }
            50% { transform: translate(-50%, -15px) scale(1.1); }
            100% { opacity: 1; transform: translate(-50%, -10px) scale(1); }
        }

        .dash-header-top { 
            display: flex; justify-content: space-between; align-items: flex-end; 
            margin-bottom: 25px; 
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        .page-title h1 { margin: 0 0 5px 0; font-size: 26px; color: #0f172a; }
        .page-title p { margin: 0; color: #64748b; font-size: 14px; }
        
        .btn-primary {
            position: relative; overflow: hidden; background: linear-gradient(135deg, #ff8002, #ea580c); 
            color: #fff; padding: 12px 24px; border-radius: 10px; border: none; font-weight: 700; text-decoration: none;
            cursor: pointer; box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3); transition: all 0.3s ease; 
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(234, 88, 12, 0.4); }
        .btn-primary:active { transform: translateY(1px); }
        .btn-primary::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-20deg); animation: btnShine 3s infinite 1s;
        }

        .toolbar-card { 
            background: #fff; padding: 20px 25px; border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.15s both;
        }
        
        .search-box { position: relative; width: 450px; }
        .search-box input { 
            width: 100%; padding: 14px 15px 14px 45px; border: 2px solid #f1f5f9; border-radius: 12px; 
            background: #f8fafc; color: #1e293b; outline: none; font-size: 14px; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-sizing: border-box; 
        }
        .search-box input:focus { 
            border-color: #ff8002; background: #fff; 
            box-shadow: 0 8px 20px rgba(255,128,2,0.08), 0 0 0 4px rgba(255,128,2,0.1); 
            transform: translateY(-2px); 
        }
        .search-box span { position: absolute; left: 18px; top: 15px; color: #94a3b8; font-size: 16px; transition: color 0.3s; }
        .search-box:focus-within span { color: #ff8002; }

        .table-container { animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both; }
        .admin-table { 
            width: 100%; border-collapse: separate; border-spacing: 0 12px; 
            margin-top: -12px; 
        }
        .admin-table th { text-align: left; padding: 10px 25px; font-size: 11px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border: none; }
        
        .admin-table td { 
            background: #fff; padding: 22px 25px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; vertical-align: middle; 
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .admin-table td:first-child { border-left: 1px solid #f1f5f9; border-radius: 16px 0 0 16px; }
        .admin-table td:last-child { border-right: 1px solid #f1f5f9; border-radius: 0 16px 16px 0; }

        .cp-row { 
            opacity: 0; animation: slideInRow 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.01);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .cp-row:nth-child(1) { animation-delay: 0.3s; }
        .cp-row:nth-child(2) { animation-delay: 0.38s; }
        .cp-row:nth-child(3) { animation-delay: 0.46s; }
        .cp-row:nth-child(4) { animation-delay: 0.54s; }
        .cp-row:nth-child(5) { animation-delay: 0.62s; }
        .cp-row:nth-child(n+6) { animation-delay: 0.7s; }

        .admin-table:hover .cp-row:not(:hover) {
            opacity: 0.4;
            transform: scale(0.98);
            filter: grayscale(40%);
        }
        .cp-row:hover td { 
            background: #fff;
            border-color: #e2e8f0;
        }
        .cp-row:hover {
            transform: scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            position: relative; z-index: 10;
        }

        .code-wrapper { position: relative; display: inline-block; cursor: pointer; }
        .code-box { 
            background: #fff7ed; color: #ea580c; border: 2px dashed #fed7aa; 
            padding: 8px 16px; border-radius: 10px; font-weight: 900; letter-spacing: 1.5px; font-size: 15px;
            display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .code-icon { font-size: 14px; opacity: 0.6; transition: opacity 0.2s; }
        
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

        .st-pill { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; text-transform: uppercase; border: 1px solid transparent; }
        .st-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; }
        
        .st-active { background: #ecfdf5; color: #047857; border-color: #a7f3d0; } 
        .st-active::before { background: #10b981; animation: radarPulse 2s infinite; } 
        
        .st-expired { background: #f8fafc; color: #64748b; border-color: #e2e8f0; } 
        .st-expired::before { background: #94a3b8; }
        
        .st-scheduled { background: #fefce8; color: #a16207; border-color: #fde047; } 
        .st-scheduled::before { background: #eab308; }
        
        .action-icon { text-decoration: none; padding: 10px; font-size: 16px; border-radius: 10px; transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); cursor: pointer; display: inline-block; background: #f8fafc; border: 1px solid #f1f5f9; }
        .action-icon.delete { color: #ef4444; } 
        .action-icon.delete:hover { background: #fee2e2; border-color: #fca5a5; transform: scale(1.15) rotate(5deg); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="dash-header-top">
                <div class="page-title">
                    <div style="font-size: 11px; color: #64748b; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 8px; text-transform: uppercase;">DASHBOARD > MARKETING</div>
                    <h1>Manage Campaigns & Rewards</h1>
                    <p>Track promotional codes and approve daily check-in rewards.</p>
                </div>
                <a href="create-coupons.php" class="btn-primary">
                    <span style="font-size: 16px;">🎟️</span> Create New Coupon
                </a>
            </div>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 16px 20px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600; animation: elasticUp 0.4s both; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.1);">
                    <span style="font-size: 20px;">🗑️</span> Coupon has been permanently deleted.
                </div>
            <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'reward_generated'): ?>
                <div style="background: #dcfce7; border: 1px solid #86efac; color: #16a34a; padding: 16px 20px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600; animation: elasticUp 0.4s both; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(22, 163, 74, 0.1);">
                    <span style="font-size: 20px;">✅</span> 10% Reward Coupon successfully generated and sent to the user!
                </div>
            <?php endif; ?>

            <?php if ($pending_count > 0): ?>
            <div class="toolbar-card" style="display: block; background: #fffbeb; border-color: #fde68a; animation-delay: 0.1s;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin:0; color: #b45309; font-size: 16px; display:flex; align-items:center; gap:8px;">
                        <span class="st-pill st-scheduled" style="padding: 4px 8px;"></span> Pending Reward Requests (<?php echo $pending_count; ?>)
                    </h3>
                    <span style="font-size: 12px; color: #92400e; font-weight: 600;">Users who completed 7-day streak</span>
                </div>
                
                <table style="width: 100%; text-align: left; font-size: 14px; border-collapse: collapse;">
                    <?php while($req = mysqli_fetch_assoc($pending_requests_res)): ?>
                    <tr style="border-top: 1px dashed #fcd34d;">
                        <td style="padding: 15px 5px;">
                            <strong style="color: #1e293b;"><?php echo htmlspecialchars($req['full_name']); ?></strong> 
                            <span style="color: #64748b; font-size: 12px; margin-left: 10px;">User ID: #<?php echo $req['user_id']; ?></span>
                        </td>
                        <td style="text-align: right; padding: 15px 5px;">
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="request_user_id" value="<?php echo $req['user_id']; ?>">
                                <button type="submit" name="generate_reward" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                    ✨ Generate 10% Code (24h)
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php endif; ?>

            <div class="toolbar-card">
                <form method="GET" action="manage-coupons.php" style="margin: 0; display: flex; align-items: center; gap: 15px;">
                    <div class="search-box">
                        <span>🔍</span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Search by coupon code (e.g. SUMMER24)...">
                    </div>
                    <button type="submit" style="background: #1e293b; color: #fff; border: none; padding: 14px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s;">Filter</button>
                    
                    <?php if(!empty($search_keyword)): ?>
                        <a href="manage-coupons.php" style="color: #ef4444; font-size: 13px; font-weight: 700; text-decoration: none; padding: 12px 16px; background: #fee2e2; border-radius: 10px; transition: 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">Clear</a>
                    <?php endif; ?>
                </form>
                <div style="font-size: 13px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 8px 16px; border-radius: 8px;">
                    Total: <strong style="color: #0f172a; font-size: 16px;"><?php echo $total_coupons; ?></strong>
                </div>
            </div>

            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>COUPON CODE</th>
                            <th>VALUE</th>
                            <th>VALIDITY TIMELINE</th>
                            <th>USAGE DATA</th>
                            <th>STATUS</th>
                            <th style="text-align: right; padding-right: 35px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_coupons > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($coupons_res)): 
                                
                                $used_count = isset($row['used_count']) ? intval($row['used_count']) : 0;
                                $limit_val = $row['usage_limit'];
                                
                                $status_class = 'st-active';
                                $status_text = 'Active Live';
                                
                                if (!empty($row['expiry_date']) && date('Y-m-d H:i:s') > $row['expiry_date']) {
                                    $status_class = 'st-expired';
                                    $status_text = 'Expired';
                                } elseif ($today < $row['start_date']) {
                                    $status_class = 'st-scheduled';
                                    $status_text = 'Scheduled';
                                } elseif ($today > $row['end_date']) {
                                    $status_class = 'st-expired';
                                    $status_text = 'Expired';
                                }

                                $discount_display = '';
                                if ($row['discount_type'] == 'percentage') {
                                    $discount_display = floatval($row['discount_value']) . '% OFF';
                                    $discount_color = '#3b82f6'; 
                                } else {
                                    $discount_display = '- MYR ' . number_format($row['discount_value'], 2);
                                    $discount_color = '#10b981'; 
                                }
                                
                                $limit_display = (is_null($limit_val) || $limit_val == 0 || $limit_val === '') ? 'Unlimited' : $limit_val . ' /user';
                            ?>
                            <tr class="cp-row">
                                <td>
                                    <div class="code-wrapper" onclick="copyCode(this, '<?php echo htmlspecialchars($row['code']); ?>')">
                                        <span class="code-box">
                                            <?php echo htmlspecialchars($row['code']); ?>
                                            <span class="code-icon">📄</span>
                                        </span>
                                        <span class="copy-tooltip">✅ Copied!</span>
                                    </div>
                                    <?php if(!empty($row['expiry_date'])): ?>
                                        <div style="font-size: 11px; color: #ef4444; margin-top: 5px; font-weight: 700;">⏱️ 24h Reward</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: <?php echo $discount_color; ?>; font-size: 16px; font-weight: 900; background: <?php echo $discount_color; ?>15; padding: 6px 12px; border-radius: 8px;">
                                        <?php echo $discount_display; ?>
                                    </strong>
                                </td>
                                <td style="color: #64748b; font-size: 13px; font-weight: 600;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="color: #0f172a;"><?php echo date('M d', strtotime($row['start_date'])); ?></span>
                                        <span style="color: #cbd5e1;">➔</span>
                                        <span style="color: <?php echo ($status_class=='st-expired') ? '#ef4444' : '#0f172a'; ?>"><?php echo date('M d, Y', strtotime($row['end_date'])); ?></span>
                                    </div>
                                </td>
                                <td style="color: #64748b; font-size: 13px;">
                                    <div style="background: #f8fafc; display: inline-flex; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                        <span style="padding: 6px 12px; background: #fff; border-right: 1px solid #e2e8f0;"><strong style="color: #0f172a;"><?php echo $used_count; ?></strong> Used</span>
                                        <span style="padding: 6px 12px;"><?php echo $limit_display; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="st-pill <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td style="text-align: right; padding-right: 25px;">
                                    <a href="manage-coupons.php?delete=<?php echo $row['id']; ?>" class="action-icon delete" title="Delete Coupon" onclick="return confirm('🚨 Delete coupon <?php echo $row['code']; ?>? This cannot be undone.');">🗑️</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="cp-row">
                                <td colspan="6" style="text-align: center; padding: 80px 20px; color: #64748b; background: #fff; border-radius: 16px; border: 2px dashed #e2e8f0;">
                                    <div style="font-size: 50px; margin-bottom: 20px; opacity: 0.5;">🎫</div>
                                    <p style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a;">No coupons found</p>
                                    <p style="margin: 8px 0 25px 0; font-size: 14px;">Running a promotion? Create a code to boost sales.</p>
                                    <a href="create-coupons.php" class="btn-primary" style="font-size: 14px; padding: 12px 28px;">➕ Generate First Coupon</a>
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
            }).catch(err => {
                console.error('Failed to copy text: ', err);
            });
        }
    </script>
</body>
</html>
