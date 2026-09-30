<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$all_candidates = $all_candidates ?? [];
$ketua_candidates = $ketua_candidates ?? [];
$pengawas_candidates = $pengawas_candidates ?? [];

// Fallback in case candidates were not passed
if (empty($all_candidates) && !empty($ketua_candidates)) {
    $all_candidates = array_merge($ketua_candidates, $pengawas_candidates);
}

$coop_name = $settings['cooperative_name'] ?? 'Koperasi UBS';
$election_title = !empty($settings['election_title']) ? $settings['election_title'] : 'Pemilihan Pengurus & Pengawas Koperasi';
$timeout_seconds = (int)($timeout_seconds ?? ($settings['booth_timeout_seconds'] ?? 60));
if ($timeout_seconds <= 0) $timeout_seconds = 120;

// Active voter state (if pre-authenticated e.g. dev_booth)
$initial_stage = (!empty($voter_id)) ? 'ballot' : 'scanner';
$clean_name = trim($voter_name ?? '');
$parts = preg_split('/\s+/', $clean_name);
$initials = '';
if (!empty($parts)) {
    if (count($parts) >= 2) {
        $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
    } else {
        $initials = strtoupper(substr($parts[0], 0, min(2, strlen($parts[0]))));
    }
}
if (empty($initials)) {
    $initials = 'PM';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk E-Voting - <?= htmlspecialchars($coop_name); ?></title>
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
    <style>
        .spa-stage {
            display: none;
            width: 100%;
        }
        .spa-stage.active {
            display: block;
        }
        #stageBallot {
            padding-bottom: 125px;
        }
    </style>
