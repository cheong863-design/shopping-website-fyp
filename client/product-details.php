<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';
include 'includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: product.php");
    exit();
}

$avg_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM product_reviews WHERE product_id = $id AND status = 'Approved'"));
$avg_rating = round($avg_res['avg_rating'], 1) ?: 0;
$rev_count = $avg_res['count'];
?>

<main class="editorial-detail-page">

    <div class="editorial-split-layout">

        <aside class="info-sticky-panel fade-in-up" style="--delay: 0">
            <div class="info-inner-wrapper">

                <nav class="micro-breadcrumb">
                    <a href="index.php">ARCHIVE</a> <span class="divider">/</span>
                    <a href="product.php">CATALOG</a> <span class="divider">/</span>
                    <span class="current"><?php echo str_pad($id, 3, '0', STR_PAD_LEFT); ?></span>
                </nav>

                <div class="title-block">
                    <h1 class="macro-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="price-row">
                        <span class="currency">MYR</span>
                        <span class="price-val"><?php echo number_format($product['price'], 2); ?></span>
                    </div>
                </div>

                <div class="description-block">
                    <p class="body-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <div class="specs-block">
                    <div class="spec-item">
                        <span class="spec-key">MATERIAL</span>
                        <span class="spec-val"><?php echo htmlspecialchars($product['material'] ?: 'Premium Curated Fabric'); ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-key">WEIGHT</span>
                        <span class="spec-val"><?php echo htmlspecialchars($product['weight'] ?: 'Standard Silhouette'); ?></span>
                    </div>
                </div>

                <div class="action-block">
                    <a href="cart-actions.php?action=add&id=<?php echo $product['id']; ?>" class="magnetic-btn" id="acquire-btn">
                        <span class="btn-text">ADD TO BAG</span>
                    </a>

                    <div class="logistics-note">
                        <span class="pulse-dot"></span> COMPLIMENTARY DISPATCH ON ORDERS OVER MYR 500
                    </div>
                </div>

            </div>
        </aside>

        <section class="gallery-scroll-panel">
            <div class="image-stack">
                <div class="image-reveal-wrapper fade-in-up" style="--delay: 1">
                    <img src="assets/images/<?php echo $product['image']; ?>" class="gallery-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>

                <div class="image-reveal-wrapper fade-in-up" style="--delay: 2">
                    <img src="assets/images/<?php echo $product['image']; ?>" class="gallery-img img-zoom" alt="Detail">
                </div>
            </div>

            <div class="editorial-reviews fade-in-up" style="--delay: 3">
                <?php include 'product-review.php'; ?>
            </div>
        </section>

    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 ASYMMETRICAL EDITORIAL (QUIET LUXURY)
   ============================================== */
body {
    background: #ffffff;
    color: #111;
    font-family: 'Inter', sans-serif;
    margin: 0;
    padding: 0;
    -webkit-font-smoothing: antialiased;
}

::-webkit-scrollbar { width: 0px; background: transparent; }

.fade-in-up { opacity: 0; transform: translateY(30px); animation: softRise 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--delay) * 0.15s); }
@keyframes softRise { to { opacity: 1; transform: translateY(0); } }

.editorial-split-layout {
    display: flex;
    min-height: 100vh;
    width: 100%;
}

.info-sticky-panel {
    width: 35%;
    background: #ffffff;
    position: relative;
    border-right: 1px solid #f1f1f1;
}

.info-inner-wrapper {
    position: sticky;
    top: 0;
    height: 100vh;
    padding: 80px 60px;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
}

