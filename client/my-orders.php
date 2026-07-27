<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ==========================================
// ==========================================
$notif_res = mysqli_query($conn, "SELECT id FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
$has_notifications = ($notif_res && mysqli_num_rows($notif_res) > 0);
// ==========================================

$orders_query = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC");

include 'includes/header.php';
?>

<main class="profile-page">
    <div class="container profile-grid">

        <aside class="profile-sidebar">
            <nav class="side-nav-transparent" id="luxury-side-nav">
                <div class="nav-tracker" id="nav-tracker"></div>

                <a href="profile.php" class="nav-link"><span>01</span> My Profile</a>

                <a href="my-orders.php" class="nav-link active"><span>02</span> My Orders</a>

                <a href="notification.php" class="nav-link">
                    <span>03</span> Notifications
                    <?php if($has_notifications): ?>
                        <div class="notif-ping"></div>
                    <?php endif; ?>
                </a>

                <a href="addresses.php" class="nav-link"><span>04</span> Addresses</a>
                <hr class="nav-divider">
                <a href="logout.php" class="logout-link">Logout</a>
            </nav>
        </aside>

        <section class="profile-main">
            <div id="editorial-paper-wrapper">

                <header class="editorial-header fade-up" style="--delay: 1">
                    <div class="header-inner">
                        <h2 class="title-serif">Order Ledger.</h2>
                        <p class="subtitle-mono">INDEX OF ACQUISITIONS / TOTAL: <?php echo str_pad(mysqli_num_rows($orders_query), 2, '0', STR_PAD_LEFT); ?></p>
                    </div>
                </header>

                <div class="ledger-container">
                    <?php if (mysqli_num_rows($orders_query) > 0): ?>

                        <div class="ledger-header-row fade-up" style="--delay: 2">
                            <div class="lh-col">REFERENCE</div>
                            <div class="lh-col">AMOUNT</div>
                            <div class="lh-col">STATUS</div>
                            <div class="lh-col text-right">ACTION</div>
                        </div>

                        <?php
                        $i = 2;
                        while($order = mysqli_fetch_assoc($orders_query)):
                            $i++;
                            $status_class = strtolower(str_replace(' ', '-', $order['status']));
                        ?>
                        <div class="editorial-ledger-row fade-up" style="--delay: <?php echo $i; ?>">

                            <div class="el-col col-info">
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="ref-link">
                                    ORD/<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?>
                                </a>
                                <p class="ledger-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                            </div>

                            <div class="el-col col-amount">
                                <span class="currency">MYR</span> <span class="amount-serif"><?php echo number_format($order['total_price'], 2); ?></span>
                            </div>

                            <div class="el-col col-status">
                                <div class="status-label <?php echo $status_class; ?>">
                                    <?php echo strtoupper($order['status']); ?>
                                </div>
                                <?php if($order['status'] === 'Shipped' && !empty($order['estimated_delivery'])): ?>
                                    <div class="eta-stamp">EST. DROP: <?php echo date('M d', strtotime($order['estimated_delivery'])); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="el-col col-actions">
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="link-editorial text-muted">View</a>
                                <span class="link-separator">/</span>
                                <a href="track-order.php?id=<?php echo $order['id']; ?>" class="link-editorial text-dark">Track</a>

                                <a href="invoice.php?id=<?php echo $order['id']; ?>" target="_blank" class="icon-link-editorial" title="Download Invoice">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>

                        <div class="empty-editorial fade-up" style="--delay: 2">
                            <p>The archive contains no transaction history.</p>
                            <a href="product.php" class="link-editorial text-dark">Explore the Catalog</a>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

<style>

.container { max-width: 1200px; margin: 0 auto; padding: 0 40px; }

.profile-grid {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 80px;
    align-items: start;
    padding-top: 60px;
    padding-bottom: 120px;
}

.profile-sidebar { position: sticky; top: 120px; height: fit-content; z-index: 10; }

.side-nav-transparent {
    position: relative; display: flex; flex-direction: column;
    padding: 20px 0; background: transparent;
}

.side-nav-transparent::before {
    content: ''; position: absolute; left: 15px; top: 20px; bottom: 20px;
    width: 1px; background: rgba(0,0,0,0.1); z-index: 0;
}

.nav-tracker {
    position: absolute; left: 14px; width: 3px; height: 20px;
    background: #ff8002; z-index: 2;
    transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), height 0.5s cubic-bezier(0.25, 1, 0.5, 1);
    opacity: 0; border-radius: 2px;
}

