<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/header.php';
?>

<div class="custom-cursor"></div>
<div class="custom-cursor-follower"></div>

<main class="about-page-ultra">
    <div class="noise-overlay"></div>

    <section class="ultra-hero">
        <div class="hero-content">
            <span class="est-label mask-text"><span class="mask-inner" style="transition-delay: 0.1s;">ESTABLISHED 2021</span></span>
            <h1 class="hero-title">
                <div class="mask-text"><span class="mask-inner" style="transition-delay: 0.2s;">INTENTIONAL</span></div>
                <div class="mask-text"><span class="mask-inner" style="transition-delay: 0.3s;"><i style="font-family: serif; font-weight: 400; color: #ff8002;">LIVING.</i></span></div>
            </h1>
        </div>
    </section>

    <div class="marquee-container">
        <div class="marquee-content">
            <span>THE BEAUTY OF ESSENTIAL ✦ DESIGNED TO LAST ✦</span>
            <span>THE BEAUTY OF ESSENTIAL ✦ DESIGNED TO LAST ✦</span>
            <span>THE BEAUTY OF ESSENTIAL ✦ DESIGNED TO LAST ✦</span>
            <span>THE BEAUTY OF ESSENTIAL ✦ DESIGNED TO LAST ✦</span>
        </div>
    </div>

    <div class="container">
        <section class="ultra-story" id="story-scene">
            <div class="story-left">
                <div class="mask-text"><h2 class="mask-inner sr-trigger">Our Story</h2></div>
                <div class="story-desc">
                    <p class="sr-fade">Founded on the principles of intentional living, FAIFA was born from a desire to create timeless pieces that honor the harmony between form and function.</p>
                    <p class="sr-fade" style="transition-delay: 0.15s;">We strip away the noise of fast fashion and return to a slower, more thoughtful way of creating. Every item is a testament to this philosophy—designed to last, intended to be loved.</p>
                </div>
            </div>

            <div class="story-right">
                <div class="image-reveal-mask sr-image">
                    <img src="assets/images/about-portrait.jpg" alt="Our Brand Story" class="ultra-parallax-img hover-expand" onerror="this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=800'">
                </div>
            </div>

            <div class="story-fingerprints">
                <div class="fp-item fp-needle layer" data-depth="0.1" data-speed="1.2">
                    <svg viewBox="0 0 100 100"><path d="M10,90 L90,10 M90,10 L75,15 M90,10 L85,25"></path></svg>
                </div>
                <div class="fp-item fp-compass layer" data-depth="0.3" data-speed="0.8">
                    <svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="45"></circle><path d="M50,5 L50,95 M5,50 L95,50 M50,5 L60,30 L50,50 L40,30 Z"></path></svg>
                </div>
                <div class="fp-item fp-leaf layer" data-depth="0.2" data-speed="1.5">
                    <svg viewBox="0 0 100 100"><path d="M50,95 C70,75 90,60 90,40 C90,20 70,5 50,5 C30,5 10,20 10,40 C10,60 30,75 50,95 Z M50,5 L50,95 M50,40 L90,40 M50,40 L10,40"></path></svg>
                </div>
                <div class="fp-item fp-spool layer" data-depth="0.4" data-speed="1.1">
                    <svg viewBox="0 0 100 100"><rect x="20" y="5" width="60" height="15" rx="5"></rect><rect x="20" y="80" width="60" height="15" rx="5"></rect><rect x="35" y="20" width="30" height="60"></rect><path d="M35,25 C45,25 55,25 65,25 M35,35 C45,35 55,35 65,35 M35,45 C45,45 55,45 65,45 M35,55 C45,55 55,55 65,55 M35,65 C45,65 55,65 65,65"></path></svg>
                </div>
                <div class="fp-item fp-triangle layer" data-depth="0.15" data-speed="0.9">
                    <svg viewBox="0 0 100 100"><path d="M50,5 L95,95 L5,95 Z"></path></svg>
                </div>
            </div>
        </section>

        <section class="ultra-values">
            <div class="mask-text" style="text-align: center; margin-bottom: 50px;">
                <h5 class="section-tag mask-inner sr-trigger">OUR CORE VALUES</h5>
            </div>

            <div class="values-grid">
                <div class="value-card magnetic-3d sr-fade">
                    <div class="card-glare"></div>
                    <div class="value-icon">🍃</div>
                    <h3>Sustainability</h3>
                    <p>Organic, recycled, ethically sourced materials to minimize our footprint.</p>
                </div>

                <div class="value-card magnetic-3d sr-fade" style="transition-delay: 0.15s;">
                    <div class="card-glare"></div>
                    <div class="value-icon">💎</div>
                    <h3>Quality</h3>
                    <p>Compromise is not in our vocabulary. Lifelong durability guaranteed.</p>
                </div>

                <div class="value-card magnetic-3d sr-fade" style="transition-delay: 0.3s;">
                    <div class="card-glare"></div>
                    <div class="value-icon">📐</div>
                    <h3>Design</h3>
                    <p>Minimalism isn't just an aesthetic; it's focusing on what truly matters.</p>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
