<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

$target_dir = "../assets/images/";
$target_file_path = $target_dir . "background.png";

$msg_state = ""; // success | error
$err_info = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['banner_image'])) {
    $file = $_FILES['banner_image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $check = getimagesize($file['tmp_name']);
        if($check !== false) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($file_ext, $allowed_types)) {
                if (move_uploaded_file($file['tmp_name'], $target_file_path)) {
                    $msg_state = "success";
                } else { $msg_state = "error"; $err_info = "Permissions Error. Check folder writes."; }
            } else { $msg_state = "error"; $err_info = "Format Unsupported. Use JPG/PNG/WEBP."; }
        } else { $msg_state = "error"; $err_info = "Invalid image file detected."; }
    }
}
$cache_buster = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/main-logo.png">
    <title>Banner Studio - FAIFA Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@600;800&family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>

        @keyframes fadeUp { 0% { opacity: 0; transform: translateY(30px); } 100% { opacity: 1; transform: translateY(0); } }
        @keyframes imageReveal { 0% { filter: brightness(1.5) blur(15px); opacity: 0; transform: scale(0.98); } 100% { filter: brightness(1) blur(0); opacity: 1; transform: scale(1); } }
        @keyframes pulseGlow { 0% { box-shadow: 0 0 0 0 rgba(255, 128, 2, 0.4); } 70% { box-shadow: 0 0 0 12px rgba(255, 128, 2, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 128, 2, 0); } }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes iconBounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }

        body { background: #f8fafc; font-family: 'Inter', sans-serif; color: #0f172a; margin: 0; overflow-x: hidden; }

        .admin-main { margin-left: 220px !important; padding: 50px 40px 100px 40px !important; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; }

        .page-wrapper {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
        }

        .dynamic-island {
            position: fixed; top: -100px; left: calc(50% + 110px); transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            color: #0f172a; padding: 14px 28px; border-radius: 100px; font-size: 13px; font-weight: 800;
            display: flex; align-items: center; gap: 14px; z-index: 9999;
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .dynamic-island.show { top: 35px; }

        .dash-header-top { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 35px; animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .page-title h1 { margin: 0; font-size: 34px; font-weight: 900; letter-spacing: -1.5px; }
        .breadcrumb { font-size: 11px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; display: block; }

        .preview-card {
            background: #fff; padding: 40px 45px; border-radius: 32px; border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 25px 60px -15px rgba(0,0,0,0.05); animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
            position: relative;
        }

        .preview-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }

        .safezone-toggle {
            display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; color: #64748b;
            cursor: pointer; user-select: none; transition: 0.3s; background: #f8fafc; padding: 10px 20px; border-radius: 12px; border: 1px solid #e2e8f0;
        }
        .safezone-toggle:hover { background: #f1f5f9; color: #0f172a; }
        .safezone-icon { opacity: 0.5; transition: 0.3s; font-size: 14px;}
        .safezone-toggle.active .safezone-icon { opacity: 1; color: #ff8002; }

        /* macOS Browser Mockup */
        .browser-mockup {
            border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.06); background: #fff;
        }
        .browser-header {
            background: #fafbfc; border-bottom: 1px solid #e2e8f0; padding: 14px 24px;
            display: flex; align-items: center; gap: 10px; position: relative;
        }
        .traffic-lights { display: flex; gap: 8px; }
        .tl-dot { width: 12px; height: 12px; border-radius: 50%; }
        .tl-red { background: #ff5f56; } .tl-yellow { background: #ffbd2e; } .tl-green { background: #27c93f; }
        .browser-url { position: absolute; left: 50%; transform: translateX(-50%); background: #fff; border: 1px solid #e2e8f0; padding: 6px 20px; border-radius: 10px; font-size: 12px; color: #94a3b8; font-weight: 600; font-family: 'JetBrains Mono', monospace; display: flex; align-items: center; gap: 8px;}

        .banner-container {
            width: 100%; height: 420px; position: relative; display: flex; align-items: center; justify-content: center;
            background-color: #f1f5f9;
            background-image: linear-gradient(45deg, #e2e8f0 25%, transparent 25%, transparent 75%, #e2e8f0 75%, #e2e8f0), linear-gradient(45deg, #e2e8f0 25%, transparent 25%, transparent 75%, #e2e8f0 75%, #e2e8f0);
            background-size: 24px 24px; background-position: 0 0, 12px 12px;
            overflow: hidden;
        }
        #banner-preview { width: 100%; height: 100%; object-fit: cover; transition: 0.6s cubic-bezier(0.4, 0, 0.2, 1); position: relative; z-index: 2; }

        .safe-zone-overlay {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 70%; height: 60%; z-index: 5; pointer-events: none;
            border: 2px dashed rgba(255,255,255,0.9); border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.4s ease;
            box-shadow: inset 0 0 80px rgba(0,0,0,0.3), 0 0 50px rgba(0,0,0,0.2);
            background-image: linear-gradient(to right, rgba(255,255,255,0.15) 50%, transparent 50%);
            background-size: 20px 100%;
        }
        .safe-zone-overlay.visible { opacity: 1; }
        .safe-zone-text { color: rgba(255,255,255,0.95); font-size: 26px; font-weight: 900; letter-spacing: 3px; text-shadow: 0 4px 15px rgba(0,0,0,0.6); text-transform: uppercase;}

        .control-grid { display: grid; grid-template-columns: 1fr 340px; gap: 35px; margin-top: 40px; }

        .dropzone-wrapper {
            background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 24px;
            padding: 50px; text-align: center; cursor: pointer; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center;
            overflow: hidden; min-height: 220px;
        }
        .dropzone-wrapper:hover { border-color: #ff8002; background: #fff; transform: translateY(-3px); box-shadow: 0 15px 40px rgba(255,128,2,0.06); }
        .dropzone-wrapper.dragover { border-color: #ff8002; background: #fff7ed; transform: scale(1.02); }

        .upload-icon { width: 72px; height: 72px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 20px; transition: 0.3s; border: 1px solid #f1f5f9; animation: iconBounce 3s ease-in-out infinite;}
        .dropzone-wrapper:hover .upload-icon { transform: translateY(-5px) scale(1.05); box-shadow: 0 15px 35px rgba(255,128,2,0.15); border-color: #fed7aa; color: #ff8002;}
        .dropzone-text h4 { margin: 0; font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
        .dropzone-text p { margin: 8px 0 0 0; color: #64748b; font-size: 14px; font-weight: 500; }

        #file-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }

        .meta-pane { background: #fff; border-radius: 24px; padding: 30px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); }
        .meta-header { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 5px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; }

        .dna-row { display: flex; justify-content: space-between; align-items: baseline; }
        .dna-label { font-size: 13px; font-weight: 600; color: #64748b; }
        .dna-value { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #0f172a; font-weight: 800; text-align: right; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .dna-value.danger { color: #ef4444; background: #fef2f2; padding: 2px 8px; border-radius: 6px;}

        .btn-deploy {
            width: 100%; background: #0f172a; color: #fff; border: none; padding: 18px;
            border-radius: 14px; font-weight: 800; font-size: 15px; cursor: pointer;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-top: auto;
        }
        .btn-deploy:disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; }
        .btn-deploy.ready { background: linear-gradient(135deg, #ff8002, #ea580c); animation: pulseGlow 2s infinite; box-shadow: 0 10px 30px rgba(255,128,2,0.25); }
        .btn-deploy.ready:hover { transform: translateY(-4px) scale(1.02); filter: brightness(1.1); }

        .btn-spinner { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; display: none; }
        .btn-deploy.loading .btn-spinner { display: block; }
        .btn-deploy.loading .btn-text { display: none; }
        .btn-deploy.loading { animation: none; background: #ea580c; cursor: wait; transform: scale(0.98); box-shadow: none; }
    </style>
</head>
<body class="admin-layout">
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <div id="dynamic-island" class="dynamic-island">
            <span id="di-icon"></span>
            <span id="di-msg">System Ready</span>
        </div>

        <main class="admin-main">
            <div class="page-wrapper">

                <header class="dash-header-top">
                    <div class="page-title">
                        <span class="breadcrumb">Marketing > Storefront Design</span>
                        <h1>Hero Visual Lab</h1>
                        <p>High-fidelity banner management for your storefront's first impression.</p>
                    </div>
                </header>

                <div class="preview-card">
                    <div class="preview-header">
                        <h3 style="margin:0; font-size:20px; font-weight:900; letter-spacing: -0.5px; color: #0f172a;">Storefront Simulation</h3>
                        <div class="safezone-toggle" id="safezone-btn">
                            <span class="safezone-icon">⛶</span> Toggle Typography Safe Zone
                        </div>
                    </div>

                    <div class="browser-mockup">
                        <div class="browser-header">
                            <div class="traffic-lights">
                                <div class="tl-dot tl-red"></div>
                                <div class="tl-dot tl-yellow"></div>
                                <div class="tl-dot tl-green"></div>
                            </div>
                            <div class="browser-url">🔒 faifa-store.com/home</div>
                        </div>

                        <div class="banner-container">
                            <div class="safe-zone-overlay" id="safe-zone">
                                <div class="safe-zone-text">CONTENT AREA</div>
                            </div>

                            <img id="banner-preview"
                                 src="<?php echo $target_file_path . '?v=' . $cache_buster; ?>"
                                 onerror="this.src='https://placehold.co/1920x600/f1f5f9/94a3b8?text=AWAITING+ASSET'">
                        </div>
                    </div>

                    <form id="upload-form" action="update-banners.php" method="POST" enctype="multipart/form-data">
                        <div class="control-grid">
                            <div class="dropzone-wrapper" id="dropzone">
                                <div class="upload-icon" id="dropzone-icon">🏞️</div>
                                <div class="dropzone-text">
                                    <h4 id="dropzone-title">Transfer Visual Asset</h4>
                                    <p id="dropzone-subtitle">Drag and drop image here or click to browse</p>
                                </div>
                                <input type="file" name="banner_image" id="file-input" required accept="image/png, image/jpeg, image/webp">
                            </div>

                            <div class="meta-pane">
                                <div class="meta-header">Asset Fingerprint</div>
                                <div class="dna-row">
                                    <span class="dna-label">File Name</span>
                                    <span class="dna-value" id="dna-name">---</span>
                                </div>
                                <div class="dna-row">
                                    <span class="dna-label">Resolution</span>
                                    <span class="dna-value" id="dna-dim">--- × ---</span>
                                </div>
                                <div class="dna-row">
                                    <span class="dna-label">File Size</span>
                                    <span class="dna-value" id="dna-size">0.00 MB</span>
                                </div>
                                <div class="dna-row">
                                    <span class="dna-label">Format</span>
                                    <span class="dna-value" id="dna-ext">---</span>
                                </div>

                                <button type="submit" id="submit-btn" class="btn-deploy" disabled>
                                    <div class="btn-spinner"></div>
                                    <div class="btn-text"><span>🚀</span> Deploy to Site</div>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <p style="text-align: center; color: #cbd5e1; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-top: 40px; animation: fadeUp 0.6s both 0.3s;">
                    Secure Asset Protocol // FAIFA Core v2.2
                </p>
            </div>
        </main>
    </div>

    <?php if($msg_state != ""): ?>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const isSuccess = "<?php echo $msg_state; ?>" === "success";
                const msg = isSuccess ? "Broadcast Successful: Homepage Hero updated." : "<?php echo $err_info; ?>";
                showToast(msg, isSuccess ? 'success' : 'error');
            });
        </script>
    <?php endif; ?>

    <script>
        const fileInput = document.getElementById('file-input');
        const previewImg = document.getElementById('banner-preview');
        const submitBtn = document.getElementById('submit-btn');
        const dropzone = document.getElementById('dropzone');

        const dnaName = document.getElementById('dna-name');
        const dnaDim = document.getElementById('dna-dim');
        const dnaSize = document.getElementById('dna-size');
        const dnaExt = document.getElementById('dna-ext');
        const uploadForm = document.getElementById('upload-form');

        window.addEventListener('load', () => {
            previewImg.style.animation = 'imageReveal 1s cubic-bezier(0.16, 1, 0.3, 1) forwards';
        });

        let toastTimeout;
        function showToast(msg, type) {
            const island = document.getElementById('dynamic-island');
            const icon = document.getElementById('di-icon');
            document.getElementById('di-msg').innerText = msg;

            if(type === 'success') icon.innerHTML = '<span style="color:#10b981; font-size: 18px;">✅</span>';
            else if(type === 'error') icon.innerHTML = '<span style="color:#ef4444; font-size: 18px;">❌</span>';

            island.classList.add('show');
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => island.classList.remove('show'), 4000);
        }

        const safezoneBtn = document.getElementById('safezone-btn');
        const safeZone = document.getElementById('safe-zone');
        safezoneBtn.addEventListener('click', () => {
            safezoneBtn.classList.toggle('active');
            safeZone.classList.toggle('visible');
        });

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                dnaSize.innerText = fileSizeMB + " MB";

                if(fileSizeMB > 8.00) {
                    showToast("Asset exceeds 8MB limit.", "error");
                    dnaSize.classList.add('danger');
                    submitBtn.disabled = true;
                    submitBtn.classList.remove('ready');
                    return;
                } else {
                    dnaSize.classList.remove('danger');
                    submitBtn.disabled = false;
                    submitBtn.classList.add('ready');
                }

                dnaExt.innerText = file.name.split('.').pop().toUpperCase();
                dnaName.innerText = file.name;

                document.getElementById('dropzone-icon').innerText = "✅";
                document.getElementById('dropzone-title').innerText = "Asset Locked";
                document.getElementById('dropzone-title').style.color = "#10b981";
                document.getElementById('dropzone-subtitle').innerText = "Ready to broadcast";
                document.getElementById('dropzone-icon').style.animation = "none";
                document.getElementById('dropzone-icon').style.borderColor = "#10b981";
                document.getElementById('dropzone-icon').style.color = "#10b981";

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.style.animation = 'none';
                    void previewImg.offsetWidth;
                    previewImg.style.animation = 'imageReveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                    previewImg.src = e.target.result;

                    const img = new Image();
                    img.onload = function() {
                        dnaDim.innerText = `${this.width} × ${this.height}`;
                    };
                    img.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        ['dragenter', 'dragover'].forEach(eName => {
            dropzone.addEventListener(eName, (e) => {
                e.preventDefault(); dropzone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(eName => {
            dropzone.addEventListener(eName, () => dropzone.classList.remove('dragover'));
        });

        uploadForm.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.classList.remove('ready');
        });
    </script>
</body>
</html>
