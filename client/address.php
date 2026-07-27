<?php
// 1. Initialization & Permissions
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

// --- 2. Handle Deletion Protocol ---
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Defensive check: Ensure the address belongs to the user
    mysqli_query($conn, "DELETE FROM user_addresses WHERE id=$del_id AND user_id='$user_id'");
    header("Location: addresses.php?msg=deleted");
    exit();
}

include 'includes/header.php';

// Fetch coordinates sorted by default status
$addr_res = mysqli_query($conn, "SELECT * FROM user_addresses WHERE user_id = '$user_id' ORDER BY is_default DESC, id DESC");
$count = mysqli_num_rows($addr_res);
?>

<main class="profile-page">
    <div class="container profile-grid">

        <aside class="profile-sidebar focus-in" style="--delay: 0">
            <nav class="side-nav-transparent" id="luxury-side-nav">
                <div class="nav-tracker" id="nav-tracker"></div>

                <a href="profile.php" class="nav-link"><span>01</span> My Profile</a>

                <a href="my-orders.php" class="nav-link"><span>02</span> My Orders</a>

                <a href="notification.php" class="nav-link">
                    <span>03</span> Notifications
                    <?php if($has_notifications): ?>
                        <div class="notif-ping"></div>
                    <?php endif; ?>
                </a>

                <a href="addresses.php" class="nav-link active"><span>04</span> Addresses</a>
                <hr class="nav-divider">
                <a href="logout.php" class="logout-link">Logout</a>
            </nav>
        </aside>

        <section class="profile-main">
            <div id="editorial-paper-wrapper">

                <header class="luxury-header fade-up" style="--delay: 1">
                    <div class="header-inner">
                        <h2 class="editorial-title">Private Archive.</h2>
                        <p class="editorial-subtitle">A curated index of your delivery destinations and logistical coordinates.</p>
                    </div>
                </header>

                <div class="toast-container">
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                        <div class="luxury-toast">RECORD SUCCESSFULLY ERASED FROM ARCHIVE.</div>
                    <?php endif; ?>
                </div>

                <div class="address-gallery fade-up" style="--delay: 2" id="address-gallery">
                    <?php if ($count > 0): ?>
                        <?php
                        $i = 0;
                        while($addr = mysqli_fetch_assoc($addr_res)):
                            $i++;
                        ?>
                            <div class="gallery-item-wrap">
                                <div class="matte-card <?php echo $addr['is_default'] ? 'is-primary' : ''; ?>"
                                     data-tilt data-tilt-max="3" data-tilt-speed="1500">

                                    <div class="card-top-section">
                                        <span class="meta-id">COORD_REF / 0<?php echo $i; ?></span>
                                        <?php if($addr['is_default']): ?>
                                            <div class="primary-indicator">
                                                <div class="breathing-dot"></div>
                                                <span>PRIMARY</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-mid-section">
                                        <h3 class="recipient-name"><?php echo htmlspecialchars($addr['receiver_name']); ?></h3>

                                        <div class="detail-stack">
                                            <div class="detail-box">
                                                <span class="tiny-label">COMMS_LINK</span>
                                                <span class="tiny-value"><?php echo htmlspecialchars($addr['phone']); ?></span>
                                            </div>
                                            <div class="detail-box">
                                                <span class="tiny-label">LOCATION_DATA</span>
                                                <span class="tiny-value address-wrap"><?php echo nl2br(htmlspecialchars($addr['address_line'])); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-bot-section">
                                        <a href="addresses.php?delete=<?php echo $addr['id']; ?>" class="silk-erase-btn" onclick="return silkErase(this, event)">
                                            <span class="hover-line"></span>
                                            [ ERASE RECORD ]
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>

                        <div class="zen-empty-state">
                            <div class="aura-container">
                                <div class="breathing-aura"></div>
                            </div>
                            <div class="zen-text-content">
                                <h3 class="zen-title">The Archive is Silent.</h3>
                                <p class="zen-desc">No logistical coordinates have been inscribed in your dossier.</p>
                                <a href="index.php" class="btn-editorial-outline">INITIATE DISCOVERY</a>
                            </div>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