/* ==========================================
   🎨 ULTRA PREMIUM STYLES
   ========================================== */

body { cursor: none; }
.about-page-ultra {
    background: #fdfdfc;
    color: #0f172a;
    font-family: 'Inter', sans-serif;
    overflow-x: hidden;
    position: relative;
    perspective: 1000px;
}

.noise-overlay {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.05'/%3E%3C/svg%3E");
    pointer-events: none; z-index: 100; mix-blend-mode: multiply;
}

.custom-cursor {
    position: fixed; top: 0; left: 0; width: 8px; height: 8px;
    background: #fff; border-radius: 50%; pointer-events: none;
    z-index: 9999; mix-blend-mode: difference;
    transform: translate(-50%, -50%); transition: width 0.3s, height 0.3s;
}
.custom-cursor-follower {
    position: fixed; top: 0; left: 0; width: 40px; height: 40px;
    border: 1px solid rgba(255,255,255,0.5); border-radius: 50%; pointer-events: none;
    z-index: 9998; mix-blend-mode: difference;
    transform: translate(-50%, -50%); transition: transform 0.15s linear, width 0.3s, height 0.3s;
}
body:hover .custom-cursor { opacity: 1; }
.hover-expand:hover ~ .custom-cursor { width: 60px; height: 60px; background: #fff; mix-blend-mode: difference; }

.mask-text { overflow: hidden; line-height: 1.1; vertical-align: top; display: block; }
.mask-inner {
    display: inline-block; transform: translateY(110%) rotate(3deg);
    transform-origin: top left; transition: transform 1.2s cubic-bezier(0.19, 1, 0.22, 1);
}
.mask-inner.is-visible, .ultra-hero .mask-inner { transform: translateY(0) rotate(0); }

.ultra-hero { height: 90vh; display: flex; align-items: center; justify-content: center; text-align: center; }
.est-label { font-size: 14px; font-weight: 800; letter-spacing: 4px; margin-bottom: 20px; text-transform: uppercase; }
.hero-title { font-size: clamp(60px, 10vw, 130px); font-weight: 900; letter-spacing: -3px; margin: 0; text-transform: uppercase; }

.marquee-container {
    width: 100vw; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;
    padding: 20px 0; overflow: hidden; background: #fff; position: relative; z-index: 2;
    transform: rotate(-2deg) scale(1.05); margin: -40px 0 60px 0; box-shadow: 0 20px 40px rgba(0,0,0,0.03);
}
.marquee-content {
    display: flex; white-space: nowrap; width: max-content;
    animation: marqueeScroll 20s linear infinite; font-size: 18px; font-weight: 800; letter-spacing: 2px; color: #ff8002;
}
.marquee-content span { padding: 0 30px; }
@keyframes marqueeScroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

.ultra-story {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    padding: 60px 0;
    position: relative;
}

.story-left {
    max-width: 480px;
    margin-left: auto;
    position: relative; z-index: 10;
}

.story-left h2 { font-size: 42px; font-weight: 800; margin-bottom: 30px; letter-spacing: -1px; }
.story-desc p { font-size: 16px; line-height: 1.8; color: #475569; margin-bottom: 20px; }

.story-right {
    display: flex;
    justify-content: flex-start;
    position: relative; z-index: 10;
}

.image-reveal-mask {
    width: 100%;
    max-width: 420px;
    border-radius: 16px;
    overflow: hidden;
    aspect-ratio: 4/5;
    clip-path: inset(100% 0 0 0); transition: clip-path 1.5s cubic-bezier(0.19, 1, 0.22, 1);
    position: relative;
}
.image-reveal-mask.is-visible { clip-path: inset(0 0 0 0); }
.ultra-parallax-img {
    width: 100%; height: 120%; object-fit: cover; transform: scale(1.2);
    transition: transform 2s cubic-bezier(0.19, 1, 0.22, 1); will-change: transform;
}
.image-reveal-mask.is-visible .ultra-parallax-img { transform: scale(1) translateY(0); }

.story-fingerprints {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    pointer-events: none;
    z-index: 1;
}

.fp-item {
    position: absolute;
    fill: none;
    stroke: #0f172a;
    stroke-width: 1px;
    opacity: 0.1;
    will-change: transform;

    transition: transform 0.1s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.fp-needle { width: 120px; top: 10%; right: 15%; }
.fp-compass { width: 180px; bottom: 15%; right: 5%; }
.fp-leaf { width: 150px; top: 40%; right: 10%; }
.fp-spool { width: 100px; bottom: 40%; left: 50%; transform: translateX(-50%); }
.fp-triangle { width: 80px; top: -10%; left: 55%; }

.ultra-values { padding: 60px 0 100px 0; }
.section-tag { font-size: 13px; font-weight: 800; letter-spacing: 3px; color: #ff8002; }
.values-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; perspective: 1000px; }

.value-card {
    background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(226, 232, 240, 0.8);
    padding: 50px 30px; border-radius: 24px; text-align: center; position: relative;
    transform-style: preserve-3d; transition: transform 0.1s ease, box-shadow 0.1s ease;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}
.card-glare {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 24px;
    background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.8), transparent);
    opacity: 0; pointer-events: none; transition: opacity 0.3s; mix-blend-mode: overlay;
}
.value-card:hover .card-glare { opacity: 1; }

.value-icon { font-size: 36px; margin-bottom: 20px; transform: translateZ(30px); }
.value-card h3 { font-size: 20px; font-weight: 800; margin-bottom: 15px; transform: translateZ(20px); }
.value-card p { font-size: 14px; color: #64748b; line-height: 1.6; transform: translateZ(10px); }

.sr-fade { opacity: 0; transform: translateY(30px); transition: all 1.2s cubic-bezier(0.19, 1, 0.22, 1); }
.sr-fade.is-visible { opacity: 1; transform: translateY(0); }

@media (max-width: 900px) {
    .ultra-story { grid-template-columns: 1fr; gap: 40px; padding: 40px 0; }
    .story-left { max-width: 100%; text-align: center; }
    .story-right { justify-content: center; }
    .image-reveal-mask { max-width: 100%; aspect-ratio: 16/9; }
    .story-fingerprints { display: none; }
    .values-grid { grid-template-columns: 1fr; }
    body { cursor: auto; } .custom-cursor, .custom-cursor-follower { display: none; }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {

    // --- 1. Custom Cursor Logic ---
    const cursor = document.querySelector('.custom-cursor');
    const follower = document.querySelector('.custom-cursor-follower');
    let mouseX = 0, mouseY = 0, posX = 0, posY = 0;

    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX; mouseY = e.clientY;
        cursor.style.left = mouseX + 'px';
        cursor.style.top = mouseY + 'px';
    });

    function cursorLoop() {
        posX += (mouseX - posX) * 0.15;
        posY += (mouseY - posY) * 0.15;
        follower.style.left = posX + 'px';
        follower.style.top = posY + 'px';
        requestAnimationFrame(cursorLoop);
    }
    cursorLoop();

    document.querySelectorAll('a, button, .hover-expand').forEach(el => {
        el.addEventListener('mouseenter', () => {
            cursor.style.transform = 'translate(-50%, -50%) scale(5)';
            follower.style.opacity = '0';
        });
        el.addEventListener('mouseleave', () => {
            cursor.style.transform = 'translate(-50%, -50%) scale(1)';
            follower.style.opacity = '1';
        });
    });

    const layers = document.querySelectorAll('.layer');
    const storyScene = document.getElementById('story-scene');

    storyScene.addEventListener('mousemove', (e) => {
        const rect = storyScene.getBoundingClientRect();
        const xOffset = (e.clientX - rect.left - rect.width / 2) / (rect.width / 2);
        const yOffset = (e.clientY - rect.top - rect.height / 2) / (rect.height / 2);

        layers.forEach(layer => {
            const speed = layer.getAttribute('data-speed');
            const depth = layer.getAttribute('data-depth');

            const xPos = xOffset * speed * depth * 50; // Amplitude Factor
            const yPos = yOffset * speed * depth * 50;

            layer.style.transform = `translate(${xPos}px, ${yPos}px)`;
        });
    });

    // --- 2. Scroll Reveal Engine ---
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.sr-trigger, .sr-fade, .sr-image').forEach(el => {
        observer.observe(el);
    });

    // --- 3. Parallax Image Scroll ---
    const parallaxImg = document.querySelector('.ultra-parallax-img');
    const mask = document.querySelector('.image-reveal-mask');
    window.addEventListener('scroll', () => {
        if (!parallaxImg || !mask) return;
        const rect = mask.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            const scrollPercent = (window.innerHeight - rect.top) / (window.innerHeight + rect.height);
            parallaxImg.style.transform = `translateY(${(scrollPercent * 20) - 10}%) scale(1)`;
        }
    });

    // --- 4. Magnetic 3D Cards Physics ---
    const cards = document.querySelectorAll('.magnetic-3d');
    cards.forEach(card => {
        const glare = card.querySelector('.card-glare');

        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -12;
            const rotateY = ((x - centerX) / centerX) * 12;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;

            if(glare) {
                glare.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.6), transparent 50%)`;
            }
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
            card.style.transition = `transform 0.5s cubic-bezier(0.23, 1, 0.32, 1)`;
            if(glare) glare.style.opacity = '0';
        });

        card.addEventListener('mouseenter', () => {
            card.style.transition = `none`;
            if(glare) glare.style.opacity = '1';
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
