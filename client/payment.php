<?php
session_start();
include 'includes/db.php';

$subtotal = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $res = mysqli_query($conn, "SELECT price FROM products WHERE id = " . intval($id));
        $p = mysqli_fetch_assoc($res);
        $subtotal += ($p['price'] * $qty);
    }
}
$total_to_pay = $subtotal * 1.06;

if ($total_to_pay <= 0) {
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/images/main-logo.png">
    <title>Transfer Authorization - FAIFA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <style>
        /* ==============================================
           🎨 THE SWISS VAULT CONTRACT (ULTIMATE PAYMENT)
           ============================================== */
        body {
            margin: 0; padding: 0;
            background: #ffffff;
            color: #111;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; flex-direction: column;
            overflow-x: hidden;
        }

        .gateway-header {
            padding: 30px 40px; border-bottom: 1px dashed #111;
            display: flex; justify-content: space-between; align-items: baseline;
            position: relative; z-index: 10;
        }
        .btn-abort {
            font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 2px;
            color: #888; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 10px;
        }
        .btn-abort:hover { color: #111; }
        .btn-abort:hover span { transform: translateX(-5px); color: #ff8002; }
        .btn-abort span { transition: 0.3s; }

        .sys-lock { font-family: monospace; font-size: 10px; color: #111; font-weight: 800; letter-spacing: 2px; }

        .contract-main {
            flex-grow: 1; display: flex; align-items: center; justify-content: center;
            padding: 40px 20px;
        }

        .contract-matrix {
            width: 100%; max-width: 1000px;
            display: grid; grid-template-columns: 1fr 1fr;
            border: 1px dashed #111;
            position: relative;
        }

        .contract-matrix::before {
            content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 1px;
            border-left: 1px dashed #111; z-index: 0;
        }

        .visual-cell {
            padding: 60px 40px; display: flex; flex-direction: column; justify-content: center;
            background: #fafafa; position: relative; z-index: 1;
        }
        .visual-tag {
            font-family: monospace; font-size: 9px; font-weight: 800; letter-spacing: 3px; color: #888;
            margin-bottom: 40px; text-align: center; display: block; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;
        }

        .card-visualizer-container {
            perspective: 1200px; width: 100%; max-width: 380px; margin: 0 auto;
        }

        .card-inner {
            width: 100%; aspect-ratio: 1.586 / 1; position: relative;
            transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            transform-style: preserve-3d;
            box-shadow: 0 40px 80px rgba(0,0,0,0.15), 0 10px 20px rgba(0,0,0,0.1);
            border-radius: 12px;
        }
        .card-inner.is-flipped { transform: rotateY(180deg); }

        .card-front, .card-back {
            position: absolute; inset: 0; width: 100%; height: 100%;
            -webkit-backface-visibility: hidden; backface-visibility: hidden;
            border-radius: 12px; background: #111;

            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='1.5' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.08'/%3E%3C/svg%3E");
            border: 1px solid #333; overflow: hidden; padding: 25px; box-sizing: border-box;
            display: flex; flex-direction: column; justify-content: space-between;
        }

        .card-front::after, .card-back::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(125deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 40%, rgba(255,255,255,0.02) 100%);
            pointer-events: none;
        }

        .card-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .chip { width: 40px; height: 30px; border: 1px solid #555; border-radius: 4px; background: linear-gradient(135deg, #444, #222); position: relative; }
        .chip::after { content: ''; position: absolute; top: 50%; left: 0; width: 100%; height: 1px; background: #111; }
        .card-logo { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 800; font-style: italic; color: #fff; letter-spacing: -1px; }

        .vis-number {
            font-family: monospace; font-size: 22px; font-weight: 600; letter-spacing: 4px; color: #e5e5e5;
            text-shadow: -1px -1px 0 #000, 1px 1px 0 rgba(255,255,255,0.3);
            margin-top: auto; margin-bottom: 20px;
        }

        .card-bottom { display: flex; justify-content: space-between; align-items: flex-end; }
        .vis-name { font-family: monospace; font-size: 12px; color: #aaa; text-transform: uppercase; letter-spacing: 2px; max-width: 70%; white-space: nowrap; overflow: hidden; }
        .vis-exp { font-family: monospace; font-size: 12px; color: #fff; letter-spacing: 1px; }

        .card-back { transform: rotateY(180deg); padding: 0; }
        .mag-stripe { width: 100%; height: 45px; background: #000; margin-top: 25px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5); }
        .cvv-area { padding: 0 25px; margin-top: 15px; }
        .cvv-label { font-family: monospace; font-size: 9px; color: #888; margin-bottom: 5px; display: block; letter-spacing: 1px;}
        .cvv-strip { width: 100%; height: 35px; background: #fff; display: flex; justify-content: flex-end; align-items: center; padding: 0 15px; box-sizing: border-box; }
        .vis-cvv { font-family: 'Playfair Display', serif; font-size: 16px; color: #111; font-style: italic; font-weight: 800; }

        .form-cell { padding: 60px 50px; position: relative; z-index: 1; display: flex; flex-direction: column; }

        .form-header { margin-bottom: 50px; }
        .serif-title { font-family: 'Playfair Display', serif; font-size: 48px; font-style: italic; font-weight: 400; margin: 0 0 10px 0; color: #111; line-height: 1; letter-spacing: -2px; }
        .mono-subtitle { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 2px; text-transform: uppercase; margin: 0; }

        .checkout-form { display: flex; flex-direction: column; gap: 40px; flex-grow: 1; }

        .input-group { position: relative; }
        .ghost-label {
            font-family: monospace; font-size: 9px; font-weight: 800; color: #888; letter-spacing: 2px;
            margin-bottom: 10px; display: flex; justify-content: space-between; align-items: baseline; transition: 0.3s;
        }

        .ghost-input {
            width: 100%; background: transparent; border: none; outline: none;
            border-bottom: 1px dashed #d1d5db; padding: 5px 0 15px 0;
            font-family: 'Playfair Display', serif; font-size: 28px; font-style: italic; color: #111;
            transition: all 0.4s; caret-color: #ff8002;
        }
        .ghost-input::placeholder { color: #e5e5e5; font-family: 'Inter', sans-serif; font-style: normal; font-weight: 300; }
        .ghost-input:focus { border-bottom-color: #111; border-bottom-style: solid; transform: translateY(-2px); }
        .input-group:focus-within .ghost-label { color: #111; }

        #in-num, #in-exp, #in-cvv { font-family: monospace; font-size: 20px; font-style: normal; font-weight: 600; letter-spacing: 1px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }

        .btn-authorize {
            background: #111; color: #fff; border: none; padding: 25px 0; margin-top: auto;
            font-family: monospace; font-size: 12px; font-weight: 800; letter-spacing: 3px;
            cursor: pointer; position: relative; overflow: hidden; display: flex; justify-content: center; align-items: center; gap: 15px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-authorize:hover { background: #ff8002; box-shadow: 0 15px 30px rgba(255, 128, 2, 0.2); }
        .btn-authorize svg { width: 16px; height: 16px; transition: transform 0.3s; }
        .btn-authorize:hover svg { transform: translateX(8px); }

        .fade-up { opacity: 0; animation: runwayUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--delay) * 0.1s); }
        @keyframes runwayUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 1024px) {
            .contract-matrix { grid-template-columns: 1fr; border-bottom: 1px dashed #111; }
            .contract-matrix::before { display: none; }
            .visual-cell { border-bottom: 1px dashed #111; padding: 40px 20px; }
            .form-cell { padding: 40px 30px; }
        }
        @media (max-width: 768px) {
            .gateway-header { padding: 20px; }
            .contract-main { padding: 20px; }
            .serif-title { font-size: 38px; }
            .form-row { grid-template-columns: 1fr; gap: 30px; }
            .vis-number { font-size: 18px; letter-spacing: 2px; }
        }
    </style>
</head>
<body>

    <header class="gateway-header fade-up" style="--delay: 0">
        <a href="cart.php" class="btn-abort"><span>←</span> RETURN TO BAG</a>
        <span class="sys-lock">AUTHORIZATION TERMINAL</span>
    </header>

    <main class="contract-main">
        <div class="contract-matrix fade-up" style="--delay: 2">

            <div class="visual-cell">
                <span class="visual-tag">SECURE INSTRUMENT</span>

                <div class="card-visualizer-container">
                    <div class="card-inner" id="card-inner">
                        <div class="card-front">
                            <div class="card-top">
                                <div class="chip"></div>
                                <span class="card-logo">FAIFA.</span>
                            </div>
                            <div class="vis-number" id="vis-num">•••• •••• •••• ••••</div>
                            <div class="card-bottom">
                                <span class="vis-name" id="vis-name">SIGNATURE ENTITY</span>
                                <span class="vis-exp" id="vis-exp">MM/YY</span>
                            </div>
                        </div>
                        <div class="card-back">
                            <div class="mag-stripe"></div>
                            <div class="cvv-area">
                                <span class="cvv-label">SECURITY CODE</span>
                                <div class="cvv-strip">
                                    <span class="vis-cvv" id="vis-cvv">•••</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-cell">
                <div class="form-header">
                    <h1 class="serif-title">Clearance.</h1>
                    <p class="mono-subtitle">PROVIDE INSTRUMENT DETAILS TO EXECUTE</p>
                </div>

                <form action="process-order.php" method="POST" class="checkout-form" id="payment-form">

                    <div class="input-group fade-up" style="--delay: 3">
                        <label class="ghost-label">SIGNATURE OF ENTITY</label>
                        <input type="text" id="in-name" name="card_name" class="ghost-input" placeholder="John Doe" required autocomplete="off" spellcheck="false">
                    </div>

                    <div class="input-group fade-up" style="--delay: 4">
                        <label class="ghost-label">INSTRUMENT SEQUENCE</label>
                        <input type="text" id="in-num" name="card_number" class="ghost-input" placeholder="0000 0000 0000 0000" maxlength="19" required autocomplete="off">
                    </div>

                    <div class="form-row fade-up" style="--delay: 5">
                        <div class="input-group">
                            <label class="ghost-label">VALIDITY</label>
                            <input type="text" id="in-exp" name="card_exp" class="ghost-input" placeholder="MM/YY" maxlength="5" required autocomplete="off">
                        </div>
                        <div class="input-group">
                            <label class="ghost-label">CVV / CVC <span style="color:#d1d5db;">(BACK)</span></label>
                            <input type="password" id="in-cvv" name="card_cvv" class="ghost-input" placeholder="•••" maxlength="4" required autocomplete="off">
                        </div>
                    </div>

                    <button type="submit" class="btn-authorize fade-up" style="--delay: 6" id="btn-submit">
                        <span class="btn-text">AUTHORIZE // MYR <?php echo number_format($total_to_pay, 2); ?></span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>

                </form>
            </div>

        </div>
    </main>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        const inName = document.getElementById('in-name');
        const inNum = document.getElementById('in-num');
        const inExp = document.getElementById('in-exp');
        const inCvv = document.getElementById('in-cvv');

        const visName = document.getElementById('vis-name');
        const visNum = document.getElementById('vis-num');
        const visExp = document.getElementById('vis-exp');
        const visCvv = document.getElementById('vis-cvv');

        const cardInner = document.getElementById('card-inner');

        inName.addEventListener('input', (e) => {
            visName.textContent = e.target.value.toUpperCase() || 'SIGNATURE ENTITY';
        });

        inNum.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '');
            let formatted = val.match(/.{1,4}/g)?.join(' ') || '';
            e.target.value = formatted;
            visNum.textContent = formatted || '•••• •••• •••• ••••';
        });

        inExp.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '');
            if (val.length > 2) { val = val.substring(0, 2) + '/' + val.substring(2, 4); }
            e.target.value = val;
            visExp.textContent = val || 'MM/YY';
        });

        inCvv.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '');
            visCvv.textContent = e.target.value.replace(/./g, '•') || '•••';
        });

        inCvv.addEventListener('focus', () => { cardInner.classList.add('is-flipped'); });
        inCvv.addEventListener('blur', () => { cardInner.classList.remove('is-flipped'); });

        const form = document.getElementById('payment-form');
        const btn = document.getElementById('btn-submit');
        const btnText = btn.querySelector('.btn-text');

        form.addEventListener('submit', () => {
            btn.style.pointerEvents = 'none';
            btnText.textContent = 'EXECUTING TRANSFER...';
        });
    });
</script>

</body>
</html>
