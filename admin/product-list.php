<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id = $del_id");
    header("Location: products-list.php?msg=deleted");
    exit();
}

$cat_query = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
$existing_categories = [];
while($row = mysqli_fetch_assoc($cat_query)) {
    $existing_categories[] = $row['category'];
}

$where_conditions = [];
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$filter_category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

if ($search_keyword !== '') {
    $where_conditions[] = "(name LIKE '%$search_keyword%' OR tags LIKE '%$search_keyword%' OR product_code LIKE '%$search_keyword%')";
}
if ($filter_category !== '') {
    $where_conditions[] = "category = '$filter_category'";
}
if ($filter_status !== '') {
    if ($filter_status === 'active') {
        $where_conditions[] = "status = 'active' AND stock > 0";
    } elseif ($filter_status === 'out_of_stock') {
        $where_conditions[] = "stock <= 0";
    } elseif ($filter_status === 'draft') {
        $where_conditions[] = "status = 'draft'";
    }
}

$where_clause = "";
if (count($where_conditions) > 0) {
    $where_clause = " WHERE " . implode(' AND ', $where_conditions);
}

if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Inventory_Export_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Product Code', 'Product ID', 'Product Name', 'Category', 'Price (MYR)', 'Stock Quantity', 'Status'));

    $export_res = mysqli_query($conn, "SELECT * FROM products $where_clause ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($export_res)) {
        $csv_status = 'Active';
        if ($row['status'] == 'draft') $csv_status = 'Draft';
        elseif ($row['stock'] <= 0) $csv_status = 'Out of Stock';
        elseif ($row['stock'] < 5) $csv_status = 'Low Stock';

        $product_code = !empty($row['product_code']) ? $row['product_code'] : 'N/A';
        fputcsv($output, array($product_code, $row['id'], $row['name'], $row['category'], $row['price'], $row['stock'], $csv_status));
    }
    fclose($output);
    exit();
}

