<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengawas &amp; Panitia - E-Voting Koperasi</title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
    <script src="<?= base_url('assets/vendor/jquery/jquery-3.6.0.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.all.min.js'); ?>"></script>
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
                    <a href="<?= base_url('admin'); ?>" class="active">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard &amp; Hasil</span>
                    </a>
                    <a href="<?= base_url('admin/candidates'); ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>Manajemen Calon</span>
                    </a>
                    <a href="<?= base_url('admin/voters'); ?>">
                        <i class="fas fa-id-card"></i>
                        <span>DPT &amp; Kartu RFID</span>
                    </a>
                    <a href="<?= base_url('admin/report_turnout'); ?>">
                        <i class="fas fa-user-check"></i>
                        <span>Laporan Partisipasi</span>
                    </a>
                    <a href="<?= base_url('admin/audit_votes'); ?>">
                        <i class="fas fa-history"></i>
                        <span>Audit Jejak Suara</span>
                    </a>
                    <a href="<?= base_url('admin/doorprize'); ?>">
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
                        <h1 class="h3 fw-bold mb-1">Monitoring &amp; Rekapitulasi Suara</h1>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($settings['election_title']); ?> - <?= htmlspecialchars($settings['election_period'] ?? ''); ?></p>
                    </div>

                    <!-- Election Status Controls -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge px-3 py-2 <?= ($settings['election_status'] === 'open') ? 'bg-success' : (($settings['election_status'] === 'paused') ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                            Status: <?= strtoupper($settings['election_status']); ?>
                        </span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalChangePassword">
                            <i class="fas fa-key me-1"></i> Ganti Password
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSettings">
                            <i class="fas fa-cog me-1"></i> Pengaturan
                        </button>
                    </div>
                </div>

                <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
                    <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Integrity Ledger & Tamper Audit Card -->
                <div class="admin-card mb-4" id="tamperCard">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 bg-light border">
                                <i class="fas fa-shield-alt text-success fs-3"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Integritas Kriptografis Database (Anti-Tamper Ledger)</h6>
                                <small class="text-muted" id="tamperStatusText">
                                    <?= htmlspecialchars($integrity['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                            <div id="tamperBadge">
                                <?php if ($integrity['is_valid']): ?>
                                <span class="tamper-badge-valid">
                                    <i class="fas fa-check-circle"></i> Database Utuh &amp; Valid
                                </span>
                                <?php else: ?>
                                <span class="tamper-badge-corrupt">
                                    <i class="fas fa-exclamation-triangle"></i> Manipulasi Terdeteksi!
                                </span>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalIncidentSOP">
                                <i class="fas fa-book-reader me-1"></i> Panduan SOP
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm fw-semibold" id="btnAuditRun">
                                <i class="fas fa-sync-alt me-1"></i> Uji Keutuhan Ledger
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Emergency Incident Action Banner (Hanya muncul jika manipulasi terdeteksi) -->
                <div class="alert alert-danger border border-danger-subtle p-3 mb-4 rounded-3 <?= ($integrity['is_valid']) ? 'd-none' : ''; ?>" id="tamperAlertBanner" role="alert">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="text-danger fs-3 lh-1 mt-1">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-danger mb-1">Peringatan Integritas: Terdeteksi Anomali pada Database Pemilihan</h6>
                                <p class="small text-danger-emphasis mb-1" id="tamperAlertMsg">
                                    <?= htmlspecialchars($integrity['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <span class="small text-muted">Prinsip Darurat: <strong>Jangan lakukan reset data secara langsung</strong> agar barang bukti digital tidak hilang.</span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mt-md-0">
                            <button type="button" class="btn btn-danger btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalIncidentSOP">
                                <i class="fas fa-list-ol me-1"></i> Langkah SOP
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm fw-semibold bg-white" id="btnQuickPause">
                                <i class="fas fa-pause-circle me-1"></i> Jeda Pemilihan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4 Metrics Row -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="admin-card">
                            <span class="text-muted small fw-semibold">Total DPT Anggota</span>
                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                <span class="fs-3 fw-bold text-dark"><?= number_format($stats['total_voters']); ?></span>
                                <span class="badge bg-light text-secondary border">Hak Suara</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="admin-card">
                            <span class="text-muted small fw-semibold">Sudah Menggunakan Hak Suara</span>
                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                <span class="fs-3 fw-bold text-success"><?= number_format($stats['voted_count']); ?></span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $stats['turnout_percentage']; ?>%</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="admin-card">
                            <span class="text-muted small fw-semibold">Belum Memilih</span>
                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                <span class="fs-3 fw-bold text-secondary"><?= number_format($stats['remaining_voters']); ?></span>
                                <span class="badge bg-light text-muted border">Tersisa</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="admin-card">
                            <span class="text-muted small fw-semibold">Total Suara Sah Tercatat</span>
                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                <span class="fs-3 fw-bold text-primary"><?= number_format($stats['total_votes_recorded']); ?></span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Kriptografis</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Count Results Row -->
                <div class="row g-4 mb-4">
                    <!-- Rekap Ketua -->
                    <div class="col-lg-6">
                        <div class="admin-card h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-user-tie text-success me-2"></i>Hasil Perolehan: Calon Ketua Koperasi
                                </h6>
                                <span class="badge bg-light text-dark border">
                                    Total Suara: <?= number_format($stats['results']['ketua']['total_category_votes']); ?>
                                </span>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($stats['results']['ketua']['candidates'] as $c): ?>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-warning text-dark fw-bold">#<?= $c['candidate_number']; ?></span>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($c['name']); ?></span>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark"><?= number_format($c['vote_count']); ?> suara</span>
                                            <span class="text-muted ms-1">(<?= $c['percentage']; ?>%)</span>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $c['percentage']; ?>%" aria-valuenow="<?= $c['percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Rekap Pengawas -->
                    <div class="col-lg-6">
                        <div class="admin-card h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-clipboard-check text-primary me-2"></i>Hasil Perolehan: Calon Pengawas Koperasi
                                </h6>
                                <span class="badge bg-light text-dark border">
                                    Total Suara: <?= number_format($stats['results']['pengawas']['total_category_votes']); ?>
                                </span>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($stats['results']['pengawas']['candidates'] as $c): ?>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-info text-dark fw-bold">#<?= $c['candidate_number']; ?></span>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($c['name']); ?></span>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark"><?= number_format($c['vote_count']); ?> suara</span>
                                            <span class="text-muted ms-1">(<?= $c['percentage']; ?>%)</span>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $c['percentage']; ?>%" aria-valuenow="<?= $c['percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audit Log Preview -->
                <div class="admin-card">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="fas fa-history text-muted me-2"></i>Log Aktivitas Pengawasan Terkini
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Aktivitas</th>
                                    <th>Pelaksana</th>
                                    <th>Alamat IP</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($audit_logs)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Belum ada catatan aktivitas.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($audit_logs as $log): ?>
                                <tr>
                                    <td class="small text-nowrap"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($log['event_type']); ?></span></td>
                                    <td class="small fw-semibold"><?= htmlspecialchars($log['actor']); ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($log['ip_address']); ?></td>
                                    <td class="small text-secondary"><?= htmlspecialchars($log['details']); ?></td>
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

    <!-- Modal Pengaturan Sesi -->
    <div class="modal fade" id="modalSettings" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="<?= base_url('admin/settings_save'); ?>" method="POST">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Pengaturan Sesi Pemilihan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Koperasi</label>
                            <input type="text" name="cooperative_name" class="form-control" value="<?= htmlspecialchars($settings['cooperative_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Judul Pemilihan</label>
                            <input type="text" name="election_title" class="form-control" value="<?= htmlspecialchars($settings['election_title'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Status Pemilihan</label>
                            <select name="election_status" class="form-select">
                                <option value="open" <?= ($settings['election_status'] === 'open') ? 'selected' : ''; ?>>Terbuka (Pemilih dapat mencoblos)</option>
                                <option value="paused" <?= ($settings['election_status'] === 'paused') ? 'selected' : ''; ?>>Dijeda Sementara</option>
                                <option value="closed" <?= ($settings['election_status'] === 'closed') ? 'selected' : ''; ?>>Ditutup</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Batas Waktu Bilik Suara (Detik)</label>
                            <input type="number" name="booth_timeout_seconds" class="form-control" value="<?= (int)($settings['booth_timeout_seconds'] ?? 120); ?>" min="30" max="600" required>
                            <small class="text-muted">Sesi bilik suara akan dibatalkan otomatis jika pemilih tidak memilih dalam durasi ini.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-kop-primary btn-sm">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal SOP Penanganan Insiden Ledger -->
    <div class="modal fade" id="modalIncidentSOP" tabindex="-1" aria-labelledby="modalIncidentSOPLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-shield-alt text-success fs-5"></i>
                        <h5 class="modal-title fw-bold" id="modalIncidentSOPLabel">SOP Penanganan Insiden Keamanan &amp; Integritas Ledger</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Callout Alert Prinsip Utama -->
                    <div class="alert alert-warning border border-warning-subtle d-flex align-items-start gap-3 mb-4 py-3" role="alert">
                        <i class="fas fa-exclamation-circle text-warning fs-4 mt-1"></i>
                        <div>
                            <div class="fw-bold text-dark mb-1">Prinsip Utama: JANGAN LANGSUNG RESET DATABASE!</div>
                            <div class="small text-secondary">Mereset database secara terburu-buru akan <strong>menghilangkan barang bukti forensik digital</strong>. Data suara yang masuk sebelum titik manipulasi masih sah dan dapat dilacak. Amankan bukti terlebih dahulu.</div>
                        </div>
                    </div>

                    <!-- 5 Langkah Penanganan -->
                    <h6 class="fw-bold text-dark mb-3">5 Fase Tindakan Tanggap Darurat</h6>
                    
                    <div class="list-group list-group-flush border rounded-3 mb-4">
                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-danger rounded-1">Fase 1</span>
                                <strong class="text-dark">Bekukan Sistem Pemilihan (Waktu Respon &lt; 3 Menit)</strong>
                            </div>
                            <p class="small text-muted mb-2">Buka menu <strong>Pengaturan Sesi Pemilihan</strong> dan ubah Status Pemilihan menjadi <code>paused</code> (dijeda). Bilik suara otomatis terkunci sehingga tidak ada suara baru yang masuk ke rantai yang sedang rusak.</p>
                            <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" id="btnSopPause">
                                <i class="fas fa-pause-circle me-1"></i> Buka Pengaturan Status Pemilihan
                            </button>
                        </div>

                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-secondary rounded-1">Fase 2</span>
                                <strong class="text-dark">Amankan Barang Bukti Forensik Digital</strong>
                            </div>
                            <p class="small text-muted mb-2">Lakukan snapshot dan backup database lengkap menggunakan MySQL dump melalui command line sebelum mengubah data apa pun:</p>
                            <code class="d-block bg-light text-dark p-2 rounded border small mb-2 user-select-all">mysqldump -u root -p vote_koperasi &gt; incident_evidence_backup.sql</code>
                            <p class="small text-muted mb-0">Simpan tangkapan layar peringatan dashboard dan catat nomor baris database yang bermasalah.</p>
                        </div>

                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-secondary rounded-1">Fase 3</span>
                                <strong class="text-dark">Investigasi &amp; Rekonsiliasi Data</strong>
                            </div>
                            <p class="small text-muted mb-1">Periksa baris suara pada tabel <code>votes</code> sesuai ID yang dilaporkan oleh sistem:</p>
                            <ul class="small text-muted ps-3 mb-2">
                                <li>Bandingkan jumlah pemilih hadir di DPT (<code>has_voted = 1</code>) dengan total baris di tabel suara. Jika selisih, ada suara yang dihapus atau disisipkan secara ilegal.</li>
                                <li>Cocokkan token tanda terima pemilih (<code>receipt_token</code>) pada baris yang rusak dengan tanda terima fisik yang dipegang pemilih.</li>
                                <li>Periksa tabel <code>audit_logs</code> untuk melacak aktivitas user dan IP yang mengakses sistem saat kejadian.</li>
                            </ul>
                        </div>

                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-secondary rounded-1">Fase 4</span>
                                <strong class="text-dark">Sidang Pleno Panitia, Pengawas, &amp; Saksi</strong>
                            </div>
                            <p class="small text-muted mb-2">Kumpulkan seluruh pihak berwenang untuk menentukan keputusan resmi:</p>
                            <div class="row g-2 small">
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light">
                                        <div class="fw-semibold text-dark">Opsi A: Koreksi Baris Terisolasi</div>
                                        <div class="text-muted">Jika kerusakan minor (1 baris), bukti token pemilih sah tersedia, dan disetujui semua saksi dalam Berita Acara Insiden.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light">
                                        <div class="fw-semibold text-dark">Opsi B: Pemungutan Suara Ulang (PSU)</div>
                                        <div class="text-muted">Jika terjadi manipulasi masif, banyak baris hilang, atau saksi menolak keabsahan data yang tercemar.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-dark rounded-1">Fase 5</span>
                                <strong class="text-dark">Prosedur Reset Terkontrol (Khusus Skenario PSU)</strong>
                            </div>
                            <p class="small text-muted mb-2">Jika diputuskan PSU dalam Berita Acara resmi, eksekusi pembersihan database:</p>
                            <pre class="bg-light text-dark p-2 rounded border small mb-0 font-monospace"><code>TRUNCATE TABLE votes;
UPDATE voters SET has_voted = 0, voted_at = NULL;
UPDATE election_settings SET setting_value = MD5(CONCAT(NOW(), UUID())) WHERE setting_key = 'ledger_secret_salt';
UPDATE election_settings SET setting_value = 'open' WHERE setting_key = 'election_status';</code></pre>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border">
                        <div class="small text-muted">
                            <i class="fas fa-file-alt me-1"></i> Dokumentasi teknis lengkap tersedia di berkas <code>docs/sop-penanganan-insiden-ledger.md</code>.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#btnAuditRun').on('click', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menguji...');

            $.ajax({
                url: '<?= base_url("admin/verify_tamper"); ?>',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1"></i> Uji Keutuhan Ledger');
                    $('#tamperStatusText').text(res.message);

                    if (res.is_valid) {
                        $('#tamperBadge').html('<span class="tamper-badge-valid"><i class="fas fa-check-circle"></i> Database Utuh & Valid</span>');
                        $('#tamperAlertBanner').slideUp();
                        Swal.fire({
                            title: 'Integritas Terverifikasi',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#0f5132'
                        });
                    } else {
                        $('#tamperBadge').html('<span class="tamper-badge-corrupt"><i class="fas fa-exclamation-triangle"></i> Manipulasi Terdeteksi!</span>');
                        $('#tamperAlertMsg').text(res.message);
                        $('#tamperAlertBanner').removeClass('d-none').hide().slideDown();
                        Swal.fire({
                            title: 'Peringatan Manipulasi Terdeteksi!',
                            text: res.message,
                            icon: 'error',
                            confirmButtonColor: '#dc2626',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-book-reader me-1"></i> Buka Panduan SOP',
                            cancelButtonText: 'Tutup'
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                var sopModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalIncidentSOP'));
                                sopModal.show();
                            }
                        });
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1"></i> Uji Keutuhan Ledger');
                    Swal.fire('Error', 'Gagal menghubungi server verifikasi.', 'error');
                }
            });
        });

        // Quick Pause handler from Emergency Banner
        $('#btnQuickPause').on('click', function() {
            $('#modalSettings select[name="election_status"]').val('paused');
            var settingsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSettings'));
            settingsModal.show();
        });

        // Pause handler from within SOP Modal
        $('#btnSopPause').on('click', function() {
            var sopModal = bootstrap.Modal.getInstance(document.getElementById('modalIncidentSOP'));
            if (sopModal) {
                sopModal.hide();
            }
            $('#modalSettings select[name="election_status"]').val('paused');
            var settingsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSettings'));
            settingsModal.show();
        });
    });
    </script>
    <?php $this->load->view('admin/modal_change_password', array('current_page' => 'admin')); ?>
</body>
</html>
