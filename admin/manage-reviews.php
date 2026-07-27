<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM product_reviews WHERE id = $id");
    header("Location: manage-reviews.php?msg=deleted");
    exit();
}

if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $current_status = mysqli_real_escape_string($conn, $_GET['current']);
    $new_status = ($current_status == 'Approved') ? 'Hidden' : 'Approved';
    mysqli_query($conn, "UPDATE product_reviews SET status = '$new_status' WHERE id = $id");
    header("Location: manage-reviews.php?msg=updated");
    exit();
}

$sql = "SELECT r.*, p.name as product_name, p.image as product_image, u.full_name as user_name
        FROM product_reviews r
        JOIN products p ON r.product_id = p.id
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $sql);

$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, AVG(rating) as avg FROM product_reviews"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Manage Reviews - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
        @keyframes glassTooltipPop {
            0% { opacity: 0; transform: translateY(10px) scale(0.95); filter: blur(10px); }
            100% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
        }

        body { margin: 0; font-family: 'Inter', sans-serif; background: #f8fafc; }

        .dash-header-top { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        .header-title h1 { font-size: 28px; color: #0f172a; margin: 0 0 5px 0; letter-spacing: -0.5px; font-weight: 900; }
        .header-title p { color: #64748b; margin: 0; font-size: 14px; font-weight: 500; }

        .header-stats { display: flex; gap: 20px; }
        .stat-card {
            background: #fff; padding: 20px 25px; border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid rgba(226, 232, 240, 0.6);
            display: flex; flex-direction: column; justify-content: center; min-width: 150px;
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
            transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.06); }
        .stat-card:nth-child(1) { animation-delay: 0.1s; border-bottom: 3px solid #ff8002; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; border-bottom: 3px solid #facc15; }

        .stat-card span { font-size: 11px; font-weight: 800; color: #94a3b8; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase; }
        .stat-card strong { font-size: 32px; color: #0f172a; font-weight: 900; letter-spacing: -1px; line-height: 1;}

        .alert-msg { padding: 16px 24px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 12px; animation: elasticUp 0.4s both; }
        .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1); }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.1); }

        .reviews-table-container {
            animation: elasticUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both;
            position: relative; overflow: hidden;
        }

        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: -12px; table-layout: fixed; }
        .admin-table th:nth-child(1) { width: 22%; } /* PRODUCT */
        .admin-table th:nth-child(2) { width: 15%; } /* CUSTOMER */
        .admin-table th:nth-child(3) { width: 15%; } /* RATING */
        .admin-table th:nth-child(4) { width: 20%; } /* COMMENT */
        .admin-table th:nth-child(5) { width: 12%; } /* DATE */
        .admin-table th:nth-child(6) { width: 10%; } /* STATUS */
        .admin-table th:nth-child(7) { width: 6%; text-align: right; } /* ACTION */

        .admin-table th { padding: 10px 25px; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; border: none; text-align: left; }

        .admin-table td {
            background: rgba(255,255,255,0.7); padding: 22px 25px;
            border-top: 1px solid rgba(226, 232, 240, 0.6); border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            vertical-align: middle; transition: all 0.3s ease; position: relative; z-index: 2;
        }
        .admin-table td:first-child { border-left: 1px solid rgba(226, 232, 240, 0.6); border-radius: 16px 0 0 16px; }
        .admin-table td:last-child { border-right: 1px solid rgba(226, 232, 240, 0.6); border-radius: 0 16px 16px 0; }

        .review-row { opacity: 0; animation: slideInRow 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards; transition: all 0.3s ease;}
        .review-row:nth-child(1) { animation-delay: 0.1s; } .review-row:nth-child(2) { animation-delay: 0.15s; } .review-row:nth-child(3) { animation-delay: 0.2s; } .review-row:nth-child(4) { animation-delay: 0.25s; } .review-row:nth-child(n+5) { animation-delay: 0.3s; }

        .admin-table:hover .review-row:not(:hover) td { opacity: 0.3; filter: grayscale(30%); background: transparent; border-color: transparent;}

        .review-row:hover td {
            background: rgba(255, 251, 250, 0.9); backdrop-filter: blur(4px);
            border-top-color: #fed7aa; border-bottom-color: #fed7aa;
        }
        .review-row:hover td:first-child { border-left-color: #ff8002; box-shadow: -4px 0 15px rgba(255, 128, 2, 0.05); padding-left: 30px; }
        .review-row:hover td:last-child { border-right-color: #fed7aa; box-shadow: 4px 0 15px rgba(255, 128, 2, 0.05); }

        .spotlight-layer {
            position: absolute; top: 0; left: 0; width: 800px; height: 800px;
            transform: translate3d(calc(var(--mouse-x, -1000px) - 50%), calc(var(--mouse-y, -1000px) - 50%), 0);
            background: radial-gradient(circle, rgba(255, 128, 2, 0.06) 0%, transparent 60%);
            pointer-events: none; z-index: 1; opacity: 0; transition: opacity 0.5s ease;
            will-change: transform;
        }
        .reviews-table-container:hover .spotlight-layer { opacity: 1; }

        .product-cell { display: flex; align-items: center; gap: 15px; }
        .prod-img { width: 50px; height: 50px; border-radius: 12px; object-fit: cover; background: #fff; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); flex-shrink: 0;}
        .review-row:hover .prod-img { transform: scale(1.2) rotate(-5deg); box-shadow: 0 8px 20px rgba(0,0,0,0.15); border-radius: 16px; border-color: #ff8002; }

        .prod-name { font-size: 14.5px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; transition: 0.3s; }
        .review-row:hover .prod-name { color: #ff8002; }

        .customer-name { font-size: 13.5px; font-weight: 700; color: #475569; }

        .star-visual { display: inline-flex; position: relative; font-size: 18px; color: #e2e8f0; letter-spacing: 2px; }
        .star-visual::before { content: '★★★★★'; position: absolute; top: 0; left: 0; color: #facc15; overflow: hidden; width: calc(var(--rating) * 20%); text-shadow: 0 2px 8px rgba(250, 204, 21, 0.4); }

        .comment-wrapper { position: relative; cursor: default; }
        .comment-text { font-size: 13px; color: #64748b; font-style: italic; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: color 0.3s; font-weight: 500; }
        .review-row:hover .comment-text { color: #0f172a; }

        .glass-tooltip {
            position: absolute; bottom: calc(100% + 15px); left: -20px; width: 280px;
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6); box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 15px 20px; border-radius: 12px; font-size: 13px; color: #0f172a; line-height: 1.6; font-style: normal;
            font-weight: 500; z-index: 100; pointer-events: none; opacity: 0; visibility: hidden; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .glass-tooltip::after { content:''; position:absolute; bottom:-6px; left:40px; border-width:6px 6px 0; border-style:solid; border-color:rgba(255, 255, 255, 0.9) transparent transparent transparent; }
        .comment-wrapper:hover .glass-tooltip { opacity: 1; visibility: visible; animation: glassTooltipPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

        .date-cell { font-size: 12px; color: #94a3b8; font-weight: 700; }

        .status-pill {
            padding: 6px 14px; border-radius: 10px; font-size: 11px; font-weight: 800;
            text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); display: inline-flex; align-items: center; gap: 6px; box-shadow: inset 0 2px 4px rgba(255,255,255,0.8);
        }
        .status-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; }
        .status-pill.approved { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; } .status-pill.approved::before { background: #10b981; box-shadow: 0 0 8px #10b981; }
        .status-pill.hidden { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; } .status-pill.hidden::before { background: #ef4444; }

        .status-pill:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.08); filter: brightness(0.95); }
        .status-pill:active { transform: translateY(1px) scale(0.95); }

        .btn-delete {
            text-decoration: none; font-size: 16px; width: 36px; height: 36px; border-radius: 10px;
            background: #f8fafc; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; justify-content: center;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); color: #94a3b8;
        }
        .btn-delete:hover { background: #fee2e2; border-color: #fca5a5; color: #ef4444; transform: scale(1.15) rotate(8deg); box-shadow: 0 6px 15px rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="admin-content">
        <header class="admin-header">
            <div class="header-title">
                <div style="font-size: 11px; color: #64748b; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 8px; text-transform: uppercase;">DASHBOARD > MODERATION</div>
                <h1>Product Reviews</h1>
                <p>Manage customer feedback and moderate comments for your storefront.</p>
            </div>
            <div class="header-stats">
                <div class="stat-card">
                    <span>Total Reviews</span>
                    <strong class="kpi-num" data-target="<?php echo $stats['total']; ?>">0</strong>
                </div>
                <div class="stat-card">
                    <span>Avg Rating</span>
                    <div style="display: flex; align-items: baseline; gap: 5px;">
                        <strong class="kpi-num" data-target="<?php echo round($stats['avg'], 1); ?>">0.0</strong>
                        <span style="font-size: 20px; color: #facc15; margin: 0; padding: 0;">★</span>
                    </div>
                </div>
            </div>
        </header>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="alert-msg alert-success">
                <span style="font-size: 20px;">✨</span> Review visibility status has been updated successfully.
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert-msg alert-error">
                <span style="font-size: 20px;">🗑️</span> Review has been permanently removed from the database.
            </div>
        <?php endif; ?>

        <div class="reviews-table-container" id="tableContainer">
            <div class="spotlight-layer"></div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>PRODUCT</th>
                        <th>CUSTOMER</th>
                        <th>RATING</th>
                        <th>COMMENT</th>
                        <th>DATE</th>
                        <th>STATUS</th>
                        <th style="text-align: right; padding-right: 35px;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="review-row">
                            <td>
                                <div class="product-cell">
                                    <img src="../assets/images/<?php echo htmlspecialchars($row['product_image']); ?>" alt="Product" class="prod-img" onerror="this.src='https://placehold.co/50x50/f1f5f9/94a3b8?text=Img'">
                                    <span class="prod-name" title="<?php echo htmlspecialchars($row['product_name']); ?>">
                                        <?php echo htmlspecialchars($row['product_name']); ?>
                                    </span>
                                </div>
                            </td>
                            <td><span class="customer-name"><?php echo htmlspecialchars($row['user_name']); ?></span></td>

                            <td>
                                <div class="star-visual" style="--rating: <?php echo $row['rating']; ?>;" title="Rating: <?php echo $row['rating']; ?>/5">
                                    ★★★★★
                                </div>
                            </td>

                            <td>
                                <div class="comment-wrapper">
                                    <div class="comment-text">
                                        "<?php echo htmlspecialchars($row['comment']); ?>"
                                    </div>
                                    <div class="glass-tooltip">
                                        <?php echo htmlspecialchars($row['comment']); ?>
                                    </div>
                                </div>
                            </td>

                            <td class="date-cell"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>

                            <td>
                                <a href="manage-reviews.php?toggle=<?php echo $row['id']; ?>&current=<?php echo $row['status']; ?>"
                                   class="status-pill <?php echo strtolower($row['status']); ?>"
                                   title="Click to toggle visibility">
                                   <?php echo $row['status']; ?>
                                </a>
                            </td>

                            <td style="text-align: right; padding-right: 25px;">
                                <a href="manage-reviews.php?delete=<?php echo $row['id']; ?>"
                                   class="btn-delete" title="Delete Review" onclick="return confirm('🚨 Are you sure you want to permanently delete this review?')">🗑️</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr class="review-row">
                            <td colspan="7" style="text-align: center; padding: 80px 20px; color: #64748b; background: transparent; border: none;">
                                <div style="font-size: 50px; margin-bottom: 20px; opacity: 0.5;">💬</div>
                                <p style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a;">No reviews yet</p>
                                <p style="margin: 8px 0 0 0; font-size: 14px;">When customers leave feedback, it will automatically appear here for moderation.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const counters = document.querySelectorAll('.kpi-num');
            const speed = 60;

            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                if (target === 0) return;

                const isFloat = target % 1 !== 0;

                const updateCount = () => {
                    const count = +counter.innerText;
                    const inc = target / speed;

                    if (count < target) {
                        let nextVal = count + inc;
                        counter.innerText = isFloat ? (nextVal).toFixed(1) : Math.ceil(nextVal);
                        setTimeout(updateCount, 20);
                    } else {
                        counter.innerText = isFloat ? target.toFixed(1) : target;
                    }
                };
                setTimeout(updateCount, 400);
            });

            const tableCard = document.getElementById('tableContainer');
            const spotlight = tableCard.querySelector('.spotlight-layer');
            let isHovering = false;

            tableCard.addEventListener('mouseenter', () => isHovering = true);
            tableCard.addEventListener('mouseleave', () => isHovering = false);
            tableCard.addEventListener('mousemove', e => {
                if (!isHovering) return;
                requestAnimationFrame(() => {
                    const rect = tableCard.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    spotlight.style.setProperty('--mouse-x', `${x}px`);
                    spotlight.style.setProperty('--mouse-y', `${y}px`);
                });
            });
        });
    </script>
</body>
</html>
