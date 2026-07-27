<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';
include 'includes/db.php';

$query = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$selected_cats = isset($_GET['cat']) ? $_GET['cat'] : [];

$sql = "SELECT * FROM products WHERE (name LIKE '%$query%' OR category LIKE '%$query%')";

if (!empty($selected_cats)) {
    $cat_filter = "'" . implode("','", array_map(function($c) use ($conn) {
        return mysqli_real_escape_string($conn, $c);
    }, $selected_cats)) . "'";
    $sql .= " AND category IN ($cat_filter)";
}

$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$count = mysqli_num_rows($result);
?>

<main class="editorial-index-page">

    <div class="index-container">

        <header class="index-header focus-in" style="--delay: 0">
            <nav class="gallery-breadcrumb">
                <a href="index.php">HOME</a> <span class="slash">/</span>
                <span class="current">THE INDEX</span>
            </nav>

            <div class="search-results-title">
                <?php if(!empty($query)): ?>
                    <h1 class="macro-title">"<?php echo htmlspecialchars($query); ?>"</h1>
                    <p class="index-meta"><?php echo str_pad($count, 2, '0', STR_PAD_LEFT); ?> ENTRIES LOCATED</p>
                <?php else: ?>
                    <h1 class="macro-title">The Archive.</h1>
                    <p class="index-meta">BROWSE ALL AVAILABLE ENTRIES</p>
                <?php endif; ?>
            </div>
        </header>

        <div class="filter-sticky-bar focus-in" style="--delay: 1">
            <form action="search.php" method="GET" id="tactical-filter-form" class="horizontal-form">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($query); ?>">

                <span class="filter-label">FILTER BY:</span>
                <div class="pill-group">
                    <?php
                    $categories = ['Men', 'Women', 'Accessories'];
                    foreach ($categories as $cat):
                        $checked = in_array($cat, $selected_cats) ? 'checked' : '';
                    ?>
                    <label class="pill-checkbox">
                        <input type="checkbox" name="cat[]" value="<?php echo htmlspecialchars($cat); ?>"
                            <?php echo $checked; ?> onchange="this.form.submit()">
                        <span class="pill-visual"><?php echo strtoupper(htmlspecialchars($cat)); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <?php if(!empty($selected_cats) || !empty($query)): ?>
                    <a href="search.php" class="btn-clear-pills">CLEAR INDEX ✕</a>
                <?php endif; ?>
            </form>
        </div>

        <section class="index-results">
            <?php if ($count > 0): ?>
                <div class="staggered-portals-grid">
                    <?php
                    $idx = 2;
                    while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <article class="exhibit-portal stagger-in" style="--stagger: <?php echo $idx; ?>">
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
                <div class="void-state focus-in" style="--delay: 2">
                    <div class="void-symbol">∅</div>
                    <h2 class="void-serif">The archive remains silent.</h2>
                    <p class="void-mono">YOUR QUERY "<?php echo htmlspecialchars($query); ?>" YIELDED NO PHYSICAL ENTRIES IN THIS SECTOR.</p>
                    <a href="search.php" class="btn-void-return">RETURN TO INDEX</a>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 THE EDITORIAL INDEX (REFINED & COMPACT)
   ============================================== */

body { background: #fdfdfd; color: #111; font-family: 'Inter', sans-serif; overflow-x: hidden; }

.editorial-index-page { padding: 20px 0 120px 0; min-height: 80vh; }
.index-container { max-width: 1400px; margin: 0 auto; padding: 0 4vw; }

.focus-in { opacity: 0; transform: translateY(20px); filter: blur(5px); animation: opticalFocus 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--delay) * 0.1s); }
.stagger-in { opacity: 0; transform: translateY(20px); filter: blur(3px); transition: opacity 0.8s, transform 0.8s cubic-bezier(0.16, 1, 0.3, 1), filter 0.8s; }
.stagger-in.visible { opacity: 1; transform: translateY(0); filter: blur(0); }
@keyframes opticalFocus { to { opacity: 1; transform: translateY(0); filter: blur(0); } }

