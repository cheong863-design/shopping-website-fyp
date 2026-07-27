<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}

$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';
$sales_query = mysqli_query($conn, "SELECT SUM(total_price) as total_sales FROM orders");
$sales_data = mysqli_fetch_assoc($sales_query);
$total_sales = $sales_data['total_sales'] ? $sales_data['total_sales'] : 0.00;

$orders_query = mysqli_query($conn, "SELECT COUNT(id) as total_orders FROM orders");
$orders_data = mysqli_fetch_assoc($orders_query);
$total_orders = $orders_data['total_orders'];

$customers_query = mysqli_query($conn, "SELECT COUNT(id) as total_customers FROM users WHERE email != 'admin123@gmail.com'");
$customers_data = mysqli_fetch_assoc($customers_query);
$total_customers = $customers_data['total_customers'];

$low_stock_query = mysqli_query($conn, "SELECT id, name, stock FROM products WHERE stock < 5 LIMIT 10");
$notifications = [];
while($notif = mysqli_fetch_assoc($low_stock_query)) {
    $notifications[] = $notif;
}
$notif_count = count($notifications);

$recent_orders_sql = "
    SELECT o.*, u.full_name, p.name as product_name
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.created_at DESC LIMIT 5
";
$recent_orders_res = mysqli_query($conn, $recent_orders_sql);

$best_sellers_sql = "
    SELECT p.id, p.name, p.price, p.image, COUNT(oi.product_id) as sales_count
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'Cancelled'
    GROUP BY p.id
    ORDER BY sales_count DESC
    LIMIT 3
";
$best_sellers_res = mysqli_query($conn, $best_sellers_sql);

