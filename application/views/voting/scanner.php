<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilik E-Voting Koperasi - Tap Kartu Anggota</title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
    <script src="<?= base_url('assets/vendor/jquery/jquery-3.6.0.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.all.min.js'); ?>"></script>
</head>
<body class="bg-light">

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
            <div class="rfid-indicator" id="rfidIndicator">
                <i class="fas fa-id-card"></i>
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

            <!-- Hidden RFID Wedge Input Form -->
            <form id="rfidForm" class="visually-hidden">
                <input type="password" id="rfidUid" name="rfid_uid" autocomplete="off" autofocus>
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            </form>
        </div>
    </main>

    <script>
    $(document).ready(function() {
        const $input = $('#rfidUid');
        const $form = $('#rfidForm');
        const $indicator = $('#rfidIndicator');
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
            $indicator.addClass('scanning');
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
                        $indicator.removeClass('scanning');
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
                    $indicator.removeClass('scanning');
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
</body>
</html>
