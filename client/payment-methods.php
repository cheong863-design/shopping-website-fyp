<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = isset($_GET['msg']) ? $_GET['msg'] : '';

// ==========================================
// ==========================================
$notif_res = mysqli_query($conn, "SELECT id FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
$has_notifications = ($notif_res && mysqli_num_rows($notif_res) > 0);
// ==========================================

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $card_id = intval($_GET['id']);
    $delete_sql = "DELETE FROM user_payments WHERE id = '$card_id' AND user_id = '$user_id'";
    if (mysqli_query($conn, $delete_sql)) {
        header("Location: payment-methods.php?msg=deleted");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_card'])) {
    $card_no = mysqli_real_escape_string($conn, $_POST['card_number']);
    $last_four = substr($card_no, -4);
    $expiry = mysqli_real_escape_string($conn, $_POST['expiry']);
    $type = "Visa";

    mysqli_query($conn, "UPDATE user_payments SET is_default = 0 WHERE user_id = '$user_id'");

    $sql = "INSERT INTO user_payments (user_id, card_type, card_last_four, expiry_date, is_default)
            VALUES ('$user_id', '$type', '$last_four', '$expiry', 1)";

    if (mysqli_query($conn, $sql)) {
        header("Location: payment-methods.php?msg=success");
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'set_default' && isset($_GET['id'])) {
    $card_id = intval($_GET['id']);
    mysqli_query($conn, "UPDATE user_payments SET is_default = 0 WHERE user_id = '$user_id'");
    mysqli_query($conn, "UPDATE user_payments SET is_default = 1 WHERE id = '$card_id' AND user_id = '$user_id'");
    header("Location: payment-methods.php?msg=default_updated");
    exit();
}

$pay_res = mysqli_query($conn, "SELECT * FROM user_payments WHERE user_id = '$user_id'");

include 'includes/header.php';
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
                        <div class="notif-ping"></div>
                    <?php endif; ?>
                </a>

                <a href="addresses.php" class="nav-link"><span>04</span> Addresses</a>
                <a href="payment-methods.php" class="nav-link active"><span>05</span> Payment</a>
                <hr class="nav-divider">
                <a href="logout.php" class="logout-link">Logout</a>
            </nav>
        </aside>

        <section class="profile-main">
            <div id="payment-vault-wrapper">

                <header class="luxury-header fade-up" style="--delay: 1">
                    <div class="header-inner">
                        <h2 class="editorial-title">Billing & Vault.</h2>
                        <p class="editorial-subtitle">Manage your payment instruments and secure authorizations.</p>
                    </div>
                </header>

                <div class="toast-container">
                    <?php if($message == 'required'): ?>
                        <div class="luxury-toast warning">Action required: Provide billing details.</div>
                    <?php elseif($message == 'success'): ?>
                        <div class="luxury-toast success">Instrument securely added.</div>
                    <?php elseif($message == 'deleted'): ?>
                        <div class="luxury-toast info">Record elegantly erased.</div>
                    <?php elseif($message == 'default_updated'): ?>
                        <div class="luxury-toast success">Primary instrument updated.</div>
                    <?php endif; ?>
                </div>

                <div class="payment-scenery fade-up" style="--delay: 2">
                    <?php
                    $i = 0;
                    while($pay = mysqli_fetch_assoc($pay_res)):
                        $i++;
                    ?>
                    <div class="cc-wrap" style="--card-idx: <?php echo $i; ?>">
                        <div class="obsidian-cc <?php echo $pay['is_default'] ? 'is-primary' : ''; ?>" data-tilt data-tilt-max="4" data-tilt-speed="1500" data-tilt-glare data-tilt-max-glare="0.2">

                            <div class="cc-noise"></div>
                            <button class="cc-remove" onclick="return eraseCard(this, <?php echo $pay['id']; ?>, event)" title="Erase Record">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>

                            <div class="cc-top">
                                <div class="cc-chip">
                                    <svg viewBox="0 0 40 30" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="1.5"><rect x="2" y="2" width="36" height="26" rx="4"></rect><path d="M10 2v26M30 2v26M2 15h36M10 10h20M10 20h20"></path></svg>
                                </div>
                                <div class="cc-brand"><?php echo strtoupper($pay['card_type']); ?></div>
                            </div>

                            <div class="cc-middle">
                                <div class="cc-number">
                                    <span>****</span><span>****</span><span>****</span><span><?php echo $pay['card_last_four']; ?></span>
                                </div>
                            </div>

                            <div class="cc-bottom">
                                <div class="cc-info">
                                    <span class="cc-label">VALID THRU</span>
                                    <span class="cc-value"><?php echo $pay['expiry_date']; ?></span>
                                </div>

                                <?php if($pay['is_default']): ?>
                                    <div class="cc-status primary">
                                        <span class="dot"></span> PRIMARY
                                    </div>
                                <?php else: ?>
                                    <a href="payment-methods.php?action=set_default&id=<?php echo $pay['id']; ?>" class="cc-status action-set">
                                        Make Primary
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="premium-form-container fade-up" style="--delay: 4">

                    <div class="form-header-micro">
                        <h3 class="heading-micro">Authorize New Instrument</h3>
                        <div class="secure-lock">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <span>Encrypted</span>
                        </div>
                    </div>

                    <form action="payment-methods.php" method="POST" class="premium-form" id="paymentForm">
                        <div class="form-group-float stagger-in" style="--stagger: 1">
                            <input type="text" id="card_num" name="card_number" class="float-input" placeholder=" " maxlength="19" required oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\d{4})(?=\d)/g, '$1 ')">
                            <label for="card_num" class="float-label">PAN (Card Number)</label>
                            <div class="input-focus-border"></div>
                        </div>

                        <div class="form-row-split">
                            <div class="form-group-float stagger-in" style="--stagger: 2">
                                <input type="text" id="exp_date" name="expiry" class="float-input" placeholder=" " maxlength="5" required oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^([2-9])$/g, '0$1').replace(/^(1{1})([3-9]{1})$/g, '01/$2').replace(/^0{0,1}(1[0-2]|0?[1-9])([0-9]{1,2}).*/g, '$1/$2')">
                                <label for="exp_date" class="float-label">EXP (MM/YY)</label>
                                <div class="input-focus-border"></div>
                            </div>
                            <div class="form-group-float stagger-in" style="--stagger: 3">
                                <input type="password" id="cvc_code" class="float-input" placeholder=" " maxlength="3" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <label for="cvc_code" class="float-label">CVC</label>
                                <div class="input-focus-border"></div>
                            </div>
                        </div>

                        <button type="submit" name="add_card" class="btn-editorial-submit stagger-in" style="--stagger: 4" id="submitBtn">
                            <span class="btn-text">Save & Authorize <span class="arrow">→</span></span>
                            <div class="btn-spinner"></div>
                        </button>
                    </form>
                </div>

            </div>
        </section>
    </div>