/* --- Profile Page Core Structure --- */
.profile-page { background: #fdfcfb; padding: 40px 0 100px 0; min-height: 90vh; font-family: 'Inter', sans-serif; color: #111; }
.profile-grid { display: grid; grid-template-columns: 260px 1fr; gap: 60px; align-items: start; }

/* --- Sidebar: Stealth Transparency --- */
.profile-sidebar { position: sticky; top: 120px; height: fit-content; z-index: 10; }
.side-nav-transparent { position: relative; display: flex; flex-direction: column; padding: 20px 0;}
.side-nav-transparent::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 1px; background: rgba(0,0,0,0.05); }

.nav-tracker { position: absolute; left: 14px; width: 3px; height: 20px; background: #ff8002; z-index: 2; transition: all 0.5s cubic-bezier(0.2, 1, 0.3, 1); opacity: 0; }

.side-nav-transparent .nav-link {
    position: relative; z-index: 1; padding: 18px 35px; display: flex; align-items: center;
    text-decoration: none; color: #888; font-size: 12px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 2px; transition: color 0.4s ease;
}
.side-nav-transparent .nav-link span { font-family: monospace; font-size: 10px; margin-right: 15px; opacity: 0.4; }
.side-nav-transparent .nav-link.active { color: #111; font-weight: 800; }
.side-nav-transparent .nav-link.active span { opacity: 1; color: #ff8002; }
.side-nav-transparent .nav-divider { border: none; border-top: 1px solid rgba(0,0,0,0.05); margin: 25px 35px; }
.side-nav-transparent .logout-link { display: block; padding: 10px 35px; color: #ef4444; font-size: 11px; font-weight: 700; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; }

.notif-ping { width: 6px; height: 6px; background: #ff8002; border-radius: 50%; margin-left: 10px; position: relative; }
.notif-ping::after { content: ''; position: absolute; inset: 0; border-radius: 50%; border: 1px solid #ff8002; animation: pingOut 2s infinite; }
@keyframes pingOut { 75%, 100% { transform: scale(3); opacity: 0; } }

/* --- Main Content: Editorial Paper --- */
#editorial-paper-wrapper { padding: 0 20px; }
.luxury-header { margin-bottom: 80px; }
.editorial-title { font-family: 'Playfair Display', serif; font-size: 52px; font-weight: 400; font-style: italic; letter-spacing: -2px; margin: 0 0 10px 0; }
.editorial-subtitle { font-family: monospace; font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 3px; }

/* --- Address Cards Gallery --- */
.address-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 40px; }

/* Optical Focus Effect */
.address-gallery:has(.gallery-item-wrap:hover) .gallery-item-wrap:not(:hover) { opacity: 0.2; filter: grayscale(1); transform: scale(0.98); }

.matte-card {
    background: #ffffff; padding: 50px; border: 1px solid #eee; position: relative;
    box-shadow: 0 30px 60px rgba(0,0,0,0.02); transition: all 0.8s cubic-bezier(0.2, 1, 0.3, 1);
}
.matte-card:hover { border-color: #111; box-shadow: 0 40px 80px rgba(0,0,0,0.05); }
.matte-card.is-primary { border-top: 4px solid #111; }

.card-top-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px; }
.meta-id { font-family: monospace; font-size: 9px; color: #ccc; letter-spacing: 2px; }

.primary-indicator { display: flex; align-items: center; gap: 10px; font-family: monospace; font-size: 9px; font-weight: 800; color: #ff8002; letter-spacing: 1px; }
.breathing-dot { width: 5px; height: 5px; background: #ff8002; border-radius: 50%; box-shadow: 0 0 10px #ff8002; animation: softPulse 2s infinite; }
@keyframes softPulse { 50% { opacity: 0.3; transform: scale(1.5); } }

.recipient-name { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 400; font-style: italic; margin-bottom: 30px; }
.detail-stack { display: flex; flex-direction: column; gap: 25px; }
.tiny-label { font-family: monospace; font-size: 9px; font-weight: 800; color: #bbb; letter-spacing: 2px; display: block; margin-bottom: 5px; }
.tiny-value { font-size: 14px; color: #333; line-height: 1.6; font-weight: 500; }

.card-bot-section { margin-top: 50px; padding-top: 30px; border-top: 1px dashed #eee; }
.silk-erase-btn { display: inline-flex; align-items: center; gap: 15px; text-decoration: none; color: #888; font-family: monospace; font-size: 9px; font-weight: 800; letter-spacing: 2px; transition: 0.4s; }
.silk-erase-btn:hover { color: #ef4444; }
.hover-line { width: 10px; height: 1px; background: currentColor; transition: 0.4s; }
.silk-erase-btn:hover .hover-line { width: 30px; }

/* --- Zen Empty State --- */
.zen-empty-state { grid-column: 1 / -1; height: 50vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
.breathing-aura { width: 300px; height: 300px; background: radial-gradient(circle, rgba(255, 128, 2, 0.1) 0%, transparent 70%); filter: blur(40px); animation: auraPulse 6s ease-in-out infinite alternate; }
@keyframes auraPulse { from { transform: scale(0.8); opacity: 0.3; } to { transform: scale(1.3); opacity: 0.8; } }
.zen-title { font-family: 'Playfair Display', serif; font-size: 32px; font-style: italic; margin-top: -150px; margin-bottom: 10px; }
.btn-editorial-outline { display: inline-block; padding: 15px 40px; border: 1px solid #111; color: #111; text-decoration: none; font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 2px; margin-top: 30px; transition: 0.4s; }
.btn-editorial-outline:hover { background: #111; color: #fff; }

.luxury-toast { position: fixed; bottom: 40px; right: 40px; background: #111; color: #fff; padding: 20px 40px; font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 2px; z-index: 100; animation: toastSlide 4s forwards; }
@keyframes toastSlide { 0% { transform: translateY(100px); opacity: 0; } 10%, 90% { transform: translateY(0); opacity: 1; } 100% { transform: translateY(100px); opacity: 0; } }

@media (max-width: 1024px) { .profile-grid { grid-template-columns: 1fr; } .profile-sidebar { position: static; margin-bottom: 60px; } .side-nav-transparent::before { display: none; } }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Tracker Logic
    const nav = document.getElementById('luxury-side-nav');
    const tracker = document.getElementById('nav-tracker');
    const links = document.querySelectorAll('.side-nav-transparent .nav-link');
    const activeLink = document.querySelector('.side-nav-transparent .nav-link.active');

    function moveTracker(el) {
        if (!el || !tracker || !nav) return;
        const navRect = nav.getBoundingClientRect();
        const elRect = el.getBoundingClientRect();
        const offset = elRect.top - navRect.top + (elRect.height / 2) - 10;
        tracker.style.transform = `translateY(${offset}px)`;
        tracker.style.opacity = '1';
    }

    setTimeout(() => moveTracker(activeLink), 150);
    links.forEach(l => {
        l.addEventListener('mouseenter', () => moveTracker(l));
        l.addEventListener('mouseleave', () => moveTracker(activeLink));
    });

    // 2. Silk Erase Animation
    window.silkErase = function(el, e) {
        if (!confirm('Erase this coordinate record?')) { e.preventDefault(); return false; }
        const wrap = el.closest('.gallery-item-wrap');
        wrap.style.pointerEvents = 'none';
        wrap.style.transition = 'all 0.8s cubic-bezier(0.2, 1, 0.3, 1)';
        wrap.style.transform = 'translateY(-40px) scale(0.95)';
        wrap.style.opacity = '0';
        wrap.style.filter = 'blur(10px)';

        setTimeout(() => { window.location.href = el.href; }, 750);
        return false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
