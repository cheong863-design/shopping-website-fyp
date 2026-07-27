<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$order_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '000000';
$display_order_no = str_pad($order_id, 6, '0', STR_PAD_LEFT);
$auth_code = strtoupper(substr(hash('sha256', $order_id . time()), 0, 12));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/images/main-logo.png">
    <title>Transaction Cleared | FAIFA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        /* ==============================================
           🎨 THE THERMAL PRINTOUT (ORDER SUCCESS)
           ============================================== */
        body {
            margin: 0; padding: 0;
            background: #ebebeb;
            color: #111;
            font-family: 'Inter', sans-serif;
            display: flex; flex-direction: column; align-items: center;
            min-height: 100vh; overflow: hidden;
        }

        .printer-slot {
            width: 100%; max-width: 440px; height: 30px;
            background: linear-gradient(to bottom, #111 0%, #000 100%);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            position: relative; z-index: 100;
            border-radius: 0 0 4px 4px;
        }
        .printer-slot::after {
            content: ''; position: absolute; bottom: -2px; left: 2%; width: 96%; height: 2px;
            background: rgba(255,255,255,0.1);
        }

        .receipt-wrapper {
            width: 100%; max-width: 400px;
            height: 650px;
            overflow: hidden;
            position: relative;
            margin-top: -10px;
            z-index: 10;
        }

        .thermal-paper {
            width: 100%; background: #ffffff;
            padding: 50px 40px 70px 40px; box-sizing: border-box;
            position: relative;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);

            transform: translateY(-100%);
            animation: dispensePaper 1.8s cubic-bezier(0.65, 0, 0.07, 1) forwards 0.2s;
        }
        @keyframes dispensePaper { to { transform: translateY(0); } }

        .thermal-paper::after {
            content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 10px;
            background: linear-gradient(-45deg, transparent 8px, #ffffff 0), linear-gradient(45deg, transparent 8px, #ffffff 0);
            background-size: 16px 16px; background-repeat: repeat-x;
            transform: translateY(10px);
        }

        .tp-header { text-align: center; border-bottom: 1px dashed #d1d5db; padding-bottom: 20px; margin-bottom: 30px; }
        .tp-logo { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 800; letter-spacing: -1px; margin: 0; }
        .tp-meta { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 2px; }

        .tp-body { text-align: center; margin-bottom: 40px; }
        .serif-massive { font-family: 'Playfair Display', serif; font-size: 46px; font-style: italic; font-weight: 700; margin: 0 0 10px 0; color: #111; line-height: 1; }
        .mono-desc { font-family: monospace; font-size: 10px; color: #666; letter-spacing: 1px; line-height: 1.6; }

        .tp-ledger { border: 1px solid #111; padding: 20px; margin-bottom: 40px; display: flex; flex-direction: column; gap: 15px; }
        .ledger-row { display: flex; justify-content: space-between; align-items: baseline; font-family: monospace; font-size: 10px; }
        .lr-label { font-weight: 700; color: #888; letter-spacing: 1px; }
        .lr-value { font-weight: 800; color: #111; font-size: 12px; }
        .lr-auth { font-size: 10px; color: #10b981; font-weight: 800; }

        .tp-barcode-area { text-align: center; position: relative; padding-top: 20px; border-top: 1px dashed #d1d5db; }
        .barcode-font { font-family: 'Courier New', Courier, monospace; font-size: 42px; color: #111; letter-spacing: -3px; transform: scaleY(1.2); opacity: 0.9; margin: 0; overflow: hidden; white-space: nowrap; }
        .barcode-num { font-family: monospace; font-size: 9px; font-weight: 800; letter-spacing: 4px; color: #888; margin-top: 5px; display: block; }

        .laser-line { position: absolute; top: 30px; left: 10%; width: 80%; height: 2px; background: #ef4444; box-shadow: 0 0 10px #ef4444; animation: laserScan 2s ease-in-out infinite alternate; opacity: 0; animation-delay: 2s; }
        @keyframes laserScan { 0% { transform: translateY(-10px); opacity: 0.8; } 100% { transform: translateY(20px); opacity: 0.8; } }

        .auth-stamp {
            position: absolute; top: 180px; right: 20px;
            font-family: monospace; font-size: 24px; font-weight: 800; letter-spacing: 4px;
            color: #111; border: 3px solid #111; padding: 10px 15px;
            transform: rotate(-15deg) scale(3); opacity: 0; pointer-events: none; z-index: 20;

            animation: stampSlam 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards 1.8s;
        }
        @keyframes stampSlam { to { transform: rotate(-15deg) scale(1); opacity: 1; } }

        .action-dock { margin-top: 20px; opacity: 0; animation: fadeUp 1s ease forwards 2.5s; z-index: 20; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .btn-tear-off {
            background: #111; color: #fff; text-decoration: none; padding: 20px 40px;
            font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 3px;
            display: inline-flex; align-items: center; gap: 15px; transition: all 0.3s;
            position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .btn-tear-off:hover { background: #ff8002; transform: translateY(-3px); box-shadow: 0 15px 30px rgba(255, 128, 2, 0.2); }
        .btn-tear-off svg { width: 16px; height: 16px; transition: transform 0.3s; }
        .btn-tear-off:hover svg { transform: translateX(5px); }

    </style>
</head>
<body>

    <div class="printer-slot"></div>

    <div class="receipt-wrapper">
        <div class="thermal-paper">

            <div class="auth-stamp">CLEARED</div>

            <div class="tp-header">
                <h2 class="tp-logo">FAIFA.</h2>
                <span class="tp-meta">PAYMENT GATEWAY // KL HQ</span>
            </div>

            <div class="tp-body">
                <h1 class="serif-massive">Secured.</h1>
                <p class="mono-desc">THE FUNDS HAVE BEEN SUCCESSFULLY TRANSFERRED. YOUR ARCHIVE IS BEING PREPARED FOR DISPATCH.</p>
            </div>

            <div class="tp-ledger">
                <div class="ledger-row">
                    <span class="lr-label">REFERENCE ID</span>
                    <span class="lr-value js-decrypt" data-ref="F-<?php echo $display_order_no; ?>">F-000000</span>
                </div>
                <div class="ledger-row">
                    <span class="lr-label">AUTH CODE</span>
                    <span class="lr-value"><?php echo $auth_code; ?></span>
                </div>
                <div class="ledger-row" style="border-top: 1px dashed #d1d5db; padding-top: 15px; margin-top: 5px;">
                    <span class="lr-label">STATUS</span>
                    <span class="lr-auth">[ TRANSACTION APPROVED ]</span>
                </div>
            </div>

            <div class="tp-barcode-area">
                <div class="laser-line"></div>
                <p class="barcode-font">|||| | ||| || ||| | || |||| | |||</p>
                <span class="barcode-num">SECURE // 256-BIT ENCRYPTED</span>
            </div>

        </div>
    </div>

    <div class="action-dock">
        <a href="order-confirmation.php?id=<?php echo $order_id; ?>" class="btn-tear-off">
            VIEW DOSSIER
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const decryptElement = document.querySelector('.js-decrypt');
        if (decryptElement) {
            const finalValue = decryptElement.getAttribute('data-ref');
            const chars = '0123456789XXYYZZ';
            let iterations = 0;

            setTimeout(() => {
                const interval = setInterval(() => {
                    decryptElement.innerText = finalValue.split('').map((char, index) => {
                        if (char === '-' || char === 'F') return char;
                        if (index < iterations / 2) return finalValue[index];
                        return chars[Math.floor(Math.random() * chars.length)];
                    }).join('');
                    iterations++;
                    if (iterations >= 30) {
                        clearInterval(interval);
                        decryptElement.innerText = finalValue;
                    }
                }, 30);
            }, 1500);
        }
    });
</script>

</body>
</html>
