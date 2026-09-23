<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suara Sah Berhasil Direkam - E-Voting Koperasi</title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="receipt-card">
            <div class="mb-3 text-success">
                <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
            </div>

            <h1 class="h3 fw-bold text-dark mb-2">Terima Kasih atas Hak Suara Anda!</h1>
            <p class="text-secondary mb-3">Pilihan Anda telah sah terenkripsi dan tercatat ke dalam ledger pemilihan koperasi.</p>

            <div class="text-start bg-light p-3 rounded-3 border mb-3">
                <span class="text-muted small d-block mb-1">Kode Tanda Terima Suara (Audit Token):</span>
                <div class="receipt-code-box my-1">
                    <?= htmlspecialchars($receipt_token); ?>
                </div>
                <small class="text-muted d-block">
                    <i class="fas fa-shield-alt text-success me-1"></i>
                    Kode acak ini menjamin bahwa suara Anda telah dihitung dalam sistem tanpa mengungkapkan siapa kandidat yang Anda pilih (Asas Rahasia).
                </small>
            </div>

            <p class="text-muted small mb-4">
                Halaman ini akan kembali ke layar awal dalam <strong id="countdown">6</strong> detik...
            </p>

            <a href="<?= base_url('voting'); ?>" class="btn btn-kop-primary px-4">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Layar Awal
            </a>
        </div>
    </div>

    <script>
    let timeLeft = 6;
    const countEl = document.getElementById('countdown');
    const interval = setInterval(function() {
        timeLeft--;
        if (countEl) countEl.innerText = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(interval);
            window.location.href = '<?= base_url("voting"); ?>';
        }
    }, 1000);
    </script>
</body>
</html>
