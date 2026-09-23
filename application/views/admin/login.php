<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Panitia &amp; Pengawas - E-Voting Koperasi</title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
</head>
<body class="bg-light">

    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow-sm border p-4" style="max-width: 420px; width: 100%; border-radius: var(--kop-radius);">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-light text-success rounded-circle mb-2" style="width: 64px; height: 64px;">
                    <i class="fas fa-user-shield fs-2"></i>
                </div>
                <h1 class="h4 fw-bold mb-1">Portal Panitia &amp; Pengawas</h1>
                <p class="text-muted small"><?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi'); ?></p>
            </div>

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger py-2 small mb-3">
                <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/authenticate'); ?>" method="POST">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus autocomplete="username">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-dark">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-kop-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-1"></i> Masuk ke Panel
                </button>

                <div class="text-center">
                    <a href="<?= base_url('voting'); ?>" class="text-decoration-none small text-muted">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Layar Bilik Suara
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
