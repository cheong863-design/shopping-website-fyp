<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$order_query = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND user_id = '$user_id'");
$order = mysqli_fetch_assoc($order_query);

if (!$order) { header("Location: my-orders.php"); exit(); }

if (empty($order['address_line'])) {
    $addr_query = mysqli_query($conn, "SELECT * FROM user_addresses WHERE user_id = '$user_id' AND is_default = 1 LIMIT 1");
    $default_addr = mysqli_fetch_assoc($addr_query);
    if ($default_addr) {
        $display_name = $default_addr['receiver_name'];
        $display_address = $default_addr['address_line'];
        $display_phone = $default_addr['phone'];
    } else {
        $user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
        $user_data = mysqli_fetch_assoc($user_query);
        $display_name = $user_data['full_name'];
        $display_address = "NO COORDINATES RECORDED IN SYSTEM.";
        $display_phone = $user_data['phone'] ?? 'N/A';
    }
} else {
    $display_name = $order['full_name'];
    $display_address = $order['address_line'];
    $display_phone = $order['phone'];
}

$items_query = mysqli_query($conn, "
    SELECT oi.*, p.name as p_name, p.image as p_img
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");

$items_array = [];
$real_subtotal = 0;

if ($items_query && mysqli_num_rows($items_query) > 0) {
    while($item = mysqli_fetch_assoc($items_query)) {
        $items_array[] = $item;
        $real_subtotal += ((float)$item['price'] * (int)$item['quantity']);
    }
}

$total_price    = (float)($order['total_price'] ?? 0.00);
$shipping_fee   = (float)($order['shipping_fee'] ?? 0.00);
$subtotal       = $real_subtotal;

$calc_tax       = $total_price - $subtotal - $shipping_fee;
$tax_amount     = $calc_tax > 0 ? $calc_tax : (float)($order['tax_amount'] ?? 0.00);

// ==========================================
// ==========================================
$notif_res = mysqli_query($conn, "SELECT id FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
$has_notifications = ($notif_res && mysqli_num_rows($notif_res) > 0);
// ==========================================

include 'includes/header.php';
?>

<main class="profile-page">
    <div class="container profile-grid">

        <aside class="profile-sidebar focus-in" style="--delay: 0">
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
            <div class="dossier-document focus-in" style="--delay: 1">

                <div class="dossier-top-bar">
                    <a href="my-orders.php" class="btn-return-stealth">← BACK TO ARCHIVES</a>
                    <div class="barcode-fake">|||| | ||| || ||| | || |||| | |||</div>
                </div>

                <header class="dossier-header stagger-in" style="--stagger: 2">
                    <div class="dh-meta">
                        <span class="meta-tag">MANIFEST IDENTIFIER</span>
                        <div class="macro-ref js-decrypt" data-ref="F-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>">
                            F-000000
                        </div>
                        <span class="mono-date">DATE ISSUED: <?php echo date('d.m.Y // H:i', strtotime($order['created_at'])); ?></span>

                        <?php if($order['status'] === 'Shipped' && !empty($order['estimated_delivery'])): ?>
                            <span class="mono-eta">EST. DROP: <?php echo date('M d, Y', strtotime($order['estimated_delivery'])); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="dh-actions">
                        <a href="invoice.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn-print-brutal">
                            [ GENERATE PDF ]
                        </a>
                        <div class="status-macro status-<?php echo strtolower($order['status'] ?? 'pending'); ?>">
                            <span class="status-dot"></span> <?php echo strtoupper($order['status'] ?? 'PENDING'); ?>
                        </div>
                    </div>
                </header>

                <div class="dossier-items stagger-in" style="--stagger: 3">
                    <div class="item-th">
                        <span>ARTIFACT</span>
                        <span style="text-align: center;">QTY</span>
                        <span style="text-align: right;">VAL</span>
                    </div>

                    <div class="items-ledger">
                        <?php
                        $idx = 1;
                        foreach($items_array as $item):
                        ?>
                        <div class="ledger-tr">
                            <div class="tr-main">
                                <span class="tr-idx">0<?php echo $idx; ?></span>
                                <div class="tr-visual shutter-reveal" style="--s-delay: <?php echo $idx; ?>">
                                    <img src="assets/images/<?php echo $item['p_img']; ?>" alt="Artifact" onerror="this.src='https://placehold.co/80x100/ebebeb/111?text=X'">
                                    <div class="shutter-mask"></div>
                                </div>
                                <h4 class="tr-name"><?php echo htmlspecialchars($item['p_name']); ?></h4>
                            </div>
                            <div class="tr-qty">x<?php echo str_pad($item['quantity'], 2, '0', STR_PAD_LEFT); ?></div>
                            <div class="tr-price">MYR <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                        </div>
                        <?php
                            $idx++;
                        endforeach;
                        ?>
                    </div>
                </div>

                <div class="dossier-split-grid">
                    <div class="split-left stagger-in" style="--stagger: 4">
                        <h3 class="section-title">DISPATCH COORDINATES</h3>
                        <div class="info-block">
                            <p class="ib-name"><?php echo htmlspecialchars($display_name); ?></p>
                            <p class="ib-desc"><?php echo nl2br(htmlspecialchars($display_address)); ?></p>
                            <p class="ib-desc" style="margin-top:15px; color:#111;">COMMS: <?php echo htmlspecialchars($display_phone); ?></p>
                        </div>
                    </div>

                    <div class="split-right stagger-in" style="--stagger: 5">
                        <h3 class="section-title" style="text-align: right;">FINANCIAL LEDGER</h3>
                        <div class="fin-ledger">
                            <div class="fin-row">
                                <span>SUBTOTAL</span>
                                <span><?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="fin-row">
                                <span>DISPATCH</span>
                                <span><?php echo number_format($shipping_fee, 2); ?></span>
                            </div>
                            <div class="fin-row">
                                <span>TAX (SST/GST)</span>
                                <span><?php echo number_format($tax_amount, 2); ?></span>
                            </div>
                            <div class="fin-row total-row">
                                <span>NET AUTHORIZED</span>
                                <span class="total-val">MYR <?php echo number_format($total_price, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ghost-stamp">SECURED</div>
            </div>
        </section>

    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

<style>

.profile-page { background: #f8fafc; padding: 40px 0 100px 0; min-height: 80vh; font-family: 'Inter', sans-serif; color: #111; }
.profile-grid { display: grid; grid-template-columns: 260px 1fr; gap: 40px; align-items: start; }

.focus-in { opacity: 0; transform: translateY(20px) scale(0.98); filter: blur(8px); animation: opticalFocus 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--delay) * 0.15s); }
.stagger-in { opacity: 0; transform: translateY(15px); animation: runwayUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--stagger) * 0.1s); }
@keyframes opticalFocus { to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); } }
@keyframes runwayUp { to { opacity: 1; transform: translateY(0); } }