.side-nav-transparent .nav-link {
    position: relative; z-index: 1; padding: 15px 35px; display: flex; align-items: center;
    text-decoration: none; color: #888; font-size: 13px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 1px; transition: color 0.4s ease;
    font-family: 'Inter', sans-serif;
}
.side-nav-transparent .nav-link span { font-family: monospace; font-size: 10px; margin-right: 15px; opacity: 0.4; transition: 0.4s; }
.side-nav-transparent .nav-link.active { color: #111; font-weight: 800; }
.side-nav-transparent .nav-link.active span { opacity: 1; color: #ff8002; }
.side-nav-transparent .nav-link:hover { color: #111; }
.side-nav-transparent .nav-divider { border: none; border-top: 1px solid rgba(0,0,0,0.1); margin: 20px 35px; }
.side-nav-transparent .logout-link { display: block; padding: 10px 35px; color: #ef4444; font-size: 13px; font-weight: 600; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; font-family: 'Inter', sans-serif;}
.side-nav-transparent .logout-link:hover { opacity: 0.5; }

.notif-ping { width: 6px; height: 6px; background: #ff8002; border-radius: 50%; margin-left: 10px; position: relative; }
.notif-ping::after { content: ''; position: absolute; inset: 0; border-radius: 50%; border: 1px solid #ff8002; animation: pingOut 2s cubic-bezier(0, 0, 0.2, 1) infinite; }
@keyframes pingOut { 75%, 100% { transform: scale(3); opacity: 0; } }

.fade-up { opacity: 0; animation: editorialUp 1.2s cubic-bezier(0.25, 1, 0.5, 1) calc(var(--delay) * 0.1s) forwards; }
@keyframes editorialUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

#editorial-paper-wrapper { color: #111; font-family: 'Inter', sans-serif; padding: 0 10px; }

#editorial-paper-wrapper .editorial-header { margin-bottom: 50px; padding-top: 10px; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid #e5e5e5; padding-bottom: 20px; }
#editorial-paper-wrapper .title-serif { font-family: 'Playfair Display', serif; font-size: 32px; font-style: italic; color: #111; margin: 0; line-height: 1; }
#editorial-paper-wrapper .subtitle-mono { font-family: monospace; font-size: 10px; color: #888; margin: 0; letter-spacing: 2px; }

#editorial-paper-wrapper .ledger-header-row { display: grid; grid-template-columns: 2fr 1.5fr 1.5fr 2fr; padding: 0 20px 15px 20px; border-bottom: 1px solid #e5e5e5; }
#editorial-paper-wrapper .lh-col { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 2px; font-weight: 700; }
#editorial-paper-wrapper .text-right { text-align: right; }

#editorial-paper-wrapper .editorial-ledger-row { display: grid; grid-template-columns: 2fr 1.5fr 1.5fr 2fr; align-items: center; padding: 30px 20px; border-bottom: 1px solid #e5e5e5; transition: 0.4s cubic-bezier(0.25, 1, 0.5, 1); }
#editorial-paper-wrapper .editorial-ledger-row:hover { padding-left: 30px; padding-right: 10px; background: rgba(0,0,0,0.015); }

#editorial-paper-wrapper .el-col { display: flex; flex-direction: column; justify-content: center; }
#editorial-paper-wrapper .col-info { gap: 6px; }
#editorial-paper-wrapper .ref-link { font-family: monospace; font-size: 12px; font-weight: 600; color: #111; text-decoration: none; letter-spacing: 1px; transition: 0.3s; }
#editorial-paper-wrapper .ref-link:hover { color: #ff8002; }
#editorial-paper-wrapper .ledger-date { font-size: 11px; color: #888; margin: 0; font-weight: 500; }

#editorial-paper-wrapper .col-amount { flex-direction: row; align-items: baseline; gap: 4px; }
#editorial-paper-wrapper .currency { font-family: monospace; font-size: 9px; color: #888; font-weight: 700; letter-spacing: 1px; }
#editorial-paper-wrapper .amount-serif { font-family: 'Playfair Display', serif; font-size: 20px; font-style: italic; color: #111; }

#editorial-paper-wrapper .status-label { display: inline-block; font-family: monospace; font-size: 9px; font-weight: 700; letter-spacing: 2px; padding: 4px 0; }
#editorial-paper-wrapper .status-label.pending { color: #854d0e; }
#editorial-paper-wrapper .status-label.shipped, #editorial-paper-wrapper .status-label.in-transit { color: #ff8002; }
#editorial-paper-wrapper .status-label.delivered { color: #111; }
#editorial-paper-wrapper .status-label.cancelled { color: #ef4444; text-decoration: line-through; }

#editorial-paper-wrapper .eta-stamp { font-family: monospace; font-size: 9px; font-weight: 800; color: #10b981; letter-spacing: 1px; margin-top: 4px; }

#editorial-paper-wrapper .col-actions { flex-direction: row; justify-content: flex-end; align-items: center; gap: 12px; font-family: monospace; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
#editorial-paper-wrapper .link-editorial { color: #111; text-decoration: none; border-bottom: 1px solid currentColor; padding-bottom: 2px; transition: 0.3s; }
#editorial-paper-wrapper .link-editorial.text-muted { color: #888; border-color: transparent; }
#editorial-paper-wrapper .link-editorial.text-muted:hover { color: #111; border-color: #111; }
#editorial-paper-wrapper .link-editorial.text-dark:hover { color: #ff8002; border-color: #ff8002; }
#editorial-paper-wrapper .link-separator { color: #e5e5e5; }
#editorial-paper-wrapper .icon-link-editorial { color: #888; display: flex; align-items: center; margin-left: 10px; transition: 0.3s; }
#editorial-paper-wrapper .icon-link-editorial svg { width: 14px; height: 14px; }
#editorial-paper-wrapper .icon-link-editorial:hover { color: #111; transform: translateY(-2px); }

#editorial-paper-wrapper .empty-editorial { padding: 100px 0; text-align: center; }
#editorial-paper-wrapper .empty-editorial p { font-family: 'Playfair Display', serif; font-size: 24px; font-style: italic; color: #888; margin-bottom: 20px; }

@media (max-width: 900px) {
    .profile-grid { grid-template-columns: 1fr; gap: 40px; padding-top: 30px; }
    .profile-sidebar { position: static; margin-bottom: 20px; }
    .side-nav-transparent::before, .nav-tracker { display: none; }
    .side-nav-transparent { flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 10px; padding: 0; }
    .side-nav-transparent .nav-link, .side-nav-transparent .logout-link { padding: 10px 15px; background: #f8fafc; border-radius: 4px; }
    .side-nav-transparent .nav-divider { display: none; }

    #editorial-paper-wrapper .ledger-header-row { display: none; }
    #editorial-paper-wrapper .editorial-ledger-row { grid-template-columns: 1fr; gap: 15px; padding: 30px 10px; text-align: center; }
    #editorial-paper-wrapper .editorial-ledger-row:hover { padding-left: 10px; padding-right: 10px; }
    #editorial-paper-wrapper .col-amount { justify-content: center; }
    #editorial-paper-wrapper .col-actions { justify-content: center; margin-top: 15px; }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const nav = document.getElementById('luxury-side-nav');
        const tracker = document.getElementById('nav-tracker');
        const links = document.querySelectorAll('.side-nav-transparent .nav-link');
        const activeLink = document.querySelector('.side-nav-transparent .nav-link.active');

        function moveTracker(targetEl) {
            if (!targetEl || !nav || !tracker || window.innerWidth <= 900) return;
            const navRect = nav.getBoundingClientRect();
            const targetRect = targetEl.getBoundingClientRect();
            const offsetY = targetRect.top - navRect.top + (targetRect.height / 2) - 10;
            tracker.style.transform = `translateY(${offsetY}px)`;
            tracker.style.opacity = '1';
        }

        setTimeout(() => moveTracker(activeLink), 100);

        links.forEach(link => {
            link.addEventListener('mouseenter', () => moveTracker(link));
            link.addEventListener('mouseleave', () => moveTracker(activeLink));
        });

        window.addEventListener('resize', () => {
            if(window.innerWidth <= 900) { tracker.style.opacity = '0'; }
            else { moveTracker(activeLink); }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
