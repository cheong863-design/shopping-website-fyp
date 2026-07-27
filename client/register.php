<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['full_name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($pass)) {
        $error = "All fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($pass) < 8) {
        $error = "Security key must exceed 8 characters.";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "This entity is already registered in the archive.";
        } else {
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (full_name, email, password)
                    VALUES ('$name', '$email', '$hashed_pass')";

            if (mysqli_query($conn, $sql)) {
                header("Location: login.php?msg=registered");
                exit();
            } else {
                $error = "System Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Establish Dossier - FAIFA</title>
    <link rel="icon" type="image/png" href="assets/images/faifa-logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <style>
        /* ==============================================
           🎨 THE MEMBERSHIP CONTRACT (EDITORIAL SPLIT)
           ============================================== */
        body { margin: 0; padding: 0; background: #fdfcfb; color: #111; font-family: 'Inter', sans-serif; overflow-x: hidden; }

        .split-gateway { display: flex; min-height: 100vh; width: 100vw; }

        .gateway-visual {
            width: 45%; position: relative; overflow: hidden;
            background: #111; display: flex; flex-direction: column; justify-content: space-between;
        }

        .visual-img {
            position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
            filter: grayscale(100%) contrast(1.1); opacity: 0.85;
            transform: scale(1.05); animation: breathZoom 30s ease-in-out infinite alternate;
        }
        @keyframes breathZoom { 0% { transform: scale(1.05); } 100% { transform: scale(1.15); } }

        .visual-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.6) 100%); mix-blend-mode: multiply; }

        .visual-brand { position: relative; z-index: 10; padding: 40px 50px; font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 800; color: #fff; letter-spacing: -1px; text-decoration: none; display: inline-block;}
        .visual-quote { position: relative; z-index: 10; padding: 40px 50px; font-family: monospace; font-size: 10px; color: #aaa; letter-spacing: 2px; line-height: 1.6; max-width: 80%; }

        .gateway-form-area {
            width: 55%; display: flex; flex-direction: column; justify-content: center;
            padding: 60px 8vw; box-sizing: border-box; position: relative;
            background: #fdfcfb;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.03'/%3E%3C/svg%3E");
        }

        .btn-nav-back { position: absolute; top: 40px; right: 6vw; font-family: monospace; font-size: 10px; font-weight: 800; color: #888; text-decoration: none; letter-spacing: 2px; transition: 0.3s; display: flex; align-items: center; gap: 8px;}
        .btn-nav-back:hover { color: #111; }
        .btn-nav-back span { transition: transform 0.3s; }
        .btn-nav-back:hover span { transform: translateX(4px); color: #ff8002; }

        .stagger-in { opacity: 0; transform: translateY(20px); animation: softRise 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--stagger) * 0.1s); }
        @keyframes softRise { to { opacity: 1; transform: translateY(0); } }

        .form-header { margin-bottom: 60px; }
        .macro-title { font-family: 'Playfair Display', serif; font-size: clamp(42px, 5vw, 64px); font-style: italic; font-weight: 400; margin: 0 0 10px 0; color: #111; line-height: 1; letter-spacing: -1px; }
        .mono-sub { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 2px; margin: 0; text-transform: uppercase; }

        .error-plaque {
            margin-bottom: 30px; padding: 15px 0; border-bottom: 1px solid #ef4444;
            font-family: monospace; font-size: 10px; font-weight: 800; color: #ef4444; letter-spacing: 1px;
            display: flex; align-items: center; gap: 10px; animation: shake 0.4s ease;
        }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }

        .contract-form { display: flex; flex-direction: column; gap: 45px; }

        .input-group { display: flex; flex-direction: column; position: relative; }
        .ghost-label {
            font-family: monospace; font-size: 9px; font-weight: 800; color: #888;
            letter-spacing: 2px; margin-bottom: 5px; transition: color 0.3s;
            display: flex; justify-content: space-between; align-items: baseline;
        }

        .ghost-input {
            width: 100%; background: transparent; border: none; outline: none;
            border-bottom: 1px dashed #d1d5db; padding: 10px 0;
            font-family: 'Playfair Display', serif; font-size: 26px; font-style: italic; color: #111;
            transition: all 0.4s; caret-color: #ff8002;
        }
        .ghost-input::placeholder { color: #e5e5e5; font-family: 'Inter', sans-serif; font-style: normal; font-weight: 300; font-size: 16px; }
        .ghost-input:focus { border-bottom-color: #111; border-bottom-style: solid; }
        .input-group:focus-within .ghost-label { color: #111; }

        input[type="password"].ghost-input { font-family: 'Inter', sans-serif; font-style: normal; font-weight: 800; font-size: 24px; letter-spacing: 4px; }

        .btn-toggle-pass { background: none; border: none; font-family: monospace; font-size: 9px; font-weight: 800; color: #888; cursor: pointer; padding: 0; transition: 0.3s; }
        .btn-toggle-pass:hover { color: #111; }

        .btn-imprint {
            background: #111; color: #fff; border: none; padding: 25px 0; margin-top: 10px;
            font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 3px;
            cursor: pointer; position: relative; overflow: hidden; display: flex; justify-content: center; align-items: center; gap: 15px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-imprint:hover { background: #ff8002; box-shadow: 0 15px 30px rgba(255, 128, 2, 0.2); transform: translateY(-2px);}
        .btn-imprint.is-loading { pointer-events: none; background: #e5e5e5; color: #888; box-shadow: none; transform: translateY(0); }

        .form-footer { margin-top: 30px; font-family: monospace; font-size: 10px; color: #888; letter-spacing: 1px; text-align: left; }
        .form-footer a { color: #111; font-weight: 800; text-decoration: none; border-bottom: 1px solid #111; padding-bottom: 2px; transition: 0.3s; }
        .form-footer a:hover { color: #ff8002; border-color: #ff8002; }

        @media (max-width: 1024px) {
            .split-gateway { flex-direction: column; }
            .gateway-visual { width: 100%; height: 35vh; min-height: 300px; }
            .gateway-form-area { width: 100%; height: auto; padding: 60px 5vw 100px 5vw; }
            .btn-nav-back { top: -20px; right: 5vw; color: #fff; }
            .btn-nav-back:hover { color: #fff; }
        }
        @media (max-width: 768px) {
            .macro-title { font-size: 38px; }
            .ghost-input { font-size: 22px; }
        }
    </style>
</head>
<body>

    <div class="split-gateway">
        <div class="gateway-visual">
            <img src="assets/images/about2.jpg" alt="FAIFA Archive" class="visual-img" onerror="this.src='https://placehold.co/1000x1200/111/333?text=ARCHIVE'">
            <div class="visual-overlay"></div>

            <a href="index.php" class="visual-brand">FAIFA.</a>
            <div class="visual-quote">ESTABLISH YOUR IDENTITY WITHIN THE ARCHIVE. GAIN ACCESS TO CLASSIFIED SILHOUETTES AND EXPEDITED DISPATCH.</div>
        </div>

        <div class="gateway-form-area">
            <a href="login.php" class="btn-nav-back">AUTHENTICATE <span>→</span></a>

            <div class="form-header stagger-in" style="--stagger: 1">
                <h1 class="macro-title">Establish Dossier.</h1>
                <p class="mono-sub">INITIATE YOUR SECURE PROFILE</p>
            </div>

            <?php if($error): ?>
                <div class="error-plaque stagger-in" style="--stagger: 2">
                    <span>✕ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="contract-form" id="register-form">

                <div class="input-group stagger-in" style="--stagger: 3">
                    <label class="ghost-label">LEGAL ENTITY / SIGNATURE</label>
                    <input type="text" name="full_name" class="ghost-input" placeholder="John Doe" autocomplete="off" spellcheck="false" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>

                <div class="input-group stagger-in" style="--stagger: 4">
                    <label class="ghost-label">COMMUNICATION UPLINK (EMAIL)</label>
                    <input type="text" name="email" class="ghost-input" placeholder="name@domain.com" autocomplete="off" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="input-group stagger-in" style="--stagger: 5">
                    <div class="ghost-label">
                        <span>SECURITY KEY</span>
                        <button type="button" class="btn-toggle-pass" id="toggle-pass">[ SHOW ]</button>
                    </div>
                    <input type="password" name="password" id="pass-input" class="ghost-input" placeholder="Min. 8 characters">
                </div>

                <button type="submit" class="btn-imprint stagger-in" style="--stagger: 6" id="btn-submit">
                    <span class="btn-text">[ AUTHORIZE IDENTITY ]</span>
                </button>

                <p class="form-footer stagger-in" style="--stagger: 7">
                    ALREADY ESTABLISHED? <a href="login.php">INITIATE LOGIN</a>
                </p>
            </form>
        </div>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const passInput = document.getElementById('pass-input');
        const toggleBtn = document.getElementById('toggle-pass');

        if(toggleBtn && passInput) {
            toggleBtn.addEventListener('click', () => {
                if (passInput.type === 'password') {
                    passInput.type = 'text';
                    passInput.style.fontFamily = "'Playfair Display', serif";
                    passInput.style.fontStyle = "italic";
                    passInput.style.letterSpacing = "normal";
                    toggleBtn.innerText = '[ HIDE ]';
                    toggleBtn.style.color = '#111';
                } else {
                    passInput.type = 'password';
                    passInput.style.fontFamily = "'Inter', sans-serif";
                    passInput.style.fontStyle = "normal";
                    passInput.style.letterSpacing = "4px";
                    toggleBtn.innerText = '[ SHOW ]';
                    toggleBtn.style.color = '#888';
                }
            });
        }

        const form = document.getElementById('register-form');
        const btn = document.getElementById('btn-submit');
        const btnText = btn.querySelector('.btn-text');

        if(form && btn) {
            form.addEventListener('submit', () => {
                btn.classList.add('is-loading');
                btnText.textContent = '[ ENCRYPTING... ]';
            });
        }
    });
</script>

</body>
</html>