$sql = "SELECT * FROM products $where_clause ORDER BY id DESC";
$products_res = mysqli_query($conn, $sql);
$total_products = mysqli_num_rows($products_res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Product Inventory - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
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
        @keyframes tooltipPop {
            0% { opacity: 0; transform: translate(-50%, 10px) scale(0.8); }
            50% { transform: translate(-50%, -15px) scale(1.1); }
            100% { opacity: 1; transform: translate(-50%, -10px) scale(1); }
        }

        body { background: #f8fafc; }

        .dash-header-top { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        .page-title h1 { margin: 0 0 5px 0; font-size: 28px; color: #0f172a; font-weight: 800; letter-spacing: -0.5px;}
        .page-title p { margin: 0; color: #64748b; font-size: 14px; font-weight: 500; }

        .btn-primary {
            position: relative; overflow: hidden; background: linear-gradient(135deg, #ff8002, #ea580c);
            color: #fff; padding: 12px 24px; border-radius: 12px; border: none; font-weight: 800; text-decoration: none;
            cursor: pointer; box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3); transition: all 0.3s ease;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(234, 88, 12, 0.4); }

        .alert-msg { padding: 16px 24px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 12px; animation: elasticUp 0.4s both; }
        .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1); }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.1); }

        .toolbar-card {
            background: #fff; padding: 20px 25px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            border: 1px solid rgba(226, 232, 240, 0.8); display: flex; justify-content: space-between;
            align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.15s both;
        }

        .search-box { position: relative; width: 350px; }
        .search-box input { width: 100%; padding: 14px 15px 14px 45px; border: 2px solid #f1f5f9; border-radius: 12px; background: #f8fafc; color: #1e293b; outline: none; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-sizing: border-box;}
        .search-box input:focus { border-color: #ff8002; background: #fff; box-shadow: 0 8px 20px rgba(255,128,2,0.08), 0 0 0 4px rgba(255,128,2,0.1); transform: translateY(-2px); }
        .search-box span { position: absolute; left: 16px; top: 15px; color: #94a3b8; font-size: 16px; transition: color 0.3s; }
        .search-box:focus-within span { color: #ff8002; }

        .filter-select { padding: 12px 18px; border: 2px solid #f1f5f9; border-radius: 10px; background: #f8fafc; color: #475569; font-weight: 700; font-size: 13px; outline: none; cursor: pointer; transition: all 0.3s ease; }
        .filter-select:hover, .filter-select:focus { border-color: #cbd5e1; background: #fff; transform: translateY(-1px); }

        .table-container { animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both; }

        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: -12px; table-layout: fixed; }

        .admin-table th:nth-child(1) { width: 35%; } /* PRODUCT INFO */
        .admin-table th:nth-child(2) { width: 15%; } /* CATEGORY */
        .admin-table th:nth-child(3) { width: 15%; } /* UNIT PRICE */
        .admin-table th:nth-child(4) { width: 15%; } /* INVENTORY HEALTH */
        .admin-table th:nth-child(5) { width: 12%; } /* VISIBILITY */
        .admin-table th:nth-child(6) { width: 8%; text-align: right; } /* MANAGE */

        .admin-table th { text-align: left; padding: 10px 25px; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; border: none; }

        .admin-table td {
            background: #fff; padding: 20px 25px;
            border-top: 1px solid rgba(226, 232, 240, 0.6);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            vertical-align: middle; transition: all 0.3s ease;
        }
        .admin-table td:first-child { border-left: 1px solid rgba(226, 232, 240, 0.6); border-radius: 12px 0 0 12px; }
        .admin-table td:last-child { border-right: 1px solid rgba(226, 232, 240, 0.6); border-radius: 0 12px 12px 0; }

        .prod-row { opacity: 0; animation: slideInRow 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        .prod-row:nth-child(1) { animation-delay: 0.1s; }
        .prod-row:nth-child(2) { animation-delay: 0.15s; }
        .prod-row:nth-child(3) { animation-delay: 0.2s; }
        .prod-row:nth-child(4) { animation-delay: 0.25s; }
        .prod-row:nth-child(n+5) { animation-delay: 0.3s; }

        .admin-table:hover .prod-row:not(:hover) td { opacity: 0.4; filter: grayscale(30%); background: #f8fafc; border-color: transparent; }

        .prod-row:hover td {
            background: #fffbfa;
            border-top-color: #fed7aa;
            border-bottom-color: #fed7aa;
        }
        .prod-row:hover td:first-child { border-left-color: #ff8002; box-shadow: -4px 0 15px rgba(255, 128, 2, 0.05); }
        .prod-row:hover td:last-child { border-right-color: #fed7aa; box-shadow: 4px 0 15px rgba(255, 128, 2, 0.05); }

        .prod-info { display: flex; align-items: center; gap: 15px; }

        .img-wrapper { position: relative; width: 55px; height: 55px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); flex-shrink: 0;}
        .prod-img { width: 100%; height: 100%; object-fit: cover; background: #f1f5f9; transition: transform 0.4s; }
        .img-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,128,2,0.7); backdrop-filter: blur(2px); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
        .prod-row:hover .img-wrapper { transform: scale(1.3) rotate(-3deg); box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 11; border-radius: 12px; }
        .prod-row:hover .prod-img { transform: scale(1.1); }
        .prod-row:hover .img-overlay { opacity: 1; }

        .prod-text-content { display: flex; flex-direction: column; overflow: hidden; }
        .prod-name { font-size: 15px; color: #0f172a; font-weight: 800; letter-spacing: -0.3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; transition: color 0.3s; }
        .prod-row:hover .prod-name { color: #ff8002; }

        .sku-wrapper { position: relative; display: inline-block; cursor: pointer; margin-top: 6px; }
        .prod-code-badge { font-size: 10px; color: #64748b; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-weight: 800; letter-spacing: 0.5px; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; font-family: monospace; }
        .sku-icon { font-size: 10px; opacity: 0; transform: translateX(-5px); transition: all 0.2s; }
        .sku-wrapper:hover .prod-code-badge { background: #fff7ed; color: #ea580c; border-color: #ff8002; }
        .sku-wrapper:hover .sku-icon { opacity: 1; transform: translateX(0); }
        .sku-wrapper:active .prod-code-badge { transform: scale(0.95); }

        .copy-tooltip { position: absolute; top: -30px; left: 50%; transform: translateX(-50%); background: #10b981; color: white; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 6px; white-space: nowrap; pointer-events: none; opacity: 0; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); z-index: 100; }
        .copy-tooltip::after { content:''; position:absolute; bottom:-4px; left:50%; transform:translateX(-50%); border-width:4px 4px 0; border-style:solid; border-color:#10b981 transparent transparent transparent; }
        .sku-wrapper.copied .prod-code-badge { background: #dcfce7; border-color: #10b981; color: #047857; }
        .sku-wrapper.copied .copy-tooltip { animation: tooltipPop 2s forwards cubic-bezier(0.34, 1.56, 0.64, 1); }

        .stock-wrapper { display: flex; flex-direction: column; gap: 6px; width: 100%; max-width: 120px; }
        .stock-number { font-size: 15px; font-weight: 900; color: #0f172a; display: flex; align-items: baseline; gap: 4px; }
        .stock-number small { font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase; }
        .stock-track { width: 100%; height: 6px; background: #f1f5f9; border-radius: 4px; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
        .stock-fill { height: 100%; border-radius: 4px; transition: width 1s cubic-bezier(0.34, 1.56, 0.64, 1); }

        .st-pill { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; text-transform: uppercase; border: 1px solid transparent;}
        .st-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; }
        .st-active { background: #ecfdf5; color: #047857; border-color: #a7f3d0; } .st-active::before { background: #10b981; }
        .st-low { background: #fefce8; color: #a16207; border-color: #fde047; } .st-low::before { background: #eab308; }
        .st-draft { background: #f8fafc; color: #475569; border-color: #e2e8f0; } .st-draft::before { background: #94a3b8; }
        .st-out { background: #fef2f2; color: #b91c1c; border-color: #fecaca; } .st-out::before { background: #ef4444; }

        .action-drawer { display: flex; justify-content: flex-end; gap: 10px; opacity: 0.2; filter: grayscale(100%); transform: translateX(10px); transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); pointer-events: none; }
        .prod-row:hover .action-drawer { opacity: 1; filter: grayscale(0%); transform: translateX(0); pointer-events: auto; }

        .action-icon { text-decoration: none; padding: 10px; font-size: 16px; border-radius: 10px; background: #f8fafc; border: 1px solid #f1f5f9; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); color: #94a3b8; }
        .action-icon.edit:hover { background: #fff7ed; border-color: #fed7aa; color: #ff8002; transform: scale(1.15) translateY(-2px); box-shadow: 0 4px 10px rgba(255, 128, 2, 0.15); }
        .action-icon.delete:hover { background: #fee2e2; border-color: #fca5a5; color: #ef4444; transform: scale(1.15) translateY(-2px) rotate(5deg); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.15); }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">

        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="dash-header-top">
                <div class="page-title">
                    <div style="font-size: 11px; color: #64748b; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 8px; text-transform: uppercase;">DASHBOARD > CATALOG</div>
                    <h1>Product Inventory</h1>
                    <p>Track stock levels, copy SKUs, and manage your store's entire catalog.</p>
                </div>
                <a href="admin-add-product.php" class="btn-primary">
                    <span style="font-size: 16px;">📦</span> Add New Product
                </a>
            </div>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="alert-msg alert-error">
                    <span style="font-size: 20px;">🗑️</span> Product has been permanently deleted from the database.
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="alert-msg alert-success">
                    <span style="font-size: 20px;">✨</span> Product details have been updated successfully.
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                <div class="alert-msg alert-success">
                    <span style="font-size: 20px;">✅</span> New product has been added to the catalog.
                </div>
            <?php endif; ?>

            <div class="toolbar-card">
                <form method="GET" action="products-list.php" id="filterForm" style="display: flex; gap: 15px; align-items: center; margin: 0; flex-wrap: wrap;">
                    <div class="search-box">
                        <span>🔍</span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Search by name or code...">
                    </div>

                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach($existing_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($filter_category == $cat) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?php if($filter_status == 'active') echo 'selected'; ?>>🟢 Active Live</option>
                        <option value="out_of_stock" <?php if($filter_status == 'out_of_stock') echo 'selected'; ?>>🔴 Out of Stock</option>
                        <option value="draft" <?php if($filter_status == 'draft') echo 'selected'; ?>>⚪ Drafts</option>
                    </select>

                    <button type="submit" style="background: #0f172a; color: #fff; border: none; padding: 14px 24px; border-radius: 10px; font-weight: 800; cursor: pointer; transition: 0.2s;">
                        Filter Catalog
                    </button>

                    <?php if(!empty($where_clause)): ?>
                        <a href="products-list.php" style="color: #ef4444; font-size: 13px; font-weight: 800; text-decoration: none; padding: 12px 18px; background: #fef2f2; border-radius: 10px; transition: 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fef2f2'">Clear</a>
                    <?php endif; ?>
                </form>

                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="color: #64748b; font-size: 13px; font-weight: 800; background: #f1f5f9; padding: 12px 18px; border-radius: 10px;">
                        Total Items: <strong style="color: #0f172a; font-size: 16px;"><?php echo $total_products; ?></strong>
                    </div>
                    <button type="button" onclick="exportFilteredCSV()" style="background: #fff; border: 2px solid #e2e8f0; padding: 12px 18px; border-radius: 10px; font-weight: 800; color: #1e293b; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.borderColor='#cbd5e1'; this.style.transform='translateY(-2px)'; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; box-shadow:none;">
                        <span>📥</span> Export CSV
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>PRODUCT INFO</th>
                            <th>CATEGORY</th>
                            <th>UNIT PRICE</th>
                            <th>INVENTORY HEALTH</th>
                            <th>VISIBILITY</th>
                            <th style="text-align: right; padding-right: 25px;">MANAGE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_products > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($products_res)):
                                $status_class = 'st-active'; $status_text = 'Active Live';
                                if ($row['status'] == 'draft') {
                                    $status_class = 'st-draft'; $status_text = 'Draft / Hidden';
                                } elseif ($row['stock'] <= 0) {
                                    $status_class = 'st-out'; $status_text = 'Out of Stock';
                                } elseif ($row['stock'] < 5) {
                                    $status_class = 'st-low'; $status_text = 'Low Stock';
                                }

                                $product_code = !empty($row['product_code']) ? htmlspecialchars($row['product_code']) : 'N/A';

                                $max_stock_benchmark = 50;
                                $stock_percent = min(100, max(0, ($row['stock'] / $max_stock_benchmark) * 100));
                                $stock_color = '#10b981';
                                if ($row['stock'] <= 0) $stock_color = '#ef4444';
                                elseif ($row['stock'] <= 10) $stock_color = '#f59e0b';
                            ?>
                            <tr class="prod-row">
                                <td>
                                    <div class="prod-info">
                                        <div class="img-wrapper">
                                            <img src="../assets/images/<?php echo htmlspecialchars($row['image']); ?>" class="prod-img" onerror="this.src='https://placehold.co/55x55/f1f5f9/94a3b8?text=Img'">
                                            <div class="img-overlay">👁️</div>
                                        </div>
                                        <div class="prod-text-content">
                                            <span class="prod-name" title="<?php echo htmlspecialchars($row['name']); ?>"><?php echo htmlspecialchars($row['name']); ?></span>
                                            <?php if($product_code !== 'N/A'): ?>
                                                <div class="sku-wrapper" onclick="copySKU(this, '<?php echo $product_code; ?>')">
                                                    <span class="prod-code-badge">
                                                        <?php echo $product_code; ?> <span class="sku-icon">📄</span>
                                                    </span>
                                                    <span class="copy-tooltip">✅ Copied</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="background: #f8fafc; padding: 6px 12px; border-radius: 8px; font-size: 12px; color: #475569; font-weight: 800; border: 1px solid #e2e8f0; display: inline-block;">
                                        <?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?>
                                    </span>
                                </td>
                                <td><strong style="color: #0f172a; font-size: 16px; font-weight: 900; font-family: 'JetBrains Mono', monospace;">MYR <?php echo number_format($row['price'], 2); ?></strong></td>

                                <td>
                                    <div class="stock-wrapper" title="Current physical stock: <?php echo $row['stock']; ?>">
                                        <div class="stock-number" style="color: <?php echo $stock_color; ?>;">
                                            <?php echo $row['stock']; ?> <small>Units</small>
                                        </div>
                                        <div class="stock-track">
                                            <div class="stock-fill" style="width: <?php echo $stock_percent; ?>%; background: <?php echo $stock_color; ?>;"></div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="st-pill <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>

                                <td style="text-align: right; padding-right: 25px;">
                                    <div class="action-drawer">
                                        <a href="edit-product.php?id=<?php echo $row['id']; ?>" class="action-icon edit" title="Edit Catalog Data">✏️</a>
                                        <a href="products-list.php?delete=<?php echo $row['id']; ?>" class="action-icon delete" title="Delete Product" onclick="return confirm('🚨 DANGER: Are you sure you want to permanently delete \'<?php echo addslashes($row['name']); ?>\'?\n\nThis will remove it from the database and cannot be undone.');">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="prod-row">
                                <td colspan="6" style="text-align: center; padding: 80px 20px; color: #64748b; background: #fff; border-radius: 16px; border: 2px dashed #e2e8f0;">
                                    <div style="font-size: 50px; margin-bottom: 20px; opacity: 0.5;">📭</div>
                                    <p style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a;">Catalog is Empty</p>
                                    <p style="margin: 8px 0 20px 0; font-size: 14px;">Try adjusting your search filters or add a new product.</p>
                                    <a href="products-list.php" style="color: #ef4444; font-size: 14px; font-weight: 800; text-decoration: none; padding: 12px 24px; background: #fef2f2; border-radius: 10px;">Clear Search & Filters</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function exportFilteredCSV() {
            let form = document.getElementById('filterForm');
            let params = new URLSearchParams(new FormData(form));
            window.location.href = "products-list.php?export=csv&" + params.toString();
        }

        function copySKU(element, code) {
            navigator.clipboard.writeText(code).then(() => {
                element.classList.add('copied');
                const icon = element.querySelector('.sku-icon');
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
