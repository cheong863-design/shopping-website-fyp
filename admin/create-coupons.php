<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_coupon'])) {
    $code = strtoupper(mysqli_real_escape_string($conn, trim($_POST['code'])));
    $type = mysqli_real_escape_string($conn, $_POST['discount_type']);
    $value = floatval($_POST['discount_value']);
    $limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : "NULL";
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);

    $check_sql = "SELECT id FROM coupons WHERE code = '$code'";
    $check_res = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_res) > 0) {
        $error_msg = "Coupon code '$code' already exists. Please use a unique code.";
    } else {
        $insert_sql = "INSERT INTO coupons (code, discount_type, discount_value, usage_limit, start_date, end_date) 
                       VALUES ('$code', '$type', $value, $limit, '$start_date', '$end_date')";
        if (mysqli_query($conn, $insert_sql)) {
            $success_msg = "Coupon '$code' created successfully!";
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
    <title>Create Coupon - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        
        @keyframes fadeUpSpring {
            0% { opacity: 0; transform: translateY(30px); }
            60% { opacity: 1; transform: translateY(-5px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes btnShine {
            0% { left: -100%; opacity: 0; }
            20% { left: 100%; opacity: 1; }
            100% { left: 100%; opacity: 0; }
        }

        @keyframes borderDance {
            100% { background-position: 0px 0px, 300px 100%, 0px 150px, 100% 0px; }
        }

        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(255, 128, 2, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 128, 2, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 128, 2, 0); }
        }

        .dash-header-top { margin-bottom: 30px; animation: fadeUpSpring 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) both; }
        .breadcrumb { font-size: 11px; color: #64748b; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px; text-transform: uppercase; }
        .page-title h1 { margin: 0 0 5px 0; font-size: 26px; color: #0f172a; }
        
        .coupon-form-card { 
            background: #fff; padding: 40px; border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 25px; 
            animation: fadeUpSpring 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.1s both; 
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .section-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 24px 0; display: flex; align-items: center; gap: 10px; }
        .section-icon { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #fff7ed; color: #ff8002; border-radius: 8px; font-size: 14px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 35px; }
        
        .form-group { position: relative; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; transition: color 0.3s ease; }
        
        .form-group input, .form-group select { 
            width: 100%; padding: 14px 16px; border: 2px solid #f1f5f9; border-radius: 10px; 
            background: #f8fafc; outline: none; font-size: 14px; color: #0f172a; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-sizing: border-box; 
        }
        
        .form-group input:focus, .form-group select:focus { 
            border-color: #ff8002; background: #fff; 
            box-shadow: 0 8px 20px rgba(255,128,2,0.08), 0 0 0 4px rgba(255,128,2,0.1); 
            transform: translateY(-2px); 
        }
        .form-group:focus-within label { color: #ff8002; }
        
        .form-hint { display: block; margin-top: 8px; font-size: 11.5px; color: #94a3b8; line-height: 1.4; }

        .form-actions { display: flex; justify-content: flex-end; align-items: center; gap: 15px; margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 25px; }
        .btn-cancel { color: #64748b; text-decoration: none; font-weight: 600; font-size: 14px; padding: 12px 24px; transition: all 0.2s ease; border-radius: 10px; }
        .btn-cancel:hover { background: #f1f5f9; color: #0f172a; }
        
        .btn-submit { 
            position: relative; overflow: hidden; background: linear-gradient(135deg, #ff8002, #ea580c); 
            color: #fff; border: none; padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 14px; 
            cursor: pointer; box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3); transition: all 0.3s ease; 
            display: flex; align-items: center; gap: 8px; 
        }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(234, 88, 12, 0.4); }
        .btn-submit:active { transform: translateY(1px); }
        .btn-submit::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-20deg); animation: btnShine 3s infinite;
        }

        /* ✨ 3D 优惠券预览框 (3D Ticket Preview) */
        .preview-wrapper { 
            perspective: 1000px; 
            animation: fadeUpSpring 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s both; 
        }
        .preview-box { 
            background: #fff; 
            border-radius: 16px; padding: 30px; 
            display: flex; align-items: center; gap: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            transition: transform 0.1s; 
            transform-style: preserve-3d; /
            
            background-image: 
                linear-gradient(90deg, #ff8002 50%, transparent 50%), 
                linear-gradient(90deg, #ff8002 50%, transparent 50%), 
                linear-gradient(0deg, #ff8002 50%, transparent 50%), 
                linear-gradient(0deg, #ff8002 50%, transparent 50%);
            background-repeat: repeat-x, repeat-x, repeat-y, repeat-y;
            background-size: 15px 2px, 15px 2px, 2px 15px, 2px 15px;
            background-position: 0px 0px, 200px 100%, 0px 150px, 100% 0px;
            animation: borderDance 1.5s infinite linear;
        }
        
        .preview-icon { 
            width: 50px; height: 50px; background: linear-gradient(135deg, #fff7ed, #ffedd5); color: #ff8002; 
            border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; 
            flex-shrink: 0; box-shadow: inset 0 2px 4px rgba(255,255,255,0.8);
            transform: translateZ(30px); 
        }
        .preview-content { transform: translateZ(20px);  }
        .preview-content h4 { margin: 0 0 8px 0; font-size: 18px; color: #0f172a; font-weight: 800; letter-spacing: -0.5px; }
        .preview-content p { margin: 0; font-size: 13.5px; color: #64748b; line-height: 1.6; }
        
        .dynamic-code { 
            font-weight: 900; color: #ff8002; background: #fff7ed; padding: 4px 10px; 
            border-radius: 6px; border: 1px solid #fed7aa; 
            display: inline-block; transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); 
        }

        .form-grid > div:nth-child(1) { animation: fadeUpSpring 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.15s both; }
        .form-grid > div:nth-child(2) { animation: fadeUpSpring 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s both; }
        .form-grid > div:nth-child(3) { animation: fadeUpSpring 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.25s both; }
        .form-grid > div:nth-child(4) { animation: fadeUpSpring 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s both; }
        .form-actions { animation: fadeUpSpring 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.4s both; }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="dash-header-top">
                <div class="page-title">
                    <div class="breadcrumb">MARKETING > COUPONS > CREATE</div>
                    <h1>Create New Coupon</h1>
                    <p>Set up a highly converting discount code for your store campaigns.</p>
                </div>
            </div>

            <?php if(isset($success_msg)): ?>
                <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #16a34a; padding: 16px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 600; animation: fadeUpSpring 0.4s both; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">✅</span> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if(isset($error_msg)): ?>
                <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 16px 20px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 600; animation: fadeUpSpring 0.4s both; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">❌</span> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form action="create-coupons.php" method="POST">
                <div class="coupon-form-card">
                    
                    <h3 class="section-title" style="animation: fadeUpSpring 0.5s both;"><div class="section-icon">🎫</div> General Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Coupon Code</label>
                            <input type="text" name="code" id="couponCodeInput" placeholder="e.g. SUMMER24" required onkeyup="updatePreview()">
                            <span class="form-hint">A unique code customers enter at checkout.</span>
                        </div>
                        <div class="form-group">
                            <label>Discount Type</label>
                            <select name="discount_type" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (MYR)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Discount Value</label>
                            <input type="number" step="0.01" name="discount_value" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label>Usage Limit (per user)</label>
                            <input type="number" name="usage_limit" placeholder="Unlimited">
                            <span class="form-hint">Maximum times one user can redeem. Blank = unlimited.</span>
                        </div>
                    </div>

                    <h3 class="section-title" style="border-top: 1px solid #f1f5f9; padding-top: 30px; animation: fadeUpSpring 0.5s 0.35s both;"><div class="section-icon">⏳</div> Validity Period</h3>
                    <div class="form-grid">
                        <div class="form-group" style="animation-delay: 0.4s;">
                            <label>Start Date</label>
                            <input type="date" name="start_date" required>
                        </div>
                        <div class="form-group" style="animation-delay: 0.45s;">
                            <label>End Date</label>
                            <input type="date" name="end_date" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="manage-coupons.php" class="btn-cancel">Cancel</a>
                        <button type="submit" name="create_coupon" class="btn-submit">
                            ✨ Generate Coupon
                        </button>
                    </div>
                </div>
            </form>

            <div class="preview-wrapper">
                <div class="preview-box" id="3dCard">
                    <div class="preview-icon">💸</div>
                    <div class="preview-content">
                        <h4>Live Coupon Preview</h4>
                        <p>Your customers will see this discount as <span class="dynamic-code" id="previewCode">"COUPON_CODE"</span> applied to their shopping cart at checkout.</p>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 40px; margin-bottom: 20px; color: #94a3b8; font-size: 11px; font-weight: 500; animation: fadeUpSpring 0.5s 0.3s both;">
                © 2024 FAIFA Admin. Enterprise Marketing Module.
            </div>
        </main>
    </div>

    <script>
        function updatePreview() {
            let inputVal = document.getElementById("couponCodeInput").value.toUpperCase();
            let previewSpan = document.getElementById("previewCode");
            
            if (inputVal.trim() === "") {
                previewSpan.innerText = '"COUPON_CODE"';
            } else {
                previewSpan.innerText = '"' + inputVal + '"';
            }
            previewSpan.style.transform = "scale(1.15) rotate(-2deg)";
            previewSpan.style.boxShadow = "0 4px 10px rgba(255, 128, 2, 0.3)";
            setTimeout(() => {
                previewSpan.style.transform = "scale(1) rotate(0deg)";
                previewSpan.style.boxShadow = "none";
            }, 200);
        }
        const card = document.getElementById('3dCard');
        
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            const rotateX = -(y / 15); 
            const rotateY = (x / 20);
            
            card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'rotateX(0) rotateY(0) scale3d(1, 1, 1)';
            card.style.transition = 'transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        });
        card.addEventListener('mouseenter', () => {
            card.style.transition = 'transform 0.1s';
        });
    </script>
</body>
</html>
