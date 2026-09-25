<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$all_candidates = $all_candidates ?? [];
$ketua_candidates = $ketua_candidates ?? [];
$pengawas_candidates = $pengawas_candidates ?? [];

// Fallback in case candidates were not passed
if (empty($all_candidates) && !empty($ketua_candidates)) {
    $all_candidates = array_merge($ketua_candidates, $pengawas_candidates);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk E-Voting - <?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi UBS'); ?></title>
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

    <div class="kiosk-wrapper">
        <!-- Top Navigation Bar / Kiosk Header -->
        <header class="kop-navbar">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <a href="<?= base_url(); ?>" class="kop-brand">
                    <i class="fas fa-landmark text-success"></i>
                    <span><?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi UBS'); ?></span>
                    <span class="kop-brand-badge">KIOSK E-VOTING</span>
                </a>
                
                <div class="d-none d-md-flex align-items-center">
                    <span class="kiosk-clock-badge" id="kioskClock" title="Waktu Kiosk Server">
                        <i class="far fa-clock text-success"></i>
                        <span id="kioskClockText">Memuat Waktu...</span>
                    </span>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <span class="badge <?= ($settings['election_status'] === 'open') ? 'bg-success' : 'bg-warning text-dark'; ?> px-3 py-2">
                        <?= ($settings['election_status'] === 'open') ? 'Bilik Suara Terbuka' : 'Sesi Dijeda'; ?>
                    </span>
                    <a href="<?= base_url('admin/login'); ?>" class="btn btn-outline-secondary btn-sm" title="Akses Panitia Pemilihan">
                        <i class="fas fa-lock"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Kiosk Body -->
        <main class="kiosk-main">
            <!-- Hero Header -->
            <div class="kiosk-hero-header">
                <h1 class="kiosk-hero-title"><?= htmlspecialchars($settings['election_title'] ?? 'Pemilihan Pengurus & Pengawas Koperasi'); ?></h1>
                <p class="kiosk-hero-subtitle">
                    Katalog Calon Pemimpin Koperasi. Sentuh, geser atau putar roda kartu seperti gasing untuk menelusuri profil serta cuplikan visi &amp; misi para kandidat.
                </p>
            </div>

            <!-- Toolbar: Filter Categories & Roulette Physics Controls -->
            <div class="kiosk-toolbar">
                <div class="kiosk-filter-group" role="tablist" aria-label="Filter Kategori Kandidat">
                    <button type="button" class="kiosk-filter-btn active" data-filter="all">
                        <i class="fas fa-users me-1"></i> Semua Calon (<?= count($all_candidates); ?>)
                    </button>
                    <button type="button" class="kiosk-filter-btn filter-ketua" data-filter="ketua">
                        <i class="fas fa-user-tie me-1"></i> Calon Ketua (<?= count($ketua_candidates); ?>)
                    </button>
                    <button type="button" class="kiosk-filter-btn filter-pengawas" data-filter="pengawas">
                        <i class="fas fa-shield-alt me-1"></i> Calon Pengawas (<?= count($pengawas_candidates); ?>)
                    </button>
                </div>

                <div class="kiosk-controls-group">
                    <button type="button" class="kiosk-btn-nav" id="btnPrevCard" title="Geser ke kiri" aria-label="Geser ke kiri">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="kiosk-btn-spin" id="btnSpinTop" title="Putar roda kandidat layaknya gasing">
                        <i class="fas fa-sync-alt me-1"></i> Putar Gasing
                    </button>
                    <button type="button" class="kiosk-btn-nav" id="btnNextCard" title="Geser ke kanan" aria-label="Geser ke kanan">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <!-- 3D Roulette Stage (Muter Gasing Turntable Cylinder) -->
            <div class="kiosk-roulette-viewport" id="rouletteStage" title="Tarik atau geser ke samping untuk memutar seperti gasing">
                <div class="kiosk-roulette-cylinder" id="rouletteCylinder">
                    <?php 
                    $idx = 0;
                    foreach ($all_candidates as $c): 
                        $is_ketua = ($c['category'] === 'ketua');
                        $cat_label = $is_ketua ? 'Calon Ketua' : 'Calon Pengawas';
                        $cat_class = $is_ketua ? 'cat-ketua' : 'cat-pengawas';
                        $cat_icon = $is_ketua ? 'fa-user-tie' : 'fa-shield-alt';
                        $theme_class = $is_ketua ? 'card-theme-ketua' : 'card-theme-pengawas';

                        // Visi Cuplikan
                        $v_raw = trim($c['vision'] ?? '');
                        if (empty($v_raw) || strtolower($v_raw) === 'visi') {
                            $v_raw = 'Mewujudkan koperasi yang transparan, modern, dan mensejahterakan seluruh anggota.';
                        }
                        $v_snippet = (mb_strlen($v_raw) > 105) ? mb_substr($v_raw, 0, 102) . '...' : $v_raw;

                        // Misi Cuplikan (ambil 2 poin pertama)
                        $m_lines = preg_split('/\r\n|\r|\n/', trim($c['mission'] ?? ''));
                        $m_clean = [];
                        foreach ($m_lines as $line) {
                            $t = trim($line);
                            if (!empty($t)) {
                                $m_clean[] = $t;
                            }
                        }
                        if (empty($m_clean)) {
                            $m_clean = ['1. Peningkatan layanan simpan pinjam', '2. Pelatihan usaha anggota'];
                        }
                        $preview_missions = array_slice($m_clean, 0, 2);
                    ?>
                    <div class="kiosk-cand-card <?= $theme_class; ?>" 
                         data-index="<?= $idx; ?>" 
                         data-id="<?= $c['id']; ?>"
                         data-cat="<?= $c['category']; ?>"
                         data-name="<?= htmlspecialchars($c['name']); ?>"
                         data-num="<?= $c['candidate_number']; ?>"
                         data-photo="<?= base_url($c['photo']); ?>"
                         data-vision="<?= htmlspecialchars($v_raw); ?>"
                         data-mission="<?= htmlspecialchars($c['mission']); ?>">
                        
                        <!-- Top Accent Ribbon (Color Indicator) -->
                        <div class="kiosk-card-top-bar <?= $is_ketua ? 'bar-ketua' : 'bar-pengawas'; ?>"></div>

                        <!-- Left Column: Category Badge, Photo, Number -->
                        <div class="kiosk-cand-left">
                            <span class="kiosk-cand-badge-cat <?= $cat_class; ?> mb-1">
                                <i class="fas <?= $cat_icon; ?> me-1"></i> <?= $cat_label; ?>
                            </span>

                            <div class="kiosk-cand-photo-wrap">
                                <img src="<?= base_url($c['photo']); ?>" alt="<?= htmlspecialchars($c['name']); ?>" class="kiosk-cand-photo" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                            </div>

                            <span class="kiosk-cand-badge-num">NO. <?= sprintf('%02d', $c['candidate_number']); ?></span>
                        </div>

                        <!-- Right Column: Name, Vision, Mission, Actions -->
                        <div class="kiosk-cand-right">
                            <div>
                                <div class="kiosk-cand-name" title="<?= htmlspecialchars($c['name']); ?>"><?= htmlspecialchars($c['name']); ?></div>

                                <div class="kiosk-cand-vision-box">
                                    <div class="kiosk-cand-vision-label">
                                        <i class="fas fa-bullseye"></i> Visi Kandidat
                                    </div>
                                    &ldquo;<?= htmlspecialchars($v_snippet); ?>&rdquo;
                                </div>

                                <ul class="kiosk-cand-mission-list">
                                    <?php foreach ($preview_missions as $pm): 
                                        $pm_text = (mb_strlen($pm) > 65) ? mb_substr($pm, 0, 62) . '...' : $pm;
                                    ?>
                                    <li>
                                        <i class="fas fa-check-circle <?= $is_ketua ? 'text-success' : 'text-primary'; ?>"></i>
                                        <span><?= htmlspecialchars($pm_text); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="kiosk-cand-actions">
                                <button type="button" class="kiosk-btn-detail btn-open-detail" 
                                        data-name="<?= htmlspecialchars($c['name']); ?>"
                                        data-num="<?= $c['candidate_number']; ?>"
                                        data-cat="<?= $cat_label; ?>"
                                        data-is-ketua="<?= $is_ketua ? '1' : '0'; ?>"
                                        data-photo="<?= base_url($c['photo']); ?>"
                                        data-vision="<?= htmlspecialchars($v_raw); ?>"
                                        data-mission="<?= htmlspecialchars($c['mission']); ?>">
                                    <i class="fas fa-file-alt"></i> Detail Visi &amp; Misi
                                </button>
                                <p class="kiosk-cand-prompt">
                                    <i class="fas fa-info-circle me-1 <?= $is_ketua ? 'text-success' : 'text-primary'; ?>"></i>Pilih di bilik suara
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $idx++;
                    endforeach; 
                    ?>
                </div>
            </div>

            <!-- Dots Indicator for Active Candidate -->
            <div class="kiosk-dots-wrap" id="rouletteDots"></div>

            <!-- Integrated RFID Hardware Reader Terminal (Kiosk Dock) -->
            <div class="kiosk-rfid-terminal">
                <!-- 3D Interactive Smartcard (Three.js WebGL) -->
                <div class="kiosk-rfid-card-stage" id="rfidStage" title="Sentuh atau arahkan kursor untuk interaksi kartu">
                    <div id="threeCardWrap" style="width: 100%; height: 100%;"></div>
                </div>

                <!-- Reader Instructions & Live Status -->
                <div class="kiosk-rfid-info">
                    <div class="kiosk-rfid-title">
                        <i class="fas fa-id-card text-success"></i>
                        <span>AREA PEMINDAIAN KARTU RFID ANGGOTA</span>
                    </div>
                    <div class="kiosk-rfid-instruction">
                        Silakan tempelkan kartu RFID anggota Anda pada pemindai reader untuk memverifikasi hak suara dan membuka bilik suara digital.
                    </div>
                    
                    <div class="kiosk-rfid-badges">
                        <div class="scanner-status m-0">
                            <span class="status-dot"></span>
                            <span id="statusText">Sensor RFID Siap Menerima Kartu</span>
                        </div>

                        <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill">
                            <i class="fas fa-shield-alt text-success me-1"></i> Asas Luber Jurdil &bull; Enkripsi Kriptografis
                        </span>

                        <?php if (ENVIRONMENT !== 'production' || in_array($this->input->ip_address(), array('127.0.0.1', '::1'), true)): ?>
                        <a href="<?= base_url('voting/dev_booth'); ?>" class="btn btn-sm btn-outline-secondary py-1" style="font-size: 0.78rem;">
                            <i class="fas fa-terminal text-success me-1"></i> Mode Dev: Masuk Bilik Uji Coba (Tanpa RFID)
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hidden RFID Wedge Input Form (Permanent Focus) -->
                <form id="rfidForm" class="visually-hidden">
                    <input type="password" id="rfidUid" name="rfid_uid" autocomplete="off" autofocus>
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                </form>
            </div>
        </main>
    </div>

    <!-- Candidate Detail Modal (Visi & Misi Lengkap) -->
    <div class="modal fade" id="modalCandidateDetail" tabindex="-1" aria-labelledby="modalCandLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success" id="modalCatBadge">Calon Ketua</span>
                        <h5 class="modal-title fw-bold text-dark m-0" id="modalCandName">Nama Calon</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4 align-items-start">
                        <div class="col-md-4 text-center border-end-md pb-3 pb-md-0">
                            <div class="kiosk-cand-photo-wrap mx-auto mb-3" style="width: 120px; height: 120px;">
                                <img src="" alt="" id="modalCandPhoto" class="kiosk-cand-photo" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                            </div>
                            <div class="fw-bold text-dark fs-5 mb-1" id="modalCandNameSub"></div>
                            <div class="badge bg-light text-secondary border px-3 py-1 rounded-pill mb-2" id="modalCandNumLabel"></div>
                            <div class="text-muted small">
                                <i class="fas fa-check-circle text-success me-1"></i> Terdaftar Resmi di DPT Koperasi
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="modal-vision-card mb-3" id="modalVisionCard">
                                <div class="modal-section-title text-success mb-2" id="modalVisionTitle" style="font-weight: 700; font-size: 0.95rem;">
                                    <i class="fas fa-bullseye me-1"></i> Visi Utama Calon:
                                </div>
                                <div id="modalCandVision" class="modal-vision-text" style="font-size: 0.92rem; line-height: 1.5; color: #334155;"></div>
                            </div>
                            
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="modal-section-title text-primary m-0" id="modalMissionTitle" style="font-weight: 700; font-size: 0.95rem;">
                                        <i class="fas fa-list-check me-1"></i> Program Kerja &amp; Misi:
                                    </div>
                                    <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small" id="modalMissionCount"></span>
                                </div>
                                <div id="modalCandMissionContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-id-card text-success me-1"></i> Tempelkan kartu RFID anggota untuk memberikan suara
                    </span>
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Web Accessibility Widget (Fitur Aksesibilitas) -->
    <?php $this->load->view('voting/accessibility_widget', ['page' => 'scanner']); ?>

    <!-- Core Kiosk Script: Clock, 3D Roulette Gasing Engine, Web Audio, Three.js Card & RFID Scanner -->
    <script>
    (function() {
        // --- 1. Real-Time WIB Digital Clock ---
        const clockEl = document.getElementById('kioskClockText');
        function updateClock() {
            if (!clockEl) return;
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const dayName = days[now.getDay()];
            const dayNum = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const mins = String(now.getMinutes()).padStart(2, '0');
            const secs = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = dayName + ', ' + dayNum + ' ' + monthName + ' ' + year + ' • ' + hours + ':' + mins + ':' + secs + ' WIB';
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- 2. Offline Web Audio Synthesizer (Zero External Audio Files) ---
        let audioCtx = null;
        function getAudioContext() {
            if (!audioCtx && (window.AudioContext || window.webkitAudioContext)) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            return audioCtx;
        }

        function playTickSound() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(820, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(320, ctx.currentTime + 0.04);
                gain.gain.setValueAtTime(0.04, ctx.currentTime);
                gain.gain.linearRampToValueAtTime(0.001, ctx.currentTime + 0.04);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.045);
            } catch (e) {}
        }

        function playSuccessChime() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;
                // Two-tone cheerful chime (C5 -> E5)
                [523.25, 659.25].forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, now + i * 0.12);
                    gain.gain.setValueAtTime(0.12, now + i * 0.12);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + i * 0.12 + 0.28);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(now + i * 0.12);
                    osc.stop(now + i * 0.12 + 0.3);
                });
            } catch (e) {}
        }

        // --- 3. 3D Roulette "Muter Gasing" Physics Engine ---
        const stage = document.getElementById('rouletteStage');
        const cylinder = document.getElementById('rouletteCylinder');
        const dotsWrap = document.getElementById('rouletteDots');
        const allCards = Array.from(document.querySelectorAll('.kiosk-cand-card'));

        let activeCards = [...allCards];
        let currentFilter = 'all';
        let currentAngle = 0;
        let targetAngle = 0;
        let velocity = 0;
        let isDragging = false;
        let isSpinningGasing = false;
        let lastX = 0;
        let startX = 0;
        let hasMoved = false;
        let lastTime = 0;
        let cylinderRadius = 350;
        let activeIndex = 0;
        let lastTickIndex = -1;
        let idleTimer = null;
        let isIdleRotating = false;

        function calcRadius(count) {
            const cardWidth = 510;
            if (count <= 2) return 320;
            if (count === 3) return 360;
            if (count === 4) return 400;
            if (count === 5) return 440;
            return Math.max(420, Math.round((cardWidth / 2) / Math.tan(Math.PI / count)) + 25);
        }

        function rebuildRouletteLayout() {
            const count = activeCards.length;
            if (count === 0) {
                dotsWrap.innerHTML = '<span class="text-muted small">Tidak ada calon dalam kategori ini.</span>';
                return;
            }

            cylinderRadius = calcRadius(count);
            const angleStep = 360 / count;

            activeCards.forEach((card, i) => {
                const cardAngle = i * angleStep;
                card.dataset.angle = cardAngle;
                card.dataset.posIndex = i;
                card.style.transform = 'rotateY(' + cardAngle + 'deg) translateZ(' + cylinderRadius + 'px)';
                card.style.display = 'flex';
            });

            // Rebuild Dots
            dotsWrap.innerHTML = '';
            for (let i = 0; i < count; i++) {
                const dot = document.createElement('div');
                dot.className = 'kiosk-dot-item' + (i === 0 ? ' active' : '');
                dot.dataset.targetIndex = i;
                dot.title = 'Lihat Kandidat ' + (i + 1);
                dot.addEventListener('click', function() {
                    rotateToIndex(i);
                });
                dotsWrap.appendChild(dot);
            }

            currentAngle = 0;
            targetAngle = 0;
            velocity = 0;
            isSpinningGasing = false;
            updateRouletteTransform();
        }

        function updateRouletteTransform() {
            cylinder.style.transform = 'translateZ(-' + cylinderRadius + 'px) rotateY(' + currentAngle + 'deg)';

            const count = activeCards.length;
            if (count === 0) return;

            const angleStep = 360 / count;
            // Find which card is frontmost (nearest to angle 0 modulo 360)
            let normAngle = ((-currentAngle % 360) + 360) % 360;
            let closestIdx = Math.round(normAngle / angleStep) % count;

            if (closestIdx !== lastTickIndex) {
                if (lastTickIndex !== -1 && (Math.abs(velocity) > 0.3 || isDragging)) {
                    playTickSound();
                }
                lastTickIndex = closestIdx;
            }

            activeIndex = closestIdx;

            // Update card appearance & active dot
            activeCards.forEach((card, i) => {
                const baseAngle = i * angleStep;
                // Delta angle from front (0 deg)
                let diff = ((baseAngle + currentAngle) % 360 + 540) % 360 - 180;
                let absDiff = Math.abs(diff);

                if (absDiff < angleStep * 0.48) {
                    card.classList.add('is-active-front');
                    card.style.opacity = '1';
                    card.style.zIndex = '20';
                    card.style.pointerEvents = 'auto';
                } else if (absDiff < 105) {
                    card.classList.remove('is-active-front');
                    card.style.opacity = '0.82';
                    card.style.zIndex = '5';
                    card.style.pointerEvents = 'auto';
                } else {
                    card.classList.remove('is-active-front');
                    card.style.opacity = '0.35';
                    card.style.zIndex = '1';
                    card.style.pointerEvents = 'none';
                }
            });

            // Update dots
            const dots = dotsWrap.querySelectorAll('.kiosk-dot-item');
            dots.forEach((d, idx) => {
                if (idx === activeIndex) {
                    d.classList.add('active');
                } else {
                    d.classList.remove('active');
                }
            });
        }

        function rotateToIndex(index) {
            const count = activeCards.length;
            if (count === 0) return;
            const angleStep = 360 / count;
            
            // Normalize currentAngle to nearest multiple
            let currentStep = -currentAngle / angleStep;
            let diff = index - (currentStep % count);
            if (diff > count / 2) diff -= count;
            if (diff < -count / 2) diff += count;

            targetAngle = currentAngle - (diff * angleStep);
            isSpinningGasing = true;
            resetIdleTimer();
        }

        // --- Gasing Drag & Physics Loop ---
        function onPointerDown(e) {
            getAudioContext();
            isDragging = true;
            isSpinningGasing = false;
            isIdleRotating = false;
            hasMoved = false;
            stage.classList.add('is-dragging');
            lastX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
            startX = lastX;
            lastTime = performance.now();
            velocity = 0;
            resetIdleTimer();
        }

        function onPointerMove(e) {
            if (!isDragging) return;
            const x = e.clientX || (e.touches && e.touches[0].clientX) || 0;
            if (Math.abs(x - startX) > 6) {
                hasMoved = true;
            }
            const now = performance.now();
            const deltaX = x - lastX;
            const deltaTime = Math.max(now - lastTime, 16);

            // Directly rotate cylinder with pointer
            const dragFactor = 0.38;
            currentAngle += deltaX * dragFactor;

            // Velocity (deg per 16ms frame)
            velocity = (deltaX / deltaTime) * 16 * dragFactor;

            lastX = x;
            lastTime = now;

            updateRouletteTransform();
            resetIdleTimer();
        }

        function onPointerUp() {
            if (!isDragging) return;
            isDragging = false;
            stage.classList.remove('is-dragging');

            // If flicked with velocity, enter Gasing Spin Mode!
            if (Math.abs(velocity) > 0.45) {
                isSpinningGasing = true;
            } else {
                // Snap to nearest card
                snapToNearestCard();
            }
            resetIdleTimer();
        }

        function snapToNearestCard() {
            const count = activeCards.length;
            if (count === 0) return;
            const angleStep = 360 / count;
            const snappedStep = Math.round(-currentAngle / angleStep);
            targetAngle = -snappedStep * angleStep;
            isSpinningGasing = true;
        }

        // Spin Button Action (Muter Gasing Rapid Burst)
        function triggerGasingSpin() {
            getAudioContext();
            const count = activeCards.length;
            if (count === 0) return;
            
            // Random direction and high velocity burst
            const dir = Math.random() > 0.5 ? 1 : -1;
            velocity = dir * (17 + Math.random() * 8);
            isSpinningGasing = true;
            isIdleRotating = false;
            resetIdleTimer();
        }

        // Idle Auto-attract for retail kiosk
        function resetIdleTimer() {
            if (idleTimer) clearTimeout(idleTimer);
            idleTimer = setTimeout(() => {
                isIdleRotating = true;
            }, 10000);
        }

        // Physics Animation Loop
        function animateRoulettePhysics() {
            requestAnimationFrame(animateRoulettePhysics);

            if (isDragging) return;

            if (isIdleRotating) {
                currentAngle -= 0.06;
                updateRouletteTransform();
                return;
            }

            if (isSpinningGasing) {
                if (Math.abs(velocity) > 0.18) {
                    currentAngle += velocity;
                    velocity *= 0.958; // Air friction damping
                    updateRouletteTransform();
                } else {
                    velocity = 0;
                    // Elastic smooth snap to target
                    const diff = targetAngle - currentAngle;
                    if (Math.abs(diff) > 0.2) {
                        currentAngle += diff * 0.12;
                        updateRouletteTransform();
                    } else {
                        currentAngle = targetAngle;
                        isSpinningGasing = false;
                        updateRouletteTransform();
                    }
                }
            }
        }
        animateRoulettePhysics();
        resetIdleTimer();

        // Stage Pointer & Touch Listeners
        stage.addEventListener('mousedown', onPointerDown);
        window.addEventListener('mousemove', onPointerMove);
        window.addEventListener('mouseup', onPointerUp);

        stage.addEventListener('touchstart', onPointerDown, { passive: true });
        window.addEventListener('touchmove', onPointerMove, { passive: true });
        window.addEventListener('touchend', onPointerUp);
        window.addEventListener('touchcancel', onPointerUp);

        // Controls Buttons
        const btnPrev = document.getElementById('btnPrevCard');
        const btnNext = document.getElementById('btnNextCard');
        const btnSpin = document.getElementById('btnSpinTop');

        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                getAudioContext();
                const count = activeCards.length;
                if (count === 0) return;
                const angleStep = 360 / count;
                targetAngle = Math.round((currentAngle + angleStep) / angleStep) * angleStep;
                isSpinningGasing = true;
                resetIdleTimer();
            });
        }

        if (btnNext) {
            btnNext.addEventListener('click', () => {
                getAudioContext();
                const count = activeCards.length;
                if (count === 0) return;
                const angleStep = 360 / count;
                targetAngle = Math.round((currentAngle - angleStep) / angleStep) * angleStep;
                isSpinningGasing = true;
                resetIdleTimer();
            });
        }

        if (btnSpin) {
            btnSpin.addEventListener('click', triggerGasingSpin);
        }

        // Category Filter Buttons
        const filterBtns = document.querySelectorAll('.kiosk-filter-btn');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                currentFilter = this.dataset.filter;
                allCards.forEach(c => {
                    if (currentFilter === 'all' || c.dataset.cat === currentFilter) {
                        c.style.display = 'flex';
                    } else {
                        c.style.display = 'none';
                    }
                });

                activeCards = allCards.filter(c => currentFilter === 'all' || c.dataset.cat === currentFilter);
                rebuildRouletteLayout();
                resetIdleTimer();
            });
        });

        // Click card to bring to front
        allCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (hasMoved) return;
                if (e.target.closest('.btn-open-detail')) return;
                const posIndex = parseInt(this.dataset.posIndex, 10);
                if (!isNaN(posIndex) && posIndex !== activeIndex) {
                    rotateToIndex(posIndex);
                }
            });
        });

        // Keyboard navigation (Left / Right arrow keys)
        window.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (e.key === 'ArrowLeft') {
                if (btnPrev) btnPrev.click();
            } else if (e.key === 'ArrowRight') {
                if (btnNext) btnNext.click();
            } else if (e.key === ' ') {
                if (btnSpin) btnSpin.click();
            }
        });

        // Initialize Layout
        rebuildRouletteLayout();

        // --- 4. Candidate Detail Modal (Visi & Misi) ---
        const modalDetailEl = document.getElementById('modalCandidateDetail');
        const modalDetail = new bootstrap.Modal(modalDetailEl);

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        $(document).on('click', '.btn-open-detail', function(e) {
            if (hasMoved) return;
            e.stopPropagation();
            const btn = $(this);
            const name = btn.data('name');
            const num = btn.data('num');
            const cat = btn.data('cat');
            const isKetua = btn.data('is-ketua') == '1';
            const photo = btn.data('photo');
            const vision = btn.data('vision');
            const mission = btn.data('mission');

            $('#modalCandName').text(name);
            $('#modalCandNameSub').text(name);
            $('#modalCatBadge').text(cat)
                .removeClass('bg-success bg-primary')
                .addClass(isKetua ? 'bg-success' : 'bg-primary');
            $('#modalCandNumLabel').text('KANDIDAT NO. ' + String(num).padStart(2, '0'));
            $('#modalCandPhoto').attr('src', photo);
            $('#modalCandVision').text(vision);

            // Format Mission Lines
            const lines = (mission || '').split('\n').filter(l => l.trim().length > 0);
            $('#modalMissionCount').text(lines.length + ' Poin Program');

            let html = '<ul class="list-group list-group-flush p-0 m-0">';
            lines.forEach(line => {
                html += '<li class="list-group-item px-0 py-2 border-0 d-flex gap-2 align-items-start">' +
                        '<i class="fas fa-check-circle ' + (isKetua ? 'text-success' : 'text-primary') + ' mt-1 flex-shrink-0"></i>' +
                        '<span class="text-secondary" style="font-size: 0.92rem;">' + escapeHtml(line) + '</span></li>';
            });
            html += '</ul>';
            $('#modalCandMissionContainer').html(html);

            modalDetail.show();
        });

        // --- 5. Three.js Floating Smartcard Setup ---
        const cardWrap = document.getElementById('threeCardWrap');
        if (window.THREE && cardWrap) {
            let scene, camera, renderer, animId;
            let cardMesh, greenGlowLight;
            let isScanningState = false;
            let mouseX = 0, mouseY = 0;
            let targetRotX = 0, targetRotY = 0;

            const w = cardWrap.clientWidth || 170;
            const h = cardWrap.clientHeight || 105;

            scene = new THREE.Scene();
            camera = new THREE.PerspectiveCamera(38, w / h, 0.1, 50);
            camera.position.set(0, 0, 3.4);

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(w, h);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
            cardWrap.appendChild(renderer.domElement);

            const amb = new THREE.AmbientLight(0xffffff, 0.9);
            scene.add(amb);

            const dirLight = new THREE.DirectionalLight(0xffffff, 0.85);
            dirLight.position.set(2, 4, 3);
            scene.add(dirLight);

            greenGlowLight = new THREE.PointLight(0x10b981, 0.8, 6);
            greenGlowLight.position.set(0, 0, 1.8);
            scene.add(greenGlowLight);

            function makeFrontTexture() {
                const cv = document.createElement('canvas');
                cv.width = 512;
                cv.height = 320;
                const ctx = cv.getContext('2d');

                const grad = ctx.createLinearGradient(0, 0, 512, 320);
                grad.addColorStop(0, '#064e3b');
                grad.addColorStop(0.65, '#065f46');
                grad.addColorStop(1, '#0f172a');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, 512, 320);

                ctx.fillStyle = 'rgba(245, 158, 11, 0.25)';
                ctx.beginPath();
                ctx.moveTo(380, 0);
                ctx.lineTo(512, 0);
                ctx.lineTo(420, 320);
                ctx.lineTo(288, 320);
                ctx.closePath();
                ctx.fill();

                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 22px sans-serif';
                ctx.fillText('KOPERASI UBS', 28, 48);
                ctx.font = '14px sans-serif';
                ctx.fillStyle = '#a7f3d0';
                ctx.fillText('KARTU ANGGOTA ELEKTRONIK', 28, 72);

                ctx.fillStyle = '#f59e0b';
                ctx.beginPath();
                ctx.roundRect ? ctx.roundRect(32, 95, 68, 52, 6) : ctx.rect(32, 95, 68, 52);
                ctx.fill();
                ctx.strokeStyle = '#b45309';
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 3;
                ctx.lineCap = 'round';
                for (let r = 10; r <= 22; r += 6) {
                    ctx.beginPath();
                    ctx.arc(430, 60, r, -Math.PI * 0.35, Math.PI * 0.35);
                    ctx.stroke();
                }

                ctx.font = 'bold 20px monospace';
                ctx.fillStyle = '#f8fafc';
                ctx.fillText('••••  ••••  ••••  9213', 32, 215);

                ctx.font = 'bold 15px sans-serif';
                ctx.fillStyle = '#34d399';
                ctx.fillText('TAP PADA SENSOR UNTUK MEMILIH', 32, 275);

                return new THREE.CanvasTexture(cv);
            }

            function makeBackTexture() {
                const cv = document.createElement('canvas');
                cv.width = 512;
                cv.height = 320;
                const ctx = cv.getContext('2d');
                ctx.fillStyle = '#1e293b';
                ctx.fillRect(0, 0, 512, 320);

                ctx.fillStyle = '#0f172a';
                ctx.fillRect(0, 35, 512, 55);

                ctx.fillStyle = '#f8fafc';
                ctx.fillRect(28, 120, 360, 42);
                ctx.fillStyle = '#94a3b8';
                ctx.font = '14px sans-serif';
                ctx.fillText('AUTHORIZED SIGNATURE / VERIFIED NFC', 36, 146);

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

            cardMesh = new THREE.Mesh(cardGeo, [rimMat, rimMat, rimMat, rimMat, frontMat, backMat]);
            scene.add(cardMesh);

            cardWrap.addEventListener('mousemove', (e) => {
                const rect = cardWrap.getBoundingClientRect();
                mouseX = ((e.clientX - rect.left) / rect.width) * 2 - 1;
                mouseY = -(((e.clientY - rect.top) / rect.height) * 2 - 1);
            });
            cardWrap.addEventListener('mouseleave', () => {
                mouseX = 0; mouseY = 0;
            });

            let isCardLoopRunning = true;
            function animateSmartcard() {
                if (!isCardLoopRunning) return;
                animId = requestAnimationFrame(animateSmartcard);

                const t = performance.now() / 1000;

                if (!isScanningState) {
                    cardMesh.position.y = Math.sin(t * 1.5) * 0.08;
                    cardMesh.position.z = 0;
                    targetRotY = Math.sin(t * 0.9) * 0.2 + mouseX * 0.35;
                    targetRotX = Math.cos(t * 1.1) * 0.08 - mouseY * 0.2;
                    cardMesh.rotation.y += (targetRotY - cardMesh.rotation.y) * 0.1;
                    cardMesh.rotation.x += (targetRotX - cardMesh.rotation.x) * 0.1;
                    greenGlowLight.intensity = 0.8;
                } else {
                    cardMesh.position.y = 0;
                    cardMesh.position.z += (0.42 - cardMesh.position.z) * 0.15;
                    cardMesh.rotation.y += (0 - cardMesh.rotation.y) * 0.2;
                    cardMesh.rotation.x += (0 - cardMesh.rotation.x) * 0.2;
                    greenGlowLight.intensity = 2.2 + Math.sin(t * 10) * 0.8;
                }

                renderer.render(scene, camera);
            }
            animateSmartcard();

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    isCardLoopRunning = false;
                    if (animId) cancelAnimationFrame(animId);
                } else {
                    if (!isCardLoopRunning) {
                        isCardLoopRunning = true;
                        animateSmartcard();
                    }
                }
            });

            window.set3DCardScanning = function(active) {
                isScanningState = active;
            };
        }

        // --- 6. RFID Form & Scanner Logic (Persistent Autofocus) ---
        $(document).ready(function() {
            const $input = $('#rfidUid');
            const $form = $('#rfidForm');
            const $statusText = $('#statusText');
            let isProcessing = false;

            function maintainFocus() {
                if (!isProcessing && !document.querySelector('.modal.show')) {
                    $input.focus();
                }
            }

            maintainFocus();
            setInterval(maintainFocus, 1000);
            $(document).on('click keydown', function(e) {
                if (!$(e.target).closest('.modal, input, textarea, button').length) {
                    maintainFocus();
                }
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
                            playSuccessChime();
                            $statusText.text('Verifikasi berhasil! Membuka bilik suara...');
                            setTimeout(() => {
                                window.location.href = res.redirect;
                            }, 500);
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
    })();
    </script>
</body>
</html>
