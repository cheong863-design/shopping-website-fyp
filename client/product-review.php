<?php
$p_id = intval($_GET['id']);
$reviews_query = "SELECT r.*, u.full_name FROM product_reviews r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.product_id = $p_id AND r.status = 'Approved'
                  ORDER BY r.created_at DESC";
$reviews_res = mysqli_query($conn, $reviews_query);
$total_reviews = mysqli_num_rows($reviews_res);

$avg_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_rating FROM product_reviews WHERE product_id = $p_id"));
$avg_rating = round($avg_res['avg_rating'], 1) ?: 0;

$has_reviewed = false;
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $check_query = "SELECT id FROM product_reviews WHERE product_id = $p_id AND user_id = $u_id LIMIT 1";
    $check_res = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_res) > 0) {
        $has_reviewed = true;
    }
}
?>

<section class="client-journal-section" id="journal-section">

    <header class="journal-header journal-reveal" style="--delay: 1">
        <div class="jh-left">
            <h2 class="serif-huge">Client Journal.</h2>
            <p class="mono-meta">CURATED FEEDBACK FROM THE ARCHIVE</p>
        </div>
        <div class="jh-right">
            <span class="macro-score"><?php echo number_format($avg_rating, 1); ?></span>
            <div class="score-details">
                <span class="lux-sparkles">
                    <?php echo str_repeat('✦ ', floor($avg_rating)) . str_repeat('✧ ', 5 - floor($avg_rating)); ?>
                </span>
                <span class="count-tag">VOL. <?php echo str_pad($total_reviews, 2, '0', STR_PAD_LEFT); ?> REFS</span>
            </div>
        </div>
    </header>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'already_reviewed'): ?>
        <div class="journal-notice error journal-reveal" style="--delay: 2">
            <span>✕ DUPLICATE ENTRY: YOU HAVE ALREADY INSCRIBED IN THIS JOURNAL.</span>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'review_success'): ?>
        <div class="journal-notice success journal-reveal" style="--delay: 2">
            <span>✦ ENTRY SECURED: THANK YOU FOR YOUR CONTRIBUTION TO THE ARCHIVE.</span>
        </div>
    <?php endif; ?>

    <div class="journal-ledger">
        <?php if($total_reviews > 0): ?>
            <?php
            $stagger = 3;
            while($rev = mysqli_fetch_assoc($reviews_res)):
            ?>
                <article class="ledger-entry journal-reveal" style="--delay: <?php echo $stagger++; ?>">
                    <div class="entry-sidebar">
                        <h4 class="client-name"><?php echo htmlspecialchars($rev['full_name']); ?></h4>
                        <span class="entry-date"><?php echo date('d M Y', strtotime($rev['created_at'])); ?></span>
                        <span class="entry-sparks">
                            <?php echo str_repeat('✦ ', $rev['rating']) . str_repeat('✧ ', 5 - $rev['rating']); ?>
                        </span>
                    </div>
                    <div class="entry-content">
                        <p class="serif-quote">"<?php echo nl2br(htmlspecialchars($rev['comment'])); ?>"</p>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-journal journal-reveal" style="--delay: 3">
                <span class="empty-icon">✧</span>
                <p>THE JOURNAL IS CURRENTLY BLANK. BE THE FIRST TO INSCRIBE.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="moodboard-card-wrapper journal-reveal" style="--delay: 5">

        <div class="masking-tape"></div>

        <div class="moodboard-card">
            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="card-locked">
                    <span class="key-icon">⚿</span>
                    <p class="locked-text">PLEASE IDENTIFY YOURSELF TO INSCRIBE.</p>
                    <a href="login.php" class="btn-underline-black">AUTHORIZE IDENTITY ↗</a>
                </div>
            <?php elseif($has_reviewed): ?>
                <div class="card-archived">
                    <span class="stamp-verified">VERIFIED</span>
                    <p class="archived-text">YOUR THOUGHTS ARE PRESERVED IN THE ARCHIVE.</p>
                </div>
            <?php else: ?>
                <div class="card-header">
                    <h3 class="serif-medium">Leave a Note.</h3>
                    <p class="mono-tiny">YOUR EXPERIENCE SHAPES OUR SILHOUETTE</p>
                </div>

                <form action="submit-review.php" method="POST" class="tailor-form">
                    <input type="hidden" name="product_id" value="<?php echo $p_id; ?>">

                    <div class="form-row">
                        <span class="input-label">EVALUATION</span>
                        <div class="lux-stars-selector">
                            <input type="radio" id="st5" name="rating" value="5" required />
                            <label for="st5">✦</label>
                            <input type="radio" id="st4" name="rating" value="4" />
                            <label for="st4">✦</label>
                            <input type="radio" id="st3" name="rating" value="3" />
                            <label for="st3">✦</label>
                            <input type="radio" id="st2" name="rating" value="2" />
                            <label for="st2">✦</label>
                            <input type="radio" id="st1" name="rating" value="1" />
                            <label for="st1">✦</label>
                        </div>
                    </div>

                    <div class="form-row-col">
                        <span class="input-label">YOUR REFLECTION</span>
                        <textarea name="comment" class="serif-textarea" rows="2" placeholder="Write your thoughts here..." required></textarea>
                    </div>

                    <button type="submit" class="btn-imprint">
                        [ IMPRINT JOURNAL ]
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</section>

