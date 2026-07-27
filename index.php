<?php
include 'includes/header.php';
include 'includes/db.php';

$sql = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
$featured_result = mysqli_query($conn, $sql);

$hero_banner_path = 'assets/images/background.png';
$hero_banner_v = file_exists($hero_banner_path) ? filemtime($hero_banner_path) : time();
?>

<main class="wireframe-homepage">

    <section class="hero-haute" style="background-image: url('assets/images/background.png?v=<?php echo $hero_banner_v; ?>');">
        <div class="hero-overlay"></div>
        <div class="container hero-container">
            <div class="hero-content fade-up" style="--delay: 1">
                <span class="hero-meta-tag">CAMPAIGN // VOL. 01</span>
                <h1 class="hero-title-serif">The Essentials.</h1>
                <p class="hero-desc-mono">CURATED ARCHIVES FOR THE MODERN SILHOUETTE. MINIMAL NOISE, MAXIMUM IMPACT.</p>
                <a href="categories.php" class="btn-hero-brutalist">
                    <span class="btn-text">EXPLORE COLLECTION</span>
                    <div class="btn-line"></div>
                    <span class="btn-arrow">→</span>
                </a>
            </div>
        </div>
    </section>

    <section class="service-wireframe">
        <div class="container">
            <div class="wireframe-grid fade-up" style="--delay: 2">

                <div class="w-item">
                    <div class="w-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    </div>
                    <div class="w-text">
                        <span class="w-title">GLOBAL DISPATCH</span>
                        <span class="w-desc">COMPLIMENTARY ON ACQUISITIONS OVER $100</span>
                    </div>
                </div>

                <div class="w-item">
                    <div class="w-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                    </div>
                    <div class="w-text">
                        <span class="w-title">CLIENT CONCIERGE</span>
                        <span class="w-desc">PRIVATE ASSISTANCE AVAILABLE 24/7</span>
                    </div>
                </div>

                <div class="w-item">
                    <div class="w-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                    <div class="w-text">
                        <span class="w-title">SECURE PROTOCOL</span>
                        <span class="w-desc">256-BIT SSL ENCRYPTED TRANSACTIONS</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="featured-archives">
        <div class="container">

            <div class="archive-header scroll-reveal">
                <div class="ah-left">
                    <span class="ah-meta">CURATION // 01</span>
                    <h2 class="ah-title">Featured Objects.</h2>
                </div>
                <a href="product.php" class="link-extend">
                    <span class="link-text">VIEW FULL ARCHIVE ↗</span>
                </a>
            </div>

            <div class="product-matrix">
                <?php
                if (mysqli_num_rows($featured_result) > 0):
                    $delay = 1;
                    while($product = mysqli_fetch_assoc($featured_result)):
                        $delay++;
                ?>
                <a href="product-details.php?id=<?php echo $product['id']; ?>" class="matrix-card scroll-reveal" style="--r-delay: <?php echo $delay; ?>" data-tilt data-tilt-max="1.5" data-tilt-speed="3000" data-tilt-glare="false">

                    <div class="card-visual">
                        <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='https://placehold.co/400x500/ebebeb/111?text=ARCHIVE'">
                        <div class="visual-ghost"><span>EXAMINE</span></div>
                    </div>

                    <div class="card-meta">
                        <div class="cm-top">
                            <span class="cm-category"><?php echo strtoupper($product['category']); ?></span>
                            <span class="cm-price">MYR <?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <h4 class="cm-title"><?php echo htmlspecialchars($product['name']); ?></h4>
                    </div>

                </a>
                <?php
                    endwhile;
                else:
                ?>
                    <div class="matrix-empty scroll-reveal">
                        <p>THE ARCHIVE IS CURRENTLY SILENT.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

</main>

<div id="haute-vip-toast" class="vip-toast-void">
    <div class="vip-dossier-toast">
        <button class="btn-abort-toast" onclick="closeVipToast()">✕</button>

        <div class="toast-top">
            <span class="toast-tag">CLIENT PROTOCOL // REWARDS</span>
        </div>

        <h2 class="toast-title">Daily Allocation.</h2>
        <p class="toast-desc">Secure your daily deposit of <strong>+50 FC</strong> to unlock exclusive acquisition privileges.</p>

        <div class="toast-actions">
            <a href="daily-check-in.php" class="btn-toast-solid">AUTHORIZE <span>→</span></a>
        </div>
    </div>
</div>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 THE WIREFRAME HOMEPAGE (TAILOR'S CUT)
   ============================================== */

.wireframe-homepage {
    background: #ebebeb;
    color: #111;
    font-family: 'Inter', sans-serif;
    padding-bottom: 100px;
    width: 100%;
}

.fade-up { opacity: 0; animation: runwayUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) calc(var(--delay) * 0.15s) forwards; }
@keyframes runwayUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

