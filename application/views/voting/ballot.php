<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Compute clean voter initials (e.g. "Ros Prabowo" -> "RP")
$clean_name = trim($voter_name);
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
$election_title = !empty($settings['election_title']) ? $settings['election_title'] : 'Pemilihan Pengurus & Pengawas KOPUS';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Suara Elektronik - <?= htmlspecialchars($settings['cooperative_name'] ?? 'KOPUS'); ?></title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css?v=' . filemtime(FCPATH . 'assets/css/koperasi.css')); ?>">
    <script src="<?= base_url('assets/vendor/jquery/jquery-3.6.0.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.all.min.js'); ?>"></script>
    <style>
        body { padding-bottom: 120px; background-color: #f8fafc; }
    </style>
</head>
<body>

    <!-- Sticky Header with Voter Info & Timeout -->
    <header class="ballot-header">
        <div class="container-fluid px-lg-4 px-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="voter-avatar-initials" title="<?= htmlspecialchars($voter_name); ?>">
                    <?= htmlspecialchars($initials); ?>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge-voter-verified">
                            <i class="fas fa-check-circle me-1"></i> PEMILIH TERVERIFIKASI
                        </span>
                        <span class="text-muted small">•</span>
                        <span class="badge-voter-member">
                            No. Anggota: <?= htmlspecialchars($member_number); ?>
                        </span>
                    </div>
                    <div class="voter-display-name">
                        <?= htmlspecialchars(strtoupper($voter_name)); ?>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="ballot-timer-box" id="timerBox" title="Sisa batas waktu bilik suara">
                    <i class="far fa-clock"></i>
                    <span>SISA WAKTU: <strong id="timerCountdown">--:--</strong></span>
                </div>
                <a href="<?= base_url('voting/cancel'); ?>" class="btn-cancel-exit" id="btnCancel">
                    <i class="fas fa-times me-1"></i> Batal &amp; Keluar
                </a>
            </div>
        </div>
    </header>

    <main class="container-fluid px-lg-4 px-3 my-4">
        <!-- Sub-Header & Event Info -->
        <div class="text-center mb-4">
            <div class="event-pill-badge mb-2">
                <i class="fas fa-shield-alt me-1"></i> <?= htmlspecialchars($election_title); ?>
            </div>
            <h1 class="ballot-main-title mb-2">Surat Suara Elektronik</h1>
            <p class="ballot-subtitle">
                Ikuti tahapan pemilihan di bawah ini secara bertahap untuk memilih <strong>Calon Ketua</strong>, <strong>Calon Pengawas</strong>, dan melakukan <strong>Konfirmasi Suara</strong>.
            </p>
        </div>

        <!-- Stepper Progress Bar (Anti-Slop UI) -->
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

            <!-- ========================================================
                 STEP 1: MEMILIH CALON KETUA KOPERASI (EMERALD THEME)
                 ======================================================== -->
            <div class="wizard-step active" id="wizardStep1" data-step="1">
                <div class="category-container category-container-ketua">
                    <div class="category-header-bar">
                        <div class="d-flex align-items-center gap-3">
                            <div class="category-icon-box category-icon-ketua">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div>
                                <h2 class="category-card-title m-0">Calon Ketua Koperasi</h2>
                                <div class="category-card-subtitle">Langkah 1: Silakan pilih 1 (satu) calon Ketua Koperasi pilihan Anda</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="category-quota-badge quota-ketua">
                                <i class="fas fa-check me-1"></i> Pilih 1 Calon
                            </div>
                            <!-- Soft Slider Navigation Controls -->
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

                    <!-- Soft Horizontal Sliding Carousel for Candidates -->
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

                                <div class="candidate-avatar-wrap">
                                    <img src="<?= base_url($c['photo']); ?>" alt="<?= htmlspecialchars($c['name']); ?>" class="candidate-avatar-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
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

            <!-- ========================================================
                 STEP 2: MEMILIH CALON PENGAWAS KOPERASI (ROYAL BLUE THEME)
                 ======================================================== -->
            <div class="wizard-step" id="wizardStep2" data-step="2">
                <div class="category-container category-container-pengawas">
                    <div class="category-header-bar">
                        <div class="d-flex align-items-center gap-3">
                            <div class="category-icon-box category-icon-pengawas">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <h2 class="category-card-title m-0">Calon Pengawas Koperasi</h2>
                                <div class="category-card-subtitle">Langkah 2: Silakan pilih 1 (satu) calon Pengawas Koperasi pilihan Anda</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="category-quota-badge quota-pengawas">
                                <i class="fas fa-check me-1"></i> Pilih 1 Calon
                            </div>
                            <!-- Soft Slider Navigation Controls -->
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

                    <!-- Soft Horizontal Sliding Carousel for Candidates -->
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

                                <div class="candidate-avatar-wrap">
                                    <img src="<?= base_url($c['photo']); ?>" alt="<?= htmlspecialchars($c['name']); ?>" class="candidate-avatar-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
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

            <!-- ========================================================
                 STEP 3: KONFIRMASI ATAS PILIHAN YANG SUDAH DIPILIH
                 ======================================================== -->
            <div class="wizard-step" id="wizardStep3" data-step="3">
                <div class="category-container category-container-review">
                    <div class="category-header-bar mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="category-icon-box category-icon-review">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div>
                                <h2 class="category-card-title m-0">Konfirmasi Pilihan Suara Anda</h2>
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
                                            <em>Silakan kembali ke Langkah 1 untuk memilih Calon Ketua.</em>
                                        </div>
                                        <div class="mt-2 pt-1 border-top" id="wrapReviewDetailKetua" style="display: none;">
                                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-success fw-semibold" id="btnReviewDetailKetua" style="font-size: 0.8rem;">
                                                <i class="fas fa-file-alt me-1"></i> Baca Visi &amp; Misi Lengkap
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-auto">
                                        <button type="button" class="btn-change-choice" id="btnChangeKetua">
                                            <i class="fas fa-undo me-1"></i> Ubah Pilihan Ketua
                                        </button>
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
                                            <em>Silakan kembali ke Langkah 2 untuk memilih Calon Pengawas.</em>
                                        </div>
                                        <div class="mt-2 pt-1 border-top" id="wrapReviewDetailPengawas" style="display: none;">
                                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-primary fw-semibold" id="btnReviewDetailPengawas" style="font-size: 0.8rem;">
                                                <i class="fas fa-file-alt me-1"></i> Baca Visi &amp; Misi Lengkap
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-auto">
                                        <button type="button" class="btn-change-choice" id="btnChangePengawas">
                                            <i class="fas fa-undo me-1"></i> Ubah Pilihan Pengawas
                                        </button>
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

            <!-- ========================================================
                 STICKY BOTTOM WIZARD BAR (BACK, NEXT, SUBMIT)
                 ======================================================== -->
            <div class="ballot-sticky-footer">
                <div class="container-fluid px-lg-4 px-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <!-- Left: Back Button -->
                    <div>
                        <button type="button" class="btn-wizard-back" id="btnWizardPrev" style="visibility: hidden;">
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
                        <!-- Next Button (Step 1 & Step 2) -->
                        <button type="button" class="btn-wizard-next next-ketua" id="btnWizardNext">
                            <span>Lanjut ke Calon Pengawas</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </button>

                        <!-- Final Submit Button (Step 3) -->
                        <button type="submit" id="btnConfirmSubmit" class="btn-submit-ballot" style="display: none;" disabled>
                            <i class="fas fa-inbox me-2"></i> Kirim Pilihan Suara
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- Modal Visi & Misi (Scrollable & Responsive) -->
    <div class="modal fade" id="modalCandidateDetail" tabindex="-1" aria-labelledby="modalCandidateLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success" id="modalCatBadge">Ketua</span>
                        <h5 class="modal-title fw-bold text-dark m-0" id="modalCandName">Nama Calon</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4 align-items-start">
                        <div class="col-md-4 text-center border-end-md pb-3 pb-md-0">
                            <div class="candidate-avatar-wrap mx-auto mb-3" style="width: 124px; height: 124px;">
                                <img src="" alt="" id="modalCandPhoto" class="candidate-avatar-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                            </div>
                            <div class="fw-bold text-dark fs-5 mb-1" id="modalCandNameSub"></div>
                            <div class="badge bg-light text-secondary border px-3 py-1 rounded-pill mb-2" id="modalCandNumLabel"></div>
                            <div class="text-muted small">
                                <i class="fas fa-check-circle text-success me-1"></i> Calon Terdaftar Resmi
                            </div>
                        </div>
                        <div class="col-md-8">
                            <!-- Visi Section -->
                            <div class="modal-vision-card" id="modalVisionCard">
                                <div class="modal-section-title text-success" id="modalVisionTitle">
                                    <i class="fas fa-bullseye"></i> Visi Utama Calon:
                                </div>
                                <div id="modalCandVision" class="modal-vision-text"></div>
                            </div>
                            
                            <!-- Misi Section -->
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="modal-section-title text-primary m-0" id="modalMissionTitle">
                                        <i class="fas fa-list-check"></i> Program Kerja &amp; Misi:
                                    </div>
                                    <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small" id="modalMissionCount"></span>
                                </div>
                                <div id="modalCandMissionContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const $form = $('#ballotForm');
        const $btnSubmit = $('#btnConfirmSubmit');
        const $btnNext = $('#btnWizardNext');
        const $btnPrev = $('#btnWizardPrev');
        const $timer = $('#timerCountdown');
        const $timerBox = $('#timerBox');
        const modalDetail = new bootstrap.Modal(document.getElementById('modalCandidateDetail'));

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

        // 1. Inactivity Countdown Timer (MM:SS)
        let totalSeconds = <?= (int)$timeout_seconds; ?>;
        if (totalSeconds <= 0) totalSeconds = 120;

        function formatTime(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }

        $timer.text(formatTime(totalSeconds));

        const timerInterval = setInterval(function() {
            totalSeconds--;
            $timer.text(formatTime(Math.max(0, totalSeconds)));

            if (totalSeconds <= 30) {
                $timerBox.addClass('timer-urgent');
            }

            if (totalSeconds <= 0) {
                clearInterval(timerInterval);
                Swal.fire({
                    title: 'Batas Waktu Berakhir',
                    text: 'Waktu di bilik suara telah habis demi keamanan.',
                    icon: 'warning',
                    confirmButtonColor: '#059669',
                    confirmButtonText: 'Kembali ke Layar Utama'
                }).then(() => {
                    window.location.href = '<?= base_url("voting/cancel"); ?>';
                });
            }
        }, 1000);

        // 2. Soft Carousel Slider Buttons & Auto-Centering Helper
        function checkCentering() {
            ['sliderKetua', 'sliderPengawas'].forEach(function(id) {
                const slider = document.getElementById(id);
                if (!slider) return;
                const container = slider.closest('.category-container');
                const navGroup = container ? container.querySelector('.slider-nav-group') : null;
                
                // If items fit inside container width without overflow
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

        // 3. Candidate Selection Handler
        $('.candidate-card').on('click', function(e) {
            // Ignore click if clicking the detail modal trigger
            if ($(e.target).closest('.btn-detail-trigger').length) return;

            const $card = $(this);
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
        });

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(s) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[s];
            });
        }

        // 4. Wizard Step Navigation Logic
        function goToStep(step) {
            if (step < 1 || step > 3) return;

            // Validation: Cannot advance to Step 2 without Ketua
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

            // Validation: Cannot advance to Step 3 without both
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

            // Transition Steps
            $('.wizard-step').removeClass('active');
            $(`#wizardStep${step}`).addClass('active');

            // Trigger slider centering check on tab display
            setTimeout(checkCentering, 50);

            // Scroll directly to the active candidate container
            setTimeout(function() {
                scrollToActiveStep(true);
            }, 30);

            syncUI();
        }

        function syncUI() {
            const safeKetua = escapeHtml(selectedKetuaName);
            const safePengawas = escapeHtml(selectedPengawasName);

            // A. Update Stepper Tabs
            $('.stepper-item').removeClass('active completed');
            $('.stepper-line').removeClass('completed');

            // Step 1: Calon Ketua
            if (selectedKetua && currentStep > 1) {
                $('#stepperTab1').addClass('completed');
                $('#stepperLine1').addClass('completed');
            } else if (currentStep === 1) {
                $('#stepperTab1').addClass('active');
            }

            // Step 2: Calon Pengawas
            if (selectedPengawas && currentStep > 2) {
                $('#stepperTab2').addClass('completed');
                $('#stepperLine2').addClass('completed');
            } else if (currentStep === 2) {
                $('#stepperTab2').addClass('active');
                if (selectedKetua) $('#stepperLine1').addClass('completed');
            }

            // Step 3: Konfirmasi Suara
            if (currentStep === 3) {
                $('#stepperTab3').addClass('active');
                if (selectedKetua) $('#stepperLine1').addClass('completed');
                if (selectedPengawas) $('#stepperLine2').addClass('completed');
            }

            // B. Update Step 3 Review Elements
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
                $('#reviewVisionKetua').html('<em>Silakan kembali ke Langkah 1 untuk memilih Calon Ketua.</em>');
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
                $('#reviewVisionPengawas').html('<em>Silakan kembali ke Langkah 2 untuk memilih Calon Pengawas.</em>');
                $('#wrapReviewDetailPengawas').hide();
            }

            // C. Update Sticky Bottom Footer Controls
            if (currentStep === 1) {
                $btnPrev.css('visibility', 'hidden');
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
                $btnPrev.css('visibility', 'visible').html('<i class="fas fa-arrow-left me-2"></i> Calon Ketua');
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
                $btnPrev.css('visibility', 'visible').html('<i class="fas fa-arrow-left me-2"></i> Calon Pengawas');
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

        // Stepper item click handlers
        $('#stepperTab1').on('click', function() { goToStep(1); });
        $('#stepperTab2').on('click', function() { goToStep(2); });
        $('#stepperTab3').on('click', function() { goToStep(3); });

        // Bottom wizard button handlers
        $btnNext.on('click', function() {
            if (currentStep === 1) {
                goToStep(2);
            } else if (currentStep === 2) {
                goToStep(3);
            }
        });

        $btnPrev.on('click', function() {
            if (currentStep === 2) {
                goToStep(1);
            } else if (currentStep === 3) {
                goToStep(2);
            }
        });

        $('#btnChangeKetua').on('click', function() { goToStep(1); });
        $('#btnChangePengawas').on('click', function() { goToStep(2); });

        // 5. Vision & Mission Modal Helper & Parsing
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
            $('#modalCandNumLabel').text(cat + ' • Urut 0' + num);
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

        $('.btn-detail-trigger').on('click', function(e) {
            e.stopPropagation();
            openDetailModal(
                $(this).data('name'),
                $(this).data('num'),
                $(this).data('category'),
                $(this).data('photo'),
                $(this).data('vision'),
                $(this).data('mission')
            );
        });

        $('#btnReviewDetailKetua').on('click', function(e) {
            e.preventDefault();
            if (!selectedKetua) return;
            openDetailModal(
                selectedKetuaName,
                selectedKetuaNum,
                'Ketua Koperasi',
                selectedKetuaPhoto,
                selectedKetuaVision,
                selectedKetuaMission
            );
        });

        $('#btnReviewDetailPengawas').on('click', function(e) {
            e.preventDefault();
            if (!selectedPengawas) return;
            openDetailModal(
                selectedPengawasName,
                selectedPengawasNum,
                'Pengawas Koperasi',
                selectedPengawasPhoto,
                selectedPengawasVision,
                selectedPengawasMission
            );
        });

        // 6. Form Submission with Confirmation
        $form.on('submit', function(e) {
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

            const safeKetua = escapeHtml(selectedKetuaName);
            const safePengawas = escapeHtml(selectedPengawasName);

            Swal.fire({
                title: 'Konfirmasi Pilihan Suara',
                html: `
                    <div class="text-start p-3 bg-light rounded-3 border small">
                        <div class="mb-2">
                            <strong>Calon Ketua Terpilih:</strong><br>
                            <span class="text-success fw-bold fs-6">${safeKetua}</span>
                        </div>
                        <div>
                            <strong>Calon Pengawas Terpilih:</strong><br>
                            <span class="text-primary fw-bold fs-6">${safePengawas}</span>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-muted small">Pilihan suara Anda dijamin asas LUBER JURDIL dan bersifat final setelah dikirim.</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fas fa-check me-1"></i> Ya, Kirim Suara',
                cancelButtonText: 'Periksa Ulang'
            }).then((res) => {
                if (res.isConfirmed) {
                    executeSubmission();
                }
            });
        });

        function executeSubmission() {
            clearInterval(timerInterval);
            $btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Merekam Suara...');

            $.ajax({
                url: '<?= base_url("voting/submit_vote"); ?>',
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        window.location.href = res.redirect;
                    } else {
                        Swal.fire({
                            title: 'Perekaman Gagal',
                            text: res.message,
                            icon: 'error',
                            confirmButtonColor: '#059669'
                        }).then(() => {
                            window.location.href = '<?= base_url("voting"); ?>';
                        });
                    }
                },
                error: function(xhr) {
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
                        window.location.href = '<?= base_url("voting"); ?>';
                    });
                }
            });
        }

        function scrollToActiveStep(smooth = true) {
            const $target = $(`#wizardStep${currentStep}`);
            if ($target.length) {
                const headerHeight = $('.ballot-header').outerHeight() || 72;
                const targetTop = Math.max(0, $target.offset().top - headerHeight - 12);
                window.scrollTo({
                    top: targetTop,
                    behavior: smooth ? 'smooth' : 'auto'
                });
            }
        }

        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        // Initialize state
        syncUI();

        // Direct view focus to candidates list without manual scroll
        setTimeout(function() { scrollToActiveStep(false); }, 40);
        setTimeout(function() { scrollToActiveStep(false); }, 150);
        setTimeout(function() { scrollToActiveStep(false); }, 400);

        $(window).on('load', function() {
            scrollToActiveStep(false);
        });
    });
    </script>
</body>
</html>
