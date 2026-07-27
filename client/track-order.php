<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

$order_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($order_id)) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_delivered'])) {
        mysqli_query($conn, "UPDATE orders SET status = 'Delivered', delivery_request = 0 WHERE id = '$order_id'");
        header("Location: track-order.php?id=$order_id&msg=thanks");
        exit();
    } elseif (isset($_POST['reject_delivered'])) {
        mysqli_query($conn, "UPDATE orders SET delivery_request = 2 WHERE id = '$order_id'");
        header("Location: track-order.php?id=$order_id&msg=notified");
        exit();
    }
}

$order_res = mysqli_query($conn, "SELECT o.*, u.location FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = '$order_id'");
$order = mysqli_fetch_assoc($order_res);

$items_query = mysqli_query($conn, "
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = '$order_id'
");

$notif_check_sql = "
    SELECT id FROM notifications WHERE user_id = '$user_id' AND is_read = 0
    UNION ALL
    SELECT id FROM orders WHERE user_id = '$user_id' AND status = 'Shipped' AND delivery_request IN (1, 2)
";
$notif_res = mysqli_query($conn, $notif_check_sql);
$has_notifications = ($notif_res && mysqli_num_rows($notif_res) > 0);
// --------------------------------------------------

include 'includes/header.php';

if (!$order) {
    echo "<div class='container' style='padding:100px 20px; text-align:center; font-family:monospace;'><h2>[ERROR 404] ARCHIVE NOT FOUND.</h2><a href='index.php'>RETURN TO BASE</a></div>";
    include 'includes/footer.php';
    exit();
}
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
            <div id="editorial-paper-wrapper" class="content-matrix focus-in" style="--delay: 1">

                <header class="detail-header stagger-in" style="--stagger: 2">
                    <div class="dh-top">
                        <a href="my-orders.php" class="link-return">
                            <span class="arrow">←</span> ABORT TRACKING
                        </a>
                        <div class="header-actions-group">
                            <a href="invoice.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn-print-ghost">VIEW DOSSIER</a>
                            <?php
                            $whatsapp_number = "60146382694";
                            $whatsapp_msg = urlencode("PROTOCOL INQUIRY: Order #FAIFA-" . $order['id']);
                            ?>
                            <a href="https://wa.me/<?php echo $whatsapp_number; ?>?text=<?php echo $whatsapp_msg; ?>" target="_blank" class="btn-comms-ghost">
                                INITIATE COMMS ↗
                            </a>
                        </div>
                    </div>

                    <div class="dh-main">
                        <div class="dh-meta">
                            <span class="meta-tag">RADAR LOCK // ACTIVE</span>
                            <span class="macro-ref js-decrypt" data-ref="F-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>">F-000000</span>
                        </div>

                        <div class="status-macro status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                            <span class="status-dot"></span> [ <?php echo strtoupper(htmlspecialchars($order['status'])); ?> ]
                        </div>
                    </div>
                </header>

                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'thanks'): ?>
                    <div class="terminal-alert success stagger-in" style="--stagger: 3">
                        <span class="pulse-box"></span> OVERRIDE ACCEPTED. TARGET ACQUIRED AND DELIVERED.
                    </div>
                <?php endif; ?>
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'notified'): ?>
                    <div class="terminal-alert warning stagger-in" style="--stagger: 3">
                        <span class="pulse-box"></span> HQ NOTIFIED. RADAR SWEEP INITIATED FOR MISSING ASSET.
                    </div>
                <?php endif; ?>

                <div class="tracker-split-grid">

                    <div class="split-left stagger-in" style="--stagger: 4">
                        <h3 class="section-title">RADAR TELEMETRY</h3>

                        <div class="brutal-timeline">
                            <div class="timeline-line"></div>
                            <div class="timeline-progress" style="height: <?php
                                if($order['status'] == 'Pending' || $order['status'] == 'Processing') echo '20%';
                                elseif($order['status'] == 'Packaging') echo '50%';
                                elseif($order['status'] == 'Shipped' || $order['status'] == 'In Transit') echo '80%';
                                elseif($order['status'] == 'Delivered') echo '100%';
                                else echo '0%';
                            ?>;">
                                <div class="laser-head"></div>
                            </div>

                            <div class="bt-step completed">
                                <div class="bt-node">[01]</div>
                                <div class="bt-info">
                                    <h4>SYSTEM ENGAGED</h4><p>Processing initiated.</p>
                                </div>
                            </div>

                            <div class="bt-step <?php echo in_array($order['status'], ['Packaging', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                <div class="bt-node">[02]</div>
                                <div class="bt-info">
                                    <h4>ARCHIVE SEALED</h4><p>Packaging completed.</p>
                                </div>
                            </div>

                            <div class="bt-step <?php echo in_array($order['status'], ['Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                <div class="bt-node">[03]</div>
                                <div class="bt-info">
                                    <h4>ASSET IN TRANSIT</h4><p>Handed over to courier.</p>
                                </div>
                            </div>

                            <div class="bt-step <?php echo ($order['status'] == 'Delivered') ? 'completed' : ''; ?>">
                                <div class="bt-node">[04]</div>
                                <div class="bt-info">
                                    <h4>TARGET SECURED</h4><p>Delivery verified.</p>
                                </div>
                            </div>
                        </div>

                        <?php if($order['status'] == 'Shipped' && isset($order['delivery_request']) && $order['delivery_request'] == 1): ?>
                            <div class="override-terminal stagger-in" style="--stagger: 5">
                                <div class="ot-header">
                                    <span class="blinking-cursor">_</span> HQ AUTHORIZATION REQUIRED
                                </div>
                                <p class="ot-desc">PLEASE CONFIRM PHYSICAL POSSESSION OF THE ASSET TO TERMINATE TRACKING PROTOCOL.</p>
                                <form method="POST" class="ot-actions">
                                    <button type="submit" name="confirm_delivered" class="btn-override-confirm">
                                        [ CONFIRM RECEIPT ]
                                    </button>
                                    <button type="submit" name="reject_delivered" class="btn-override-deny">
                                        NEGATIVE
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="split-right stagger-in" style="--stagger: 5">

                        <div class="coord-block">
                            <h3 class="section-title">TARGET COORDINATES</h3>
                            <p class="ib-name"><?php echo !empty($order['location']) ? htmlspecialchars($order['location']) : 'SYSTEM DEFAULT'; ?></p>
                            <p class="ib-desc" style="color: #111; margin-top: 10px;">EST. ARRIVAL: <?php echo date('M d, Y', strtotime($order['created_at'] . ' + 4 days')); ?></p>
                        </div>

                        <div class="mini-ledger">
                            <h3 class="section-title">PAYLOAD</h3>
                            <div class="mini-items">
                                <?php
                                mysqli_data_seek($items_query, 0);
                                while($item = mysqli_fetch_assoc($items_query)):
                                ?>
                                <div class="ml-item">
                                    <span class="ml-qty">x<?php echo $item['quantity']; ?></span>
                                    <span class="ml-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="ml-total">
                                <span>TOTAL AUTHORIZED</span>
                                <span>MYR <?php echo number_format($order['total_price'], 2); ?></span>
                            </div>
                        </div>

                        <div class="ghost-watermark">TRACKING</div>

                    </div>

                </div>

            </div>
        </section>
    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 PROFILE FRAMEWORK (UNTOUCHED OUTER SHELL)
   ============================================== */
