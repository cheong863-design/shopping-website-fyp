<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// ==========================================
// ==========================================
$notif_res = mysqli_query($conn, "SELECT id FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
$has_notifications = ($notif_res && mysqli_num_rows($notif_res) > 0);
// ==========================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $query = "SELECT password FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if (!password_verify($current_password, $user['password'])) {
        $error_msg = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_msg = "New password must be at least 8 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";

        if (mysqli_query($conn, $update_sql)) {
            $success_msg = "Password updated successfully! You're securely locked in.";
        } else {
            $error_msg = "Something went wrong. Please try again.";
        }
    }
}
?>

<main class="profile-page">
    <div class="container profile-grid">

        <aside class="profile-sidebar">
            <nav class="side-nav-transparent" id="luxury-side-nav">
                <div class="nav-tracker" id="nav-tracker"></div>

                <a href="profile.php" class="nav-link"><span>01</span> My Profile</a>

                <a href="my-orders.php" class="nav-link"><span>02</span> My Orders</a>

                <a href="notification.php" class="nav-link">
                    <span>03</span> Notifications
                    <?php if($has_notifications): ?>
                        <div class="notif-ping" title="Unread Message"></div>
                    <?php endif; ?>
                </a>

                <a href="addresses.php" class="nav-link"><span>04</span> Addresses</a>
                <hr class="nav-divider">
                <a href="logout.php" class="logout-link">Logout</a>
            </nav>
        </aside>

        <section class="profile-main">
            <div class="spotlight-card" id="spotlight-card">
                <div class="card-content">

                    <div class="card-header-ultra">
                        <div class="shield-container">
                            <div class="shield-body">
                                <div class="shield-check"></div>
                            </div>
                        </div>
                        <div class="header-text-group">
                            <h3 class="gradient-title">Secure Your Account</h3>
                            <p>Update your credentials with enterprise-grade encryption.</p>
                        </div>
                    </div>

                    <?php if ($success_msg): ?>
                        <div class="alert alert-success animate-in">
                            <span class="alert-icon">✓</span>
                            <span><?php echo htmlspecialchars($success_msg); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger animate-shake">
                            <span class="alert-icon">!</span>
                            <span><?php echo htmlspecialchars($error_msg); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="account-settings.php" method="POST" class="settings-form" id="passwordForm">

                        <div class="input-ultra-group">
                            <div class="input-wrapper">
                                <input type="password" id="current_pwd" name="current_password" class="ultra-input" placeholder=" " required>
                                <label for="current_pwd" class="ultra-label">Current Password</label>
                                <button type="button" class="eye-toggle" onclick="togglePwd('current_pwd', this)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                                <div class="input-focus-ring"></div>
                            </div>
                        </div>

                        <div class="divider-ultra"><span>ENTER NEW CREDENTIALS</span></div>

                        <div class="input-ultra-group">
                            <div class="input-wrapper">
                                <input type="password" id="new_pwd" name="new_password" class="ultra-input" placeholder=" " required onkeyup="checkPasswordRules()">
                                <label for="new_pwd" class="ultra-label">New Password</label>
                                <button type="button" class="eye-toggle" onclick="togglePwd('new_pwd', this)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                                <div class="input-focus-ring"></div>
                            </div>

                            <div class="pwd-rules-tracker">
                                <div class="rule-item" id="rule-len">
                                    <div class="rule-icon"></div> <span>8+ characters</span>
                                </div>
                                <div class="rule-item" id="rule-up">
                                    <div class="rule-icon"></div> <span>1 uppercase letter</span>
                                </div>
                                <div class="rule-item" id="rule-num">
                                    <div class="rule-icon"></div> <span>1 number</span>
                                </div>
                            </div>
                        </div>

                        <div class="input-ultra-group" style="margin-top: 15px;">
                            <div class="input-wrapper">
                                <input type="password" id="confirm_pwd" name="confirm_password" class="ultra-input" placeholder=" " required onkeyup="checkMatchUltra()">
                                <label for="confirm_pwd" class="ultra-label">Confirm Password</label>
                                <button type="button" class="eye-toggle" onclick="togglePwd('confirm_pwd', this)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                                <div class="input-focus-ring"></div>
                                <div class="match-stamp" id="match-stamp">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"></path></svg>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-ultra-submit" id="submit-btn">
                            <span class="btn-text">Update Security Key</span>
                            <div class="btn-spinner"></div>
                            <div class="btn-glow"></div>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</main>

<style>

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

/* ==========================================
   🎨 APPLE/STRIPE LEVEL PREMIUM STYLES
   ========================================== */
