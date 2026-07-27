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

$MAX_HUNGER = 100;
$MAX_FOOD = 5;
$FEED_AMOUNT = 5;
$FOOD_REGEN_SECONDS = 3600;
$MAX_STAGE = 5;

// ==========================================
// ==========================================
$user_query = mysqli_query($conn, "SELECT bear_food, bear_hunger, bear_stage, last_treat_time FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

$current_food = $user_data['bear_food'] !== null ? (int)$user_data['bear_food'] : 5;
$current_hunger = $user_data['bear_hunger'] !== null ? (int)$user_data['bear_hunger'] : 0;
$current_stage = isset($user_data['bear_stage']) ? (int)$user_data['bear_stage'] : 1;
$last_treat_time = $user_data['last_treat_time'] ? strtotime($user_data['last_treat_time']) : time();

$now = time();
$seconds_passed = $now - $last_treat_time;
$time_until_next = 0;

if ($current_food < $MAX_FOOD) {
    $gained_food = floor($seconds_passed / $FOOD_REGEN_SECONDS);
    if ($gained_food > 0) {
        $current_food = min($MAX_FOOD, $current_food + $gained_food);
        $last_treat_time += $gained_food * $FOOD_REGEN_SECONDS;
        $new_time_str = date('Y-m-d H:i:s', $last_treat_time);

        mysqli_query($conn, "UPDATE users SET bear_food = '$current_food', last_treat_time = '$new_time_str' WHERE id = '$user_id'");
    }

    if ($current_food < $MAX_FOOD) {
        $time_until_next = $FOOD_REGEN_SECONDS - ($now - $last_treat_time);
    }
} else {
    $new_time_str = date('Y-m-d H:i:s', $now);
    mysqli_query($conn, "UPDATE users SET last_treat_time = '$new_time_str' WHERE id = '$user_id'");
}

$stage_names = [1 => "Mysterious Egg", 2 => "Baby Bear", 3 => "Child Bear", 4 => "Teen Bear", 5 => "Adult Bear"];
$current_stage_name = $stage_names[$current_stage];

// ==========================================
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'feed_bear') {
        if ($current_food > 0) {

            if ($current_stage == $MAX_STAGE && $current_hunger >= $MAX_HUNGER) {
                echo json_encode(['success' => false, 'message' => 'Companion is fully grown and full!']);
                exit;
            }

            $new_food = $current_food - 1;
            $new_hunger = $current_hunger + $FEED_AMOUNT;
            $new_stage = $current_stage;
            $did_evolve = false;

            if ($new_hunger >= $MAX_HUNGER && $current_stage < $MAX_STAGE) {
                $new_stage++;
                $new_hunger = 0;
                $did_evolve = true;
            } else if ($current_stage == $MAX_STAGE) {
                $new_hunger = min($MAX_HUNGER, $new_hunger);
            }

            $new_time_str = date('Y-m-d H:i:s');
            $update_sql = "UPDATE users SET bear_food = '$new_food', bear_hunger = '$new_hunger', bear_stage = '$new_stage' " .
                          ($new_food == $MAX_FOOD - 1 ? ", last_treat_time = '$new_time_str'" : "") .
                          " WHERE id = '$user_id'";

            if (mysqli_query($conn, $update_sql)) {
                echo json_encode([
                    'success' => true,
                    'new_food' => $new_food,
                    'new_hunger' => $new_hunger,
                    'new_stage' => $new_stage,
                    'did_evolve' => $did_evolve,
                    'is_maxed' => ($new_stage == $MAX_STAGE && $new_hunger >= $MAX_HUNGER)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Cannot feed right now.']);
        }
        exit;
    }

    if ($_POST['action'] === 'claim_regen_food') {
        if ($current_food < $MAX_FOOD) {
            $new_food = $current_food + 1;
            $new_time_str = date('Y-m-d H:i:s');
            mysqli_query($conn, "UPDATE users SET bear_food = '$new_food', last_treat_time = '$new_time_str' WHERE id = '$user_id'");
            echo json_encode(['success' => true, 'new_food' => $new_food]);
        }
        exit;
    }
}

$is_tired = ($current_hunger < 40 && $current_stage > 1) ? 'true' : 'false';
include 'includes/header.php';
?>

<main class="companion-room">

    <div class="scene-track" id="scene-track">
        <div class="scene">
            <div class="room-wall bathroom-wall">
                <div class="bg-element window">
                    <div class="window-pane"></div><div class="window-pane"></div>
                </div>
            </div>
            <div class="room-floor bathroom-floor">
                <div class="bg-element bathtub"></div>
                <div class="bg-element rug"></div>
            </div>
        </div>

        <div class="scene">
            <div class="room-wall livingroom-wall">
                <div class="bg-element picture-frame" style="left: 20%; top: 20%; width: 60px; height: 80px;"></div>
                <div class="bg-element picture-frame" style="left: 35%; top: 30%; width: 50px; height: 50px;"></div>
            </div>
            <div class="room-floor livingroom-floor">
                <div class="bg-element sofa"></div>
                <div class="bg-element carpet"></div>
            </div>
        </div>

        <div class="scene">
            <div class="room-wall kitchen-wall">
                <div class="bg-element kitchen-cabinet"></div>
                <div class="bg-element kitchen-shelf"></div>
            </div>
            <div class="room-floor kitchen-floor">
                <div class="bg-element kitchen-counter"></div>
            </div>
        </div>
    </div>

    <button class="nav-arrow nav-left" id="btn-left">❮</button>
    <button class="nav-arrow nav-right" id="btn-right">❯</button>

    <div class="vitality-card fade-in">
        <div class="stage-badge">
            STAGE <span id="stage-level-text"><?php echo $current_stage; ?></span>:
            <span id="stage-name-text" class="highlight-text"><?php echo $current_stage_name; ?></span>
        </div>

        <div class="v-row top-row">
            <span class="v-icon">❤️</span>
            <div class="v-bar">
                <div class="v-fill" id="hunger-bar" style="width: <?php echo $current_hunger; ?>%;"></div>
            </div>
            <span class="v-text" id="hunger-text"><?php echo $current_hunger; ?>%</span>
        </div>

        <div class="v-row bottom-row">
            <div class="candy-dragger" id="candy-dispenser">
                <?php if($current_food > 0): ?>
                    <div class="candy-emoji" id="drag-candy" draggable="true">🍬</div>
                <?php else: ?>
                    <div class="candy-empty">⌛</div>
                <?php endif; ?>
            </div>
            <span class="v-text food-count-text" id="food-count-ui"><?php echo $current_food; ?></span>

            <div class="cd-box <?php echo ($current_food >= $MAX_FOOD) ? 'hidden' : ''; ?>" id="cooldown-box">
                <span class="cd-timer" id="cd-timer">--:--</span>
            </div>
        </div>
    </div>

    <div id="evo-flash" class="evo-flash hidden"></div>

    <div class="stage-3d-wrapper" id="stage-3d">
        <div class="pet-3d-container stage-<?php echo $current_stage; ?> <?php echo ($current_hunger < 40 && $current_stage > 1) ? 'state-tired' : ''; ?>" id="pet-character">
            <div class="pet-shadow"></div>

            <svg viewBox="0 0 400 550" xmlns="http://www.w3.org/2000/svg" id="bear-svg">
                <defs>
                    <radialGradient id="bodyGradient" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                        <stop offset="0%" stop-color="#7a3d01" />
                        <stop offset="100%" stop-color="#5c3000" />
                    </radialGradient>
                    <radialGradient id="eggGradient" cx="50%" cy="50%" r="50%" fx="30%" fy="30%">
                        <stop offset="0%" stop-color="#e0c8b0" />
                        <stop offset="100%" stop-color="#8c4c06" />
                    </radialGradient>
                </defs>

                <g id="part-egg" class="clickable-part parallax-layer" data-depth="0.1" style="display: <?php echo $current_stage == 1 ? 'block' : 'none'; ?>">
                    <circle cx="150" cy="210" r="20" fill="#5c3000" />
                    <circle cx="250" cy="210" r="20" fill="#5c3000" />
                    <ellipse cx="200" cy="350" rx="120" ry="150" fill="url(#eggGradient)" />
                    <path d="M 130 300 Q 170 340 200 290 T 270 310" stroke="#7a3d01" stroke-width="6" fill="none" opacity="0.4" />
                </g>

                <g id="part-bear" style="display: <?php echo $current_stage > 1 ? 'block' : 'none'; ?>">
                    <g id="part-belly" class="clickable-part parallax-layer" data-depth="0.05">
                        <rect x="130" y="420" width="40" height="60" rx="20" fill="#4a2600" class="bear-leg"/>
                        <rect x="230" y="420" width="40" height="60" rx="20" fill="#4a2600" class="bear-leg"/>
                        <ellipse cx="200" cy="340" rx="110" ry="120" fill="#5c3000" class="bear-tummy-out"/>
                        <ellipse cx="200" cy="360" rx="65" ry="75" fill="#7a3d01" class="bear-tummy-in"/>
                        <path d="M 100 280 Q 70 350 90 400" stroke="#4a2600" stroke-width="35" stroke-linecap="round" fill="none" class="bear-arm"/>
                        <path d="M 300 280 Q 330 350 310 400" stroke="#4a2600" stroke-width="35" stroke-linecap="round" fill="none" class="bear-arm"/>
                    </g>

                    <g id="part-head" class="clickable-part parallax-layer" data-depth="0.15">
                        <g id="ears">
                            <circle cx="120" cy="110" r="35" fill="#5c3000" class="ear-left" />
                            <circle cx="280" cy="110" r="35" fill="#5c3000" class="ear-right" />
                            <circle cx="120" cy="110" r="15" fill="#ffecd9" opacity="0.3" class="ear-left" />
                            <circle cx="280" cy="110" r="15" fill="#ffecd9" opacity="0.3" class="ear-right" />
                        </g>

                        <circle cx="200" cy="180" r="95" fill="url(#bodyGradient)" class="bear-face-bg"/>

                        <g id="eye-group">
                            <circle cx="160" cy="160" r="18" fill="#ffecd9" class="eye-w" />
                            <circle cx="240" cy="160" r="18" fill="#ffecd9" class="eye-w" />
                            <g id="pupil-left-group" class="pupil-group">
                                <circle cx="160" cy="160" r="14" fill="#111" class="pupil-b"/> <circle cx="160" cy="160" r="6" fill="#3f2000" class="pupil-m"/> <circle cx="164" cy="156" r="3" fill="#fff" class="pupil-s"/> </g>
                            <g id="pupil-right-group" class="pupil-group">
                                <circle cx="240" cy="160" r="14" fill="#111" class="pupil-b"/> <circle cx="240" cy="160" r="6" fill="#3f2000" class="pupil-m"/> <circle cx="244" cy="156" r="3" fill="#fff" class="pupil-s"/> </g>

                            <path d="M140 140 Q160 135 180 145" stroke="#3f2000" stroke-width="3" fill="none" stroke-linecap="round" class="browBrow" />
                            <path d="M220 145 Q240 135 260 140" stroke="#3f2000" stroke-width="3" fill="none" stroke-linecap="round" class="browBrow" />
                        </g>

                        <g id="blush-group" opacity="0.5">
                            <ellipse cx="140" cy="195" rx="18" ry="10" fill="#ffb6c1" />
                            <ellipse cx="260" cy="195" rx="18" ry="10" fill="#ffb6c1" />
                        </g>

                        <g id="hitbox-snout" class="dropzone">
                            <ellipse cx="200" cy="210" rx="55" ry="45" fill="#ffecd9" class="snout-bg" style="transition: all 0.3s;" />
                            <path d="M185 195 Q200 205 215 195 L200 215 Z" fill="#111" />
                            <path d="M200 215 V225 M180 225 Q200 240 220 225" stroke="#111" stroke-width="4" stroke-linecap="round" fill="none" id="bear-mouth" />
                        </g>
                    </g>
                </g>
            </svg>
        </div>
        <div id="fx-container"></div>
    </div>

    <div class="bottom-hint fade-in">
        <div class="hint-pill" id="interaction-hint">Living Room</div>
    </div>
</main>

<script>
    window.initialCooldown = <?php echo $time_until_next; ?>;
    window.maxFood = <?php echo $MAX_FOOD; ?>;
    window.currentStageInit = <?php echo $current_stage; ?>;
    window.isTiredInit = <?php echo $is_tired; ?>;
</script>

<style>

.companion-room { position: relative; width: 100%; min-height: 100vh; overflow: hidden; font-family: 'Inter', sans-serif; display: flex; flex-direction: column; margin-top: 80px; background: #000; user-select: none;}

.scene-track {
    position: absolute; inset: 0; width: 300%; display: flex;
    transform: translateX(-33.3333%);
    transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
    z-index: 1;
}
.scene { width: 33.3333%; height: 100%; position: relative; overflow: hidden;}
.room-wall { position: absolute; top: 0; left: 0; width: 100%; height: 75%; }
.room-floor { position: absolute; bottom: 0; left: 0; width: 100%; height: 25%; border-top: 6px solid rgba(0,0,0,0.1); }

.bg-element { position: absolute; }

.bathroom-wall { background: radial-gradient(circle at center 50%, #e0f7fa 0%, #b2ebf2 100%); }
.bathroom-floor { background: linear-gradient(to bottom, #b0bec5 0%, #4db6ac 100%); }
.window { width: 250px; height: 120px; background: rgba(255,255,255,0.4); border: 8px solid #fff; border-radius: 10px; top: 15%; right: 15%; display: flex; gap: 5px; padding: 5px; box-shadow: 0 10px 20px rgba(0,0,0,0.05);}
.window-pane { flex: 1; background: linear-gradient(135deg, rgba(255,255,255,0.6) 0%, rgba(255,255,255,0.1) 100%); border-radius: 5px;}
.bathtub { width: 300px; height: 100px; background: #fff; border-radius: 10px 10px 50px 50px; bottom: 0; right: 10%; box-shadow: inset 0 -20px 20px rgba(0,0,0,0.05), 0 -10px 20px rgba(0,0,0,0.1);}
.rug { width: 200px; height: 30px; background: #eceff1; border-radius: 50%; bottom: 10%; left: 15%; filter: blur(2px);}

.livingroom-wall { background: radial-gradient(circle at center 60%, #fff 0%, #e8ddcf 100%); }
.livingroom-floor { background: linear-gradient(to bottom, #d5c6b5 0%, #a89785 100%); border-top-color: #bbaaa0;}
.picture-frame { background: #fdfbf9; border: 4px solid #333; box-shadow: 5px 5px 15px rgba(0,0,0,0.1); border-radius: 2px;}
.sofa { width: 400px; height: 150px; background: #cfd8dc; border-radius: 20px 20px 0 0; bottom: 0; left: 5%; box-shadow: inset 0 20px 30px rgba(255,255,255,0.5), 0 -10px 20px rgba(0,0,0,0.05);}
.carpet { width: 500px; height: 150px; background: repeating-linear-gradient(45deg, #a89785, #a89785 10px, #bcaaa4 10px, #bcaaa4 20px); border-radius: 50%; bottom: -50px; left: 50%; transform: translateX(-50%); opacity: 0.3; filter: blur(5px);}

.kitchen-wall { background: repeating-linear-gradient(0deg, transparent, transparent 49px, rgba(0,0,0,0.03) 49px, rgba(0,0,0,0.03) 50px), repeating-linear-gradient(90deg, transparent, transparent 49px, rgba(0,0,0,0.03) 49px, rgba(0,0,0,0.03) 50px), radial-gradient(circle at center 60%, #fff9c4 0%, #ffe082 100%); }
.kitchen-floor { background: linear-gradient(to bottom, #bcaaa4 0%, #795548 100%); }
.kitchen-cabinet { width: 200px; height: 100px; background: #4db6ac; top: 10%; right: 5%; border-radius: 5px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-bottom: 5px solid #00867d;}
.kitchen-shelf { width: 150px; height: 10px; background: #795548; top: 40%; right: 10%; border-radius: 5px; box-shadow: 0 5px 10px rgba(0,0,0,0.1);}
.kitchen-counter { width: 100%; height: 60px; background: #263238; bottom: 0; left: 0; border-top: 10px solid #eceff1;}

.nav-arrow {
    position: absolute; top: 50%; transform: translateY(-50%); z-index: 50;
    width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.4);
    border: none; font-size: 24px; color: #7a3d01; cursor: pointer;
    backdrop-filter: blur(5px); box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: all 0.2s; display: flex; align-items: center; justify-content: center;
}
.nav-arrow:hover { background: #fff; transform: translateY(-50%) scale(1.1); color: #ff8002;}
.nav-arrow:disabled { opacity: 0.2; cursor: not-allowed; transform: translateY(-50%);}
.nav-left { left: 20px; }
.nav-right { right: 20px; }

.fade-in { opacity: 0; animation: fadeIn 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; z-index: 10;}
@keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }

.vitality-card {
    position: absolute; top: 30px; left: 40px; z-index: 100;
    background: rgba(253, 251, 249, 0.9); backdrop-filter: blur(15px);
    border-radius: 20px; padding: 15px 20px;
    box-shadow: 0 10px 30px rgba(63,32,0,0.1); border: 2px solid #fff;
    display: flex; flex-direction: column; gap: 10px;
    min-width: 220px;
}
.stage-badge { font-size: 11px; font-weight: 800; color: #a0958a; letter-spacing: 1px; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 8px;}
.highlight-text { color: #ff8002; margin-left: 5px;}

.v-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.v-icon { font-size: 20px; filter: drop-shadow(0 2px 4px rgba(255,0,0,0.3)); }
.v-bar { flex: 1; height: 14px; background: rgba(0,0,0,0.08); border-radius: 10px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);}
.v-fill { height: 100%; background: linear-gradient(90deg, #ffaa4a, #ff8002); transition: width 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.v-text { font-family: monospace; font-weight: 900; color: #111; font-size: 14px;}

.bottom-row { padding-left: 2px; }
.candy-dragger { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; position: relative;}
.candy-emoji { font-size: 26px; cursor: grab; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3)); transition: transform 0.2s;}
.candy-emoji:active { cursor: grabbing; transform: scale(1.2); }
.candy-emoji.dragging { opacity: 0; }
.candy-empty { font-size: 18px; opacity: 0.5; }

.food-count-text { color: #ff8002; font-size: 18px; margin-left: 5px;}

.cd-box { margin-left: auto; background: rgba(0,0,0,0.05); padding: 4px 10px; border-radius: 8px;}
.cd-box.hidden { display: none; }
.cd-timer { font-family: monospace; font-size: 13px; font-weight: bold; color: #ff8002;}

.evo-flash { position: fixed; inset: 0; background: #fff; z-index: 999; pointer-events: none; opacity: 0; transition: opacity 1.5s ease-in-out; }
.evo-flash.active { opacity: 1; }
.evo-flash.hidden { display: none; }

.stage-3d-wrapper { flex: 1; display: flex; align-items: center; justify-content: center; perspective: 1200px; z-index: 5; position: relative; padding-bottom: 5vh; pointer-events: none; }
.pet-3d-container { width: 350px; height: 480px; position: relative; transform-style: preserve-3d; transform-origin: bottom center; pointer-events: auto;}

#part-head, #part-belly { transition: transform 1s cubic-bezier(0.34, 1.56, 0.64, 1); transform-origin: center; }

.stage-1 #part-bear { display: none; }
.stage-1 #part-egg { display: block; animation: eggWobble 4s infinite ease-in-out; transform-origin: bottom center; }
@keyframes eggWobble { 0%, 100% { transform: rotate(0deg) scale(0.9); } 50% { transform: rotate(3deg) scale(0.9) translateY(5px); } }

.stage-2 #part-egg { display: none; }
.stage-2 #part-bear { display: block; animation: bodyBreathe 4s infinite ease-in-out; }
.stage-2 #part-head { transform: scale(1.3) translateY(20px); }
.stage-2 #part-belly { transform: scale(0.55) translateY(120px); }
.stage-2 .bear-arm { stroke-width: 25; } .stage-2 .eye-w { r: 24; } .stage-2 .pupil-b { r: 18; }

.stage-3 #part-egg { display: none; }
.stage-3 #part-bear { display: block; animation: bodyBreathe 4s infinite ease-in-out; }
.stage-3 #part-head { transform: scale(1.15) translateY(10px); }
.stage-3 #part-belly { transform: scale(0.75) translateY(60px); }

.stage-4 #part-egg { display: none; }
.stage-4 #part-bear { display: block; animation: bodyBreathe 4s infinite ease-in-out; }
.stage-4 #part-head { transform: scale(1.05) translateY(5px); }
.stage-4 #part-belly { transform: scale(0.9) translateY(20px); }

.stage-5 #part-egg { display: none; }
.stage-5 #part-bear { display: block; animation: bodyBreathe 4s infinite ease-in-out; }
.stage-5 #part-head { transform: scale(1) translateY(0); }
.stage-5 #part-belly { transform: scale(1) translateY(0); }

.parallax-layer { transition: transform 0.1s ease-out; }
@keyframes bodyBreathe { 0%, 100% { transform: scaleY(1); } 50% { transform: scaleY(0.97) translateY(8px); } }

.pet-shadow { width: 220px; height: 30px; background: rgba(0,0,0,0.15); border-radius: 50%; filter: blur(5px); position: absolute; bottom: -15px; left: 50%; transform: translateX(-50%); animation: shadowBreathe 4s infinite ease-in-out; transition: transform 1s;}
.stage-1 .pet-shadow { transform: translateX(-50%) scale(0.7); }
@keyframes shadowBreathe { 0%, 100% { transform: translateX(-50%) scale(1); opacity: 0.15; } 50% { transform: translateX(-50%) scale(1.1); opacity: 0.1; } }

.browBrow { opacity: 0; transition: opacity 0.5s; stroke-dasharray: 1000; stroke-dashoffset: 1000;}
.state-tired .browBrow { opacity: 1; animation: drawBrow 0.5s forwards; }
@keyframes drawBrow { to { stroke-dashoffset: 0; } }
.state-tired .ear-left { transform: rotate(-15deg) translateY(5px); transform-origin: 120px 110px; transition: 0.5s; }
.state-tired .ear-right { transform: rotate(15deg) translateY(5px); transform-origin: 280px 110px; transition: 0.5s; }
.state-tired { animation: bodyBreatheTired 6s infinite ease-in-out; }
@keyframes bodyBreatheTired { 0%, 100% { transform: scaleY(1); } 50% { transform: scaleY(0.95) translateY(12px); } }

.dropzone.drag-over { filter: brightness(1.2); }
#bear-mouth { transition: d 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.pupil-group { transition: transform 0.1s ease-out; }

#fx-container { position: absolute; inset: 0; pointer-events: none; z-index: 20; }
.fx-text-bubble { position: absolute; background: #fff; padding: 8px 16px; border-radius: 20px; font-weight: 800; font-size: 14px; color: #ff8002; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: 2px solid #ffecd9; animation: bubbleUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes bubbleUp { 0% { opacity: 0; transform: translateY(20px) scale(0.5); } 20% { opacity: 1; transform: translateY(0) scale(1.1); } 100% { opacity: 0; transform: translateY(-50px) scale(1); } }

.fx-heart {
    position: absolute;
    font-size: 26px;
    pointer-events: none;
    z-index: 30;
    opacity: 0;
    animation: floatHeart 1.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}
@keyframes floatHeart {
    0% { opacity: 0; transform: translate(-50%, 0) scale(0.5); }
    20% { opacity: 1; transform: translate(-50%, -15px) scale(1.2) rotate(var(--rot)); }
    100% { opacity: 0; transform: translate(calc(-50% + var(--x-offset)), -120px) scale(0.9) rotate(var(--rot)); }
}

.bottom-hint { position: absolute; bottom: 40px; width: 100%; display: flex; justify-content: center; z-index: 10; pointer-events: none;}
.hint-pill { background: rgba(63,32,0,0.6); color: #fff; padding: 8px 20px; border-radius: 30px; font-size: 13px; font-weight: 600; letter-spacing: 1px; backdrop-filter: blur(5px); transition: 0.3s;}

@media (max-width: 768px) {
    .vitality-card { top: 20px; left: 50%; transform: translateX(-50%); width: 90%; }
    .nav-arrow { width: 45px; height: 45px; font-size: 20px;}
    .nav-left { left: 10px; } .nav-right { right: 10px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let foodCount = parseInt(document.getElementById('food-count-ui').innerText);
    let hungerVal = parseInt(document.getElementById('hunger-text').innerText);
    let currentStage = window.currentStageInit;
    let isTired = window.isTiredInit;

    // DOM
    const petContainer = document.getElementById('pet-character');
    const mouth = document.getElementById('bear-mouth');
    const fxContainer = document.getElementById('fx-container');
    const evoFlash = document.getElementById('evo-flash');
    const hungerBar = document.getElementById('hunger-bar');
    const hungerText = document.getElementById('hunger-text');
    const foodCountUI = document.getElementById('food-count-ui');
    const cdTimer = document.getElementById('cd-timer');
    const candyDispenser = document.getElementById('candy-dispenser');
    const partEgg = document.getElementById('part-egg');
    const hintText = document.getElementById('interaction-hint');

    // ==========================================
    // ==========================================
    let currentSceneIndex = 1;
    const sceneTrack = document.getElementById('scene-track');
    const btnLeft = document.getElementById('btn-left');
    const btnRight = document.getElementById('btn-right');
    const sceneNamesDisplay = ["Bathroom", "Living Room", "Kitchen"];

    function updateScene() {
        const offset = -(currentSceneIndex * 33.3333);
        sceneTrack.style.transform = `translateX(${offset}%)`;

        btnLeft.disabled = (currentSceneIndex === 0);
        btnRight.disabled = (currentSceneIndex === 2);

        hintText.style.opacity = 0;
        setTimeout(() => {
            hintText.innerText = sceneNamesDisplay[currentSceneIndex];
            hintText.style.opacity = 1;
        }, 300);
    }

    btnLeft.addEventListener('click', () => { if (currentSceneIndex > 0) { currentSceneIndex--; updateScene(); } });
    btnRight.addEventListener('click', () => { if (currentSceneIndex < 2) { currentSceneIndex++; updateScene(); } });

    // ==========================================
    // ==========================================
    let cooldownTime = window.initialCooldown;
    function formatTime(sec) {
        let m = Math.floor(sec / 60); let s = sec % 60;
        return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }

    setInterval(() => {
        if (foodCount < window.maxFood) {
            cooldownTime--;
            if (cooldownTime <= 0) {
                fetch(window.location.href, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=claim_regen_food' })
                .then(res => res.json()).then(data => { if (data.success) updateInventoryUI(data.new_food); });
            } else { cdTimer.innerText = formatTime(cooldownTime); }
        }
    }, 1000);

    function updateInventoryUI(newFood) {
        foodCount = newFood; foodCountUI.innerText = foodCount;
        document.getElementById('cooldown-box').classList.toggle('hidden', foodCount >= window.maxFood);
        if(foodCount < window.maxFood && cooldownTime <= 0) cooldownTime = 3600;
        candyDispenser.innerHTML = foodCount > 0 ? '<div class="candy-emoji" id="drag-candy" draggable="true">🍬</div>' : '<div class="candy-empty">⌛</div>';
        attachDragEvents();
    }

    // ==========================================
    // ==========================================
    const stageNames = {1: "Mysterious Egg", 2: "Baby Bear", 3: "Child Bear", 4: "Teen Bear", 5: "Adult Bear"};
    const happyMouth = "M175 215 Q200 245 225 215";
    const normalMouth = "M185 220 Q200 230 215 220";
    const sadMouth = "M185 225 Q200 215 215 225";
    const mouthOpen = "M180 215 Q200 250 220 215 Z";
    const chewMouth = "M180 220 Q200 235 220 220 Q200 205 180 220";

    if(currentStage > 1) mouth.setAttribute('d', isTired ? sadMouth : normalMouth);

    document.getElementById('stage-3d').addEventListener('mousemove', (e) => {
        const rect = e.currentTarget.getBoundingClientRect();
        const xAxis = ((e.clientX - rect.left) / rect.width - 0.5) * 2;
        const yAxis = ((e.clientY - rect.top) / rect.height - 0.5) * 2;
        petContainer.style.transform = `rotateY(${xAxis * 15}deg) rotateX(${-yAxis * 15}deg)`;
        if (currentStage > 1) {
            document.querySelectorAll('.pupil-group').forEach(group => {
                const pRect = group.getBoundingClientRect();
                const angle = Math.atan2(e.clientY - (pRect.top + pRect.height/2), e.clientX - (pRect.left + pRect.width/2));
                group.style.transform = `translate(${Math.cos(angle) * 6}px, ${Math.sin(angle) * 6}px)`;
            });
        }
    });

    function triggerEvolution(newStage) {
        evoFlash.classList.remove('hidden'); evoFlash.classList.add('active');
        setTimeout(() => {
            currentStage = newStage;
            document.getElementById('stage-level-text').innerText = newStage;
            document.getElementById('stage-name-text').innerText = stageNames[newStage];
            petContainer.className = `pet-3d-container stage-${newStage}`;
            evoFlash.classList.remove('active');
            setTimeout(() => evoFlash.classList.add('hidden'), 1500);
        }, 1500);
    }

    function createBubble(text) {
        const bubble = document.createElement('div'); bubble.className = 'fx-text-bubble'; bubble.innerText = text;
        bubble.style.left = `50%`; bubble.style.top = `35%`; fxContainer.appendChild(bubble); setTimeout(() => bubble.remove(), 1000);
    }

    function createFloatingHearts() {
        const numHearts = Math.floor(Math.random() * 3) + 3;
        for (let i = 0; i < numHearts; i++) {
            const heart = document.createElement('div');
            heart.className = 'fx-heart';
            heart.innerText = '❤️';

            const startX = 50 + (Math.random() * 10 - 5);
            const startY = currentStage === 1 ? 55 : 45;

            const xOffset = (Math.random() * 60 - 30) + 'px';
            const rot = (Math.random() * 40 - 20) + 'deg';

            heart.style.left = `${startX}%`;
            heart.style.top = `${startY}%`;
            heart.style.setProperty('--x-offset', xOffset);
            heart.style.setProperty('--rot', rot);

            heart.style.animationDelay = `${Math.random() * 0.3}s`;

            fxContainer.appendChild(heart);

            setTimeout(() => heart.remove(), 1800);
        }
    }

    petContainer.addEventListener('click', (e) => {
        if(currentStage === 1) {
            petContainer.style.transform = "scale(1.05) rotate(5deg)";
            setTimeout(() => petContainer.style.transform = "scale(1) rotate(0deg)", 200);
            createBubble("Wiggle...");
            return;
        }

        if(e.target.closest('#part-head')) {
            if (isTired) { createBubble("...so hungry"); return; }
            mouth.setAttribute('d', happyMouth); createBubble("💖");
            setTimeout(() => { mouth.setAttribute('d', normalMouth); }, 1000);
        } else if (e.target.closest('#part-belly')) {
            if (isTired) { createBubble("Need a treat..."); return; }
            mouth.setAttribute('d', happyMouth); createBubble("Hehe!");
            setTimeout(() => { mouth.setAttribute('d', normalMouth); }, 600);
        }
    });

    // ==========================================
    // ==========================================
    let activeDragCandy = null;
    function attachDragEvents() {
        const candy = document.getElementById('drag-candy');
        if(!candy) return;
        candy.addEventListener('dragstart', (e) => {
            if(currentStage === 5 && hungerVal >= 100) { e.preventDefault(); createBubble("I'm fully grown!"); return; }
            activeDragCandy = candy; setTimeout(() => candy.classList.add('dragging'), 0);
            const dragImg = document.createElement("div"); dragImg.innerText = "🍬"; dragImg.style.fontSize = "50px"; dragImg.style.position = "absolute"; dragImg.style.top = "-1000px";
            document.body.appendChild(dragImg); e.dataTransfer.setDragImage(dragImg, 25, 25);
        });
        candy.addEventListener('dragend', () => {
            if(activeDragCandy) activeDragCandy.classList.remove('dragging');
            activeDragCandy = null;
            if(currentStage > 1) mouth.setAttribute('d', isTired ? sadMouth : normalMouth);
            const dropzone = currentStage === 1 ? partEgg : document.getElementById('hitbox-snout');
            dropzone.classList.remove('drag-over');
        });
    }
    attachDragEvents();

    function handleDrop(e) {
        e.preventDefault();
        const dropzone = currentStage === 1 ? partEgg : document.getElementById('hitbox-snout');
        dropzone.classList.remove('drag-over');
        if (!activeDragCandy) return;

        if (currentStage > 1) {
            mouth.setAttribute('d', chewMouth);
            setTimeout(() => {
                mouth.setAttribute('d', happyMouth);
                setTimeout(() => { if(!isTired) mouth.setAttribute('d', normalMouth); }, 1000);
            }, 500);
        } else {
            petContainer.style.transform = "scale(1.1) rotate(-5deg)";
            setTimeout(() => petContainer.style.transform = "scale(1) rotate(0)", 300);
        }

        fetch(window.location.href, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=feed_bear' })
        .then(res => res.json()).then(data => {
            if(data.success) {
                hungerVal = data.new_hunger; hungerText.innerText = hungerVal + '%'; hungerBar.style.width = hungerVal + '%';
                updateInventoryUI(data.new_food);

                createFloatingHearts();

                if(data.did_evolve) { triggerEvolution(data.new_stage); isTired = true;}
                else if (data.new_hunger >= 40) { isTired = false; mouth.setAttribute('d', normalMouth); petContainer.classList.remove('state-tired'); }
                if (data.is_maxed) { createBubble("I'm fully grown!"); }
            }
        });
    }

    function bindDropzone() {
        const dropzone = currentStage === 1 ? partEgg : document.getElementById('hitbox-snout');
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('drag-over'); if(currentStage>1) mouth.setAttribute('d', mouthOpen); });
        dropzone.addEventListener('dragleave', () => { dropzone.classList.remove('drag-over'); if(currentStage>1) mouth.setAttribute('d', isTired ? sadMouth : normalMouth); });
        dropzone.addEventListener('drop', handleDrop);
    }
    bindDropzone();
});
</script>

<?php include 'includes/footer.php'; ?>