.micro-breadcrumb { font-family: monospace; font-size: 9px; letter-spacing: 2px; color: #888; margin-bottom: 60px; text-transform: uppercase; }
.micro-breadcrumb a { color: #888; text-decoration: none; transition: 0.3s; }
.micro-breadcrumb a:hover { color: #111; }
.micro-breadcrumb .divider { margin: 0 8px; opacity: 0.5; }
.micro-breadcrumb .current { color: #111; font-weight: 600; }

.title-block { margin-bottom: 40px; }
.macro-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(36px, 4vw, 56px);
    font-weight: 400; line-height: 1.1; margin: 0 0 20px 0;
    color: #111; letter-spacing: -1px;
}

.price-row { display: flex; align-items: baseline; gap: 8px; }
.currency { font-family: monospace; font-size: 11px; font-weight: 600; color: #888; letter-spacing: 1px; }
.price-val { font-size: 24px; font-weight: 500; color: #111; letter-spacing: -0.5px; }

.description-block { margin-bottom: 40px; }
.body-text { font-size: 13px; font-weight: 300; color: #555; line-height: 1.8; margin: 0; max-width: 90%; }

.specs-block { display: flex; flex-direction: column; gap: 15px; margin-bottom: auto;  }
.spec-item { display: flex; justify-content: space-between; font-size: 11px; border-bottom: 1px solid #f1f1f1; padding-bottom: 8px; }
.spec-key { font-family: monospace; color: #888; letter-spacing: 1px; }
.spec-val { color: #111; font-weight: 500; }

.action-block { margin-top: 40px; }

.magnetic-btn {
    display: flex; justify-content: center; align-items: center;
    width: 100%; height: 70px;
    background: #111; color: #fff; text-decoration: none;
    border-radius: 4px;
    cursor: pointer;
    position: relative; overflow: hidden;

    will-change: transform;
}
.magnetic-btn .btn-text {
    font-family: monospace; font-size: 11px; font-weight: 600; letter-spacing: 3px;
    position: relative; z-index: 2; pointer-events: none;
    transition: transform 0.3s ease;
}

.magnetic-btn::before {
    content: ''; position: absolute; top: 50%; left: 50%; width: 150%; aspect-ratio: 1/1;
    background: #ff8002; border-radius: 50%; z-index: 1;
    transform: translate(-50%, -50%) scale(0); transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.magnetic-btn:hover::before { transform: translate(-50%, -50%) scale(1); }

.magnetic-btn.is-loading::before { background: #f8f8f8; transform: translate(-50%, -50%) scale(1); }
.magnetic-btn.is-loading .btn-text { color: #111; }

.logistics-note { margin-top: 20px; font-family: monospace; font-size: 9px; color: #888; letter-spacing: 1px; display: flex; align-items: center; justify-content: center; gap: 8px; }
.pulse-dot { width: 4px; height: 4px; background: #111; border-radius: 50%; animation: pulse 2s infinite; }
@keyframes pulse { 50% { opacity: 0.2; } }

.gallery-scroll-panel {
    width: 65%;
    background: #fdfdfd;
    display: flex; flex-direction: column;
}

.image-stack { display: flex; flex-direction: column; gap: 2px;  }

.image-reveal-wrapper { width: 100%; overflow: hidden; background: #f4f4f4; }

.gallery-img {
    width: 100%; height: auto; display: block;
    object-fit: cover;

    filter: contrast(1.05) saturate(0.9);
}

.img-zoom { height: 80vh; object-fit: cover; transform: scale(1.3); transform-origin: top center; }

.editorial-reviews { padding: 80px; max-width: 800px; margin: 0 auto; background: #fff; width: 100%; box-sizing: border-box;}

@media (max-width: 1024px) {
    .editorial-split-layout { flex-direction: column; }

    .gallery-scroll-panel { width: 100%; order: 1; }
    .image-stack { gap: 0; }
    .img-zoom { height: auto; transform: scale(1); }

    .info-sticky-panel { width: 100%; order: 2; border-right: none; }
    .info-inner-wrapper { position: static; height: auto; padding: 40px 20px; }
    .specs-block { margin-bottom: 40px; }
    .magnetic-btn { transform: none !important;  }

    .editorial-reviews { padding: 40px 20px; }
}
</style>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        const magnetBtn = document.querySelector('.magnetic-btn');
        const btnText = magnetBtn.querySelector('.btn-text');

        if (window.innerWidth > 1024 && magnetBtn) {
            magnetBtn.addEventListener('mousemove', function(e) {
                const position = magnetBtn.getBoundingClientRect();

                const x = e.clientX - position.left - position.width / 2;
                const y = e.clientY - position.top - position.height / 2;

                magnetBtn.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
                btnText.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
            });

            magnetBtn.addEventListener('mouseleave', function() {
                magnetBtn.style.transition = 'transform 0.6s cubic-bezier(0.25, 1, 0.5, 1)';
                btnText.style.transition = 'transform 0.6s cubic-bezier(0.25, 1, 0.5, 1)';

                magnetBtn.style.transform = 'translate(0px, 0px)';
                btnText.style.transform = 'translate(0px, 0px)';

                setTimeout(() => {
                    magnetBtn.style.transition = 'none';
                    btnText.style.transition = 'transform 0.3s ease';
                }, 600);
            });
        }

        if(magnetBtn) {
            magnetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const targetUrl = magnetBtn.getAttribute('href');

                magnetBtn.classList.add('is-loading');
                btnText.textContent = 'SECURING ASSET...';
                magnetBtn.style.transform = 'translate(0px, 0px)';

                setTimeout(() => {
                    window.location.href = targetUrl;
                }, 800);
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
