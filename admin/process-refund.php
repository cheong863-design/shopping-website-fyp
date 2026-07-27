<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders-mgt.php");
    exit();
}
$order_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_refund'])) {
    $reason = mysqli_real_escape_string($conn, $_POST['refund_reason']);
    $notes = mysqli_real_escape_string($conn, $_POST['refund_notes']);

    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, "UPDATE orders SET status = 'Refunded' WHERE id = $order_id");

        $items_res = mysqli_query($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id");
        while ($item = mysqli_fetch_assoc($items_res)) {
            $p_id = $item['product_id'];
            $qty = $item['quantity'];
            mysqli_query($conn, "UPDATE products SET stock = stock + $qty WHERE id = $p_id");
        }

        mysqli_commit($conn);
        header("Location: orders-mgt.php?msg=refunded");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = "Refund failed: " . $e->getMessage();
    }
}

$order_sql = "SELECT o.*, u.full_name, u.email
              FROM orders o JOIN users u ON o.user_id = u.id
              WHERE o.id = $order_id";
$order_res = mysqli_query($conn, $order_sql);
$order = mysqli_fetch_assoc($order_res);

if (!$order) { header("Location: orders-mgt.php"); exit(); }

$items_sql = "SELECT oi.*, p.name, p.image, p.price as current_price
              FROM order_items oi
              JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = $order_id";
