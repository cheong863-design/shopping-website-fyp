<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';
include 'includes/db.php';
include 'includes/functions.php';

$cart_items = $_SESSION['cart'] ?? [];
$subtotal = 0;
$user_id = $_SESSION['user_id'] ?? null;
$is_logged_in = isset($_SESSION['user_id']);
$can_checkout = true;

$current_zone = 'Domestic';

$tax_data = get_tax_data_by_ip($conn);
$dynamic_tax_rate = $tax_data['rate'];
$detected_location = $tax_data['location'];

$rules_res = mysqli_query($conn, "SELECT * FROM shipping_rules WHERE zone = '$current_zone' AND is_active = 1 ORDER BY rate ASC");
$shipping_rules_data = [];
while($r = mysqli_fetch_assoc($rules_res)) { $shipping_rules_data[] = $r; }
?>

<div id="system-alert-modal" class="editorial-modal">
    <div class="modal-canvas" style="text-align: center; padding: 50px 40px;">
        <h3 class="serif-title" style="font-size: 28px; margin-bottom: 15px;">Please note.</h3>
        <p id="system-alert-msg" class="mono-subtitle" style="font-size: 11px; line-height: 1.8; margin-bottom: 35px; color: #666; text-transform: none; white-space: pre-wrap;"></p>
        <button id="system-alert-btn" class="btn-solid-black" style="width: 100%;">Acknowledge</button>
    </div>
</div>