.profile-sidebar { position: sticky; top: 100px; height: fit-content; z-index: 10; }
.side-nav-transparent { position: relative; display: flex; flex-direction: column; padding: 20px 0; background: transparent; }
.side-nav-transparent::before { content: ''; position: absolute; left: 15px; top: 20px; bottom: 20px; width: 1px; background: rgba(0,0,0,0.1); z-index: 0; }

.nav-tracker {
    position: absolute; left: 14px; width: 3px; height: 20px; background: #ff8002; z-index: 2;
    transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), height 0.5s cubic-bezier(0.25, 1, 0.5, 1);
    opacity: 0; border-radius: 2px;
}

.side-nav-transparent .nav-link {
    position: relative; z-index: 1; padding: 15px 35px; display: flex; align-items: center;
    text-decoration: none; color: #888; font-size: 13px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 1px; transition: color 0.4s ease;
}
.side-nav-transparent .nav-link span { font-family: monospace; font-size: 10px; margin-right: 15px; opacity: 0.4; transition: 0.4s; }
.side-nav-transparent .nav-link.active { color: #111; font-weight: 800; }
.side-nav-transparent .nav-link.active span { opacity: 1; color: #ff8002; }
.side-nav-transparent .nav-link:hover { color: #111; }
.side-nav-transparent .nav-divider { border: none; border-top: 1px solid rgba(0,0,0,0.1); margin: 20px 35px; }
.side-nav-transparent .logout-link { display: block; padding: 10px 35px; color: #ef4444; font-size: 13px; font-weight: 600; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
.side-nav-transparent .logout-link:hover { opacity: 0.5; }

.notif-ping { width: 6px; height: 6px; background: #ff8002; border-radius: 50%; margin-left: 10px; position: relative; }
.notif-ping::after { content: ''; position: absolute; inset: 0; border-radius: 50%; border: 1px solid #ff8002; animation: pingOut 2s cubic-bezier(0, 0, 0.2, 1) infinite; }
@keyframes pingOut { 75%, 100% { transform: scale(3); opacity: 0; } }

.dossier-document {
    background-color: #ffffff;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.03'/%3E%3C/svg%3E");
    border: 1px solid #e5e5e5;
    border-radius: 0;
    box-shadow: 0 20px 40px rgba(0,0,0,0.02), 0 1px 3px rgba(0,0,0,0.01);
    position: relative;
    overflow: hidden;
}

.dossier-top-bar { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background: #fafafa; border-bottom: 1px dashed #111; }
.btn-return-stealth { font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 2px; color: #888; text-decoration: none; transition: 0.3s; }
.btn-return-stealth:hover { color: #111; transform: translateX(-3px); }
.barcode-fake { font-family: 'Courier New', Courier, monospace; font-size: 20px; color: #111; letter-spacing: -2px; transform: scaleY(1.2); opacity: 0.6; }

.dossier-header { padding: 50px 40px; border-bottom: 1px dashed #111; display: flex; justify-content: space-between; align-items: flex-end; }
.dh-meta { display: flex; flex-direction: column; gap: 8px; }
.meta-tag { font-family: monospace; font-size: 9px; font-weight: 800; color: #ff8002; letter-spacing: 4px; }
.macro-ref { font-family: 'Inter', sans-serif; font-size: clamp(32px, 4vw, 52px); font-weight: 800; color: #111; line-height: 1; letter-spacing: -2px; min-width: 220px; display: inline-block; margin-left: -2px;}
.mono-date { font-family: monospace; font-size: 10px; font-weight: 700; color: #888; letter-spacing: 2px; margin-top: 5px; }

.mono-eta { font-family: monospace; font-size: 10px; font-weight: 800; color: #10b981; letter-spacing: 2px; margin-top: 8px; border-left: 2px solid #10b981; padding-left: 8px;}

.dh-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 20px; }
.btn-print-brutal { font-family: monospace; font-size: 10px; font-weight: 800; color: #111; letter-spacing: 2px; text-decoration: none; transition: 0.3s; }
.btn-print-brutal:hover { color: #ff8002; }

.status-macro { display: inline-flex; align-items: center; gap: 10px; font-family: monospace; font-size: 12px; font-weight: 800; letter-spacing: 3px; border: 1px solid currentColor; padding: 10px 15px; }
.status-dot { width: 6px; height: 6px; border-radius: 50%; animation: pulseDot 2s infinite; }
.status-pending { color: #888; } .status-pending .status-dot { background: #888; box-shadow: 0 0 8px #888; }
.status-paid { color: #111; } .status-paid .status-dot { background: #111; box-shadow: 0 0 8px #111; }
.status-shipped { color: #ff8002; } .status-shipped .status-dot { background: #ff8002; box-shadow: 0 0 8px #ff8002; }

.dossier-items { padding: 40px; border-bottom: 1px dashed #111; }
.item-th { display: grid; grid-template-columns: 1fr 80px 100px; padding-bottom: 15px; border-bottom: 1px solid #111; font-family: monospace; font-size: 9px; font-weight: 800; color: #888; letter-spacing: 2px; margin-bottom: 10px; }

.ledger-tr { display: grid; grid-template-columns: 1fr 80px 100px; align-items: center; padding: 20px 0; border-bottom: 1px dashed #e5e5e5; transition: background 0.3s; }
.ledger-tr:last-child { border-bottom: none; padding-bottom: 0; }
.tr-main { display: flex; align-items: center; gap: 25px; }
.tr-idx { font-family: monospace; font-size: 9px; color: #ccc; font-weight: 700; }

.tr-visual { width: 50px; aspect-ratio: 3/4; background: #fafafa; position: relative; overflow: hidden; mix-blend-mode: multiply; }
.tr-visual img { width: 100%; height: 100%; object-fit: contain; filter: grayscale(100%) contrast(1.2); transition: 0.5s ease; }
.ledger-tr:hover .tr-visual img { filter: grayscale(0%) contrast(1); transform: scale(1.05); }

.shutter-mask { position: absolute; inset: 0; background: #111; transform-origin: right; animation: shutterSlide 0.8s cubic-bezier(0.77, 0, 0.175, 1) forwards; animation-delay: calc(0.6s + (var(--s-delay) * 0.15s)); }
@keyframes shutterSlide { 0% { transform: scaleX(1); } 100% { transform: scaleX(0); } }

.tr-name { font-family: 'Playfair Display', serif; font-size: 18px; font-style: italic; font-weight: 700; margin: 0; color: #111; }
.tr-qty { font-family: monospace; font-size: 11px; font-weight: 700; color: #888; text-align: center; }
.tr-price { font-family: monospace; font-size: 13px; font-weight: 800; color: #111; text-align: right; }

.dossier-split-grid { display: grid; grid-template-columns: 1fr 1fr; position: relative; z-index: 2; }
.section-title { font-family: monospace; font-size: 10px; font-weight: 800; color: #888; letter-spacing: 3px; margin: 0 0 25px 0; border-bottom: 1px solid #111; padding-bottom: 8px; width: fit-content; }

.split-left { padding: 40px 50px; border-right: 1px dashed #111; }
.ib-name { font-family: 'Playfair Display', serif; font-size: 20px; font-style: italic; font-weight: 700; margin: 0 0 10px 0; color: #111; }
.ib-desc { font-family: monospace; font-size: 10px; color: #888; line-height: 1.8; letter-spacing: 1px; margin: 0; text-transform: uppercase; }

.split-right { padding: 40px; display: flex; flex-direction: column; align-items: flex-end; }
.fin-ledger { width: 100%; max-width: 300px; display: flex; flex-direction: column; gap: 12px; }
.fin-row { display: flex; justify-content: space-between; font-family: monospace; font-size: 10px; font-weight: 700; color: #888; letter-spacing: 1px; }
.total-row { margin-top: 15px; padding-top: 20px; border-top: 1px dashed #111; color: #111; align-items: baseline; }
.total-val { font-family: 'Playfair Display', serif; font-size: 26px; font-style: italic; font-weight: 700; }

.ghost-stamp {
    position: absolute; bottom: 80px; left: 50px;
    font-family: monospace; font-size: 50px; font-weight: 800; letter-spacing: 10px;
    color: transparent; border: 4px solid rgba(17, 17, 17, 0.03);
    padding: 10px 20px; transform: rotate(-15deg); pointer-events: none; z-index: 1;
    background-image: repeating-linear-gradient(45deg, rgba(17,17,17,0.03) 0, rgba(17,17,17,0.03) 2px, transparent 2px, transparent 6px);
}

@media (max-width: 1024px) {
    .profile-grid { grid-template-columns: 1fr; }
    .side-nav-transparent { flex-direction: row; flex-wrap: wrap; padding: 0; }
    .side-nav-transparent::before, .nav-tracker { display: none; }
    .side-nav-transparent .nav-link { flex-grow: 1; justify-content: center; padding: 15px; border-bottom: 1px solid #e5e5e5; }
    .side-nav-transparent .nav-divider { display: none; }
    .side-nav-transparent .logout-link { display: none; }
}

@media (max-width: 768px) {
    .dossier-header { flex-direction: column; align-items: flex-start; gap: 30px; }
    .dh-actions { align-items: flex-start; width: 100%; flex-direction: row-reverse; justify-content: space-between; }
    .macro-ref { font-size: 36px; min-width: auto; }
    .dossier-header, .dossier-items, .split-left, .split-right { padding: 30px 20px; }
    .item-th { display: none; }
    .ledger-tr { grid-template-columns: 1fr; gap: 15px; }
    .tr-main { gap: 15px; }
    .tr-idx { display: none; }
    .tr-qty, .tr-price { text-align: left; padding-left: 65px; }
    .tr-price { font-size: 16px; color: #111; }
    .dossier-split-grid { grid-template-columns: 1fr; }
    .split-left { border-right: none; border-bottom: 1px dashed #111; }
    .split-right { align-items: flex-start; }
    .split-right .section-title { margin-left: 0; }
    .fin-ledger { max-width: 100%; }
    .ghost-stamp { font-size: 30px; bottom: 150px; left: 20px; }
}
</style>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const nav = document.getElementById('luxury-side-nav');
        const tracker = document.getElementById('nav-tracker');
        const links = document.querySelectorAll('.side-nav-transparent .nav-link');
        const activeLink = document.querySelector('.side-nav-transparent .nav-link.active');

        function moveTracker(targetEl) {
            if (!targetEl || !nav || !tracker || window.innerWidth <= 1024) return;
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
        window.addEventListener('resize', () => moveTracker(activeLink));

        const decryptElement = document.querySelector('.js-decrypt');
        if (decryptElement) {
            const finalValue = decryptElement.getAttribute('data-ref');
            const chars = '0123456789XXYYZZ';
            let iterations = 0;
            const maxIterations = 20;

            setTimeout(() => {
                const interval = setInterval(() => {
                    decryptElement.innerText = finalValue.split('').map((char, index) => {
                        if (char === '-' || char === 'F') return char;
                        if (index < iterations / 2) return finalValue[index];
                        return chars[Math.floor(Math.random() * chars.length)];
                    }).join('');
                    iterations++;
                    if (iterations >= maxIterations * 2) {
                        clearInterval(interval);
                        decryptElement.innerText = finalValue;
                    }
                }, 30);
            }, 500);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
