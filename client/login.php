<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$error = "";
$success = "";

if(isset($_GET['msg']) && $_GET['msg'] == 'registered') {
    $success = "IDENTITY PROTOCOL INITIATED. PROCEED TO AUTHENTICATE.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';

    if (empty($email) || empty($pass)) {
        $error = "MISSING DATA: ALL FIELDS ARE REQUIRED.";
    } else {
        if ($email === 'admin123@gmail.com' && $pass === 'admin123') {
            $_SESSION['user_id'] = 9999;
            $_SESSION['user_name'] = 'Admin';
            $_SESSION['is_admin'] = true;
            header("Location: admin/dashboard.php");
            exit();
        }

        $res = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

        if ($user = mysqli_fetch_assoc($res)) {
            if (password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['profile_image'] = !empty($user['profile_image']) ? $user['profile_image'] : 'default-avatar.png';
                header("Location: index.php");
                exit();
            }
        }

        $error = "COORDINATES UNRECOGNIZED IN THE ARCHIVE.";
    }
}

$bg_image = 'assets/images/background.png';
$bg_v = file_exists($bg_image) ? filemtime($bg_image) : time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/images/main-logo.png">
    <title>Gateway - FAIFA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <style>
        /* ==============================================
           🎨 THE ASYMMETRIC EDITORIAL (ULTIMATE LOGIN)
           ============================================== */
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #111;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .editorial-split {
            display: flex;
            min-height: 100vh;
            width: 100vw;
        }

        .split-visual {
            width: 45%;
            position: relative;
            overflow: hidden;
            background: #111;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .split-visual img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;

            filter: grayscale(60%) sepia(20%) contrast(1.1) brightness(0.8);

            animation: breatheZoom 30s ease-in-out infinite alternate;
        }
        @keyframes breatheZoom { 0% { transform: scale(1); } 100% { transform: scale(1.1); } }

        .visual-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(17,17,17,0) 0%, rgba(17,17,17,0.4) 100%);
            z-index: 1;
        }
        .visual-meta {
            position: absolute;
            bottom: 40px;
            left: 40px;
            z-index: 2;
            color: #fff;
        }
        .v-logo { font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; margin: 0 0 5px 0; letter-spacing: -1px; }
        .v-tag { font-family: monospace; font-size: 9px; letter-spacing: 4px; opacity: 0.7; }
        .v-vertical {
            position: absolute; top: 40px; right: 40px; z-index: 2;
            font-family: monospace; font-size: 9px; letter-spacing: 4px; color: #fff; opacity: 0.5;
            writing-mode: vertical-rl; transform: rotate(180deg); text-transform: uppercase;
        }

        .split-interaction {
            width: 55%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 8% 10%;
            position: relative;
        }

        .btn-return {
            position: absolute; top: 40px; right: 50px;
            font-family: monospace; font-size: 10px; font-weight: 700; letter-spacing: 2px;
            color: #888; text-decoration: none; transition: color 0.3s;
        }
        .btn-return:hover { color: #111; }

        .stagger-in { opacity: 0; transform: translateY(20px); animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .stagger-in:nth-child(1) { animation-delay: 0.1s; }
        .stagger-in:nth-child(2) { animation-delay: 0.2s; }
        .stagger-in:nth-child(3) { animation-delay: 0.3s; }
        .stagger-in:nth-child(4) { animation-delay: 0.4s; }
        .stagger-in:nth-child(5) { animation-delay: 0.5s; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .form-header { margin-bottom: 60px; }
        .form-meta { font-family: monospace; font-size: 10px; font-weight: 700; color: #ff8002; letter-spacing: 3px; display: block; margin-bottom: 15px; }
        .form-title { font-family: 'Playfair Display', serif; font-size: clamp(48px, 6vw, 80px); font-weight: 400; line-height: 0.9; margin: 0; letter-spacing: -2px; }

        .alert-bar { margin-bottom: 40px; font-family: monospace; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; padding: 15px 20px; display: flex; align-items: flex-start; gap: 10px; border: 1px solid transparent; }
        .alert-error { border-color: #ef4444; color: #ef4444; animation: shake 0.4s ease; }
        .alert-success { border-color: #111; color: #111; }

        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }

        .auth-form { display: flex; flex-direction: column; gap: 50px; }

        .input-group { position: relative; display: flex; flex-direction: column; }

        .macro-label {
            font-family: monospace; font-size: 10px; font-weight: 700; color: #888; letter-spacing: 2px;
            margin-bottom: 10px; display: flex; justify-content: space-between; align-items: baseline;
            transition: color 0.3s;
        }

        .macro-input {
            width: 100%; border: none; background: transparent; outline: none;
            border-bottom: 1px solid #e5e5e5; padding: 10px 0 20px 0;

            font-family: 'Playfair Display', serif; font-size: clamp(28px, 4vw, 40px); font-style: italic; color: #111;
            caret-color: #ff8002; transition: all 0.4s ease;
        }
        .macro-input::placeholder { color: #f1f5f9; font-style: normal; font-family: 'Inter', sans-serif; font-weight: 300; }

        .macro-input:focus { border-bottom-color: #111; transform: translateY(-3px); }
        .input-group:focus-within .macro-label { color: #111; }

        .btn-reveal { background: transparent; border: none; font-family: monospace; font-size: 9px; font-weight: 800; color: #888; cursor: pointer; letter-spacing: 2px; padding: 0; transition: color 0.3s; }
        .btn-reveal:hover { color: #ff8002; }

        .form-actions { margin-top: 20px; display: flex; flex-direction: column; gap: 30px; }

        .btn-auth-macro {
            width: 100%; background: #111; color: #fff; border: none; padding: 25px 0;
            font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 4px; text-transform: uppercase;
            cursor: pointer; position: relative; overflow: hidden; display: flex; justify-content: center; align-items: center; gap: 15px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-arrow { font-size: 16px; transition: transform 0.4s ease; }
        .btn-auth-macro:hover { background: #ff8002; box-shadow: 0 20px 40px rgba(255, 128, 2, 0.2); }
        .btn-auth-macro:hover .btn-arrow { transform: translateX(8px); }

        .btn-spinner { display: none; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }
        .is-loading .btn-text { content: 'AUTHORIZING'; }
        .is-loading .btn-arrow { display: none; }
        .is-loading .btn-spinner { display: block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .link-register {
            text-align: center; font-family: monospace; font-size: 10px; font-weight: 600; color: #888; letter-spacing: 2px; text-decoration: none;
            transition: color 0.3s;
        }
        .link-register span { border-bottom: 1px solid #111; color: #111; padding-bottom: 2px; margin-left: 5px; transition: color 0.3s, border-color 0.3s;}
        .link-register:hover span { color: #ff8002; border-color: #ff8002; }

        @media (max-width: 1024px) {
            .split-visual { width: 35%; }
            .split-interaction { width: 65%; padding: 8% 8%; }
        }

        @media (max-width: 768px) {
            .editorial-split { flex-direction: column; }
            .split-visual { display: none; }
            .split-interaction { width: 100%; padding: 40px 30px; min-height: 100vh; justify-content: flex-start; padding-top: 100px; }
            .btn-return { top: 30px; right: 30px; }
            .form-header { margin-bottom: 50px; }
        }
    </style>
</head>
<body>

    <div class="editorial-split">

        <aside class="split-visual">
            <img src="<?php echo $bg_image; ?>?v=<?php echo $bg_v; ?>" alt="Campaign Archive">
            <div class="visual-overlay"></div>
            <div class="v-vertical">SECURE PROTOCOL // 256-BIT</div>
            <div class="visual-meta">
                <h2 class="v-logo">FAIFA.</h2>
                <span class="v-tag">ARCHIVE // ACCESS</span>
            </div>
        </aside>

        <main class="split-interaction">

            <a href="index.php" class="btn-return stagger-in">RETURN ↗</a>

            <div class="form-header stagger-in">
                <span class="form-meta">IDENTITY PORTAL</span>
                <h1 class="form-title">Gateway.</h1>
            </div>

            <?php if($error): ?>
                <div class="alert-bar alert-error stagger-in" id="php-alert">
                    <span>✕</span>
                    <span id="php-alert-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert-bar alert-success stagger-in">
                    <span>✦</span>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <div class="alert-bar alert-error" id="js-alert" style="display: none;">
                <span>✕</span>
                <span id="js-alert-msg"></span>
            </div>

            <form action="login.php" method="POST" class="auth-form" id="authForm">

                <div class="input-group stagger-in">
                    <label for="l_email" class="macro-label">ELECTRONIC MAIL</label>
                    <input type="email" id="l_email" name="email" class="macro-input" placeholder="name@domain.com" autocomplete="off" spellcheck="false">
                </div>

                <div class="input-group stagger-in">
                    <label for="l_pass" class="macro-label">
                        SECURITY KEY
                        <button type="button" class="btn-reveal" id="revealBtn" tabindex="-1">[ REVEAL ]</button>
                    </label>
                    <input type="password" id="l_pass" name="password" class="macro-input" placeholder="••••••••" autocomplete="off">
                </div>

                <div class="form-actions stagger-in">
                    <button type="submit" class="btn-auth-macro" id="authBtn">
                        <span class="btn-text">AUTHORIZE</span>
                        <span class="btn-arrow">→</span>
                        <div class="btn-spinner"></div>
                    </button>

                    <a href="register.php" class="link-register">NEW TO THE ARCHIVE? <span>INITIATE PROTOCOL</span></a>
                </div>

            </form>
        </main>

    </div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById('authForm');
        const btn = document.getElementById('authBtn');
        const btnText = btn.querySelector('.btn-text');
        const emailInput = document.getElementById('l_email');
        const passInput = document.getElementById('l_pass');
        const jsAlert = document.getElementById('js-alert');
        const jsAlertMsg = document.getElementById('js-alert-msg');
        const phpAlert = document.getElementById('php-alert');

        if(form && btn) {
            form.addEventListener('submit', (e) => {
                let emailVal = emailInput.value.trim();
                let passVal = passInput.value.trim();

                if (emailVal === '' || passVal === '') {
                    e.preventDefault();
                    if (phpAlert) phpAlert.style.display = 'none';
                    jsAlertMsg.textContent = "CREDENTIALS MISSING. PLEASE FILL IN ALL FIELDS.";
                    jsAlert.style.display = 'flex';

                    btn.style.transform = 'translateY(0)';
                    btn.style.background = '#ef4444';
                    setTimeout(() => { btn.style.background = '#111'; }, 500);
                    return;
                }

                btn.classList.add('is-loading');
                btn.style.pointerEvents = 'none';
                btnText.textContent = 'VERIFYING';
                jsAlert.style.display = 'none';
            });
        }

        const revealBtn = document.getElementById('revealBtn');

        if(passInput && revealBtn) {
            revealBtn.addEventListener('click', () => {
                if(passInput.type === 'password') {
                    passInput.type = 'text';
                    revealBtn.textContent = '[ CONCEAL ]';
                    revealBtn.style.color = '#ff8002';
                } else {
                    passInput.type = 'password';
                    revealBtn.textContent = '[ REVEAL ]';
                    revealBtn.style.color = '#888';
                }
            });
        }
    });
</script>

</body>
</html>
