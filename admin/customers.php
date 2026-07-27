<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

$search_keyword = "";
$where_clause = " WHERE u.email != 'admin123@gmail.com' "; 

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_keyword = mysqli_real_escape_string($conn, trim($_GET['search']));
    $where_clause .= " AND (u.full_name LIKE '%$search_keyword%' OR u.email LIKE '%$search_keyword%') ";
}

$sql = "SELECT u.id, u.full_name, u.email, u.created_at, 
               COUNT(o.id) as total_orders, 
               SUM(CASE WHEN o.status != 'Refunded' THEN o.total_price ELSE 0 END) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id 
        $where_clause
        GROUP BY u.id
        ORDER BY u.created_at DESC";

$customers_res = mysqli_query($conn, $sql);
$total_customers_shown = mysqli_num_rows($customers_res);

$kpi_cust = mysqli_query($conn, "SELECT COUNT(id) as c FROM users WHERE email != 'admin123@gmail.com'");
$kpi_total_users = mysqli_fetch_assoc($kpi_cust)['c'];

$kpi_rev = mysqli_query($conn, "SELECT SUM(total_price) as r FROM orders WHERE status != 'Refunded'");
$kpi_total_revenue = mysqli_fetch_assoc($kpi_rev)['r'] ?? 0;
$avg_value = $kpi_total_users > 0 ? ($kpi_total_revenue / $kpi_total_users) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Manage Customers - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        
        @keyframes elasticUp {
            0% { opacity: 0; transform: translateY(40px) scale(0.96); }
            60% { opacity: 1; transform: translateY(-5px) scale(1.01); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes slideInRow {
            0% { opacity: 0; transform: translateX(-20px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        @keyframes btnShine {
            0% { left: -100%; opacity: 0; }
            20% { left: 100%; opacity: 1; }
            100% { left: 100%; opacity: 0; }
        }

        .dash-header-top { 
            display: flex; justify-content: space-between; align-items: flex-end; 
            margin-bottom: 25px; 
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        .page-title h1 { margin: 0 0 5px 0; font-size: 26px; color: #0f172a; }
        .page-title p { margin: 0; color: #64748b; font-size: 14px; }
        
        .btn-export {
            position: relative; overflow: hidden; background: #fff; 
            color: #1e293b; padding: 12px 24px; border-radius: 10px; border: 1px solid #e2e8f0; 
            font-weight: 700; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.02); 
            transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;
        }
        .btn-export:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); border-color: #cbd5e1; }
        .btn-export:active { transform: translateY(1px); }

        .mini-kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .mini-kpi-card { 
            background: #fff; padding: 25px 20px; border-radius: 16px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 15px; 
            border-left: 4px solid #ff8002; border-top: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .mini-kpi-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.06); }
        
        .mini-kpi-card:nth-child(1) { animation-delay: 0.1s; }
        .mini-kpi-card:nth-child(2) { animation-delay: 0.18s; border-left-color: #10b981; }
        .mini-kpi-card:nth-child(3) { animation-delay: 0.26s; border-left-color: #8b5cf6; }

        .kpi-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; background: #f8fafc; transition: transform 0.3s ease; }
        .mini-kpi-card:hover .kpi-icon { transform: scale(1.1) rotate(5deg); }
        
        .kpi-text h4 { margin: 0 0 6px 0; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .kpi-text p { margin: 0; font-size: 24px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px; }

        .admin-card { 
            padding: 0; overflow: hidden; background: #fff; border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid rgba(226, 232, 240, 0.8);
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.35s both;
        }

        .toolbar { padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .search-box { position: relative; width: 400px; }
        .search-box input { 
            width: 100%; padding: 12px 15px 12px 40px; border: 2px solid #f1f5f9; border-radius: 10px; 
            background: #f8fafc; color: #1e293b; outline: none; font-size: 14px; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-sizing: border-box; 
        }
        .search-box input:focus { 
            border-color: #ff8002; background: #fff; 
            box-shadow: 0 8px 20px rgba(255,128,2,0.08), 0 0 0 4px rgba(255,128,2,0.1); 
            transform: translateY(-2px); 
        }
        .search-box span { position: absolute; left: 15px; top: 13px; color: #94a3b8; font-size: 14px; transition: color 0.3s; }
        .search-box:focus-within span { color: #ff8002; }

        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { text-align: left; padding: 18px 25px; font-size: 11px; color: #94a3b8; font-weight: 800; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px; }
        .admin-table td { padding: 18px 25px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .cust-row { 
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); 
            opacity: 0; animation: slideInRow 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards; 
        }
        .cust-row:nth-child(1) { animation-delay: 0.4s; }
        .cust-row:nth-child(2) { animation-delay: 0.45s; }
        .cust-row:nth-child(3) { animation-delay: 0.5s; }
        .cust-row:nth-child(4) { animation-delay: 0.55s; }
        .cust-row:nth-child(5) { animation-delay: 0.6s; }
        .cust-row:nth-child(n+6) { animation-delay: 0.65s; }

        .cust-row:hover { 
            background: #fff;
            transform: scale(1.01) translateY(-2px); 
            box-shadow: 0 10px 25px rgba(0,0,0,0.06); 
            position: relative; z-index: 10;
        }

        .customer-cell { display: flex; align-items: center; gap: 15px; }
        .avatar { 
            width: 45px; height: 45px; border-radius: 50%; background: #f1f5f9; color: #475569; 
            display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; 
            transition: transform 0.3s ease; box-shadow: inset 0 2px 4px rgba(255,255,255,0.5);
        }
        .cust-row:hover .avatar { transform: scale(1.1); }
        
        .avatar.vip { 
            background: linear-gradient(135deg, #fef08a, #facc15); color: #713f12; 
            box-shadow: 0 4px 10px rgba(250, 204, 21, 0.3), inset 0 2px 4px rgba(255,255,255,0.6);
        } 
        
        .cust-details { display: flex; flex-direction: column; }
        .cust-name { color: #0f172a; font-weight: 700; font-size: 14.5px; }
        .cust-email { color: #64748b; font-size: 12.5px; margin-top: 3px; font-weight: 500; }
        
        .badge-vip { background: #fef08a; color: #854d0e; font-size: 9px; padding: 3px 8px; border-radius: 6px; font-weight: 900; margin-left: 10px; letter-spacing: 0.5px; border: 1px solid #fde047; }
        .badge-new { background: #dcfce7; color: #166534; font-size: 9px; padding: 3px 8px; border-radius: 6px; font-weight: 900; margin-left: 10px; letter-spacing: 0.5px; border: 1px solid #bbf7d0; }

        .order-pill { background: #f8fafc; padding: 6px 12px; border-radius: 8px; font-weight: 700; color: #475569; font-size: 13px; border: 1px solid #e2e8f0; display: inline-block; }

        .action-link { 
            color: #ff8002; text-decoration: none; font-size: 13px; font-weight: 700; 
            padding: 8px 16px; border: 2px solid #ffedd5; border-radius: 8px; 
            transition: all 0.2s ease; display: inline-block; background: #fff;
        }
        .action-link:hover { background: #ff8002; color: #fff; border-color: #ff8002; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(255, 128, 2, 0.2); }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="dash-header-top">
                <div class="page-title">
                    <div style="font-size: 11px; color: #64748b; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 8px;">DASHBOARD > CUSTOMERS</div>
                    <h1>Customer Directory</h1>
                    <p>View your registered users, their purchase history, and lifetime value.</p>
                </div>
                <button class="btn-export">
                    <span>📥</span> Export CSV
                </button>
            </div>

            <div class="mini-kpi-grid">
                <div class="mini-kpi-card">
                    <div class="kpi-icon" style="color: #ff8002;">👥</div>
                    <div class="kpi-text">
                        <h4>Total Registered</h4>
                        <p class="kpi-num" data-target="<?php echo $kpi_total_users; ?>">0</p>
                    </div>
                </div>
                <div class="mini-kpi-card green">
                    <div class="kpi-icon" style="color: #10b981; background: #dcfce7;">💵</div>
                    <div class="kpi-text">
                        <h4>Lifetime Revenue</h4>
                        <p class="kpi-num" data-prefix="MYR " data-target="<?php echo $kpi_total_revenue; ?>">0.00</p>
                    </div>
                </div>
                <div class="mini-kpi-card purple">
                    <div class="kpi-icon" style="color: #8b5cf6; background: #ede9fe;">⭐</div>
                    <div class="kpi-text">
                        <h4>Avg. Order Value</h4>
                        <p class="kpi-num" data-prefix="MYR " data-target="<?php echo $avg_value; ?>">0.00</p>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <form method="GET" action="customers.php" class="toolbar">
                    <div class="search-box">
                        <span>🔍</span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Search by name or email...">
                    </div>
                    <?php if(!empty($search_keyword)): ?>
                        <a href="customers.php" style="color: #ef4444; font-size: 13px; font-weight: 700; text-decoration: none; padding: 8px 16px; background: #fee2e2; border-radius: 8px; transition: 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">Clear Filter</a>
                    <?php endif; ?>
                </form>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>CUSTOMER</th>
                            <th>JOINED DATE</th>
                            <th>TOTAL ORDERS</th>
                            <th>TOTAL SPENT</th>
                            <th style="text-align: right; padding-right: 35px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_customers_shown > 0): ?>
                            <?php while($user = mysqli_fetch_assoc($customers_res)): 
                                $initials = strtoupper(substr($user['full_name'], 0, 2));
                                $spent = floatval($user['total_spent']);
                                
                                $is_new = (strtotime($user['created_at']) >= strtotime('-7 days'));
                                $is_vip = ($spent >= 500); 
                            ?>
                            <tr class="cust-row">
                                <td>
                                    <div class="customer-cell">
                                        <div class="avatar <?php echo $is_vip ? 'vip' : ''; ?>">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div class="cust-details">
                                            <div style="display: flex; align-items: center;">
                                                <span class="cust-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                                <?php if($is_vip): ?><span class="badge-vip">VIP</span><?php endif; ?>
                                                <?php if($is_new): ?><span class="badge-new">NEW</span><?php endif; ?>
                                            </div>
                                            <span class="cust-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td style="color: #64748b; font-size: 13px; font-weight: 500;">
                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td>
                                    <span class="order-pill">
                                        <?php echo $user['total_orders']; ?> orders
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: <?php echo $is_vip ? '#b45309' : '#0f172a'; ?>; font-size: 14px;">
                                        MYR <?php echo number_format($spent, 2); ?>
                                    </strong>
                                </td>
                                <td style="text-align: right; padding-right: 30px;">
                                    <a href="orders-mgt.php?search=<?php echo urlencode($user['email']); ?>" class="action-link">View History</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="cust-row">
                                <td colspan="5" style="text-align: center; padding: 60px 20px; color: #64748b;">
                                    <div style="font-size: 50px; margin-bottom: 15px; opacity: 0.5;">👥</div>
                                    <p style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">No customers found</p>
                                    <?php if(!empty($search_keyword)): ?>
                                        <p style="margin: 5px 0 0 0; font-size: 14px;">No one matches "<?php echo htmlspecialchars($search_keyword); ?>"</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div style="padding: 20px 25px; color: #64748b; font-size: 13px; font-weight: 500; border-top: 1px solid #f1f5f9; background: #f8fafc;">
                    Showing <strong><?php echo $total_customers_shown; ?></strong> registered customers
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const counters = document.querySelectorAll('.kpi-num');
            const speed = 100; 
            
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const prefix = counter.getAttribute('data-prefix') || '';
                const isFloat = target % 1 !== 0; 
                
                const updateCount = () => {
                    const count = +counter.innerText.replace(/[^0-9.-]+/g,"");
                    const inc = target / speed;

                    if (count < target) {
                        let nextVal = count + inc;
                        if(isFloat) {
                            counter.innerText = prefix + (nextVal).toFixed(2);
                        } else {
                            counter.innerText = prefix + Math.ceil(nextVal);
                        }
                        setTimeout(updateCount, 15);
                    } else {
                        if(isFloat) {
                            counter.innerText = prefix + target.toLocaleString('en-US', {minimumFractionDigits: 2});
                        } else {
                            counter.innerText = prefix + target.toLocaleString('en-US');
                        }
                    }
                };
                setTimeout(updateCount, 400); 
            });
        });
    </script>
</body>
</html>