</main>

<style>

.profile-sidebar { position: sticky; top: 100px; height: fit-content; z-index: 10; }
.side-nav-transparent { position: relative; display: flex; flex-direction: column; background: transparent; padding: 20px 0;}
.side-nav-transparent::before { content: ''; position: absolute; left: 15px; top: 10px; bottom: 10px; width: 1px; background: rgba(0,0,0,0.1); z-index: 0; }
.nav-tracker { position: absolute; left: 14px; width: 3px; height: 20px; background: #ff8002; z-index: 2; transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), height 0.5s cubic-bezier(0.25, 1, 0.5, 1); opacity: 0; border-radius: 2px; }
.side-nav-transparent .nav-link { position: relative; z-index: 1; padding: 15px 35px; display: flex; align-items: center; text-decoration: none; color: #888; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: color 0.4s ease; font-family: 'Inter', sans-serif; }
.side-nav-transparent .nav-link span { font-family: monospace; font-size: 10px; margin-right: 15px; opacity: 0.4; transition: 0.4s; }
.side-nav-transparent .nav-link.active { color: #111; font-weight: 800; }
.side-nav-transparent .nav-link.active span { opacity: 1; color: #ff8002; }
.side-nav-transparent .nav-link:hover { color: #111; }
.side-nav-transparent .nav-divider { border: none; border-top: 1px solid rgba(0,0,0,0.1); margin: 15px 35px; }
.side-nav-transparent .logout-link { display: block; padding: 10px 35px; color: #ef4444; font-size: 13px; font-weight: 600; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; font-family: 'Inter', sans-serif;}
.side-nav-transparent .logout-link:hover { opacity: 0.5; }

.notif-ping { width: 6px; height: 6px; background: #ff8002; border-radius: 50%; margin-left: 10px; position: relative; }
.notif-ping::after { content: ''; position: absolute; inset: 0; border-radius: 50%; border: 1px solid #ff8002; animation: pingOut 2s cubic-bezier(0, 0, 0.2, 1) infinite; }
@keyframes pingOut { 75%, 100% { transform: scale(3); opacity: 0; } }

.fade-up { opacity: 0; animation: fadeFloatUp 1s cubic-bezier(0.25, 1, 0.5, 1) calc(var(--delay) * 0.1s) forwards; }
@keyframes fadeFloatUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

#payment-vault-wrapper { color: #111; font-family: 'Inter', sans-serif; padding: 0 10px; }

#payment-vault-wrapper .luxury-header { margin-bottom: 40px; padding-top: 20px; }
#payment-vault-wrapper .editorial-title { font-size: 42px; font-weight: 800; color: #111; margin: 0 0 10px 0; letter-spacing: -1.5px; line-height: 1; }
#payment-vault-wrapper .editorial-subtitle { font-size: 15px; color: #888; font-weight: 400; margin: 0; }

#payment-vault-wrapper .toast-container { margin-bottom: 30px; }
#payment-vault-wrapper .luxury-toast { padding: 15px 25px; border-radius: 4px; font-size: 12px; font-weight: 700; letter-spacing: 1px; animation: toastFade 4s forwards; }
#payment-vault-wrapper .luxury-toast.success { background: #f0fdf4; color: #16a34a; border-left: 3px solid #16a34a; }
#payment-vault-wrapper .luxury-toast.warning { background: #fff7ed; color: #ea580c; border-left: 3px solid #ea580c; }
#payment-vault-wrapper .luxury-toast.info { background: #f8fafc; color: #64748b; border-left: 3px solid #64748b; }
@keyframes toastFade { 0% { opacity: 0; transform: translateX(-10px); } 10% { opacity: 1; transform: translateX(0); } 90% { opacity: 1; } 100% { opacity: 0; } }

#payment-vault-wrapper .payment-scenery { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px; margin-bottom: 50px; transition: 0.8s cubic-bezier(0.25, 1, 0.5, 1); perspective: 1000px; }
#payment-vault-wrapper .payment-scenery:has(.cc-wrap:hover) .cc-wrap:not(:hover) { opacity: 0.3; filter: blur(5px) grayscale(0.5); transform: scale(0.96); }
#payment-vault-wrapper .cc-wrap { transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1); transform-style: preserve-3d; }

#payment-vault-wrapper .obsidian-cc { aspect-ratio: 1.586; background: linear-gradient(135deg, #1c1917 0%, #292524 100%); border-radius: 16px; padding: 25px 30px; color: #fff; position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.15), inset 0 1px 1px rgba(255,255,255,0.1); display: flex; flex-direction: column; justify-content: space-between; transition: all 0.6s cubic-bezier(0.25, 1, 0.5, 1); border: 1px solid #3f3f46; }
#payment-vault-wrapper .obsidian-cc:hover { border-color: #78716c; box-shadow: 0 30px 60px rgba(0,0,0,0.25); z-index: 10; }
#payment-vault-wrapper .cc-noise { position: absolute; inset: 0; opacity: 0.15; mix-blend-mode: overlay; pointer-events: none; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='1.5' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); }

#payment-vault-wrapper .cc-top { display: flex; justify-content: space-between; align-items: flex-start; transform: translateZ(20px); }
#payment-vault-wrapper .cc-chip svg { width: 35px; height: auto; opacity: 0.8; }
#payment-vault-wrapper .cc-brand { font-size: 16px; font-weight: 900; letter-spacing: 2px; font-style: italic; opacity: 0.9; }
#payment-vault-wrapper .cc-middle { transform: translateZ(30px); margin-top: 10px; }
#payment-vault-wrapper .cc-number { font-family: monospace; font-size: 20px; font-weight: 600; letter-spacing: 3px; display: flex; justify-content: space-between; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
#payment-vault-wrapper .cc-bottom { display: flex; justify-content: space-between; align-items: flex-end; transform: translateZ(20px); }
#payment-vault-wrapper .cc-info { display: flex; flex-direction: column; gap: 4px; }
#payment-vault-wrapper .cc-label { font-size: 8px; color: #a8a29e; letter-spacing: 1.5px; }
#payment-vault-wrapper .cc-value { font-family: monospace; font-size: 14px; font-weight: 600; letter-spacing: 2px; }

#payment-vault-wrapper .cc-status { font-size: 9px; font-weight: 800; letter-spacing: 1.5px; text-decoration: none; padding: 6px 12px; border-radius: 100px; transition: 0.3s; }
#payment-vault-wrapper .cc-status.primary { background: rgba(255,255,255,0.1); color: #fff; display: flex; align-items: center; gap: 6px; border: 1px solid rgba(255,255,255,0.2); }
#payment-vault-wrapper .cc-status.primary .dot { width: 4px; height: 4px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; }
#payment-vault-wrapper .cc-status.action-set { background: transparent; color: #a8a29e; border: 1px solid #a8a29e; cursor: pointer; }
#payment-vault-wrapper .cc-status.action-set:hover { background: #fff; color: #1c1917; border-color: #fff; }

#payment-vault-wrapper .cc-remove { position: absolute; top: 20px; right: 20px; background: transparent; border: none; color: #a8a29e; cursor: pointer; opacity: 0; transition: 0.3s; transform: translateZ(40px); }
#payment-vault-wrapper .obsidian-cc:hover .cc-remove { opacity: 0.6; }
#payment-vault-wrapper .cc-remove:hover { opacity: 1 !important; color: #ef4444; transform: translateZ(40px) scale(1.2); }
#payment-vault-wrapper .cc-remove svg { width: 18px; height: 18px; }

#payment-vault-wrapper .premium-form-container { max-width: 500px; background: #ffffff; padding: 40px; border-radius: 4px; border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 20px 40px rgba(0,0,0,0.02); transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1); }
#payment-vault-wrapper .premium-form-container:hover { border-color: rgba(0,0,0,0.08); box-shadow: 0 30px 60px rgba(0,0,0,0.04); transform: translateY(-2px); }

#payment-vault-wrapper .form-header-micro { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid rgba(0,0,0,0.04); padding-bottom: 15px; }
#payment-vault-wrapper .heading-micro { font-family: monospace; font-size: 10px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 1.5px; margin: 0; }
#payment-vault-wrapper .secure-lock { display: flex; align-items: center; gap: 6px; font-size: 9px; font-weight: 800; color: #10b981; letter-spacing: 1px; text-transform: uppercase; }
#payment-vault-wrapper .secure-lock svg { width: 10px; height: 10px; }

.stagger-in { opacity: 0; transform: translateY(15px); animation: staggerFade 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
.stagger-in:nth-child(1) { animation-delay: calc(0.4s + 0.1s); }
.stagger-in:nth-child(2) { animation-delay: calc(0.4s + 0.2s); }
.stagger-in:nth-child(3) { animation-delay: calc(0.4s + 0.3s); }
@keyframes staggerFade { to { opacity: 1; transform: translateY(0); } }

#payment-vault-wrapper .premium-form { display: flex; flex-direction: column; gap: 20px; }
#payment-vault-wrapper .form-row-split { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

#payment-vault-wrapper .form-group-float { position: relative; background: transparent; border-bottom: 1px solid rgba(0,0,0,0.08); transition: border-color 0.3s; }

#payment-vault-wrapper .float-input { width: 100%; padding: 24px 0 10px 0; background: transparent; border: none; outline: none; font-size: 15px; color: #111; font-weight: 600; font-family: monospace; letter-spacing: 1px; }
#payment-vault-wrapper .float-label { position: absolute; left: 0; top: 18px; color: #888; font-size: 13px; font-weight: 600; pointer-events: none; transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); }
#payment-vault-wrapper .float-input:focus ~ .float-label, #payment-vault-wrapper .float-input:not(:placeholder-shown) ~ .float-label { top: 4px; font-size: 9px; font-weight: 800; color: #ff8002; letter-spacing: 1px; }

#payment-vault-wrapper .input-focus-border { position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background: #111; transform-origin: left; transform: scaleX(0); transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1); }
#payment-vault-wrapper .float-input:focus ~ .input-focus-border { transform: scaleX(1); }

#payment-vault-wrapper .btn-editorial-submit { width: 100%; background: #111; color: #fff; border: none; padding: 18px; border-radius: 2px; font-size: 11px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1); margin-top: 15px; display: flex; justify-content: center; align-items: center; }
#payment-vault-wrapper .btn-editorial-submit .arrow { opacity: 0; transform: translateX(-10px); display: inline-block; transition: 0.4s cubic-bezier(0.25, 1, 0.5, 1); margin-left: 5px; }
#payment-vault-wrapper .btn-editorial-submit:hover { background: #ff8002; box-shadow: 0 15px 30px rgba(255,128,2,0.2); padding-right: 10px; }
#payment-vault-wrapper .btn-editorial-submit:hover .arrow { opacity: 1; transform: translateX(0); }

#payment-vault-wrapper .btn-editorial-submit::before { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transform: skewX(-20deg); transition: 0.7s cubic-bezier(0.25, 1, 0.5, 1); }
#payment-vault-wrapper .btn-editorial-submit:hover::before { left: 150%; }

#payment-vault-wrapper .btn-spinner { display: none; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }
#payment-vault-wrapper .is-loading .btn-text { display: none; }
#payment-vault-wrapper .is-loading .btn-spinner { display: block; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 900px) {
    #payment-vault-wrapper .payment-scenery { grid-template-columns: 1fr; }
    #payment-vault-wrapper .form-row-split { grid-template-columns: 1fr; }
    .profile-sidebar { position: static; margin-bottom: 40px; }
    .side-nav-transparent::before, .nav-tracker { display: none; }
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

        const ccCards = document.querySelectorAll(".obsidian-cc");
        if (ccCards.length > 0) {
            VanillaTilt.init(ccCards, {
                max: 4,
                speed: 1500,
                glare: true,
                "max-glare": 0.2,
                scale: 1.02
            });
        }
    });

    function eraseCard(btn, id, e) {
        if (!confirm('Erase this payment instrument from vault?')) {
            e.preventDefault();
            return false;
        }
        const cardItem = btn.closest('.cc-wrap');
        cardItem.style.transition = 'all 0.8s cubic-bezier(0.25, 1, 0.5, 1)';
        cardItem.style.transform = 'translateY(-30px) scale(0.9)';
        cardItem.style.opacity = '0';
        cardItem.style.filter = 'blur(15px)';

        setTimeout(() => { window.location.href = `payment-methods.php?action=delete&id=${id}`; }, 600);
        return false;
    }

    document.getElementById('paymentForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('is-loading');
        btn.style.pointerEvents = 'none';
    });
</script>

<?php include 'includes/footer.php'; ?>