<style>
/* ==============================================
   🎨 THE CLIENT JOURNAL (QUIET LUXURY REVIEWS)
   ============================================== */

.client-journal-section {
    margin-top: 100px; padding-top: 60px;
    border-top: 1px solid #f1f1f1;
    display: flex; flex-direction: column; gap: 60px;
    font-family: 'Inter', sans-serif; color: #111;
}

.journal-reveal { opacity: 0; transform: translateY(30px); animation: softRise 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: calc(var(--delay) * 0.1s); }
@keyframes softRise { to { opacity: 1; transform: translateY(0); } }

.journal-header { display: flex; justify-content: space-between; align-items: flex-end; padding-bottom: 20px; border-bottom: 1px solid #111; }
.jh-left { display: flex; flex-direction: column; gap: 5px; }
.serif-huge { font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 400; margin: 0; line-height: 1; letter-spacing: -1px; }
.mono-meta { font-family: monospace; font-size: 9px; color: #888; font-weight: 700; letter-spacing: 2px; margin: 0; }

.jh-right { display: flex; align-items: center; gap: 15px; }
.macro-score { font-size: 42px; font-weight: 300; line-height: 1; letter-spacing: -2px; }
.score-details { display: flex; flex-direction: column; gap: 4px; }
.lux-sparkles { font-size: 14px; color: #111; letter-spacing: 2px; }
.count-tag { font-family: monospace; font-size: 9px; font-weight: 700; color: #888; letter-spacing: 1px; }

.journal-notice { padding: 15px 0; font-family: monospace; font-size: 9px; font-weight: 700; letter-spacing: 1px; border-bottom: 1px dashed #d1d5db; margin-bottom: -20px; }
.journal-notice.error { color: #ef4444; }
.journal-notice.success { color: #111; }

.journal-ledger { display: flex; flex-direction: column; }
.ledger-entry {
    display: grid; grid-template-columns: 220px 1fr; gap: 40px;
    padding: 50px 0; border-bottom: 1px solid #f1f1f1;
}

.entry-sidebar { display: flex; flex-direction: column; gap: 8px; }
.client-name { font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; color: #111; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
.entry-date { font-family: monospace; font-size: 10px; color: #888; }
.entry-sparks { font-size: 10px; color: #111; letter-spacing: 2px; margin-top: 5px; }

.entry-content { display: flex; align-items: flex-start; }
.serif-quote {
    font-family: 'Playfair Display', serif; font-size: 20px; font-style: italic; font-weight: 400;
    color: #333; line-height: 1.6; margin: 0; max-width: 90%;
}

.empty-journal { padding: 80px 0; text-align: center; display: flex; flex-direction: column; gap: 15px; border-bottom: 1px solid #f1f1f1; }
.empty-icon { font-size: 32px; color: #d1d5db; }
.empty-journal p { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 2px; margin: 0; }

.moodboard-card-wrapper {
    position: relative; margin-top: 40px; max-width: 600px; margin-left: auto; margin-right: auto;
}

.masking-tape {
    position: absolute; top: -15px; left: 50%; transform: translateX(-50%) rotate(-2deg);
    width: 120px; height: 35px;
    background-color: rgba(245, 245, 240, 0.8);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    backdrop-filter: blur(2px);
    z-index: 10;

    clip-path: polygon(2% 5%, 98% 2%, 99% 95%, 1% 98%);
}

.moodboard-card {
    background: #fdfcfb;
    border: 1px solid #e5e5e5;
    padding: 60px 50px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.03);
    position: relative; z-index: 1;
}

.card-header { margin-bottom: 40px; text-align: center; }
.serif-medium { font-family: 'Playfair Display', serif; font-size: 32px; font-style: italic; font-weight: 400; margin: 0 0 10px 0; color: #111; }
.mono-tiny { font-family: monospace; font-size: 9px; color: #888; font-weight: 700; letter-spacing: 2px; margin: 0; }

.tailor-form { display: flex; flex-direction: column; gap: 40px; }

.form-row { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #d1d5db; padding-bottom: 15px; }
.form-row-col { display: flex; flex-direction: column; gap: 15px; position: relative; }

.input-label { font-family: monospace; font-size: 9px; font-weight: 800; color: #888; letter-spacing: 2px; }

.lux-stars-selector { display: flex; flex-direction: row-reverse; gap: 10px; }
.lux-stars-selector input { display: none; }
.lux-stars-selector label { font-size: 18px; color: #e5e5e5; cursor: pointer; transition: 0.3s; }
.lux-stars-selector label:hover,
.lux-stars-selector label:hover ~ label,
.lux-stars-selector input:checked ~ label { color: #111; }

.serif-textarea {
    width: 100%; background: transparent; border: none; outline: none;
    border-bottom: 1px solid #111; padding: 10px 0;
    font-family: 'Playfair Display', serif; font-size: 20px; font-style: italic; color: #111;
    resize: none; overflow: hidden; line-height: 1.5; transition: all 0.3s; caret-color: #ff8002;
}
.serif-textarea::placeholder { color: #ccc; font-family: 'Inter', sans-serif; font-style: normal; font-weight: 300; font-size: 14px; }
.form-row-col:focus-within .input-label { color: #111; }

.btn-imprint {
    background: transparent; color: #111; border: none; padding: 0;
    font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 3px;
    cursor: pointer; display: inline-block; margin: 0 auto;
    transition: all 0.3s; border-bottom: 1px solid transparent;
}
.btn-imprint:hover { color: #ff8002; border-bottom-color: #ff8002; }

.card-locked, .card-archived { text-align: center; padding: 20px 0; }
.key-icon { font-size: 24px; color: #111; margin-bottom: 15px; display: block; }
.locked-text { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 1px; margin-bottom: 25px; }
.btn-underline-black { color: #111; text-decoration: none; font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 1px; border-bottom: 1px solid #111; padding-bottom: 2px; transition: 0.3s; }
.btn-underline-black:hover { color: #ff8002; border-color: #ff8002; }

.stamp-verified { font-family: monospace; font-size: 20px; font-weight: 800; color: #111; letter-spacing: 4px; border: 2px solid #111; padding: 10px 15px; display: inline-block; margin-bottom: 20px; transform: rotate(-5deg); }
.archived-text { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 2px; margin: 0; }

@media (max-width: 768px) {
    .client-journal-section { margin-top: 60px; gap: 40px; }
    .journal-header { flex-direction: column; align-items: flex-start; gap: 20px; }
    .ledger-entry { grid-template-columns: 1fr; gap: 15px; padding: 40px 0; }
    .entry-sidebar { flex-direction: row; align-items: baseline; flex-wrap: wrap; gap: 10px; }
    .serif-quote { font-size: 18px; max-width: 100%; }
    .moodboard-card { padding: 40px 25px; }
}
</style>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const textarea = document.querySelector('.serif-textarea');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }
    });
</script>
