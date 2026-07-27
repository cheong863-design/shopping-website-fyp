<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';
include 'includes/db.php';

$msg_sent = false;
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $to = "cheong.munchun@ypccollege.edu.my";
    $headers = "From: $email\r\nReply-To: $email\r\nX-Mailer: PHP/" . phpversion();
    $full_message = "From: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";

    if (@mail($to, $subject, $full_message, $headers)) {
        $msg_sent = true;
    } else {
        $sql = "INSERT INTO customer_inquiries (name, email, subject, message, created_at)
                VALUES ('$name', '$email', '$subject', '$message', NOW())";
        if(mysqli_query($conn, $sql)) { $msg_sent = true; }
        else { $error_msg = "TRANSMISSION FAILED. PLEASE RETRY."; }
    }
}
?>

<main class="concierge-page">
    <div class="container dossier-container">

        <div class="dossier-paper fade-up" style="--delay: 1">

            <header class="dossier-header">
                <div class="header-top">
                    <span class="mono-barcode">|||| ||||| ||| | || |||||</span>
                    <div class="live-clock">
                        <div class="pulse-dot"></div>
                        <span class="clock-text">KUALA LUMPUR HQ — <span id="local-time">--:--:--</span> MYT</span>
                    </div>
                </div>
                <h1 class="serif-title">Concierge.</h1>
                <p class="mono-subtitle">PRIVATE CLIENT SERVICES & INQUIRIES</p>
            </header>

            <div class="dossier-body">

                <aside class="dossier-meta">
                    <div class="meta-group">
                        <span class="m-label">01. ELECTRONIC MAIL</span>
                        <a href="mailto:cheong.munchun@ypccollege.edu.my" class="m-value link-underline">cheong.munchun<br>@ypccollege.edu.my</a>
                    </div>

                    <div class="meta-group">
                        <span class="m-label">02. DIRECT LINE</span>
                        <a href="tel:0123456789" class="m-value link-underline">+60 12-345 6789</a>
                    </div>

                    <div class="meta-group">
                        <span class="m-label">03. COORDINATES</span>
                        <span class="m-value">3°08'20.0"N<br>101°41'13.0"E</span>
                        <a href="https://www.google.com/maps/search/?api=1&query=Kuala+Lumpur+Malaysia" target="_blank" class="m-action">View Map ↗</a>
                    </div>
                </aside>

                <section class="dossier-form-area">
                    <?php if ($msg_sent): ?>
                        <div class="success-block fade-in-slow">
                            <span class="success-icon">✦</span>
                            <h3 class="success-serif">Transmission Logged.</h3>
                            <p class="success-mono">OUR CONCIERGE TEAM HAS RECEIVED YOUR DOSSIER AND WILL RESPOND SHORTLY.</p>
                            <a href="contact.php" class="btn-solid-black">Send Another</a>
                        </div>
                    <?php else: ?>

                        <?php if($error_msg): ?>
                            <div class="error-alert"><?php echo $error_msg; ?></div>
                        <?php endif; ?>

                        <form action="contact.php" method="POST" class="telegram-form">

                            <div class="form-row">
                                <div class="form-group stagger-in" style="--stagger: 1">
                                    <input type="text" id="f_name" name="name" class="t-input" placeholder=" " required>
                                    <label for="f_name" class="t-label">CLIENT IDENTITY</label>
                                    <div class="t-line"></div>
                                </div>
                                <div class="form-group stagger-in" style="--stagger: 2">
                                    <input type="email" id="f_email" name="email" class="t-input" placeholder=" " required>
                                    <label for="f_email" class="t-label">RETURN COORDINATES (EMAIL)</label>
                                    <div class="t-line"></div>
                                </div>
                            </div>

                            <div class="form-group stagger-in" style="--stagger: 3">
                                <input type="text" id="f_subj" name="subject" class="t-input" placeholder=" " required>
                                <label for="f_subj" class="t-label">SUBJECT OF INQUIRY</label>
                                <div class="t-line"></div>
                            </div>

                            <div class="form-group stagger-in" style="--stagger: 4">
                                <textarea id="f_msg" name="message" class="t-input" rows="1" placeholder=" " required oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"></textarea>
                                <label for="f_msg" class="t-label">MESSAGE / DOSSIER</label>
                                <div class="t-line"></div>
                            </div>

                            <div class="form-action stagger-in" style="--stagger: 5">
                                <span class="action-note">TO ENSURE PROMPT RESOLUTION, PLEASE PROVIDE PRECISE DETAILS.</span>
                                <button type="submit" class="btn-dispatch">
                                    DISPATCH <span>↗</span>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>

            </div>
        </div>
    </div>
</main>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

<style>
/* ==============================================
   🎨 THE CONCIERGE DOSSIER (Loro Piana / The Row Vibe)
   ============================================== */

.concierge-page {
    background: transparent;
    padding: 60px 0 100px 0;
    font-family: 'Inter', sans-serif;
    color: #111;
}

.dossier-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px;
}

