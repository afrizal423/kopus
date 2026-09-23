<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Suara Digital - E-Voting Koperasi</title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
    <script src="<?= base_url('assets/vendor/jquery/jquery-3.6.0.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.all.min.js'); ?>"></script>
    <style>
        body { padding-bottom: 120px; }
    </style>
</head>
<body>

    <!-- Sticky Header with Voter Info & Timeout -->
    <header class="ballot-header">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted small d-block">Pemilih Terverifikasi</span>
                <span class="fw-bold text-dark fs-5"><?= htmlspecialchars($voter_name); ?></span>
                <span class="badge bg-light text-secondary border ms-2">No. Anggota: <?= htmlspecialchars($member_number); ?></span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="ballot-timer" title="Sisa batas waktu bilik suara">
                    <i class="fas fa-stopwatch"></i>
                    <span>Sisa Waktu: <strong id="timerCountdown"><?= (int)$timeout_seconds; ?></strong>s</span>
                </div>
                <a href="<?= base_url('voting/cancel'); ?>" class="btn btn-outline-danger btn-sm" id="btnCancel">
                    <i class="fas fa-times me-1"></i> Batal &amp; Keluar
                </a>
            </div>
        </div>
    </header>

    <main class="container my-4">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold mb-1">Surat Suara Elektronik</h1>
            <p class="text-secondary">Pilihlah satu calon Ketua dan satu calon Pengawas dengan mengetuk kartu pilihan Anda.</p>
        </div>

        <form id="ballotForm">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            
            <div class="row g-4">
                <!-- Section: Calon Ketua -->
                <div class="col-lg-6 ballot-col-ketua">
                    <div class="category-header category-header-ketua">
                        <h2 class="category-title">
                            <i class="fas fa-user-tie text-success me-2"></i>Calon Ketua Koperasi
                        </h2>
                        <span class="category-badge category-badge-ketua">
                            <i class="fas fa-check-square me-1"></i>Pilih 1 Calon
                        </span>
                    </div>

                    <div class="candidate-scroll-row">
                        <?php foreach ($ketua_candidates as $c): ?>
                        <div class="candidate-col">
                            <div class="candidate-box" data-category="ketua" data-id="<?= $c['id']; ?>" data-name="<?= htmlspecialchars($c['name']); ?>">
                                <div class="candidate-num-tag"><?= $c['candidate_number']; ?></div>
                                <div class="candidate-check-badge">
                                    <i class="fas fa-check"></i> Terpilih
                                </div>

                                <div class="candidate-photo-wrapper">
                                    <img src="<?= base_url($c['photo']); ?>" alt="<?= htmlspecialchars($c['name']); ?>" class="candidate-photo-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                                </div>

                                <div class="candidate-name"><?= htmlspecialchars($c['name']); ?></div>
                                <div class="candidate-vision-preview"><?= htmlspecialchars($c['vision']); ?></div>

                                <button type="button" class="btn-detail-trigger mt-auto" 
                                        data-name="<?= htmlspecialchars($c['name']); ?>"
                                        data-num="<?= $c['candidate_number']; ?>"
                                        data-category="Ketua Koperasi"
                                        data-vision="<?= htmlspecialchars($c['vision']); ?>"
                                        data-mission="<?= htmlspecialchars($c['mission']); ?>">
                                    <i class="fas fa-file-alt me-1"></i> Visi &amp; Misi
                                </button>
                                <input type="radio" name="selections[ketua]" value="<?= $c['id']; ?>" class="d-none">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Section: Calon Pengawas -->
                <div class="col-lg-6 ballot-col-pengawas">
                    <div class="category-header category-header-pengawas">
                        <h2 class="category-title text-primary">
                            <i class="fas fa-clipboard-check text-primary me-2"></i>Calon Pengawas Koperasi
                        </h2>
                        <span class="category-badge category-badge-pengawas">
                            <i class="fas fa-check-square me-1"></i>Pilih 1 Calon
                        </span>
                    </div>

                    <div class="candidate-scroll-row">
                        <?php foreach ($pengawas_candidates as $c): ?>
                        <div class="candidate-col">
                            <div class="candidate-box" data-category="pengawas" data-id="<?= $c['id']; ?>" data-name="<?= htmlspecialchars($c['name']); ?>">
                                <div class="candidate-num-tag candidate-num-tag-pengawas"><?= $c['candidate_number']; ?></div>
                                <div class="candidate-check-badge candidate-check-badge-pengawas">
                                    <i class="fas fa-check"></i> Terpilih
                                </div>

                                <div class="candidate-photo-wrapper">
                                    <img src="<?= base_url($c['photo']); ?>" alt="<?= htmlspecialchars($c['name']); ?>" class="candidate-photo-img" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                                </div>

                                <div class="candidate-name"><?= htmlspecialchars($c['name']); ?></div>
                                <div class="candidate-vision-preview"><?= htmlspecialchars($c['vision']); ?></div>

                                <button type="button" class="btn-detail-trigger mt-auto" 
                                        data-name="<?= htmlspecialchars($c['name']); ?>"
                                        data-num="<?= $c['candidate_number']; ?>"
                                        data-category="Pengawas Koperasi"
                                        data-vision="<?= htmlspecialchars($c['vision']); ?>"
                                        data-mission="<?= htmlspecialchars($c['mission']); ?>">
                                    <i class="fas fa-file-alt me-1"></i> Visi &amp; Misi
                                </button>
                                <input type="radio" name="selections[pengawas]" value="<?= $c['id']; ?>" class="d-none">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Bottom Floating Action Bar -->
            <div class="bottom-submit-bar d-flex justify-content-between align-items-center">
                <div class="d-none d-md-block">
                    <span class="text-secondary small">Status Pilihan: </span>
                    <span id="selectionSummary" class="fw-semibold text-dark">Belum memilih calon</span>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button type="submit" id="btnConfirmSubmit" class="btn btn-kop-primary btn-lg px-4" disabled>
                        <i class="fas fa-envelope-open-text me-2"></i> Kirim Pilihan Suara
                    </button>
                </div>
            </div>
        </form>
    </main>

    <!-- Modal Visi & Misi -->
    <div class="modal fade" id="modalCandidateDetail" tabindex="-1" aria-labelledby="modalCandidateLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="badge bg-success mb-1" id="modalCatBadge">Ketua</span>
                        <h5 class="modal-title fw-bold" id="modalCandName">Nama Calon</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold text-dark mb-1">Visi:</h6>
                    <p id="modalCandVision" class="text-secondary small mb-3"></p>
                    
                    <h6 class="fw-bold text-dark mb-1">Misi:</h6>
                    <div id="modalCandMission" class="text-secondary small" style="white-space: pre-line;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-kop-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const $form = $('#ballotForm');
        const $btnSubmit = $('#btnConfirmSubmit');
        const $summary = $('#selectionSummary');
        const $timer = $('#timerCountdown');
        const modalDetail = new bootstrap.Modal(document.getElementById('modalCandidateDetail'));

        let selectedKetua = null;
        let selectedPengawas = null;
        let selectedKetuaName = '';
        let selectedPengawasName = '';

        // 1. Inactivity Countdown Timer
        let secondsLeft = parseInt($timer.text()) || 120;
        const timerInterval = setInterval(function() {
            secondsLeft--;
            $timer.text(secondsLeft);
            if (secondsLeft <= 0) {
                clearInterval(timerInterval);
                Swal.fire({
                    title: 'Waktu Habis',
                    text: 'Batas waktu di bilik suara telah berakhir.',
                    icon: 'info',
                    confirmButtonColor: '#0f5132',
                    confirmButtonText: 'Kembali'
                }).then(() => {
                    window.location.href = '<?= base_url("voting/cancel"); ?>';
                });
            }
        }, 1000);

        // 2. Candidate Selection
        $('.candidate-box').on('click', function(e) {
            // Ignore click if clicking the detail button directly
            if ($(e.target).closest('.btn-detail-trigger').length) return;

            const $card = $(this);
            const category = $card.data('category');
            const id = $card.data('id');
            const name = $card.data('name');

            // Reset sibling cards in this category
            $(`.candidate-box[data-category="${category}"]`).removeClass('selected');
            $card.addClass('selected');

            $(`input[name="selections[${category}]"][value="${id}"]`).prop('checked', true);

            if (category === 'ketua') {
                selectedKetua = id;
                selectedKetuaName = name;
            } else {
                selectedPengawas = id;
                selectedPengawasName = name;
            }

            updateSummary();
        });

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(s) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[s];
            });
        }

        function updateSummary() {
            const safeKetua = escapeHtml(selectedKetuaName);
            const safePengawas = escapeHtml(selectedPengawasName);

            if (selectedKetua && selectedPengawas) {
                $summary.html(`<span class="text-success"><i class="fas fa-check-circle"></i> Lengkap:</span> Ketua: <b>${safeKetua}</b> & Pengawas: <b>${safePengawas}</b>`);
                $btnSubmit.prop('disabled', false);
            } else if (selectedKetua) {
                $summary.html(`Ketua: <b>${safeKetua}</b> | <span class="text-warning">Belum memilih Pengawas</span>`);
                $btnSubmit.prop('disabled', true);
            } else if (selectedPengawas) {
                $summary.html(`<span class="text-warning">Belum memilih Ketua</span> | Pengawas: <b>${safePengawas}</b>`);
                $btnSubmit.prop('disabled', true);
            } else {
                $summary.text('Belum memilih calon');
                $btnSubmit.prop('disabled', true);
            }
        }

        // 3. Vision & Mission Modal
        $('.btn-detail-trigger').on('click', function(e) {
            e.stopPropagation();
            const name = $(this).data('name');
            const num = $(this).data('num');
            const cat = $(this).data('category');
            const vision = $(this).data('vision');
            const mission = $(this).data('mission');

            $('#modalCatBadge').text(cat + ' - No. ' + num);
            $('#modalCandName').text(name);
            $('#modalCandVision').text(vision || 'Tidak ada keterangan visi.');
            $('#modalCandMission').text(mission || 'Tidak ada keterangan misi.');
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
                    confirmButtonColor: '#0f5132'
                });
                return;
            }

            const safeKetua = escapeHtml(selectedKetuaName);
            const safePengawas = escapeHtml(selectedPengawasName);

            Swal.fire({
                title: 'Konfirmasi Pilihan Anda',
                html: `
                    <div class="text-start p-3 bg-light rounded-3 border small">
                        <div class="mb-2"><strong>Calon Ketua:</strong><br><span class="text-success fw-bold fs-6">${safeKetua}</span></div>
                        <div><strong>Calon Pengawas:</strong><br><span class="text-primary fw-bold fs-6">${safePengawas}</span></div>
                    </div>
                    <p class="mt-3 mb-0 text-muted small">Pilihan yang telah dikirim bersifat final dan tidak dapat diubah kembali.</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f5132',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Kirim Suara',
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
                            confirmButtonColor: '#0f5132'
                        }).then(() => {
                            window.location.href = '<?= base_url("voting"); ?>';
                        });
                    }
                },
                error: function(xhr) {
                    let errMsg = 'Terjadi kendala saat menyimpan suara.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Kesalahan Sistem',
                        text: errMsg,
                        icon: 'error',
                        confirmButtonColor: '#0f5132'
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
