<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$pay_res = mysqli_query($conn, "SELECT * FROM user_payments WHERE user_id = '$user_id'");
$count = mysqli_num_rows($pay_res);

$shipping_fee = isset($_POST['shipping_fee']) ? floatval($_POST['shipping_fee']) : 0;
?>

<main class="vault-selection-page">
    <div class="container selection-grid">

        <header class="vault-header focus-in" style="--delay: 0">
            <nav class="micro-breadcrumb">
                <span>CHECKOUT</span> <span class="slash">/</span> <span class="current">SETTLEMENT</span>
            </nav>
            <h1 class="macro-title">Select Instrument.</h1>
            <p class="vault-subtitle">DECRYPT AND AUTHORIZE AN ASSET FOR TRANSFER</p>
        </header>

        <?php if ($count > 0): ?>
            <form action="process-order.php" method="POST" id="vault-auth-form">
                <input type="hidden" name="shipping_fee" value="<?php echo $shipping_fee; ?>">

                <div class="instrument-vault-grid">
                    <?php while($pay = mysqli_fetch_assoc($pay_res)): ?>
                        <label class="instrument-card-wrapper stagger-in">
                            <input type="radio" name="payment_id" value="<?php echo $pay['id']; ?>"
                                   <?php echo $pay['is_default'] ? 'checked' : ''; ?> required>

                            <div class="mini-obsidian-card" data-tilt data-tilt-max="6" data-tilt-speed="1000" data-tilt-glare data-tilt-max-glare="0.15">
                                <div class="card-noise"></div>
                                <div class="card-glint"></div>

                                <div class="card-top">
                                    <span class="card-type"><?php echo strtoupper($pay['card_type']); ?></span>
                                    <div class="card-chip"></div>
                                </div>

                                <div class="card-mid">
                                    <span class="card-dots">•••• •••• ••••</span>
                                    <span class="card-last-four"><?php echo $pay['card_last_four']; ?></span>
                                </div>

                                <div class="card-bottom">
                                    <span class="card-label">AUTHORIZED ASSET</span>
                                    <div class="selection-indicator">
                                        <div class="target-lock"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    <?php endwhile; ?>

                    <a href="payment-methods.php" class="add-instrument-portal stagger-in">
                        <div class="portal-inner">
                            <span class="plus-icon">+</span>
                            <span class="portal-text">NEW INSTRUMENT</span>
                        </div>
                    </a>
                </div>

                <div class="vault-actions-bar focus-in" style="--delay: 3">
                    <button type="submit" class="btn-authorize-vault" id="auth-trigger">
                        <span class="btn-text">AUTHORIZE TRANSFER</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                    <p class="security-note">SECURE 256-BIT ENCRYPTED GATEWAY // KL_HQ</p>
                </div>
            </form>
        <?php else: ?>
            <div class="empty-vault-state focus-in" style="--delay: 1">
                <h2 class="serif-title">No authorized instruments found.</h2>
                <p class="mono-sub">PLEASE ESTABLISH A SETTLEMENT PATHWAY TO PROCEED.</p>
                <a href="payment-methods.php" class="btn-establish">ESTABLISH INSTRUMENT ↗</a>
            </div>
        <?php endif; ?>

    </div>
</main>

<style>
/* ==============================================
   🎨 THE SETTLEMENT VAULT (QUIET LUXURY)
   ============================================== */
.vault-selection-page { background: #ffffff; padding: 60px 0 120px 0; min-height: 80vh; color: #111; font-family: 'Inter', sans-serif; }
.selection-grid { max-width: 1000px; margin: 0 auto; padding: 0 30px; }

.focus-in { opacity: 0; transform: translateY(20px); filter: blur(5px); animation: opticalFocus 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--delay) * 0.15s); }
.stagger-in { opacity: 0; transform: translateY(20px); animation: softRise 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes opticalFocus { to { opacity: 1; transform: translateY(0); filter: blur(0); } }
@keyframes softRise { to { opacity: 1; transform: translateY(0); } }

.vault-header { margin-bottom: 60px; text-align: center; }
.micro-breadcrumb { font-family: monospace; font-size: 9px; letter-spacing: 2px; color: #888; margin-bottom: 20px; }
.micro-breadcrumb .slash { margin: 0 10px; opacity: 0.3; }
.macro-title { font-family: 'Playfair Display', serif; font-size: 56px; font-weight: 400; font-style: italic; margin: 0 0 10px 0; letter-spacing: -2px; }
.vault-subtitle { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 3px; font-weight: 700; }

.instrument-vault-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
    margin-bottom: 80px;
}

.instrument-card-wrapper input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }

