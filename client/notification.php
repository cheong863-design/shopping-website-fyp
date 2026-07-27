<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// ==========================================
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_one') {
        $notif_id = (int)$_POST['notif_id'];
        mysqli_query($conn, "DELETE FROM notifications WHERE id = '$notif_id' AND user_id = '$user_id'");
        echo "success";
        exit();
    }

    if ($_POST['action'] === 'delete_all') {
        mysqli_query($conn, "DELETE FROM notifications WHERE user_id = '$user_id'");
        echo "success";
        exit();
    }
}

mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id' AND is_read = 0");

$notif_query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY id DESC");
$notifications = [];
while($row = mysqli_fetch_assoc($notif_query)) {
    $notifications[] = $row;
}
$notif_count = count($notifications);

include 'includes/header.php';
?>

<main class="profile-page">
    <div class="container profile-grid">

        <aside class="profile-sidebar focus-in" style="--delay: 0">
            <nav class="side-nav-transparent" id="luxury-side-nav">
                <div class="nav-tracker" id="nav-tracker"></div>

                <a href="profile.php" class="nav-link"><span>01</span> My Profile</a>
                <a href="my-orders.php" class="nav-link"><span>02</span> My Orders</a>
                <a href="notification.php" class="nav-link active">
                    <span>03</span> Notifications
                    </a>
                <a href="addresses.php" class="nav-link"><span>04</span> Addresses</a>
                <hr class="nav-divider">
                <a href="logout.php" class="logout-link">Logout</a>
            </nav>
        </aside>

        <section class="profile-main">
            <div class="dossier-document focus-in" style="--delay: 1">

                <header class="dossier-header stagger-in" style="--stagger: 2; padding-bottom: 30px;">
                    <div class="dh-meta">
                        <span class="meta-tag">COMMUNICATION UPLINK</span>
                        <h1 class="macro-ref" style="font-size: 42px; margin-top: 5px;">Inbox.</h1>
                    </div>
                    <?php if ($notif_count > 0): ?>
                        <div class="dh-actions">
                            <button type="button" class="btn-purge-all" onclick="openPurgeModal()">
                                [ PURGE ALL RECORDS ]
                            </button>
                        </div>
                    <?php endif; ?>
                </header>

                <div class="dossier-items stagger-in" style="--stagger: 3" style="border-bottom: none; padding-top: 10px;">

                    <div class="items-ledger" id="notif-container">
                        <?php if ($notif_count > 0): ?>
                            <?php foreach($notifications as $notif): ?>
                                <div class="notif-row" id="notif-<?php echo $notif['id']; ?>">
                                    <div class="notif-content">
                                        <div class="notif-head">
                                            <span class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></span>
                                            <span class="notif-date">
                                                <?php echo isset($notif['created_at']) ? date('d.m.Y H:i', strtotime($notif['created_at'])) : 'SYS-MSG-'.str_pad($notif['id'], 4, '0', STR_PAD_LEFT); ?>
                                            </span>
                                        </div>
                                        <div class="notif-body">
                                            <?php echo nl2br(htmlspecialchars($notif['message'])); ?>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-delete-x" onclick="deleteOne(<?php echo $notif['id']; ?>)" title="Delete this record">✕</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state-raw" id="initial-empty">
                                <p class="empty-serif">Silence in the archive.</p>
                                <p class="mono-sub" style="margin-top: 10px;">CURRENTLY NO COMMUNICATIONS OR MESSAGES FOUND.</p>
                            </div>
                        <?php endif; ?>

                        <div class="empty-state-raw" id="dynamic-empty" style="display: none;">
                            <p class="empty-serif">Silence in the archive.</p>
                            <p class="mono-sub" style="margin-top: 10px;">ALL RECORDS HAVE BEEN PURGED.</p>
                        </div>

                    </div>
                </div>

                <div class="ghost-stamp">INBOX</div>
            </div>
        </section>

    </div>
</main>

