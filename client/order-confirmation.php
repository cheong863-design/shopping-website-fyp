<?php
include 'includes/header.php';
include 'includes/db.php';

$order_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (!empty($order_id)) {
    $order_query = mysqli_query($conn, "SELECT * FROM orders WHERE id = '$order_id'");
    $order_data = mysqli_fetch_assoc($order_query);

    if (!$order_data) {
        header("Location: index.php");
        exit();
    }

    $items_query = mysqli_query($conn, "
        SELECT oi.*, p.name, p.price, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = '$order_id'
    ");

    $display_order_no = str_pad($order_id, 6, '0', STR_PAD_LEFT);
    $total_amount = $order_data['total_price'];

    $shipping_fee = isset($order_data['shipping_fee']) ? $order_data['shipping_fee'] : 0.00;
    $tax_amount = isset($order_data['tax_amount']) ? $order_data['tax_amount'] : 0.00;
    $subtotal = $total_amount - $shipping_fee - $tax_amount;
} else {
    header("Location: index.php");
    exit();
}
?>

<main class="manifest-editorial-page">
    <div class="container manifest-container">

        <div class="declaration-box fade-up" style="--delay: 1">

            <div class="barcode-header">
                <div class="barcode-pattern">
                    |||| | ||| || ||| | || |||| | ||| || ||| | || |||| | ||| || ||| | || |||| | ||| || ||| | || ||||
                </div>
                <div class="scanner-laser"></div>
            </div>

            <div class="manifest-grid">

                <div class="grid-vertical-meta">
                    <span>TRANSFER MANIFEST // DO NOT DESTROY</span>
                </div>

                <div class="grid-content">

                    <div class="content-header">
                        <div class="ch-top">
                            <span class="ch-tag">[ STATUS: SECURED ]</span>
                            <a href="invoice.php?id=<?php echo $order_id; ?>" class="link-hardcopy" target="_blank">INITIATE HARDCOPY ↗</a>
                        </div>
                        <h1 class="ch-title">Acquisition Sealed.</h1>
                        <p class="ch-desc">THE TRANSACTION HAS BEEN AUTHORIZED. YOUR ARCHIVE IS BEING PREPARED FOR GLOBAL DISPATCH.</p>

                        <div class="macro-ref">
                            <span class="ref-label">REF_ID</span>
                            <span class="ref-number">F-<?php echo $display_order_no; ?></span>
                        </div>
                    </div>

                    <div class="content-items">
                        <div class="item-th">
                            <div class="th-col">ITEM DESCRIPTION</div>
                            <div class="th-col" style="text-align: center;">QTY</div>
                            <div class="th-col" style="text-align: right;">VAL</div>
                        </div>

                        <?php
                        $item_idx = 1;
                        while($item = mysqli_fetch_assoc($items_query)):
                        ?>
                        <div class="item-tr stagger-in" style="--stagger: <?php echo $item_idx + 1; ?>">
                            <div class="td-col td-main">
                                <span class="item-idx">[0<?php echo $item_idx; ?>]</span>
                                <div class="item-visual">
                                    <img src="assets/images/<?php echo $item['image']; ?>" alt="Artifact" onerror="this.src='https://placehold.co/100x120/ffffff/111?text=X'">
                                </div>
                                <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                            </div>
                            <div class="td-col td-qty">x<?php echo $item['quantity']; ?></div>
                            <div class="td-col td-price"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                        </div>
                        <?php
                            $item_idx++;
                        endwhile;
                        ?>
                    </div>

                    <div class="content-financials stagger-in" style="--stagger: 5">
                        <div class="fin-row">
                            <span>SUBTOTAL</span>
                            <span><?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="fin-row">
                            <span>DISPATCH (SHIPPING)</span>
                            <span><?php echo number_format($shipping_fee, 2); ?></span>
                        </div>
                        <div class="fin-row">
                            <span>TAX (SST/GST)</span>
                            <span><?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                        <div class="fin-row fin-total">
                            <span>NET AUTHORIZED</span>
                            <span class="total-val">MYR <?php echo number_format($total_amount, 2); ?></span>
                        </div>
                    </div>

                    <div class="content-footer stagger-in" style="--stagger: 6">
                        <div class="dispatch-eta">
                            <span class="eta-label">EXPECTED ARRIVAL</span>
                            <span class="eta-val">3 - 5 BUSINESS DAYS</span>
                        </div>
                        <a href="index.php" class="btn-return-macro">
                            RETURN TO ARCHIVE <span>→</span>
                        </a>
                    </div>

                </div>
            </div>

            <div class="stamp-authorized">VERIFIED</div>
        </div>

    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 THE CUSTOMS DECLARATION (ULTIMATE CONFIRMATION)
   ============================================== */

.manifest-editorial-page {
    background: #ffffff;
    color: #111;
    font-family: 'Inter', sans-serif;
    padding: 60px 0 100px 0;
    min-height: 85vh;
}

.manifest-container { max-width: 900px; margin: 0 auto; padding: 0 20px; }

.fade-up { opacity: 0; animation: runwayUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.stagger-in { opacity: 0; transform: translateY(20px); animation: runwayUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--stagger) * 0.15s); }
@keyframes runwayUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

