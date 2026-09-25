<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilik E-Voting Koperasi - Tap Kartu Anggota</title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css?v=' . filemtime(FCPATH . 'assets/css/koperasi.css')); ?>">
    <script>
    (function() {
        try {
            var raw = localStorage.getItem('kopus_a11y_prefs');
            if (raw) {
                var p = JSON.parse(raw);
                var el = document.documentElement;
                if (p.fontSize) el.classList.add('a11y-font-' + p.fontSize);
                if (p.contrast && p.contrast !== 'default') el.classList.add('a11y-contrast-' + p.contrast);
                if (p.bold) el.classList.add('a11y-bold');
                if (p.spacing) el.classList.add('a11y-spacing');
                if (p.readingGuide) el.classList.add('a11y-guide-active');
            }
        } catch (e) {}
    })();
    </script>
    <script src="<?= base_url('assets/vendor/jquery/jquery-3.6.0.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.all.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/three/three.min.js'); ?>"></script>
</head>
<body class="bg-light is-scanner-page">

    <!-- Top Info Bar -->
    <header class="kop-navbar">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="<?= base_url(); ?>" class="kop-brand">
                <i class="fas fa-landmark text-success"></i>
                <span><?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi'); ?></span>
                <span class="kop-brand-badge">E-Voting</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="badge <?= ($settings['election_status'] === 'open') ? 'bg-success' : 'bg-warning text-dark'; ?> px-3 py-2">
                    <?= ($settings['election_status'] === 'open') ? 'Bilik Suara Terbuka' : 'Sesi Dijeda'; ?>
                </span>
                <a href="<?= base_url('admin/login'); ?>" class="btn btn-outline-secondary btn-sm" title="Akses Panitia">
                    <i class="fas fa-lock"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="scanner-viewport">
        <div class="scanner-panel">
            <!-- 3D Floating Smartcard Stage (Low-Poly / Tablet-Optimized) -->
            <div class="rfid-3d-stage" id="rfidStage" style="width: 250px; height: 160px; margin: 0 auto 1rem; position: relative; cursor: grab;" title="Sentuh kartu untuk interaksi">
                <div id="threeCardWrap" style="width: 100%; height: 100%;"></div>
            </div>
            
            <h2 class="fw-bold mb-2"><?= htmlspecialchars($settings['election_title'] ?? 'Pemilihan Pengurus Koperasi'); ?></h2>
            <p class="text-secondary mb-4">Silakan tempelkan kartu RFID anggota Anda pada pemindai reader untuk masuk ke bilik suara digital.</p>
            
            <div class="p-3 bg-light rounded-3 text-start border mb-3">
                <div class="d-flex align-items-center gap-2 mb-1 text-dark fw-semibold">
                    <i class="fas fa-shield-alt text-success"></i>
                    <span>Jaminan Asas Luber &amp; Jurdil</span>
                </div>
                <small class="text-muted">
                    Sistem merekam suara secara terenkripsi dan anonim. Pilihan suara tidak ditautkan ke data identitas anggota.
                </small>
            </div>

            <div class="scanner-status">
                <span class="status-dot"></span>
                <span id="statusText">Sensor RFID Siap Menerima Kartu</span>
            </div>

            <?php if (ENVIRONMENT !== 'production' || in_array($this->input->ip_address(), array('127.0.0.1', '::1'), true)): ?>
            <div class="mt-4 pt-3 border-top text-center">
                <a href="<?= base_url('voting/dev_booth'); ?>" class="btn btn-sm btn-outline-secondary" style="font-size: 0.8rem;">
                    <i class="fas fa-terminal text-success me-1"></i> Mode Dev: Masuk Bilik Uji Coba (Tanpa RFID)
                </a>
            </div>
            <?php endif; ?>

            <!-- Hidden RFID Wedge Input Form -->
            <form id="rfidForm" class="visually-hidden">
                <input type="password" id="rfidUid" name="rfid_uid" autocomplete="off" autofocus>
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            </form>
        </div>
    </main>

    <!-- Three.js Floating Smartcard & Scanner Logic -->
    <script>
    (function() {
        // --- 1. Three.js Floating Smartcard Setup ---
        const cardWrap = document.getElementById('threeCardWrap');
        if (!window.THREE || !cardWrap) return;

        let scene, camera, renderer, animId;
        let cardMesh, greenGlowLight;
        let isScanningState = false;
        let mouseX = 0, mouseY = 0;
        let targetRotX = 0, targetRotY = 0;

        const w = cardWrap.clientWidth || 220;
        const h = cardWrap.clientHeight || 145;

        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(38, w / h, 0.1, 50);
        camera.position.set(0, 0, 3.4);

        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(w, h);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
        cardWrap.appendChild(renderer.domElement);

        // Lighting (Warm Key + Ambient + Emerald Accent)
        const amb = new THREE.AmbientLight(0xffffff, 0.85);
        scene.add(amb);

        const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
        dirLight.position.set(2, 4, 3);
        scene.add(dirLight);

        greenGlowLight = new THREE.PointLight(0x10b981, 0.8, 6);
        greenGlowLight.position.set(0, 0, 1.8);
        scene.add(greenGlowLight);

        // Generate Textures for Smartcard (Front & Back)
        function makeFrontTexture() {
            const cv = document.createElement('canvas');
            cv.width = 512;
            cv.height = 320;
            const ctx = cv.getContext('2d');

            // Emerald-Navy Gradient Base
            const grad = ctx.createLinearGradient(0, 0, 512, 320);
            grad.addColorStop(0, '#064e3b');
            grad.addColorStop(0.65, '#065f46');
            grad.addColorStop(1, '#0f172a');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, 512, 320);

            // Subtle gold diagonal ribbon
            ctx.fillStyle = 'rgba(245, 158, 11, 0.25)';
            ctx.beginPath();
            ctx.moveTo(380, 0);
            ctx.lineTo(512, 0);
            ctx.lineTo(420, 320);
            ctx.lineTo(288, 320);
            ctx.closePath();
            ctx.fill();

            // Card Header
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 22px sans-serif';
            ctx.fillText('KOPERASI UBS', 28, 48);
            ctx.font = '14px sans-serif';
            ctx.fillStyle = '#a7f3d0';
            ctx.fillText('KARTU ANGGOTA ELEKTRONIK', 28, 72);

            // Golden Smartcard Chip Graphic
            ctx.fillStyle = '#f59e0b';
            ctx.beginPath();
            ctx.roundRect ? ctx.roundRect(32, 95, 68, 52, 6) : ctx.rect(32, 95, 68, 52);
            ctx.fill();
            ctx.strokeStyle = '#b45309';
            ctx.lineWidth = 2;
            ctx.stroke();

            // Contactless RFID Wave Waves ()))
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            for (let r = 10; r <= 22; r += 6) {
                ctx.beginPath();
                ctx.arc(430, 60, r, -Math.PI * 0.35, Math.PI * 0.35);
                ctx.stroke();
            }

            // Dummy Card Number & Tap Note
            ctx.font = 'bold 20px monospace';
            ctx.fillStyle = '#f8fafc';
            ctx.fillText('••••  ••••  ••••  9213', 32, 215);

            ctx.font = 'bold 15px sans-serif';
            ctx.fillStyle = '#34d399';
            ctx.fillText('TAP PADA READER UNTUK MEMILIH', 32, 275);

            return new THREE.CanvasTexture(cv);
        }

        function makeBackTexture() {
            const cv = document.createElement('canvas');
            cv.width = 512;
            cv.height = 320;
            const ctx = cv.getContext('2d');
            ctx.fillStyle = '#1e293b';
            ctx.fillRect(0, 0, 512, 320);

            // Black Magnetic Stripe
            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 35, 512, 55);

            // White Signature Strip
            ctx.fillStyle = '#f8fafc';
            ctx.fillRect(28, 120, 360, 42);
            ctx.fillStyle = '#94a3b8';
            ctx.font = '14px sans-serif';
            ctx.fillText('AUTHORIZED SIGNATURE / VERIFIED NFC', 36, 146);

            // Barcode Lines
            ctx.fillStyle = '#e2e8f0';
            for (let x = 28; x < 260; x += (x % 7 === 0 ? 8 : 4)) {
                ctx.fillRect(x, 195, 2.5, 38);
            }

            return new THREE.CanvasTexture(cv);
        }

        const frontTex = makeFrontTexture();
        const backTex = makeBackTexture();

        const cardGeo = new THREE.BoxGeometry(2.35, 1.48, 0.035);
        const rimMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.3, metalness: 0.4 });
        const frontMat = new THREE.MeshStandardMaterial({ map: frontTex, roughness: 0.35, metalness: 0.15 });
        const backMat = new THREE.MeshStandardMaterial({ map: backTex, roughness: 0.4, metalness: 0.1 });

        // Materials array: [right, left, top, bottom, front, back]
        const materials = [rimMat, rimMat, rimMat, rimMat, frontMat, backMat];
        cardMesh = new THREE.Mesh(cardGeo, materials);
        scene.add(cardMesh);

        // Interaction (Parallax tracking)
        cardWrap.addEventListener('mousemove', (e) => {
            const rect = cardWrap.getBoundingClientRect();
            mouseX = ((e.clientX - rect.left) / rect.width) * 2 - 1;
            mouseY = -(((e.clientY - rect.top) / rect.height) * 2 - 1);
        });
        cardWrap.addEventListener('mouseleave', () => {
            mouseX = 0; mouseY = 0;
        });

        let isLoopRunning = true;
        function animateCard() {
            if (!isLoopRunning) return;
            animId = requestAnimationFrame(animateCard);

            const t = performance.now() / 1000;

            if (!isScanningState) {
                // Gentle floating bob and slight yaw
                cardMesh.position.y = Math.sin(t * 1.5) * 0.08;
                cardMesh.position.z = 0;

                targetRotY = Math.sin(t * 0.9) * 0.2 + mouseX * 0.35;
                targetRotX = Math.cos(t * 1.1) * 0.08 - mouseY * 0.2;
                cardMesh.rotation.y += (targetRotY - cardMesh.rotation.y) * 0.1;
                cardMesh.rotation.x += (targetRotX - cardMesh.rotation.x) * 0.1;
                greenGlowLight.intensity = 0.8;
            } else {
                // Tapping state: card pushes forward and illuminates with green aura
                cardMesh.position.y = 0;
                cardMesh.position.z += (0.42 - cardMesh.position.z) * 0.15;
                cardMesh.rotation.y += (0 - cardMesh.rotation.y) * 0.2;
                cardMesh.rotation.x += (0 - cardMesh.rotation.x) * 0.2;
                greenGlowLight.intensity = 2.2 + Math.sin(t * 10) * 0.8;
            }

            renderer.render(scene, camera);
        }
        animateCard();

        // Pause loop when tab is hidden to save tablet battery
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                isLoopRunning = false;
                if (animId) cancelAnimationFrame(animId);
            } else {
                if (!isLoopRunning) {
                    isLoopRunning = true;
                    animateCard();
                }
            }
        });

        // Global trigger for scanning state
        window.set3DCardScanning = function(active) {
            isScanningState = active;
        };
    })();

    // --- 2. RFID Form & Scanner Logic ---
    $(document).ready(function() {
        const $input = $('#rfidUid');
        const $form = $('#rfidForm');
        const $statusText = $('#statusText');
        let isProcessing = false;

        function maintainFocus() {
            if (!isProcessing) {
                $input.focus();
            }
        }

        maintainFocus();
        setInterval(maintainFocus, 1000);
        $(document).on('click keydown', function() {
            maintainFocus();
        });

        $form.on('submit', function(e) {
            e.preventDefault();
            const rfidVal = $input.val().trim();
            if (!rfidVal || isProcessing) return;

            isProcessing = true;
            if (window.set3DCardScanning) window.set3DCardScanning(true);
            $statusText.text('Memverifikasi data kartu...');

            $.ajax({
                url: '<?= base_url("voting/verify_rfid"); ?>',
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        $statusText.text('Verifikasi berhasil. Membuka bilik suara...');
                        window.location.href = res.redirect;
                    } else {
                        isProcessing = false;
                        if (window.set3DCardScanning) window.set3DCardScanning(false);
                        $statusText.text('Sensor RFID Siap Menerima Kartu');
                        $input.val('');
                        
                        Swal.fire({
                            title: 'Pemberitahuan',
                            text: res.message,
                            icon: 'warning',
                            confirmButtonColor: '#0f5132',
                            confirmButtonText: 'Kembali'
                        }).then(() => {
                            maintainFocus();
                        });
                    }
                },
                error: function(xhr) {
                    isProcessing = false;
                    if (window.set3DCardScanning) window.set3DCardScanning(false);
                    $statusText.text('Sensor RFID Siap Menerima Kartu');
                    $input.val('');

                    let msg = 'Terjadi gangguan jaringan atau sesi tidak sah.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        title: 'Akses Ditolak',
                        text: msg,
                        icon: 'error',
                        confirmButtonColor: '#0f5132'
                    }).then(() => {
                        maintainFocus();
                    });
                }
            });
        });
    });
    </script>

    <!-- Web Accessibility Widget (Fitur Aksesibilitas) -->
    <?php $this->load->view('voting/accessibility_widget', ['page' => 'scanner']); ?>
</body>
</html>
