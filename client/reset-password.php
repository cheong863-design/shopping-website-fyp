<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$message = "";
$error = "";

$token = isset($_GET['token']) ? mysqli_real_escape_string($conn, $_GET['token']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass !== $confirm_pass) {
        $error = "SEQUENCES DO NOT ALIGN. PLEASE VERIFY.";
    } elseif (strlen($new_pass) < 8) {
        $error = "KEY MUST EXCEED 8 CHARACTERS FOR ARCHIVE SECURITY.";
    } else {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        // mysqli_query($conn, "UPDATE users SET password = '$hashed_pass' WHERE id = '$user_id'");

        $message = "IDENTITY RESTORED";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Identity Recovery - FAIFA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <style>
        /* ==============================================
           🎨 THE REDACTED DOSSIER (QUIET LUXURY OVERRIDE)
           ============================================== */
        body { margin: 0; padding: 0; background: #fdfcfb; color: #111; font-family: 'Inter', sans-serif; overflow-x: hidden; }

        .split-gateway { display: flex; min-height: 100vh; width: 100vw; }

        .gateway-visual {
            width: 45%; position: relative; overflow: hidden;
            background: #111; display: flex; flex-direction: column; justify-content: space-between;
        }

        .visual-img {
            position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
            filter: grayscale(100%) contrast(1.2) brightness(0.8);
            transform: scale(1.05); animation: breathZoom 30s ease-in-out infinite alternate;
        }
        @keyframes breathZoom { 0% { transform: scale(1.05); } 100% { transform: scale(1.15); } }

        .redact-bar {
            position: absolute; background: #111; z-index: 5;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        .redact-1 { top: 30%; left: 10%; width: 40%; height: 8vh; transform: rotate(-2deg); }
        .redact-2 { top: 45%; right: -5%; width: 60%; height: 12vh; transform: rotate(1deg); }
        .redact-3 { bottom: 20%; left: -10%; width: 80%; height: 10vh; transform: rotate(-1deg); }

        .visual-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.8) 100%); z-index: 1;}

        .visual-brand { position: relative; z-index: 10; padding: 40px 50px; font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 800; color: #fff; letter-spacing: -1px; text-decoration: none; display: inline-block;}
        .visual-quote { position: relative; z-index: 10; padding: 40px 50px; font-family: monospace; font-size: 10px; color: #888; letter-spacing: 2px; line-height: 1.6; max-width: 80%; }
        .visual-quote strong { color: #fff; background: #111; padding: 0 4px; }

        .gateway-form-area {
            width: 55%; display: flex; flex-direction: column; justify-content: center;
            padding: 60px 8vw; box-sizing: border-box; position: relative;
            background: #fdfcfb;

            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.03'/%3E%3C/svg%3E");
        }

        .btn-nav-back { position: absolute; top: 40px; right: 6vw; font-family: monospace; font-size: 10px; font-weight: 800; color: #888; text-decoration: none; letter-spacing: 2px; transition: 0.3s; display: flex; align-items: center; gap: 8px;}
        .btn-nav-back:hover { color: #111; }
        .btn-nav-back span { transition: transform 0.3s; }
        .btn-nav-back:hover span { transform: translateX(-4px); color: #ff8002; }

        .stagger-in { opacity: 0; transform: translateY(20px); animation: softRise 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--stagger) * 0.1s); }
        @keyframes softRise { to { opacity: 1; transform: translateY(0); } }

        .form-header { margin-bottom: 60px; }
        .macro-title { font-family: 'Playfair Display', serif; font-size: clamp(42px, 5vw, 64px); font-style: italic; font-weight: 400; margin: 0 0 10px 0; color: #111; line-height: 1; letter-spacing: -1px; }
        .mono-sub { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 2px; margin: 0; text-transform: uppercase; }

        .error-plaque {
            margin-bottom: 40px; font-family: monospace; font-size: 10px; font-weight: 800; color: #111;
            letter-spacing: 1px; display: flex; align-items: center; gap: 10px;
        }
        .error-plaque::before { content: '[ ERROR ]'; color: #888; }
        .error-plaque span { text-decoration: line-through; text-decoration-color: #111; text-decoration-thickness: 2px; }

        .success-block { display: flex; flex-direction: column; align-items: flex-start; gap: 40px; }

        .stamp-wrapper { position: relative; display: inline-block; }
        .stamp-cleared {
            font-family: 'Playfair Display', serif; font-size: clamp(48px, 6vw, 72px); font-weight: 800; font-style: italic;
            color: #111; margin: 0; line-height: 1; letter-spacing: -2px;

            transform: scale(1.5); opacity: 0;
            animation: stampSlam 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards 0.2s;
        }
        .stamp-line { position: absolute; left: -10%; top: 50%; width: 120%; height: 2px; background: #111; z-index: -1; transform: scaleX(0); animation: expandLine 0.6s ease forwards 0.5s; }

        @keyframes stampSlam { to { transform: scale(1); opacity: 1; } }
        @keyframes expandLine { to { transform: scaleX(1); } }

        .success-desc { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 2px; line-height: 1.8; max-width: 80%; }

        .btn-proceed {
            background: transparent; color: #111; text-decoration: none; padding: 0 0 5px 0;
            font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 3px;
            border-bottom: 2px solid #111; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s;
        }
        .btn-proceed:hover { color: #ff8002; border-color: #ff8002; padding-left: 10px; }

        .contract-form { display: flex; flex-direction: column; gap: 50px; }

        .input-group { display: flex; flex-direction: column; position: relative; }
        .ghost-label {
            font-family: monospace; font-size: 9px; font-weight: 800; color: #888;
            letter-spacing: 2px; margin-bottom: 5px; transition: color 0.3s;
            display: flex; justify-content: space-between; align-items: baseline;
        }

        .ghost-input {
            width: 100%; background: transparent; border: none; outline: none;
            border-bottom: 1px dashed #d1d5db; padding: 10px 0;
            font-family: 'Inter', sans-serif; font-style: normal; font-weight: 800; font-size: 28px; letter-spacing: 6px; color: #111;
            transition: all 0.4s; caret-color: #ff8002;
        }
        .ghost-input::placeholder { color: #e5e5e5; font-family: 'Inter', sans-serif; font-style: normal; font-weight: 300; font-size: 16px; letter-spacing: normal;}
        .ghost-input:focus { border-bottom-color: #111; border-bottom-style: solid; }
        .input-group:focus-within .ghost-label { color: #111; }

        .btn-toggle-pass { background: none; border: none; font-family: monospace; font-size: 9px; font-weight: 800; color: #888; cursor: pointer; padding: 0; transition: 0.3s; }
        .btn-toggle-pass:hover { color: #111; }
        .align-status { font-family: monospace; font-size: 9px; font-weight: 800; color: #888; letter-spacing: 1px; transition: 0.3s; }

        .btn-imprint {
            background: transparent; color: #111; border: none; padding: 0 0 5px 0; margin-top: 20px;
            font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 3px; border-bottom: 2px solid #111;
            cursor: pointer; display: inline-flex; align-items: center; justify-content: flex-start; width: fit-content; gap: 15px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-imprint:hover { color: #ff8002; border-color: #ff8002; padding-left: 10px; }
        .btn-imprint.is-loading { pointer-events: none; color: #888; border-color: #e5e5e5; padding-left: 0; }

        @media (max-width: 1024px) {
            .split-gateway { flex-direction: column; }
            .gateway-visual { width: 100%; height: 30vh; min-height: 250px; }
            .gateway-form-area { width: 100%; height: auto; padding: 60px 5vw 100px 5vw; }
            .btn-nav-back { top: -20px; right: 5vw; color: #fff; }
            .btn-nav-back:hover { color: #fff; }
        }
        @media (max-width: 768px) {
            .macro-title { font-size: 38px; }
            .ghost-input { font-size: 22px; letter-spacing: 3px;}
            .stamp-cleared { font-size: 42px; }
        }
    </style>
</head>
<body>

    <div class="split-gateway">

        <div class="gateway-visual">

            <div class="redact-bar redact-1"></div>
            <div class="redact-bar redact-2"></div>
            <div class="redact-bar redact-3"></div>

            <img src="assets/images/about3.jpg" alt="Secure Archive" class="visual-img" onerror="this.src='https://placehold.co/1000x1200/111/333?text=REDACTED'">
            <div class="visual-overlay"></div>

            <a href="index.php" class="visual-brand">FAIFA.</a>
            <div class="visual-quote">IDENTITY RECOVERY PROTOCOL. YOUR PREVIOUS CREDENTIALS HAVE BEEN <strong>REDACTED</strong>. PLEASE ENGRAVE A NEW SEQUENCE TO REGAIN ACCESS.</div>
        </div>

        <div class="gateway-form-area">
            <a href="login.php" class="btn-nav-back"><span>←</span> RETURN TO LOGIN</a>

            <?php if($message): ?>
                <div class="success-block stagger-in" style="--stagger: 1">
                    <div class="stamp-wrapper">
                        <div class="stamp-line"></div>
                        <h2 class="stamp-cleared"><?php echo htmlspecialchars($message); ?>.</h2>
                    </div>
                    <p class="success-desc">THE NEW CRYPTOGRAPHIC SEQUENCE HAS BEEN PERMANENTLY ENGRAVED IN THE ARCHIVE. THE DOSSIER IS NOW UNLOCKED.</p>
                    <a href="login.php" class="btn-proceed">PROCEED TO LOGIN <span>→</span></a>
                </div>

            <?php else: ?>
                <div class="form-header stagger-in" style="--stagger: 1">
                    <h1 class="macro-title">Recovery.</h1>
                    <p class="mono-sub">ENGRAVE NEW SECURITY SEQUENCE</p>
                </div>

                <?php if($error): ?>
                    <div class="error-plaque stagger-in" style="--stagger: 2">
                        <span><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>

                <form action="reset-password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST" class="contract-form" id="recovery-form">

                    <div class="input-group stagger-in" style="--stagger: 3">
                        <div class="ghost-label">
                            <span>NEW SEQUENCE</span>
                            <button type="button" class="btn-toggle-pass" onclick="togglePass('pass1', this)">[ SHOW ]</button>
                        </div>
                        <input type="password" name="password" id="pass1" class="ghost-input" placeholder="Min. 8 characters" required>
                    </div>

                    <div class="input-group stagger-in" style="--stagger: 4">
                        <div class="ghost-label">
                            <span>CONFIRM SEQUENCE</span>
                            <span class="align-status" id="align-status">[ PENDING ]</span>
                        </div>
                        <input type="password" name="confirm_password" id="pass2" class="ghost-input" placeholder="Repeat sequence" required>
                    </div>

                    <button type="submit" class="btn-imprint stagger-in" style="--stagger: 5" id="btn-submit">
                        <span class="btn-text">ENGRAVE SEQUENCE</span>
                    </button>

                </form>
            <?php endif; ?>
        </div>

    </div>

<script>
    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            input.style.fontFamily = "'Playfair Display', serif";
            input.style.fontStyle = "italic";
            input.style.letterSpacing = "normal";
            btn.innerText = '[ HIDE ]';
            btn.style.color = '#111';
        } else {
            input.type = 'password';
            input.style.fontFamily = "'Inter', sans-serif";
            input.style.fontStyle = "normal";
            input.style.letterSpacing = "6px";
            btn.innerText = '[ SHOW ]';
            btn.style.color = '#888';
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const pass1 = document.getElementById('pass1');
        const pass2 = document.getElementById('pass2');
        const alignStatus = document.getElementById('align-status');

        if(pass1 && pass2 && alignStatus) {
            pass2.addEventListener('input', () => {
                if (pass2.value === '') {
                    alignStatus.innerText = '[ PENDING ]';
                    alignStatus.style.color = '#888';
                    alignStatus.style.textDecoration = 'none';
                } else if (pass1.value === pass2.value) {
                    alignStatus.innerText = '[ ALIGNED ]';
                    alignStatus.style.color = '#111';
                    alignStatus.style.textDecoration = 'none';
                } else {
                    alignStatus.innerText = '[ MISMATCH ]';
                    alignStatus.style.color = '#888';
                    alignStatus.style.textDecoration = 'line-through';
                }
            });

            pass1.addEventListener('input', () => {
                if (pass2.value !== '') {
                    if (pass1.value === pass2.value) {
                        alignStatus.innerText = '[ ALIGNED ]';
                        alignStatus.style.color = '#111';
                        alignStatus.style.textDecoration = 'none';
                    } else {
                        alignStatus.innerText = '[ MISMATCH ]';
                        alignStatus.style.color = '#888';
                        alignStatus.style.textDecoration = 'line-through';
                    }
                }
            });
        }

        const form = document.getElementById('recovery-form');
        const btn = document.getElementById('btn-submit');
        const btnText = btn?.querySelector('.btn-text');

        if(form && btn) {
            form.addEventListener('submit', () => {
                btn.classList.add('is-loading');
                btnText.textContent = 'ENGRAVING...';
            });
        }
    });
</script>

</body>
</html>
