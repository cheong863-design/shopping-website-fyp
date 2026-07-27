<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// ==========================================
// ==========================================
$addr_query = mysqli_query($conn, "SELECT * FROM user_addresses WHERE user_id = '$user_id' ORDER BY is_default DESC, id DESC LIMIT 1");
if (mysqli_num_rows($addr_query) == 0) {
    header("Location: profile.php?msg=need_address"); exit();
}
$address_data = mysqli_fetch_assoc($addr_query);

// ==========================================
// ==========================================
$zone = 'Domestic';
$shipping_rules = [];
$rules_query = mysqli_query($conn, "SELECT * FROM shipping_rules WHERE zone = '$zone' AND is_active = 1 ORDER BY rate ASC");
if ($rules_query) { while($row = mysqli_fetch_assoc($rules_query)) { $shipping_rules[] = $row; } }

$subtotal = 11.80;
$tax_rate = 0.075;

include 'includes/header.php';
?>

<main class="checkout-editorial-page">

    <div style="max-width: 1200px; margin: 0 auto; padding: 30px 40px 0; font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;">
        <a href="cart.php" style="text-decoration: none; color: #94a3b8; transition: color 0.4s ease;" onmouseover="this.style.color='#111'" onmouseout="this.style.color='#94a3b8'">← Return to Archive</a>
    </div>

    <div id="errorToast" class="toast-notification">Action required.</div>

    <form action="process-order.php" method="POST" id="checkoutForm">
        <input type="hidden" name="address_id" value="<?php echo $address_data['id']; ?>">

        <input type="hidden" id="final_shipping_fee" name="shipping_fee" value="0">
        <input type="hidden" id="final_shipping_method" name="shipping_method" value="">
        <input type="hidden" id="final_tax" name="tax_amount" value="0">
        <input type="hidden" id="final_total" name="final_total" value="0">

        <div class="checkout-container">

            <div class="checkout-left">

                <div class="address-section cinematic-reveal" style="--delay: 1;">
                    <h2 class="section-title">
                        Shipping Coordinates
                        <a href="profile.php">Modify</a>
                    </h2>
                    <div class="saved-address-card">
                        <h4>
                            <?php echo htmlspecialchars($address_data['receiver_name']); ?>
                            <?php if(isset($address_data['is_default']) && $address_data['is_default'] == 1): ?>
                                <span class="badge">PRIMARY</span>
                            <?php endif; ?>
                        </h4>
                        <p><?php echo nl2br(htmlspecialchars($address_data['address_line'])); ?></p>
                        <p class="phone">TEL. <?php echo htmlspecialchars($address_data['phone']); ?></p>
                    </div>
                </div>

                <div class="shipping-section cinematic-reveal" style="--delay: 2;">
                    <h2 class="section-title">Delivery Protocol</h2>
                    <div class="options-grid" id="shippingOptionsGroup">
                        <?php if(empty($shipping_rules)): ?>
                            <p style="font-size:13px; color:#ef4444; grid-column: span 3; font-style: italic;">No routing options active.</p>
                        <?php else: ?>
                            <?php foreach($shipping_rules as $rule): ?>
                                <label class="option-label">
                                    <input type="radio" name="shipping_rule_id" value="<?php echo $rule['id']; ?>" class="shipping-radio" data-rate="<?php echo $rule['rate']; ?>" data-name="<?php echo htmlspecialchars($rule['rule_name']); ?>">
                                    <div class="option-box">
                                        <div class="check-ring"></div>
                                        <span class="opt-icon" style="filter: grayscale(100%);">📦</span>
                                        <span class="opt-name"><?php echo htmlspecialchars($rule['rule_name']); ?></span>
                                        <?php if($rule['rate'] == 0): ?>
                                            <span class="opt-price" style="color:#555;">COMPLIMENTARY</span>
                                        <?php else: ?>
                                            <span class="opt-price">MYR <?php echo number_format($rule['rate'], 2); ?></span>
                                        <?php endif; ?>
                                        <span class="opt-desc"><?php echo htmlspecialchars($rule['description']); ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="payment-section cinematic-reveal" style="--delay: 3;">
                    <h2 class="section-title">Billing Instrument</h2>
                    <div class="options-grid" id="paymentOptionsGroup">
                        <label class="option-label"><input type="radio" name="payment_method" value="tng" class="pay-radio"><div class="option-box"><div class="check-ring"></div><span class="opt-icon" style="filter: grayscale(100%);">📱</span><span class="opt-name">Touch 'n Go</span></div></label>
                        <label class="option-label"><input type="radio" name="payment_method" value="card" class="pay-radio"><div class="option-box"><div class="check-ring"></div><span class="opt-icon" style="filter: grayscale(100%);">💳</span><span class="opt-name">Credit / Debit</span></div></label>
                        <label class="option-label"><input type="radio" name="payment_method" value="fpx" class="pay-radio"><div class="option-box"><div class="check-ring"></div><span class="opt-icon" style="filter: grayscale(100%);">🏦</span><span class="opt-name">FPX Banking</span></div></label>
                        <label class="option-label"><input type="radio" name="payment_method" value="alipay" class="pay-radio"><div class="option-box"><div class="check-ring"></div><span class="opt-icon" style="filter: grayscale(100%);">🔵</span><span class="opt-name">Alipay</span></div></label>
                        <label class="option-label"><input type="radio" name="payment_method" value="cod" class="pay-radio"><div class="option-box"><div class="check-ring"></div><span class="opt-icon" style="filter: grayscale(100%);">🚚</span><span class="opt-name">Cash on Delivery</span></div></label>
                        <label class="option-label"><input type="radio" name="payment_method" value="store" class="pay-radio"><div class="option-box"><div class="check-ring"></div><span class="opt-icon" style="filter: grayscale(100%);">🏪</span><span class="opt-name">Pay at Store</span></div></label>
                    </div>
                </div>

            </div>

            <div class="checkout-right cinematic-reveal" style="--delay: 4;">
                <div class="summary-box">
                    <h2 class="section-title" style="border: none; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: var(--text-muted);">Final Ledger</h2>

                    <div class="summary-row">
                        <span>SUBTOTAL</span>
                        <span class="summary-val" id="ui-subtotal">MYR <?php echo number_format($subtotal, 2); ?></span>
                    </div>

                    <div class="summary-row" id="discount-row" style="display: none; color: var(--highlight);">
                        <span>DISCOUNT (<span id="applied-code-name"></span>)</span>
                        <span class="summary-val" id="ui-discount">- MYR 0.00</span>
                    </div>

                    <div class="summary-row">
                        <span>SHIPPING<br><small id="ui-shipping-name" style="font-size: 9px; opacity: 0.6; letter-spacing: 1px;">AWAITING PROTOCOL</small></span>
                        <span class="summary-val" id="ui-shipping-fee">MYR 0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>TAX<br><small style="font-size: 9px; opacity: 0.6; letter-spacing: 1px;">GLOBAL (<?php echo ($tax_rate * 100); ?>%)</small></span>
                        <span class="summary-val" id="ui-tax">MYR 0.00</span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-total">
                        <span class="label">TOTAL</span>
                        <span class="val" id="ui-total">MYR 0.00</span>
                    </div>

                    <button type="submit" class="btn-submit" id="mainSubmitBtn">
                        <span class="btn-txt">AUTHORIZE PAYMENT</span>
                        <div class="btn-loader"></div>
                    </button>
                </div>
            </div>

        </div>
    </form>