<main class="cart-editorial-page">
    <div class="matte-grain"></div>

    <div class="container cart-container-raw">

        <?php if (isset($_GET['error'])): ?>
            <div class="editorial-alert fade-up">
                <span class="alert-msg">
                    <?php
                        if ($_GET['error'] == 'stock') echo "<strong>ATTENTION:</strong> Some items in your bag exceed our current inventory.";
                        else echo "<strong>ATTENTION:</strong> An error occurred during checkout. Please try again.";
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <header class="cart-header-raw fade-up" style="--delay: 1">
            <h1 class="title-serif-huge">Shopping Bag.</h1>
            <span class="meta-mono-count">VOL. <?php echo str_pad(count($cart_items), 2, '0', STR_PAD_LEFT); ?></span>
        </header>

        <div class="cart-grid-raw">

            <section class="item-list-raw fade-up" style="--delay: 2">
                <?php
                if (!empty($cart_items)):
                    $idx = 2;
                    foreach ($cart_items as $id => $qty):
                        $res = mysqli_query($conn, "SELECT * FROM products WHERE id = " . intval($id));
                        $p = mysqli_fetch_assoc($res);
                        if(!$p) continue;

                        $stock_shortage = ($qty > $p['stock']);
                        $can_checkout = $stock_shortage ? false : $can_checkout;
                        $item_total = $p['price'] * $qty;
                        $subtotal += $item_total;
                        $idx++;
                ?>
                <div class="item-row-raw <?php echo $stock_shortage ? 'stock-error' : ''; ?> fade-up" style="--delay: <?php echo $idx; ?>">

                    <div class="item-image-raw">
                        <img src="assets/images/<?php echo $p['image']; ?>" onerror="this.src='https://placehold.co/120x160/f1f5f9/94a3b8?text=Img'">
                    </div>

                    <div class="item-info-raw">
                        <div class="info-top">
                            <span class="brand-mono">FAIFA ESSENTIALS</span>
                            <a href="cart-actions.php?action=remove&id=<?php echo $id; ?>" class="remove-btn-raw">✕</a>
                        </div>

                        <h3 class="name-serif"><?php echo htmlspecialchars($p['name']); ?></h3>

                        <?php if($stock_shortage): ?>
                            <p class="error-mono">LIMIT EXCEEDED: <?php echo $p['stock']; ?> LEFT</p>
                        <?php endif; ?>

                        <div class="info-bottom">
                            <div class="micro-stepper">
                                <button type="button" data-id="<?php echo $id; ?>" data-action="minus">−</button>
                                <input type="text" value="<?php echo str_pad($qty, 2, '0', STR_PAD_LEFT); ?>" readonly>
                                <button type="button" data-id="<?php echo $id; ?>" data-action="plus">+</button>
                            </div>
                            <div class="price-mono">MYR <?php echo number_format($item_total, 2); ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                    <div class="empty-state-raw fade-up" style="--delay: 2">
                        <p class="empty-serif">The bag is empty.</p>
                        <a href="product.php" class="empty-link-mono">CONTINUE BROWSING</a>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="summary-raw fade-up" style="--delay: 3">
                <div class="summary-sticky">
                    <h4 class="summary-heading">ORDER SUMMARY</h4>

                    <ul class="ledger-lines">
                        <li>
                            <span class="label">SUBTOTAL</span>
                            <span class="value">MYR <span id="ui-subtotal"><?php echo number_format($subtotal, 2); ?></span></span>
                        </li>

                        <li id="discount-row" style="display: none; color: #ff8002;">
                            <span class="label">DISCOUNT (<span id="applied-code-name"></span>)</span>
                            <span class="value">- MYR <span id="ui-discount">0.00</span></span>
                        </li>

                        <li>
                            <span class="label flex-col">SHIPPING <small id="ui-shipping-name">Calculating...</small></span>
                            <span class="value" id="ui-shipping-wrapper">MYR 0.00</span>
                        </li>

                        <li>
                            <span class="label flex-col">TAX <small><span id="ui-location-name"><?php echo strtoupper($detected_location); ?></span> (<?php echo number_format($dynamic_tax_rate, 1); ?>%)</small></span>
                            <span class="value">MYR <span id="ui-tax">0.00</span></span>
                        </li>

                        <li class="total-line">
                            <span class="label">TOTAL</span>
                            <span class="value total-amount">MYR <span id="ui-total">0.00</span></span>
                        </li>
                    </ul>

                    <?php if (!empty($cart_items)): ?>
                    <div class="ghost-promo-box">
                        <input type="text" id="couponInput" placeholder="PROMO CODE" class="ghost-promo-input">
                        <button type="button" onclick="applyCoupon()" class="ghost-promo-btn">→</button>
                    </div>
                    <div id="coupon-msg" class="promo-msg-mono"></div>
                    <?php endif; ?>

                    <?php if ($is_logged_in): ?>
                        <a href="checkout.php" id="main-checkout-btn" class="btn-checkout-raw <?php echo (!$can_checkout || empty($cart_items)) ? 'disabled' : ''; ?>" style="display: block; text-align: center; text-decoration: none;" <?php echo (!$can_checkout || empty($cart_items)) ? 'onclick="return false;"' : ''; ?>>
                            <?php echo $can_checkout ? 'PROCEED TO CHECKOUT' : 'UNAVAILABLE'; ?>
                        </a>
                    <?php else: ?>
                        <button type="button" id="main-checkout-btn" class="btn-checkout-raw" onclick="requireIdentity(this)" <?php echo empty($cart_items) ? 'disabled' : ''; ?>>
                            PROCEED TO CHECKOUT
                        </button>
                    <?php endif; ?>

                </div>
            </aside>
        </div>
    </div>
</main>

<style>
/* ==============================================
   🎨 EXTREME EDITORIAL BRUTALISM (THE NAKED UI)
   ============================================== */

:root {
    --brand-orange: #ff8002;
    --text-black: #111111;
    --text-gray: #888888;
    --hairline: #e5e5e5;
    --ease-editorial: cubic-bezier(0.25, 1, 0.5, 1);
}