.profile-page { background: #f8fafc; padding: 40px 0 100px 0; min-height: 80vh; font-family: 'Inter', sans-serif; color: #111; }
.profile-grid { display: grid; grid-template-columns: 260px 1fr; gap: 40px; align-items: start; }

.profile-sidebar { position: sticky; top: 100px; height: fit-content; z-index: 10; }
.side-nav-transparent { position: relative; display: flex; flex-direction: column; background: transparent; padding: 20px 0;}
.side-nav-transparent::before { content: ''; position: absolute; left: 15px; top: 10px; bottom: 10px; width: 1px; background: rgba(0,0,0,0.1); z-index: 0; }
.nav-tracker { position: absolute; left: 14px; width: 3px; height: 20px; background: #ff8002; z-index: 2; transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), height 0.5s cubic-bezier(0.25, 1, 0.5, 1); opacity: 0; border-radius: 2px; }
.side-nav-transparent .nav-link { position: relative; z-index: 1; padding: 15px 35px; display: flex; align-items: center; text-decoration: none; color: #888; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: color 0.4s ease; font-family: 'Inter', sans-serif; }
.side-nav-transparent .nav-link span { font-family: monospace; font-size: 10px; margin-right: 15px; opacity: 0.4; transition: 0.4s; }
.side-nav-transparent .nav-link.active { color: #111; font-weight: 800; }
.side-nav-transparent .nav-link.active span { opacity: 1; color: #ff8002; }
.side-nav-transparent .nav-link:hover { color: #111; }
.side-nav-transparent .nav-divider { border: none; border-top: 1px solid rgba(0,0,0,0.1); margin: 15px 35px; }
.side-nav-transparent .logout-link { display: block; padding: 10px 35px; color: #ef4444; font-size: 13px; font-weight: 600; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; font-family: 'Inter', sans-serif;}
.side-nav-transparent .logout-link:hover { opacity: 0.5; }

.notif-ping { width: 6px; height: 6px; background: #ff8002; border-radius: 50%; margin-left: 10px; position: relative; }
.notif-ping::after { content: ''; position: absolute; inset: 0; border-radius: 50%; border: 1px solid #ff8002; animation: pingOut 2s cubic-bezier(0, 0, 0.2, 1) infinite; }
@keyframes pingOut { 75%, 100% { transform: scale(3); opacity: 0; } }

.focus-in { opacity: 0; transform: translateY(20px) scale(0.98); filter: blur(8px); animation: opticalFocus 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--delay) * 0.15s); }
.stagger-in { opacity: 0; transform: translateY(15px); animation: runwayUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--stagger) * 0.1s); }
@keyframes opticalFocus { to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); } }
@keyframes runwayUp { to { opacity: 1; transform: translateY(0); } }

/* ==============================================
   🔥 THE TRACKING TERMINAL (INNER BRUTALISM)
   ============================================== */
#editorial-paper-wrapper {
    background-color: #ffffff; border: 1px dashed #111; border-radius: 0;
    box-shadow: 0 20px 40px rgba(0,0,0,0.02); position: relative; overflow: hidden;
}

.detail-header { padding: 40px 50px; border-bottom: 1px dashed #111; }
.dh-top { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 30px; }
.link-return { font-family: monospace; font-size: 10px; font-weight: 700; letter-spacing: 2px; color: #888; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
.link-return .arrow { transition: transform 0.3s; }
.link-return:hover { color: #111; }
.link-return:hover .arrow { transform: translateX(-5px); color: #ff8002; }

.header-actions-group { display: flex; gap: 15px; }
.btn-print-ghost, .btn-comms-ghost { font-family: monospace; font-size: 9px; font-weight: 800; letter-spacing: 2px; color: #111; text-decoration: none; border: 1px solid #e5e5e5; padding: 10px 15px; transition: all 0.3s; }
.btn-comms-ghost { border-color: #111; }
.btn-print-ghost:hover { background: #111; color: #fff; border-color: #111; }
.btn-comms-ghost:hover { background: #ff8002; color: #111; border-color: #ff8002; }

.dh-main { display: flex; justify-content: space-between; align-items: flex-end; }
.dh-meta { display: flex; flex-direction: column; gap: 8px; }
.meta-tag { font-family: monospace; font-size: 9px; font-weight: 800; color: #ff8002; letter-spacing: 4px; }
.macro-ref { font-family: 'Inter', sans-serif; font-size: clamp(32px, 4vw, 52px); font-weight: 800; color: #111; line-height: 1; letter-spacing: -2px; min-width: 220px; display: inline-block; margin-left: -2px;}

.status-macro { display: inline-flex; align-items: center; gap: 10px; font-family: monospace; font-size: 12px; font-weight: 800; letter-spacing: 3px; padding-bottom: 5px; border-bottom: 2px solid currentColor;}
.status-dot { width: 6px; height: 6px; border-radius: 50%; animation: pulseDot 2s infinite; }
.status-pending { color: #888; } .status-pending .status-dot { background: #888; box-shadow: 0 0 8px #888; }
.status-packaging { color: #111; } .status-packaging .status-dot { background: #111; box-shadow: 0 0 8px #111; }
.status-shipped, .status-in-transit { color: #ff8002; } .status-shipped .status-dot, .status-in-transit .status-dot { background: #ff8002; box-shadow: 0 0 8px #ff8002; }
.status-delivered { color: #10b981; } .status-delivered .status-dot { background: #10b981; box-shadow: 0 0 8px #10b981; }

.terminal-alert { padding: 15px 40px; font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 2px; border-bottom: 1px dashed #111; display: flex; align-items: center; gap: 15px; }
.pulse-box { width: 8px; height: 8px; background: currentColor; animation: blink 1s step-end infinite; }
.terminal-alert.success { background: #fafafa; color: #10b981; }
.terminal-alert.warning { background: #111; color: #ff8002; }
@keyframes blink { 50% { opacity: 0; } }

.tracker-split-grid { display: grid; grid-template-columns: 1.5fr 1fr; position: relative; z-index: 2; }
.section-title { font-family: monospace; font-size: 10px; font-weight: 800; color: #888; letter-spacing: 3px; margin: 0 0 40px 0; border-bottom: 1px solid #111; padding-bottom: 8px; width: fit-content; text-transform: uppercase;}

.split-left { padding: 50px; border-right: 1px dashed #111; position: relative; }

.brutal-timeline { position: relative; padding-left: 40px; display: flex; flex-direction: column; gap: 40px; }
.timeline-line { position: absolute; left: 6px; top: 10px; bottom: 10px; width: 1px; background: #e5e5e5; z-index: 1; }

.timeline-progress { position: absolute; left: 6px; top: 10px; width: 1px; background: #111; z-index: 2; transition: height 1s cubic-bezier(0.16, 1, 0.3, 1); }
.laser-head { position: absolute; bottom: 0; left: -2px; width: 5px; height: 5px; background: #ff8002; border-radius: 50%; box-shadow: 0 0 10px #ff8002; }

.bt-step { position: relative; z-index: 3; display: flex; align-items: flex-start; gap: 30px; opacity: 0.4; transition: 0.4s; }
.bt-step.completed { opacity: 1; }

.bt-node { font-family: monospace; font-size: 10px; font-weight: 800; background: #fff; padding: 2px 0; color: #111; margin-left: -48px; }
.bt-step.completed .bt-node { color: #ff8002; }

.bt-info h4 { font-family: monospace; font-size: 13px; font-weight: 800; color: #111; margin: 0 0 5px 0; letter-spacing: 1px; }
.bt-info p { font-family: 'Playfair Display', serif; font-size: 13px; font-style: italic; color: #888; margin: 0; }

.override-terminal { margin-top: 60px; padding: 30px; background: #111; color: #fff; border: 1px solid #333; position: relative; overflow: hidden; }
.ot-header { font-family: monospace; font-size: 11px; font-weight: 800; color: #ff8002; letter-spacing: 2px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
.blinking-cursor { animation: blink 1s step-end infinite; font-size: 14px; }
.ot-desc { font-family: 'Inter', sans-serif; font-size: 11px; color: #888; line-height: 1.6; margin-bottom: 25px; }

.ot-actions { display: flex; gap: 15px; }
.btn-override-confirm { background: #fff; color: #111; border: none; font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 1px; padding: 12px 20px; cursor: pointer; transition: 0.3s; }
.btn-override-confirm:hover { background: #10b981; color: #fff; }
.btn-override-deny { background: transparent; color: #888; border: 1px solid #555; font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 1px; padding: 12px 20px; cursor: pointer; transition: 0.3s; }
.btn-override-deny:hover { border-color: #ef4444; color: #ef4444; }

.split-right { padding: 50px; position: relative; }
.coord-block { margin-bottom: 60px; }
.ib-name { font-family: 'Playfair Display', serif; font-size: 20px; font-style: italic; font-weight: 700; margin: 0 0 10px 0; color: #111; }

.mini-ledger { display: flex; flex-direction: column; }
.mini-items { display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px; border-bottom: 1px dashed #e5e5e5; padding-bottom: 30px; }
.ml-item { display: flex; gap: 15px; align-items: baseline; font-family: monospace; font-size: 11px; }
.ml-qty { color: #888; font-weight: 700; }
.ml-name { color: #111; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.ml-total { display: flex; justify-content: space-between; font-family: monospace; font-size: 11px; font-weight: 800; color: #111; letter-spacing: 1px; }

.ghost-watermark { position: absolute; bottom: -20px; right: -20px; font-family: monospace; font-size: 80px; font-weight: 800; color: rgba(17,17,17,0.02); transform: rotate(-90deg); transform-origin: bottom right; pointer-events: none; z-index: 0; }

@media (max-width: 1024px) {
    .profile-grid { grid-template-columns: 1fr; }
    .side-nav-transparent { flex-direction: row; flex-wrap: wrap; padding: 0; }
    .side-nav-transparent::before, .nav-tracker { display: none; }
    .side-nav-transparent .nav-link { flex-grow: 1; justify-content: center; padding: 15px; border-bottom: 1px solid #e5e5e5; }
    .side-nav-transparent .nav-divider { display: none; }
    .side-nav-transparent .logout-link { display: none; }
}

@media (max-width: 768px) {
    .dh-top { flex-direction: column; gap: 20px; }
    .header-actions-group { width: 100%; }
    .btn-print-ghost, .btn-comms-ghost { flex: 1; text-align: center; }
    .dh-main { flex-direction: column; align-items: flex-start; gap: 20px; }
    .macro-ref { font-size: 36px; min-width: auto; }
    .detail-header, .terminal-alert, .split-left, .split-right { padding: 30px 20px; }

    .tracker-split-grid { grid-template-columns: 1fr; }
    .split-left { border-right: none; border-bottom: 1px dashed #111; }

    .ot-actions { flex-direction: column; }
    .btn-override-confirm, .btn-override-deny { width: 100%; }
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

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.focus-in, .stagger-in').forEach(el => { observer.observe(el); });

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
