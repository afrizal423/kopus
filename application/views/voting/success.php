<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suara Sah Berhasil Direkam - E-Voting Koperasi</title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
    <script src="<?= base_url('assets/vendor/three/three.min.js'); ?>"></script>
    <style>
        .receipt-card-3d {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
            padding: 2rem 2.25rem;
            max-width: 560px;
            margin: 1.5rem auto;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .ballot-3d-stage {
            width: 100%;
            height: 250px;
            border-radius: 16px;
            position: relative;
            background: radial-gradient(circle at 50% 35%, #ffffff 0%, #f1f5f9 65%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            margin-bottom: 1.25rem;
            overflow: hidden;
            box-shadow: inset 0 2px 8px rgba(15, 23, 42, 0.04);
            cursor: grab;
        }
        .ballot-3d-stage:active {
            cursor: grabbing;
        }
        .ballot-status-pill {
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(6px);
            border: 1px solid #a7f3d0;
            color: #065f46;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.12);
            z-index: 10;
            transition: all 0.3s ease;
        }
        .ballot-hint {
            position: absolute;
            bottom: 8px;
            right: 12px;
            font-size: 0.7rem;
            color: #94a3b8;
            pointer-events: none;
            user-select: none;
        }
        .countdown-progress {
            height: 4px;
            background-color: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
            margin: 1rem 0 1.25rem;
        }
        .countdown-progress-bar {
            height: 100%;
            background-color: #059669;
            width: 100%;
            transition: width 1s linear;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container py-3 py-md-4">
        <div class="receipt-card-3d">
            <!-- 3D Ballot Box Stage -->
            <div class="ballot-3d-stage" id="stageContainer" title="Geser untuk memutar kotak suara">
                <div class="ballot-status-pill" id="ballotStatusPill">
                    <span class="spinner-border spinner-border-sm text-success" style="width: 12px; height: 12px; border-width: 2px;"></span>
                    <span>Memasukkan Suara ke Kotak...</span>
                </div>
                <div id="threeCanvasWrap" style="width: 100%; height: 100%;"></div>
                <div class="ballot-hint">
                    <i class="fas fa-arrows-alt me-1"></i> Sentuh / geser untuk memutar
                </div>
            </div>

            <h1 class="h4 fw-bold text-dark mb-1">Terima Kasih atas Partisipasi Anda!</h1>
            <p class="text-secondary small mb-3">Hak suara Anda telah berhasil disimpan dan disegel secara anonim dalam ledger digital.</p>

            <div class="text-start bg-light p-3 rounded-3 border mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted small fw-semibold">Kode Tanda Terima Suara (Audit Token):</span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-size: 0.7rem;">
                        <i class="fas fa-check-circle me-1"></i> Asas Rahasia
                    </span>
                </div>
                <div class="receipt-code-box my-1 py-2 fs-6 text-center">
                    <?= htmlspecialchars($receipt_token); ?>
                </div>
                <small class="text-muted d-block" style="font-size: 0.76rem; line-height: 1.4;">
                    <i class="fas fa-shield-alt text-success me-1"></i>
                    Simpan atau catat token ini. Kode acak kriptografis ini dapat digunakan untuk memverifikasi bahwa suara Anda telah dihitung pada rekapitulasi tanpa mengungkap kandidat yang Anda pilih.
                </small>
            </div>

            <div class="countdown-progress">
                <div class="countdown-progress-bar" id="progressBar"></div>
            </div>

            <p class="text-muted small mb-3">
                Bilik suara akan kembali ke layar awal dalam <strong id="countdown" class="text-dark">8</strong> detik...
            </p>

            <a href="<?= base_url('voting'); ?>" class="btn btn-kop-primary w-100 py-2 fw-semibold" id="btnFinish">
                <i class="fas fa-check-circle me-1"></i> Selesai &amp; Kembali ke Layar Awal
            </a>
        </div>
    </div>

    <!-- Offline Three.js Script for 3D Ballot Box Animation -->
    <script>
    (function() {
        const container = document.getElementById('threeCanvasWrap');
        const statusPill = document.getElementById('ballotStatusPill');

        if (!window.THREE || !container) {
            if (statusPill) statusPill.innerHTML = '<i class="fas fa-check-circle text-success"></i> Suara Sah Terkunci';
            return;
        }

        let scene, camera, renderer, animationFrameId;
        let boxGroup, ballotMesh, lockMesh, particles;
        let width = container.clientWidth || 500;
        let height = container.clientHeight || 250;

        // Interaction state
        let isDragging = false;
        let previousMouseX = 0;
        let userRotationY = 0;

        // Sequence timing state
        let startTime = performance.now();
        let phase = 0; // 0: enter, 1: dropping, 2: locked

        function initScene() {
            scene = new THREE.Scene();

            camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 100);
            camera.position.set(0, 2.2, 5.2);
            camera.lookAt(0, 0.2, 0);

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(width, height);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1.05;
            container.appendChild(renderer.domElement);

            // Lighting
            const ambLight = new THREE.AmbientLight(0xffffff, 0.75);
            scene.add(ambLight);

            const keyLight = new THREE.DirectionalLight(0xffffff, 0.9);
            keyLight.position.set(4, 7, 5);
            scene.add(keyLight);

            const emeraldFill = new THREE.PointLight(0x059669, 1.4, 8);
            emeraldFill.position.set(0, 0.4, 2.0);
            scene.add(emeraldFill);

            const topLight = new THREE.SpotLight(0xfef08a, 1.2, 8, Math.PI / 4, 0.4);
            topLight.position.set(0, 4.5, 0.5);
            scene.add(topLight);

            // 1. Box Group
            boxGroup = new THREE.Group();
            boxGroup.position.set(0, -0.15, 0);
            scene.add(boxGroup);

            // 1.1 Outer Transparent Frosted Box
            const boxGeo = new THREE.BoxGeometry(2.3, 1.5, 1.7);
            const boxMat = new THREE.MeshPhysicalMaterial({
                color: 0xffffff,
                transparent: true,
                opacity: 0.48,
                roughness: 0.15,
                metalness: 0.05,
                transmission: 0.4,
                depthWrite: false
            });
            const boxMesh = new THREE.Mesh(boxGeo, boxMat);
            boxGroup.add(boxMesh);

            // 1.2 Elegant Box Edges Wireframe
            const edgeGeo = new THREE.EdgesGeometry(boxGeo);
            const edgeMat = new THREE.LineBasicMaterial({
                color: 0x059669,
                transparent: true,
                opacity: 0.45,
                linewidth: 1
            });
            const edgeLines = new THREE.LineSegments(edgeGeo, edgeMat);
            boxGroup.add(edgeLines);

            // 1.3 Bottom Plate inside Box
            const baseGeo = new THREE.BoxGeometry(2.2, 0.06, 1.6);
            const baseMat = new THREE.MeshStandardMaterial({
                color: 0x0f172a,
                roughness: 0.3,
                metalness: 0.7
            });
            const baseMesh = new THREE.Mesh(baseGeo, baseMat);
            baseMesh.position.y = -0.72;
            boxGroup.add(baseMesh);

            // 1.4 Top Lid with Slot Opening
            const lidMat = new THREE.MeshStandardMaterial({
                color: 0x1e293b,
                roughness: 0.25,
                metalness: 0.6
            });
            // Left & right wings leaving central slot
            const lidWingGeo = new THREE.BoxGeometry(0.75, 0.05, 1.62);
            const lidLeft = new THREE.Mesh(lidWingGeo, lidMat);
            lidLeft.position.set(-0.72, 0.73, 0);
            boxGroup.add(lidLeft);

            const lidRight = new THREE.Mesh(lidWingGeo, lidMat);
            lidRight.position.set(0.72, 0.73, 0);
            boxGroup.add(lidRight);

            // Front & back margins of the slot
            const lidRimGeo = new THREE.BoxGeometry(0.8, 0.05, 0.65);
            const lidFront = new THREE.Mesh(lidRimGeo, lidMat);
            lidFront.position.set(0, 0.73, 0.48);
            boxGroup.add(lidFront);

            const lidBack = new THREE.Mesh(lidRimGeo, lidMat);
            lidBack.position.set(0, 0.73, -0.48);
            boxGroup.add(lidBack);

            // Slot Gold Lip Trim
            const slotTrimGeo = new THREE.BoxGeometry(0.74, 0.06, 0.16);
            const slotTrimMat = new THREE.MeshStandardMaterial({
                color: 0xf59e0b,
                metalness: 0.85,
                roughness: 0.2
            });
            const slotTrim = new THREE.Mesh(slotTrimGeo, slotTrimMat);
            slotTrim.position.set(0, 0.735, 0);
            boxGroup.add(slotTrim);

            // 1.5 Cooperative Emblem on Front of Box
            const canvasEmblem = document.createElement('canvas');
            canvasEmblem.width = 256;
            canvasEmblem.height = 256;
            const ctxE = canvasEmblem.getContext('2d');
            ctxE.fillStyle = '#065f46';
            ctxE.beginPath();
            ctxE.arc(128, 128, 115, 0, Math.PI * 2);
            ctxE.fill();
            ctxE.lineWidth = 10;
            ctxE.strokeStyle = '#f59e0b';
            ctxE.stroke();
            ctxE.fillStyle = '#ffffff';
            ctxE.font = 'bold 36px sans-serif';
            ctxE.textAlign = 'center';
            ctxE.fillText('KOPUS', 128, 120);
            ctxE.font = 'bold 22px sans-serif';
            ctxE.fillStyle = '#34d399';
            ctxE.fillText('E-VOTING', 128, 155);

            const emblemTex = new THREE.CanvasTexture(canvasEmblem);
            const emblemGeo = new THREE.PlaneGeometry(0.76, 0.76);
            const emblemMat = new THREE.MeshStandardMaterial({
                map: emblemTex,
                transparent: true,
                roughness: 0.3,
                metalness: 0.2
            });
            const emblemMesh = new THREE.Mesh(emblemGeo, emblemMat);
            emblemMesh.position.set(0, 0.05, 0.865);
            boxGroup.add(emblemMesh);

            // 2. Digital Ballot Paper
            const canvasBallot = document.createElement('canvas');
            canvasBallot.width = 512;
            canvasBallot.height = 360;
            const ctxB = canvasBallot.getContext('2d');
            ctxB.fillStyle = '#ffffff';
            ctxB.fillRect(0, 0, 512, 360);
            ctxB.fillStyle = '#059669';
            ctxB.fillRect(0, 0, 512, 60);
            ctxB.fillStyle = '#ffffff';
            ctxB.font = 'bold 24px sans-serif';
            ctxB.fillText('SURAT SUARA ELEKTRONIK', 24, 40);
            // Rows for selections
            ctxB.fillStyle = '#ecfdf5';
            ctxB.fillRect(24, 80, 464, 110);
            ctxB.strokeStyle = '#a7f3d0';
            ctxB.strokeRect(24, 80, 464, 110);
            ctxB.fillStyle = '#065f46';
            ctxB.font = 'bold 20px sans-serif';
            ctxB.fillText('PILIHAN KETUA: TERVERIFIKASI [✓]', 44, 130);
            ctxB.fillStyle = '#eff6ff';
            ctxB.fillRect(24, 210, 464, 110);
            ctxB.strokeStyle = '#bfdbfe';
            ctxB.strokeRect(24, 210, 464, 110);
            ctxB.fillStyle = '#1e40af';
            ctxB.fillText('PILIHAN PENGAWAS: TERVERIFIKASI [✓]', 44, 260);

            const ballotTex = new THREE.CanvasTexture(canvasBallot);
            const ballotGeo = new THREE.BoxGeometry(0.68, 0.015, 0.48);
            const ballotMat = new THREE.MeshStandardMaterial({
                map: ballotTex,
                roughness: 0.4,
                metalness: 0.1
            });
            ballotMesh = new THREE.Mesh(ballotGeo, ballotMat);
            // Start position high above the slot
            ballotMesh.position.set(0, 2.3, 0.1);
            ballotMesh.rotation.set(-0.25, 0.1, -0.05);
            scene.add(ballotMesh);

            // 3. 3D Digital Shield / Lock Bar over Slot
            const lockGeo = new THREE.BoxGeometry(0.72, 0.08, 0.14);
            const lockMat = new THREE.MeshStandardMaterial({
                color: 0x059669,
                metalness: 0.85,
                roughness: 0.2
            });
            lockMesh = new THREE.Mesh(lockGeo, lockMat);
            lockMesh.position.set(0, 0.77, 0);
            lockMesh.scale.set(0.001, 0.001, 0.001); // Hidden initially
            boxGroup.add(lockMesh);

            // 4. Sparkle Burst Particle System
            const particleCount = 45;
            const pGeo = new THREE.BufferGeometry();
            const pPositions = new Float32Array(particleCount * 3);
            const pColors = new Float32Array(particleCount * 3);
            for (let i = 0; i < particleCount; i++) {
                pPositions[i * 3] = (Math.random() - 0.5) * 0.4;
                pPositions[i * 3 + 1] = 0.75;
                pPositions[i * 3 + 2] = (Math.random() - 0.5) * 0.15;

                const isGold = Math.random() > 0.5;
                pColors[i * 3] = isGold ? 0.96 : 0.02;
                pColors[i * 3 + 1] = isGold ? 0.62 : 0.72;
                pColors[i * 3 + 2] = isGold ? 0.07 : 0.41;
            }
            pGeo.setAttribute('position', new THREE.BufferAttribute(pPositions, 3));
            pGeo.setAttribute('color', new THREE.BufferAttribute(pColors, 3));

            const pMat = new THREE.PointsMaterial({
                size: 0.075,
                vertexColors: true,
                transparent: true,
                opacity: 0,
                blending: THREE.AdditiveBlending
            });
            particles = new THREE.Points(pGeo, pMat);
            boxGroup.add(particles);

            // Bind Drag Interaction
            setupInteraction();

            // Window resize
            window.addEventListener('resize', onResize);

            // Start animation loop
            animate();
        }

        function setupInteraction() {
            const el = container;
            const onDown = (clientX) => {
                isDragging = true;
                previousMouseX = clientX;
            };
            const onMove = (clientX) => {
                if (!isDragging) return;
                const delta = clientX - previousMouseX;
                previousMouseX = clientX;
                userRotationY += delta * 0.008;
            };
            const onUp = () => { isDragging = false; };

            el.addEventListener('mousedown', (e) => onDown(e.clientX));
            window.addEventListener('mousemove', (e) => onMove(e.clientX));
            window.addEventListener('mouseup', onUp);

            el.addEventListener('touchstart', (e) => {
                if (e.touches.length === 1) onDown(e.touches[0].clientX);
            }, { passive: true });
            window.addEventListener('touchmove', (e) => {
                if (e.touches.length === 1) onMove(e.touches[0].clientX);
            }, { passive: true });
            window.addEventListener('touchend', onUp);
        }

        function onResize() {
            if (!container || !renderer || !camera) return;
            width = container.clientWidth;
            height = container.clientHeight;
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            renderer.setSize(width, height);
        }

        function animate() {
            animationFrameId = requestAnimationFrame(animate);

            const elapsed = (performance.now() - startTime) / 1000;

            // Phase 0: 0.0s - 0.7s (Hover and align)
            if (elapsed < 0.7) {
                const p = elapsed / 0.7;
                ballotMesh.position.y = 2.3 - p * 0.4;
                ballotMesh.rotation.x = -0.25 * (1 - p);
                ballotMesh.rotation.y = 0.1 * (1 - p);
            }
            // Phase 1: 0.7s - 1.9s (Slip through slot into box)
            else if (elapsed < 1.9) {
                if (phase === 0) phase = 1;
                const p = (elapsed - 0.7) / 1.2;
                const ease = p < 0.5 ? 2 * p * p : -1 + (4 - 2 * p) * p;
                ballotMesh.position.y = 1.9 - ease * 2.5;
                ballotMesh.rotation.x = -0.05 + ease * 1.45;
                ballotMesh.rotation.z = ease * 0.2;
                ballotMesh.position.z = 0.1 * (1 - ease);
                ballotMesh.scale.set(1 - ease * 0.15, 1 - ease * 0.15, 1 - ease * 0.15);
            }
            // Phase 2: 1.9s - 2.5s (Drop complete, Lock engaged & Sparkle burst)
            else if (elapsed < 2.5) {
                if (phase === 1) {
                    phase = 2;
                    if (ballotMesh.parent !== boxGroup) {
                        scene.remove(ballotMesh);
                        boxGroup.add(ballotMesh);
                        ballotMesh.position.set(0.08, -0.62, 0.05);
                        ballotMesh.rotation.set(1.4, 0.2, 0.15);
                        ballotMesh.scale.set(0.85, 0.85, 0.85);
                    }
                    if (statusPill) {
                        statusPill.innerHTML = '<i class="fas fa-lock text-success me-1"></i> <strong>Suara Sah Tersegel &amp; Terkunci</strong>';
                        statusPill.style.borderColor = '#10b981';
                        statusPill.style.backgroundColor = '#ecfdf5';
                    }
                }
                const p = (elapsed - 1.9) / 0.6;
                // Lock pops in
                const lockScale = Math.min(1.0, p * 1.25);
                lockMesh.scale.set(lockScale, lockScale, lockScale);

                // Sparkle particles burst upwards
                particles.material.opacity = Math.max(0, 1 - p);
                const pos = particles.geometry.attributes.position.array;
                for (let i = 0; i < pos.length; i += 3) {
                    pos[i + 1] += 0.015; // float up
                    pos[i] += (Math.random() - 0.5) * 0.005;
                }
                particles.geometry.attributes.position.needsUpdate = true;
            } else {
                // Post-lock idle state
                lockMesh.scale.set(1, 1, 1);
                particles.material.opacity = 0;
            }

            // Continuous gentle idle orbit rotation (plus user drag interaction)
            if (!isDragging) {
                userRotationY *= 0.95; // smoothly decay manual momentum
            }
            const idleAngle = Math.sin(elapsed * 0.6) * 0.22;
            boxGroup.rotation.y = idleAngle + userRotationY;
            boxGroup.rotation.x = Math.sin(elapsed * 0.4) * 0.04;

            renderer.render(scene, camera);
        }

        // Clean up on unload
        window.addEventListener('beforeunload', function() {
            if (animationFrameId) cancelAnimationFrame(animationFrameId);
        });

        // Initialize 3D scene
        initScene();
    })();

    // 8-second countdown with visual progress bar
    (function() {
        let totalTime = 8;
        let timeLeft = totalTime;
        const countEl = document.getElementById('countdown');
        const progressBar = document.getElementById('progressBar');

        const interval = setInterval(function() {
            timeLeft--;
            if (countEl) countEl.innerText = timeLeft;
            if (progressBar) {
                const percent = Math.max(0, (timeLeft / totalTime) * 100);
                progressBar.style.width = percent + '%';
            }
            if (timeLeft <= 0) {
                clearInterval(interval);
                window.location.href = '<?= base_url("voting"); ?>';
            }
        }, 1000);
    })();
    </script>
</body>
</html>