</head>
<body class="bg-light is-scanner-page">

    <div class="kiosk-spa-container" id="kioskSpaContainer">

        <!-- ====================================================================
             STAGE 1: SCANNER & STANDBY TERMINAL (3D Smartcard & Roulette)
             ==================================================================== -->
        <div id="stageScanner" class="spa-stage <?= ($initial_stage === 'scanner') ? 'active' : ''; ?>">
            <div class="kiosk-wrapper">
                <!-- Top Navigation Bar / Kiosk Header -->
                <header class="kop-navbar">
                    <div class="container-fluid d-flex justify-content-between align-items-center">
                        <a href="<?= base_url(); ?>" class="kop-brand">
                            <i class="fas fa-landmark text-success"></i>
                            <span><?= htmlspecialchars($coop_name); ?></span>
                            <span class="kop-brand-badge">KIOSK E-VOTING</span>
                        </a>
                        
                        <div class="d-none d-md-flex align-items-center">
                            <span class="kiosk-clock-badge" id="kioskClock" title="Waktu Kiosk Server">
                                <i class="far fa-clock text-success"></i>
                                <span id="kioskClockText">Memuat Waktu...</span>
                            </span>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-kiosk-fullscreen" id="btnFullscreenToggle" title="Mode Layar Penuh (Fullscreen)" aria-label="Mode Layar Penuh">
                                <i class="fas fa-expand me-1" id="iconFullscreen"></i>
                                <span id="textFullscreen" class="d-none d-sm-inline">Layar Penuh</span>
                            </button>
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
                        <h1 class="kiosk-hero-title"><?= htmlspecialchars($election_title); ?></h1>
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

                        <!-- Hidden RFID Wedge Input Form (No virtual keyboard on tablets) -->
                        <form id="rfidForm" class="visually-hidden" aria-hidden="true">
                            <input type="password" id="rfidUid" name="rfid_uid" autocomplete="off" inputmode="none" tabindex="-1">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                        </form>
                    </div>
                </main>
            </div>
        </div>


        <!-- ====================================================================
             STAGE 2: DIGITAL BALLOT BOOTH (Stepper: Ketua -> Pengawas -> Tinjau)
             ==================================================================== -->
        <div id="stageBallot" class="spa-stage <?= ($initial_stage === 'ballot') ? 'active' : ''; ?>">
            <!-- Sticky Header with Voter Info & Timeout -->
            <header class="ballot-header">
                <div class="container-fluid px-lg-4 px-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="voter-avatar-initials" id="voterAvatarInitials" title="<?= htmlspecialchars($clean_name); ?>">
                            <?= htmlspecialchars($initials); ?>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="badge-voter-verified">
                                    <i class="fas fa-check-circle me-1"></i> PEMILIH TERVERIFIKASI
                                </span>
                                <span class="text-muted small">•</span>
                                <span class="badge-voter-member">
                                    No. Anggota: <span id="voterMemberNumber"><?= htmlspecialchars($member_number ?? '-'); ?></span>
                                </span>
                            </div>
                            <div class="voter-display-name" id="voterDisplayName">
                                <?= htmlspecialchars(strtoupper($clean_name ?: 'ANGGOTA PEMILIH')); ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="ballot-timer-box" id="timerBox" title="Sisa batas waktu bilik suara">
                            <i class="far fa-clock"></i>
                            <span>SISA WAKTU: <strong id="timerCountdown">--:--</strong></span>
                        </div>
                        <button type="button" class="btn-fullscreen-toggle" id="btnFullscreenToggleBallot" title="Mode Layar Penuh (Fullscreen)" aria-label="Mode Layar Penuh">
                            <i class="fas fa-expand me-1" id="iconFullscreenBallot"></i>
                            <span id="textFullscreenBallot" class="d-none d-md-inline">Layar Penuh</span>
                        </button>
                        <button type="button" class="btn-cancel-exit" id="btnCancelBooth">
                            <i class="fas fa-times me-1"></i> Batal &amp; Keluar
                        </button>
                    </div>
                </div>
            </header>

            <main class="container-fluid px-lg-4 px-3 my-4">
                <!-- Sub-Header & Event Info -->
                <div class="text-center mb-4">
                    <div class="event-pill-badge mb-2">
                        <i class="fas fa-shield-alt me-1"></i> <span id="ballotEventTitle"><?= htmlspecialchars($election_title); ?></span>
                    </div>
                    <h2 class="ballot-main-title mb-2">Surat Suara Elektronik</h2>
                    <p class="ballot-subtitle">
                        Ikuti tahapan pemilihan di bawah ini secara bertahap untuk memilih <strong>Calon Ketua</strong>, <strong>Calon Pengawas</strong>, dan melakukan <strong>Konfirmasi Suara</strong>.
                    </p>
                </div>

                <!-- Stepper Progress Bar -->
                <div class="ballot-stepper-wrapper">
                    <div class="ballot-stepper">
                        <!-- Step 1 Tab -->
                        <div class="stepper-item active" id="stepperTab1" data-step="1" role="button">
                            <div class="stepper-circle">
                                <span class="step-num">1</span>
                                <i class="fas fa-check step-check"></i>
                            </div>
                            <div class="stepper-content">
                                <span class="stepper-label">Langkah 1</span>
                                <span class="stepper-title">Calon Ketua</span>
                            </div>
                        </div>

                        <div class="stepper-line" id="stepperLine1"></div>

                        <!-- Step 2 Tab -->
                        <div class="stepper-item" id="stepperTab2" data-step="2" role="button">
                            <div class="stepper-circle">
                                <span class="step-num">2</span>
                                <i class="fas fa-check step-check"></i>
                            </div>
                            <div class="stepper-content">
                                <span class="stepper-label">Langkah 2</span>
                                <span class="stepper-title">Calon Pengawas</span>
                            </div>
                        </div>

                        <div class="stepper-line" id="stepperLine2"></div>

                        <!-- Step 3 Tab -->
                        <div class="stepper-item" id="stepperTab3" data-step="3" role="button">
                            <div class="stepper-circle">
                                <span class="step-num">3</span>
                                <i class="fas fa-check step-check"></i>
                            </div>
                            <div class="stepper-content">
                                <span class="stepper-label">Langkah 3</span>
                                <span class="stepper-title">Konfirmasi Suara</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="ballotForm">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    
                    <!-- Hidden Radios for Form Submission -->
                    <div class="d-none">
                        <?php foreach ($ketua_candidates as $c): ?>
                            <input type="radio" name="selections[ketua]" id="radio_ketua_<?= $c['id']; ?>" value="<?= $c['id']; ?>">
                        <?php endforeach; ?>
                        <?php foreach ($pengawas_candidates as $c): ?>
                            <input type="radio" name="selections[pengawas]" id="radio_pengawas_<?= $c['id']; ?>" value="<?= $c['id']; ?>">
                        <?php endforeach; ?>
                    </div>

                    <!-- STEP 1: MEMILIH CALON KETUA KOPERASI (EMERALD THEME) -->
                    <div class="wizard-step active" id="wizardStep1" data-step="1">
                        <div class="category-container category-container-ketua">
                            <div class="category-header-bar">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="category-icon-box category-icon-ketua">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <h3 class="category-card-title m-0">Calon Ketua Koperasi</h3>
                                        <div class="category-card-subtitle">Langkah 1: Silakan pilih 1 (satu) calon Ketua Koperasi pilihan Anda</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="category-quota-badge quota-ketua">
                                        <i class="fas fa-check me-1"></i> Pilih 1 Calon
                                    </div>
                                    <div class="slider-nav-group" title="Geser daftar calon">
                                        <button type="button" class="btn-slider-nav" id="btnSlidePrevKetua" aria-label="Geser ke kiri">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button type="button" class="btn-slider-nav" id="btnSlideNextKetua" aria-label="Geser ke kanan">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Carousel for Candidates -->
                            <div class="candidate-scroll-row" id="sliderKetua">
                                <?php foreach ($ketua_candidates as $c): 
                                    $v_preview = trim($c['vision'] ?? '');
                                    if (empty($v_preview) || strtolower($v_preview) === 'visi') {
                                        $v_preview = 'Mewujudkan koperasi yang transparan, modern, dan mensejahterakan seluruh anggota.';
                                    }
                                ?>
                                <div class="candidate-col">
                                    <div class="candidate-card" 
                                         data-category="ketua" 
                                         data-id="<?= $c['id']; ?>" 
                                         data-name="<?= htmlspecialchars($c['name']); ?>"
                                         data-num="<?= $c['candidate_number']; ?>"
                                         data-photo="<?= base_url($c['photo']); ?>"
                                         data-vision="<?= htmlspecialchars($v_preview); ?>"
                                         data-mission="<?= htmlspecialchars($c['mission']); ?>">
                                        <div class="candidate-floating-terpilih floating-ketua">
                                            <i class="fas fa-check"></i> Terpilih
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="candidate-num-box num-ketua"><?= $c['candidate_number']; ?></span>
                                            <span class="candidate-seq-text">KANDIDAT <?= sprintf('%02d', $c['candidate_number']); ?></span>
                                        </div>

                                        <div class="candidate-avatar-wrap" title="Klik untuk melihat foto lebih besar" role="button" aria-label="Lihat foto <?= htmlspecialchars($c['name']); ?> lebih besar">
                                            <img src="<?= base_url($c['photo']); ?>" alt="<?= htmlspecialchars($c['name']); ?>" class="candidate-avatar-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                                            <span class="avatar-zoom-icon"><i class="fas fa-search-plus"></i></span>
                                        </div>

                                        <div class="candidate-card-name"><?= htmlspecialchars($c['name']); ?></div>
                                        <div class="candidate-quote-preview">"<?= htmlspecialchars($v_preview); ?>"</div>

                                        <div class="candidate-actions-group mt-auto">
                                            <button type="button" class="btn-card-vision mb-2 btn-detail-trigger" 
                                                    data-name="<?= htmlspecialchars($c['name']); ?>"
                                                    data-num="<?= $c['candidate_number']; ?>"
                                                    data-category="Ketua Koperasi"
                                                    data-photo="<?= base_url($c['photo']); ?>"
                                                    data-vision="<?= htmlspecialchars($v_preview); ?>"
                                                    data-mission="<?= htmlspecialchars($c['mission']); ?>">
                                                <i class="fas fa-file-alt me-1"></i> Visi &amp; Misi
                                            </button>
                                            <button type="button" class="btn-card-action">
                                                <span class="text-default">Pilih Calon</span>
                                                <span class="text-selected"><i class="fas fa-check"></i> Pilihan Anda</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-3 pt-2 text-center text-muted small border-top">
                                <i class="fas fa-info-circle text-success me-1"></i> Pilihan Anda saat ini: <strong id="statusSummaryKetua" class="text-dark">Belum Memilih</strong>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: MEMILIH CALON PENGAWAS KOPERASI (ROYAL BLUE THEME) -->
                    <div class="wizard-step" id="wizardStep2" data-step="2">
                        <div class="category-container category-container-pengawas">
                            <div class="category-header-bar">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="category-icon-box category-icon-pengawas">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <h3 class="category-card-title m-0">Calon Pengawas Koperasi</h3>
                                        <div class="category-card-subtitle">Langkah 2: Silakan pilih 1 (satu) calon Pengawas Koperasi pilihan Anda</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="category-quota-badge quota-pengawas">
                                        <i class="fas fa-check me-1"></i> Pilih 1 Calon
                                    </div>
                                    <div class="slider-nav-group" title="Geser daftar calon">
                                        <button type="button" class="btn-slider-nav" id="btnSlidePrevPengawas" aria-label="Geser ke kiri">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button type="button" class="btn-slider-nav" id="btnSlideNextPengawas" aria-label="Geser ke kanan">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Carousel for Candidates -->
                            <div class="candidate-scroll-row" id="sliderPengawas">
                                <?php foreach ($pengawas_candidates as $c): 
                                    $v_preview = trim($c['vision'] ?? '');
                                    if (empty($v_preview) || strtolower($v_preview) === 'visi') {
                                        $v_preview = 'Pengawasan independen, akuntabel, dan berintegritas demi tata kelola koperasi berkeadilan.';
                                    }
                                ?>
                                <div class="candidate-col">
                                    <div class="candidate-card" 
                                         data-category="pengawas" 
                                         data-id="<?= $c['id']; ?>" 
                                         data-name="<?= htmlspecialchars($c['name']); ?>"
                                         data-num="<?= $c['candidate_number']; ?>"
                                         data-photo="<?= base_url($c['photo']); ?>"
                                         data-vision="<?= htmlspecialchars($v_preview); ?>"
                                         data-mission="<?= htmlspecialchars($c['mission']); ?>">
                                        <div class="candidate-floating-terpilih floating-pengawas">
                                            <i class="fas fa-check"></i> Terpilih
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="candidate-num-box num-pengawas"><?= $c['candidate_number']; ?></span>
                                            <span class="candidate-seq-text">KANDIDAT <?= sprintf('%02d', $c['candidate_number']); ?></span>
                                        </div>

                                        <div class="candidate-avatar-wrap" title="Klik untuk melihat foto lebih besar" role="button" aria-label="Lihat foto <?= htmlspecialchars($c['name']); ?> lebih besar">
                                            <img src="<?= base_url($c['photo']); ?>" alt="<?= htmlspecialchars($c['name']); ?>" class="candidate-avatar-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                                            <span class="avatar-zoom-icon"><i class="fas fa-search-plus"></i></span>
                                        </div>

                                        <div class="candidate-card-name"><?= htmlspecialchars($c['name']); ?></div>
                                        <div class="candidate-quote-preview">"<?= htmlspecialchars($v_preview); ?>"</div>

                                        <div class="candidate-actions-group mt-auto">
                                            <button type="button" class="btn-card-vision mb-2 btn-detail-trigger" 
                                                    data-name="<?= htmlspecialchars($c['name']); ?>"
                                                    data-num="<?= $c['candidate_number']; ?>"
                                                    data-category="Pengawas Koperasi"
                                                    data-photo="<?= base_url($c['photo']); ?>"
                                                    data-vision="<?= htmlspecialchars($v_preview); ?>"
                                                    data-mission="<?= htmlspecialchars($c['mission']); ?>">
                                                <i class="fas fa-file-alt me-1"></i> Visi &amp; Misi
                                            </button>
                                            <button type="button" class="btn-card-action">
                                                <span class="text-default">Pilih Calon</span>
                                                <span class="text-selected"><i class="fas fa-check"></i> Pilihan Anda</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-3 pt-2 text-center text-muted small border-top">
                                <i class="fas fa-info-circle text-primary me-1"></i> Pilihan Anda saat ini: <strong id="statusSummaryPengawas" class="text-dark">Belum Memilih</strong>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: KONFIRMASI ATAS PILIHAN YANG SUDAH DIPILIH -->
                    <div class="wizard-step" id="wizardStep3" data-step="3">
                        <div class="category-container category-container-review">
                            <div class="category-header-bar mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="category-icon-box category-icon-review">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                    <div>
                                        <h3 class="category-card-title m-0">Konfirmasi Pilihan Suara Anda</h3>
                                        <div class="category-card-subtitle">Langkah 3: Periksa kembali pilihan Anda sebelum mengirimkan suara ke ledger pemilu</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Side-by-side Review Cards -->
                            <div class="row g-4 mb-3">
                                <!-- Review Card 1: Ketua -->
                                <div class="col-md-6">
                                    <div class="confirm-card confirm-card-ketua">
                                        <div class="confirm-card-header">
                                            <span class="confirm-badge-header confirm-badge-ketua">
                                                <i class="fas fa-user-tie me-1"></i> Calon Ketua Terpilih
                                            </span>
                                            <span class="badge bg-success rounded-pill px-3 py-1">Pilihan 1</span>
                                        </div>

                                        <div class="confirm-card-body">
                                            <div class="confirm-avatar-box">
                                                <img src="" id="reviewPhotoKetua" alt="Foto Calon Ketua" class="confirm-avatar-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                                            </div>

                                            <div class="confirm-candidate-name" id="reviewNameKetua">Belum Memilih</div>
                                            <div class="confirm-candidate-num" id="reviewNumKetua">Kandidat No. -</div>

                                            <div class="mb-3">
                                                <span class="badge-status-picked">
                                                    <i class="fas fa-check-circle me-1 text-success"></i> Terverifikasi Siap Kirim
                                                </span>
                                            </div>
                                            
                                            <div class="confirm-vision-styled">
                                                <div class="confirm-vision-label">
                                                    <i class="fas fa-quote-left me-1 text-success"></i> Visi Calon:
                                                </div>
                                                <div class="confirm-vision-content" id="reviewVisionKetua">
                                                    <em>Calon Ketua belum dipilih.</em>
                                                </div>
                                                <div class="mt-2 pt-1 border-top" id="wrapReviewDetailKetua" style="display: none;">
                                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-success fw-semibold" id="btnReviewDetailKetua" style="font-size: 0.8rem;">
                                                        <i class="fas fa-file-alt me-1"></i> Baca Visi &amp; Misi Lengkap
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mt-auto pt-2 text-center">
                                                <span class="badge bg-light text-success border border-success-subtle px-3 py-1.5 rounded-pill">
                                                    <i class="fas fa-lock me-1"></i> Pilihan Calon Terkunci
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Review Card 2: Pengawas -->
                                <div class="col-md-6">
                                    <div class="confirm-card confirm-card-pengawas">
                                        <div class="confirm-card-header">
                                            <span class="confirm-badge-header confirm-badge-pengawas">
                                                <i class="fas fa-shield-alt me-1"></i> Calon Pengawas Terpilih
                                            </span>
                                            <span class="badge bg-primary rounded-pill px-3 py-1">Pilihan 2</span>
                                        </div>

                                        <div class="confirm-card-body">
                                            <div class="confirm-avatar-box">
                                                <img src="" id="reviewPhotoPengawas" alt="Foto Calon Pengawas" class="confirm-avatar-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                                            </div>

                                            <div class="confirm-candidate-name" id="reviewNamePengawas">Belum Memilih</div>
                                            <div class="confirm-candidate-num" id="reviewNumPengawas">Kandidat No. -</div>

                                            <div class="mb-3">
                                                <span class="badge-status-picked">
                                                    <i class="fas fa-check-circle me-1 text-primary"></i> Terverifikasi Siap Kirim
                                                </span>
                                            </div>

                                            <div class="confirm-vision-styled">
                                                <div class="confirm-vision-label">
                                                    <i class="fas fa-quote-left me-1 text-primary"></i> Visi Calon:
                                                </div>
                                                <div class="confirm-vision-content" id="reviewVisionPengawas">
                                                    <em>Calon Pengawas belum dipilih.</em>
                                                </div>
                                                <div class="mt-2 pt-1 border-top" id="wrapReviewDetailPengawas" style="display: none;">
                                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-primary fw-semibold" id="btnReviewDetailPengawas" style="font-size: 0.8rem;">
                                                        <i class="fas fa-file-alt me-1"></i> Baca Visi &amp; Misi Lengkap
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mt-auto pt-2 text-center">
                                                <span class="badge bg-light text-primary border border-primary-subtle px-3 py-1.5 rounded-pill">
                                                    <i class="fas fa-lock me-1"></i> Pilihan Calon Terkunci
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security LUBER JURDIL Assurance -->
                            <div class="confirm-trust-strip">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                    <i class="fas fa-lock text-success fs-5"></i>
                                    <strong class="text-dark">Prinsip Kerahasiaan &amp; Asas Pemilu LUBER JURDIL</strong>
                                </div>
                                <p class="m-0 text-muted small" style="max-width: 720px; margin: 0 auto !important;">
                                    Pilihan Anda dijamin <strong>Langsung, Umum, Bebas, Rahasia, Jujur dan Adil</strong> dengan enkripsi kriptografis 256-bit. Pilihan suara bersifat final setelah tombol <strong>Kirim Pilihan Suara</strong> ditekan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- STICKY BOTTOM WIZARD BAR (NEXT, SUBMIT) -->
                    <div class="ballot-sticky-footer">
                        <div class="container-fluid px-lg-4 px-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <!-- Left: Placeholder -->
                            <div class="d-none">
                                <button type="button" class="btn-wizard-back d-none" id="btnWizardPrev" style="display: none !important;">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali
                                </button>
                            </div>

                            <!-- Center: Status Progress -->
                            <div class="d-flex align-items-center gap-3">
                                <div class="sticky-status-circle" id="stickyStatusCircle">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="sticky-status-label">STATUS WIZARD:</span>
                                        <span id="statusCompletenessBadge" class="badge-completeness badge-incomplete">Langkah 1 dari 3</span>
                                    </div>
                                    <div id="stickySelectionSummary" class="sticky-summary-text">
                                        Ketua: <span class="text-muted">Belum Dipilih</span> • Pengawas: <span class="text-muted">Belum Dipilih</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Next / Submit Button -->
                            <div class="ms-auto d-flex align-items-center gap-2">
                                <button type="button" class="btn-wizard-next next-ketua" id="btnWizardNext">
                                    <span>Lanjut ke Calon Pengawas</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </button>

                                <button type="submit" id="btnConfirmSubmit" class="btn-submit-ballot" style="display: none;" disabled>
                                    <i class="fas fa-inbox me-2"></i> Kirim Pilihan Suara
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>


        <!-- ====================================================================
             STAGE 3: SUCCESS & DIGITAL RECEIPT (3D Ballot Box & Auto-Reset)
             ==================================================================== -->
        <div id="stageSuccess" class="spa-stage">
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

                    <h2 class="h4 fw-bold text-dark mb-1">Terima Kasih atas Partisipasi Anda!</h2>
                    <p class="text-secondary small mb-3">Hak suara Anda telah berhasil disimpan dan disegel secara anonim dalam ledger digital.</p>

                    <div class="text-start bg-light p-3 rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-semibold">Kode Tanda Terima Suara (Audit Token):</span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-size: 0.7rem;">
                                <i class="fas fa-check-circle me-1"></i> Asas Rahasia
                            </span>
                        </div>
                        <div class="receipt-code-box my-1 py-2 fs-6 text-center" id="successReceiptToken">
                            -
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
                        Bilik suara akan kembali ke layar awal dalam <strong id="countdown" class="text-dark">3</strong> detik...
                    </p>

                    <button type="button" class="btn btn-kop-primary w-100 py-2 fw-semibold" id="btnFinish">
                        <i class="fas fa-check-circle me-1"></i> Selesai &amp; Kembali ke Layar Awal
                    </button>
                </div>
            </div>
        </div>

    </div><!-- /#kioskSpaContainer -->


    <!-- ====================================================================
         SHARED MODALS: VISI & MISI DETAIL + PHOTO PREVIEW LIGHTBOX
         ==================================================================== -->

    <!-- Modal Visi & Misi (Scrollable & Responsive) -->
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

    <!-- Modal Preview Foto Kandidat (Lightbox Dialog) -->
    <div class="modal fade" id="modalPhotoPreview" tabindex="-1" aria-labelledby="modalPhotoTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" id="modalPhotoCatBadge">Calon</span>
                        <h6 class="modal-title fw-bold text-dark m-0" id="modalPhotoTitle">Foto Kandidat</h6>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="photo-preview-frame mb-3">
                        <img src="" id="modalPhotoImage" alt="Foto Kandidat" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                    </div>
                    <h5 class="fw-bold text-dark mb-1" id="modalPhotoCandName">-</h5>
                    <div class="text-muted small mb-4" id="modalPhotoCandSub">-</div>
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-pill" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Tutup
                        </button>
                        <button type="button" class="btn px-4 py-2 rounded-pill text-white fw-bold" id="btnSelectFromPhotoModal">
                            <span id="btnSelectFromPhotoText"><i class="fas fa-check me-1"></i> Pilih Calon Ini</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Web Accessibility Widget -->
    <?php $this->load->view('voting/accessibility_widget', ['page' => 'kiosk']); ?>


    <!-- ====================================================================
         UNIFIED SPA SCRIPTS ENGINE
         ==================================================================== -->
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

        // --- 2. Offline Web Audio Synthesizer ---
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

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(s) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[s];
            });
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
            if (!dotsWrap || !cylinder) return;
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
            if (!cylinder) return;
            cylinder.style.transform = 'translateZ(-' + cylinderRadius + 'px) rotateY(' + currentAngle + 'deg)';

            const count = activeCards.length;
            if (count === 0) return;

            const angleStep = 360 / count;
            let normAngle = ((-currentAngle % 360) + 360) % 360;
            let closestIdx = Math.round(normAngle / angleStep) % count;

            if (closestIdx !== lastTickIndex) {
                if (lastTickIndex !== -1 && (Math.abs(velocity) > 0.3 || isDragging)) {
                    playTickSound();
                }
                lastTickIndex = closestIdx;
            }

            activeIndex = closestIdx;

            activeCards.forEach((card, i) => {
                const baseAngle = i * angleStep;
                let diff = ((baseAngle + currentAngle) % 360 + 540) % 360 - 180;
                let absDiff = Math.abs(diff);

                if (absDiff < angleStep * 0.48) {
                    card.classList.add('is-active-front');
                    card.style.opacity = '1';
                    card.style.zIndex = '20';
                    card.style.pointerEvents = 'auto';
                } else if (absDiff < 105) {
                    card.classList.remove('is-active-front');
                    card.style.opacity = '0.55';
                    card.style.zIndex = '5';
                    card.style.pointerEvents = 'auto';
                } else {
                    card.classList.remove('is-active-front');
                    card.style.opacity = '0.18';
                    card.style.zIndex = '1';
                    card.style.pointerEvents = 'none';
                }
            });

            if (dotsWrap) {
                const dots = dotsWrap.querySelectorAll('.kiosk-dot-item');
                dots.forEach((d, i) => {
                    if (i === activeIndex) {
                        d.classList.add('active');
                    } else {
                        d.classList.remove('active');
                    }
                });
            }
        }

        function rotateToIndex(idx) {
            const count = activeCards.length;
            if (count === 0) return;
            const angleStep = 360 / count;
            const targetNorm = -idx * angleStep;
            
            let curNorm = currentAngle % 360;
            let diff = ((targetNorm - curNorm) % 360 + 540) % 360 - 180;
            targetAngle = currentAngle + diff;
            isSpinningGasing = false;
            velocity = 0;
            resetIdleTimer();
        }

        function spinGasingBoost() {
            resetIdleTimer();
            isSpinningGasing = true;
            const dir = Math.random() > 0.5 ? 1 : -1;
            velocity = dir * (26 + Math.random() * 12);
            playSuccessChime();
        }

        function physicsLoop() {
            if (isDragging) {
                currentAngle = targetAngle;
            } else if (isSpinningGasing) {
                currentAngle += velocity;
                velocity *= 0.985;
                if (Math.abs(velocity) < 0.2) {
                    isSpinningGasing = false;
                    const count = activeCards.length;
                    if (count > 0) {
                        const step = 360 / count;
                        targetAngle = Math.round(currentAngle / step) * step;
                    }
                }
            } else if (isIdleRotating) {
                currentAngle += 0.08;
            } else {
                currentAngle += (targetAngle - currentAngle) * 0.12;
            }

            updateRouletteTransform();
            requestAnimationFrame(physicsLoop);
        }
        requestAnimationFrame(physicsLoop);

        function resetIdleTimer() {
            isIdleRotating = false;
            if (idleTimer) clearTimeout(idleTimer);
            idleTimer = setTimeout(() => {
                if (!isDragging && !isSpinningGasing && $('#stageScanner').hasClass('active')) {
                    isIdleRotating = true;
                }
            }, 12000);
        }
        resetIdleTimer();

        // Drag & Touch events for roulette
        if (stage) {
            function onDragStart(clientX) {
                isDragging = true;
                isIdleRotating = false;
                isSpinningGasing = false;
                startX = clientX;
                lastX = clientX;
                lastTime = performance.now();
                hasMoved = false;
                velocity = 0;
                resetIdleTimer();
            }

            function onDragMove(clientX) {
                if (!isDragging) return;
                const deltaX = clientX - lastX;
                if (Math.abs(clientX - startX) > 8) {
                    hasMoved = true;
                }
                const now = performance.now();
                const dt = Math.max(1, now - lastTime);
                velocity = (deltaX / dt) * 14;
                lastX = clientX;
                lastTime = now;

                targetAngle += deltaX * 0.38;
            }

            function onDragEnd() {
                if (!isDragging) return;
                isDragging = false;
                resetIdleTimer();

                if (Math.abs(velocity) > 6) {
                    isSpinningGasing = true;
                } else {
                    const count = activeCards.length;
                    if (count > 0) {
                        const step = 360 / count;
                        targetAngle = Math.round(targetAngle / step) * step;
                    }
                }
            }

            stage.addEventListener('mousedown', e => {
                if (e.target.closest('.btn-open-detail')) return;
                onDragStart(e.clientX);
            });
            window.addEventListener('mousemove', e => onDragMove(e.clientX));
            window.addEventListener('mouseup', onDragEnd);

            stage.addEventListener('touchstart', e => {
                if (e.touches.length === 1) {
                    if (e.target.closest('.btn-open-detail')) return;
                    onDragStart(e.touches[0].clientX);
                }
            }, { passive: true });
            window.addEventListener('touchmove', e => {
                if (e.touches.length === 1) onDragMove(e.touches[0].clientX);
            }, { passive: true });
            window.addEventListener('touchend', onDragEnd);
        }

        // Toolbar Buttons
        const btnPrev = document.getElementById('btnPrevCard');
        const btnNext = document.getElementById('btnNextCard');
        const btnSpin = document.getElementById('btnSpinTop');

        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                const count = activeCards.length;
                if (count === 0) return;
                const nextIdx = (activeIndex - 1 + count) % count;
                rotateToIndex(nextIdx);
            });
        }
        if (btnNext) {
            btnNext.addEventListener('click', () => {
                const count = activeCards.length;
                if (count === 0) return;
                const nextIdx = (activeIndex + 1) % count;
                rotateToIndex(nextIdx);
            });
        }
        if (btnSpin) {
            btnSpin.addEventListener('click', spinGasingBoost);
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

        rebuildRouletteLayout();


        // --- 4. Candidate Detail Modal (Visi & Misi) ---
        const modalDetailEl = document.getElementById('modalCandidateDetail');
        const modalDetail = new bootstrap.Modal(modalDetailEl);

        function renderMissionList(missionRaw, isKetua) {
            if (!missionRaw || !missionRaw.trim()) {
                $('#modalMissionCount').text('0 Program');
                return '<div class="text-muted fst-italic p-3 bg-light rounded-3 small">Tidak ada rincian misi khusus yang dicantumkan.</div>';
            }
            
            const lines = missionRaw.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
            $('#modalMissionCount').text(`${lines.length} Program Misi`);
            
            let html = '<div class="modal-mission-list">';
            let itemIndex = 1;
            const badgeClass = isKetua ? 'mission-num-badge badge-ketua' : 'mission-num-badge';
            
            lines.forEach(line => {
                let cleanLine = line.replace(/^(\d+[\.\)]|\-|\*)\s*/, '');
                let badgeNum = itemIndex++;
                
                let titlePart = '';
                let descPart = cleanLine;
                const colonIdx = cleanLine.indexOf(':');
                if (colonIdx > 0 && colonIdx < 55) {
                    titlePart = cleanLine.substring(0, colonIdx);
                    descPart = cleanLine.substring(colonIdx + 1).trim();
                }
                
                html += `
                    <div class="modal-mission-item">
                        <span class="${badgeClass}">${badgeNum}</span>
                        <div class="mission-item-text">
                            ${titlePart ? `<span class="mission-lead-title">${escapeHtml(titlePart)}:</span> ` : ''}
                            <span>${escapeHtml(descPart)}</span>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            return html;
        }

        function openDetailModal(name, num, cat, photo, vision, mission) {
            const isKetua = (cat.indexOf('Ketua') !== -1);
            $('#modalCatBadge').text(cat + ' - No. ' + num);
            if (isKetua) {
                $('#modalCatBadge').removeClass('bg-primary').addClass('bg-success');
                $('#modalVisionCard').removeClass('is-pengawas');
                $('#modalVisionTitle').removeClass('text-primary').addClass('text-success');
                $('#modalMissionTitle').removeClass('text-success').addClass('text-primary');
            } else {
                $('#modalCatBadge').removeClass('bg-success').addClass('bg-primary');
                $('#modalVisionCard').addClass('is-pengawas');
                $('#modalVisionTitle').removeClass('text-success').addClass('text-primary');
                $('#modalMissionTitle').removeClass('text-primary').addClass('text-success');
            }

            $('#modalCandName').text(name);
            $('#modalCandNameSub').text(name);
            $('#modalCandNumLabel').text(cat + ' • Urut ' + String(num).padStart(2, '0'));
            $('#modalCandPhoto').attr('src', photo);
            
            let cleanVision = vision ? vision.trim() : '';
            if (!cleanVision || cleanVision.toLowerCase() === 'visi') {
                cleanVision = isKetua 
                    ? 'Mewujudkan koperasi yang transparan, modern, dan mensejahterakan seluruh anggota.' 
                    : 'Pengawasan independen, akuntabel, dan berintegritas demi tata kelola koperasi berkeadilan.';
            }
            $('#modalCandVision').text(`"${cleanVision}"`);
            $('#modalCandMissionContainer').html(renderMissionList(mission, isKetua));
            modalDetail.show();
        }

        $(document).on('click', '.btn-open-detail, .btn-detail-trigger', function(e) {
            if (hasMoved) return;
            e.stopPropagation();
            openDetailModal(
                $(this).data('name'),
                $(this).data('num'),
                $(this).data('category') || $(this).data('cat'),
                $(this).data('photo'),
                $(this).data('vision'),
                $(this).data('mission')
            );
        });


        // --- 5. Three.js Floating Smartcard Setup (Original Scanner Stage) ---
        const cardWrap = document.getElementById('threeCardWrap');
        let cardMesh, greenGlowLight, smartcardRenderer, smartcardAnimId;
        let isCardLoopRunning = false;
        let isScanningState = false;

        if (window.THREE && cardWrap) {
            let scene, camera;
            let mouseX = 0, mouseY = 0;
            let targetRotX = 0, targetRotY = 0;

            const w = cardWrap.clientWidth || 170;
            const h = cardWrap.clientHeight || 105;

            scene = new THREE.Scene();
            camera = new THREE.PerspectiveCamera(38, w / h, 0.1, 50);
            camera.position.set(0, 0, 3.4);

            smartcardRenderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            smartcardRenderer.setSize(w, h);
            smartcardRenderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
            cardWrap.appendChild(smartcardRenderer.domElement);

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

            isCardLoopRunning = true;
            function animateSmartcard() {
                if (!isCardLoopRunning) return;
                smartcardAnimId = requestAnimationFrame(animateSmartcard);

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

                smartcardRenderer.render(scene, camera);
            }
            animateSmartcard();

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    isCardLoopRunning = false;
                    if (smartcardAnimId) cancelAnimationFrame(smartcardAnimId);
                } else {
                    if (!isCardLoopRunning && $('#stageScanner').hasClass('active')) {
                        isCardLoopRunning = true;
                        animateSmartcard();
                    }
                }
            });

            window.resume3DSmartcard = function() {
                if (!isCardLoopRunning) {
                    isCardLoopRunning = true;
                    animateSmartcard();
                }
            };

            window.pause3DSmartcard = function() {
                isCardLoopRunning = false;
                if (smartcardAnimId) cancelAnimationFrame(smartcardAnimId);
            };

            window.set3DCardScanning = function(active) {
                isScanningState = active;
            };
        }


        // --- 6. Three.js 3D Ballot Box Setup (Success Stage) ---
        const ballotBoxContainer = document.getElementById('threeCanvasWrap');
        const ballotStatusPill = document.getElementById('ballotStatusPill');
        let bbScene, bbCamera, bbRenderer, bbAnimId;
        let bbBoxGroup, bbBallotMesh, bbLockMesh, bbParticles;
        let bbIsDragging = false, bbPreviousMouseX = 0, bbUserRotationY = 0;
        let bbStartTime = 0, bbPhase = 0;
        let isBallotBoxInitialized = false;

        function initBallotBoxScene() {
            if (!window.THREE || !ballotBoxContainer) {
                if (ballotStatusPill) ballotStatusPill.innerHTML = '<i class="fas fa-check-circle text-success"></i> Suara Sah Terkunci';
                return;
            }

            let width = ballotBoxContainer.clientWidth || 500;
            let height = ballotBoxContainer.clientHeight || 250;

            if (!isBallotBoxInitialized) {
                bbScene = new THREE.Scene();
                bbCamera = new THREE.PerspectiveCamera(40, width / height, 0.1, 100);
                bbCamera.position.set(0, 2.2, 5.2);
                bbCamera.lookAt(0, 0.2, 0);

                bbRenderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
                bbRenderer.setSize(width, height);
                bbRenderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
                bbRenderer.toneMapping = THREE.ACESFilmicToneMapping;
                bbRenderer.toneMappingExposure = 1.05;
                ballotBoxContainer.appendChild(bbRenderer.domElement);

                // Lighting
                const ambLight = new THREE.AmbientLight(0xffffff, 0.75);
                bbScene.add(ambLight);

                const keyLight = new THREE.DirectionalLight(0xffffff, 0.9);
                keyLight.position.set(4, 7, 5);
                bbScene.add(keyLight);

                const emeraldFill = new THREE.PointLight(0x059669, 1.4, 8);
                emeraldFill.position.set(0, 0.4, 2.0);
                bbScene.add(emeraldFill);

                const topLight = new THREE.SpotLight(0xfef08a, 1.2, 8, Math.PI / 4, 0.4);
                topLight.position.set(0, 4.5, 0.5);
                bbScene.add(topLight);

                // Box Group
                bbBoxGroup = new THREE.Group();
                bbBoxGroup.position.set(0, -0.15, 0);
                bbScene.add(bbBoxGroup);

                // Outer Frosted Box
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
                bbBoxGroup.add(boxMesh);

                // Wireframe edges
                const edgeGeo = new THREE.EdgesGeometry(boxGeo);
                const edgeMat = new THREE.LineBasicMaterial({
                    color: 0x059669,
                    transparent: true,
                    opacity: 0.45,
                    linewidth: 1
                });
                bbBoxGroup.add(new THREE.LineSegments(edgeGeo, edgeMat));

                // Base inside Box
                const baseGeo = new THREE.BoxGeometry(2.2, 0.06, 1.6);
                const baseMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3, metalness: 0.7 });
                const baseMesh = new THREE.Mesh(baseGeo, baseMat);
                baseMesh.position.y = -0.72;
                bbBoxGroup.add(baseMesh);

                // Lid with Slot
                const lidMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.25, metalness: 0.6 });
                const lidWingGeo = new THREE.BoxGeometry(0.75, 0.05, 1.62);
                const lidLeft = new THREE.Mesh(lidWingGeo, lidMat);
                lidLeft.position.set(-0.72, 0.73, 0);
                bbBoxGroup.add(lidLeft);

                const lidRight = new THREE.Mesh(lidWingGeo, lidMat);
                lidRight.position.set(0.72, 0.73, 0);
                bbBoxGroup.add(lidRight);

                const lidRimGeo = new THREE.BoxGeometry(0.8, 0.05, 0.65);
                const lidFront = new THREE.Mesh(lidRimGeo, lidMat);
                lidFront.position.set(0, 0.73, 0.48);
                bbBoxGroup.add(lidFront);

                const lidBack = new THREE.Mesh(lidRimGeo, lidMat);
                lidBack.position.set(0, 0.73, -0.48);
                bbBoxGroup.add(lidBack);

                const slotTrimGeo = new THREE.BoxGeometry(0.74, 0.06, 0.16);
                const slotTrimMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, metalness: 0.85, roughness: 0.2 });
                const slotTrim = new THREE.Mesh(slotTrimGeo, slotTrimMat);
                slotTrim.position.set(0, 0.735, 0);
                bbBoxGroup.add(slotTrim);

                // Cooperative Emblem
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
                const emblemMat = new THREE.MeshStandardMaterial({ map: emblemTex, transparent: true, roughness: 0.3, metalness: 0.2 });
                const emblemMesh = new THREE.Mesh(emblemGeo, emblemMat);
                emblemMesh.position.set(0, 0.05, 0.865);
                bbBoxGroup.add(emblemMesh);

                // Digital Ballot Paper
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
                const ballotMat = new THREE.MeshStandardMaterial({ map: ballotTex, roughness: 0.4, metalness: 0.1 });
                bbBallotMesh = new THREE.Mesh(ballotGeo, ballotMat);
                bbScene.add(bbBallotMesh);

                // 3D Digital Shield / Lock Bar
                const lockGeo = new THREE.BoxGeometry(0.72, 0.08, 0.14);
                const lockMat = new THREE.MeshStandardMaterial({ color: 0x059669, metalness: 0.85, roughness: 0.2 });
                bbLockMesh = new THREE.Mesh(lockGeo, lockMat);
                bbLockMesh.position.set(0, 0.77, 0);
                bbBoxGroup.add(bbLockMesh);

                // Particles
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

                const pMat = new THREE.PointsMaterial({ size: 0.075, vertexColors: true, transparent: true, opacity: 0, blending: THREE.AdditiveBlending });
                bbParticles = new THREE.Points(pGeo, pMat);
                bbBoxGroup.add(bbParticles);

                // Drag Interaction
                const el = ballotBoxContainer;
                const onDown = (clientX) => { bbIsDragging = true; bbPreviousMouseX = clientX; };
                const onMove = (clientX) => {
                    if (!bbIsDragging) return;
                    const delta = clientX - bbPreviousMouseX;
                    bbPreviousMouseX = clientX;
                    bbUserRotationY += delta * 0.008;
                };
                const onUp = () => { bbIsDragging = false; };

                el.addEventListener('mousedown', (e) => onDown(e.clientX));
                window.addEventListener('mousemove', (e) => onMove(e.clientX));
                window.addEventListener('mouseup', onUp);
                el.addEventListener('touchstart', (e) => { if (e.touches.length === 1) onDown(e.touches[0].clientX); }, { passive: true });
                window.addEventListener('touchmove', (e) => { if (e.touches.length === 1) onMove(e.touches[0].clientX); }, { passive: true });
                window.addEventListener('touchend', onUp);

                window.addEventListener('resize', () => {
                    if (!ballotBoxContainer || !bbRenderer || !bbCamera) return;
                    width = ballotBoxContainer.clientWidth;
                    height = ballotBoxContainer.clientHeight;
                    bbCamera.aspect = width / height;
                    bbCamera.updateProjectionMatrix();
                    bbRenderer.setSize(width, height);
                });

                isBallotBoxInitialized = true;
            }

            // Reset animation state
            bbStartTime = performance.now();
            bbPhase = 0;
            bbUserRotationY = 0;

            if (bbBallotMesh.parent !== bbScene) {
                bbBoxGroup.remove(bbBallotMesh);
                bbScene.add(bbBallotMesh);
            }
            bbBallotMesh.position.set(0, 2.3, 0.1);
            bbBallotMesh.rotation.set(-0.25, 0.1, -0.05);
            bbBallotMesh.scale.set(1, 1, 1);

            bbLockMesh.scale.set(0.001, 0.001, 0.001);
            bbParticles.material.opacity = 0;

            if (ballotStatusPill) {
                ballotStatusPill.innerHTML = '<span class="spinner-border spinner-border-sm text-success" style="width: 12px; height: 12px; border-width: 2px;"></span> <span>Memasukkan Suara ke Kotak...</span>';
                ballotStatusPill.style.borderColor = '#a7f3d0';
                ballotStatusPill.style.backgroundColor = 'rgba(255, 255, 255, 0.92)';
            }

            if (bbAnimId) cancelAnimationFrame(bbAnimId);
            animateBallotBox();
        }

        function animateBallotBox() {
            bbAnimId = requestAnimationFrame(animateBallotBox);
            const elapsed = (performance.now() - bbStartTime) / 1000;

            if (elapsed < 0.7) {
                const p = elapsed / 0.7;
                bbBallotMesh.position.y = 2.3 - p * 0.4;
                bbBallotMesh.rotation.x = -0.25 * (1 - p);
                bbBallotMesh.rotation.y = 0.1 * (1 - p);
            } else if (elapsed < 1.9) {
                if (bbPhase === 0) bbPhase = 1;
                const p = (elapsed - 0.7) / 1.2;
                const ease = p < 0.5 ? 2 * p * p : -1 + (4 - 2 * p) * p;
                bbBallotMesh.position.y = 1.9 - ease * 2.5;
                bbBallotMesh.rotation.x = -0.05 + ease * 1.45;
                bbBallotMesh.rotation.z = ease * 0.2;
                bbBallotMesh.position.z = 0.1 * (1 - ease);
                bbBallotMesh.scale.set(1 - ease * 0.15, 1 - ease * 0.15, 1 - ease * 0.15);
            } else if (elapsed < 2.5) {
                if (bbPhase === 1) {
                    bbPhase = 2;
                    if (bbBallotMesh.parent !== bbBoxGroup) {
                        bbScene.remove(bbBallotMesh);
                        bbBoxGroup.add(bbBallotMesh);
                        bbBallotMesh.position.set(0.08, -0.62, 0.05);
                        bbBallotMesh.rotation.set(1.4, 0.2, 0.15);
                        bbBallotMesh.scale.set(0.85, 0.85, 0.85);
                    }
                    if (ballotStatusPill) {
                        ballotStatusPill.innerHTML = '<i class="fas fa-lock text-success me-1"></i> <strong>Suara Sah Tersegel &amp; Terkunci</strong>';
                        ballotStatusPill.style.borderColor = '#10b981';
                        ballotStatusPill.style.backgroundColor = '#ecfdf5';
                    }
                }
                const p = (elapsed - 1.9) / 0.6;
                const lockScale = Math.min(1.0, p * 1.25);
                bbLockMesh.scale.set(lockScale, lockScale, lockScale);
                bbParticles.material.opacity = Math.max(0, 1 - p);
                const pos = bbParticles.geometry.attributes.position.array;
                for (let i = 0; i < pos.length; i += 3) {
                    pos[i + 1] += 0.015;
                    pos[i] += (Math.random() - 0.5) * 0.005;
                }
                bbParticles.geometry.attributes.position.needsUpdate = true;
            } else {
                bbLockMesh.scale.set(1, 1, 1);
                bbParticles.material.opacity = 0;
            }

            if (!bbIsDragging) {
                bbUserRotationY *= 0.95;
            }
            const idleAngle = Math.sin(elapsed * 0.6) * 0.22;
            bbBoxGroup.rotation.y = idleAngle + bbUserRotationY;
            bbBoxGroup.rotation.x = Math.sin(elapsed * 0.4) * 0.04;

            bbRenderer.render(bbScene, bbCamera);
        }

        function stopBallotBox() {
            if (bbAnimId) {
                cancelAnimationFrame(bbAnimId);
                bbAnimId = null;
            }
        }


        // --- 7. SPA Stage Controller & Inactivity Countdown ---
        let boothTimerInterval = null;
        let successCountdownInterval = null;
        let currentVoterTimeout = <?= (int)$timeout_seconds; ?>;

        function showStage(stageName) {
            $('.spa-stage').removeClass('active').hide();
            if (stageName === 'scanner') {
                $('#stageScanner').addClass('active').show();
                $('body').removeClass('is-ballot-page').addClass('is-scanner-page');
                window.scrollTo({ top: 0, behavior: 'instant' });

                if (window.resume3DSmartcard) window.resume3DSmartcard();
                stopBallotBox();

                $('#rfidUid').val('').focus();
                $('#statusText').text('Sensor RFID Siap Menerima Kartu');
            } else if (stageName === 'ballot') {
                $('#stageBallot').addClass('active').show();
                $('body').removeClass('is-scanner-page').addClass('is-ballot-page');
                window.scrollTo({ top: 0, behavior: 'instant' });

                if (window.pause3DSmartcard) window.pause3DSmartcard();
                stopBallotBox();

                setTimeout(() => scrollToActiveStep(false), 60);
            } else if (stageName === 'success') {
                $('#stageSuccess').addClass('active').show();
                $('body').removeClass('is-scanner-page is-ballot-page');
                window.scrollTo({ top: 0, behavior: 'instant' });

                if (window.pause3DSmartcard) window.pause3DSmartcard();
                initBallotBoxScene();
                startSuccessCountdown();
            }
        }

        function resetToScannerStage() {
            if (boothTimerInterval) clearInterval(boothTimerInterval);
            if (successCountdownInterval) clearInterval(successCountdownInterval);

            // Reset ballot selections
            selectedKetua = null;
            selectedKetuaName = '';
            selectedKetuaNum = '';
            selectedKetuaPhoto = '';
            selectedKetuaVision = '';
            selectedKetuaMission = '';

            selectedPengawas = null;
            selectedPengawasName = '';
            selectedPengawasNum = '';
            selectedPengawasPhoto = '';
            selectedPengawasVision = '';
            selectedPengawasMission = '';

            $('#ballotForm')[0].reset();
            $('.candidate-card').removeClass('is-selected-ketua is-selected-pengawas');
            currentStep = 1;
            goToStep(1);
            syncUI();

            // Reset Voter details in DOM
            $('#voterAvatarInitials').text('PM').attr('title', 'Pemilih');
            $('#voterDisplayName').text('ANGGOTA PEMILIH');
            $('#voterMemberNumber').text('-');

            // Reset RFID input and indicator
            $('#rfidUid').val('');
            if (window.set3DCardScanning) window.set3DCardScanning(false);
            $('#statusText').text('Sensor RFID Siap Menerima Kartu');

            // Switch to scanner without leaving fullscreen
            showStage('scanner');
        }

        function startBoothTimer(seconds) {
            if (boothTimerInterval) clearInterval(boothTimerInterval);
            let total = seconds > 0 ? seconds : 120;
            const $timer = $('#timerCountdown');
            const $timerBox = $('#timerBox');

            function formatTime(s) {
                const m = Math.floor(s / 60);
                const sec = s % 60;
                return String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
            }

            $timer.text(formatTime(total));
            $timerBox.removeClass('timer-urgent');
            if (total <= 30) {
                $timerBox.addClass('timer-urgent');
            }

            boothTimerInterval = setInterval(function() {
                total--;
                $timer.text(formatTime(Math.max(0, total)));

                if (total <= 30) {
                    $timerBox.addClass('timer-urgent');
                }

                if (total <= 0) {
                    clearInterval(boothTimerInterval);
                    Swal.fire({
                        title: 'Batas Waktu Berakhir',
                        text: 'Waktu di bilik suara telah habis demi keamanan.',
                        icon: 'warning',
                        confirmButtonColor: '#059669',
                        confirmButtonText: 'Kembali ke Layar Utama'
                    }).then(() => {
                        const csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
                        const csrfHash = $(`input[name="${csrfName}"]`).val() || '<?= $this->security->get_csrf_hash(); ?>';
                        const postData = { ajax: 1 };
                        postData[csrfName] = csrfHash;

                        $.ajax({
                            url: '<?= base_url("voting/cancel"); ?>',
                            type: 'POST',
                            data: postData,
                            dataType: 'json',
                            success: function(res) {
                                if (res && res.csrf_token_name && res.csrf_hash) {
                                    $(`input[name="${res.csrf_token_name}"]`).val(res.csrf_hash);
                                }
                            },
                            complete: function() {
                                resetToScannerStage();
                            }
                        });
                    });
                }
            }, 1000);
        }

        function startSuccessCountdown() {
            if (successCountdownInterval) clearInterval(successCountdownInterval);
            let totalTime = 3;
            let timeLeft = totalTime;
            const countEl = document.getElementById('countdown');
            const progressBar = document.getElementById('progressBar');

            if (countEl) countEl.innerText = timeLeft;
            if (progressBar) progressBar.style.width = '100%';

            successCountdownInterval = setInterval(function() {
                timeLeft--;
                if (countEl) countEl.innerText = timeLeft;
                if (progressBar) {
                    const percent = Math.max(0, (timeLeft / totalTime) * 100);
                    progressBar.style.width = percent + '%';
                }
                if (timeLeft <= 0) {
                    clearInterval(successCountdownInterval);
                    resetToScannerStage();
                }
            }, 1000);
        }

        $('#btnFinish').on('click', function(e) {
            e.preventDefault();
            resetToScannerStage();
        });

        // Cancel Booth Button Handler
        $('#btnCancelBooth').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Batalkan Sesi Bilik Suara?',
                text: 'Pilihan Anda belum disimpan. Anda akan kembali ke layar awal pemindaian kartu.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Batalkan & Keluar',
                cancelButtonText: 'Lanjutkan Memilih'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (boothTimerInterval) clearInterval(boothTimerInterval);
                    const csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
                    const csrfHash = $(`input[name="${csrfName}"]`).val() || '<?= $this->security->get_csrf_hash(); ?>';
                    const postData = { ajax: 1 };
                    postData[csrfName] = csrfHash;

                    $.ajax({
                        url: '<?= base_url("voting/cancel"); ?>',
                        type: 'POST',
                        data: postData,
                        dataType: 'json',
                        success: function(res) {
                            if (res && res.csrf_token_name && res.csrf_hash) {
                                $(`input[name="${res.csrf_token_name}"]`).val(res.csrf_hash);
                            }
                        },
                        complete: function() {
                            resetToScannerStage();
                        }
                    });
                }
            });
        });


        // --- 8. RFID Hardware Keydown Buffer & Verification ---
        const $input = $('#rfidUid');
        const $form = $('#rfidForm');
        const $statusText = $('#statusText');
        let isProcessing = false;
        let rfidBuffer = '';
        let lastKeyTime = 0;
        const SCAN_TIMEOUT_MS = 1500;

        window.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'TEXTAREA' || (e.target.tagName === 'INPUT' && e.target.id !== 'rfidUid')) {
                return;
            }

            const currentTime = Date.now();
            if (currentTime - lastKeyTime > SCAN_TIMEOUT_MS) {
                rfidBuffer = '';
            }
            lastKeyTime = currentTime;

            if (e.key === 'Enter') {
                const scannedUid = (rfidBuffer.trim() || $input.val().trim());
                if (scannedUid.length >= 4 && !isProcessing && $('#stageScanner').hasClass('active')) {
                    e.preventDefault();
                    e.stopPropagation();

                    const modalEl = document.getElementById('modalCandidateDetail');
                    if (modalEl && modalEl.classList.contains('show')) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                    }

                    $input.val(scannedUid);
                    rfidBuffer = '';
                    $form.trigger('submit');
                }
                rfidBuffer = '';
                return;
            }

            if (/^[a-zA-Z0-9]$/.test(e.key)) {
                rfidBuffer += e.key;
            }
        }, true);

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

                        // Update CSRF token if returned
                        if (res.csrf_token_name && res.csrf_hash) {
                            $(`input[name="${res.csrf_token_name}"]`).val(res.csrf_hash);
                        }

                        // Populate Voter Information in Ballot Stage
                        if (res.voter) {
                            const name = res.voter.name || '';
                            const memNum = res.voter.member_number || '';
                            const parts = name.trim().split(/\s+/);
                            let inits = 'PM';
                            if (parts.length >= 2) {
                                inits = (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
                            } else if (parts.length === 1 && parts[0].length > 0) {
                                inits = parts[0].substring(0, Math.min(2, parts[0].length)).toUpperCase();
                            }
                            $('#voterAvatarInitials').text(inits).attr('title', name);
                            $('#voterDisplayName').text(name.toUpperCase());
                            $('#voterMemberNumber').text(memNum);
                        }

                        currentVoterTimeout = res.timeout_seconds || <?= (int)$timeout_seconds; ?>;

                        setTimeout(() => {
                            isProcessing = false;
                            if (window.set3DCardScanning) window.set3DCardScanning(false);
                            
                            // Transition cleanly to Ballot stage without page reload (Fullscreen stays active!)
                            showStage('ballot');
                            startBoothTimer(currentVoterTimeout);
                        }, 500);

                    } else {
                        isProcessing = false;
                        if (window.set3DCardScanning) window.set3DCardScanning(false);
                        $statusText.text('Sensor RFID Siap Menerima Kartu');
                        $input.val('');
                        rfidBuffer = '';
                        
                        Swal.fire({
                            title: 'Pemberitahuan',
                            text: res.message,
                            icon: 'warning',
                            confirmButtonColor: '#0f5132',
                            confirmButtonText: 'Kembali'
                        });
                    }
                },
                error: function(xhr) {
                    isProcessing = false;
                    if (window.set3DCardScanning) window.set3DCardScanning(false);
                    $statusText.text('Sensor RFID Siap Menerima Kartu');
                    $input.val('');
                    rfidBuffer = '';

                    let msg = 'Terjadi gangguan jaringan atau sesi tidak sah.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        title: 'Akses Ditolak',
                        text: msg,
                        icon: 'error',
                        confirmButtonColor: '#0f5132'
                    });
                }
            });
        });


        // --- 9. Ballot Booth Selection & Stepper Logic ---
        const $ballotForm = $('#ballotForm');
        const $btnSubmit = $('#btnConfirmSubmit');
        const $btnNext = $('#btnWizardNext');
        const $btnPrev = $('#btnWizardPrev');
        const modalPhoto = new bootstrap.Modal(document.getElementById('modalPhotoPreview'));
        let currentPhotoCandidate = null;

        let currentStep = 1;
        let selectedKetua = null;
        let selectedKetuaName = '';
        let selectedKetuaNum = '';
        let selectedKetuaPhoto = '';
        let selectedKetuaVision = '';
        let selectedKetuaMission = '';

        let selectedPengawas = null;
        let selectedPengawasName = '';
        let selectedPengawasNum = '';
        let selectedPengawasPhoto = '';
        let selectedPengawasVision = '';
        let selectedPengawasMission = '';

        // Carousel Slider Centering
        function checkCentering() {
            ['sliderKetua', 'sliderPengawas'].forEach(function(id) {
                const slider = document.getElementById(id);
                if (!slider) return;
                const container = slider.closest('.category-container');
                const navGroup = container ? container.querySelector('.slider-nav-group') : null;
                
                if (slider.scrollWidth <= slider.clientWidth + 15) {
                    slider.classList.add('is-centered');
                    if (navGroup) navGroup.style.display = 'none';
                } else {
                    slider.classList.remove('is-centered');
                    if (navGroup) navGroup.style.display = 'inline-flex';
                }
            });
        }

        function setupSoftSlider(sliderId, prevBtnId, nextBtnId) {
            const slider = document.getElementById(sliderId);
            const prevBtn = document.getElementById(prevBtnId);
            const nextBtn = document.getElementById(nextBtnId);
            if (!slider || !prevBtn || !nextBtn) return;

            function updateButtonsState() {
                const atStart = slider.scrollLeft <= 10;
                const atEnd = (slider.scrollLeft + slider.clientWidth) >= (slider.scrollWidth - 10);
                prevBtn.disabled = atStart;
                nextBtn.disabled = atEnd;
            }

            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const scrollStep = Math.max(260, Math.floor(slider.clientWidth * 0.65));
                slider.scrollBy({ left: -scrollStep, behavior: 'smooth' });
            });

            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const scrollStep = Math.max(260, Math.floor(slider.clientWidth * 0.65));
                slider.scrollBy({ left: scrollStep, behavior: 'smooth' });
            });

            slider.addEventListener('scroll', updateButtonsState, { passive: true });
            window.addEventListener('resize', function() {
                checkCentering();
                updateButtonsState();
            });
            setTimeout(function() {
                checkCentering();
                updateButtonsState();
            }, 120);
        }

        setupSoftSlider('sliderKetua', 'btnSlidePrevKetua', 'btnSlideNextKetua');
        setupSoftSlider('sliderPengawas', 'btnSlidePrevPengawas', 'btnSlideNextPengawas');

        function selectCandidateCard($card) {
            const category = $card.data('category');
            const id = $card.data('id');
            const name = $card.data('name');
            const num = $card.data('num');
            const photo = $card.data('photo');
            const vision = $card.data('vision');
            const mission = $card.data('mission');

            if (category === 'ketua') {
                $('.candidate-card[data-category="ketua"]').removeClass('is-selected-ketua');
                $card.addClass('is-selected-ketua');
                $(`#radio_ketua_${id}`).prop('checked', true);

                selectedKetua = id;
                selectedKetuaName = name;
                selectedKetuaNum = num;
                selectedKetuaPhoto = photo;
                selectedKetuaVision = vision;
                selectedKetuaMission = mission;

                $('#statusSummaryKetua').text(`${name} (Kandidat 0${num})`);
            } else if (category === 'pengawas') {
                $('.candidate-card[data-category="pengawas"]').removeClass('is-selected-pengawas');
                $card.addClass('is-selected-pengawas');
                $(`#radio_pengawas_${id}`).prop('checked', true);

                selectedPengawas = id;
                selectedPengawasName = name;
                selectedPengawasNum = num;
                selectedPengawasPhoto = photo;
                selectedPengawasVision = vision;
                selectedPengawasMission = mission;

                $('#statusSummaryPengawas').text(`${name} (Kandidat 0${num})`);
            }

            syncUI();
        }

        $('.candidate-card').on('click', function(e) {
            if ($(e.target).closest('.btn-detail-trigger, .candidate-avatar-wrap').length) return;
            selectCandidateCard($(this));
        });

        $('.candidate-avatar-wrap').on('click', function(e) {
            e.stopPropagation();
            const $card = $(this).closest('.candidate-card');
            if (!$card.length) return;

            const category = $card.data('category');
            const id = $card.data('id');
            const name = $card.data('name');
            const num = $card.data('num');
            const photo = $card.data('photo');

            openPhotoPreviewModal(name, num, category, photo, id);
        });

        function openPhotoPreviewModal(name, num, category, photo, id) {
            const isKetua = (category === 'ketua');
            const catLabel = isKetua ? 'Calon Ketua Koperasi' : 'Calon Pengawas Koperasi';
            const numPadded = String(num).padStart(2, '0');

            currentPhotoCandidate = { category: category, id: id };

            $('#modalPhotoCatBadge')
                .text(`Kandidat ${numPadded}`)
                .removeClass('bg-success bg-primary')
                .addClass(isKetua ? 'bg-success' : 'bg-primary');

            $('#modalPhotoCandName').text(name);
            $('#modalPhotoCandSub').text(`${catLabel} • No. Urut ${numPadded}`);
            $('#modalPhotoImage').attr('src', photo);

            const isSelected = (isKetua && selectedKetua == id) || (!isKetua && selectedPengawas == id);
            const $btnPick = $('#btnSelectFromPhotoModal');
            $btnPick
                .removeClass('btn-success btn-primary')
                .addClass(isKetua ? 'btn-success' : 'btn-primary');

            if (isSelected) {
                $('#btnSelectFromPhotoText').html('<i class="fas fa-check-circle me-1"></i> Pilihan Anda Saat Ini');
                $btnPick.addClass('disabled').prop('disabled', true);
            } else {
                $('#btnSelectFromPhotoText').html('<i class="fas fa-check me-1"></i> Pilih Calon Ini');
                $btnPick.removeClass('disabled').prop('disabled', false);
            }

            modalPhoto.show();
        }

        $('#btnSelectFromPhotoModal').on('click', function() {
            if (!currentPhotoCandidate) return;
            const targetId = currentPhotoCandidate.id;
            const targetCategory = currentPhotoCandidate.category;
            modalPhoto.hide();

            const $targetCard = $(`.candidate-card[data-category="${targetCategory}"][data-id="${targetId}"]`);
            if ($targetCard.length) {
                selectCandidateCard($targetCard);
            }
        });

        // Step Navigation (Strictly Forward-Only)
        function goToStep(step) {
            if (step < 1 || step > 3) return;
            if (step < currentStep) return;

            if (step === 2 && !selectedKetua) {
                Swal.fire({
                    title: 'Pilih Calon Ketua',
                    text: 'Silakan pilih 1 Calon Ketua terlebih dahulu sebelum melanjutkan.',
                    icon: 'info',
                    confirmButtonColor: '#059669',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

            if (step === 3 && (!selectedKetua || !selectedPengawas)) {
                let missingMsg = !selectedKetua ? 'Silakan pilih Calon Ketua terlebih dahulu.' : 'Silakan pilih Calon Pengawas terlebih dahulu.';
                Swal.fire({
                    title: 'Pilihan Belum Lengkap',
                    text: missingMsg,
                    icon: 'warning',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

            currentStep = step;

            $('.wizard-step').removeClass('active');
            $(`#wizardStep${step}`).addClass('active');

            setTimeout(checkCentering, 50);
            setTimeout(function() { scrollToActiveStep(true); }, 30);
            syncUI();
        }

        function syncUI() {
            const safeKetua = escapeHtml(selectedKetuaName);
            const safePengawas = escapeHtml(selectedPengawasName);

            // Update Stepper Tabs
            $('.stepper-item').removeClass('active completed');
            $('.stepper-line').removeClass('completed');

            if (selectedKetua && currentStep > 1) {
                $('#stepperTab1').addClass('completed');
                $('#stepperLine1').addClass('completed');
            } else if (currentStep === 1) {
                $('#stepperTab1').addClass('active');
            }

            if (selectedPengawas && currentStep > 2) {
                $('#stepperTab2').addClass('completed');
                $('#stepperLine2').addClass('completed');
            } else if (currentStep === 2) {
                $('#stepperTab2').addClass('active');
                if (selectedKetua) $('#stepperLine1').addClass('completed');
            }

            if (currentStep === 3) {
                $('#stepperTab3').addClass('active');
                if (selectedKetua) $('#stepperLine1').addClass('completed');
                if (selectedPengawas) $('#stepperLine2').addClass('completed');
            }

            // Update Review Elements
            if (selectedKetua) {
                $('#reviewNameKetua').text(selectedKetuaName);
                $('#reviewNumKetua').text(`Kandidat 0${selectedKetuaNum}`);
                $('#reviewPhotoKetua').attr('src', selectedKetuaPhoto);
                let vKetua = selectedKetuaVision ? selectedKetuaVision.trim() : '';
                if (!vKetua || vKetua.toLowerCase() === 'visi') {
                    vKetua = 'Mewujudkan koperasi yang transparan, modern, dan mensejahterakan seluruh anggota.';
                }
                $('#reviewVisionKetua').text(`"${vKetua}"`);
                $('#wrapReviewDetailKetua').show();
            } else {
                $('#reviewNameKetua').text('Belum Memilih');
                $('#reviewNumKetua').text('Kandidat No. -');
                $('#reviewPhotoKetua').attr('src', '<?= base_url("assets/foto/default-avatar.svg"); ?>');
                $('#reviewVisionKetua').html('<em>Calon Ketua belum dipilih.</em>');
                $('#wrapReviewDetailKetua').hide();
            }

            if (selectedPengawas) {
                $('#reviewNamePengawas').text(selectedPengawasName);
                $('#reviewNumPengawas').text(`Kandidat 0${selectedPengawasNum}`);
                $('#reviewPhotoPengawas').attr('src', selectedPengawasPhoto);
                let vPengawas = selectedPengawasVision ? selectedPengawasVision.trim() : '';
                if (!vPengawas || vPengawas.toLowerCase() === 'visi') {
                    vPengawas = 'Pengawasan independen, akuntabel, dan berintegritas demi tata kelola koperasi berkeadilan.';
                }
                $('#reviewVisionPengawas').text(`"${vPengawas}"`);
                $('#wrapReviewDetailPengawas').show();
            } else {
                $('#reviewNamePengawas').text('Belum Memilih');
                $('#reviewNumPengawas').text('Kandidat No. -');
                $('#reviewPhotoPengawas').attr('src', '<?= base_url("assets/foto/default-avatar.svg"); ?>');
                $('#reviewVisionPengawas').html('<em>Calon Pengawas belum dipilih.</em>');
                $('#wrapReviewDetailPengawas').hide();
            }

            // Update Bottom Wizard Bar Controls
            $btnPrev.hide().css('visibility', 'hidden');

            if (currentStep === 1) {
                $btnNext.show()
                        .removeClass('next-pengawas')
                        .addClass('next-ketua')
                        .html('Lanjut ke Calon Pengawas <i class="fas fa-arrow-right ms-2"></i>')
                        .prop('disabled', !selectedKetua);
                $btnSubmit.hide();

                $('#statusCompletenessBadge')
                    .removeClass('badge-complete')
                    .addClass('badge-incomplete')
                    .text('Langkah 1 dari 3: Calon Ketua');

                let ketuaText = selectedKetua ? `<strong class="text-success">${safeKetua}</strong>` : '<span class="text-muted">Belum Dipilih</span>';
                $('#stickySelectionSummary').html(`Ketua: ${ketuaText}`);
                $('#stickyStatusCircle').removeClass('is-complete');
            } else if (currentStep === 2) {
                $btnNext.show()
                        .removeClass('next-ketua')
                        .addClass('next-pengawas')
                        .html('Tinjau &amp; Konfirmasi <i class="fas fa-arrow-right ms-2"></i>')
                        .prop('disabled', !selectedPengawas);
                $btnSubmit.hide();

                $('#statusCompletenessBadge')
                    .removeClass('badge-complete')
                    .addClass('badge-incomplete')
                    .text('Langkah 2 dari 3: Calon Pengawas');

                let ketuaText = selectedKetua ? `<strong class="text-success">${safeKetua}</strong>` : '<span class="text-muted">Belum Dipilih</span>';
                let pengawasText = selectedPengawas ? `<strong class="text-primary">${safePengawas}</strong>` : '<span class="text-muted">Belum Dipilih</span>';
                $('#stickySelectionSummary').html(`Ketua: ${ketuaText} • Pengawas: ${pengawasText}`);
                $('#stickyStatusCircle').removeClass('is-complete');
            } else if (currentStep === 3) {
                $btnNext.hide();
                $btnSubmit.show().prop('disabled', !(selectedKetua && selectedPengawas));

                $('#statusCompletenessBadge')
                    .removeClass('badge-incomplete')
                    .addClass('badge-complete')
                    .text('Langkah 3 dari 3: Siap Kirim (Lengkap)');

                $('#stickySelectionSummary').html(`Ketua: <strong class="text-success">${safeKetua}</strong> • Pengawas: <strong class="text-primary">${safePengawas}</strong>`);
                $('#stickyStatusCircle').addClass('is-complete');
            }
        }

        $('#stepperTab1').on('click', function() { if (currentStep === 1) goToStep(1); });
        $('#stepperTab2').on('click', function() { if (currentStep <= 2) goToStep(2); });
        $('#stepperTab3').on('click', function() { goToStep(3); });

        $btnNext.on('click', function() {
            if (currentStep === 1) goToStep(2);
            else if (currentStep === 2) goToStep(3);
        });

        $('#btnReviewDetailKetua').on('click', function(e) {
            e.preventDefault();
            if (!selectedKetua) return;
            openDetailModal(selectedKetuaName, selectedKetuaNum, 'Ketua Koperasi', selectedKetuaPhoto, selectedKetuaVision, selectedKetuaMission);
        });

        $('#btnReviewDetailPengawas').on('click', function(e) {
            e.preventDefault();
            if (!selectedPengawas) return;
            openDetailModal(selectedPengawasName, selectedPengawasNum, 'Pengawas Koperasi', selectedPengawasPhoto, selectedPengawasVision, selectedPengawasMission);
        });

        function scrollToActiveStep(smooth = true) {
            const $target = $(`#wizardStep${currentStep}`);
            if ($target.length && $('#stageBallot').hasClass('active')) {
                const headerHeight = $('.ballot-header').outerHeight() || 72;
                const targetTop = Math.max(0, $target.offset().top - headerHeight - 12);
                window.scrollTo({
                    top: targetTop,
                    behavior: smooth ? 'smooth' : 'auto'
                });
            }
        }


        // --- 10. Ballot Form Submission (Direct to Success Stage in SPA) ---
        $ballotForm.on('submit', function(e) {
            e.preventDefault();

            if (!selectedKetua || !selectedPengawas) {
                Swal.fire({
                    title: 'Pilihan Belum Lengkap',
                    text: 'Harap pastikan Anda telah memilih 1 calon Ketua dan 1 calon Pengawas.',
                    icon: 'warning',
                    confirmButtonColor: '#059669'
                });
                return;
            }

            executeSubmission();
        });

        function executeSubmission() {
            if (boothTimerInterval) clearInterval(boothTimerInterval);
            $btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Merekam Suara...');

            $.ajax({
                url: '<?= base_url("voting/submit_vote"); ?>',
                type: 'POST',
                data: $ballotForm.serialize(),
                dataType: 'json',
                success: function(res) {
                    $btnSubmit.prop('disabled', false).html('<i class="fas fa-inbox me-2"></i> Kirim Pilihan Suara');
                    if (res.status === 'success') {
                        // Populate Receipt Token in Success stage
                        $('#successReceiptToken').text(res.receipt_token || '-');

                        // Transition to Success Stage (Fullscreen mode remains 100% active!)
                        showStage('success');
                    } else {
                        Swal.fire({
                            title: 'Perekaman Gagal',
                            text: res.message,
                            icon: 'error',
                            confirmButtonColor: '#059669'
                        }).then(() => {
                            resetToScannerStage();
                        });
                    }
                },
                error: function(xhr) {
                    $btnSubmit.prop('disabled', false).html('<i class="fas fa-inbox me-2"></i> Kirim Pilihan Suara');
                    let errMsg = 'Terjadi kendala saat menyimpan suara ke blockchain ledger.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Kesalahan Sistem',
                        text: errMsg,
                        icon: 'error',
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        resetToScannerStage();
                    });
                }
            });
        }

        // Initialize state
        syncUI();
        <?php if ($initial_stage === 'ballot'): ?>
            startBoothTimer(<?= (int)$timeout_seconds; ?>);
        <?php endif; ?>

    })();
    </script>
</body>
</html>