.declaration-box {
    width: 100%;
    border: 1px dashed #111;
    position: relative;
    background: transparent;
}

.barcode-header {
    width: 100%; height: 60px;
    border-bottom: 1px dashed #111;
    position: relative; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    background: #fafafa;
}
.barcode-pattern {
    font-family: 'Courier New', Courier, monospace;
    font-size: 40px; color: #111; letter-spacing: -3px;
    transform: scaleY(1.5); opacity: 0.8; white-space: nowrap; overflow: hidden;
}

.scanner-laser {
    position: absolute; top: 0; left: 0; width: 2px; height: 100%;
    background: #ef4444; box-shadow: 0 0 10px #ef4444;
    animation: scanLaser 3s ease-in-out infinite alternate;
}
@keyframes scanLaser { 0% { left: 0; } 100% { left: 100%; } }

.manifest-grid {
    display: grid;
    grid-template-columns: 60px 1fr;
}

.grid-vertical-meta {
    border-right: 1px dashed #111;
    display: flex; align-items: center; justify-content: center;
    padding: 40px 0;
}
.grid-vertical-meta span {
    font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 5px;
    color: #888; writing-mode: vertical-rl; transform: rotate(180deg);
}

.grid-content { display: flex; flex-direction: column; }

.content-header { padding: 40px 50px; border-bottom: 1px dashed #111; }

.ch-top { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 20px; }
.ch-tag { font-family: monospace; font-size: 11px; font-weight: 800; color: #111; letter-spacing: 2px; }
.link-hardcopy { font-family: monospace; font-size: 10px; font-weight: 700; color: #888; text-decoration: none; letter-spacing: 1px; border-bottom: 1px solid transparent; transition: 0.3s; }
.link-hardcopy:hover { color: #ff8002; border-color: #ff8002; }

.ch-title { font-family: 'Playfair Display', serif; font-size: 42px; font-style: italic; font-weight: 700; margin: 0 0 10px 0; color: #111; }
.ch-desc { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 1.5px; line-height: 1.6; margin: 0 0 40px 0; max-width: 80%; }

.macro-ref { display: flex; flex-direction: column; gap: 5px; }
.ref-label { font-family: monospace; font-size: 10px; font-weight: 700; color: #ff8002; letter-spacing: 4px; }
.ref-number { font-family: 'Inter', sans-serif; font-size: clamp(40px, 8vw, 90px); font-weight: 800; color: #111; line-height: 0.9; letter-spacing: -3px; margin-left: -4px;}

.content-items { display: flex; flex-direction: column; }

.item-th {
    display: grid; grid-template-columns: 1fr 80px 100px;
    padding: 15px 50px; border-bottom: 1px dashed #111;
    font-family: monospace; font-size: 9px; font-weight: 700; color: #888; letter-spacing: 2px;
}

.item-tr {
    display: grid; grid-template-columns: 1fr 80px 100px; align-items: center;
    padding: 20px 50px; border-bottom: 1px dashed #e5e5e5;
    transition: background 0.3s;
}
.item-tr:hover { background: #fafafa; border-bottom-color: #111; }

.td-col { font-family: monospace; font-size: 12px; font-weight: 600; color: #111; }
.td-main { display: flex; align-items: center; gap: 20px; }

.item-idx { font-size: 10px; color: #888; }
.item-visual { width: 45px; aspect-ratio: 3/4; background: transparent; mix-blend-mode: multiply; }
.item-visual img { width: 100%; height: 100%; object-fit: contain; filter: grayscale(100%) contrast(1.2); }

.item-name { font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 600; font-style: italic; color: #111; }
.td-qty { text-align: center; color: #888; }
.td-price { text-align: right; }

.content-financials {
    padding: 30px 50px; border-bottom: 1px dashed #111;
    display: flex; flex-direction: column; gap: 12px; align-items: flex-end;
}
.fin-row { width: 100%; max-width: 300px; display: flex; justify-content: space-between; font-family: monospace; font-size: 10px; font-weight: 600; color: #888; letter-spacing: 1px; }
.fin-total { border-top: 1px dashed #111; padding-top: 15px; margin-top: 5px; color: #111; font-weight: 800; font-size: 11px; align-items: baseline; }
.total-val { font-family: 'Playfair Display', serif; font-size: 28px; font-style: italic; }

.content-footer {
    padding: 40px 50px; display: flex; justify-content: space-between; align-items: center;
}
.dispatch-eta { display: flex; flex-direction: column; gap: 5px; }
.eta-label { font-family: monospace; font-size: 9px; color: #888; font-weight: 700; letter-spacing: 2px; }
.eta-val { font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 800; color: #111; }

.btn-return-macro {
    background: #111; color: #fff; padding: 20px 30px; text-decoration: none;
    font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 2px;
    display: inline-flex; align-items: center; gap: 15px; transition: 0.4s ease;
}
.btn-return-macro span { transition: transform 0.4s ease; }
.btn-return-macro:hover { background: #ff8002; }
.btn-return-macro:hover span { transform: translateX(5px); }

.stamp-authorized {
    position: absolute; bottom: 120px; left: 80px;
    font-family: monospace; font-size: 28px; font-weight: 800; letter-spacing: 6px;
    color: rgba(17, 17, 17, 0.05);
    border: 3px solid rgba(17, 17, 17, 0.05);
    padding: 10px 15px;
    transform: rotate(-15deg) scale(2);
    pointer-events: none; opacity: 0;
    animation: stampSlam 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards 1s;
}
@keyframes stampSlam { to { opacity: 1; transform: rotate(-15deg) scale(1); } }

@media (max-width: 768px) {
    .manifest-editorial-page { padding: 40px 0 60px 0; }
    .manifest-grid { grid-template-columns: 1fr; }
    .grid-vertical-meta { display: none;  }

    .content-header, .item-th, .item-tr, .content-financials, .content-footer { padding-left: 20px; padding-right: 20px; }

    .ref-number { font-size: 48px; }
    .item-th { display: none; }
    .item-tr { grid-template-columns: 1fr; gap: 15px; }
    .td-main { align-items: flex-start; }
    .td-qty, .td-price { text-align: left; padding-left: 65px; color: #888;}
    .td-price { font-size: 14px; color: #111; }

    .content-footer { flex-direction: column; align-items: flex-start; gap: 30px; }
    .btn-return-macro { width: 100%; justify-content: center; }

    .stamp-authorized { left: 20px; bottom: 180px; font-size: 20px; }
}
</style>

<?php include 'includes/footer.php'; ?>
