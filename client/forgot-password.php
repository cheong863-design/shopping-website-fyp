<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';
include 'includes/db.php';

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $res = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");

    if (mysqli_num_rows($res) > 0) {
        $message = "DISPATCHED.";
    } else {
        $error = "COORDINATES UNRECOGNIZED.";
    }
}
?>

<main class="terminal-void-page">
    <div class="terminal-split-layout">

        <aside class="terminal-meta-sidebar fade-in">
            <div class="sidebar-top">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span class="meta-tag">SYS // RECOVERY</span>
            </div>

            <div class="vertical-text-wrap">
                <span class="vertical-text">IDENTITY OVERRIDE PROTOCOL</span>
            </div>

            <div class="sidebar-bottom">
                <span class="meta-tag">KUALA LUMPUR HQ</span>
                <span class="meta-tag">V. 2.0.4</span>
            </div>
        </aside>

        <section class="terminal-interaction-area">

            <div class="interaction-inner">
                <?php if($message): ?>
                    <div class="evaporate-success fade-up">
                        <h1 class="success-massive"><?php echo $message; ?></h1>
                        <p class="success-sub">A SECURE LINK HAS BEEN TRANSMITTED TO YOUR INBOX.<br>PLEASE FOLLOW THE ENCLOSED DIRECTIVES.</p>
                        <a href="login.php" class="btn-return-void">RETURN TO GATEWAY <span>↗</span></a>
                    </div>
                <?php else: ?>

                    <div class="terminal-form-block fade-up" style="--delay: 1">

                        <div class="form-header">
                            <h1 class="serif-huge">Identify.</h1>
                            <p class="mono-instruction">PROVIDE YOUR REGISTERED ELECTRONIC MAIL TO INITIATE RECOVERY.</p>
                        </div>

                        <?php if($error): ?>
                            <div class="terminal-error pulse-error">
                                <span>✕ <?php echo $error; ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="forgot-password.php" method="POST" class="macro-form" id="recoveryForm">

                            <div class="macro-input-group stagger-in" style="--stagger: 2">
                                <label for="void_email" class="macro-label">ENTER COORDINATES</label>
                                <input type="email" id="void_email" name="email" class="macro-input" placeholder="name@domain.com" required autocomplete="off" spellcheck="false">
                                <div class="macro-line"></div>
                            </div>

                            <div class="macro-action stagger-in" style="--stagger: 3">
                                <button type="submit" class="btn-kinetic-submit" id="submitBtn">
                                    <span class="btn-text">TRANSMIT</span>
                                    <div class="kinetic-line"></div>
                                    <span class="btn-arrow">→</span>
                                    <div class="btn-spinner"></div>
                                </button>

                                <a href="login.php" class="link-abort">ABORT PROTOCOL</a>
                            </div>

                        </form>
                    </div>

                <?php endif; ?>
            </div>

        </section>

    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 THE TERMINAL VOID (ULTIMATE HAUTE COUTURE)
   ============================================== */

.terminal-void-page {
    background: #fff;
    color: #111;
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    width: 100%;
    margin-top: 0;
    display: flex;
    flex-direction: column;
}

.terminal-split-layout {
    display: grid;
    grid-template-columns: 80px 1fr;
    min-height: 100vh;
    width: 100%;
}

.terminal-meta-sidebar {
    background: #f8fafc;
    border-right: 1px solid #e5e5e5;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    padding: 40px 0;
    position: sticky;
    top: 0;
    height: 100vh;
}