.fade-up { opacity: 0; animation: editorialUp 1.2s var(--ease-editorial) calc(var(--delay) * 0.1s) forwards; }
@keyframes editorialUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.cart-editorial-page { padding: 50px 0 60px 0; background: transparent; color: var(--text-black); font-family: 'Inter', sans-serif; }
.cart-container-raw { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

.matte-grain { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1; opacity: 0.3; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.06'/%3E%3C/svg%3E"); }

.editorial-alert { border-bottom: 1px solid var(--text-black); padding: 15px 0; margin-bottom: 40px; display: flex; justify-content: space-between; align-items: center; font-family: monospace; font-size: 11px; letter-spacing: 1px; }
.alert-msg strong { color: #ef4444; }

.cart-header-raw { display: flex; justify-content: space-between; align-items: baseline; border-bottom: 1px solid var(--text-black); padding-bottom: 20px; margin-bottom: 60px; }
.title-serif-huge { font-family: 'Playfair Display', serif; font-size: 56px; font-style: italic; margin: 0; line-height: 1; font-weight: 400; color: var(--text-black); }
.meta-mono-count { font-family: monospace; font-size: 11px; color: var(--text-gray); letter-spacing: 2px; }

.cart-grid-raw { display: grid; grid-template-columns: 1.8fr 1fr; gap: 80px; align-items: start; }

.item-list-raw { display: flex; flex-direction: column; }
.item-row-raw { display: flex; gap: 40px; padding: 40px 0; border-bottom: 1px solid var(--hairline); transition: 0.4s var(--ease-editorial); }
.item-row-raw:hover { background: rgba(0,0,0,0.015); padding-left: 10px; padding-right: 10px; }
.item-row-raw.stock-error { border-bottom-color: #ef4444; }

.item-image-raw { width: 130px; aspect-ratio: 3/4; background: #eee; flex-shrink: 0; overflow: hidden; }
.item-image-raw img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(100%); transition: 0.8s var(--ease-editorial); }
.item-row-raw:hover .item-image-raw img { filter: grayscale(0%); transform: scale(1.05); }

.item-info-raw { flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
.info-top { display: flex; justify-content: space-between; align-items: flex-start; }
.brand-mono { font-family: monospace; font-size: 10px; color: var(--text-gray); letter-spacing: 2px; }
.remove-btn-raw { color: var(--text-gray); text-decoration: none; font-size: 14px; transition: 0.3s; line-height: 1; }
.remove-btn-raw:hover { color: #ef4444; transform: rotate(90deg); }

.name-serif { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 400; margin: 15px 0; color: var(--text-black); }
.error-mono { font-family: monospace; font-size: 10px; color: #ef4444; letter-spacing: 1px; margin: 0; }

.info-bottom { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto; }
.price-mono { font-family: monospace; font-size: 16px; font-weight: 600; color: var(--text-black); }

.micro-stepper { display: flex; align-items: center; gap: 10px; font-family: monospace; font-size: 12px; }
.micro-stepper button { background: none; border: none; color: var(--text-gray); cursor: pointer; font-size: 14px; padding: 0 5px; transition: color 0.3s; }
.micro-stepper button:hover { color: var(--text-black); }
.micro-stepper input { width: 24px; text-align: center; border: none; background: transparent; color: var(--text-black); font-weight: 600; pointer-events: none; padding: 0; outline: none; }

.empty-state-raw { padding: 100px 0; text-align: center; }
.empty-serif { font-family: 'Playfair Display', serif; font-size: 28px; font-style: italic; color: var(--text-gray); margin-bottom: 20px; }
.empty-link-mono { font-family: monospace; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: var(--text-black); text-decoration: none; border-bottom: 1px solid var(--text-black); padding-bottom: 4px; transition: 0.3s; }
.empty-link-mono:hover { color: var(--brand-orange); border-color: var(--brand-orange); }

.summary-sticky { position: sticky; top: 120px; }
.summary-heading { font-family: monospace; font-size: 11px; font-weight: 700; letter-spacing: 2px; color: var(--text-gray); margin: 0 0 30px 0; border-bottom: 1px solid var(--hairline); padding-bottom: 15px; }

.ledger-lines { list-style: none; padding: 0; margin: 0 0 40px 0; display: flex; flex-direction: column; gap: 20px; }
.ledger-lines li { display: flex; justify-content: space-between; align-items: baseline; font-family: monospace; font-size: 11px; color: var(--text-gray); letter-spacing: 1px; }
.ledger-lines li .value { color: var(--text-black); font-weight: 600; font-size: 12px; }
.flex-col { display: flex; flex-direction: column; gap: 4px; }
.flex-col small { font-size: 9px; opacity: 0.6; }

.total-line { border-top: 1px solid var(--text-black); padding-top: 20px; margin-top: 10px; color: var(--text-black) !important; font-weight: 700; }
.total-amount { font-size: 24px !important; font-weight: 400 !important; letter-spacing: -0.5px; }

.ghost-promo-box { display: flex; border-bottom: 1px solid var(--hairline); transition: border-color 0.3s; margin-bottom: 5px; }
.ghost-promo-box:focus-within { border-color: var(--text-black); }
.ghost-promo-input { flex-grow: 1; border: none; background: transparent; padding: 10px 0; font-family: monospace; font-size: 11px; outline: none; text-transform: uppercase; letter-spacing: 1px; color: var(--text-black); }
.ghost-promo-input::placeholder { color: var(--text-gray); }
.ghost-promo-btn { background: transparent; border: none; color: var(--text-black); font-size: 16px; cursor: pointer; padding: 0 10px; transition: transform 0.3s; }
.ghost-promo-btn:hover { transform: translateX(5px); color: var(--brand-orange); }
.promo-msg-mono { font-family: monospace; font-size: 9px; letter-spacing: 1px; margin-bottom: 40px; height: 12px; }

.btn-checkout-raw { width: 100%; padding: 22px; background: var(--text-black); color: #fff; border: none; font-family: monospace; font-size: 12px; font-weight: 700; letter-spacing: 2px; cursor: pointer; transition: 0.4s var(--ease-editorial); box-sizing: border-box; }
.btn-checkout-raw:hover:not(.disabled):not(:disabled) { background: var(--brand-orange); transform: translateY(-2px); box-shadow: 0 15px 30px rgba(255, 128, 2, 0.15); }
.btn-checkout-raw.disabled, .btn-checkout-raw:disabled { background: var(--hairline); color: var(--text-gray); cursor: not-allowed; }
.btn-checkout-raw.require-identity { background: #ef4444; animation: shakeBtn 0.5s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shakeBtn { 10%, 90% { transform: translateX(-2px); } 20%, 80% { transform: translateX(2px); } 30%, 50%, 70% { transform: translateX(-4px); } 40%, 60% { transform: translateX(4px); } }

.editorial-modal { display: none; position: fixed; inset: 0; background: rgba(250, 250, 249, 0.95); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.4s; }
.editorial-modal.active { display: flex; opacity: 1; }

.modal-canvas { background: #fff; width: 450px; max-width: 90%; border: 1px solid var(--hairline); box-shadow: 0 20px 40px rgba(0,0,0,0.05); transform: translateY(20px); transition: 0.4s var(--ease-editorial); }
.editorial-modal.active .modal-canvas { transform: translateY(0); }

.btn-solid-black { background: var(--text-black); color: #fff; border: none; padding: 18px; font-family: monospace; font-size: 11px; letter-spacing: 2px; cursor: pointer; transition: 0.3s; }
.btn-solid-black:hover { background: var(--brand-orange); }

@media (max-width: 900px) {
    .cart-grid-raw { grid-template-columns: 1fr; gap: 60px; }
    .item-row-raw { flex-direction: column; gap: 20px; }
    .item-image-raw { width: 100%; aspect-ratio: 16/9; }
    .summary-sticky { position: static; }
}
</style>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

<script>
const originalSubtotal = <?php echo $subtotal; ?>;
const shippingRules = <?php echo json_encode($shipping_rules_data); ?>;
const taxRateFromIP = <?php echo ($dynamic_tax_rate / 100); ?>;

function calculateAll(discountAmt = 0) {
    const discountedSubtotal = originalSubtotal - discountAmt;
    let currentShipping = 0;
    let shippingName = "N/A";

    if (shippingRules.length > 0) {
        let freeRule = shippingRules.find(r => parseFloat(r.rate) === 0);
        if (freeRule && discountedSubtotal >= 150) {
            currentShipping = 0;
            shippingName = "COMPLIMENTARY";
        } else {
            let baseRule = shippingRules.find(r => parseFloat(r.rate) > 0) || shippingRules[0];
            currentShipping = parseFloat(baseRule.rate);
            shippingName = baseRule.rule_name.toUpperCase();
        }
    }

    document.getElementById('ui-shipping-name').innerText = shippingName;
    document.getElementById('ui-shipping-wrapper').innerHTML = currentShipping === 0 ? 'COMPLIMENTARY' : `MYR ${currentShipping.toFixed(2)}`;

    const tax = (discountedSubtotal + currentShipping) * taxRateFromIP;
    const total = discountedSubtotal + currentShipping + tax;

    document.getElementById('ui-tax').innerText = tax.toFixed(2);
    document.getElementById('ui-total').innerText = total.toFixed(2);

    sessionStorage.setItem('faifa_discount', discountAmt);
    sessionStorage.setItem('faifa_coupon_code', document.getElementById("applied-code-name").innerText || "");
    sessionStorage.setItem('faifa_shipping', currentShipping);
    sessionStorage.setItem('faifa_shipping_name', shippingName);
}

window.onload = () => calculateAll(0);

function applyCoupon() {
    let code = document.getElementById("couponInput").value.toUpperCase();
    let msgBox = document.getElementById("coupon-msg");
    if(code.trim() === "") return;

    let formData = new FormData();
    formData.append("coupon_code", code);

    fetch("apply-coupon.php", { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            msgBox.innerHTML = "PROMO APPLIED: " + data.message;
            msgBox.style.color = "#ff8002";

            let discountAmt = 0;
            let val = parseFloat(data.value);
            if(data.type === "percentage") {
                discountAmt = originalSubtotal * (val / 100);
            } else {
                discountAmt = val;
            }
            if(discountAmt > originalSubtotal) discountAmt = originalSubtotal;

            document.getElementById("discount-row").style.display = "flex";
            document.getElementById("applied-code-name").innerText = code;
            document.getElementById("ui-discount").innerText = discountAmt.toFixed(2);

            calculateAll(discountAmt);
            document.getElementById("couponInput").readOnly = true;
        } else {
            msgBox.innerHTML = "DECLINED: " + data.message;
            msgBox.style.color = "#ef4444";
            calculateAll(0);
        }
    });
}

let alertCallback = null;
const alertModal = document.getElementById('system-alert-modal');
const alertMsg = document.getElementById('system-alert-msg');
const alertBtn = document.getElementById('system-alert-btn');

function showCustomAlert(message, callback) {
    alertMsg.innerText = message;
    alertCallback = callback;
    alertModal.classList.add('active');
}

alertBtn.addEventListener('click', () => {
    alertModal.classList.remove('active');
    if (alertCallback) alertCallback();
});

function requireIdentity(btn) {
    btn.classList.add('require-identity');
    btn.innerText = 'IDENTITY REQUIRED';
    setTimeout(() => { window.location.href = 'login.php'; }, 1200);
}

document.querySelectorAll('button[data-action]').forEach(btn => {
    btn.onclick = function() {
        location.href = `cart-actions.php?action=update_qty&id=${this.dataset.id}&type=${this.dataset.action}`;
    };
});
</script>

<?php include 'includes/footer.php'; ?>