.index-header { padding: 10px 0 30px 0; border-bottom: 1px solid #111; margin-bottom: 0; display: flex; flex-direction: column; align-items: center; text-align: center; }

.gallery-breadcrumb { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 2px; margin-bottom: 25px; }
.gallery-breadcrumb a { color: #888; text-decoration: none; transition: 0.3s; }
.gallery-breadcrumb a:hover { color: #111; }
.gallery-breadcrumb .slash { margin: 0 10px; opacity: 0.3; }
.gallery-breadcrumb .current { color: #111; font-weight: 700; }

.search-results-title { display: flex; flex-direction: column; align-items: center; gap: 8px; }

.macro-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(24px, 4vw, 42px);
    font-style: italic; font-weight: 400; color: #111;
    line-height: 1.1; letter-spacing: -0.5px; margin: 0;
}

.index-meta { font-family: monospace; font-size: 9px; font-weight: 600; color: #888; letter-spacing: 2px; margin: 0; }

.filter-sticky-bar {
    position: sticky; top: 0; z-index: 50;
    background: rgba(253, 253, 253, 0.95); backdrop-filter: blur(10px);
    padding: 15px 0; border-bottom: 1px dashed #d1d5db; margin-bottom: 40px;
}

.horizontal-form { display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 15px; }
.filter-label { font-family: monospace; font-size: 9px; font-weight: 700; color: #888; letter-spacing: 2px; }

.pill-group { display: flex; gap: 8px; }
.pill-checkbox { cursor: pointer; }
.pill-checkbox input { display: none; }

.pill-visual {
    font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 1px; color: #888;
    border: 1px solid #e5e5e5; border-radius: 100px; padding: 8px 20px;
    display: inline-block; transition: all 0.3s ease;
}
.pill-checkbox:hover .pill-visual { border-color: #111; color: #111; }
.pill-checkbox input:checked + .pill-visual { background: #111; color: #fff; border-color: #111; }

.btn-clear-pills { font-family: monospace; font-size: 9px; font-weight: 800; color: #111; text-decoration: none; margin-left: 10px; transition: 0.3s; }
.btn-clear-pills:hover { color: #ff8002; }

.staggered-portals-grid { display: grid; grid-template-columns: repeat(4, 1fr); column-gap: 3vw; row-gap: 60px; }
.exhibit-portal:nth-child(4n+1) { margin-top: 0; }
.exhibit-portal:nth-child(4n+2) { margin-top: 30px; }
.exhibit-portal:nth-child(4n+3) { margin-top: 10px; }
.exhibit-portal:nth-child(4n+4) { margin-top: 40px; }

.portal-link { text-decoration: none; color: inherit; display: block; width: 100%; position: relative; }

.mirror-portal-body {
    width: 100%; aspect-ratio: 1/1; position: relative; border-radius: 50%; overflow: hidden;
    border: 1px solid #eaeaea; margin-bottom: 15px; background: #fdfdfd;
    box-shadow: inset 0 6px 12px rgba(0,0,0,0.08), inset 0 -3px 6px rgba(255,255,255,0.8), 0 10px 20px rgba(0,0,0,0.03);
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.6s ease, border-color 0.4s ease;
}
.exhibit-portal:hover .mirror-portal-body { transform: translateY(-4px) scale(1.02); box-shadow: inset 0 10px 18px rgba(0,0,0,0.12), inset 0 -3px 6px rgba(255,255,255,0.9), 0 15px 30px rgba(0,0,0,0.06); border-color: #d1d5db; }
.mirror-glare { position: absolute; inset: 0; z-index: 10; pointer-events: none; background: linear-gradient(125deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 40%, rgba(255,255,255,0.02) 100%); }
.mirror-lens-viewport { position: absolute; inset: 8px; border-radius: 50%; overflow: hidden; background: #f4f4f4; mix-blend-mode: multiply; }
.exhibit-img { width: 100%; height: 100%; object-fit: cover; transform: scale(1.15); filter: contrast(1.05) saturate(1.05); transition: transform 1.6s cubic-bezier(0.16, 1, 0.3, 1); }
.exhibit-portal:hover .exhibit-img { transform: scale(1.05); transition-duration: 0.8s; }

.shutter-curtain-circle { position: absolute; inset: -1px; background: #fafafa; transform-origin: center center; border-radius: 50%; z-index: 5; transition: transform 1s cubic-bezier(0.16, 1, 0.3, 1); }
.exhibit-portal.is-revealed .shutter-curtain-circle { transform: scale(0); }
.exhibit-portal.is-revealed .exhibit-img { transform: scale(1); }

.exhibit-intel { display: flex; flex-direction: column; padding: 0 10px; text-align: center; }
.exhibit-cat { font-family: monospace; font-size: 8px; color: #888; letter-spacing: 2px; margin-bottom: 4px;}
.exhibit-name { font-family: 'Playfair Display', serif; font-size: 16px; font-weight: 400; margin: 0 0 8px 0; color: #111; font-style: italic; transition: transform 0.3s; }
.exhibit-portal:hover .exhibit-name { transform: translateX(3px); }
.exhibit-meta { display: flex; justify-content: center; align-items: baseline; gap: 10px; border-top: 1px solid #e5e5e5; padding-top: 8px;}
.exhibit-id { font-family: monospace; font-size: 8px; font-weight: 600; color: #d1d5db; letter-spacing: 1px; }
.exhibit-price { font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 600; color: #111; }

.void-state { padding: 100px 0; display: flex; flex-direction: column; align-items: center; text-align: center; }
.void-symbol { font-family: 'Playfair Display', serif; font-size: 64px; font-weight: 300; color: #e5e5e5; line-height: 1; margin-bottom: 15px; animation: floatVoid 4s ease-in-out infinite alternate; }
@keyframes floatVoid { 0% { transform: translateY(0); } 100% { transform: translateY(-10px); } }
.void-serif { font-family: 'Playfair Display', serif; font-size: 24px; font-style: italic; font-weight: 400; margin: 0 0 10px 0; color: #111; }
.void-mono { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 2px; line-height: 1.6; max-width: 400px; margin-bottom: 30px; text-transform: uppercase;}
.btn-void-return { background: transparent; color: #111; text-decoration: none; padding: 0 0 4px 0; font-family: monospace; font-size: 10px; font-weight: 800; letter-spacing: 3px; border-bottom: 2px solid #111; transition: 0.3s; }
.btn-void-return:hover { color: #ff8002; border-color: #ff8002; padding-left: 8px; }

@media (max-width: 1024px) {
    .staggered-portals-grid { grid-template-columns: repeat(3, 1fr); column-gap: 4vw; row-gap: 60px; }
    .exhibit-portal:nth-child(n) { margin-top: 0; }
    .exhibit-portal:nth-child(3n+2) { margin-top: 25px; }
    .exhibit-portal:nth-child(3n+3) { margin-top: 10px; }
}

@media (max-width: 768px) {
    .editorial-index-page { padding: 20px 0 80px 0; }

    .macro-title { font-size: 32px; }

    .horizontal-form { flex-direction: column; align-items: flex-start; }
    .filter-sticky-bar { padding: 15px 0; margin-bottom: 30px; }
    .pill-group { flex-wrap: wrap; }

    .staggered-portals-grid { grid-template-columns: repeat(2, 1fr); column-gap: 4vw; row-gap: 30px; }
    .exhibit-portal:nth-child(n) { margin-top: 0; }
    .exhibit-portal:nth-child(even) { margin-top: 15px; }

    .mirror-lens-viewport { inset: 5px; }
    .void-symbol { font-size: 48px; }
}
</style>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -30px 0px',
            threshold: 0.05
        };

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal-trigger, .stagger-in').forEach(el => {
            revealObserver.observe(el);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