</main>

<?php include 'includes/footer.php'; ?>

<style>

    :root {
        --text-main: #111111;
        --text-muted: #888888;
        --border-color: #D1D1D1;
        --highlight: #111111;
        --bg-color: #EBEBEB;

        --ease-cinematic: cubic-bezier(0.16, 1, 0.3, 1);
        --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .checkout-editorial-page {
        background-color: var(--bg-color);
        min-height: calc(100vh - 200px);
        padding-bottom: 100px;
        overflow-x: hidden;
    }

    .cinematic-reveal {
        opacity: 0; filter: blur(10px); transform: translateY(30px) scale(0.98);
        animation: focusReveal 1.2s var(--ease-cinematic) calc(var(--delay) * 0.15s) forwards;
    }
    @keyframes focusReveal {
        to { opacity: 1; filter: blur(0); transform: translateY(0) scale(1); }
    }

    .checkout-container {
        max-width: 1200px; margin: 40px auto 0; padding: 0 40px;
        display: grid; grid-template-columns: 1.6fr 1fr; gap: 80px;
    }

    .section-title {
        font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 500;
        margin-bottom: 25px; letter-spacing: -0.5px; color: var(--text-main);
        border-bottom: 1px solid var(--border-color); padding-bottom: 15px;
        display: flex; justify-content: space-between; align-items: baseline;
    }
    .section-title a {
        font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-muted);
        text-decoration: none; transition: color 0.4s ease; border-bottom: 1px solid transparent;
    }
    .section-title a:hover { color: var(--text-main); border-bottom-color: var(--text-main); }

    .checkout-left { display: flex; flex-direction: column; gap: 50px; }

    .saved-address-card {
        border: 1px solid var(--border-color); background: transparent; padding: 30px; border-radius: 4px;
        display: flex; flex-direction: column; gap: 10px; position: relative; overflow: hidden;
        transition: 0.5s var(--ease-cinematic);
    }
    .saved-address-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--text-main);
    }
    .saved-address-card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.03); background: rgba(255,255,255,0.3); }
    .saved-address-card h4 { margin: 0; font-size: 16px; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 12px; letter-spacing: -0.3px; }
    .saved-address-card .badge { font-size: 9px; font-family: monospace; background: var(--text-main); color: #fff; padding: 3px 8px; border-radius: 2px; letter-spacing: 1.5px; }
    .saved-address-card p { margin: 0; font-size: 14px; color: #555; line-height: 1.7; font-weight: 500; }
    .saved-address-card .phone { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: var(--text-main); font-weight: 700; margin-top: 8px; }

    .options-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .option-label { display: block; position: relative; cursor: pointer; }
    .option-label input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }

    .option-box {
        border: 1px solid var(--border-color); padding: 25px 15px; border-radius: 4px;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;
        transition: all 0.5s var(--ease-spring); background: transparent; height: 100%; box-sizing: border-box;
        text-align: center; position: relative; z-index: 1;
    }

    .check-ring {
        position: absolute; top: 14px; right: 14px; width: 14px; height: 14px;
        border: 1px solid #ccc; border-radius: 50%;
        transition: all 0.4s var(--ease-spring);
    }

    .option-box:hover { border-color: #bbb; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.02); background: rgba(255,255,255,0.3);}

    .option-label input[type="radio"]:checked + .option-box {
        transform: scale(1.02); border-color: var(--text-main); border-width: 1.5px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05); background: #fff;
    }
    .option-label input[type="radio"]:checked + .option-box .check-ring {
        background: var(--text-main); border-color: var(--text-main);
        box-shadow: inset 0 0 0 3px #fff; transform: scale(1.1);
    }

    .opt-icon { font-size: 26px; line-height: 1; transition: transform 0.5s var(--ease-spring); }
    .option-label input[type="radio"]:checked + .option-box .opt-icon { transform: scale(1.1) translateY(-2px); }

    .opt-name { font-size: 12px; font-weight: 800; color: var(--text-main); line-height: 1.4; transition: color 0.3s; letter-spacing: -0.3px;}

    .opt-price { font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 800; color: var(--text-main); margin-top: 2px; }
    .opt-desc { font-size: 10px; color: var(--text-muted); font-weight: 600; }

    .is-invalid .option-box { border-color: #111; border-width: 1.5px; background: #e0e0e0; animation: errorShake 0.5s var(--ease-spring) both; }
    @keyframes errorShake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-5px); } 40%, 80% { transform: translateX(5px); } }

    .summary-box { position: sticky; top: 120px; padding-left: 30px; }
    .summary-row {
        display: flex; justify-content: space-between; margin-bottom: 22px;
        font-size: 11px; font-weight: 800; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 1.5px; align-items: center;
    }
    .summary-val { font-family: 'JetBrains Mono', monospace; color: var(--text-main); font-size: 14px; font-weight: 700; letter-spacing: 0;}
    .summary-divider { border-top: 1px solid var(--border-color); margin: 35px 0; }
    .summary-total { display: flex; justify-content: space-between; align-items: baseline; }
    .summary-total .label { font-size: 13px; font-weight: 900; letter-spacing: 2px; color: var(--text-main);}
    .summary-total .val { font-family: 'JetBrains Mono', monospace; font-size: 32px; font-weight: 400; color: var(--text-main); letter-spacing: -1px; }

    .price-bump { animation: pricePulseMono 0.5s var(--ease-cinematic); }
    @keyframes pricePulseMono {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); color: #888; }
        100% { transform: scale(1); color: var(--text-main); }
    }

    .btn-submit {
        width: 100%; background: var(--text-main); color: #fff; border: none;
        padding: 22px; font-size: 12px; font-weight: 800; letter-spacing: 3px;
        text-transform: uppercase; cursor: pointer; margin-top: 45px; border-radius: 2px;
        transition: all 0.5s var(--ease-cinematic); position: relative; overflow: hidden;
        display: flex; align-items: center; justify-content: center;
    }
    .btn-submit:hover { background: #333; transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); }
    .btn-submit:active { transform: translateY(0) scale(0.99); }

    .btn-loader { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; display: none; position: absolute;}
    .btn-submit.loading .btn-txt { opacity: 0; }
    .btn-submit.loading .btn-loader { display: block; }
    .btn-submit.loading { pointer-events: none; background: #555; transform: scale(0.99); }
    @keyframes spin { to { transform: rotate(360deg); } }

    .toast-notification {
        position: fixed; top: 120px; left: 50%; transform: translateX(-50%) translateY(-20px) scale(0.9);
        background: #111; color: #fff; padding: 16px 35px; border-radius: 4px;
        font-size: 12px; font-weight: 800; letter-spacing: 2px; z-index: 9999;
        opacity: 0; visibility: hidden; transition: all 0.6s var(--ease-spring);
        box-shadow: 0 15px 30px rgba(0,0,0,0.2); text-transform: uppercase;
    }
    .toast-notification.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0) scale(1); }

    @media (max-width: 900px) {
        .checkout-container { grid-template-columns: 1fr; gap: 60px; padding: 0 20px; }
        .summary-box { padding-left: 0; position: static; }
        .options-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<script>
    const subtotal = <?php echo $subtotal; ?>;
    const taxRate = <?php echo $tax_rate; ?>;
    let discount = parseFloat(sessionStorage.getItem('faifa_discount')) || 0;
    let promoCodeName = sessionStorage.getItem('faifa_coupon_code') || '';

    const shippingRadios = document.querySelectorAll('.shipping-radio');
    const uiShippingName = document.getElementById('ui-shipping-name');
    const uiShippingFee = document.getElementById('ui-shipping-fee');
    const uiTax = document.getElementById('ui-tax');
    const uiTotal = document.getElementById('ui-total');

    const inputShippingFee = document.getElementById('final_shipping_fee');
    const inputShippingMethod = document.getElementById('final_shipping_method');
    const inputTax = document.getElementById('final_tax');
    const inputTotal = document.getElementById('final_total');

    if(discount > 0) {
        document.getElementById('discount-row').style.display = 'flex';
        document.getElementById('applied-code-name').innerText = promoCodeName;
        document.getElementById('ui-discount').innerText = `- MYR ${discount.toFixed(2)}`;
    }

    let currentDisplayedTotal = subtotal - discount + ((subtotal - discount) * taxRate);

    function rollNumber(element, start, end) {
        const duration = 800;
        const startTime = performance.now();
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 4);
            const val = start + (end - start) * ease;
            element.innerText = `MYR ${val.toFixed(2)}`;
            if (progress < 1) requestAnimationFrame(update);
            else element.innerText = `MYR ${end.toFixed(2)}`;
        }
        requestAnimationFrame(update);
    }

    function calculateCart(triggerAnimation = false) {
        let shippingFee = 0;
        let shippingName = "AWAITING PROTOCOL";

        const selectedShipping = document.querySelector('.shipping-radio:checked');
        if (selectedShipping) {
            shippingFee = parseFloat(selectedShipping.getAttribute('data-rate'));
            shippingName = selectedShipping.getAttribute('data-name').toUpperCase();
        }

        const taxableAmount = Math.max(0, subtotal - discount + shippingFee);
        const calculatedTax = taxableAmount * taxRate;
        const targetTotal = taxableAmount + calculatedTax;

        uiShippingName.innerText = shippingName;
        uiShippingFee.innerText = shippingFee === 0 && selectedShipping ? 'COMPLIMENTARY' : `MYR ${shippingFee.toFixed(2)}`;
        uiTax.innerText = `MYR ${calculatedTax.toFixed(2)}`;

        if (currentDisplayedTotal !== targetTotal) {
            rollNumber(uiTotal, currentDisplayedTotal, targetTotal);
            currentDisplayedTotal = targetTotal;

            if(triggerAnimation) {
                uiTotal.classList.remove('price-bump');
                void uiTotal.offsetWidth;
                uiTotal.classList.add('price-bump');
            }
        } else {
            uiTotal.innerText = `MYR ${targetTotal.toFixed(2)}`;
        }

        inputShippingFee.value = shippingFee.toFixed(2);
        inputShippingMethod.value = shippingName;
        inputTax.value = calculatedTax.toFixed(2);
        inputTotal.value = targetTotal.toFixed(2);
    }

    calculateCart(false);

    shippingRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('shippingOptionsGroup').classList.remove('is-invalid');
            calculateCart(true);
        });
    });

    const checkoutForm = document.getElementById('checkoutForm');
    const payRadios = document.querySelectorAll('.pay-radio');
    const paymentGroup = document.getElementById('paymentOptionsGroup');
    const shippingGroup = document.getElementById('shippingOptionsGroup');
    const toast = document.getElementById('errorToast');
    const submitBtn = document.getElementById('mainSubmitBtn');

    function showToast(msg) {
        toast.innerText = msg;
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 3000);
    }

    payRadios.forEach(radio => radio.addEventListener('change', () => paymentGroup.classList.remove('is-invalid')));

    checkoutForm.addEventListener('submit', function(e) {
        let isValid = true;
        let errorMsg = "";

        let shippingSelected = false;
        shippingRadios.forEach(r => { if(r.checked) shippingSelected = true; });
        if (!shippingSelected) {
            shippingGroup.classList.add('is-invalid');
            isValid = false;
            errorMsg = "PROTOCOL REQUIRED: SELECT DELIVERY METHOD.";
        }

        let paymentSelected = false;
        payRadios.forEach(r => { if(r.checked) paymentSelected = true; });
        if (!paymentSelected) {
            paymentGroup.classList.add('is-invalid');
            isValid = false;
            errorMsg = errorMsg ? "PROTOCOL REQUIRED: SELECT DELIVERY & BILLING." : "PROTOCOL REQUIRED: SELECT BILLING INSTRUMENT.";
        }

        if (!isValid) {
            e.preventDefault();
            showToast(errorMsg);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            submitBtn.classList.add('loading');
        }
    });
</script>
