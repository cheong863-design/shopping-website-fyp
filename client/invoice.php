<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) { die("Access Denied."); }

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

$items_query = "SELECT oi.*, p.name as product_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = $order_id";
$items_res = mysqli_query($conn, $items_query);

$items_array = [];
$real_subtotal = 0;

if ($items_res && mysqli_num_rows($items_res) > 0) {
    while($item = mysqli_fetch_assoc($items_res)) {
        $items_array[] = $item;
        $real_subtotal += ((float)$item['price'] * (int)$item['quantity']);
    }
}

$order_query = "SELECT o.*, u.full_name, u.email
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = $order_id";

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    $order_query .= " AND o.user_id = $user_id";
}

$order_res = mysqli_query($conn, $order_query);
$order = $order_res ? mysqli_fetch_assoc($order_res) : null;

if (!$order) {
    $order = ['id' => $order_id, 'created_at' => date('Y-m-d H:i:s')];
}

$full_name = $order['full_name'] ?? 'ANONYMOUS ENTITY';
$email = $order['email'] ?? 'REDACTED_DATA';
$payment_method = $order['payment_method'] ?? 'SECURE TERMINAL';
$payment_ref = $order['payment_ref'] ?? 'REF-NODE-' . rand(1000, 9999);
$order_status = $order['status'] ?? 'AUTHORIZED';
$order_date = isset($order['created_at']) ? date('F d, Y', strtotime($order['created_at'])) : date('F d, Y');

$total_price    = (float)($order['total_price'] ?? 0.00);
$shipping_fee   = (float)($order['shipping_fee'] ?? 0.00);
$subtotal       = $real_subtotal;

$calc_tax       = $total_price - $subtotal - $shipping_fee;
$tax_amount     = $calc_tax > 0 ? $calc_tax : (float)($order['tax_amount'] ?? 0.00);