.hero-haute { position: relative; width: 100%; height: 90vh; background-size: cover; background-position: center; background-attachment: fixed; display: flex; align-items: center; }

.hero-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.5) 50%, rgba(255,255,255,0.95) 100%); z-index: 1; }

.hero-container { position: relative; z-index: 2; width: 100%; }
.hero-content { max-width: 800px; }
.hero-meta-tag { font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 4px; color: #ff8002; display: block; margin-bottom: 20px; }

.hero-title-serif { font-family: 'Playfair Display', serif; font-size: clamp(60px, 10vw, 120px); font-weight: 700; font-style: italic; color: #111; line-height: 0.9; letter-spacing: -2px; margin: 0 0 25px 0; transform: translateX(-5px); }

.hero-desc-mono { font-family: monospace; font-size: 11px; font-weight: 700; color: #111; letter-spacing: 2px; line-height: 1.8; margin: 0 0 40px 0; max-width: 450px; }

.btn-hero-brutalist { display: inline-flex; align-items: center; gap: 20px; background: transparent; border: none; padding: 0; color: #111; cursor: pointer; font-family: monospace; font-size: 12px; font-weight: 800; letter-spacing: 3px; text-decoration: none; position: relative; }
.btn-line { width: 50px; height: 2px; background: #111; transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1), background 0.3s; }
.btn-arrow { font-size: 16px; color: #111; transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), color 0.3s; }
.btn-hero-brutalist:hover { color: #ff8002; }
.btn-hero-brutalist:hover .btn-line { width: 100px; background: #ff8002; }
.btn-hero-brutalist:hover .btn-arrow { transform: translateX(10px); color: #ff8002; }

.service-wireframe { border-bottom: 1px dashed #d1d5db; border-top: 1px dashed #d1d5db; }
.wireframe-grid { display: grid; grid-template-columns: repeat(3, 1fr); }
.w-item { display: flex; flex-direction: column; gap: 20px; padding: 60px 40px; border-right: 1px dashed #d1d5db; transition: background 0.4s ease, color 0.4s ease; }
.w-item:last-child { border-right: none; }
.w-icon svg { width: 24px; height: 24px; color: inherit; transition: color 0.4s; }
.w-text { display: flex; flex-direction: column; gap: 8px; }
.w-title { font-family: 'Playfair Display', serif; font-size: 20px; font-style: italic; color: inherit; transition: color 0.4s; }
.w-desc { font-family: monospace; font-size: 9px; letter-spacing: 1.5px; opacity: 0.6; transition: opacity 0.4s; }
.w-item:hover { background: #111; color: #fff; }
.w-item:hover .w-icon svg { color: #ff8002; }
.w-item:hover .w-desc { opacity: 0.8; }

.featured-archives { padding: 100px 0; }

.archive-header {
    display: flex; justify-content: space-between; align-items: flex-end;
    margin-bottom: 60px;
    border-bottom: 1px solid #d1d5db;
    padding-bottom: 20px;
}
.ah-left { display: flex; flex-direction: column; gap: 10px; }
.ah-meta { font-family: monospace; font-size: 10px; color: #ff8002; font-weight: 700; letter-spacing: 3px; }
.ah-title { font-family: 'Playfair Display', serif; font-size: 56px; font-weight: 400; margin: 0; color: #111; line-height: 0.8; letter-spacing: -2px; }

.link-extend { display: inline-block; color: #111; text-decoration: none; padding-bottom: 5px; transition: all 0.3s ease; }
.link-text { font-family: monospace; font-size: 10px; letter-spacing: 3px; font-weight: 700; }
.link-extend:hover { color: #ff8002; }

.product-matrix { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; }
.matrix-card { text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 20px; outline: none; }

.card-visual {
    width: 100%;
    aspect-ratio: 3/4;
    background: transparent;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-visual img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    mix-blend-mode: darken;
    filter: contrast(1.05);
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.matrix-card:hover .card-visual img { transform: scale(1.05); }

.visual-ghost { position: absolute; inset: 0; background: rgba(17,17,17,0.05); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.5s ease; backdrop-filter: blur(2px); }
.matrix-card:hover .visual-ghost { opacity: 1; }
.visual-ghost span { color: #fff; font-family: monospace; font-size: 10px; font-weight: 700; letter-spacing: 3px; border: 1px solid #fff; padding: 12px 20px; transform: translateY(15px); transition: 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.matrix-card:hover .visual-ghost span { transform: translateY(0); }
.visual-ghost span:hover { background: #fff; color: #111; }

.card-meta { display: flex; flex-direction: column; gap: 10px; border-bottom: 1px dashed #d1d5db; padding-bottom: 10px; transition: border-color 0.4s; }
.matrix-card:hover .card-meta { border-bottom-color: #111; }

.cm-top { display: flex; justify-content: space-between; align-items: baseline; font-family: monospace; font-size: 8px; letter-spacing: 1.5px; color: #888; text-transform: uppercase; }
.cm-price { color: #111; font-weight: 700; }

.cm-title { font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 400; margin: 0; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.3s; }
.matrix-card:hover .cm-title { color: #ff8002; }

.matrix-empty { grid-column: 1 / -1; padding: 100px 0; text-align: center; font-family: monospace; font-size: 11px; color: #888; letter-spacing: 2px; border: 1px dashed #d1d5db; }

.vip-toast-void {
    position: fixed; bottom: 30px; right: 30px; z-index: 99999;
    opacity: 0; pointer-events: none; transform: translateY(50px);
    transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.vip-toast-void.active { opacity: 1; pointer-events: auto; transform: translateY(0); }

.vip-dossier-toast {
    background: #111; color: #fff; width: 340px; padding: 30px 25px;
    position: relative; box-shadow: 10px 10px 0 rgba(17, 17, 17, 0.15);
    border: 1px solid #333;
}

.btn-abort-toast { position: absolute; top: 15px; right: 15px; background: transparent; border: none; color: #888; font-size: 14px; cursor: pointer; transition: 0.3s; }
.btn-abort-toast:hover { color: #fff; transform: rotate(90deg); }

.toast-top { margin-bottom: 15px; }
.toast-tag { font-family: monospace; font-size: 8px; font-weight: 700; letter-spacing: 2px; color: #888; }
.toast-title { font-family: 'Playfair Display', serif; font-size: 24px; font-style: italic; font-weight: 400; margin: 0 0 10px 0; color: #fff; }
.toast-desc { font-family: 'Inter', sans-serif; font-size: 11px; color: #cbd5e1; line-height: 1.6; margin: 0 0 25px 0; }
.toast-desc strong { color: #ff8002; }

.toast-actions { display: flex; }

.btn-toast-solid {
    background: transparent; color: #fff; text-decoration: none;
    font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 2px;
    border-bottom: 1px solid rgba(255,255,255,0.3); padding-bottom: 4px;
    transition: 0.4s ease; display: inline-flex; align-items: center; gap: 8px;
}
.btn-toast-solid span { transition: transform 0.4s; }
.btn-toast-solid:hover { color: #ff8002; border-color: #ff8002; }
.btn-toast-solid:hover span { transform: translateX(4px); }

.scroll-reveal { opacity: 0; transform: translateY(40px); transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1); transition-delay: calc(var(--r-delay, 0) * 0.15s); }
.scroll-reveal.visible { opacity: 1; transform: translateY(0); }

@media (max-width: 1024px) {
    .wireframe-grid { grid-template-columns: 1fr; }
    .w-item { border-right: none; border-bottom: 1px dashed #d1d5db; padding: 40px; }
    .w-item:last-child { border-bottom: none; }
    .product-matrix { grid-template-columns: repeat(2, 1fr); gap: 40px 20px; }
    .ah-title { font-size: 48px; }
}
@media (max-width: 768px) {
    .hero-haute { height: 80vh; background-attachment: scroll; }
    .product-matrix { grid-template-columns: 1fr; gap: 50px; }
    .archive-header { flex-direction: column; align-items: flex-start; gap: 20px; }
    .vip-toast-void { bottom: 20px; right: 20px; left: 20px; }
    .vip-dossier-toast { width: auto; }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const popup = document.getElementById('haute-vip-toast');
        if (popup) {
            const todayStr = new Date().toDateString();
            const lastSeenStr = localStorage.getItem('faifa_vip_toast_v2');

            if (lastSeenStr !== todayStr) {
                setTimeout(() => {
                    popup.classList.add('active');
                }, 800);
            }
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.scroll-reveal').forEach(el => { observer.observe(el); });

        if (typeof VanillaTilt !== 'undefined') {
            const cards = document.querySelectorAll(".matrix-card");
            if (cards.length > 0) {
                VanillaTilt.init(cards, { max: 1.5, speed: 3000, glare: false, scale: 1.01 });
            }
        }
    });

    function closeVipToast() {
        const popup = document.getElementById('haute-vip-toast');
        if(popup) popup.classList.remove('active');
        localStorage.setItem('faifa_vip_toast_v2', new Date().toDateString());
    }
</script>

<?php include 'includes/footer.php'; ?>