.fade-up { opacity: 0; animation: dossierUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.fade-in-slow { opacity: 0; animation: fadeIn 1.5s ease forwards; }
@keyframes dossierUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeIn { to { opacity: 1; } }

.dossier-paper {
    background: #fff;
    border: 1px solid #e5e5e5;

    box-shadow: 0 20px 60px rgba(0,0,0,0.02), 0 1px 3px rgba(0,0,0,0.02);
    padding: 60px 80px;
}

.dossier-header {
    border-bottom: 1px solid #111;
    padding-bottom: 30px;
    margin-bottom: 50px;
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.mono-barcode {
    font-family: monospace;
    font-size: 24px;
    color: #111;
    letter-spacing: 2px;
    opacity: 0.8;
}

.live-clock {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: monospace;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1px;
    color: #888;
}
.pulse-dot {
    width: 6px;
    height: 6px;
    background: #ff8002;
    border-radius: 50%;
    position: relative;
}
.pulse-dot::after {
    content: ''; position: absolute; inset: -3px; border-radius: 50%;
    border: 1px solid #ff8002;
    animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
@keyframes ping { 75%, 100% { transform: scale(2.5); opacity: 0; } }
#local-time { color: #111; font-weight: 800; }

.serif-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(48px, 6vw, 72px);
    font-style: italic;
    font-weight: 400;
    margin: 0 0 10px 0;
    line-height: 1;
    letter-spacing: -1px;
}
.mono-subtitle {
    font-family: monospace;
    font-size: 10px;
    color: #888;
    letter-spacing: 2px;
    margin: 0;
}

.dossier-body {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 80px;
}

.dossier-meta {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.meta-group { display: flex; flex-direction: column; gap: 8px; }
.m-label { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 1.5px; font-weight: 700; border-bottom: 1px solid #e5e5e5; padding-bottom: 8px; margin-bottom: 4px; }
.m-value { font-family: 'Playfair Display', serif; font-size: 18px; color: #111; text-decoration: none; line-height: 1.4; word-break: break-word;}

.link-underline { position: relative; width: max-content; }
.link-underline::after { content: ''; position: absolute; bottom: 2px; left: 0; width: 100%; height: 1px; background: #ff8002; transform: scaleX(0); transform-origin: right; transition: transform 0.4s ease; }
.link-underline:hover::after { transform: scaleX(1); transform-origin: left; }

.m-action { font-family: monospace; font-size: 10px; color: #888; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; transition: color 0.3s; }
.m-action:hover { color: #ff8002; }

.telegram-form { display: flex; flex-direction: column; gap: 40px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }

.form-group { position: relative; }
.t-input { width: 100%; padding: 15px 0 10px 0; background: transparent; border: none; outline: none; font-size: 16px; font-family: 'Playfair Display', serif; font-style: italic; color: #111; resize: none; overflow: hidden; }
.t-label { position: absolute; left: 0; top: 15px; color: #888; font-family: monospace; font-size: 10px; font-weight: 600; letter-spacing: 1.5px; pointer-events: none; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }

.t-input:focus ~ .t-label, .t-input:not(:placeholder-shown) ~ .t-label { top: -8px; font-size: 8px; color: #ff8002; }

.t-line { position: absolute; bottom: 0; left: 0; width: 100%; height: 1px; background: #e5e5e5; transition: background 0.3s; }
.t-line::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background: #111; transform: scaleX(0); transform-origin: right; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.t-input:focus ~ .t-line::after { transform: scaleX(1); transform-origin: left; }

.stagger-in { opacity: 0; transform: translateY(15px); animation: staggerFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.stagger-in:nth-child(1) { animation-delay: 0.2s; }
.stagger-in:nth-child(2) { animation-delay: 0.3s; }
.stagger-in:nth-child(3) { animation-delay: 0.4s; }
.stagger-in:nth-child(4) { animation-delay: 0.5s; }
.stagger-in:nth-child(5) { animation-delay: 0.6s; }
@keyframes staggerFade { to { opacity: 1; transform: translateY(0); } }

.form-action { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 20px; border-top: 1px solid #111; padding-top: 20px; }
.action-note { font-family: monospace; font-size: 8px; color: #888; max-width: 200px; line-height: 1.5; letter-spacing: 1px; }

.btn-dispatch { background: transparent; color: #111; border: none; font-family: monospace; font-size: 14px; font-weight: 800; letter-spacing: 2px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: color 0.3s; padding: 0; }
.btn-dispatch span { font-size: 18px; transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.btn-dispatch:hover { color: #ff8002; }
.btn-dispatch:hover span { transform: translate(4px, -4px); }

.error-alert { font-family: monospace; font-size: 10px; color: #ef4444; margin-bottom: 30px; letter-spacing: 1px; }
.success-block { text-align: center; padding: 60px 0; }
.success-icon { font-size: 32px; color: #ff8002; margin-bottom: 20px; display: inline-block; }
.success-serif { font-family: 'Playfair Display', serif; font-size: 32px; font-style: italic; margin: 0 0 10px 0; }
.success-mono { font-family: monospace; font-size: 10px; color: #888; letter-spacing: 2px; margin-bottom: 40px; line-height: 1.6; }
.btn-solid-black { background: #111; color: #fff; text-decoration: none; padding: 15px 30px; font-family: monospace; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; transition: 0.3s; display: inline-block; }
.btn-solid-black:hover { background: #ff8002; }

@media (max-width: 768px) {
    .dossier-paper { padding: 40px 30px; }
    .dossier-body { grid-template-columns: 1fr; gap: 60px; }
    .form-row { grid-template-columns: 1fr; gap: 40px; }
    .header-top { flex-direction: column; align-items: flex-start; gap: 15px; }
    .mono-barcode { display: none; }
    .form-action { flex-direction: column; align-items: flex-start; gap: 20px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        document.getElementById('local-time').textContent = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
    }

    setInterval(updateClock, 1000);
    updateClock();
});
</script>

<?php include 'includes/footer.php'; ?>