$display_total  = $subtotal + $shipping_fee + $tax_amount;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>3D Physical Receipt - #FAIFA-<?php echo $order_id; ?></title>
    <link rel="icon" type="image/png" href="assets/images/faifa-logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>

        body { margin: 0; padding: 0; overflow: hidden; background: #fdfcfb; font-family: 'Inter', sans-serif; }

        #webgl-container { position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 10; cursor: grab; background: #fdfcfb; }
        #webgl-container:active { cursor: grabbing; }

        .interaction-hint {
            position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%);
            font-family: monospace; font-size: 11px; color: #888; letter-spacing: 2px;
            z-index: 20; pointer-events: none; text-align: center;
            background: rgba(255,255,255,0.8); padding: 10px 20px; border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        #invoice-capture-zone {

            position: absolute; top: 0; left: 0; z-index: 1;
            width: 800px;
            height: max-content;
            background: #111111;
            padding: 2px;
            box-sizing: border-box;
        }

        .receipt-inner {
            background: #ffffff;
            padding: 80px 60px;

            border: 1px solid #e5e5e5;
            position: relative;
            height: 100%;
            box-sizing: border-box;
        }

        .invoice-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #e5e5e5; padding-bottom: 40px; margin-bottom: 40px; }
        .brand-name { font-family: 'Playfair Display', serif; font-size: 56px; font-weight: 700; margin: 0; line-height: 1; letter-spacing: -2px; }
        .brand-tag { font-family: monospace; font-size: 14px; color: #666; letter-spacing: 3px; }

        .invoice-meta { text-align: right; display: flex; flex-direction: column; gap: 8px; }
        .doc-type { font-family: monospace; font-size: 16px; color: #ff8002; letter-spacing: 3px; font-weight: 800; margin-bottom: 10px; }
        .meta-line { font-family: monospace; font-size: 14px; font-weight: 600; }
        .meta-label { color: #888; margin-right: 10px; }

        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 60px; }
        .detail-title { font-family: monospace; font-size: 14px; font-weight: 800; color: #666; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px; border-bottom: 2px solid #111; padding-bottom: 5px; width: fit-content; }
        .detail-name { font-size: 24px; margin: 0 0 5px 0; font-family: 'Playfair Display', serif; font-style: italic; }
        .detail-sub { font-family: monospace; font-size: 14px; color: #666; display: block; }

        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 60px; }
        .invoice-table th { text-align: left; padding: 20px 10px; border-bottom: 2px solid #111; font-family: monospace; font-size: 14px; font-weight: 800; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .invoice-table td { padding: 25px 10px; border-bottom: 1px solid #e5e5e5; font-size: 18px; font-weight: 600;}
        .mono-num { font-family: monospace; font-size: 18px; }

        .summary-wrapper { display: flex; justify-content: flex-end; margin-bottom: 80px;}
        .summary-box { width: 400px; display: flex; flex-direction: column; gap: 15px; }
        .summary-row { display: flex; justify-content: space-between; font-family: monospace; font-size: 16px; color: #666; }
        .summary-row.total { border-top: 2px solid #111; margin-top: 15px; padding-top: 25px; color: #111; font-size: 24px; font-weight: 800; }

        .invoice-footer { border-top: 2px solid #e5e5e5; padding-top: 30px; display: flex; justify-content: space-between; font-family: monospace; font-size: 14px; color: #888; letter-spacing: 2px; }

        .auth-stamp { position: absolute; top: 250px; left: 50px; font-family: monospace; font-size: 70px; font-weight: 800; color: rgba(17, 17, 17, 0.04); border: 4px solid rgba(17, 17, 17, 0.04); padding: 20px 30px; transform: rotate(-15deg); pointer-events: none; }
    </style>
</head>
<body>

    <div class="interaction-hint">
        [ LEFT CLICK & DRAG TO INTERACT WITH THE RECEIPT ]
    </div>

    <div id="webgl-container"></div>

    <div id="invoice-capture-zone">
        <div class="receipt-inner">
            <div class="auth-stamp">AUTHORIZED</div>

            <header class="invoice-header">
                <div class="logo-box">
                    <h1 class="brand-name">FAIFA.</h1>
                    <span class="brand-tag">KUALA LUMPUR HQ</span>
                </div>
                <div class="invoice-meta">
                    <span class="doc-type">DIGITAL ARCHIVE // RECEIPT</span>
                    <div class="meta-line"><span class="meta-label">REF ID</span> #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></div>
                    <div class="meta-line"><span class="meta-label">ISSUED</span> <?php echo htmlspecialchars($order_date); ?></div>
                </div>
            </header>

            <div class="details-grid">
                <div class="detail-block">
                    <h4 class="detail-title">BILLED TO</h4>
                    <p class="detail-name"><?php echo htmlspecialchars($full_name); ?></p>
                    <span class="detail-sub"><?php echo htmlspecialchars($email); ?></span>
                </div>
                <div class="detail-block right-align" style="text-align: right;">
                    <h4 class="detail-title" style="margin-left: auto;">TRANSACTION</h4>
                    <p class="detail-name"><?php echo strtoupper(htmlspecialchars($payment_method)); ?></p>
                    <span class="detail-sub">REF: <?php echo htmlspecialchars($payment_ref); ?></span>
                    <span class="detail-sub" style="color: #10b981; font-weight: bold; margin-top:8px;">STATUS: <?php echo strtoupper(htmlspecialchars($order_status)); ?></span>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($items_array)): ?>
                        <?php foreach($items_array as $item): ?>
                        <tr>
                            <td class="item-name"><?php echo htmlspecialchars($item['product_name'] ?? 'Unknown Artifact'); ?></td>
                            <td class="mono-num" style="text-align: center;">x<?php echo $item['quantity'] ?? 0; ?></td>
                            <td class="mono-num" style="text-align: right;"><?php echo number_format($item['price'] ?? 0, 2); ?></td>
                            <td class="mono-num" style="text-align: right; font-weight:800;">MYR <?php echo number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #888; font-style: italic; padding: 40px 0;">No specific artifacts found for this reference.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="summary-wrapper">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>SUBTOTAL</span>
                        <span class="mono-num"><?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>SHIPPING (DISPATCH)</span>
                        <span class="mono-num"><?php echo number_format($shipping_fee, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>TAX (SST/GST)</span>
                        <span class="mono-num"><?php echo number_format($tax_amount, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>NET TOTAL</span>
                        <span class="mono-num">MYR <?php echo number_format($display_total, 2); ?></span>
                    </div>
                </div>
            </div>

            <footer class="invoice-footer">
                <span>SYSTEM GENERATED DOSSIER. NO SIGNATURE REQUIRED.</span>
                <span>WWW.FAIFA.COM.MY</span>
            </footer>
        </div>
    </div>

<script>

class Particle {
    constructor(x, y, z, mass) {
        this.position = new THREE.Vector3(x, y, z);
        this.previous = new THREE.Vector3(x, y, z);
        this.original = new THREE.Vector3(x, y, z);
        this.acceleration = new THREE.Vector3(0, 0, 0);
        this.mass = mass;
        this.invMass = mass > 0 ? 1 / mass : 0;
    }

    addForce(force) {
        if (this.invMass > 0) {
            this.acceleration.add(force.clone().multiplyScalar(this.invMass));
        }
    }

    integrate(sqDeltaT) {
        if (this.invMass === 0) return;

        const damping = 0.95;
        const newPos = this.position.clone()
            .sub(this.previous)
            .multiplyScalar(damping)
            .add(this.position)
            .add(this.acceleration.clone().multiplyScalar(sqDeltaT));

        this.previous.copy(this.position);
        this.position.copy(newPos);
        this.acceleration.set(0, 0, 0);
    }
}

class Constraint {
    constructor(p1, p2, distance) {
        this.p1 = p1;
        this.p2 = p2;
        this.distance = distance;
    }

    satisfy() {
        const diff = new THREE.Vector3().subVectors(this.p2.position, this.p1.position);
        const currentDist = diff.length();
        if (currentDist === 0) return;

        const correction = diff.multiplyScalar(1 - this.distance / currentDist);
        const halfCorrection = correction.multiplyScalar(0.5);

        if (this.p1.invMass > 0 && this.p2.invMass > 0) {
            this.p1.position.add(halfCorrection);
            this.p2.position.sub(halfCorrection);
        } else if (this.p1.invMass > 0) {
            this.p1.position.add(correction);
        } else if (this.p2.invMass > 0) {
            this.p2.position.sub(correction);
        }
    }
}

let scene, camera, renderer;
let paperMesh, paperGeometry;
let particles = [], constraints = [];

const paperWidth = 15;
let paperHeight = 20;
const segmentsX = 15;
let segmentsY = 30;

let draggedParticle = null;
let mousePosition = new THREE.Vector2();
let raycaster = new THREE.Raycaster();
let dragPlane = new THREE.Plane();
let intersectionPoint = new THREE.Vector3();

function init() {
    const container = document.getElementById('webgl-container');

    scene = new THREE.Scene();
    scene.background = null;

    camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.set(0, 0, 30);
    camera.lookAt(0, 0, 0);

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    container.appendChild(renderer.domElement);

    const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
    scene.add(ambientLight);

    const dirLight = new THREE.DirectionalLight(0xffffff, 0.4);
    dirLight.position.set(15, 15, 20);
    dirLight.castShadow = true;
    dirLight.shadow.mapSize.width = 2048;
    dirLight.shadow.mapSize.height = 2048;
    scene.add(dirLight);

    document.fonts.ready.then(() => {
        setTimeout(captureAndBuild, 500);
    });

    window.addEventListener('resize', onWindowResize);
    container.addEventListener('mousedown', onMouseDown);
    container.addEventListener('mousemove', onMouseMove);
    container.addEventListener('mouseup', onMouseUp);
    container.addEventListener('mouseleave', onMouseUp);
    container.addEventListener('touchstart', onTouchStart, {passive: false});
    container.addEventListener('touchmove', onTouchMove, {passive: false});
    container.addEventListener('touchend', onMouseUp);
}

function captureAndBuild() {
    const targetDOM = document.getElementById('invoice-capture-zone');

    html2canvas(targetDOM, {
        scale: 1.5,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false,
        width: targetDOM.scrollWidth,
        height: targetDOM.scrollHeight,
        windowWidth: targetDOM.scrollWidth,
        windowHeight: targetDOM.scrollHeight,
        x: 0,
        y: 0,
        scrollY: 0
    }).then(canvas => {
        const aspect = canvas.height / canvas.width;
        paperHeight = paperWidth * aspect;
        segmentsY = Math.floor(segmentsX * aspect * 1.2);

        const cameraZ = Math.max(22, paperHeight * 1.05);
        camera.position.set(0, 0, cameraZ);
        camera.lookAt(0, 0, 0);

        const texture = new THREE.CanvasTexture(canvas);
        texture.anisotropy = renderer.capabilities.getMaxAnisotropy();
        texture.minFilter = THREE.LinearMipmapLinearFilter;

        const paperMaterial = new THREE.MeshStandardMaterial({
            map: texture,
            side: THREE.DoubleSide,
            roughness: 0.9,
            metalness: 0.0,
            color: 0xffffff
        });

        buildPhysics(paperWidth, paperHeight, segmentsX, segmentsY);

        paperMesh = new THREE.Mesh(paperGeometry, paperMaterial);
        paperMesh.castShadow = true;
        paperMesh.receiveShadow = true;
        scene.add(paperMesh);

        animate();
    });
}

function buildPhysics(width, height, segX, segY) {
    for (let j = 0; j <= segY; j++) {
        for (let i = 0; i <= segX; i++) {
            const x = (i / segX - 0.5) * width;
            const y = (0.5 - j / segY) * height;
            const z = 0;

            const mass = (j === 0) ? 0 : 0.1;
            particles.push(new Particle(x, y, z, mass));
        }
    }

    function getParticle(i, j) { return particles[j * (segX + 1) + i]; }

    for (let j = 0; j <= segY; j++) {
        for (let i = 0; i <= segX; i++) {
            const p = getParticle(i, j);

            if (i < segX) {
                const pRight = getParticle(i + 1, j);
                constraints.push(new Constraint(p, pRight, p.original.distanceTo(pRight.original)));
            }
            if (j < segY) {
                const pDown = getParticle(i, j + 1);
                constraints.push(new Constraint(p, pDown, p.original.distanceTo(pDown.original)));
            }

            if (i < segX && j < segY) {
                const pDiag = getParticle(i + 1, j + 1);
                constraints.push(new Constraint(p, pDiag, p.original.distanceTo(pDiag.original)));
                const pDiag2 = getParticle(i, j + 1);
                const pRight = getParticle(i + 1, j);
                constraints.push(new Constraint(pDiag2, pRight, pDiag2.original.distanceTo(pRight.original)));
            }

            if (j < segY - 1) {
                const pDown2 = getParticle(i, j + 2);
                constraints.push(new Constraint(p, pDown2, p.original.distanceTo(pDown2.original)));
            }
            if (i < segX - 1) {
                const pRight2 = getParticle(i + 2, j);
                constraints.push(new Constraint(p, pRight2, p.original.distanceTo(pRight2.original)));
            }
        }
    }

    paperGeometry = new THREE.PlaneGeometry(width, height, segX, segY);
}

const gravity = new THREE.Vector3(0, -9.8 * 2, 0);
const windForce = new THREE.Vector3();
const TIMESTEP = 18 / 1000;
const TIMESTEP_SQ = TIMESTEP * TIMESTEP;
let simTime = 0;

function simulate() {
    simTime += TIMESTEP;

    windForce.set(Math.sin(simTime * 2) * 1.5, 0, Math.cos(simTime * 1.5) * 2.0);

    for (let i = 0; i < particles.length; i++) {
        const p = particles[i];
        p.addForce(gravity);
        p.addForce(windForce);
        p.integrate(TIMESTEP_SQ);
    }

    const iterations = 15;
    for (let i = 0; i < iterations; i++) {
        for (let j = 0; j < constraints.length; j++) {
            constraints[j].satisfy();
        }
    }

    if (draggedParticle) {
        draggedParticle.position.copy(intersectionPoint);
    }

    if (paperGeometry) {
        const positions = paperGeometry.attributes.position.array;
        for (let i = 0; i < particles.length; i++) {
            positions[i * 3] = particles[i].position.x;
            positions[i * 3 + 1] = particles[i].position.y;
            positions[i * 3 + 2] = particles[i].position.z;
        }
        paperGeometry.attributes.position.needsUpdate = true;
        paperGeometry.computeVertexNormals();
    }
}

function handleInteractionStart(clientX, clientY) {
    if (!paperMesh) return;

    mousePosition.x = (clientX / window.innerWidth) * 2 - 1;
    mousePosition.y = -(clientY / window.innerHeight) * 2 + 1;

    raycaster.setFromCamera(mousePosition, camera);
    const intersects = raycaster.intersectObject(paperMesh);

    if (intersects.length > 0) {
        const point = intersects[0].point;
        let closestDist = Infinity;
        let closestP = null;

        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];
            if (p.invMass === 0) continue;

            const dist = p.position.distanceTo(point);
            if (dist < closestDist) {
                closestDist = dist;
                closestP = p;
            }
        }

        if (closestP) {
            draggedParticle = closestP;
            dragPlane.setFromNormalAndCoplanarPoint(camera.getWorldDirection(dragPlane.normal), draggedParticle.position);
        }
    }
}

function handleInteractionMove(clientX, clientY) {
    if (draggedParticle) {
        mousePosition.x = (clientX / window.innerWidth) * 2 - 1;
        mousePosition.y = -(clientY / window.innerHeight) * 2 + 1;
        raycaster.setFromCamera(mousePosition, camera);
        raycaster.ray.intersectPlane(dragPlane, intersectionPoint);
    }
}

function onMouseDown(event) { handleInteractionStart(event.clientX, event.clientY); }
function onMouseMove(event) { handleInteractionMove(event.clientX, event.clientY); }
function onTouchStart(event) {
    if(event.touches.length > 0) {
        event.preventDefault();
        handleInteractionStart(event.touches[0].clientX, event.touches[0].clientY);
    }
}
function onTouchMove(event) {
    if(draggedParticle && event.touches.length > 0) {
        event.preventDefault();
        handleInteractionMove(event.touches[0].clientX, event.touches[0].clientY);
    }
}

function onMouseUp() { draggedParticle = null; }
function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
}

function animate() {
    requestAnimationFrame(animate);
    simulate();
    renderer.render(scene, camera);
}

window.onload = init;

</script>
</body>
</html>
