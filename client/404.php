<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';
?>

<main class="error-page-premium">
    <div class="noise-overlay"></div>

    <div class="container text-center premium-container">

        <div class="parallax-scene" id="scene">
            <span class="p-num layer" data-speed="-3">4</span>
            <span class="p-num layer zero" data-speed="4">0</span>
            <span class="p-num layer" data-speed="-2">4</span>
        </div>

        <div class="error-text-content">
            <h2 class="error-title">Lost in the sauce.</h2>
            <p class="error-text">
                The page you are looking for has vanished into the void.<br>
                But don't worry, your style journey doesn't end here.
            </p>

            <div class="error-actions">
                <a href="index.php" class="magnetic-btn btn-primary-black" id="magnet-btn">
                    <span class="btn-text">Take Me Home</span>
                </a>
            </div>
        </div>
    </div>
</main>

<style>

.error-page-premium {
    height: 85vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    font-family: 'Inter', sans-serif;
    position: relative;
    overflow: hidden;
    perspective: 1000px;
}

.noise-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 1;
}

.premium-container {
    position: relative;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.parallax-scene {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: -20px;
    user-select: none;
}

.p-num {
    font-size: 220px;
    font-weight: 900;
    color: #0f172a;
    line-height: 1;
    letter-spacing: -10px;
    transition: transform 0.1s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    will-change: transform;
    text-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
}

.p-num.zero {
    color: transparent;
    -webkit-text-stroke: 4px #ff8002;
    margin: 0 -15px;
    z-index: 2;
    text-shadow: none;
}

@keyframes textReveal {
    0% { opacity: 0; transform: translateY(30px) scale(0.95); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}

.error-text-content {
    opacity: 0;
    animation: textReveal 1s cubic-bezier(0.23, 1, 0.32, 1) 0.3s forwards;
}

.error-title {
    font-size: 36px;
    color: #0f172a;
    margin: 0 0 15px 0;
    font-weight: 900;
    letter-spacing: -1px;
}

.error-text {
    color: #64748b;
    font-size: 16px;
    line-height: 1.8;
    margin-bottom: 40px;
    font-weight: 500;
}

.error-actions {
    display: flex;
    justify-content: center;
}

.magnetic-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #0f172a;
    color: #fff;
    text-decoration: none;
    padding: 18px 40px;
    border-radius: 100px;
    font-weight: 700;
    font-size: 16px;
    transition: background 0.3s, box-shadow 0.3s;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);

    will-change: transform;
}

.magnetic-btn:hover {
    background: #ff8002;
    box-shadow: 0 15px 35px rgba(255, 128, 2, 0.3);
}

.btn-text {
    pointer-events: none;
    transition: transform 0.1s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

@media (max-width: 768px) {
    .p-num { font-size: 150px; }
    .error-title { font-size: 28px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const layers = document.querySelectorAll('.layer');
    const heroSection = document.querySelector('.error-page-premium');

    heroSection.addEventListener('mousemove', (e) => {
        const x = (window.innerWidth - e.pageX * 2) / 100;
        const y = (window.innerHeight - e.pageY * 2) / 100;

        layers.forEach(layer => {
            const speed = layer.getAttribute('data-speed');
            const xPos = x * speed;
            const yPos = y * speed;
            layer.style.transform = `translate(${xPos}px, ${yPos}px)`;
        });
    });

    heroSection.addEventListener('mouseleave', () => {
        layers.forEach(layer => {
            layer.style.transition = 'transform 0.6s cubic-bezier(0.23, 1, 0.32, 1)';
            layer.style.transform = `translate(0px, 0px)`;
        });
    });
    heroSection.addEventListener('mouseenter', () => {
        layers.forEach(layer => {
            layer.style.transition = 'none';
        });
    });

    const magnet = document.getElementById('magnet-btn');
    const magnetText = magnet.querySelector('.btn-text');

    magnet.addEventListener('mousemove', (e) => {
        const rect = magnet.getBoundingClientRect();
        const h = rect.width / 2;
        const w = rect.height / 2;
        const x = e.clientX - rect.left - h;
        const y = e.clientY - rect.top - w;

        magnet.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
        magnetText.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
    });

    magnet.addEventListener('mouseleave', () => {
        magnet.style.transform = `translate(0px, 0px)`;
        magnetText.style.transform = `translate(0px, 0px)`;

        magnet.style.transition = 'transform 0.6s cubic-bezier(0.23, 1, 0.32, 1), background 0.3s';
        magnetText.style.transition = 'transform 0.6s cubic-bezier(0.23, 1, 0.32, 1)';
    });

    magnet.addEventListener('mouseenter', () => {
        magnet.style.transition = 'none';
        magnetText.style.transition = 'none';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