.spotlight-card {
    position: relative;
    background: #fff;
    border-radius: 28px;
    padding: 1px;
    box-shadow: 0 20px 40px -10px rgba(0,0,0,0.03), 0 1px 3px rgba(0,0,0,0.02);
    max-width: 550px;
    --mouse-x: 50%;
    --mouse-y: 50%;
    background-image: radial-gradient(
        800px circle at var(--mouse-x) var(--mouse-y),
        rgba(255, 128, 2, 0.15),
        transparent 40%
    );
    animation: fadeSlideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
    transform: translateY(20px);
}

.card-content {
    background: #ffffff;
    border-radius: 27px;
    padding: 45px 40px;
    position: relative;
    z-index: 2;
    background-image: radial-gradient(
        600px circle at var(--mouse-x) var(--mouse-y),
        rgba(255, 128, 2, 0.02),
        transparent 40%
    );
}

.card-header-ultra { display: flex; align-items: center; gap: 20px; margin-bottom: 40px; }
.shield-container { position: relative; width: 56px; height: 56px; background: #fff4e5; border-radius: 16px; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 0 1px rgba(255,128,2,0.1); }
.shield-body { width: 28px; height: 32px; border: 2.5px solid #ff8002; border-radius: 4px 4px 14px 14px; position: relative; display: flex; align-items: center; justify-content: center; animation: floatShield 4s ease-in-out infinite alternate; }
.shield-check { width: 8px; height: 14px; border-right: 2.5px solid #ff8002; border-bottom: 2.5px solid #ff8002; transform: rotate(45deg) translate(-2px, -2px); opacity: 0.8; }
@keyframes floatShield { 0% { transform: translateY(0); } 100% { transform: translateY(-4px); } }

.gradient-title { font-size: 24px; font-weight: 800; margin: 0 0 6px 0; background: linear-gradient(135deg, #0f172a, #334155); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.header-text-group p { margin: 0; font-size: 14px; color: #64748b; line-height: 1.5; font-family: 'Inter', sans-serif;}

.alert { padding: 16px 20px; border-radius: 14px; margin-bottom: 30px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 12px; }
.alert-icon { display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; color: white; font-weight: bold; font-size: 12px; }
.alert-success { background: #f0fdf4; color: #15803d; box-shadow: inset 0 0 0 1px #bbf7d0; }
.alert-success .alert-icon { background: #22c55e; }
.alert-danger { background: #fef2f2; color: #b91c1c; box-shadow: inset 0 0 0 1px #fecaca; }
.alert-danger .alert-icon { background: #ef4444; }

@keyframes fadeSlideUp { to { opacity: 1; transform: translateY(0); } }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
.animate-in { animation: fadeSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.animate-shake { animation: shake 0.4s ease-in-out; }

.input-ultra-group { margin-bottom: 25px; position: relative; }
.input-wrapper { position: relative; border-radius: 14px; background: #f8fafc; z-index: 1; transition: background 0.3s; }
.input-wrapper:focus-within { background: #fff; }

.ultra-input {
    width: 100%; padding: 26px 50px 12px 20px; background: transparent; border: none; outline: none;
    font-size: 16px; color: #0f172a; font-weight: 600; font-family: 'Inter', monospace; letter-spacing: 2px;
}

.ultra-label {
    position: absolute; left: 20px; top: 20px; color: #94a3b8; font-size: 15px; font-weight: 500; letter-spacing: 0; pointer-events: none; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.ultra-input:focus ~ .ultra-label, .ultra-input:not(:placeholder-shown) ~ .ultra-label {
    top: 8px; font-size: 11px; font-weight: 800; color: #ff8002; letter-spacing: 0.5px;
}

.input-focus-ring {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 14px;
    box-shadow: inset 0 0 0 1.5px #e2e8f0; pointer-events: none; transition: all 0.3s ease; z-index: -1;
}
.ultra-input:focus ~ .input-focus-ring { box-shadow: inset 0 0 0 2px #ff8002, 0 0 0 4px rgba(255, 128, 2, 0.1); }

.eye-toggle {
    position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 5px; color: #94a3b8; cursor: pointer; transition: color 0.2s; display: flex; align-items: center; justify-content: center;
}
.eye-toggle svg { width: 20px; height: 20px; }
.eye-toggle:hover { color: #0f172a; }

.divider-ultra { text-align: center; position: relative; margin: 35px 0; }
.divider-ultra::before { content: ''; position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background: linear-gradient(90deg, transparent, #e2e8f0, transparent); }
.divider-ultra span { position: relative; background: #fff; padding: 0 15px; color: #cbd5e1; font-size: 11px; font-weight: 800; letter-spacing: 2px; }

.pwd-rules-tracker { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px; padding-left: 5px; }
.rule-item { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #94a3b8; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.rule-icon { width: 16px; height: 16px; border-radius: 50%; border: 2px solid #cbd5e1; position: relative; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); display: flex; align-items: center; justify-content: center; }

.rule-item.met { color: #10b981; }
.rule-item.met .rule-icon { background: #10b981; border-color: #10b981; transform: scale(1.1); }
.rule-item.met .rule-icon::after {
    content: ''; width: 4px; height: 8px; border-right: 2px solid #fff; border-bottom: 2px solid #fff;
    transform: rotate(45deg) translate(-1px, -1px); opacity: 0; animation: checkPop 0.3s forwards 0.1s;
}
.rule-item.met span { text-decoration: line-through; opacity: 0.8; }
@keyframes checkPop { to { opacity: 1; transform: rotate(45deg) translate(-1px, -1px) scale(1); } }

.match-stamp {
    position: absolute; right: 55px; top: 50%; transform: translateY(-50%) scale(0);
    width: 24px; height: 24px; background: #22c55e; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); pointer-events: none;
}
.match-stamp svg { width: 14px; height: 14px; }
.match-stamp.active { transform: translateY(-50%) scale(1); }

.btn-ultra-submit {
    width: 100%; background: #0f172a; color: #fff; border: none; padding: 20px; border-radius: 16px;
    font-size: 16px; font-weight: 800; letter-spacing: 0.5px; cursor: pointer; position: relative; overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s; margin-top: 15px; display: flex; align-items: center; justify-content: center;
}
.btn-ultra-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(15,23,42,0.2); }
.btn-ultra-submit:active { transform: translateY(0); }

.btn-glow {
    position: absolute; width: 100px; height: 100px; background: radial-gradient(circle, rgba(255,255,255,0.3), transparent 70%);
    border-radius: 50%; pointer-events: none; opacity: 0; transition: opacity 0.3s; transform: translate(-50%, -50%); mix-blend-mode: overlay;
}
.btn-ultra-submit:hover .btn-glow { opacity: 1; }

.btn-spinner {
    display: none; width: 22px; height: 22px; border: 3px solid rgba(255,255,255,0.3); border-top-color: #fff;
    border-radius: 50%; animation: spinUltra 0.8s cubic-bezier(0.6, 0.2, 0.4, 0.8) infinite;
}
.is-loading .btn-text { display: none; }
.is-loading .btn-spinner { display: block; }
@keyframes spinUltra { to { transform: rotate(360deg); } }

@media (max-width: 900px) { .profile-sidebar { position: static; margin-bottom: 40px; } .side-nav-transparent::before, .nav-tracker { display: none; } }
@media (max-width: 768px) { .card-content { padding: 30px 25px; } .spotlight-card { background-image: none; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const nav = document.getElementById('luxury-side-nav');
    const tracker = document.getElementById('nav-tracker');
    const links = document.querySelectorAll('.side-nav-transparent .nav-link');
    const activeLink = document.querySelector('.side-nav-transparent .nav-link.active');

    function moveTracker(targetEl) {
        if (!targetEl || !nav || !tracker) return;
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

    const card = document.getElementById('spotlight-card');
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        card.style.setProperty('--mouse-x', `${x}px`);
        card.style.setProperty('--mouse-y', `${y}px`);
    });

    const btn = document.getElementById('submit-btn');
    const glow = btn.querySelector('.btn-glow');
    btn.addEventListener('mousemove', (e) => {
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        glow.style.left = `${x}px`;
        glow.style.top = `${y}px`;
    });

});

function togglePwd(inputId, btnEl) {
    const input = document.getElementById(inputId);
    const svg = btnEl.querySelector('svg');
    if (input.type === "password") {
        input.type = "text";
        svg.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        btnEl.style.color = '#ff8002';
    } else {
        input.type = "password";
        svg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        btnEl.style.color = '#94a3b8';
    }
}

function checkPasswordRules() {
    const pwd = document.getElementById('new_pwd').value;

    const ruleLen = document.getElementById('rule-len');
    if (pwd.length >= 8) ruleLen.classList.add('met');
    else ruleLen.classList.remove('met');

    const ruleUp = document.getElementById('rule-up');
    if (/[A-Z]/.test(pwd)) ruleUp.classList.add('met');
    else ruleUp.classList.remove('met');

    const ruleNum = document.getElementById('rule-num');
    if (/[0-9]/.test(pwd)) ruleNum.classList.add('met');
    else ruleNum.classList.remove('met');

    checkMatchUltra();
}

function checkMatchUltra() {
    const newPwd = document.getElementById('new_pwd').value;
    const confPwd = document.getElementById('confirm_pwd').value;
    const stamp = document.getElementById('match-stamp');

    if (confPwd.length > 0 && newPwd === confPwd) {
        stamp.classList.add('active');
    } else {
        stamp.classList.remove('active');
    }
}

document.getElementById('passwordForm').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.classList.add('is-loading');
    btn.style.pointerEvents = 'none';
});
</script>

<?php include 'includes/footer.php'; ?>
