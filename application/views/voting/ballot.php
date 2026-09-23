<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Compute clean voter initials (e.g. "Afrizal Muhammad Yasin" -> "AY")
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
$election_title = !empty($settings['election_title']) ? $settings['election_title'] : 'Rapat Anggota Tahunan (RAT) 2025';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Suara Elektronik - <?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi'); ?></title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
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
                Pilihlah <strong>1 (satu) Calon Ketua</strong> dan <strong>1 (satu) Calon Pengawas</strong> dengan mengetuk kartu kandidat pilihan Anda di bawah ini.
            </p>

            <!-- Selection Status Summary Pills -->
            <div class="d-flex justify-content-center flex-wrap gap-3 mt-3">
                <div class="selection-pill selection-pill-ketua" id="pillKetua">
                    <span class="pill-dot">●</span>
                    <span>Ketua: <strong id="pillKetuaText">Belum Dipilih</strong></span>
                </div>
                <div class="selection-pill selection-pill-pengawas" id="pillPengawas">
                    <span class="pill-dot">●</span>
                    <span>Pengawas: <strong id="pillPengawasText">Belum Dipilih</strong></span>
                </div>
            </div>
        </div>

        <form id="ballotForm">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            
            <div class="row g-4 mb-2">
                <!-- Section 1: Calon Ketua Koperasi (Emerald Green) -->
                <div class="col-xl-6 col-12">
                    <div class="category-container category-container-ketua">
                        <div class="category-header-bar">
                            <div class="d-flex align-items-center gap-3">
                                <div class="category-icon-box category-icon-ketua">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h2 class="category-card-title m-0">Calon Ketua Koperasi</h2>
                                    <div class="category-card-subtitle">Masa Bakti Periode 2025 - 2028</div>
                                </div>
                            </div>
                            <div class="category-quota-badge quota-ketua">
                                <i class="fas fa-check me-1"></i> Pilih 1 Calon
                            </div>
                        </div>

                        <div class="candidate-scroll-row">
                            <?php foreach ($ketua_candidates as $c): ?>
                            <div class="candidate-col">
                                <div class="candidate-card" data-category="ketua" data-id="<?= $c['id']; ?>" data-name="<?= htmlspecialchars($c['name']); ?>">
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
                                    <div class="candidate-quote-preview">"<?= htmlspecialchars($c['vision'] ?: 'Dedikasi terbaik untuk kemajuan seluruh anggota koperasi.'); ?>"</div>

                                    <div class="candidate-actions-group mt-auto">
                                        <button type="button" class="btn-card-vision mb-2 btn-detail-trigger" 
                                                data-name="<?= htmlspecialchars($c['name']); ?>"
                                                data-num="<?= $c['candidate_number']; ?>"
                                                data-category="Ketua Koperasi"
                                                data-photo="<?= base_url($c['photo']); ?>"
                                                data-vision="<?= htmlspecialchars($c['vision']); ?>"
                                                data-mission="<?= htmlspecialchars($c['mission']); ?>">
                                            <i class="fas fa-file-alt me-1"></i> Visi &amp; Misi
                                        </button>
                                        <button type="button" class="btn-card-action">
                                            <span class="text-default">Pilih Calon</span>
                                            <span class="text-selected"><i class="fas fa-check"></i> Pilihan Anda</span>
                                        </button>
                                    </div>

                                    <input type="radio" name="selections[ketua]" value="<?= $c['id']; ?>" class="d-none">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Calon Pengawas Koperasi (Royal Blue) -->
                <div class="col-xl-6 col-12">
                    <div class="category-container category-container-pengawas">
                        <div class="category-header-bar">
                            <div class="d-flex align-items-center gap-3">
                                <div class="category-icon-box category-icon-pengawas">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <h2 class="category-card-title m-0">Calon Pengawas Koperasi</h2>
                                    <div class="category-card-subtitle">Masa Bakti Periode 2025 - 2028</div>
                                </div>
                            </div>
                            <div class="category-quota-badge quota-pengawas">
                                <i class="fas fa-check me-1"></i> Pilih 1 Calon
                            </div>
                        </div>

                        <div class="candidate-scroll-row">
                            <?php foreach ($pengawas_candidates as $c): ?>
                            <div class="candidate-col">
                                <div class="candidate-card" data-category="pengawas" data-id="<?= $c['id']; ?>" data-name="<?= htmlspecialchars($c['name']); ?>">
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
                                    <div class="candidate-quote-preview">"<?= htmlspecialchars($c['vision'] ?: 'Pengawasan independen, akuntabel, dan berintegritas demi koperasi berkeadilan.'); ?>"</div>

                                    <div class="candidate-actions-group mt-auto">
                                        <button type="button" class="btn-card-vision mb-2 btn-detail-trigger" 
                                                data-name="<?= htmlspecialchars($c['name']); ?>"
                                                data-num="<?= $c['candidate_number']; ?>"
                                                data-category="Pengawas Koperasi"
                                                data-photo="<?= base_url($c['photo']); ?>"
                                                data-vision="<?= htmlspecialchars($c['vision']); ?>"
                                                data-mission="<?= htmlspecialchars($c['mission']); ?>">
                                            <i class="fas fa-file-alt me-1"></i> Visi &amp; Misi
                                        </button>
                                        <button type="button" class="btn-card-action">
                                            <span class="text-default">Pilih Calon</span>
                                            <span class="text-selected"><i class="fas fa-check"></i> Pilihan Anda</span>
                                        </button>
                                    </div>

                                    <input type="radio" name="selections[pengawas]" value="<?= $c['id']; ?>" class="d-none">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trust Guarantee LUBER JURDIL -->
            <div class="ballot-trust-guarantee text-center">
                <i class="fas fa-lock text-success me-1"></i> Pilihan Anda dijamin <strong>Langsung, Umum, Bebas, Rahasia, Jujur dan Adil (LUBER JURDIL)</strong> dengan enkripsi 256-bit.
            </div>

            <!-- Sticky Bottom Action & Confirmation Bar -->
            <div class="ballot-sticky-footer">
                <div class="container-fluid px-lg-4 px-2 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="sticky-status-circle" id="stickyStatusCircle">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="sticky-status-label">STATUS PILIHAN:</span>
                                <span id="statusCompletenessBadge" class="badge-completeness badge-incomplete">Belum Lengkap (0 dari 2)</span>
                            </div>
                            <div id="stickySelectionSummary" class="sticky-summary-text">
                                Ketua: <span class="text-muted">Belum Dipilih</span> • Pengawas: <span class="text-muted">Belum Dipilih</span>
                            </div>
                        </div>
                    </div>
                    <div class="ms-auto">
                        <button type="submit" id="btnConfirmSubmit" class="btn-submit-ballot" disabled>
                            <i class="fas fa-inbox me-2"></i> Kirim Pilihan Suara
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- Modal Visi & Misi -->
    <div class="modal fade" id="modalCandidateDetail" tabindex="-1" aria-labelledby="modalCandidateLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
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
                        <div class="col-md-4 text-center">
                            <div class="candidate-avatar-wrap mx-auto mb-3" style="width: 130px; height: 130px;">
                                <img src="" alt="" id="modalCandPhoto" class="candidate-avatar-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                            </div>
                            <div class="fw-bold text-dark fs-5" id="modalCandNameSub"></div>
                            <div class="text-muted small" id="modalCandNumLabel"></div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                    <i class="fas fa-bullseye text-success"></i> Visi:
                                </h6>
                                <div id="modalCandVision" class="p-3 bg-light rounded-3 text-secondary border small"></div>
                            </div>
                            
                            <div>
                                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                    <i class="fas fa-list-check text-primary"></i> Misi:
                                </h6>
                                <div id="modalCandMission" class="p-3 bg-light rounded-3 text-secondary border small" style="white-space: pre-line; line-height: 1.6;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const $form = $('#ballotForm');
        const $btnSubmit = $('#btnConfirmSubmit');
        const $timer = $('#timerCountdown');
        const $timerBox = $('#timerBox');
        const modalDetail = new bootstrap.Modal(document.getElementById('modalCandidateDetail'));

        let selectedKetua = null;
        let selectedPengawas = null;
        let selectedKetuaName = '';
        let selectedPengawasName = '';

        // 1. Countdown Timer (MM:SS)
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

        // 2. Candidate Selection Interactive Handler
        $('.candidate-card').on('click', function(e) {
            // Ignore click if clicking the detail modal trigger
            if ($(e.target).closest('.btn-detail-trigger').length) return;

            const $card = $(this);
            const category = $card.data('category');
            const id = $card.data('id');
            const name = $card.data('name');

            if (category === 'ketua') {
                $(`.candidate-card[data-category="ketua"]`).removeClass('is-selected-ketua');
                $card.addClass('is-selected-ketua');
                $(`input[name="selections[ketua]"][value="${id}"]`).prop('checked', true);
                selectedKetua = id;
                selectedKetuaName = name;
            } else if (category === 'pengawas') {
                $(`.candidate-card[data-category="pengawas"]`).removeClass('is-selected-pengawas');
                $card.addClass('is-selected-pengawas');
                $(`input[name="selections[pengawas]"][value="${id}"]`).prop('checked', true);
                selectedPengawas = id;
                selectedPengawasName = name;
            }

            updateFeedbackUI();
        });

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(s) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[s];
            });
        }

        function updateFeedbackUI() {
            const safeKetua = escapeHtml(selectedKetuaName);
            const safePengawas = escapeHtml(selectedPengawasName);

            // A. Update Top Selection Pills
            if (selectedKetua) {
                $('#pillKetua').addClass('is-active');
                $('#pillKetuaText').html(`${safeKetua} <span class="fw-normal">(Terpilih)</span>`);
            } else {
                $('#pillKetua').removeClass('is-active');
                $('#pillKetuaText').text('Belum Dipilih');
            }

            if (selectedPengawas) {
                $('#pillPengawas').addClass('is-active');
                $('#pillPengawasText').html(`${safePengawas} <span class="fw-normal">(Terpilih)</span>`);
            } else {
                $('#pillPengawas').removeClass('is-active');
                $('#pillPengawasText').text('Belum Dipilih');
            }

            // B. Update Sticky Bottom Bar
            let selectedCount = (selectedKetua ? 1 : 0) + (selectedPengawas ? 1 : 0);

            let ketuaSummaryHtml = selectedKetua 
                ? `<strong class="text-success">${safeKetua}</strong>` 
                : `<span class="text-muted">Belum Dipilih</span>`;
            let pengawasSummaryHtml = selectedPengawas 
                ? `<strong class="text-primary">${safePengawas}</strong>` 
                : `<span class="text-muted">Belum Dipilih</span>`;

            $('#stickySelectionSummary').html(`Ketua: ${ketuaSummaryHtml} • Pengawas: ${pengawasSummaryHtml}`);

            if (selectedCount === 2) {
                $('#statusCompletenessBadge')
                    .removeClass('badge-incomplete')
                    .addClass('badge-complete')
                    .text('Lengkap (2 dari 2)');
                $('#stickyStatusCircle').addClass('is-complete');
                $btnSubmit.prop('disabled', false);
            } else {
                $('#statusCompletenessBadge')
                    .removeClass('badge-complete')
                    .addClass('badge-incomplete')
                    .text(`Belum Lengkap (${selectedCount} dari 2)`);
                $('#stickyStatusCircle').removeClass('is-complete');
                $btnSubmit.prop('disabled', true);
            }
        }

        // 3. Vision & Mission Modal
        $('.btn-detail-trigger').on('click', function(e) {
            e.stopPropagation();
            const name = $(this).data('name');
            const num = $(this).data('num');
            const cat = $(this).data('category');
            const photo = $(this).data('photo');
            const vision = $(this).data('vision');
            const mission = $(this).data('mission');

            $('#modalCatBadge').text(cat + ' - No. ' + num);
            if (cat.indexOf('Ketua') !== -1) {
                $('#modalCatBadge').removeClass('bg-primary').addClass('bg-success');
            } else {
                $('#modalCatBadge').removeClass('bg-success').addClass('bg-primary');
            }

            $('#modalCandName').text(name);
            $('#modalCandNameSub').text(name);
            $('#modalCandNumLabel').text(cat + ' - Urut ' + num);
            $('#modalCandPhoto').attr('src', photo);
            $('#modalCandVision').text(vision || 'Tidak ada keterangan visi khusus.');
            $('#modalCandMission').text(mission || 'Tidak ada keterangan misi khusus.');
            modalDetail.show();
        });

        // 4. Form Submission with Confirmation
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
    });
    </script>
</body>
</html>
