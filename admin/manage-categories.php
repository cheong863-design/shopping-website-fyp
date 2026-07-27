<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

$sql = "SELECT category, COUNT(id) as product_count FROM products GROUP BY category ORDER BY product_count DESC";
$cat_res = mysqli_query($conn, $sql);
$total_cats = mysqli_num_rows($cat_res);

function getCategoryIcon($cat_name) {
    $cat_name = strtolower($cat_name);
    if (strpos($cat_name, 'men') !== false || strpos($cat_name, 'apparel') !== false) return '👕';
    if (strpos($cat_name, 'accessories') !== false || strpos($cat_name, 'watch') !== false) return '⌚';
    if (strpos($cat_name, 'shoe') !== false || strpos($cat_name, 'footwear') !== false) return '👟';
    if (strpos($cat_name, 'home') !== false || strpos($cat_name, 'decor') !== false) return '🛋️';
    if (strpos($cat_name, 'women') !== false) return '👗';
    if (strpos($cat_name, 'electronic') !== false) return '💻';
    return '📁'; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Manage Categories - FAIFA Admin</title>
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

        @keyframes btnShine {
            0% { left: -100%; opacity: 0; }
            20% { left: 100%; opacity: 1; }
            100% { left: 100%; opacity: 0; }
        }

        @keyframes modalPopSmooth {
            0% { transform: scale(0.9) translateY(20px); opacity: 0; }
            60% { transform: scale(1.02) translateY(-2px); opacity: 1; }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }

        @keyframes radarPulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
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
        
        .cat-count-text { color: #64748b; font-size: 14px; font-weight: 700; background: #f1f5f9; padding: 8px 16px; border-radius: 8px; }

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

        .cat-row { 
            opacity: 0; animation: slideInRow 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.01);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .cat-row:nth-child(1) { animation-delay: 0.3s; }
        .cat-row:nth-child(2) { animation-delay: 0.38s; }
        .cat-row:nth-child(3) { animation-delay: 0.46s; }
        .cat-row:nth-child(4) { animation-delay: 0.54s; }
        .cat-row:nth-child(5) { animation-delay: 0.62s; }
        .cat-row:nth-child(n+6) { animation-delay: 0.7s; }

        .admin-table:hover .cat-row:not(:hover) {
            opacity: 0.4;
            transform: scale(0.98);
            filter: grayscale(40%);
        }
        
        .cat-row:hover td { 
            background: #fff;
            border-color: #e2e8f0;
        }
        .cat-row:hover {
            transform: scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            position: relative; z-index: 10;
        }

        .cat-info { display: flex; align-items: center; gap: 15px; }
        .cat-icon-box { 
            width: 50px; height: 50px; background: linear-gradient(135deg, #fff7ed, #ffedd5); 
            color: #ea580c; border-radius: 14px; display: flex; align-items: center; justify-content: center; 
            font-size: 24px; box-shadow: inset 0 2px 4px rgba(255,255,255,0.8);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .cat-row:hover .cat-icon-box { transform: scale(1.15) rotate(5deg); box-shadow: 0 8px 15px rgba(234, 88, 12, 0.2); }
        .cat-name { color: #0f172a; font-size: 16px; font-weight: 800; letter-spacing: -0.3px; }
        
        .count-badge { background: #f8fafc; padding: 6px 12px; border-radius: 8px; font-size: 13px; color: #475569; font-weight: 700; border: 1px solid #e2e8f0; display: inline-block; }
        
        .st-pill { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; text-transform: uppercase; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .st-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #10b981; animation: radarPulse 2s infinite; }
        
        .action-icon { text-decoration: none; padding: 10px; font-size: 16px; border-radius: 10px; transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); cursor: pointer; display: inline-block; background: #f8fafc; border: 1px solid #f1f5f9; color: #64748b; margin-right: 5px; }
        .action-icon:hover { background: #fff; border-color: #cbd5e1; transform: scale(1.1); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); color: #0f172a; }

        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(15, 23, 42, 0.4); z-index: 9999; align-items: center; justify-content: center; 
            backdrop-filter: blur(8px);
            opacity: 0; transition: opacity 0.3s ease;
        }
        .modal-overlay.active { opacity: 1; display: flex; }
        
        .cat-modal-inner { 
            background: #fff; width: 480px; padding: 35px; border-radius: 20px; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.5);
        }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="dash-header-top">
                <div class="page-title">
                    <div style="font-size: 11px; color: #64748b; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 8px; text-transform: uppercase;">DASHBOARD > CATEGORIES</div>
                    <h1>Manage Categories</h1>
                    <p>Organize and maintain your store's product taxonomy.</p>
                </div>
                <button class="btn-primary" onclick="openModal()">
                    <span style="font-size: 16px;">✨</span> Auto-Generate Category
                </button>
            </div>

            <div class="toolbar-card">
                <div class="search-box">
                    <span>🔍</span>
                    <input type="text" id="searchInput" onkeyup="filterCategories()" placeholder="Search category name...">
                </div>
                <div class="cat-count-text" id="catCountText">
                    Total: <strong style="color: #0f172a; font-size: 16px;"><?php echo $total_cats; ?></strong>
                </div>
            </div>

            <div class="table-container">
                <table class="admin-table" id="categoryTable">
                    <thead>
                        <tr>
                            <th>CATEGORY NAME</th>
                            <th>PRODUCT COUNT</th>
                            <th>STATUS</th>
                            <th style="text-align: right; padding-right: 35px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_cats > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($cat_res)): ?>
                            <tr class="cat-row">
                                <td>
                                    <div class="cat-info">
                                        <div class="cat-icon-box">
                                            <?php echo getCategoryIcon($row['category']); ?>
                                        </div>
                                        <span class="cat-name"><?php echo htmlspecialchars($row['category'] ? strtoupper($row['category']) : 'UNCATEGORIZED'); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="count-badge">📦 <?php echo $row['product_count']; ?> items</span>
                                </td>
                                <td>
                                    <span class="st-pill">Active Live</span>
                                </td>
                                <td style="text-align: right; padding-right: 25px;">
                                    <a onclick="alert('💡 To rename a category, please edit the category name inside the specific products via the Products menu.')" class="action-icon" title="Edit Category">✏️</a>
                                    <a onclick="alert('🗑️ Categories are automatically removed when no products are assigned to them. To delete this category, please delete or reassign its products.')" class="action-icon" title="Delete Category" style="color: #ef4444;">🗑️</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="cat-row">
                                <td colspan="4" style="text-align: center; padding: 80px 20px; color: #64748b; background: #fff; border-radius: 16px; border: 2px dashed #e2e8f0;">
                                    <div style="font-size: 50px; margin-bottom: 20px; opacity: 0.5;">🗂️</div>
                                    <p style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a;">No categories found</p>
                                    <p style="margin: 8px 0 25px 0; font-size: 14px;">Add products with categories to see them automatically generated here.</p>
                                    <button class="btn-primary" onclick="openModal()" style="font-size: 14px; padding: 12px 28px;">Generate First Category</button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="addCatModal" class="modal-overlay">
        <div class="cat-modal-inner" id="modalInner">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px;">
                <div style="width: 55px; height: 55px; background: linear-gradient(135deg, #fff7ed, #ffedd5); color: #ff8002; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: inset 0 2px 4px rgba(255,255,255,0.8);">✨</div>
                <div>
                    <h3 style="margin: 0 0 5px 0; font-size: 20px; color: #0f172a; font-weight: 800; letter-spacing: -0.5px;">Smart Taxonomy</h3>
                    <p style="margin: 0; font-size: 13px; color: #ff8002; font-weight: 600;">Dynamic Category Generation</p>
                </div>
            </div>
            
            <p style="color: #475569; font-size: 14px; line-height: 1.6; margin-bottom: 30px; padding-bottom: 25px; border-bottom: 1px dashed #e2e8f0;">
                FAIFA uses an automatic tagging system to keep your database lightweight. <strong style="color:#0f172a;">Empty categories cannot be created manually.</strong><br><br>
                To create a new category, simply click below to add a new product and type your new category name into the category field!
            </p>
            
            <div style="display: flex; justify-content: flex-end; gap: 15px;">
                <button onclick="closeModal()" style="background: #f8fafc; color: #475569; border: 2px solid #e2e8f0; padding: 12px 24px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">Cancel</button>
                <a href="admin-add-product.php" class="btn-primary" style="padding: 12px 24px;">Proceed to Add Product ➔</a>
            </div>
        </div>
    </div>

    <script>
        function filterCategories() {
            let input = document.getElementById("searchInput").value.toUpperCase();
            let table = document.getElementById("categoryTable");
            let tr = table.getElementsByClassName("cat-row");
            let visibleCount = 0;

            for (let i = 0; i < tr.length; i++) {
                let catNameSpan = tr[i].querySelector(".cat-name");
                if (catNameSpan) {
                    let txtValue = catNameSpan.textContent || catNameSpan.innerText;
                    if (txtValue.toUpperCase().indexOf(input) > -1) {
                        tr[i].style.display = "";
                        visibleCount++;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
            document.getElementById("catCountText").innerHTML = "Total: <strong style='color:#0f172a; font-size:16px;'>" + visibleCount + "</strong>";
        }

        const modal = document.getElementById('addCatModal');
        const modalInner = document.getElementById('modalInner');

        function openModal() {
            modal.style.display = 'flex';
            void modal.offsetWidth; 
            modal.classList.add('active');
            modalInner.style.animation = 'modalPopSmooth 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
        }

        function closeModal() {
            modal.classList.remove('active');
            modalInner.style.animation = 'none'; 
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    </script>
</body>
</html>
