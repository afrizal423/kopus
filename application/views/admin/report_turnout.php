<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Partisipasi Pemilih - <?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi'); ?></title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
    <script>
        (function() {
            try {
                if (localStorage.getItem('kopus_admin_sidebar_collapsed') === '1' && window.innerWidth >= 768) {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch(e) {}
        })();
    </script>
    <script src="<?= base_url('assets/vendor/jquery/jquery-3.6.0.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <?php $this->load->view('admin/sidebar', array('current_page' => 'report_turnout')); ?>

            <!-- Main Content Area -->
            <main class="col-md-9 col-lg-10 p-4 admin-main" id="adminMain">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn btn-outline-secondary btn-sidebar-toggle shadow-sm" id="sidebarToggle" title="Tampilkan/Sembunyikan Sidebar" aria-label="Toggle Sidebar">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div>
                            <h1 class="h3 fw-bold mb-1">Laporan Partisipasi Pemilih</h1>
                            <p class="text-muted small mb-0">Statistik kehadiran anggota dan pemantauan hak pilih DPT secara real-time</p>
                        </div>
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

                <!-- Voters Turnout Tabs & Table -->
                <div class="admin-card">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <!-- Navigation Tabs (Pills) -->
                        <ul class="nav nav-pills" id="turnoutTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= ($active_tab === 'unvoted') ? 'active' : ''; ?> fw-semibold" id="tab-unvoted-btn" data-bs-toggle="pill" data-bs-target="#tab-unvoted" type="button" role="tab" aria-controls="tab-unvoted" aria-selected="<?= ($active_tab === 'unvoted') ? 'true' : 'false'; ?>">
                                    <i class="fas fa-user-clock me-1 text-danger"></i> Belum Memilih
                                    <span class="badge <?= ($active_tab === 'unvoted') ? 'bg-danger' : 'bg-secondary'; ?> ms-1" id="badgeUnvotedCount"><?= number_format(count($unvoted_voters), 0, ',', '.'); ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= ($active_tab === 'voted') ? 'active' : ''; ?> fw-semibold" id="tab-voted-btn" data-bs-toggle="pill" data-bs-target="#tab-voted" type="button" role="tab" aria-controls="tab-voted" aria-selected="<?= ($active_tab === 'voted') ? 'true' : 'false'; ?>">
                                    <i class="fas fa-user-check me-1 text-success"></i> Sudah Memilih
                                    <span class="badge <?= ($active_tab === 'voted') ? 'bg-success' : 'bg-secondary'; ?> ms-1" id="badgeVotedCount"><?= number_format(count($voted_voters), 0, ',', '.'); ?></span>
                                </button>
                            </li>
                        </ul>

                        <!-- Search Form -->
                        <form method="GET" action="<?= base_url('admin/report_turnout'); ?>" class="d-flex gap-2" id="searchForm">
                            <input type="hidden" name="tab" id="activeTabInput" value="<?= htmlspecialchars($active_tab); ?>">
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama atau No. Anggota..." value="<?= htmlspecialchars($search ?? ''); ?>" style="width: 240px;">
                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Cari">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (!empty($search)): ?>
                            <a href="<?= base_url('admin/report_turnout?tab=' . urlencode($active_tab)); ?>" class="btn btn-outline-danger btn-sm" title="Hapus Filter">
                                <i class="fas fa-times"></i>
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Tab Contents -->
                    <div class="tab-content" id="turnoutTabsContent">
                        
                        <!-- TAB 1: BELUM MEMILIH -->
                        <div class="tab-pane fade <?= ($active_tab === 'unvoted') ? 'show active' : ''; ?>" id="tab-unvoted" role="tabpanel" aria-labelledby="tab-unvoted-btn">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Daftar Anggota Belum Menggunakan Hak Suara</h6>
                                    <p class="text-muted small mb-0">Total <?= number_format(count($unvoted_voters), 0, ',', '.'); ?> anggota dalam DPT yang belum mencoblos di bilik suara</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">No</th>
                                            <th style="width: 140px;">No. Anggota</th>
                                            <th>Nama Anggota</th>
                                            <th style="width: 160px;">Departemen</th>
                                            <th style="width: 130px;" class="text-center">Status Akun</th>
                                            <th style="width: 140px;" class="text-center">Aksi Bantuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($unvoted_voters)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
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
                                            <td><?= !empty($v['department']) ? htmlspecialchars($v['department']) : '<span class="text-muted">-</span>'; ?></td>
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

                        <!-- TAB 2: SUDAH MEMILIH -->
                        <div class="tab-pane fade <?= ($active_tab === 'voted') ? 'show active' : ''; ?>" id="tab-voted" role="tabpanel" aria-labelledby="tab-voted-btn">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Daftar Anggota yang Sudah Memilih</h6>
                                    <p class="text-muted small mb-0">Total <?= number_format(count($voted_voters), 0, ',', '.'); ?> anggota yang telah hadir dan menuntaskan hak suara di bilik suara</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">No</th>
                                            <th style="width: 140px;">No. Anggota</th>
                                            <th>Nama Anggota</th>
                                            <th style="width: 160px;">Departemen</th>
                                            <th style="width: 180px;" class="text-center">Waktu Memilih</th>
                                            <th style="width: 150px;" class="text-center">Status Suara</th>
                                            <th style="width: 150px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($voted_voters)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <?php if (!empty($search)): ?>
                                                Tidak ditemukan anggota sudah memilih yang cocok dengan pencarian "<strong><?= htmlspecialchars($search); ?></strong>".
                                                <?php else: ?>
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox me-1"></i> Belum ada data anggota yang menggunakan hak suara saat ini.
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php $no = 1; foreach ($voted_voters as $v): ?>
                                        <tr>
                                            <td class="text-center text-muted small"><?= $no++; ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($v['member_number']); ?></span>
                                            </td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($v['name']); ?></td>
                                            <td><?= !empty($v['department']) ? htmlspecialchars($v['department']) : '<span class="text-muted">-</span>'; ?></td>
                                            <td class="text-center text-nowrap small text-muted">
                                                <?php if (!empty($v['voted_at'])): ?>
                                                    <i class="far fa-clock text-success me-1"></i><?= date('d/m/Y H:i:s', strtotime($v['voted_at'])); ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="fas fa-check me-1"></i> SUDAH MEMILIH
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= base_url('admin/audit_votes?q=' . urlencode($v['member_number'])); ?>" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 0.75rem;" title="Lihat Rekam Audit Ledger">
                                                    <i class="fas fa-receipt me-1"></i> Audit Suara
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                <script>
                $(document).ready(function() {
                    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
                        var target = $(e.target).attr('data-bs-target');
                        var tabName = (target === '#tab-voted') ? 'voted' : 'unvoted';
                        $('#activeTabInput').val(tabName);
                        
                        // Update badge active colors
                        if (tabName === 'voted') {
                            $('#badgeVotedCount').removeClass('bg-secondary').addClass('bg-success');
                            $('#badgeUnvotedCount').removeClass('bg-danger').addClass('bg-secondary');
                        } else {
                            $('#badgeUnvotedCount').removeClass('bg-secondary').addClass('bg-danger');
                            $('#badgeVotedCount').removeClass('bg-success').addClass('bg-secondary');
                        }

                        // Update URL query string without reloading
                        if (window.history && window.history.pushState) {
                            var url = new URL(window.location.href);
                            url.searchParams.set('tab', tabName);
                            window.history.pushState({path: url.toString()}, '', url.toString());
                        }
                    });
                });
                </script>

            </main>
        </div>
    </div>

    <?php $this->load->view('admin/modal_change_password', array('redirect_to' => 'admin/report_turnout')); ?>
</body>
</html>
