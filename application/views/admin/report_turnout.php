<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Partisipasi Pemilih - <?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi'); ?></title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
    <script src="<?= base_url('assets/vendor/jquery/jquery-3.6.0.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-3 col-lg-2 p-3 admin-sidebar d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-4 px-2">
                    <i class="fas fa-landmark text-success fs-4"></i>
                    <div>
                        <div class="fw-bold text-white small"><?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi'); ?></div>
                        <span class="badge bg-secondary" style="font-size: 0.65rem;">Panel Pengawas</span>
                    </div>
                </div>

                <div class="d-flex flex-column gap-1 flex-grow-1">
                    <a href="<?= base_url('admin'); ?>" class="<?= ($current_page === 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard &amp; Hasil</span>
                    </a>
                    <a href="<?= base_url('admin/candidates'); ?>" class="<?= ($current_page === 'candidates') ? 'active' : ''; ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>Manajemen Calon</span>
                    </a>
                    <a href="<?= base_url('admin/voters'); ?>" class="<?= ($current_page === 'voters') ? 'active' : ''; ?>">
                        <i class="fas fa-id-card"></i>
                        <span>DPT &amp; Kartu RFID</span>
                    </a>
                    <a href="<?= base_url('admin/report_turnout'); ?>" class="<?= ($current_page === 'report_turnout') ? 'active' : ''; ?>">
                        <i class="fas fa-user-check"></i>
                        <span>Laporan Partisipasi</span>
                    </a>
                    <a href="<?= base_url('admin/audit_votes'); ?>" class="<?= ($current_page === 'audit_votes') ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i>
                        <span>Audit Jejak Suara</span>
                    </a>
                    <a href="<?= base_url('admin/doorprize'); ?>" class="<?= ($current_page === 'doorprize') ? 'active' : ''; ?>">
                        <i class="fas fa-gift"></i>
                        <span>Undian Doorprize</span>
                    </a>
                    <a href="<?= base_url('admin/export_results'); ?>" target="_blank">
                        <i class="fas fa-file-signature"></i>
                        <span>Cetak Berita Acara</span>
                    </a>
                    <a href="<?= base_url('voting'); ?>" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Bilik Suara (Kiosk)</span>
                    </a>
                </div>

                <div class="border-top border-secondary pt-3 mt-auto px-2">
                    <div class="text-white small fw-semibold mb-1"><?= htmlspecialchars($this->session->userdata('admin_name')); ?></div>
                    <div class="text-muted small mb-2 text-capitalize">Peran: <?= htmlspecialchars($this->session->userdata('admin_role')); ?></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-light btn-sm py-0 px-2" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalChangePassword">
                            <i class="fas fa-key me-1"></i> Ganti Password
                        </button>
                        <a href="<?= base_url('admin/logout'); ?>" class="text-danger p-0 d-inline-flex align-items-center gap-1 small">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 col-lg-10 p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h3 fw-bold mb-1">Laporan Partisipasi Pemilih</h1>
                        <p class="text-muted small mb-0">Statistik kehadiran anggota dan pemantauan hak pilih DPT secara real-time</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= base_url('admin/export_turnout_excel'); ?>" class="btn btn-success btn-sm fw-semibold">
                            <i class="fas fa-file-excel me-1"></i> Unduh Excel (.xlsx)
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.location.reload();">
                            <i class="fas fa-sync-alt me-1"></i> Segarkan Data
                        </button>
                    </div>
                </div>

                <!-- Turnout Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="admin-card h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small fw-semibold text-uppercase">Total Hak Suara (DPT)</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0"><?= number_format($stats['total_voters'], 0, ',', '.'); ?></h3>
                                    <small class="text-muted">Total anggota terdaftar aktif</small>
                                </div>
                                <div class="p-3 bg-light rounded-3 border">
                                    <i class="fas fa-users text-secondary fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="admin-card h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small fw-semibold text-uppercase">Sudah Memilih</span>
                                    <h3 class="fw-bold text-success mt-2 mb-0">
                                        <?= number_format($stats['voted_count'], 0, ',', '.'); ?>
                                        <span class="fs-6 fw-normal text-muted">(<?= $stats['voted_pct']; ?>%)</span>
                                    </h3>
                                    <div class="progress mt-2" style="height: 6px; width: 140px;">
                                        <div class="progress-bar bg-success" style="width: <?= $stats['voted_pct']; ?>%;"></div>
                                    </div>
                                </div>
                                <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success-subtle">
                                    <i class="fas fa-check-double text-success fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="admin-card h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small fw-semibold text-uppercase">Belum Memilih</span>
                                    <h3 class="fw-bold text-warning mt-2 mb-0">
                                        <?= number_format($stats['unvoted_count'], 0, ',', '.'); ?>
                                        <span class="fs-6 fw-normal text-muted">(<?= $stats['unvoted_pct']; ?>%)</span>
                                    </h3>
                                    <div class="progress mt-2" style="height: 6px; width: 140px;">
                                        <div class="progress-bar bg-warning" style="width: <?= $stats['unvoted_pct']; ?>%;"></div>
                                    </div>
                                </div>
                                <div class="p-3 bg-warning bg-opacity-10 rounded-3 border border-warning-subtle">
                                    <i class="fas fa-user-clock text-warning fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hourly Distribution Breakdown -->
                <div class="admin-card mb-4">
                    <h5 class="fw-bold mb-1">Sebaran Jam Kedatangan Pemilih</h5>
                    <p class="text-muted small mb-3">Distribusi volume suara masuk per jam untuk evaluasi kepadatan bilik suara</p>

                    <?php if (empty($stats['hourly'])): ?>
                    <div class="p-4 text-center text-muted bg-light rounded-2 border">
                        <i class="fas fa-chart-bar fs-3 mb-2 text-secondary"></i>
                        <p class="mb-0">Belum ada suara yang masuk pada sesi pemungutan suara ini.</p>
                    </div>
                    <?php else: ?>
                    <div class="row g-2">
                        <?php 
                        $maxHourly = 1;
                        foreach ($stats['hourly'] as $h) {
                            if ((int)$h['count'] > $maxHourly) $maxHourly = (int)$h['count'];
                        }
                        foreach ($stats['hourly'] as $h): 
                            $barPct = round(((int)$h['count'] / $maxHourly) * 100);
                        ?>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="p-3 border rounded-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold small text-dark"><?= htmlspecialchars($h['time_slot']); ?> - <?= date('H:00', strtotime($h['time_slot'] . ' +1 hour')); ?></span>
                                    <span class="badge bg-dark fw-bold"><?= (int)$h['count']; ?> Suara</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $barPct; ?>%;"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Unvoted Voters Table -->
                <div class="admin-card">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">Daftar Anggota Belum Memilih</h5>
                            <p class="text-muted small mb-0">Total <?= number_format(count($unvoted_voters), 0, ',', '.'); ?> anggota dalam daftar pemilih yang belum menggunakan hak pilih</p>
                        </div>

                        <form method="GET" action="<?= base_url('admin/report_turnout'); ?>" class="d-flex gap-2">
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama atau No. Anggota..." value="<?= htmlspecialchars($search ?? ''); ?>" style="width: 240px;">
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (!empty($search)): ?>
                            <a href="<?= base_url('admin/report_turnout'); ?>" class="btn btn-outline-danger btn-sm" title="Hapus Filter">
                                <i class="fas fa-times"></i>
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">No</th>
                                    <th style="width: 160px;">No. Anggota</th>
                                    <th>Nama Anggota</th>
                                    <th style="width: 140px;" class="text-center">Status Akun</th>
                                    <th style="width: 140px;" class="text-center">Aksi Bantuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($unvoted_voters)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <?php if (!empty($search)): ?>
                                        Tidak ditemukan anggota belum memilih yang cocok dengan pencarian "<strong><?= htmlspecialchars($search); ?></strong>".
                                        <?php else: ?>
                                        <div class="text-success fw-semibold">
                                            <i class="fas fa-check-circle me-1"></i> Luar biasa! Seluruh anggota terdaftar telah menggunakan hak suaranya (Partisipasi 100%).
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php $no = 1; foreach ($unvoted_voters as $v): ?>
                                <tr>
                                    <td class="text-center text-muted small"><?= $no++; ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($v['member_number']); ?></span>
                                    </td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($v['name']); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= ($v['status'] === 'active') ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?= strtoupper(htmlspecialchars($v['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('admin/voters?q=' . urlencode($v['member_number'])); ?>" class="btn btn-outline-secondary btn-sm py-1 px-2" style="font-size: 0.75rem;">
                                            <i class="fas fa-id-card me-1"></i> Periksa Kartu
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <?php $this->load->view('admin/modal_change_password', array('redirect_to' => 'admin/report_turnout')); ?>
</body>
</html>
