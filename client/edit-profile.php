<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    $user_temp_res = mysqli_query($conn, "SELECT profile_icon FROM users WHERE id = '$user_id'");
    $user_temp = mysqli_fetch_assoc($user_temp_res);
    $icon_name = $user_temp['profile_icon'];

    if (!empty($_FILES['profile_icon']['name'])) {
        $target_dir = "assets/images/uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_ext = pathinfo($_FILES['profile_icon']['name'], PATHINFO_EXTENSION);
        $icon_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $icon_name;
        move_uploaded_file($_FILES['profile_icon']['tmp_name'], $target_file);
    }

    $update_sql = "UPDATE users SET
                    full_name = '$full_name',
                    phone = '$phone',
                    location = '$location',
                    profile_icon = '$icon_name'
                  WHERE id = '$user_id'";

    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_icon'] = $icon_name;
        $success_msg = "IDENTITY ARCHIVE AUTHORIZED AND UPDATED.";
    }
}

$user_res = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_res);

// ==========================================
// ==========================================
$notif_res = mysqli_query($conn, "SELECT id FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
$has_notifications = ($notif_res && mysqli_num_rows($notif_res) > 0);

include 'includes/header.php';
?>

<main class="profile-page">
    <div class="container profile-grid">

        <aside class="profile-sidebar focus-in" style="--delay: 0">
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
            <div id="passport-matrix-wrapper">

                <header class="matrix-header fade-up" style="--delay: 1">
                    <div class="header-meta">
                        <span class="mono-idx">CLIENT PROTOCOL / 01</span>
                        <p class="mono-desc">IDENTITY MODIFICATION TERMINAL</p>
                    </div>
                    <h1 class="serif-gigantic">Identity.</h1>
                </header>

                <?php if ($success_msg): ?>
                    <div class="matrix-alert success fade-up" style="--delay: 2">
                        <span class="alert-msg"><?php echo $success_msg; ?></span>
                        <a href="profile.php" class="btn-return-mono">CLOSE TERMINAL ↗</a>
                    </div>
                <?php endif; ?>

                <form action="edit-profile.php" method="POST" enctype="multipart/form-data" class="matrix-form" id="identity-form">

                    <div class="matrix-layout fade-up" style="--delay: 2">

                        <div class="matrix-col-left">

                            <div class="polaroid-uploader" data-tilt data-tilt-max="2" data-tilt-speed="2500" data-tilt-glare="false">
                                <?php
                                    $is_upload = (strpos($user['profile_icon'], 'user_') === 0);
                                    $img_path = ($is_upload ? "assets/images/uploads/" : "assets/images/") . (!empty($user['profile_icon']) ? $user['profile_icon'] : 'default-avatar.png');
                                ?>
                                <img src="<?php echo $img_path; ?>" alt="Identity Portrait" id="portrait-preview">

                                <div class="upload-curtain">
                                    <span class="curtain-text">REDEFINE PORTRAIT</span>
                                    <input type="file" name="profile_icon" accept="image/*" class="stealth-file-input" id="portrait-input">
                                </div>
                                <div class="polaroid-meta">VISUAL.ID // REF-0<?php echo $user_id; ?></div>
                            </div>

                            <div class="system-locked-data">
                                <div class="locked-item">
                                    <span class="locked-key">SYSTEM ID</span>
                                    <span class="locked-val"><?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <div class="locked-item">
                                    <span class="locked-key">AUTHORIZATION</span>
                                    <span class="locked-val status-green">VERIFIED</span>
                                </div>
                                <div class="locked-item">
                                    <span class="locked-key">SECURE EMAIL</span>
                                    <span class="locked-val val-truncate" title="<?php echo htmlspecialchars($user['email']); ?>"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="matrix-col-right">

                            <div class="matrix-field stagger-in" style="--stagger: 1">
                                <label for="m_name" class="matrix-label">LEGAL DESIGNATION</label>
                                <input type="text" id="m_name" name="full_name" class="matrix-input" value="<?php echo htmlspecialchars($user['full_name']); ?>" required autocomplete="off">
                            </div>

                            <div class="matrix-field stagger-in" style="--stagger: 2">
                                <label for="m_phone" class="matrix-label">DIRECT CONTACT LINE</label>
                                <input type="text" id="m_phone" name="phone" class="matrix-input" value="<?php echo htmlspecialchars($user['phone'] ?? '+60 '); ?>" autocomplete="off">
                            </div>

                            <div class="matrix-field stagger-in" style="--stagger: 3">
                                <label for="m_loc" class="matrix-label">OPERATING REGION</label>
                                <input type="text" id="m_loc" name="location" class="matrix-input" value="<?php echo htmlspecialchars($user['location'] ?? 'Malaysia'); ?>" autocomplete="off">
                            </div>

                            <div class="matrix-action-dock stagger-in" style="--stagger: 4">
                                <p class="action-warning">BY AUTHORIZING, YOU CONFIRM ALL IDENTITY COORDINATES ARE ACCURATE.</p>
                                <div class="btn-group">
                                    <a href="profile.php" class="btn-matrix-discard">DISCARD</a>
                                    <button type="submit" class="btn-matrix-authorize" id="authorize-btn">
                                        AUTHORIZE MODIFICATION
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 THE IDENTITY MATRIX (EXTREME LUXURY)
   ============================================== */
.profile-page { background: #fdfcfb; padding: 40px 0 100px 0; min-height: 80vh; font-family: 'Inter', sans-serif; color: #111; }
.profile-grid { display: grid; grid-template-columns: 260px 1fr; gap: 40px; align-items: start; }

.profile-sidebar { position: sticky; top: 100px; height: fit-content; z-index: 10; }
.side-nav-transparent { position: relative; display: flex; flex-direction: column; padding: 20px 0; background: transparent; }
.side-nav-transparent::before { content: ''; position: absolute; left: 15px; top: 20px; bottom: 20px; width: 1px; background: rgba(0,0,0,0.1); z-index: 0; }
.nav-tracker { position: absolute; left: 14px; width: 3px; height: 20px; background: #ff8002; z-index: 2; transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), height 0.5s cubic-bezier(0.25, 1, 0.5, 1); opacity: 0; border-radius: 2px; }

.side-nav-transparent .nav-link { position: relative; z-index: 1; padding: 15px 35px; display: flex; align-items: center; text-decoration: none; color: #888; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: color 0.4s ease; font-family: 'Inter', sans-serif; }
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

.focus-in { opacity: 0; filter: blur(4px); transform: translateY(20px); animation: editorialUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.fade-up { opacity: 0; animation: editorialUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) calc(var(--delay) * 0.15s) forwards; }
@keyframes editorialUp { from { opacity: 0; transform: translateY(40px); filter: blur(4px); } to { opacity: 1; transform: translateY(0); filter: blur(0); } }

#passport-matrix-wrapper { color: #111; font-family: 'Inter', sans-serif; background: transparent; padding: 0 10px 100px 10px; }

#passport-matrix-wrapper .matrix-header { margin-bottom: 60px; border-bottom: 1px solid #111; padding-bottom: 30px; }
#passport-matrix-wrapper .header-meta { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 20px; }
#passport-matrix-wrapper .mono-idx, #passport-matrix-wrapper .mono-desc { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 2px; font-weight: 700; text-transform: uppercase; margin: 0; }
#passport-matrix-wrapper .serif-gigantic { font-family: 'Playfair Display', serif; font-size: clamp(48px, 6vw, 80px); font-weight: 400; font-style: italic; color: #111; margin: 0; line-height: 0.9; letter-spacing: -2px; transform: translateX(-3px); }

.matrix-alert { border: 1px solid #10b981; padding: 20px 30px; margin-bottom: 50px; display: flex; justify-content: space-between; align-items: center; font-family: monospace; font-size: 10px; letter-spacing: 2px; color: #10b981; background: #f0fdf4; }
.btn-return-mono { color: #111; text-decoration: none; border-bottom: 1px solid #111; padding-bottom: 2px; transition: 0.3s; font-weight: 700; }
.btn-return-mono:hover { color: #ff8002; border-color: #ff8002; }

.matrix-layout {
    display: grid;
    grid-template-columns: 32% 1fr;
    gap: 80px;
    align-items: start;
}

.matrix-col-left { display: flex; flex-direction: column; gap: 40px; }

.polaroid-uploader {
    width: 100%;
    aspect-ratio: 3/4;
    background: #e5e5e5;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    border: 1px solid #e5e5e5;
}
.polaroid-uploader img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(100%) contrast(1.05); transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1), filter 1s ease; }
.polaroid-uploader:hover img { filter: grayscale(0%) contrast(1); transform: scale(1.03); }

.upload-curtain { position: absolute; inset: 0; background: rgba(17, 17, 17, 0.6); backdrop-filter: blur(2px); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.5s ease; }
.polaroid-uploader:hover .upload-curtain { opacity: 1; }
.curtain-text { color: #fff; font-family: monospace; font-size: 10px; font-weight: 700; letter-spacing: 3px; border: 1px solid rgba(255,255,255,0.4); padding: 12px 20px; transition: 0.3s; }
.polaroid-uploader:hover .curtain-text { border-color: #fff; transform: translateY(-5px); }
.stealth-file-input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }

.polaroid-meta { position: absolute; bottom: 15px; left: 15px; font-family: monospace; font-size: 9px; color: #fff; font-weight: 700; letter-spacing: 2px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); z-index: 2; pointer-events: none;}

.system-locked-data { display: flex; flex-direction: column; gap: 20px; padding-top: 20px; border-top: 1px solid #e5e5e5; }
.locked-item { display: flex; flex-direction: column; gap: 5px; }
.locked-key { font-family: monospace; font-size: 9px; color: #888; font-weight: 700; letter-spacing: 1.5px; }
.locked-val { font-family: monospace; font-size: 12px; color: #111; font-weight: 600; }
.status-green { color: #10b981; }
.val-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: block; }

.matrix-col-right { display: flex; flex-direction: column; gap: 60px; padding-top: 10px; }

.matrix-field { display: flex; flex-direction: column; position: relative; border-bottom: 1px solid #e5e5e5; transition: border-color 0.4s; }
.matrix-field:focus-within { border-color: #111; }

.matrix-label { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 2px; font-weight: 700; text-transform: uppercase; margin-bottom: 15px; transition: color 0.4s; }
.matrix-field:focus-within .matrix-label { color: #ff8002; }

.matrix-input { width: 100%; border: none; background: transparent; outline: none; font-family: 'Playfair Display', serif; font-size: 32px; font-style: italic; color: #111; padding-bottom: 15px; transition: transform 0.4s ease; }
.matrix-input:focus { transform: translateX(5px); }
.matrix-input::placeholder { color: #e5e5e5; font-style: normal; font-family: 'Inter', sans-serif; font-size: 24px; }

.stagger-in { opacity: 0; transform: translateY(20px); animation: staggerFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.stagger-in:nth-child(1) { animation-delay: calc(0.3s + 0.1s); }
.stagger-in:nth-child(2) { animation-delay: calc(0.3s + 0.2s); }
.stagger-in:nth-child(3) { animation-delay: calc(0.3s + 0.3s); }
.stagger-in:nth-child(4) { animation-delay: calc(0.3s + 0.4s); }
@keyframes staggerFade { to { opacity: 1; transform: translateY(0); } }

.matrix-action-dock { margin-top: 20px; display: flex; flex-direction: column; gap: 30px; }
.action-warning { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 1.5px; line-height: 1.6; margin: 0; max-width: 80%; }

.btn-group { display: flex; gap: 20px; align-items: stretch; }

.btn-matrix-discard { display: flex; align-items: center; justify-content: center; padding: 0 30px; border: 1px solid #e5e5e5; color: #888; text-decoration: none; font-family: monospace; font-size: 10px; font-weight: 700; letter-spacing: 2px; transition: 0.3s; }
.btn-matrix-discard:hover { border-color: #111; color: #111; }

.btn-matrix-authorize { flex-grow: 1; background: #111; color: #fff; border: none; padding: 25px 30px; font-family: monospace; font-size: 12px; font-weight: 700; letter-spacing: 3px; cursor: pointer; transition: 0.4s ease; text-transform: uppercase; }
.btn-matrix-authorize:hover { background: #ff8002; box-shadow: 0 15px 30px rgba(255, 128, 2, 0.2); }

.flash-stamp { animation: stampFlash 0.5s ease forwards; }
@keyframes stampFlash { 0% { filter: invert(1); } 100% { filter: invert(0); } }

@media (max-width: 1024px) {
    .matrix-layout { grid-template-columns: 1fr; gap: 60px; }
    .matrix-col-left { flex-direction: row; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 40px;}
    .polaroid-uploader { width: 180px; flex-shrink: 0; }
    .system-locked-data { border-top: none; padding-top: 0; padding-left: 40px; }
    .profile-grid { grid-template-columns: 1fr; }
    .side-nav-transparent { flex-direction: row; flex-wrap: wrap; padding: 0; }
    .side-nav-transparent::before, .nav-tracker { display: none; }
    .side-nav-transparent .nav-link { flex-grow: 1; justify-content: center; padding: 15px; border-bottom: 1px solid #e5e5e5; }
    .side-nav-transparent .nav-divider { display: none; }
    .side-nav-transparent .logout-link { display: none; }
}
@media (max-width: 768px) {
    .matrix-col-left { flex-direction: column; align-items: flex-start; padding-bottom: 30px; gap: 30px;}
    .system-locked-data { padding-left: 0; width: 100%; border-top: 1px solid #e5e5e5; padding-top: 20px;}
    .matrix-input { font-size: 24px; }
    .btn-group { flex-direction: column-reverse; }
    .btn-matrix-discard { padding: 20px; }
    .profile-sidebar { position: static; margin-bottom: 40px; }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
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

        const polaroid = document.querySelectorAll(".polaroid-uploader");
        if (polaroid.length > 0) {
            VanillaTilt.init(polaroid, {
                max: 2, speed: 2500, glare: false, scale: 1.01
            });
        }

        const fileInput = document.getElementById('portrait-input');
        const imgPreview = document.getElementById('portrait-preview');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    imgPreview.style.filter = 'grayscale(0%) contrast(1)';
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        const form = document.getElementById('identity-form');
        const authBtn = document.getElementById('authorize-btn');

        form.addEventListener('submit', function() {
            authBtn.innerHTML = 'AUTHORIZING...';
            document.getElementById('passport-matrix-wrapper').classList.add('flash-stamp');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
