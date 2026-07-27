<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';

$user_id = $_SESSION['user_id'];

$notif_check_sql = "SELECT id FROM notifications WHERE user_id = '$user_id' AND is_read = 0 LIMIT 1";
$notif_res = mysqli_query($conn, $notif_check_sql);
$has_notifications = ($notif_res && mysqli_num_rows($notif_res) > 0);

$user_res = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_res);

$total_orders = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders WHERE user_id = '$user_id'"));
$in_transit = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders WHERE user_id = '$user_id' AND (status = 'Shipped' OR status = 'In Transit')"));

$latest_order_res = mysqli_query($conn, "
    SELECT o.*, p.name as p_name, p.image as p_img
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = '$user_id'
    ORDER BY o.created_at DESC LIMIT 1
");
$latest_order = mysqli_fetch_assoc($latest_order_res);
?>

<main class="profile-page">
    <div class="container profile-grid">

        <aside class="profile-sidebar">
            <nav class="side-nav-transparent" id="luxury-side-nav">
                <div class="nav-tracker" id="nav-tracker"></div>

                <a href="profile.php" class="nav-link active"><span>01</span> My Profile</a>
                <a href="my-orders.php" class="nav-link"><span>02</span> My Orders</a>
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
                        <h2 class="title-serif">Identity.</h2>
                        <p class="subtitle-mono">USER / <?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></p>
                    </div>
                </header>

                <div class="identity-section fade-up" style="--delay: 2">
                    <div class="avatar-editorial">
                        <?php
                            $user_icon = !empty($user['profile_icon']) ? $user['profile_icon'] : 'default-avatar.png';
                            $icon_path = (strpos($user_icon, 'user_') === 0) ? "assets/images/uploads/" : "assets/images/";
                        ?>
                        <img src="<?php echo $icon_path . $user_icon; ?>" alt="Profile">
                    </div>

                    <div class="identity-meta">
                        <h1 class="name-serif">
                            <?php echo htmlspecialchars($user['full_name']); ?>
                            <span class="verified-dot" title="Verified Account"></span>
                        </h1>
                        <p class="email-mono"><?php echo htmlspecialchars($user['email']); ?></p>

                        <div class="action-links">
                            <a href="edit-profile.php" class="link-editorial">Edit Profile</a>
                            <span class="link-separator">/</span>
                            <a href="account-settings.php" class="link-editorial text-muted">Security</a>
                        </div>
                    </div>
                </div>

                <div class="metrics-row fade-up" style="--delay: 3">
                    <div class="metric-item">
                        <div class="m-value"><?php echo str_pad($total_orders, 2, '0', STR_PAD_LEFT); ?></div>
                        <div class="m-label">ACQUISITIONS</div>
                    </div>
                    <div class="metric-item">
                        <div class="m-value">1.2<span class="m-unit">K</span></div>
                        <div class="m-label">REWARDS</div>
                    </div>
                    <div class="metric-item highlight">
                        <div class="m-value"><?php echo str_pad($in_transit, 2, '0', STR_PAD_LEFT); ?></div>
                        <div class="m-label">IN TRANSIT</div>
                    </div>
                </div>

                <div class="archive-section fade-up" style="--delay: 4">
                    <h3 class="section-title-sans">Personal Details</h3>
                    <div class="ledger-list">
                        <div class="ledger-row">
                            <span class="l-key">LEGAL NAME</span>
                            <span class="l-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                        <div class="ledger-row">
                            <span class="l-key">CONTACT</span>
                            <span class="l-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '—'; ?></span>
                        </div>
                        <div class="ledger-row">
                            <span class="l-key">REGION</span>
                            <span class="l-value"><?php echo !empty($user['location']) ? htmlspecialchars($user['location']) : 'Malaysia'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="recent-activity-section fade-up" style="--delay: 5">
                    <div class="section-header-flex">
                        <h3 class="section-title-sans">Latest Acquisition</h3>
                        <a href="my-orders.php" class="link-view-all">View All <span>→</span></a>
                    </div>

                    <?php if ($latest_order): ?>
                    <a href="order-details.php?id=<?php echo $latest_order['id']; ?>" class="polaroid-order-card">
                        <div class="po-image">
                            <img src="assets/images/<?php echo htmlspecialchars($latest_order['p_img']); ?>" alt="">
                        </div>
                        <div class="po-details">
                            <div class="po-meta">
                                <span class="po-id">REF / <?php echo str_pad($latest_order['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                <span class="po-date"><?php echo date('M d, Y', strtotime($latest_order['created_at'])); ?></span>
                            </div>
                            <h4 class="po-name"><?php echo htmlspecialchars($latest_order['p_name']); ?></h4>

                            <div class="po-bottom">
                                <span class="po-status <?php echo strtolower(str_replace(' ', '-', $latest_order['status'])); ?>">
                                    <?php echo strtoupper($latest_order['status']); ?>
                                </span>
                                <span class="po-price">MYR <?php echo number_format($latest_order['total_price'], 2); ?></span>
                            </div>
                        </div>
                    </a>
                    <?php else: ?>
                        <div class="empty-editorial">
                            <p>The archive contains no recent transactions.</p>
                            <a href="product.php" class="link-editorial">Explore the Catalog</a>
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

#editorial-paper-wrapper { color: #111; font-family: 'Inter', sans-serif; background: transparent; }
#editorial-paper-wrapper .editorial-header { margin-bottom: 60px; padding-top: 10px; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid #e5e5e5; padding-bottom: 20px; }
#editorial-paper-wrapper .title-serif { font-family: 'Playfair Display', serif; font-size: 32px; font-style: italic; color: #111; margin: 0; line-height: 1; }
#editorial-paper-wrapper .subtitle-mono { font-family: monospace; font-size: 10px; color: #888; margin: 0; letter-spacing: 2px; }

#editorial-paper-wrapper .identity-section { display: flex; align-items: center; gap: 50px; margin-bottom: 60px; }
#editorial-paper-wrapper .avatar-editorial { width: 120px; height: 120px; flex-shrink: 0; border-radius: 50%; overflow: hidden; position: relative; background: #eee; }
#editorial-paper-wrapper .avatar-editorial img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(100%) contrast(1.1); transition: 0.8s ease; }
#editorial-paper-wrapper .avatar-editorial:hover img { filter: grayscale(0%) contrast(1); transform: scale(1.05); }
#editorial-paper-wrapper .name-serif { font-family: 'Playfair Display', serif; font-size: 46px; font-weight: 400; color: #111; margin: 0 0 5px 0; display: flex; align-items: center; gap: 15px; letter-spacing: -1px; }
#editorial-paper-wrapper .verified-dot { width: 8px; height: 8px; background: #ff8002; border-radius: 50%; display: inline-block; transform: translateY(-8px); }
#editorial-paper-wrapper .email-mono { font-family: monospace; font-size: 12px; color: #888; margin: 0 0 25px 0; letter-spacing: 1px; }

#editorial-paper-wrapper .action-links { display: flex; align-items: center; gap: 12px; font-family: monospace; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
#editorial-paper-wrapper .link-editorial { color: #111; text-decoration: none; border-bottom: 1px solid #111; padding-bottom: 2px; transition: 0.3s; }
#editorial-paper-wrapper .link-editorial:hover { color: #ff8002; border-color: #ff8002; }
#editorial-paper-wrapper .link-editorial.text-muted { color: #888; border-color: transparent; }
#editorial-paper-wrapper .link-editorial.text-muted:hover { color: #111; border-color: #111; }
#editorial-paper-wrapper .link-separator { color: #e5e5e5; }

#editorial-paper-wrapper .metrics-row { display: grid; grid-template-columns: repeat(3, 1fr); margin-bottom: 70px; border-top: 1px solid #e5e5e5; border-bottom: 1px solid #e5e5e5; }
#editorial-paper-wrapper .metric-item { padding: 40px 20px; display: flex; flex-direction: column; justify-content: center; position: relative; transition: 0.4s; }
#editorial-paper-wrapper .metric-item:not(:last-child)::after { content: ''; position: absolute; right: 0; top: 0; height: 100%; width: 1px; background: #e5e5e5; }
#editorial-paper-wrapper .metric-item:hover { background: rgba(0,0,0,0.02); }
#editorial-paper-wrapper .m-value { font-size: 56px; font-weight: 300; letter-spacing: -2px; color: #111; line-height: 1; margin-bottom: 10px; }
#editorial-paper-wrapper .m-unit { font-size: 24px; color: #888; margin-left: 2px; }
#editorial-paper-wrapper .m-label { font-family: monospace; font-size: 10px; font-weight: 700; color: #888; letter-spacing: 2px; }
#editorial-paper-wrapper .metric-item.highlight .m-value { color: #ff8002; }

#editorial-paper-wrapper .archive-section { margin-bottom: 70px; }
#editorial-paper-wrapper .section-title-sans { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin: 0 0 30px 0; color: #111; }
#editorial-paper-wrapper .ledger-list { display: flex; flex-direction: column; }
#editorial-paper-wrapper .ledger-row { display: flex; justify-content: space-between; padding: 20px 0; border-bottom: 1px solid #e5e5e5; transition: 0.3s; }
#editorial-paper-wrapper .ledger-row:hover { padding-left: 10px; padding-right: 10px; background: rgba(0,0,0,0.01); }
#editorial-paper-wrapper .l-key { font-family: monospace; font-size: 11px; color: #888; letter-spacing: 1px; }
#editorial-paper-wrapper .l-value { font-size: 14px; font-weight: 500; color: #111; }

#editorial-paper-wrapper .recent-activity-section { margin-bottom: 60px; }
#editorial-paper-wrapper .section-header-flex { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 30px; }
#editorial-paper-wrapper .link-view-all { font-family: monospace; font-size: 11px; color: #888; text-decoration: none; letter-spacing: 1px; text-transform: uppercase; transition: 0.3s; }
#editorial-paper-wrapper .link-view-all span { transition: 0.3s; display: inline-block; }
#editorial-paper-wrapper .link-view-all:hover { color: #111; }
#editorial-paper-wrapper .link-view-all:hover span { transform: translateX(5px); color: #ff8002; }

#editorial-paper-wrapper .polaroid-order-card { display: flex; gap: 40px; text-decoration: none; color: inherit; padding: 20px; transition: all 0.5s ease; border: 1px solid transparent; border-radius: 4px; }
#editorial-paper-wrapper .polaroid-order-card:hover { background: #fff; border-color: #e5e5e5; box-shadow: 0 20px 40px rgba(0,0,0,0.03); transform: translateY(-3px); }
#editorial-paper-wrapper .po-image { width: 120px; aspect-ratio: 3/4; overflow: hidden; background: #eee; flex-shrink: 0; border-radius: 2px;}
#editorial-paper-wrapper .po-image img { width: 100%; height: 100%; object-fit: cover; transition: 0.8s ease; filter: contrast(1.05); }
#editorial-paper-wrapper .polaroid-order-card:hover .po-image img { transform: scale(1.05); }
#editorial-paper-wrapper .po-details { flex-grow: 1; display: flex; flex-direction: column; justify-content: center; }
#editorial-paper-wrapper .po-meta { display: flex; justify-content: space-between; font-family: monospace; font-size: 10px; color: #888; margin-bottom: 15px; letter-spacing: 1px; }
#editorial-paper-wrapper .po-name { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 400; font-style: italic; color: #111; margin: 0 0 20px 0; }
#editorial-paper-wrapper .po-bottom { display: flex; justify-content: space-between; align-items: baseline; margin-top: auto; }
#editorial-paper-wrapper .po-price { font-family: monospace; font-size: 16px; font-weight: 600; color: #111; }
#editorial-paper-wrapper .po-status { font-family: monospace; font-size: 9px; font-weight: 700; letter-spacing: 2px; padding: 4px 10px; border: 1px solid currentColor; border-radius: 2px; }
#editorial-paper-wrapper .po-status.pending { color: #854d0e; }
#editorial-paper-wrapper .po-status.shipped, #editorial-paper-wrapper .po-status.in-transit { color: #ff8002; }
#editorial-paper-wrapper .po-status.delivered { color: #111; }
#editorial-paper-wrapper .po-status.cancelled { color: #ef4444; border: none; text-decoration: line-through; }

#editorial-paper-wrapper .empty-editorial { padding: 60px 0; text-align: center; border-top: 1px solid #e5e5e5; }
#editorial-paper-wrapper .empty-editorial p { font-family: 'Playfair Display', serif; font-size: 20px; font-style: italic; color: #888; margin-bottom: 20px; }

@media (max-width: 900px) {
    .profile-grid { grid-template-columns: 1fr; gap: 40px; padding-top: 30px; }
    .profile-sidebar { position: static; margin-bottom: 20px; }
    .side-nav-transparent::before, .nav-tracker { display: none; }
    .side-nav-transparent { flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 10px; padding: 0; }
    .side-nav-transparent .nav-link, .side-nav-transparent .logout-link { padding: 10px 15px; background: #f8fafc; border-radius: 4px; }
    .side-nav-transparent .nav-divider { display: none; }

    #editorial-paper-wrapper .identity-section { flex-direction: column; text-align: center; gap: 30px; }
    #editorial-paper-wrapper .action-links { justify-content: center; }
    #editorial-paper-wrapper .metrics-row { grid-template-columns: 1fr; border-left: 1px solid #e5e5e5; border-right: 1px solid #e5e5e5; }
    #editorial-paper-wrapper .metric-item:not(:last-child)::after { width: 100%; height: 1px; top: auto; bottom: 0; right: auto; left: 0; }
    #editorial-paper-wrapper .polaroid-order-card { flex-direction: column; gap: 20px; padding: 0; background: transparent !important; box-shadow: none !important; border-color: transparent !important; transform: none !important;}
    #editorial-paper-wrapper .po-image { width: 100%; aspect-ratio: 16/9; }
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
