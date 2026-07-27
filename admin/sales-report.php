<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

$stats_query = mysqli_query($conn, "SELECT
    SUM(total_price) as total_revenue,
    COUNT(id) as total_orders,
    AVG(total_price) as avg_value
    FROM orders WHERE status != 'Cancelled' AND status != 'Refunded'");
$stats = mysqli_fetch_assoc($stats_query);

$total_revenue = $stats['total_revenue'] ?? 0.00;
$total_orders = $stats['total_orders'] ?? 0;
$avg_value = $stats['avg_value'] ?? 0.00;

$today = date('Y-m-d');
$today_sales_query = mysqli_query($conn, "SELECT SUM(total_price) as daily FROM orders WHERE DATE(created_at) = '$today' AND status != 'Cancelled' AND status != 'Refunded'");
$today_sales = mysqli_fetch_assoc($today_sales_query)['daily'] ?? 0.00;

$chart_data = [];
$max_sales = 1;
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_data[$date] = 0;
}

$chart_sql = "SELECT DATE(created_at) as d, SUM(total_price) as total
              FROM orders
              WHERE created_at >= DATE(NOW() - INTERVAL 7 DAY)
              AND status != 'Cancelled' AND status != 'Refunded'
              GROUP BY DATE(created_at)";
$chart_res = mysqli_query($conn, $chart_sql);

if ($chart_res) {
    while($row = mysqli_fetch_assoc($chart_res)) {
        $date_key = $row['d'];
        if(isset($chart_data[$date_key])) {
            $chart_data[$date_key] = (float)$row['total'];
        }
    }
}
$max_val_in_db = max($chart_data);
if ($max_val_in_db > 0) $max_sales = $max_val_in_db;

$sales_detail_sql = "
    SELECT o.*, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC";