$items_res = mysqli_query($conn, $items_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Process Refund - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700;800&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>

        @keyframes elasticUp {
            0% { opacity: 0; transform: translateY(40px) scale(0.96); }
            60% { opacity: 1; transform: translateY(-5px) scale(1.01); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes scanline {
            0% { top: 0; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .refund-container { max-width: 800px; margin: 0 auto; padding-bottom: 50px; }

        .dash-header-top { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        .page-title h1 { margin: 0 0 5px 0; font-size: 32px; color: #0f172a; font-weight: 800; letter-spacing: -1px; }

        .receipt-wrapper {
            background: #fff; border-radius: 4px; padding: 40px; margin-bottom: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05), 0 5px 15px rgba(0,0,0,0.03);
            position: relative; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both;

            background-image: radial-gradient(circle at 10px -5px, transparent 12px, #fff 13px), radial-gradient(circle at 10px calc(100% + 5px), transparent 12px, #fff 13px);
            background-size: 20px 100%; background-position: top, bottom; background-repeat: repeat-x;
        }

        .receipt-brand { text-align: center; margin-bottom: 30px; border-bottom: 2px dashed #cbd5e1; padding-bottom: 20px; }
        .receipt-brand h2 { margin: 0; font-family: 'JetBrains Mono', monospace; font-size: 24px; font-weight: 800; letter-spacing: 2px; color: #0f172a; }
        .receipt-brand p { margin: 5px 0 0 0; font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #64748b; }

        .css-barcode {
            height: 40px; width: 100%; max-width: 250px; margin: 15px auto 0;
            background: repeating-linear-gradient(90deg, #0f172a, #0f172a 2px, transparent 2px, transparent 4px, #0f172a 4px, #0f172a 5px, transparent 5px, transparent 8px, #0f172a 8px, #0f172a 12px, transparent 12px, transparent 14px);
            position: relative; overflow: hidden;
        }

        .css-barcode::after {
            content: ''; position: absolute; left: -10%; right: -10%; height: 2px; background: #ef4444;
            box-shadow: 0 0 8px #ef4444; animation: scanline 2s ease-in-out infinite alternate;
        }

        .receipt-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #334155; }
        .receipt-divider { border-top: 1px dashed #cbd5e1; margin: 20px 0; }

        .receipt-item { display: flex; justify-content: space-between; margin-bottom: 10px; font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #0f172a; font-weight: 700;}
        .item-qty { color: #64748b; margin-right: 10px; }

        .receipt-total { display: flex; justify-content: space-between; align-items: baseline; font-family: 'JetBrains Mono', monospace; margin-top: 20px; }
        .receipt-total span { font-size: 16px; font-weight: 800; color: #0f172a; }
        .receipt-total strong { font-size: 28px; font-weight: 800; color: #ef4444; letter-spacing: -1px; }

        .form-card {
            background: #fff; border-radius: 16px; padding: 30px; margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid rgba(226, 232, 240, 0.8);
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
        }

        .form-group label { font-size: 12px; text-transform: uppercase; font-weight: 800; color: #475569; margin-bottom: 10px; display: block; letter-spacing: 0.5px; }
        .form-group select, .form-group textarea {
            width: 100%; padding: 16px; border: 2px solid #f1f5f9; border-radius: 12px;
            background: #f8fafc; outline: none; font-size: 14px; color: #0f172a; font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-sizing: border-box;
        }
        .form-group select:focus, .form-group textarea:focus {
            border-color: #ef4444; background: #fff;
            box-shadow: 0 8px 20px rgba(239,68,68,0.08), 0 0 0 4px rgba(239,68,68,0.1); transform: translateY(-2px);
        }

        .danger-zone {
            background: #fff; border-radius: 16px; padding: 35px; border: 2px solid #fecaca;
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.05); position: relative; overflow: hidden;
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both;
        }

        .danger-zone::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px;
            background: repeating-linear-gradient(45deg, #ef4444, #ef4444 10px, #f87171 10px, #f87171 20px);
        }

        .slide-container {
            width: 100%; height: 65px; background: #fef2f2; border-radius: 12px;
            position: relative; border: 1px solid #fca5a5; overflow: hidden; margin-top: 25px;
            display: flex; align-items: center; justify-content: center;

            user-select: none;
            -webkit-user-select: none;
            touch-action: none;
        }

        .slide-text {
            font-size: 16px; font-weight: 800; color: #ef4444; letter-spacing: 1px; z-index: 1;
            pointer-events: none; transition: opacity 0.2s;
        }

        .slide-thumb {
            position: absolute; left: 6px; top: 6px; bottom: 6px; width: 60px;
            background: #ef4444; border-radius: 8px; cursor: grab; z-index: 2;
            display: flex; align-items: center; justify-content: center; font-size: 20px;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); transition: transform 0.3s ease;
            will-change: transform;
        }
        .slide-thumb:active { cursor: grabbing; transform: scale(0.95); }

        .slide-fill {
            position: absolute; left: 0; top: 0; bottom: 0; width: 0;
            background: #ef4444; z-index: 0; transition: width 0.1s;
        }

        .btn-cancel {
            display: block; text-align: center; color: #64748b; text-decoration: none; font-weight: 700;
            font-size: 14px; margin-top: 20px; transition: color 0.3s;
        }
        .btn-cancel:hover { color: #0f172a; }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">

        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <div class="refund-container">
                <div class="dash-header-top">
                    <div class="page-title">
                        <div style="font-size: 11px; color: #64748b; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 8px;">
                            <a href="orders-mgt.php" style="color: #94a3b8; text-decoration: none;">ORDERS QUEUE</a>
                            <span style="margin: 0 5px;">/</span>
                            <span style="color: #ef4444;">SECURITY OVERRIDE</span>
                        </div>
                        <h1>Process Refund</h1>
                    </div>
                </div>

                <form action="process-refund.php?id=<?php echo $order_id; ?>" method="POST" id="refundForm">

                    <div class="receipt-wrapper">
                        <div class="receipt-brand">
                            <h2>FAIFA STORE</h2>
                            <p>REFUND AUTHORIZATION SLIP</p>
                            <div class="css-barcode"></div>
                        </div>

                        <div class="receipt-row">
                            <span>TRANS_ID</span>
                            <span>#ORD-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="receipt-row">
                            <span>CUSTOMER</span>
                            <span><?php echo htmlspecialchars($order['full_name']); ?></span>
                        </div>
                        <div class="receipt-row">
                            <span>DATE</span>
                            <span><?php echo date('Y/m/d H:i:s', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="receipt-row">
                            <span>METHOD</span>
                            <span>ORIGINAL_PAYMENT</span>
                        </div>

                        <div class="receipt-divider"></div>

                        <div style="margin-bottom: 10px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #94a3b8;">ITEMS TO RESTOCK:</div>

                        <?php
                        mysqli_data_seek($items_res, 0);
                        while($item = mysqli_fetch_assoc($items_res)):
                        ?>
                        <div class="receipt-item">
                            <div>
                                <span class="item-qty">[<?php echo $item['quantity']; ?>x]</span>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </div>
                            <span><?php echo number_format($item['price'], 2); ?></span>
                        </div>
                        <?php endwhile; ?>

                        <div class="receipt-divider"></div>

                        <div class="receipt-total">
                            <span>TOTAL REFUND</span>
                            <strong>MYR <?php echo number_format($order['total_price'], 2); ?></strong>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-group">
                            <label>Reason for Reversal <span style="color: #ef4444;">*</span></label>
                            <select name="refund_reason" id="refundReason" required>
                                <option value="" disabled selected>Select a justification for audit...</option>
                                <option value="Customer Request">Customer requested cancelation</option>
                                <option value="Out of Stock">Inventory Discrepancy (OOS)</option>
                                <option value="Defective">Product Damaged / Defective</option>
                                <option value="Fraudulent">Suspected Fraud / Chargeback</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-top: 20px; margin-bottom: 0;">
                            <label>Internal Audit Notes</label>
                            <textarea name="refund_notes" rows="2" placeholder="Record specifics for future reference..."></textarea>
                        </div>
                    </div>

                    <div class="danger-zone">
                        <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #991b1b; font-weight: 800;">Danger Zone</h3>
                        <p style="margin: 0; font-size: 13px; color: #b91c1c; font-weight: 500;">This action will permanently reverse the transaction and restock inventory.</p>

                        <div class="slide-container" id="slideTrack">
                            <div class="slide-fill" id="slideFill"></div>
                            <div class="slide-text" id="slideText">SLIDE TO REFUND</div>
                            <div class="slide-thumb" id="slideThumb">➔</div>
                        </div>

                        <button type="submit" name="confirm_refund" id="realSubmitBtn" style="display: none;"></button>

                        <a href="orders-mgt.php" class="btn-cancel">Cancel & Go Back</a>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <script>
        const track = document.getElementById('slideTrack');
        const thumb = document.getElementById('slideThumb');
        const text = document.getElementById('slideText');
        const fill = document.getElementById('slideFill');
        const reasonSelect = document.getElementById('refundReason');
        const submitBtn = document.getElementById('realSubmitBtn');

        let isDragging = false;
        let startClientX = 0;
        let currentX = 0;
        let maxMove = 0;
        let unlocked = false;

        function dragStart(e) {
            if (unlocked) return;

            if (reasonSelect.value === "") {
                reasonSelect.style.borderColor = '#ef4444';
                reasonSelect.style.boxShadow = '0 0 0 4px rgba(239,68,68,0.2)';
                setTimeout(() => { reasonSelect.style.boxShadow = 'none'; }, 500);
                reasonSelect.focus();
                return;
            }

            isDragging = true;

            let clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;

            startClientX = clientX - currentX;
            maxMove = track.offsetWidth - thumb.offsetWidth - 12;

            thumb.style.transition = 'none';
            fill.style.transition = 'none';
        }

        function dragMove(e) {
            if (!isDragging || unlocked) return;

            e.preventDefault();

            let clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
            currentX = clientX - startClientX;

            if (currentX < 0) currentX = 0;
            if (currentX > maxMove) currentX = maxMove;

            thumb.style.transform = `translateX(${currentX}px)`;
            fill.style.width = `${currentX + 30}px`;
            text.style.opacity = 1 - (currentX / maxMove);

            if (currentX >= maxMove * 0.95) {
                unlocked = true;
                isDragging = false;

                thumb.style.transition = 'all 0.3s ease';
                thumb.style.transform = `translateX(${maxMove}px)`;
                thumb.style.background = '#fff';
                thumb.style.color = '#ef4444';
                thumb.innerHTML = '✔';

                fill.style.transition = 'width 0.3s ease';
                fill.style.width = '100%';

                text.style.color = '#fff';
                text.innerHTML = 'PROCESSING...';
                text.style.opacity = 1;
                text.style.zIndex = 3;

                setTimeout(() => {
                    submitBtn.click();
                }, 800);
            }
        }

        function dragEnd() {
            if (isDragging && !unlocked) {
                isDragging = false;
                currentX = 0;

                thumb.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                thumb.style.transform = 'translateX(0)';

                fill.style.transition = 'width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                fill.style.width = '0';

                text.style.opacity = 1;
            }
        }

        thumb.addEventListener('mousedown', dragStart);
        document.addEventListener('mousemove', dragMove, { passive: false });
        document.addEventListener('mouseup', dragEnd);

        thumb.addEventListener('touchstart', dragStart, { passive: false });
        document.addEventListener('touchmove', dragMove, { passive: false });
        document.addEventListener('touchend', dragEnd);
    </script>
</body>
</html>
