<?php
include 'includes/header.php';
include 'includes/db.php';

$cat_res = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE category NOT LIKE '%Sale%'");
?>

<main class="collections-page">
    <div id="museum-archive-wrapper">
        <div class="container museum-container">

            <header class="museum-header fade-in-blur" style="--delay: 1">
                <div class="header-inner">
                    <span class="mono-label">INDEX — 001</span>
                    <h1 class="serif-title">The Collection.</h1>
                </div>
            </header>

            <div class="museum-grid">
                <?php
                $idx = 0;
                while($cat = mysqli_fetch_assoc($cat_res)):
                    $idx++;
                    $category_name = $cat['category'];

                    if (strtolower($category_name) === 'men') {
                        $bg_image = "assets/images/cat-fashion.png";
                    } else {
                        $bg_image = "assets/images/cat-" . strtolower($category_name) . ".png";
                    }

                    $subtitles = [
                        'Men' => 'Tailored Essentials',
                        'Women' => 'Fluid Silhouettes',
                        'Accessories' => 'Timeless Accents'
                    ];
                    $desc = isset($subtitles[$category_name]) ? $subtitles[$category_name] : 'Everyday Wear';
                ?>

                <a href="product.php?cat=<?php echo urlencode($category_name); ?>" class="museum-card fade-up-stagger" style="--delay: <?php echo $idx + 1; ?>">
                    <div class="card-visual" data-tilt data-tilt-max="1.5" data-tilt-speed="3000" data-tilt-glare="false">
                        <img src="<?php echo $bg_image; ?>" alt="<?php echo htmlspecialchars($category_name); ?>"
                             onerror="this.src='https://placehold.co/600x800/f1f5f9/94a3b8?text=<?php echo urlencode($category_name); ?>'">
                    </div>

                    <div class="card-placard">
                        <span class="placard-idx">0<?php echo $idx; ?>.</span>
                        <div class="placard-info">
                            <h3 class="placard-title"><?php echo ucfirst($category_name); ?></h3>
                            <span class="placard-desc"><?php echo $desc; ?></span>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>

                <?php $idx++; ?>
                <a href="product.php?cat=new" class="museum-card fade-up-stagger" style="--delay: <?php echo $idx + 1; ?>">
                    <div class="card-visual" data-tilt data-tilt-max="1.5" data-tilt-speed="3000" data-tilt-glare="false">
                        <img src="assets/images/new-arrivals-bg.png" alt="New Arrivals"
                             onerror="this.src='https://placehold.co/600x800/f1f5f9/94a3b8?text=New+Arrivals'">
                    </div>

                    <div class="card-placard">
                        <span class="placard-idx">0<?php echo $idx; ?>.</span>
                        <div class="placard-info">
                            <h3 class="placard-title">Arrivals</h3>
                            <span class="placard-desc">The Latest Drops</span>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 MUSEUM ARCHIVE (THE ROW / JIL SANDER VIBE)
   ============================================== */

#museum-archive-wrapper {
    background: transparent;
    color: #111111;
    font-family: 'Inter', sans-serif;
    padding: 80px 0 120px 0;
}

.museum-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 40px;
}

.fade-in-blur { opacity: 0; filter: blur(5px); animation: blurIn 1.2s cubic-bezier(0.16, 1, 0.3, 1) calc(var(--delay) * 0.15s) forwards; }
.fade-up-stagger { opacity: 0; transform: translateY(30px); animation: staggerUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) calc(var(--delay) * 0.15s) forwards; }

@keyframes blurIn { to { opacity: 1; filter: blur(0); } }
@keyframes staggerUp { to { opacity: 1; transform: translateY(0); } }

#museum-archive-wrapper .museum-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    border-bottom: 1px solid #e5e5e5;
    padding-bottom: 20px;
    margin-bottom: 60px;
}

#museum-archive-wrapper .mono-label {
    font-family: monospace;
    font-size: 10px;
    color: #888;
    letter-spacing: 2px;
    font-weight: 700;
    margin-right: 20px;
}

#museum-archive-wrapper .serif-title {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-style: italic;
    font-weight: 400;
    margin: 0;
    display: inline-block;
}

#museum-archive-wrapper .museum-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
    align-items: start;
}

#museum-archive-wrapper .museum-card:nth-child(even) {
    margin-top: 40px;
}

#museum-archive-wrapper .museum-card {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    gap: 16px;
    outline: none;
}

#museum-archive-wrapper .card-visual {
    width: 100%;
    aspect-ratio: 4/5;
    background: #f8fafc;
    overflow: hidden;
}

#museum-archive-wrapper .card-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(100%);
    transition: transform 1.5s cubic-bezier(0.16, 1, 0.3, 1), filter 1s ease;
}

#museum-archive-wrapper .museum-card:hover .card-visual img {
    filter: grayscale(0%);
    transform: scale(1.03);
}

#museum-archive-wrapper .card-placard {
    display: flex;
    gap: 15px;
    padding-top: 10px;
    border-top: 1px solid transparent;
    transition: border-color 0.5s ease;
}

#museum-archive-wrapper .museum-card:hover .card-placard {
    border-top-color: #111;
}

#museum-archive-wrapper .placard-idx {
    font-family: monospace;
    font-size: 10px;
    color: #ff8002;
    font-weight: 700;
    line-height: 1.4;
}

#museum-archive-wrapper .placard-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

#museum-archive-wrapper .placard-title {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 400;
    margin: 0;
    color: #111;
    transition: color 0.4s ease;
}

#museum-archive-wrapper .museum-card:hover .placard-title {
    color: #ff8002;
}

#museum-archive-wrapper .placard-desc {
    font-family: monospace;
    font-size: 9px;
    color: #888;
    letter-spacing: 1px;
    text-transform: uppercase;
}

@media (max-width: 1024px) {

    #museum-archive-wrapper .museum-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 40px;
    }

    #museum-archive-wrapper .museum-card:nth-child(even) { margin-top: 40px; }
}

@media (max-width: 768px) {

    #museum-archive-wrapper { padding: 40px 0; }
    #museum-archive-wrapper .museum-container { padding: 0 20px; }
    #museum-archive-wrapper .museum-header { flex-direction: column; gap: 10px; }
    #museum-archive-wrapper .museum-grid { grid-template-columns: 1fr; gap: 50px; }
    #museum-archive-wrapper .museum-card:nth-child(even) { margin-top: 0; }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const visualWraps = document.querySelectorAll(".card-visual");
        if (visualWraps.length > 0) {
            VanillaTilt.init(visualWraps, {
                max: 1.5,
                speed: 3000,
                glare: false,
                scale: 1.005
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
