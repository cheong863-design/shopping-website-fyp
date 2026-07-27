<?php
include 'includes/header.php';
include 'includes/db.php';

$category = isset($_GET['cat']) ? $_GET['cat'] : 'all';

if ($category == 'all') {
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $display_title = "The Archive";
} else {
    $safe_cat = mysqli_real_escape_string($conn, $category);
    $sql = "SELECT * FROM products WHERE category = '$safe_cat' ORDER BY id DESC";
    $display_title = ucfirst(strtolower($category));
}

$result = mysqli_query($conn, $sql);
$total_items = mysqli_num_rows($result);
?>

<main class="reflective-gallery-page">

    <header class="gallery-header focus-in" style="--delay: 0">
        <div class="header-container">
            <nav class="gallery-breadcrumb">
                <a href="index.php">HOME</a> <span class="slash">/</span>
                <span class="current">CATALOG (<?php echo strtoupper($category); ?>)</span>
            </nav>
            <h1 class="gallery-macro-title"><?php echo $display_title; ?>.</h1>
            <p class="gallery-count">CURATED_VOL: <?php echo str_pad($total_items, 2, '0', STR_PAD_LEFT); ?></p>
        </div>
    </header>

    <section class="gallery-container">
        <?php if ($total_items > 0): ?>
            <div class="staggered-portals-grid">
                <?php
                $idx = 1;
                while($row = mysqli_fetch_assoc($result)):
                ?>

                <article class="exhibit-portal reveal-trigger">
                    <a href="product-details.php?id=<?php echo $row['id']; ?>" class="portal-link">

                        <div class="mirror-portal-body">
                            <div class="mirror-glare"></div>

                            <div class="mirror-lens-viewport">
                                <img src="assets/images/<?php echo $row['image']; ?>"
                                     alt="<?php echo htmlspecialchars($row['name']); ?>"
                                     class="exhibit-img"
                                     onerror="this.src='https://placehold.co/600x600/ebebeb/111?text=ARTIFACT'">

                                <div class="shutter-curtain-circle"></div>
                            </div>
                        </div>

                        <div class="exhibit-intel">
                            <span class="exhibit-cat">[ <?php echo strtoupper($row['category']); ?> ]</span>
                            <h3 class="exhibit-name"><?php echo htmlspecialchars($row['name']); ?></h3>
                            <div class="exhibit-meta">
                                <span class="exhibit-id">REF_<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                <span class="exhibit-price">MYR <?php echo number_format($row['price'], 2); ?></span>
                            </div>
                        </div>

                    </a>
                </article>

                <?php
                    $idx++;
                endwhile;
                ?>
            </div>
        <?php else: ?>
            <div class="gallery-empty focus-in" style="--delay: 2">
                <h2 class="empty-serif">The Archive is currently resting.</h2>
                <p class="empty-mono">NO ARTIFACTS FOUND IN THIS SECTOR.</p>
                <a href="product.php" class="btn-underline-dark">RESET QUERY_</a>
            </div>
        <?php endif; ?>
    </section>

</main>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 THE REFLECTIVE PORTALS (MICRO-REFINED)
   ============================================== */

body { background: #fafafa; color: #111; font-family: 'Inter', sans-serif; overflow-x: hidden; }

.reflective-gallery-page { padding: 40px 0 120px 0; }
.header-container, .gallery-container { max-width: 1400px; margin: 0 auto; padding: 0 4vw; }

.focus-in { opacity: 0; transform: translateY(20px); filter: blur(4px); animation: opticalFocus 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--delay) * 0.15s); }
@keyframes opticalFocus { to { opacity: 1; transform: translateY(0); filter: blur(0); } }