if (!$best_sellers_res || mysqli_num_rows($best_sellers_res) == 0) {
    $best_sellers_sql = "SELECT id, name, price, image, 0 as sales_count FROM products ORDER BY id DESC LIMIT 3";
    $best_sellers_res = mysqli_query($conn, $best_sellers_sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Dashboard - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        @keyframes elasticUp { 0% { opacity: 0; transform: translateY(40px) scale(0.95); } 60% { opacity: 1; transform: translateY(-5px) scale(1.01); } 100% { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes barGrowPremium { 0% { transform: scaleY(0); opacity: 0; } 100% { transform: scaleY(1); opacity: 1; } }
        @keyframes holographicSweep { 0% { top: 100%; opacity: 0; } 50% { opacity: 0.8; } 100% { top: -50%; opacity: 0; } }
        @keyframes pulseShadow { 0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); } 50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); } }

        .dashboard-main { padding-bottom: 50px; }
        
        .dash-header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; animation: elasticUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) both; position: relative; z-index: 1000; }
        .dash-greeting { animation: elasticUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both; }

        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .kpi-card { 
            position: relative; background: #fff; padding: 25px 20px; 
            border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); 
            border: 1px solid rgba(226, 232, 240, 0.6); overflow: hidden; 
            animation: elasticUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) both;
            transition: transform 0.3s ease, border-color 0.3s ease; z-index: 10;
        }
        .kpi-card::before {
            content: ''; position: absolute; top: var(--y, -100px); left: var(--x, -100px);
            width: 250px; height: 250px; background: radial-gradient(circle, rgba(255, 128, 2, 0.12) 0%, transparent 70%);
            transform: translate(-50%, -50%); opacity: 0; transition: opacity 0.3s ease; pointer-events: none; z-index: 1;
        }
        .kpi-card:hover { transform: translateY(-5px); border-color: #ff8002; box-shadow: 0 15px 30px rgba(255, 128, 2, 0.08); z-index: 11; }
        .kpi-card:hover::before { opacity: 1; }
        .kpi-card > * { position: relative; z-index: 2; }

        .dash-card-1 { animation-delay: 0.15s; } .dash-card-2 { animation-delay: 0.2s; } .dash-card-3 { animation-delay: 0.25s; } .dash-card-4 { animation-delay: 0.3s; }
        
        .kpi-icon-row { display: flex; justify-content: space-between; margin-bottom: 15px; align-items: center; }
        .kpi-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: transform 0.3s; }
        .kpi-card:hover .kpi-icon { transform: scale(1.1) rotate(5deg); }
        .kpi-trend { font-size: 12px; font-weight: 700; padding: 4px 8px; border-radius: 6px; }
        .trend-up { background: #dcfce7; color: #16a34a; }
        .trend-down { background: #fee2e2; color: #ef4444; }
        
        .kpi-card h3 { margin: 0 0 8px 0; font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .kpi-value { margin: 0; font-size: 32px; font-weight: 900; color: #0f172a; letter-spacing: -1px; }

        /* 全息柱状图 */
        .mid-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; }
        .admin-card { background: #fff; padding: 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226, 232, 240, 0.6); animation: elasticUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        .admin-card:nth-child(1) { animation-delay: 0.35s; } .admin-card:nth-child(2) { animation-delay: 0.4s; }
        
        .chart-mockup { height: 220px; display: flex; align-items: flex-end; justify-content: space-between; padding-top: 20px; border-bottom: 1px dashed #e2e8f0; }
        .bar { position: relative; width: 10%; border-radius: 6px 6px 0 0; transform-origin: bottom; background: #f1f5f9; animation: barGrowPremium 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; overflow: hidden; }
        .bar.active { background: linear-gradient(to top, #ff8002, #fcd34d); box-shadow: 0 -5px 15px rgba(255, 128, 2, 0.3); }
        .bar.active::after { content: ''; position: absolute; left: 0; width: 100%; height: 50%; background: linear-gradient(to top, transparent, rgba(255,255,255,0.6), transparent); animation: holographicSweep 3s infinite linear; }
        .bar:nth-child(1) { animation-delay: 0.4s; } .bar:nth-child(2) { animation-delay: 0.45s; } .bar:nth-child(3) { animation-delay: 0.5s; } .bar:nth-child(4) { animation-delay: 0.55s; } .bar:nth-child(5) { animation-delay: 0.6s; } .bar:nth-child(6) { animation-delay: 0.65s; } .bar:nth-child(7) { animation-delay: 0.7s; }

        .recent-table-card { background: #fff; padding: 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226, 232, 240, 0.6); animation: elasticUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) 0.5s both; }
        .admin-table tbody tr { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); border-radius: 8px; }
        .admin-table tbody tr:hover { background: #fff; transform: scale(1.01) translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.06); z-index: 10; position: relative; }
        
        .search-form { position: relative; width: 400px; }
        .search-bar { background: #f1f5f9; border: none; padding: 12px 20px 12px 40px; border-radius: 10px; width: 100%; color: #1e293b; outline: none; transition: 0.3s; }
        .search-icon { position: absolute; left: 15px; top: 12px; color: #94a3b8; }
        .search-bar:focus { background: #fff; box-shadow: 0 0 0 3px rgba(255,128,2,0.15); border: 1px solid #ff8002; }
        
        .top-actions { display: flex; gap: 15px; align-items: center; }
        .btn-primary { background: linear-gradient(135deg, #ff8002, #ea580c); color: #fff; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; transition: 0.3s; box-shadow: 0 4px 10px rgba(255, 128, 2, 0.2); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(255, 128, 2, 0.4); }

        .bell-btn { width: 40px; height: 40px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: 0.3s; position: relative; z-index: 1001;}
        .bell-btn:hover { border-color: #ff8002; color: #ff8002; }
        .notif-badge { position: absolute; top: -4px; right: -4px; background: #ef4444; color: #fff; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 10px; animation: pulseShadow 2s infinite; z-index: 1002;}
        
        .notif-dropdown { 
            position: absolute; top: 55px; right: -10px; width: 340px; 
            background: #fff; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); 
            border: 1px solid rgba(226, 232, 240, 0.8); display: none; z-index: 1000; 
            transform-origin: top right;
        }
        .notif-dropdown::before {
            content: ''; position: absolute; top: -6px; right: 24px; 
            width: 12px; height: 12px; background: #f8fafc; 
            border-left: 1px solid rgba(226, 232, 240, 0.8); 
            border-top: 1px solid rgba(226, 232, 240, 0.8); 
            transform: rotate(45deg);
        }
        .notif-dropdown.active { display: block; animation: elasticUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        
        .custom-scroll { max-height: 320px; overflow-y: auto; padding-bottom: 10px; }
        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .badge { padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 800; letter-spacing: 0.5px; display: inline-block; text-align: center; width: 75px; text-transform: uppercase;}
        .bg-shipped { background: #dcfce7; color: #16a34a; }
        .bg-processing { background: #fef08a; color: #ca8a04; }
        .bg-paid { background: #fff7ed; color: #ea580c; }
        .bg-delivered { background: #f1f5f9; color: #475569; }
        
        .bs-img { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="admin-main dashboard-main">
            <div class="dash-header-top">
                <form action="products-list.php" method="GET" class="search-form">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="search" class="search-bar" placeholder="Search orders, products...">
                </form>

                <div class="top-actions">
                    <a href="admin-add-product.php" class="btn-primary">➕ Add New Product</a>
                    <div class="notif-wrapper" style="position: relative;">
                        <div class="bell-btn" id="bell-trigger">🔔</div>
                        <?php if($notif_count > 0): ?>
                            <span class="notif-badge"><?php echo $notif_count; ?></span>
                        <?php endif; ?>
                        
                        <div class="notif-dropdown" id="notif-panel">
                            <div style="padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 800; color: #0f172a; border-radius: 16px 16px 0 0;">
                                System Alerts <span style="background: #ef4444; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 5px;"><?php echo $notif_count; ?></span>
                            </div>
                            
                            <div class="custom-scroll">
                                <?php if($notif_count > 0): ?>
                                    <?php foreach($notifications as $n): ?>
                                        <a href="products-list.php?search=<?php echo urlencode($n['name']); ?>" style="display: block; padding: 15px 20px; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                            <?php if($n['stock'] <= 0): ?>
                                                <div style="color: #ef4444; font-size: 13px; font-weight: 800; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                                    <span style="width:6px; height:6px; background:#ef4444; border-radius:50%; display:inline-block; box-shadow: 0 0 5px #ef4444;"></span> Out of Stock
                                                </div>
                                                <div style="color: #475569; font-size: 13px; line-height: 1.4; word-wrap: break-word;">
                                                    <?php echo htmlspecialchars($n['name']); ?> 
                                                    <span style="background:#fee2e2; color:#ef4444; padding:2px 6px; border-radius:6px; font-size:10px; font-weight:800; margin-left:4px;">0 Left</span>
                                                </div>
                                            <?php else: ?>
                                                <div style="color: #f59e0b; font-size: 13px; font-weight: 800; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                                    <span style="width:6px; height:6px; background:#f59e0b; border-radius:50%; display:inline-block;"></span> Low Stock
                                                </div>
                                                <div style="color: #475569; font-size: 13px; line-height: 1.4; word-wrap: break-word;">
                                                    <?php echo htmlspecialchars($n['name']); ?> 
                                                    <span style="background:#fef3c7; color:#d97706; padding:2px 6px; border-radius:6px; font-size:10px; font-weight:800; margin-left:4px;"><?php echo $n['stock']; ?> Left</span>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div style="padding: 40px 20px; text-align: center; color: #94a3b8; font-size: 13px;">
                                        <div style="font-size: 30px; margin-bottom: 10px; opacity: 0.5;">✅</div>
                                        All quiet! No new alerts.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dash-greeting">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?php echo explode(' ', trim($admin_name))[0]; ?>. Here's the performance breakdown for today.</p>
            </div>

            <div class="kpi-grid" id="kpiContainer">
                <div class="kpi-card dash-card-1">
                    <div class="kpi-icon-row">
                        <div class="kpi-icon" style="background: #fff7ed; color: #ff8002;">💵</div>
                        <div class="kpi-trend trend-up">↗ 12.5%</div>
                    </div>
                    <h3>Total Sales</h3>
                    <p class="kpi-value" data-prefix="$" data-target="<?php echo $total_sales; ?>">0.00</p>
                </div>
                <div class="kpi-card dash-card-2">
                    <div class="kpi-icon-row">
                        <div class="kpi-icon" style="background: #f0fdf4; color: #22c55e;">🛍️</div>
                        <div class="kpi-trend trend-up">↗ 8.4%</div>
                    </div>
                    <h3>Total Orders</h3>
                    <p class="kpi-value" data-target="<?php echo $total_orders; ?>">0</p>
                </div>
                <div class="kpi-card dash-card-3">
                    <div class="kpi-icon-row">
                        <div class="kpi-icon" style="background: #eff6ff; color: #3b82f6;">👥</div>
                        <div class="kpi-trend trend-up">↗ 5.7%</div>
                    </div>
                    <h3>Active Customers</h3>
                    <p class="kpi-value" data-target="<?php echo $total_customers; ?>">0</p>
                </div>
                <div class="kpi-card dash-card-4">
                    <div class="kpi-icon-row">
                        <div class="kpi-icon" style="background: #fdf4ff; color: #c026d3;">📈</div>
                        <div class="kpi-trend trend-up">↗ 0.8%</div>
                    </div>
                    <h3>Conversion Rate</h3>
                    <p class="kpi-value" data-suffix="%" data-target="3.42">0</p>
                </div>
            </div>

            <div class="mid-grid">
                <div class="admin-card">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <h3 style="margin:0; font-size: 16px;">Revenue Trend</h3>
                        <span style="font-size: 12px; font-weight: 700; color: #94a3b8; background: #f8fafc; padding: 4px 8px; border-radius: 6px;">Last 7 Days</span>
                    </div>
                    <div class="chart-mockup">
                        <div class="bar" style="height: 30%;"></div>
                        <div class="bar" style="height: 50%;"></div>
                        <div class="bar active" style="height: 90%;"></div>
                        <div class="bar" style="height: 40%;"></div>
                        <div class="bar" style="height: 65%;"></div>
                        <div class="bar" style="height: 80%;"></div>
                        <div class="bar" style="height: 45%;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 15px; font-size: 11px; color: #94a3b8; font-weight: 700;">
                        <span>MON</span><span>TUE</span><span>WED</span><span>THU</span><span>FRI</span><span>SAT</span><span>SUN</span>
                    </div>
                </div>

                <div class="admin-card">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 25px;">
                        <h3 style="margin:0; font-size: 16px;">Top Sellers</h3>
                        <a href="products-list.php" style="font-size: 12px; color: #ff8002; font-weight: 700; text-decoration: none;">View All</a>
                    </div>
                    <div class="best-seller-list">
                        <?php if($best_sellers_res && mysqli_num_rows($best_sellers_res) > 0): ?>
                            <?php while($item = mysqli_fetch_assoc($best_sellers_res)): ?>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding: 10px; border-radius: 10px; transition: 0.2s; cursor: pointer;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" class="bs-img" onerror="this.src='https://placehold.co/48x48/f1f5f9/94a3b8?text=Img'">
                                        <div>
                                            <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #0f172a; max-width: 130px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 600;"><?php echo $item['sales_count']; ?> Units Sold</p>
                                        </div>
                                    </div>
                                    <span style="font-weight: 800; font-size: 14px; color: #0f172a;">$<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="recent-table-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="margin:0; font-size: 18px;">Recent Live Orders</h3>
                    <a href="orders-mgt.php" class="btn-primary" style="background: #f8fafc; color: #0f172a; box-shadow: none; border: 1px solid #e2e8f0; font-size: 12px;">View All Orders</a>
                </div>
                <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="color: #94a3b8; font-size: 11px; text-align: left; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">ORDER ID</th>
                            <th style="color: #94a3b8; font-size: 11px; text-align: left; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">CUSTOMER</th>
                            <th style="color: #94a3b8; font-size: 11px; text-align: left; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">PRODUCT</th>
                            <th style="color: #94a3b8; font-size: 11px; text-align: left; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">DATE</th>
                            <th style="color: #94a3b8; font-size: 11px; text-align: left; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">STATUS</th>
                            <th style="color: #94a3b8; font-size: 11px; text-align: right; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($recent_orders_res && mysqli_num_rows($recent_orders_res) > 0): ?>
                            <?php while($order = mysqli_fetch_assoc($recent_orders_res)): 
                                $status_class = 'bg-paid';
                                if($order['status'] == 'Shipped') $status_class = 'bg-shipped';
                                if($order['status'] == 'Processing') $status_class = 'bg-processing';
                                if($order['status'] == 'Delivered') $status_class = 'bg-delivered';
                            ?>
                            <tr>
                                <td style="padding: 18px 10px;"><strong style="color: #ff8002;">#ORD-<?php echo $order['id']; ?></strong></td>
                                <td style="padding: 18px 10px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width:32px; height:32px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:900; color:#475569;">
                                            <?php echo strtoupper(substr($order['full_name'], 0, 2)); ?>
                                        </div>
                                        <span style="font-weight: 600; color: #0f172a; font-size: 13px;"><?php echo htmlspecialchars($order['full_name']); ?></span>
                                    </div>
                                </td>
                                <td style="color: #64748b; font-size: 13px; padding: 18px 10px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($order['product_name']); ?>
                                </td>
                                <td style="color: #64748b; font-size: 13px; font-weight: 500; padding: 18px 10px;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td style="padding: 18px 10px;"><span class="badge <?php echo $status_class; ?>"><?php echo strtoupper($order['status']); ?></span></td>
                                <td style="text-align: right; padding: 18px 10px;"><strong style="color: #0f172a; font-size: 14px;">MYR <?php echo number_format($order['total_price'], 2); ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 50px;">
                                <div style="font-size: 40px; margin-bottom: 10px;">🛒</div>
                                No recent orders found.
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        const bell = document.getElementById('bell-trigger');
        const panel = document.getElementById('notif-panel');
        bell.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.classList.toggle('active');
        });
        document.addEventListener('click', (e) => {
            if (!panel.contains(e.target) && e.target !== bell) {
                panel.classList.remove('active');
            }
        });

        const counters = document.querySelectorAll('.kpi-value');
        const speed = 100; 
        
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const prefix = counter.getAttribute('data-prefix') || '';
            const suffix = counter.getAttribute('data-suffix') || '';
            const isFloat = target % 1 !== 0; 
            
            const updateCount = () => {
                const count = +counter.innerText.replace(/[^0-9.-]+/g,"");
                const inc = target / speed;

                if (count < target) {
                    let nextVal = count + inc;
                    if(isFloat) {
                        counter.innerText = prefix + (nextVal).toFixed(2) + suffix;
                    } else {
                        counter.innerText = prefix + Math.ceil(nextVal) + suffix;
                    }
                    setTimeout(updateCount, 15);
                } else {
                    if(isFloat) {
                        counter.innerText = prefix + target.toLocaleString('en-US', {minimumFractionDigits: 2}) + suffix;
                    } else {
                        counter.innerText = prefix + target.toLocaleString('en-US') + suffix;
                    }
                }
            };
            setTimeout(updateCount, 300);
        });

        document.querySelectorAll('.kpi-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--x', `${x}px`);
                card.style.setProperty('--y', `${y}px`);
            });
        });
    </script>
</body>
</html>
