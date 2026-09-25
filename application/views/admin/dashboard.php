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
    <script src="<?= base_url('assets/vendor/three/three.min.js'); ?>"></script>
    <style>
        /* 3D Quick Count Fullscreen Presentation Mode */
        #quickCount3dContainer:fullscreen,
        #quickCount3dContainer:-webkit-full-screen {
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            margin: 0 !important;
            padding: 1.25rem 2rem !important;
            border-radius: 0 !important;
            border: none !important;
            background: #080d1a !important;
            display: flex !important;
            flex-direction: column !important;
            box-sizing: border-box !important;
        }

        #quickCount3dContainer.is-fullscreen-fallback {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            margin: 0 !important;
            padding: 1.25rem 2rem !important;
            border-radius: 0 !important;
            border: none !important;
            background: #080d1a !important;
            display: flex !important;
            flex-direction: column !important;
            z-index: 999999 !important;
            box-sizing: border-box !important;
        }

        #quickCount3dContainer:fullscreen .border-bottom,
        #quickCount3dContainer:-webkit-full-screen .border-bottom,
        #quickCount3dContainer.is-fullscreen-fallback .border-bottom {
            border-color: #1e293b !important;
        }

        #quickCount3dContainer:fullscreen .text-dark,
        #quickCount3dContainer:-webkit-full-screen .text-dark,
        #quickCount3dContainer.is-fullscreen-fallback .text-dark {
            color: #f8fafc !important;
        }

        #quickCount3dContainer:fullscreen .text-muted,
        #quickCount3dContainer:-webkit-full-screen .text-muted,
        #quickCount3dContainer.is-fullscreen-fallback .text-muted {
            color: #94a3b8 !important;
        }

        #quickCount3dContainer:fullscreen .btn-outline-secondary,
        #quickCount3dContainer:-webkit-full-screen .btn-outline-secondary,
        #quickCount3dContainer.is-fullscreen-fallback .btn-outline-secondary {
            color: #e2e8f0 !important;
            border-color: #334155 !important;
            background-color: rgba(30, 41, 59, 0.6) !important;
        }
        #quickCount3dContainer:fullscreen .btn-outline-secondary:hover,
        #quickCount3dContainer:-webkit-full-screen .btn-outline-secondary:hover,
        #quickCount3dContainer.is-fullscreen-fallback .btn-outline-secondary:hover {
            background-color: #334155 !important;
            color: #fff !important;
        }

        #quickCount3dContainer:fullscreen #quickCountStageWrapper,
        #quickCount3dContainer:-webkit-full-screen #quickCountStageWrapper,
        #quickCount3dContainer.is-fullscreen-fallback #quickCountStageWrapper {
            flex: 1 1 auto !important;
            height: 100% !important;
            min-height: 0 !important;
            border-color: #1e293b !important;
        }
    </style>
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

                <!-- 3D Live Quick Count Pillars (Cylinders) -->
                <div class="admin-card mb-4" id="quickCount3dContainer">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle p-2 rounded-3">
                                <i class="fas fa-cubes fs-5"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Visualisasi 3D Live Quick Count: Pilar Perolehan Suara</h6>
                                <small class="text-muted">Grafik pilar 3D interaktif real-time untuk penayangan hasil di aula atau layar utama</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <!-- Category Switcher Pills -->
                            <div class="btn-group btn-group-sm" role="group" id="pillarCategoryToggle">
                                <button type="button" class="btn btn-outline-success active fw-semibold" data-category="ketua">
                                    <i class="fas fa-user-tie me-1"></i> Calon Ketua
                                </button>
                                <button type="button" class="btn btn-outline-primary fw-semibold" data-category="pengawas">
                                    <i class="fas fa-shield-alt me-1"></i> Calon Pengawas
                                </button>
                            </div>

                            <!-- Zoom Controls -->
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary" id="btnZoomIn3D" title="Perbesar (Zoom In)">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btnZoomOut3D" title="Perkecil (Zoom Out)">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btnResetView3D" title="Reset Sudut &amp; Zoom">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnToggle3DOrbit" title="Jeda / Lanjutkan Putaran Otomatis">
                                <i class="fas fa-sync-alt" id="orbitIcon"></i> <span class="d-none d-md-inline ms-1">Auto Orbit</span>
                            </button>

                            <!-- Fullscreen Presentation Button -->
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnToggle3DFullscreen" title="Tampilkan Layar Penuh (Proyektor / TV)">
                                <i class="fas fa-expand" id="fullscreenIcon"></i> <span class="d-none d-md-inline ms-1" id="fullscreenText">Fullscreen</span>
                            </button>
                        </div>
                    </div>

                    <!-- 3D Stadium Stage Container -->
                    <div id="quickCountStageWrapper" class="position-relative rounded-3 overflow-hidden border" style="background: radial-gradient(circle at 50% 20%, #1e293b 0%, #0f172a 100%); height: 350px; box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);">
                        <div id="quickCount3dCanvas" style="width: 100%; height: 100%; cursor: grab;" title="Klik dua kali untuk Layar Penuh"></div>
                        
                        <!-- Floating Category & Leader Badge -->
                        <div class="position-absolute top-0 start-0 p-3 pointer-events-none" style="z-index: 5;">
                            <span class="badge bg-dark bg-opacity-75 border border-secondary text-warning fw-bold px-3 py-2" id="pillarStageBadge">
                                <i class="fas fa-crown me-1"></i> Memuat Pilar Suara...
                            </span>
                        </div>

                        <!-- Stage Controls Hint -->
                        <div class="position-absolute bottom-0 end-0 p-2 text-white-50 small user-select-none pointer-events-none" style="font-size: 0.74rem; z-index: 5;">
                            <i class="fas fa-search-plus me-1 text-info"></i> Scroll untuk Zoom • Geser 360° • Dobel-klik / tombol untuk Fullscreen
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

    <!-- Three.js 3D Live Quick Count Pillars Controller (Tablet-Optimized) -->
    <script>
    (function() {
        const stageWrap = document.getElementById('quickCount3dCanvas');
        if (!window.THREE || !stageWrap) return;

        const countStats = {
            ketua: <?= json_encode($stats['results']['ketua']['candidates'] ?? []); ?>,
            pengawas: <?= json_encode($stats['results']['pengawas']['candidates'] ?? []); ?>
        };

        let currentCat = 'ketua';
        let scene, camera, renderer, animId;
        let stageGroup, platformMesh, pillarGroup;
        let pillars = [];
        let isOrbitActive = true;
        let orbitAngle = 0;
        let userYaw = 0, isDragging = false, lastMouseX = 0;
        let isVisibleOnScreen = true;

        // Zoom & Camera state
        let targetCamDist = 5.2;
        let currentCamDist = 5.2;
        const minCamDist = 3.2;
        const maxCamDist = 8.5;
        let userPitch = 0;
        let targetUserPitch = 0;

        const stageBadge = document.getElementById('pillarStageBadge');
        const btnOrbit = document.getElementById('btnToggle3DOrbit');
        const orbitIcon = document.getElementById('orbitIcon');

        function initScene() {
            const w = stageWrap.clientWidth || 800;
            const h = stageWrap.clientHeight || 350;

            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x0f172a); // Deep Navy Slate

            camera = new THREE.PerspectiveCamera(40, w / h, 0.1, 100);
            camera.position.set(0, 2.5, currentCamDist);
            camera.lookAt(0, 0.95, 0);

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
            renderer.setSize(w, h);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1.08;
            stageWrap.innerHTML = '';
            stageWrap.appendChild(renderer.domElement);

            // Lighting
            const amb = new THREE.AmbientLight(0xffffff, 0.8);
            scene.add(amb);

            const keyLight = new THREE.DirectionalLight(0xffffff, 0.95);
            keyLight.position.set(5, 9, 6);
            scene.add(keyLight);

            const fillLight = new THREE.DirectionalLight(0x38bdf8, 0.45);
            fillLight.position.set(-5, 6, -4);
            scene.add(fillLight);

            // Stage Root Group
            stageGroup = new THREE.Group();
            scene.add(stageGroup);

            // Circular Platform Pedestal
            const platGeo = new THREE.CylinderGeometry(3.6, 3.8, 0.15, 48);
            const platMat = new THREE.MeshStandardMaterial({
                color: 0x1e293b,
                roughness: 0.35,
                metalness: 0.6
            });
            platformMesh = new THREE.Mesh(platGeo, platMat);
            platformMesh.position.y = -0.08;
            stageGroup.add(platformMesh);

            // Grid Rings on Platform
            const ringGeo = new THREE.RingGeometry(2.4, 2.44, 48);
            const ringMat = new THREE.MeshBasicMaterial({ color: 0x334155, side: THREE.DoubleSide });
            const ringMesh = new THREE.Mesh(ringGeo, ringMat);
            ringMesh.rotation.x = Math.PI / 2;
            ringMesh.position.y = 0.005;
            stageGroup.add(ringMesh);

            // Pillar Container Group
            pillarGroup = new THREE.Group();
            stageGroup.add(pillarGroup);

            setupInteraction();
            rebuildPillars(currentCat);
            animate();
        }

        // High-DPI 512x256 Crisp Billboard Text Texture
        function createTextTexture(name, percentage, isLeader, num, voteCount) {
            const cv = document.createElement('canvas');
            cv.width = 512;
            cv.height = 256;
            const ctx = cv.getContext('2d');

            // Card background box with glowing border
            ctx.fillStyle = isLeader ? 'rgba(6, 78, 59, 0.96)' : 'rgba(15, 23, 42, 0.94)';
            ctx.beginPath();
            ctx.roundRect ? ctx.roundRect(10, 10, 492, 236, 22) : ctx.rect(10, 10, 492, 236);
            ctx.fill();
            ctx.lineWidth = isLeader ? 6 : 3;
            ctx.strokeStyle = isLeader ? '#f59e0b' : '#38bdf8';
            ctx.stroke();

            // Candidate number badge pill (top-left)
            ctx.fillStyle = isLeader ? '#f59e0b' : '#0284c7';
            ctx.beginPath();
            ctx.roundRect ? ctx.roundRect(24, 24, 82, 48, 12) : ctx.rect(24, 24, 82, 48);
            ctx.fill();
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 30px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('#' + num, 65, 58);

            // Percentage Big Bold Text (top-right)
            ctx.fillStyle = isLeader ? '#fef08a' : '#ffffff';
            ctx.font = 'bold 52px sans-serif';
            ctx.textAlign = 'right';
            ctx.fillText(percentage, 480, 64);

            // Candidate Name (Bold, bright & large)
            ctx.fillStyle = '#f8fafc';
            ctx.font = 'bold 34px sans-serif';
            ctx.textAlign = 'left';
            let shortName = name;
            if (shortName.length > 21) shortName = shortName.substring(0, 19) + '...';
            ctx.fillText(shortName, 26, 136);

            // Bottom Ribbon: Vote Count
            ctx.fillStyle = 'rgba(255, 255, 255, 0.12)';
            ctx.beginPath();
            ctx.roundRect ? ctx.roundRect(24, 162, 464, 56, 12) : ctx.rect(24, 162, 464, 56);
            ctx.fill();
            ctx.fillStyle = isLeader ? '#34d399' : '#94a3b8';
            ctx.font = 'bold 24px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(voteCount + ' SUARA TERCATAT', 256, 198);

            return new THREE.CanvasTexture(cv);
        }

        function rebuildPillars(cat) {
            while(pillarGroup.children.length > 0) {
                const obj = pillarGroup.children[0];
                if (obj.geometry) obj.geometry.dispose();
                if (obj.material) {
                    if (Array.isArray(obj.material)) obj.material.forEach(m => m.dispose());
                    else obj.material.dispose();
                }
                pillarGroup.remove(obj);
            }
            pillars = [];

            const candidates = countStats[cat] || [];
            if (candidates.length === 0) {
                if (stageBadge) stageBadge.innerHTML = '<i class="fas fa-info-circle me-1"></i> Belum ada data calon';
                return;
            }

            // Find leader
            let maxVotes = 0;
            let leader = candidates[0];
            candidates.forEach(c => {
                const votes = parseInt(c.vote_count) || 0;
                if (votes > maxVotes) {
                    maxVotes = votes;
                    leader = c;
                }
            });

            if (stageBadge) {
                const catLabel = (cat === 'ketua') ? 'Ketua' : 'Pengawas';
                if (maxVotes > 0) {
                    stageBadge.innerHTML = `<i class="fas fa-crown text-warning me-1"></i> Unggul ${catLabel}: <strong>${leader.name}</strong> (${leader.vote_count} suara / ${leader.percentage}%)`;
                } else {
                    stageBadge.innerHTML = `<i class="fas fa-cubes text-info me-1"></i> Rekapitulasi: Calon ${catLabel} (Menunggu Suara Masuk)`;
                }
            }

            const n = candidates.length;
            const spacing = n <= 3 ? 1.75 : 1.4;
            const pillarRadius = n <= 3 ? 0.46 : 0.38;

            candidates.forEach((c, idx) => {
                const xPos = (idx - (n - 1) / 2) * spacing;
                const voteCount = parseInt(c.vote_count) || 0;
                const isLeader = (voteCount > 0 && c.id === leader.id);

                // Cylinder with bottom pivot
                const cylGeo = new THREE.CylinderGeometry(pillarRadius, pillarRadius, 1, 24);
                cylGeo.translate(0, 0.5, 0);

                const baseColor = (cat === 'ketua') ? 0x059669 : 0x2563eb;
                const leaderColor = (cat === 'ketua') ? 0x10b981 : 0x0284c7;
                const cylMat = new THREE.MeshStandardMaterial({
                    color: isLeader ? leaderColor : baseColor,
                    metalness: 0.75,
                    roughness: 0.25
                });

                const pMesh = new THREE.Mesh(cylGeo, cylMat);
                pMesh.position.set(xPos, 0, 0);
                pMesh.scale.set(1, 0.05, 1);
                pillarGroup.add(pMesh);

                // Golden Ring Trim for Leader
                let crownRing = null;
                if (isLeader) {
                    const cRingGeo = new THREE.TorusGeometry(pillarRadius + 0.04, 0.025, 16, 32);
                    const cRingMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, metalness: 0.9, roughness: 0.1 });
                    crownRing = new THREE.Mesh(cRingGeo, cRingMat);
                    crownRing.rotation.x = Math.PI / 2;
                    pillarGroup.add(crownRing);
                }

                // Top Floating Info Disc/Badge (Large & crisp 1.65 x 0.85)
                const labelTex = createTextTexture(c.name, c.percentage + '%', isLeader, c.candidate_number, voteCount);
                const labelGeo = new THREE.PlaneGeometry(1.65, 0.85);
                const labelMat = new THREE.MeshBasicMaterial({ map: labelTex, transparent: true });
                const labelMesh = new THREE.Mesh(labelGeo, labelMat);
                labelMesh.position.set(xPos, 0.52, 0);
                pillarGroup.add(labelMesh);

                // Calculate visual target height: min 0.35, max 2.2
                let targetH = 0.35;
                if (maxVotes > 0) {
                    targetH = 0.35 + (voteCount / maxVotes) * 1.8;
                }

                pillars.push({
                    mesh: pMesh,
                    crownRing: crownRing,
                    label: labelMesh,
                    targetHeight: targetH,
                    x: xPos
                });
            });
        }

        function setupInteraction() {
            const el = stageWrap;
            let lastMouseY = 0;

            const onDown = (clientX, clientY) => {
                isDragging = true;
                lastMouseX = clientX;
                lastMouseY = clientY;
            };
            const onMove = (clientX, clientY) => {
                if (!isDragging) return;
                const deltaX = clientX - lastMouseX;
                const deltaY = clientY - lastMouseY;
                lastMouseX = clientX;
                lastMouseY = clientY;

                userYaw += deltaX * 0.008;
                targetUserPitch = Math.max(-0.4, Math.min(0.65, targetUserPitch + deltaY * 0.005));
            };
            const onUp = () => { isDragging = false; };

            el.addEventListener('mousedown', (e) => onDown(e.clientX, e.clientY));
            window.addEventListener('mousemove', (e) => onMove(e.clientX, e.clientY));
            window.addEventListener('mouseup', onUp);

            // Touch Drag & Pinch-to-Zoom
            let initialPinchDist = null;
            let initialCamDist = targetCamDist;

            el.addEventListener('touchstart', (e) => {
                if (e.touches.length === 1) {
                    onDown(e.touches[0].clientX, e.touches[0].clientY);
                } else if (e.touches.length === 2) {
                    isDragging = false;
                    const dx = e.touches[0].clientX - e.touches[1].clientX;
                    const dy = e.touches[0].clientY - e.touches[1].clientY;
                    initialPinchDist = Math.hypot(dx, dy);
                    initialCamDist = targetCamDist;
                }
            }, { passive: true });

            window.addEventListener('touchmove', (e) => {
                if (e.touches.length === 1 && isDragging) {
                    onMove(e.touches[0].clientX, e.touches[0].clientY);
                } else if (e.touches.length === 2 && initialPinchDist) {
                    const dx = e.touches[0].clientX - e.touches[1].clientX;
                    const dy = e.touches[0].clientY - e.touches[1].clientY;
                    const currentDist = Math.hypot(dx, dy);
                    const factor = initialPinchDist / Math.max(10, currentDist);
                    targetCamDist = Math.max(minCamDist, Math.min(maxCamDist, initialCamDist * factor));
                }
            }, { passive: true });

            window.addEventListener('touchend', () => {
                onUp();
                initialPinchDist = null;
            });

            // Mouse Wheel Zoom
            el.addEventListener('wheel', (e) => {
                e.preventDefault();
                const delta = e.deltaY > 0 ? 0.45 : -0.45;
                targetCamDist = Math.max(minCamDist, Math.min(maxCamDist, targetCamDist + delta));
            }, { passive: false });

            // Toolbar Zoom Buttons
            $('#btnZoomIn3D').on('click', function() {
                targetCamDist = Math.max(minCamDist, targetCamDist - 0.7);
            });
            $('#btnZoomOut3D').on('click', function() {
                targetCamDist = Math.min(maxCamDist, targetCamDist + 0.7);
            });
            $('#btnResetView3D').on('click', function() {
                targetCamDist = 5.2;
                targetUserPitch = 0;
                userYaw = 0;
                orbitAngle = 0;
            });

            // Orbit Toggle Button
            if (btnOrbit) {
                btnOrbit.addEventListener('click', function() {
                    isOrbitActive = !isOrbitActive;
                    if (isOrbitActive) {
                        btnOrbit.classList.add('active');
                        orbitIcon.className = 'fas fa-sync-alt fa-spin';
                    } else {
                        btnOrbit.classList.remove('active');
                        orbitIcon.className = 'fas fa-pause';
                    }
                });
            }

            // Category switcher pills
            $('#pillarCategoryToggle button').on('click', function() {
                $('#pillarCategoryToggle button').removeClass('active');
                $(this).addClass('active');
                currentCat = $(this).data('category');
                rebuildPillars(currentCat);
            });

            // Window resize & canvas sync
            function resizeStage() {
                if (!stageWrap || !renderer || !camera) return;
                const nw = stageWrap.clientWidth;
                const nh = stageWrap.clientHeight;
                if (nw > 0 && nh > 0) {
                    camera.aspect = nw / nh;
                    camera.updateProjectionMatrix();
                    renderer.setSize(nw, nh);
                }
            }

            window.addEventListener('resize', resizeStage);

            // Fullscreen Presentation Mode Logic
            const container3D = document.getElementById('quickCount3dContainer');
            const btnFs = document.getElementById('btnToggle3DFullscreen');
            const fsIcon = document.getElementById('fullscreenIcon');
            const fsText = document.getElementById('fullscreenText');

            function syncFullscreenUI() {
                const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement || (container3D && container3D.classList.contains('is-fullscreen-fallback')));
                if (fsIcon) {
                    fsIcon.className = isFs ? 'fas fa-compress' : 'fas fa-expand';
                }
                if (fsText) {
                    fsText.textContent = isFs ? 'Keluar Layar Penuh' : 'Fullscreen';
                }
                if (btnFs) {
                    if (isFs) {
                        btnFs.classList.add('btn-warning');
                        btnFs.classList.remove('btn-outline-secondary');
                        btnFs.title = 'Keluar dari Mode Layar Penuh (Esc)';
                    } else {
                        btnFs.classList.remove('btn-warning');
                        btnFs.classList.add('btn-outline-secondary');
                        btnFs.title = 'Tampilkan Layar Penuh (Proyektor / TV)';
                    }
                }
                // Multiple passes to accommodate CSS animation and viewport adjustment
                resizeStage();
                setTimeout(resizeStage, 50);
                setTimeout(resizeStage, 150);
                setTimeout(resizeStage, 300);
            }

            function toggleFullscreen() {
                if (!container3D) return;
                const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement || container3D.classList.contains('is-fullscreen-fallback'));

                if (!isFs) {
                    let entered = false;
                    try {
                        let p = null;
                        if (container3D.requestFullscreen) {
                            p = container3D.requestFullscreen();
                        } else if (container3D.webkitRequestFullscreen) {
                            p = container3D.webkitRequestFullscreen();
                        }

                        if (p && typeof p.then === 'function') {
                            p.then(() => {
                                syncFullscreenUI();
                            }).catch(() => {
                                container3D.classList.add('is-fullscreen-fallback');
                                syncFullscreenUI();
                            });
                            entered = true;
                        } else if (p !== null && p !== undefined) {
                            entered = true;
                        }
                    } catch (err) {
                        entered = false;
                    }

                    if (!entered) {
                        container3D.classList.add('is-fullscreen-fallback');
                        syncFullscreenUI();
                    }
                } else {
                    if (document.fullscreenElement || document.webkitFullscreenElement) {
                        if (document.exitFullscreen) {
                            try { document.exitFullscreen(); } catch (e) {}
                        } else if (document.webkitExitFullscreen) {
                            try { document.webkitExitFullscreen(); } catch (e) {}
                        }
                    }
                    container3D.classList.remove('is-fullscreen-fallback');
                    syncFullscreenUI();
                }
            }

            if (btnFs) {
                btnFs.addEventListener('click', toggleFullscreen);
            }

            // Double click canvas to toggle Fullscreen
            stageWrap.addEventListener('dblclick', function(e) {
                e.preventDefault();
                toggleFullscreen();
            });

            // Native fullscreen change and error events
            document.addEventListener('fullscreenchange', syncFullscreenUI);
            document.addEventListener('webkitfullscreenchange', syncFullscreenUI);

            document.addEventListener('fullscreenerror', function() {
                if (container3D && !document.fullscreenElement) {
                    container3D.classList.add('is-fullscreen-fallback');
                    syncFullscreenUI();
                }
            });
            document.addEventListener('webkitfullscreenerror', function() {
                if (container3D && !document.webkitFullscreenElement) {
                    container3D.classList.add('is-fullscreen-fallback');
                    syncFullscreenUI();
                }
            });

            // Escape key support for CSS fallback
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && container3D && container3D.classList.contains('is-fullscreen-fallback')) {
                    container3D.classList.remove('is-fullscreen-fallback');
                    syncFullscreenUI();
                }
            });
        }

        function animate() {
            animId = requestAnimationFrame(animate);

            if (!isVisibleOnScreen) return; // Battery & GPU safeguard

            // Smooth Zoom & Pitch Interpolation
            currentCamDist += (targetCamDist - currentCamDist) * 0.1;
            userPitch += (targetUserPitch - userPitch) * 0.1;

            // Grow pillars smoothly to target height
            pillars.forEach(p => {
                p.mesh.scale.y += (p.targetHeight - p.mesh.scale.y) * 0.07;
                const currentH = p.mesh.scale.y;

                // Move label disc above pillar
                p.label.position.y = currentH + 0.52;
                p.label.quaternion.copy(camera.quaternion); // Always face camera

                if (p.crownRing) {
                    p.crownRing.position.set(p.x, currentH + 0.02, 0);
                    p.crownRing.rotation.z += 0.02;
                }
            });

            // Orbit & user rotation
            if (isOrbitActive && !isDragging) {
                orbitAngle += 0.0035;
            }
            if (!isDragging) {
                userYaw *= 0.94;
            }

            const effAngle = orbitAngle + userYaw;
            camera.position.x = Math.sin(effAngle) * currentCamDist;
            camera.position.z = Math.cos(effAngle) * currentCamDist;
            camera.position.y = (currentCamDist * 0.44) + 0.35 + userPitch * 2.2;
            camera.lookAt(0, 0.95, 0);

            renderer.render(scene, camera);
        }

        // Tablet performance safeguards
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    isVisibleOnScreen = entry.isIntersecting;
                });
            }, { threshold: 0.1 });
            observer.observe(stageWrap);
        }

        document.addEventListener('visibilitychange', () => {
            isVisibleOnScreen = !document.hidden;
        });

        initScene();
    })();
    </script>
    <?php $this->load->view('admin/modal_change_password', array('current_page' => 'admin')); ?>
</body>
</html>