.gallery-header { margin-bottom: 60px; text-align: center; display: flex; flex-direction: column; align-items: center; }
.gallery-breadcrumb { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 2px; margin-bottom: 20px; }
.gallery-breadcrumb a { color: #888; text-decoration: none; transition: 0.3s; }
.gallery-breadcrumb a:hover { color: #111; }
.gallery-breadcrumb .slash { margin: 0 10px; opacity: 0.3; }
.gallery-breadcrumb .current { color: #111; font-weight: 700; }
.gallery-macro-title { font-family: 'Playfair Display', serif; font-size: clamp(40px, 6vw, 72px); font-weight: 400; margin: 0 0 15px 0; color: #111; letter-spacing: -1px; line-height: 1; }
.gallery-count { font-family: monospace; font-size: 9px; font-weight: 600; color: #888; letter-spacing: 2px; }

.staggered-portals-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    column-gap: 3vw;
    row-gap: 80px;
}

.exhibit-portal:nth-child(4n+1) { margin-top: 0; }
.exhibit-portal:nth-child(4n+2) { margin-top: 40px; }
.exhibit-portal:nth-child(4n+3) { margin-top: 15px; }
.exhibit-portal:nth-child(4n+4) { margin-top: 55px; }

.exhibit-portal { display: flex; flex-direction: column; }
.portal-link { text-decoration: none; color: inherit; display: block; width: 100%; position: relative; }

.mirror-portal-body {
    width: 100%;
    aspect-ratio: 1/1;
    position: relative;
    border-radius: 50%;
    overflow: hidden;
    border: 1px solid #eaeaea;
    margin-bottom: 20px;
    background: #fdfdfd;

    box-shadow:
        inset 0 8px 15px rgba(0,0,0,0.1),
        inset 0 -4px 8px rgba(255,255,255,0.8),
        0 15px 30px rgba(0,0,0,0.04);

    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.6s ease, border-color 0.4s ease;
    will-change: transform;
}

.exhibit-portal:hover .mirror-portal-body {
    transform: translateY(-5px) scale(1.02);
    box-shadow:
        inset 0 12px 20px rgba(0,0,0,0.15),
        inset 0 -4px 8px rgba(255,255,255,0.9),
        0 20px 40px rgba(0,0,0,0.08);
    border-color: #d1d5db;
}

.mirror-glare {
    position: absolute; inset: 0; z-index: 10; pointer-events: none;
    background: linear-gradient(125deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 40%, rgba(255,255,255,0.02) 100%);
}

.mirror-lens-viewport {
    position: absolute; inset: 10px;
    border-radius: 50%; overflow: hidden; background: #f4f4f4; mix-blend-mode: multiply;
}

.exhibit-img {
    width: 100%; height: 100%; object-fit: cover;
    transform: scale(1.15); filter: contrast(1.05) saturate(1.05);
    transition: transform 1.6s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform;
}
.exhibit-portal:hover .exhibit-img { transform: scale(1.05); transition-duration: 0.8s; }

.shutter-curtain-circle {
    position: absolute; inset: -1px; background: #fafafa;
    transform-origin: center center; border-radius: 50%; z-index: 5;
    transition: transform 1s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform;
}
.exhibit-portal.is-revealed .shutter-curtain-circle { transform: scale(0); }
.exhibit-portal.is-revealed .exhibit-img { transform: scale(1); }

.exhibit-intel { display: flex; flex-direction: column; padding: 0 10px; text-align: center; }

.exhibit-cat { font-family: monospace; font-size: 8px; color: #888; letter-spacing: 2px; margin-bottom: 6px;}

.exhibit-name {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    font-weight: 400; margin: 0 0 10px 0; color: #111; font-style: italic;
}

.exhibit-meta { display: flex; justify-content: center; align-items: baseline; gap: 10px; border-top: 1px solid #e5e5e5; padding-top: 8px;}
.exhibit-id { font-family: monospace; font-size: 8px; font-weight: 600; color: #d1d5db; letter-spacing: 1px; }
.exhibit-price { font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; color: #111; }

.gallery-empty { text-align: center; padding: 100px 0; grid-column: 1 / -1; }
.empty-serif { font-family: 'Playfair Display', serif; font-size: 24px; font-style: italic; font-weight: 400; color: #111; margin: 0 0 10px 0; }
.empty-mono { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 2px; margin: 0 0 30px 0; }
.btn-underline-dark { color: #111; text-decoration: none; font-family: monospace; font-size: 9px; font-weight: 700; letter-spacing: 2px; border-bottom: 1px solid #111; padding-bottom: 3px; transition: 0.3s; }

@media (max-width: 1024px) {
    .staggered-portals-grid { grid-template-columns: repeat(3, 1fr); column-gap: 4vw; row-gap: 60px; }
    .exhibit-portal:nth-child(n) { margin-top: 0; }
    .exhibit-portal:nth-child(3n+2) { margin-top: 30px; }
    .exhibit-portal:nth-child(3n+3) { margin-top: 15px; }
}

@media (max-width: 768px) {
    .reflective-gallery-page { padding: 20px 0 80px 0; }
    .gallery-macro-title { font-size: 36px; }

    .staggered-portals-grid { grid-template-columns: repeat(2, 1fr); column-gap: 4vw; row-gap: 40px; }

    .exhibit-portal:nth-child(n) { margin-top: 0; }
    .exhibit-portal:nth-child(even) { margin-top: 20px; }

    .mirror-lens-viewport { inset: 6px; }
    .mirror-portal-body { margin-bottom: 12px; box-shadow: inset 0 5px 10px rgba(0,0,0,0.1), 0 10px 20px rgba(0,0,0,0.05); }

    .exhibit-intel { padding: 0 2px; }
    .exhibit-name { font-size: 14px; margin-bottom: 6px; }
    .exhibit-meta { gap: 6px; padding-top: 6px; }
    .exhibit-id { display: none; }
}
</style>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -50px 0px',
            threshold: 0.05
        };

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal-trigger').forEach(card => {
            revealObserver.observe(card);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
