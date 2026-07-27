<?php
// 1. 初始化与权限检查
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

// 核心安全：拦截未登录或非管理员用户
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}

$message = "";

// ==========================================
// 2. 处理 Admin 的派发请求 (Approve Action)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_request_id'])) {
    $request_id = (int)$_POST['approve_request_id'];
    
    // 查找这条 pending 申请对应的用户
    $check_query = mysqli_query($conn, "SELECT user_id FROM reward_requests WHERE id = '$request_id' AND status = 'pending'");
    
    if (mysqli_num_rows($check_query) > 0) {
        $row = mysqli_fetch_assoc($check_query);
        $target_user_id = $row['user_id'];
        
        // 生成一个全球唯一的 10% 折扣码
        $unique_code = 'FAIFA10-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        
        // ✨ 核心修复：设定今天为生效日，一年后过期
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+1 year'));
        
        // ✨ 核心修复：使用新规范写入数据库，指明类型、数值和有效期 (同时兼容写入旧字段 discount_percent 防报错)
        $insert_coupon = "INSERT INTO coupons (user_id, code, discount_type, discount_value, discount_percent, start_date, end_date, is_used) 
                          VALUES ('$target_user_id', '$unique_code', 'percentage', 10, 10, '$start_date', '$end_date', 0)";
        
        if (mysqli_query($conn, $insert_coupon)) {
            // 更新申请状态为 approved
            mysqli_query($conn, "UPDATE reward_requests SET status = 'approved' WHERE id = '$request_id'");

            // ==========================================
            // ✨ 发送通知给用户 (Notification Center Link)
            // ==========================================
            $notif_title = "🎁 Your 10% Reward is here!";
            $notif_msg = "Congratulations! Your daily check-in reward has been approved. Use code: **$unique_code** at checkout to get 10% off your order!";
            
            $insert_notif = "INSERT INTO notifications (user_id, title, message, is_read) 
                             VALUES ('$target_user_id', '$notif_title', '$notif_msg', 0)";
            mysqli_query($conn, $insert_notif);
            // ==========================================

            $message = "<div class='alert success'>✅ Approved! Code <strong>$unique_code</strong> issued and notification sent to User ID: $target_user_id!</div>";
        } else {
            $message = "<div class='alert error'>❌ Database Error: Failed to generate coupon.</div>";
        }
    } else {
        $message = "<div class='alert error'>❌ Invalid request or already approved.</div>";
    }
}

// ==========================================
// 3. 获取所有 Pending 状态的申请记录
// ==========================================
$query = "SELECT r.id AS request_id, r.user_id, r.created_at, u.full_name, u.email 
          FROM reward_requests r 
          LEFT JOIN users u ON r.user_id = u.id 
          WHERE r.status = 'pending' 
          ORDER BY r.created_at ASC";
$pending_requests = mysqli_query($conn, $query);
$pending_count = mysqli_num_rows($pending_requests);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Reward Approvals - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        /* 沿用你的 SaaS 动画 */
        @keyframes elasticUp { 0% { opacity: 0; transform: translateY(40px) scale(0.95); } 60% { opacity: 1; transform: translateY(-5px) scale(1.01); } 100% { opacity: 1; transform: translateY(0) scale(1); } }
        
        .admin-main { padding-bottom: 50px; }

        /* 顶部导航占位 */
        .dash-header-top { display: flex; justify-content: flex-end; margin-bottom: 30px; animation: elasticUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) both; }

        .recent-table-card { 
            background: #fff; padding: 30px; border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); 
            border: 1px solid rgba(226, 232, 240, 0.6); 
            animation: elasticUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both; 
        }

        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .header-flex h3 { margin: 0; font-size: 20px; font-weight: 800; color: #0f172a; }
        .badge-count { background: #fef08a; color: #a16207; font-size: 13px; padding: 6px 12px; border-radius: 20px; font-weight: 800; border: 1px solid #facc15; }

        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .admin-table th { color: #94a3b8; font-size: 12px; text-align: left; padding: 0 10px 15px 10px; border-bottom: 1px solid #e2e8f0; text-transform: uppercase; font-weight: 800; letter-spacing: 1px;}
        .admin-table td { padding: 20px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .admin-table tbody tr { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); border-radius: 8px; }
        .admin-table tbody tr:hover { background: #f8fafc; transform: scale(1.01); box-shadow: 0 10px 25px rgba(0,0,0,0.04); z-index: 10; position: relative; }

        .btn-approve { 
            background: linear-gradient(135deg, #facc15, #f59e0b); color: #fff; 
            border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 800; 
            cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .btn-approve:hover { transform: translateY(-2px) scale(1.05); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.5); }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state .emoji { font-size: 48px; margin-bottom: 15px; opacity: 0.5; }
        .empty-state h4 { margin: 0; font-size: 18px; color: #475569; }

        .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; font-size: 14px; animation: elasticUp 0.5s both; }
        .alert.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        
        .avatar-box { width: 40px; height: 40px; background: #ffecd9; color: #ff8002; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 900; }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="dash-header-top">
                 <div class="dash-greeting">
                    <h1 style="margin:0; font-size: 24px;">Rewards Approval</h1>
                    <p style="margin: 5px 0 0 0; color: #64748b;">Issue coupons and notify customers instantly.</p>
                </div>
            </div>

            <?php echo $message; ?>

            <div class="recent-table-card">
                <div class="header-flex">
                    <h3>Pending Requests</h3>
                    <span class="badge-count"><?php echo $pending_count; ?> Awaiting</span>
                </div>

                <?php if ($pending_count > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>REQ ID</th>
                                <th>CUSTOMER</th>
                                <th>EMAIL</th>
                                <th>TARGET REACHED</th>
                                <th style="text-align: right;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($req = mysqli_fetch_assoc($pending_requests)): ?>
                                <tr>
                                    <td><strong style="color: #64748b;">#REQ-<?php echo $req['request_id']; ?></strong></td>
                                    
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <div class="avatar-box">
                                                <?php echo strtoupper(substr($req['full_name'] ?? 'U', 0, 2)); ?>
                                            </div>
                                            <div>
                                                <span style="font-weight: 800; color: #0f172a; display: block; margin-bottom: 3px;">
                                                    <?php echo htmlspecialchars($req['full_name'] ?? 'Unknown User'); ?>
                                                </span>
                                                <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">UID: <?php echo $req['user_id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td style="color: #475569; font-weight: 600; font-size: 13px;">
                                        <?php echo htmlspecialchars($req['email'] ?? 'No email'); ?>
                                    </td>
                                    
                                    <td style="color: #64748b; font-size: 13px; font-weight: 600;">
                                        <?php echo date('M d, Y - h:i A', strtotime($req['created_at'])); ?>
                                    </td>
                                    
                                    <td style="text-align: right;">
                                        <form method="POST" action="">
                                            <input type="hidden" name="approve_request_id" value="<?php echo $req['request_id']; ?>">
                                            <button type="submit" class="btn-approve" onclick="return confirm('Generate a 10% coupon and send notification to <?php echo htmlspecialchars($req['full_name'] ?? 'user'); ?>?');">
                                                Approve & Notify
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="emoji">🎉</div>
                        <h4>All Caught Up!</h4>
                        <p style="font-size: 13px; margin-top: 5px;">There are no pending reward requests at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
