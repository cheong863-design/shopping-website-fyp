<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
$today = date('Y-m-d');

$goal = 1000;
$daily_reward = 50;

// ==========================================
// ==========================================
$user_query = mysqli_query($conn, "SELECT faifa_coins, last_checkin FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

$current_coins = $user_data['faifa_coins'] ? (int)$user_data['faifa_coins'] : 0;
$last_checkin = $user_data['last_checkin'];
$already_checked_in = ($last_checkin === $today);

$req_check = mysqli_query($conn, "SELECT id FROM reward_requests WHERE user_id = '$user_id' AND status = 'pending' LIMIT 1");
$is_pending = mysqli_num_rows($req_check) > 0;

// ==========================================
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'checkin') {
        if (!$already_checked_in) {
            $new_balance = $current_coins + $daily_reward;
            mysqli_query($conn, "UPDATE users SET faifa_coins = '$new_balance', last_checkin = '$today' WHERE id = '$user_id'");
            echo json_encode(['success' => true, 'new_balance' => $new_balance, 'added' => $daily_reward]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Already checked in today.']);
        }
        exit;
    }

    if ($_POST['action'] === 'request_reward') {
        if ($current_coins >= $goal && !$is_pending) {

            $insert_query = "INSERT INTO reward_requests (user_id, status, created_at) VALUES ('$user_id', 'pending', NOW())";

            if (mysqli_query($conn, $insert_query)) {
                $new_balance = $current_coins - $goal;
                mysqli_query($conn, "UPDATE users SET faifa_coins = '$new_balance' WHERE id = '$user_id'");

                echo json_encode(['success' => true, 'new_balance' => $new_balance]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error. Failed to send request to Admin.']);
            }

        } else {
            echo json_encode(['success' => false, 'message' => 'Not enough coins or request already pending.']);
        }
        exit;
    }
}

$progress_percent = min(100, ($current_coins / $goal) * 100);

$is_unlocked = ($current_coins >= $goal && !$is_pending);

include 'includes/header.php';
?>

<main class="ledger-editorial-page">
    <div class="container ledger-container">

        <div class="monolith-receipt fade-up <?php echo $is_unlocked ? 'state-unlocked' : ''; ?>" id="main-ledger">

            <div class="receipt-header">
                <div class="meta-row">
                    <span class="meta-key">ACCOUNT ID</span>
                    <span class="meta-val"><?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">DATE</span>
                    <span class="meta-val"><?php echo date('d.m.Y'); ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">STATUS</span>
                    <span class="meta-val status-indicator" id="ui-status">
                        <?php
                            if($is_pending) echo 'PENDING';
                            elseif($is_unlocked) echo 'UNLOCKED';
                            else echo 'LOCKED';
                        ?>
                    </span>
                </div>
            </div>

            <div class="receipt-body">
                <h2 class="body-title">Coin Balance.</h2>

                <div class="yield-display">
                    <span class="yield-number" id="coin-counter"><?php echo $current_coins; ?></span>
                    <span class="yield-unit">COINS</span>
                </div>

                <div class="scale-tracker">
                    <div class="scale-bar">
                        <div class="scale-fill" id="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>
                    </div>
                    <div class="scale-labels">
                        <span>0</span>
                        <span>GOAL / 1000</span>
                    </div>
                </div>
            </div>

            <div class="receipt-footer">
                <p class="terms-mono" id="ui-terms">
                    <?php
                        if($is_pending) echo 'YOUR REWARD REQUEST HAS BEEN SENT. PLEASE WAIT FOR ADMIN APPROVAL.';
                        elseif($is_unlocked) echo 'CONGRATULATIONS! YOU HAVE REACHED 1000 COINS. REDEEM NOW FOR A 10% DISCOUNT CODE.';
                        else echo 'CHECK IN EVERY DAY TO EARN +50 COINS. COLLECT 1000 COINS TO UNLOCK A PREMIUM DISCOUNT.';
                    ?>
                </p>

                <div class="action-dock">
                    <?php if ($is_pending): ?>
                        <button class="btn-brutalist btn-disabled" disabled>
                            REQUEST PENDING
                        </button>
                    <?php elseif ($is_unlocked): ?>
                        <button class="btn-brutalist btn-obsidian" id="request-reward-btn">
                            REDEEM 10% OFF (-1000 COINS)
                        </button>
                    <?php else: ?>
                        <button class="btn-brutalist" id="checkin-btn" <?php echo $already_checked_in ? 'disabled' : ''; ?>>
                            <?php echo $already_checked_in ? 'CHECKED IN TODAY' : 'CHECK IN TODAY (+50 COINS)'; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 THE MONOLITHIC LEDGER (EXTREME BRUTALISM)
   ============================================== */

.ledger-editorial-page {
    background: transparent;
    color: #111;
    font-family: 'Inter', sans-serif;
    padding: 100px 0 120px 0;
    min-height: 85vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ledger-container {
    width: 100%;
    display: flex;
    justify-content: center;
}

.fade-up { opacity: 0; animation: ledgerUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes ledgerUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

.monolith-receipt {
    width: 100%;
    max-width: 480px;
    background: #fff;
    border: 1px solid #111;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 15px 15px 0 rgba(17, 17, 17, 0.05);
}

.monolith-receipt.state-unlocked {
    background: #111;
    color: #fff;
    border-color: #111;
    box-shadow: 15px 15px 0 rgba(255, 128, 2, 0.1);
}

.receipt-header {
    padding: 30px;
    border-bottom: 1px solid #111;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: border-color 1.2s;
}
.monolith-receipt.state-unlocked .receipt-header { border-bottom-color: #333; }

.meta-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    font-family: monospace;
    font-size: 10px;
    letter-spacing: 2px;
}
.meta-key { color: #888; font-weight: 600; }
.meta-val { color: #111; font-weight: 800; transition: color 1.2s; }
.monolith-receipt.state-unlocked .meta-val { color: #fff; }
.status-indicator { color: #ff8002; }

.receipt-body {
    padding: 50px 30px;
    text-align: center;
}

.body-title {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-style: italic;
    font-weight: 400;
    margin: 0 0 30px 0;
    color: inherit;
}

.yield-display {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 10px;
    margin-bottom: 50px;
}

.yield-number {
    font-family: 'Playfair Display', serif;
    font-size: 120px;
    line-height: 0.8;
    letter-spacing: -4px;
    font-weight: 400;
    color: inherit;
}
.yield-unit {
    font-family: monospace;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #ff8002;
    margin-top: 10px;
}

.scale-tracker { width: 100%; }
.scale-bar {
    width: 100%;
    height: 1px;
    background: #e5e5e5;
    position: relative;
    margin-bottom: 15px;
    transition: background 1.2s;
}
.monolith-receipt.state-unlocked .scale-bar { background: #333; }

.scale-fill {
    position: absolute;
    left: 0;
    top: -1px;
    height: 3px;
    background: #111;
    transition: width 2s cubic-bezier(0.16, 1, 0.3, 1), background 1.2s;
}
.monolith-receipt.state-unlocked .scale-fill { background: #ff8002; }

.scale-labels {
    display: flex;
    justify-content: space-between;
    font-family: monospace;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 2px;
    color: #888;
}

.receipt-footer {
    padding: 0 30px 30px 30px;
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.terms-mono {
    font-family: monospace;
    font-size: 9px;
    line-height: 1.6;
    letter-spacing: 1px;
    color: #888;
    margin: 0;
    text-align: center;
}

.btn-brutalist {
    width: 100%;
    padding: 20px;
    background: #111;
    color: #fff;
    border: none;
    font-family: monospace;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    cursor: pointer;
    transition: 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-brutalist::after {
    content: ''; position: absolute; inset: 0; background: #fff;
    opacity: 0; transition: 0.3s; pointer-events: none;
}
.btn-brutalist:active::after { opacity: 0.2; }

.btn-brutalist:hover:not(:disabled) { background: #ff8002; }
.btn-brutalist:disabled { background: #e5e5e5; color: #888; cursor: not-allowed; }

.btn-obsidian { background: #fff; color: #111; }
.btn-obsidian:hover { background: #ff8002; color: #fff; }
.btn-disabled { background: #333 !important; color: #666 !important; border: 1px solid #333; }

.flash-stamp { animation: stampFlash 0.5s ease forwards; }
@keyframes stampFlash { 0% { filter: invert(1); } 100% { filter: invert(0); } }

@media (max-width: 768px) {
    .ledger-editorial-page { padding: 40px 20px 80px 20px; }
    .monolith-receipt { box-shadow: 8px 8px 0 rgba(17, 17, 17, 0.05); }
    .yield-number { font-size: 100px; }
}
</style>

<script>
    function animateValue(obj, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const easeOut = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            obj.innerHTML = Math.floor(start + (end - start) * easeOut);
            if (progress < 1) window.requestAnimationFrame(step);
            else obj.innerHTML = end;
        };
        window.requestAnimationFrame(step);
    }

    document.addEventListener('DOMContentLoaded', () => {

        const checkinBtn = document.getElementById('checkin-btn');
        if (checkinBtn) {
            checkinBtn.addEventListener('click', function(e) {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = 'PROCESSING...';

                document.getElementById('main-ledger').classList.add('flash-stamp');
                setTimeout(() => document.getElementById('main-ledger').classList.remove('flash-stamp'), 500);

                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=checkin'
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        btn.innerHTML = 'CHECKED IN TODAY';

                        const counterEl = document.getElementById('coin-counter');
                        const startVal = parseInt(counterEl.innerText);
                        const targetVal = data.new_balance;
                        animateValue(counterEl, startVal, targetVal, 2000);

                        const progressEl = document.getElementById('progress-fill');
                        const newPercent = Math.min(100, (targetVal / 1000) * 100);
                        progressEl.style.width = newPercent + '%';

                        if (targetVal >= 1000) {
                            setTimeout(() => window.location.reload(), 2500);
                        }
                    }
                });
            });
        }

        const requestBtn = document.getElementById('request-reward-btn');
        if (requestBtn) {
            requestBtn.addEventListener('click', function(e) {
                const btn = this;
                btn.innerHTML = 'PROCESSING...';
                btn.style.pointerEvents = 'none';

                document.getElementById('main-ledger').classList.add('flash-stamp');
                setTimeout(() => document.getElementById('main-ledger').classList.remove('flash-stamp'), 500);

                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=request_reward'
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        const counterEl = document.getElementById('coin-counter');
                        const startVal = parseInt(counterEl.innerText);
                        const targetVal = data.new_balance;
                        animateValue(counterEl, startVal, targetVal, 2000);
                        document.getElementById('progress-fill').style.width = Math.min(100, (targetVal / 1000) * 100) + '%';

                        setTimeout(() => window.location.reload(), 2500);
                    } else {
                        alert(data.message);
                        btn.innerHTML = 'REDEEM 10% OFF (-1000 COINS)';
                        btn.style.pointerEvents = 'auto';
                    }
                });
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
