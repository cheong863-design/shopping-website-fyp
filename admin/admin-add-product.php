<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php'; 

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

$cat_query = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
$existing_categories = [];
while($row = mysqli_fetch_assoc($cat_query)) {
    $existing_categories[] = $row['category'];
}

$msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_product'])) {
    $product_code = mysqli_real_escape_string($conn, trim($_POST['product_code']));
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price = floatval($_POST['base_price']);
    $d_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : "NULL";
    
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    if ($category === '___NEW_CATEGORY___') {
        $category = mysqli_real_escape_string($conn, trim($_POST['new_category_name']));
    }
    
    $tags = mysqli_real_escape_string($conn, trim($_POST['tags']));
    $status = isset($_POST['draft_mode']) ? 'draft' : 'active';
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;

    $check_sql = "SELECT id FROM products WHERE product_code = '$product_code' OR LOWER(name) = LOWER('$name')";
    $check_res = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_res) > 0) {
        $error_msg = "A product with the code <strong>'$product_code'</strong> or the name <strong>'$name'</strong> already exists. Please check your inventory or use a unique code.";
    } else {
        $image_name = "default-product.png";
        if (!empty($_FILES['product_image']['name'])) {
            $target_dir = "../assets/images/"; 
            $image_name = time() . "_" . basename($_FILES['product_image']['name']);
            move_uploaded_file($_FILES['product_image']['tmp_name'], $target_dir . $image_name);
        }

        $sql = "INSERT INTO products (product_code, name, description, price, discount_price, category, tags, image, status, stock) 
                VALUES ('$product_code', '$name', '$desc', $price, $d_price, '$category', '$tags', '$image_name', '$status', $stock)";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: products-list.php?msg=added");
            exit();
        } else {
            $error_msg = "Database Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Add New Product - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        
        @keyframes elasticUp {
            0% { opacity: 0; transform: translateY(40px) scale(0.96); }
            60% { opacity: 1; transform: translateY(-5px) scale(1.01); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes btnShine {
            0% { left: -100%; opacity: 0; }
            20% { left: 100%; opacity: 1; }
            100% { left: 100%; opacity: 0; }
        }
        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .admin-header { 
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; 
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        .breadcrumb { font-size: 11px; color: #64748b; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }
        
        .add-product-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }

        .admin-card { 
            background: #fff; padding: 35px; border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 25px; 
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        .admin-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        
        .main-col .admin-card:nth-child(1) { animation-delay: 0.15s; }
        .main-col .admin-card:nth-child(2) { animation-delay: 0.25s; }
        .side-col .admin-card:nth-child(1) { animation-delay: 0.2s; }
        .side-col .admin-card:nth-child(2) { animation-delay: 0.3s; }

        .admin-card h3 { margin: 0 0 25px 0; font-size: 16px; color: #0f172a; font-weight: 700; border-bottom: 1px dashed #e2e8f0; padding-bottom: 15px; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        
        .form-group { position: relative; margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; transition: color 0.3s ease; }
        
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 14px 16px; border: 2px solid #f1f5f9; border-radius: 10px; 
            background: #f8fafc; outline: none; font-size: 14px; color: #0f172a; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-sizing: border-box; 
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { 
            border-color: #ff8002; background: #fff; 
            box-shadow: 0 8px 20px rgba(255,128,2,0.08), 0 0 0 4px rgba(255,128,2,0.1); 
            transform: translateY(-2px); 
        }
        .form-group:focus-within label { color: #ff8002; }

        .upload-area {
            border: 2px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; 
            text-align: center; padding: 40px 20px; transition: all 0.3s ease; 
            position: relative; overflow: hidden; cursor: pointer;
        }
        .upload-area:hover { 
            border-color: #ff8002; background: #fff7ed; 
            box-shadow: inset 0 0 0 4px rgba(255, 128, 2, 0.05);
        }
        .upload-label { cursor: pointer; display: block; color: #64748b; font-weight: 600; font-size: 14px; transition: transform 0.3s ease; }
        .upload-area:hover .upload-label { transform: scale(1.05); color: #ff8002; }

        .header-actions { display: flex; gap: 15px; }
        .btn-discard { 
            background: #fff; color: #475569; border: 1px solid #e2e8f0; padding: 12px 24px; 
            border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; 
        }
        .btn-discard:hover { background: #f1f5f9; color: #0f172a; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        .btn-add { 
            position: relative; overflow: hidden; background: linear-gradient(135deg, #ff8002, #ea580c); 
            color: #fff; border: none; padding: 12px 28px; border-radius: 10px; font-weight: 700; 
            cursor: pointer; box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3); transition: all 0.3s ease; 
        }
        .btn-add:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(234, 88, 12, 0.4); }
        .btn-add:active { transform: translateY(1px); }
        .btn-add::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-20deg); animation: btnShine 3s infinite 1.5s;
        }

        .alert-error { 
            background: #fee2e2; color: #b91c1c; padding: 16px 20px; border-radius: 10px; 
            border: 1px solid #fca5a5; margin-bottom: 25px; font-size: 14px; font-weight: 500;
            animation: elasticUp 0.5s both, shakeError 0.5s ease-in-out 0.5s; 
            display: flex; align-items: flex-start; gap: 12px;
        }

        #newCategoryInput {
            display: none; margin-top: 15px; border: 2px solid #ff8002; background: #fff7ed;
            animation: elasticUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        #newCategoryInput:focus { box-shadow: 0 8px 20px rgba(255,128,2,0.15); }
    </style>
</head> 
<body class="admin-layout">
    <div class="admin-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="admin-main add-product-main">
            <form action="admin-add-product.php" method="POST" enctype="multipart/form-data">
                
                <header class="admin-header">
                    <div>
                        <div class="breadcrumb">DASHBOARD > PRODUCTS > ADD NEW</div>
                        <h1 style="margin: 5px 0 0 0; font-size: 26px; color: #0f172a;">Create Product</h1>
                    </div>
                    <div class="header-actions">
                        <button type="button" class="btn-discard" onclick="window.location.href='products-list.php'">Discard</button>
                        <button type="submit" name="create_product" class="btn-add">✨ Save Product</button>
                    </div>
                </header>

                <?php if(!empty($error_msg)): ?>
                    <div class="alert-error">
                        <span style="font-size: 20px;">🚨</span>
                        <div>
                            <strong style="display: block; margin-bottom: 4px;">Action Denied</strong>
                            <?php echo $error_msg; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="add-product-grid">
                    <div class="main-col">
                        <div class="admin-card">
                            <h3><span style="margin-right: 8px;">📝</span> General Information</h3>
                            
                            <div class="grid-2">
                                <div class="form-group">
                                    <label>Product Code / SKU <span style="color: #ef4444;">*</span></label>
                                    <input type="text" name="product_code" placeholder="e.g. MEN-TSHIRT-001" required>
                                </div>
                                <div class="form-group">
                                    <label>Product Name <span style="color: #ef4444;">*</span></label>
                                    <input type="text" name="name" placeholder="e.g. Minimalist Wooden Chair" required>
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Description</label>
                                <textarea name="description" rows="6" placeholder="Tell us more about the product..."></textarea>
                            </div>
                        </div>

                        <div class="admin-card">
                            <h3><span style="margin-right: 8px;">🖼️</span> Product Media</h3>
                            <div class="upload-area" id="uploadArea" onclick="document.getElementById('imgInput').click();">
                                <input type="file" name="product_image" id="imgInput" accept="image/*" hidden>
                                <label class="upload-label" id="uploadLabel">
                                    <span style="font-size: 36px; display: block; margin-bottom: 10px;">☁️</span> 
                                    Click or drag image to upload<br>
                                    <span style="font-size: 11px; color: #94a3b8; font-weight: 400; margin-top: 5px; display: block;">Supports JPG, PNG, WEBP (Max 2MB)</span>
                                </label>
                                <img id="imagePreview" src="" alt="Image Preview" style="display:none; max-width: 100%; max-height: 300px; border-radius: 10px; margin: 0 auto; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    </div>

                    <aside class="side-col">
                        <div class="admin-card">
                            <h3><span style="margin-right: 8px;">💰</span> Pricing & Inventory</h3>
                            <div class="form-group">
                                <label>Base Price (MYR) <span style="color: #ef4444;">*</span></label>
                                <input type="number" step="0.01" name="base_price" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label>Discount Price (Optional)</label>
                                <input type="number" step="0.01" name="discount_price" placeholder="0.00">
                            </div>
                            <div class="form-group" style="border-top: 1px dashed #e2e8f0; padding-top: 20px; margin-bottom: 0;">
                                <label>Initial Stock Quantity <span style="color: #ef4444;">*</span></label>
                                <input type="number" name="stock" placeholder="e.g. 50" required min="0">
                            </div>
                        </div>

                        <div class="admin-card">
                            <h3><span style="margin-right: 8px;">🗂️</span> Organization</h3>
                            <div class="form-group">
                                <label>Category <span style="color: #ef4444;">*</span></label>
                                <select name="category" id="categorySelect" onchange="toggleCategoryInput()" required>
                                    <option value="" disabled selected>Select a category...</option>
                                    <?php foreach($existing_categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                    <?php endforeach; ?>
                                    
                                    <?php if(empty($existing_categories)): ?>
                                        <option value="Men">Men</option>
                                        <option value="Women">Women</option>
                                        <option value="Accessories">Accessories</option>
                                    <?php endif; ?>
                                    
                                    <option disabled>──────────</option>
                                    <option value="___NEW_CATEGORY___" style="color: #ff8002; font-weight: bold;">➕ Add New Category...</option>
                                </select>
                                
                                <input type="text" name="new_category_name" id="newCategoryInput" placeholder="Type new category name...">
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Tags</label>
                                <input type="text" name="tags" placeholder="e.g. new, wood, modern">
                            </div>
                        </div>
                        
                        <div class="admin-card">
                            <h3><span style="margin-right: 8px;">⚙️</span> Visibility</h3>
                            <div style="display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                                <input type="checkbox" name="draft_mode" id="draftMode" style="width: 18px; height: 18px; cursor: pointer; accent-color: #ff8002;">
                                <div>
                                    <label for="draftMode" style="margin: 0; cursor: pointer; font-size: 14px; font-weight: 700; color: #0f172a; display: block;">Save as Draft</label>
                                    <span style="font-size: 11px; color: #64748b;">Hidden from storefront</span>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </form>
        </main>
    </div>

    <script>
        const imgInput = document.getElementById('imgInput');
        const imagePreview = document.getElementById('imagePreview');
        const uploadLabel = document.getElementById('uploadLabel');
        const uploadArea = document.getElementById('uploadArea');

        imgInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    
                    uploadLabel.style.display = 'none'; 
                    imagePreview.style.opacity = 0;
                    imagePreview.style.display = 'block';
                    uploadArea.style.padding = '20px'; 
                    
                    setTimeout(() => {
                        imagePreview.style.transition = 'opacity 0.5s ease';
                        imagePreview.style.opacity = 1;
                    }, 50);
                }
                reader.readAsDataURL(file);
            }
        });

        function toggleCategoryInput() {
            const select = document.getElementById('categorySelect');
            const input = document.getElementById('newCategoryInput');
            
            if (select.value === '___NEW_CATEGORY___') {
                input.style.display = 'block';
                input.setAttribute('required', 'true');
                setTimeout(() => input.focus(), 100); 
            } else {
                input.style.display = 'none';
                input.removeAttribute('required');
                input.value = '';
            }
        }
    </script>
</body>
</html>
