<?php
$current_page = basename($_SERVER['PHP_SELF']);

$sidebar_admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

$is_catalog = in_array($current_page, ['products-list.php', 'admin-add-product.php', 'edit-product.php', 'manage-categories.php', 'manage-reviews.php']);
$is_sales = in_array($current_page, ['orders-mgt.php', 'process-refund.php', 'customers.php', 'sales-report.php']);
$is_marketing = in_array($current_page, ['manage-coupons.php', 'create-coupons.php', 'update-banners.php', 'admin-rewards.php']);
$is_settings = in_array($current_page, ['shipping-rules.php', 'tax-rules.php']);
?>

<style>

    .admin-main, .admin-content {
        margin-left: 220px !important;
        padding: 40px 45px 60px 45px !important;
        box-sizing: border-box !important;
        min-height: 100vh;
        transition: margin-left 0.3s ease;
    }

    .admin-sidebar {
        position: fixed; top: 0; left: 0;
        width: 220px !important;
        min-width: 220px !important;
        display: flex; flex-direction: column; height: 100vh;
        background: rgba(255, 244, 232, 0.98) !important;
        border-right: 2px solid #ff8002;
        z-index: 100;
    }

    .sidebar-nav { flex-grow: 1; overflow-y: auto; padding: 0 12px; }

    .sidebar-nav::-webkit-scrollbar { width: 3px; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: #fed7aa; border-radius: 10px; }

    .sidebar-link {
        padding: 10px 12px;
        border-radius: 8px; color: #475569; text-decoration: none;
        font-size: 13px;
        font-weight: 700; transition: all 0.2s ease;
        display: flex; align-items: center; gap: 10px; margin-bottom: 2px;
    }
    .sidebar-link:hover { background: #fff; color: #ff8002; box-shadow: 0 2px 6px rgba(255,128,2,0.08); transform: translateX(2px); }
    .sidebar-link.active { background: linear-gradient(135deg, #ff8002, #ea580c); color: #fff; box-shadow: 0 4px 10px rgba(234, 88, 12, 0.3); }

    .nav-group { margin-bottom: 4px; }
    .nav-group summary {
        list-style: none; cursor: pointer;
        padding: 10px 12px; font-size: 10px;
        font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;
        display: flex; justify-content: space-between; align-items: center; border-radius: 8px;
        transition: all 0.2s; user-select: none;
    }
    .nav-group summary::-webkit-details-marker { display: none; }
    .nav-group summary:hover { color: #0f172a; background: rgba(0,0,0,0.03); }
    .nav-group summary::after { content: '▼'; font-size: 8px; transition: transform 0.3s; opacity: 0.5; }

    .nav-group[open] summary::after { transform: rotate(180deg); color: #ff8002; opacity: 1; }
    .nav-group[open] summary { color: #ff8002; margin-bottom: 2px; }

    .nav-group .sub-menu {
        display: flex; flex-direction: column; gap: 2px; padding-left: 10px;
        border-left: 2px solid rgba(255,128,2,0.15); margin-left: 16px; margin-bottom: 8px;
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

    .sub-menu .sidebar-link { padding: 8px 12px; font-size: 12px; font-weight: 600; margin-bottom: 1px; }
    .sub-menu .sidebar-link.active { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; box-shadow: none; }

    .admin-profile-card {
        padding: 15px; border-top: 1px dashed #fed7aa; background: #fff7ed;
        margin-top: auto; display: flex; align-items: center; justify-content: space-between;
    }

    .btn-admin-logout {
        background: transparent; border: none; padding: 8px; color: #94a3b8;
        cursor: pointer; transition: all 0.3s ease; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none;
    }
    .btn-admin-logout:hover {
        background: #fee2e2; color: #ef4444; transform: translateX(2px);
    }
    .btn-admin-logout svg { width: 16px; height: 16px; stroke-width: 2.5; }

    @media print {
        .admin-sidebar { display: none !important; }
    }
</style>

<aside class="admin-sidebar">
    <div class="brand" style="padding: 20px 15px; text-align: center; border-bottom: 1px dashed #fed7aa; margin-bottom: 15px;">
        <img src="../assets/images/main-logo.png" alt="FAIFA Logo" style="height: 30px; width: auto; margin-bottom: 8px;">
        <div style="font-weight: 900; color: #0f172a; font-size: 18px; letter-spacing: 1px;">FAIFA</div>
        <span style="font-size: 9px; color: #ff8002; display: block; font-weight: 800; text-transform: uppercase; margin-top: 2px;">Admin Panel</span>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <span>🏠</span> Dashboard
        </a>

        <details class="nav-group" <?php echo $is_catalog ? 'open' : ''; ?>>
            <summary>Catalog</summary>
            <div class="sub-menu">
                <a href="products-list.php" class="sidebar-link <?php echo ($current_page == 'products-list.php' || $current_page == 'admin-add-product.php' || $current_page == 'edit-product.php') ? 'active' : ''; ?>">📦 Products</a>
                <a href="manage-categories.php" class="sidebar-link <?php echo ($current_page == 'manage-categories.php') ? 'active' : ''; ?>">🗂️ Categories</a>
                <a href="manage-reviews.php" class="sidebar-link <?php echo ($current_page == 'manage-reviews.php') ? 'active' : ''; ?>">💬 Reviews</a>
            </div>
        </details>

        <details class="nav-group" <?php echo $is_sales ? 'open' : ''; ?>>
            <summary>Sales & Users</summary>
            <div class="sub-menu">
                <a href="orders-mgt.php" class="sidebar-link <?php echo ($current_page == 'orders-mgt.php' || $current_page == 'process-refund.php') ? 'active' : ''; ?>">🛒 Orders</a>
                <a href="customers.php" class="sidebar-link <?php echo ($current_page == 'customers.php') ? 'active' : ''; ?>">👥 Customers</a>
                <a href="sales-report.php" class="sidebar-link <?php echo ($current_page == 'sales-report.php') ? 'active' : ''; ?>">📈 Sales Report</a>
            </div>
        </details>

        <details class="nav-group" <?php echo $is_marketing ? 'open' : ''; ?>>
            <summary>Marketing</summary>
            <div class="sub-menu">
                <a href="manage-coupons.php" class="sidebar-link <?php echo ($current_page == 'manage-coupons.php' || $current_page == 'create-coupons.php') ? 'active' : ''; ?>">🎟️ Coupons</a>
                <a href="admin-rewards.php" class="sidebar-link <?php echo ($current_page == 'admin-rewards.php') ? 'active' : ''; ?>">👑 Rewards</a>
                <a href="update-banners.php" class="sidebar-link <?php echo ($current_page == 'update-banners.php') ? 'active' : ''; ?>">
                    🖼️ Banners <span style="font-size: 8px; background: #fee2e2; color: #ef4444; padding: 2px 5px; border-radius: 4px; margin-left: auto;">UI</span>
                </a>
            </div>
        </details>

        <details class="nav-group" <?php echo $is_settings ? 'open' : ''; ?>>
            <summary>Settings</summary>
            <div class="sub-menu">
                <a href="shipping-rules.php" class="sidebar-link <?php echo ($current_page == 'shipping-rules.php') ? 'active' : ''; ?>">🚚 Shipping</a>
                <a href="tax-rules.php" class="sidebar-link <?php echo ($current_page == 'tax-rules.php') ? 'active' : ''; ?>">⚖️ Tax Rules</a>
            </div>
        </details>
    </nav>

    <div class="admin-profile-card">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 32px; height: 32px; background: #ff8002; color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px;">
                <?php echo strtoupper(substr($sidebar_admin_name, 0, 1)); ?>
            </div>
            <div style="overflow: hidden; max-width: 100px;">
                <div style="font-weight: 800; color: #0f172a; font-size: 13px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;"><?php echo $sidebar_admin_name; ?></div>
                <span style="font-size: 9px; color: #ea580c; font-weight: 700; text-transform: uppercase;">System Admin</span>
            </div>
        </div>

        <a href="../logout.php" class="btn-admin-logout" title="Secure Logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
        </a>
    </div>
</aside>