.sidebar-top, .sidebar-bottom {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

.sidebar-top svg { width: 18px; height: 18px; color: #111; }

.meta-tag {
    font-family: monospace;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 2px;
    color: #888;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    text-transform: uppercase;
}

.vertical-text-wrap { flex-grow: 1; display: flex; align-items: center; justify-content: center; }
.vertical-text {
    font-family: monospace;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 6px;
    color: #111;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    white-space: nowrap;
}

.terminal-interaction-area {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 8%;
    background: #fff;
}

.interaction-inner {
    width: 100%;
    max-width: 900px;
}

.fade-in { opacity: 0; animation: simpleFade 1.5s ease forwards; }
.fade-up { opacity: 0; animation: voidUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) calc(var(--delay) * 0.15s) forwards; }
.stagger-in { opacity: 0; transform: translateY(20px); animation: staggerFade 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.stagger-in:nth-child(1) { animation-delay: 0.3s; }
.stagger-in:nth-child(2) { animation-delay: 0.4s; }
.stagger-in:nth-child(3) { animation-delay: 0.5s; }

@keyframes simpleFade { to { opacity: 1; } }
@keyframes voidUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
@keyframes staggerFade { to { opacity: 1; transform: translateY(0); } }

.form-header { margin-bottom: 80px; }

.serif-huge {
    font-family: 'Playfair Display', serif;
    font-size: clamp(60px, 10vw, 120px);
    font-style: italic;
    font-weight: 400;
    line-height: 0.9;
    letter-spacing: -2px;
    margin: 0 0 20px 0;
    color: #111;
}

.mono-instruction {
    font-family: monospace;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 2px;
    color: #888;
    margin: 0;
}

.terminal-error {
    margin-bottom: 40px;
    font-family: monospace;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2px;
    color: #ef4444;
}
.pulse-error { animation: textPulse 2s infinite; }
@keyframes textPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

.macro-form {
    display: flex;
    flex-direction: column;
    gap: 60px;
}

.macro-input-group {
    display: flex;
    flex-direction: column;
    position: relative;
}

.macro-label {
    font-family: monospace;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 3px;
    color: #111;
    margin-bottom: 20px;
    transition: color 0.4s;
}

.macro-input {
    width: 100%;
    background: transparent;
    border: none;
    outline: none;

    font-family: 'Playfair Display', serif;
    font-size: clamp(36px, 6vw, 72px);
    font-style: italic;
    color: #111;
    padding-bottom: 20px;

    caret-color: #ff8002;
    transition: color 0.4s ease;
}

.macro-input::placeholder {
    color: #e5e5e5;
    font-style: normal;
    font-family: 'Inter', sans-serif;
    font-weight: 300;
}

.macro-line {
    width: 100%;
    height: 2px;
    background: #e5e5e5;
    position: relative;
}
.macro-line::after {
    content: '';
    position: absolute;
    left: 0; top: 0; width: 100%; height: 100%;
    background: #111;
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.macro-input:focus ~ .macro-line::after {
    transform: scaleX(1);
    transform-origin: left;
}
.macro-input:focus { color: #ff8002; }

.macro-action {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 20px;
}

.btn-kinetic-submit {
    display: inline-flex;
    align-items: center;
    gap: 20px;
    background: transparent;
    border: none;
    padding: 0;
    color: #111;
    cursor: pointer;
    font-family: monospace;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 4px;
    position: relative;
}

.kinetic-line {
    width: 60px;
    height: 1px;
    background: #111;
    transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1), background 0.3s;
}

.btn-kinetic-submit .btn-arrow {
    font-size: 20px;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), color 0.3s;
}

.btn-kinetic-submit:hover { color: #ff8002; }
.btn-kinetic-submit:hover .kinetic-line { width: 120px; background: #ff8002; }
.btn-kinetic-submit:hover .btn-arrow { transform: translateX(10px); color: #ff8002; }

/* Spinner */
.btn-spinner {
    display: none; width: 18px; height: 18px; border: 2px solid #e5e5e5; border-top-color: #ff8002;
    border-radius: 50%; animation: spin 0.8s linear infinite; position: absolute; right: 0;
}
.is-loading .btn-arrow { opacity: 0; }
.is-loading .kinetic-line { width: 20px; background: #ff8002; }
.is-loading .btn-spinner { display: block; }
@keyframes spin { to { transform: rotate(360deg); } }

.link-abort {
    font-family: monospace;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 2px;
    color: #888;
    text-decoration: none;
    transition: color 0.3s;
}
.link-abort:hover { color: #ef4444; }

.evaporate-success {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.success-massive {
    font-family: 'Playfair Display', serif;
    font-size: clamp(60px, 12vw, 150px);
    font-weight: 400;
    font-style: italic;
    color: #111;
    margin: 0 0 30px 0;
    line-height: 0.9;
    letter-spacing: -2px;
}

.success-sub {
    font-family: monospace;
    font-size: 11px;
    font-weight: 600;
    color: #888;
    letter-spacing: 2px;
    line-height: 1.8;
    margin: 0 0 60px 0;
}

.btn-return-void {
    display: inline-flex;
    align-items: center;
    gap: 15px;
    padding: 20px 30px;
    background: #111;
    color: #fff;
    text-decoration: none;
    font-family: monospace;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 3px;
    transition: all 0.4s ease;
}
.btn-return-void span { transition: transform 0.4s; }
.btn-return-void:hover { background: #ff8002; }
.btn-return-void:hover span { transform: translate(4px, -4px); }

@media (max-width: 900px) {
    .terminal-split-layout { grid-template-columns: 1fr; }
    .terminal-meta-sidebar { display: none;  }
    .terminal-interaction-area { padding: 60px 20px; align-items: flex-start; }
    .macro-action { flex-direction: column; align-items: flex-start; gap: 40px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('recoveryForm');
    const btn = document.getElementById('submitBtn');

    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.classList.add('is-loading');
            btn.style.pointerEvents = 'none';
            btn.querySelector('.btn-text').textContent = 'AUTHORIZING';
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