$sales_detail_res = mysqli_query($conn, $sales_detail_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Sales Report - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700;800&family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>

        @keyframes elasticUp {
            0% { opacity: 0; transform: translateY(30px) scale(0.97); }
            60% { opacity: 1; transform: translateY(-3px) scale(1.01); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes slideInRow {
            0% { opacity: 0; transform: translateX(-15px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        @keyframes barGrowPremium {
            0% { transform: scaleY(0); opacity: 0; }
            100% { transform: scaleY(1); opacity: 1; }
        }

        @keyframes holoSweep {
            0% { transform: translateY(100%); opacity: 0; }
            50% { opacity: 0.5; }
            100% { transform: translateY(-100%); opacity: 0; }
        }
        @keyframes pulseGreen {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        body { background: #f8fafc; font-family: 'Inter', sans-serif; overflow-x: hidden; }

        .admin-main { margin-left: 220px !important; padding: 40px 45px 80px 45px !important; box-sizing: border-box; }

        .dash-header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; }

        .dash-greeting { margin-bottom: 30px; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both; }
        .dash-greeting h1 { margin: 0 0 5px 0; font-size: 32px; color: #0f172a; font-weight: 900; letter-spacing: -1.5px; }
        .dash-greeting p { margin: 0; color: #64748b; font-size: 14px; font-weight: 500; }

        .btn-print-report {
            background: #0f172a; color: #fff; border: none; padding: 12px 24px;
            border-radius: 12px; font-weight: 800; font-size: 13px; cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2);
        }
        .btn-print-report:hover { background: #1e293b; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(15, 23, 42, 0.3); }

        .bento-grid {
            display: grid; grid-template-columns: 1fr 1fr 1fr; grid-template-rows: auto auto; gap: 25px; margin-bottom: 35px;
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
        }

        .bento-card {
            background: #fff; padding: 25px 30px; border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .bento-card::before {
            content: ''; position: absolute; top: var(--y, -100px); left: var(--x, -100px);
            width: 300px; height: 300px; background: radial-gradient(circle, rgba(255, 128, 2, 0.08) 0%, transparent 70%);
            transform: translate(-50%, -50%); opacity: 0; transition: opacity 0.3s ease; pointer-events: none; z-index: 1;
        }
        .bento-card:hover { transform: translateY(-5px); border-color: #cbd5e1; box-shadow: 0 20px 40px rgba(0,0,0,0.06); }
        .bento-card:hover::before { opacity: 1; }
        .bento-card > * { position: relative; z-index: 2; }

        .chart-card { grid-column: span 2; grid-row: span 2; display: flex; flex-direction: column; }

        .kpi-icon-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .kpi-icon { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; transition: transform 0.4s; box-shadow: inset 0 2px 4px rgba(255,255,255,0.8); }
        .bento-card:hover .kpi-icon { transform: scale(1.15) rotate(5deg); }

        .bento-card h3 { margin: 0 0 8px 0; font-size: 12px; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .kpi-value { margin: 0; font-size: 40px; font-weight: 900; color: #0f172a; letter-spacing: -2px; font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; line-height: 1; }

        .chart-container {
            flex-grow: 1; display: flex; align-items: flex-end; justify-content: space-between;
            padding-top: 40px; border-bottom: 1px solid #e2e8f0; position: relative;
            min-height: 240px;
        }
        .chart-bg-lines { position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; flex-direction: column; justify-content: space-between; pointer-events: none; opacity: 0.5; z-index: 0; }
        .chart-line { width: 100%; height: 1px; border-top: 1px dashed #cbd5e1; }

        .bar-group {
            display: flex; flex-direction: column; justify-content: flex-end; align-items: center;
            gap: 12px; z-index: 2; width: 10%; position: relative;
            height: 100%;
        }
        .bar {
            width: 100%;
            background: linear-gradient(to top, #e2e8f0, #f1f5f9);
            border-radius: 8px 8px 0 0;
            position: relative; cursor: pointer; transition: 0.3s;
            height: var(--target-height);
            transform-origin: bottom;
            transform: scaleY(0);
            animation: barGrowPremium 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        .bar.active {
            background: linear-gradient(to top, #ff8002, #facc15);
            box-shadow: 0 -5px 15px rgba(255, 128, 2, 0.25);
        }
        .bar.active::after {
            content: ''; position: absolute; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to top, transparent, rgba(255,255,255,0.7), transparent);
            animation: holoSweep 3s infinite linear;
        }
        .bar:hover { filter: brightness(1.05); box-shadow: 0 -8px 20px rgba(255, 128, 2, 0.35); }

        .bar-tooltip {
            position: absolute; top: -45px; background: #0f172a; color: #fff; font-size: 12px; font-weight: 800; font-family: 'JetBrains Mono';
            padding: 8px 12px; border-radius: 8px; white-space: nowrap; opacity: 0; transform: translateY(10px);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); pointer-events: none; z-index: 10;
        }
        .bar-tooltip::after { content: ''; position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%); border-width: 5px 5px 0; border-style: solid; border-color: #0f172a transparent transparent transparent; }
        .bar:hover .bar-tooltip { opacity: 1; transform: translateY(0); }

        .day-label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;}
        .bar-group:hover .day-label { color: #0f172a; }

        .recent-table-card {
            background: #fff; padding: 35px 40px; border-radius: 28px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.02); border: 1px solid rgba(226, 232, 240, 0.8);
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s both;
        }
        .table-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .table-header h3 { margin: 0 0 5px 0; font-size: 20px; color: #0f172a; font-weight: 900; letter-spacing: -0.5px;}

        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: -12px; table-layout: fixed; }

        .admin-table th:nth-child(1) { width: 20%; }
        .admin-table th:nth-child(2) { width: 25%; }
        .admin-table th:nth-child(3) { width: 25%; }
        .admin-table th:nth-child(4) { width: 15%; }
        .admin-table th:nth-child(5) { width: 15%; text-align: right; }

        .admin-table th { text-align: left; padding: 10px 25px; font-size: 11px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border: none; }

        .admin-table td {
            background: #fff; padding: 22px 25px;
            border-top: 1px solid rgba(226, 232, 240, 0.6);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            vertical-align: middle; transition: all 0.3s ease;
        }
        .admin-table td:first-child { border-left: 1px solid rgba(226, 232, 240, 0.6); border-radius: 12px 0 0 12px; }
        .admin-table td:last-child { border-right: 1px solid rgba(226, 232, 240, 0.6); border-radius: 0 12px 12px 0; }

        .sales-row { opacity: 0; animation: slideInRow 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards; }

        .admin-table:hover .sales-row:not(:hover) td { opacity: 0.4; filter: grayscale(50%) blur(1px); background: transparent; border-color: transparent; }

        .sales-row:hover td {
            background: #fffbfa; border-top-color: #fed7aa; border-bottom-color: #fed7aa;
        }
        .sales-row:hover td:first-child { border-left-color: #ff8002; box-shadow: -4px 0 15px rgba(255, 128, 2, 0.05); }
        .sales-row:hover td:last-child { border-right-color: #fed7aa; box-shadow: 4px 0 15px rgba(255, 128, 2, 0.05); }

        .ord-id { background: #f8fafc; color: #0f172a; padding: 6px 12px; border-radius: 8px; font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 800; letter-spacing: 0.5px; border: 1px solid #e2e8f0; transition: 0.3s; display: inline-block; }
        .sales-row:hover .ord-id { background: #fff7ed; color: #ea580c; border-color: #ff8002; transform: scale(1.05); }

        .badge { padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 800; display: inline-block; text-align: center; width: 85px; letter-spacing: 0.5px; text-transform: uppercase;}
        .bg-paid { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
        .bg-shipped { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .bg-processing { background: #fefce8; color: #a16207; border: 1px solid #fde047; }
        .bg-refunded { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; text-decoration: line-through; }

        @media print {
            body { background: #fff; }
            .sidebar, .dash-header-top, .bento-grid, .btn-print-report, .table-header p { display: none !important; }
            .admin-main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .recent-table-card { box-shadow: none; border: none; padding: 0; }
            .admin-table th { color: #000; border-bottom: 2px solid #000; }
            .admin-table td { border-bottom: 1px solid #ddd; background: #fff !important; }
            .admin-table:hover .sales-row:not(:hover) td { opacity: 1; filter: none; background: #fff; }
            .sales-row:hover td { border-color: #ddd; box-shadow: none; }
            .ord-id { border: none; background: transparent; padding: 0; }
            .admin-main::before {
                content: 'FAIFA Verified Sales Ledger - Printed on <?php echo date("M d, Y"); ?>';
                display: block; font-size: 20px; font-family: monospace; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;
            }
        }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">

        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="dash-header-top">
                <div class="dash-greeting" style="margin:0; animation:none;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 800; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase;">DASHBOARD > ANALYTICS</div>
                    <h1>Sales Intelligence</h1>
                    <p>Real-time financial performance and transaction ledger.</p>
                </div>

                <div class="top-actions">
                    <button onclick="window.print()" class="btn-print-report">
                        <span style="font-size: 16px;">🖨️</span> Export PDF Report
                    </button>
                </div>
            </div>

            <div class="bento-grid">

                <div class="bento-card chart-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h3 style="color: #0f172a; font-size: 14px;">7-Day Revenue Trend</h3>
                            <div style="display: flex; align-items: baseline; gap: 10px;">
                                <p class="kpi-value" data-prefix="MYR " data-target="<?php echo $total_revenue; ?>">0.00</p>
                                <span style="background: #ecfdf5; color: #10b981; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #a7f3d0;">
                                    <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; display: inline-block; animation: pulseGreen 2s infinite;"></span> Live
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #fff7ed, #ffedd5); color: #ff8002;">📈</div>
                    </div>

                    <div class="chart-container">
                        <div class="chart-bg-lines">
                            <div class="chart-line"></div>
                            <div class="chart-line"></div>
                            <div class="chart-line"></div>
                            <div class="chart-line"></div>
                        </div>
                        <?php
                        $idx = 0;
                        foreach($chart_data as $date => $amount):
                            $height_percent = ($amount / $max_sales) * 100;
                            if($height_percent < 3) $height_percent = 3;

                            $is_today = ($date == $today);
                            $active_class = $is_today ? 'active' : '';
                        ?>
                        <div class="bar-group">
                            <div class="bar <?php echo $active_class; ?>" style="--target-height: <?php echo $height_percent; ?>%; animation-delay: <?php echo 0.3 + ($idx * 0.05); ?>s;">
                                <div class="bar-tooltip">MYR <?php echo number_format($amount, 2); ?></div>
                            </div>
                            <div class="day-label" style="<?php echo $is_today ? 'color: #ff8002;' : ''; ?>">
                                <?php echo date('D', strtotime($date)); ?>
                            </div>
                        </div>
                        <?php $idx++; endforeach; ?>
                    </div>
                </div>

                <div class="bento-card">
                    <div class="kpi-icon-row">
                        <div class="kpi-icon" style="background: #f1f5f9; color: #475569;">🛍️</div>
                    </div>
                    <h3>Net Orders Generated</h3>
                    <p class="kpi-value" data-target="<?php echo $total_orders; ?>">0</p>
                </div>

                <div class="bento-card">
                    <div class="kpi-icon-row">
                        <div class="kpi-icon" style="background: #f1f5f9; color: #475569;">⭐</div>
                    </div>
                    <h3>Avg. Order Value</h3>
                    <p class="kpi-value" data-prefix="MYR " data-target="<?php echo $avg_value; ?>">0.00</p>
                </div>

            </div>

            <div class="recent-table-card">
                <div class="table-header">
                    <div>
                        <h3>Verified Transaction Ledger</h3>
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #64748b; font-weight: 500;">Immutable record of all processed customer payments.</p>
                    </div>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>REFERENCE ID</th>
                            <th>CUSTOMER NAME</th>
                            <th>TIMESTAMP</th>
                            <th>PAYMENT STATUS</th>
                            <th style="text-align: right; padding-right: 25px;">NET AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $row_idx = 1;
                        while($row = mysqli_fetch_assoc($sales_detail_res)):
                            $status_class = 'bg-paid';
                            if($row['status'] == 'Processing' || $row['status'] == 'Packaging') $status_class = 'bg-processing';
                            if($row['status'] == 'Shipped' || $row['status'] == 'Delivered') $status_class = 'bg-shipped';
                            if($row['status'] == 'Refunded') $status_class = 'bg-refunded';

                            $is_refunded = ($row['status'] == 'Refunded');
                        ?>
                        <tr class="sales-row" style="animation-delay: <?php echo 0.1 + ($row_idx * 0.05); ?>s;">
                            <td><span class="ord-id">#FA-<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></span></td>
                            <td>
                                <div style="font-weight: 800; color: #0f172a; font-size: 14.5px;"><?php echo htmlspecialchars($row['full_name']); ?></div>
                            </td>
                            <td style="color: #64748b; font-size: 13px; font-weight: 600;">
                                <?php echo date('Y/m/d', strtotime($row['created_at'])); ?>
                                <span style="color: #cbd5e1; margin: 0 6px;">•</span>
                                <span style="font-family: 'JetBrains Mono';"><?php echo date('H:i', strtotime($row['created_at'])); ?></span>
                            </td>
                            <td><span class="badge <?php echo $status_class; ?>"><?php echo strtoupper($row['status']); ?></span></td>
                            <td style="text-align: right; padding-right: 25px;">
                                <strong style="color: <?php echo $is_refunded ? '#ef4444' : '#10b981'; ?>; font-size: 17px; font-weight: 900; font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums;">
                                    <?php if($is_refunded) echo "-"; else echo "+"; ?>MYR <?php echo number_format($row['total_price'], 2); ?>
                                </strong>
                            </td>
                        </tr>
                        <?php $row_idx++; endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const counters = document.querySelectorAll('.kpi-value');
            counters.forEach(counter => {
                const targetAttr = counter.getAttribute('data-target');
                if(!targetAttr) return;

                const target = parseFloat(targetAttr);
                const prefix = counter.getAttribute('data-prefix') || '';
                const suffix = counter.getAttribute('data-suffix') || '';
                const isFloat = target % 1 !== 0;

                let current = 0;
                const duration = 1500;
                const startTime = performance.now();

                const updateCount = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeProgress = 1 - Math.pow(1 - progress, 4);
                    const currentVal = target * easeProgress;

                    if (progress < 1) {
                        counter.innerText = prefix + (isFloat ? currentVal.toFixed(2) : Math.floor(currentVal)) + suffix;
                        requestAnimationFrame(updateCount);
                    } else {
                        counter.innerText = prefix + target.toLocaleString('en-US', {minimumFractionDigits: isFloat ? 2 : 0}) + suffix;
                    }
                };
                requestAnimationFrame(updateCount);
            });

            document.querySelectorAll('.bento-card').forEach(el => {
                el.addEventListener('mousemove', e => {
                    requestAnimationFrame(() => {
                        const rect = el.getBoundingClientRect();
                        el.style.setProperty('--x', `${e.clientX - rect.left}px`);
                        el.style.setProperty('--y', `${e.clientY - rect.top}px`);
                    });
                });
            });
        });
    </script>
</body>
</html>
