<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Jejak Suara &amp; Pilihan - <?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi'); ?></title>
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
                        <h1 class="h3 fw-bold mb-1">Audit Jejak Pilihan Suara</h1>
                        <p class="text-muted small mb-0">Verifikasi riwayat pilihan pemilih dan rincian suara per kandidat untuk kebutuhan pengawasan resmi</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= base_url('admin/export_audit_excel'); ?>" class="btn btn-success btn-sm fw-semibold">
                            <i class="fas fa-file-excel me-1"></i> Unduh Excel (.xlsx)
                        </a>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <ul class="nav nav-pills mb-4" id="auditTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" id="tab-voters-btn" data-bs-toggle="pill" data-bs-target="#tab-voters" type="button" role="tab">
                            <i class="fas fa-user-tag me-1"></i> Jejak Pilihan Pemilih (Voter History)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="tab-candidate-btn" data-bs-toggle="pill" data-bs-target="#tab-candidate" type="button" role="tab">
                            <i class="fas fa-list-ol me-1"></i> Rekap Pemilih per Calon
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="auditTabsContent">
                    <!-- Tab 1: Jejak Pilihan Pemilih -->
                    <div class="tab-pane fade show active" id="tab-voters" role="tabpanel">
                        <div class="admin-card">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                <div>
                                    <h5 class="fw-bold mb-1">Daftar Rekam Suara Pemilih</h5>
                                    <p class="text-muted small mb-0">Total <?= count($voter_history); ?> pemilih tercatat dengan riwayat pilihan lengkap</p>
                                </div>

                                <form method="GET" action="<?= base_url('admin/audit_votes'); ?>" class="d-flex gap-2">
                                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama, no. anggota, token..." value="<?= htmlspecialchars($search_voter ?? ''); ?>" style="width: 260px;">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($search_voter)): ?>
                                    <a href="<?= base_url('admin/audit_votes'); ?>" class="btn btn-outline-danger btn-sm" title="Hapus Filter">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">No</th>
                                            <th style="width: 140px;">Waktu Suara</th>
                                            <th style="width: 130px;">No. Anggota</th>
                                            <th>Nama Pemilih</th>
                                            <th>Pilihan Ketua</th>
                                            <th>Pilihan Pengawas</th>
                                            <th style="width: 130px;" class="text-center">Token Struk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($voter_history)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <?php if (!empty($search_voter)): ?>
                                                Tidak ditemukan data pemilih yang cocok dengan "<strong><?= htmlspecialchars($search_voter); ?></strong>".
                                                <?php else: ?>
                                                Belum ada pemilih yang menyelesaikan proses voting pada sesi ini.
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php $no = 1; foreach ($voter_history as $vh): ?>
                                        <tr>
                                            <td class="text-center text-muted small"><?= $no++; ?></td>
                                            <td class="small text-muted font-monospace">
                                                <?= $vh['voted_at'] ? date('d/m/Y H:i', strtotime($vh['voted_at'])) : '-'; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($vh['member_number']); ?></span>
                                            </td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($vh['voter_name']); ?></td>
                                            <td>
                                                <?php if (isset($vh['votes']['ketua'])): ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-semibold">
                                                    #<?= $vh['votes']['ketua']['candidate_number']; ?> - <?= htmlspecialchars($vh['votes']['ketua']['candidate_name']); ?>
                                                </span>
                                                <?php else: ?>
                                                <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($vh['votes']['pengawas'])): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle fw-semibold">
                                                    #<?= $vh['votes']['pengawas']['candidate_number']; ?> - <?= htmlspecialchars($vh['votes']['pengawas']['candidate_name']); ?>
                                                </span>
                                                <?php else: ?>
                                                <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <code class="px-2 py-1 bg-light border rounded text-dark small fw-bold"><?= htmlspecialchars($vh['receipt_token']); ?></code>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Pemilih per Calon -->
                    <div class="tab-pane fade" id="tab-candidate" role="tabpanel">
                        <div class="admin-card mb-4">
                            <h5 class="fw-bold mb-2">Pilih Kandidat untuk Ditinjau</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($candidates as $c): ?>
                                <a href="<?= base_url('admin/audit_votes?candidate_id=' . $c['id']); ?>#tab-candidate" class="btn btn-sm <?= ($selected_candidate_id === (int)$c['id']) ? 'btn-dark' : 'btn-outline-secondary'; ?>">
                                    <span class="badge <?= ($c['category'] === 'ketua') ? 'bg-primary' : 'bg-success'; ?> me-1">
                                        <?= strtoupper($c['category']); ?> #<?= $c['candidate_number']; ?>
                                    </span>
                                    <?= htmlspecialchars($c['name']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Candidate Voter List -->
                        <?php 
                        $curCand = null;
                        foreach ($candidates as $c) {
                            if ((int)$c['id'] === $selected_candidate_id) {
                                $curCand = $c;
                                break;
                            }
                        }
                        ?>
                        <?php if ($curCand): ?>
                        <div class="admin-card">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= base_url($curCand['photo']); ?>" alt="Foto" class="rounded-circle border" style="width: 48px; height: 48px; object-fit: cover;">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($curCand['name']); ?></h5>
                                            <span class="badge <?= ($curCand['category'] === 'ketua') ? 'bg-primary' : 'bg-success'; ?>">
                                                <?= strtoupper($curCand['category']); ?> #<?= $curCand['candidate_number']; ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">Total Suara Diterima: <strong><?= count($candidate_voters); ?> suara</strong></small>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">No</th>
                                            <th style="width: 160px;">Waktu Suara</th>
                                            <th style="width: 140px;">No. Anggota</th>
                                            <th>Nama Pemilih</th>
                                            <th style="width: 140px;" class="text-center">Token Struk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($candidate_voters)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                Belum ada pemilih yang memberikan suara untuk kandidat ini.
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php $no = 1; foreach ($candidate_voters as $cv): ?>
                                        <tr>
                                            <td class="text-center text-muted small"><?= $no++; ?></td>
                                            <td class="small text-muted font-monospace">
                                                <?= $cv['voted_at'] ? date('d/m/Y H:i:s', strtotime($cv['voted_at'])) : '-'; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($cv['member_number'] ?? '-'); ?></span>
                                            </td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($cv['voter_name'] ?? 'Data Suara Historis'); ?></td>
                                            <td class="text-center">
                                                <code class="px-2 py-1 bg-light border rounded text-dark small fw-bold"><?= htmlspecialchars($cv['receipt_token']); ?></code>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
    // Tab persistent memory on reload or hash
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#tab-candidate') {
            const triggerEl = document.querySelector('#tab-candidate-btn');
            if (triggerEl) bootstrap.Tab.getOrCreateInstance(triggerEl).show();
        }
    });
    </script>

    <?php $this->load->view('admin/modal_change_password', array('redirect_to' => 'admin/audit_votes')); ?>
</body>
</html>