<div id="purge-modal" class="editorial-modal">
    <div class="modal-canvas" style="text-align: center; padding: 50px 40px;">
        <h3 class="serif-title" style="font-size: 28px; margin-bottom: 15px; color: #ef4444;">Warning.</h3>
        <p class="mono-subtitle" style="font-size: 11px; line-height: 1.8; margin-bottom: 35px; color: #111; text-transform: uppercase;">
            You are about to permanently delete all communications.<br>This action cannot be reversed. Proceed?
        </p>
        <div class="modal-actions-row">
            <button class="btn-solid-red" onclick="confirmPurgeAll()">Confirm Purge</button>
            <button class="btn-link-muted" onclick="closePurgeModal()">Cancel</button>
        </div>
    </div>
</div>

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

.dossier-document {
    background-color: #ffffff;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.03'/%3E%3C/svg%3E");
    border: 1px solid #e5e5e5;
    border-radius: 0;
    box-shadow: 0 20px 40px rgba(0,0,0,0.02);
    position: relative;
    overflow: hidden;
    min-height: 500px;
}

.dossier-header { padding: 50px 40px; border-bottom: 1px dashed #111; display: flex; justify-content: space-between; align-items: flex-end; }
.dh-meta { display: flex; flex-direction: column; gap: 8px; }
.meta-tag { font-family: monospace; font-size: 9px; font-weight: 800; color: #ff8002; letter-spacing: 4px; }
.macro-ref { font-family: 'Playfair Display', serif; font-size: 52px; font-style: italic; font-weight: 400; color: #111; line-height: 1; letter-spacing: -2px; margin: 0;}

.btn-purge-all { background: transparent; border: none; font-family: monospace; font-size: 10px; font-weight: 800; color: #ef4444; letter-spacing: 2px; cursor: pointer; transition: 0.3s; padding: 0; }
.btn-purge-all:hover { color: #111; transform: translateY(-2px); }

.dossier-items { padding: 0 40px 40px 40px; }
.notif-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 30px 0; border-bottom: 1px dashed #e5e5e5; transition: 0.4s ease;
}
.notif-row:last-child { border-bottom: none; }
.notif-row:hover { background: rgba(0,0,0,0.015); padding-left: 15px; padding-right: 15px; }

.notif-content { display: flex; flex-direction: column; gap: 10px; flex-grow: 1; padding-right: 30px; }
.notif-head { display: flex; justify-content: space-between; align-items: baseline; }
.notif-title { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: #111; margin: 0; }
.notif-date { font-family: monospace; font-size: 10px; font-weight: 600; color: #888; letter-spacing: 1px; }

.notif-body { font-size: 13px; color: #666; line-height: 1.6; }

.btn-delete-x {
    background: transparent; border: none; color: #ccc; font-size: 16px; cursor: pointer; padding: 5px;
    transition: 0.3s cubic-bezier(0.16, 1, 0.3, 1); align-self: center; outline: none;
}
.btn-delete-x:hover { color: #ef4444; transform: rotate(90deg) scale(1.2); }

.squash-out { animation: squashFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes squashFade {
    0% { opacity: 1; transform: scale(1); max-height: 100px; }
    100% { opacity: 0; transform: scale(0.95); max-height: 0; padding: 0; margin: 0; border: none; }
}

.empty-state-raw { padding: 80px 0; text-align: center; }
.empty-serif { font-family: 'Playfair Display', serif; font-size: 24px; font-style: italic; color: #888; margin-bottom: 5px; }

.ghost-stamp {
    position: absolute; bottom: 40px; right: 40px;
    font-family: monospace; font-size: 60px; font-weight: 800; letter-spacing: 10px;
    color: rgba(17, 17, 17, 0.03); pointer-events: none; z-index: 0;
}

.editorial-modal { display: none; position: fixed; inset: 0; background: rgba(250, 250, 249, 0.95); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
.editorial-modal.active { display: flex; opacity: 1; }

.modal-canvas { background: #fff; width: 420px; max-width: 90%; border: 1px solid #111; box-shadow: 15px 15px 0 rgba(17,17,17,0.05); transform: translateY(20px); transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.editorial-modal.active .modal-canvas { transform: translateY(0); }

.modal-actions-row { display: flex; flex-direction: column; gap: 10px; }
.btn-solid-red { background: #ef4444; color: #fff; border: none; padding: 18px; font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 2px; cursor: pointer; transition: 0.3s; text-transform: uppercase; }
.btn-solid-red:hover { background: #dc2626; box-shadow: 0 10px 20px rgba(239, 68, 68, 0.2); }
.btn-link-muted { background: transparent; color: #888; border: none; font-family: monospace; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; transition: 0.3s; padding: 10px; }
.btn-link-muted:hover { color: #111; }

@media (max-width: 1024px) {
    .profile-grid { grid-template-columns: 1fr; }
    .side-nav-transparent { flex-direction: row; flex-wrap: wrap; padding: 0; }
    .side-nav-transparent::before, .nav-tracker { display: none; }
    .side-nav-transparent .nav-link { flex-grow: 1; justify-content: center; padding: 15px; border-bottom: 1px solid #e5e5e5; }
    .side-nav-transparent .nav-divider { display: none; }
    .side-nav-transparent .logout-link { display: none; }
}
@media (max-width: 768px) {
    .dossier-header { flex-direction: column; align-items: flex-start; gap: 20px; }
    .dossier-items, .dossier-header { padding: 30px 20px; }
    .notif-row { flex-direction: column; position: relative; }
    .notif-content { padding-right: 0; }
    .btn-delete-x { position: absolute; top: 30px; right: 0; }
    .notif-head { flex-direction: column; gap: 5px; margin-bottom: 10px; padding-right: 20px; }
    .ghost-stamp { font-size: 40px; }
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
});

const container = document.getElementById('notif-container');

function checkEmptyState() {
    const remainingRows = container.querySelectorAll('.notif-row');
    if (remainingRows.length === 0) {
        const initialEmpty = document.getElementById('initial-empty');
        if(initialEmpty) initialEmpty.style.display = 'none';
        document.getElementById('dynamic-empty').style.display = 'block';

        const purgeBtn = document.querySelector('.btn-purge-all');
        if (purgeBtn) purgeBtn.style.display = 'none';
    }
}

function deleteOne(notifId) {
    const row = document.getElementById(`notif-${notifId}`);
    if (!row) return;

    const btn = row.querySelector('.btn-delete-x');
    btn.style.pointerEvents = 'none';
    btn.style.color = '#e5e5e5';

    const formData = new FormData();
    formData.append('action', 'delete_one');
    formData.append('notif_id', notifId);

    fetch('notification.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => {
        if (data.includes('success')) {
            row.classList.add('squash-out');

            setTimeout(() => {
                row.remove();
                checkEmptyState();
            }, 400);
        } else {
            alert('Failed to delete message.');
            btn.style.pointerEvents = 'auto';
            btn.style.color = '';
        }
    })
    .catch(err => { console.error(err); btn.style.pointerEvents = 'auto'; });
}

const purgeModal = document.getElementById('purge-modal');
const purgeBtn = purgeModal.querySelector('.btn-solid-red');

function openPurgeModal() {
    purgeModal.classList.add('active');
}

function closePurgeModal() {
    purgeModal.classList.remove('active');
}

function confirmPurgeAll() {
    purgeBtn.innerText = 'PURGING...';
    purgeBtn.style.pointerEvents = 'none';

    const formData = new FormData();
    formData.append('action', 'delete_all');

    fetch('notification.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => {
        if (data.includes('success')) {
            closePurgeModal();
            const allRows = document.querySelectorAll('.notif-row');
            allRows.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add('squash-out');
                }, index * 80);
            });

            setTimeout(() => {
                allRows.forEach(row => row.remove());
                checkEmptyState();
            }, allRows.length * 80 + 400);

        } else {
            alert('Failed to purge records.');
            purgeBtn.innerText = 'CONFIRM PURGE';
            purgeBtn.style.pointerEvents = 'auto';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