.mini-obsidian-card {
    aspect-ratio: 1.586 / 1;
    background: #111;
    border-radius: 12px;
    padding: 25px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    border: 1px solid #222;
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.card-noise { position: absolute; inset: 0; opacity: 0.1; mix-blend-mode: overlay; pointer-events: none; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); }

.card-top { display: flex; justify-content: space-between; align-items: flex-start; }
.card-type { font-family: monospace; font-size: 11px; font-weight: 800; color: #fff; letter-spacing: 2px; }
.card-chip { width: 32px; height: 24px; background: linear-gradient(135deg, #333, #111); border: 1px solid #444; border-radius: 4px; }

.card-mid { color: #fff; font-family: monospace; font-size: 18px; letter-spacing: 3px; margin-top: 10px; }
.card-dots { color: #444; }

.card-bottom { display: flex; justify-content: space-between; align-items: flex-end; }
.card-label { font-family: monospace; font-size: 8px; color: #666; letter-spacing: 1px; }

.selection-indicator { width: 18px; height: 18px; border: 1px solid #333; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.4s; }
.target-lock { width: 6px; height: 6px; background: transparent; border-radius: 50%; transition: 0.4s; }

.instrument-card-wrapper input[type="radio"]:checked + .mini-obsidian-card {
    border-color: #ff8002;
    box-shadow: 0 20px 50px rgba(255, 128, 2, 0.15);
    transform: translateY(-5px);
}
.instrument-card-wrapper input[type="radio"]:checked + .mini-obsidian-card .selection-indicator { border-color: #ff8002; }
.instrument-card-wrapper input[type="radio"]:checked + .mini-obsidian-card .target-lock { background: #ff8002; box-shadow: 0 0 10px #ff8002; }

.add-instrument-portal {
    aspect-ratio: 1.586 / 1;
    border: 1px dashed #d1d5db;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: 0.4s;
}
.add-instrument-portal:hover { border-color: #111; background: #fafafa; }
.portal-inner { text-align: center; color: #888; transition: 0.3s; }
.add-instrument-portal:hover .portal-inner { color: #111; transform: scale(1.05); }
.plus-icon { font-size: 24px; display: block; margin-bottom: 10px; font-weight: 300; }
.portal-text { font-family: monospace; font-size: 9px; font-weight: 800; letter-spacing: 2px; }

.vault-actions-bar { text-align: center; max-width: 400px; margin: 0 auto; }
.btn-authorize-vault {
    width: 100%; background: #111; color: #fff; border: none; padding: 25px 0;
    font-family: monospace; font-size: 12px; font-weight: 800; letter-spacing: 4px;
    cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 15px;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.btn-authorize-vault:hover { background: #ff8002; box-shadow: 0 20px 40px rgba(255, 128, 2, 0.2); }
.btn-authorize-vault svg { width: 16px; height: 16px; transition: 0.3s; }
.btn-authorize-vault:hover svg { transform: translateX(8px); }

.security-note { font-family: monospace; font-size: 9px; color: #ccc; margin-top: 25px; letter-spacing: 1px; }

.empty-vault-state { text-align: center; padding: 100px 0; }
.serif-title { font-family: 'Playfair Display', serif; font-size: 32px; font-style: italic; margin-bottom: 15px; }
.btn-establish { display: inline-block; margin-top: 30px; font-family: monospace; font-size: 11px; font-weight: 800; color: #111; text-decoration: none; border-bottom: 2px solid #111; padding-bottom: 5px; transition: 0.3s; }
.btn-establish:hover { color: #ff8002; border-color: #ff8002; }

@media (max-width: 768px) {
    .macro-title { font-size: 38px; }
    .instrument-vault-grid { grid-template-columns: 1fr; }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll(".mini-obsidian-card");
    if (cards.length > 0) {
        VanillaTilt.init(cards);
    }

    const form = document.getElementById('vault-auth-form');
    const btn = document.getElementById('auth-trigger');
    if (form && btn) {
        form.addEventListener('submit', () => {
            btn.style.pointerEvents = 'none';
            btn.querySelector('.btn-text').textContent = 'DECRYPTING & AUTHORIZING...';
        });
    }

    const staggers = document.querySelectorAll('.stagger-in');
    staggers.forEach((el, index) => {
        el.style.animationDelay = `${(index * 0.1) + 0.3}s`;
    });
});
</script>

<?php include 'includes/footer.php'; ?>
