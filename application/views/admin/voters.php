<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pemilih Tetap (DPT) &amp; RFID - E-Voting Koperasi</title>
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
                    <a href="<?= base_url('admin'); ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard &amp; Hasil</span>
                    </a>
                    <a href="<?= base_url('admin/candidates'); ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>Manajemen Calon</span>
                    </a>
                    <a href="<?= base_url('admin/voters'); ?>" class="active">
                        <i class="fas fa-id-card"></i>
                        <span>DPT &amp; Kartu RFID</span>
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
                    <a href="<?= base_url('admin/logout'); ?>" class="text-danger p-0 d-inline-flex align-items-center gap-1">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 col-lg-10 p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h3 fw-bold mb-1">Daftar Pemilih Tetap (DPT) &amp; Kartu RFID</h1>
                        <p class="text-muted small mb-0">Kelola keanggotaan pemilih, pemetaan kartu RFID, dan status hak suara.</p>
                    </div>

                    <button type="button" class="btn btn-kop-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddVoter">
                        <i class="fas fa-plus me-1"></i> Daftarkan Kartu Baru
                    </button>
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

                <!-- Search Filter Form -->
                <div class="admin-card mb-4">
                    <form action="<?= base_url('admin/voters'); ?>" method="GET" class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Cari nama, nomor anggota, atau UID kartu..." value="<?= htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-kop-primary btn-sm">Filter</button>
                            <?php if (!empty($search)): ?>
                            <a href="<?= base_url('admin/voters'); ?>" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Voters Table -->
                <div class="admin-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Anggota</th>
                                    <th>Nama Pemilih</th>
                                    <th>UID Kartu RFID</th>
                                    <th>Status Akses</th>
                                    <th>Status Hak Suara</th>
                                    <th>Waktu Memilih</th>
                                    <th style="width: 140px;" class="text-end">Aksi Pengawas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($voters)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Tidak ditemukan data pemilih yang sesuai.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($voters as $v): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($v['member_number']); ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($v['name']); ?></div>
                                    </td>
                                    <td>
                                        <code class="text-dark bg-light px-2 py-1 rounded border small">
                                            <?= htmlspecialchars($v['rfid_uid']); ?>
                                        </code>
                                    </td>
                                    <td>
                                        <form action="<?= base_url('admin/voter_toggle/' . $v['id']); ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                            <button type="submit" class="badge <?= ($v['status'] === 'active') ? 'bg-success' : 'bg-danger'; ?> border-0" title="Klik untuk ubah status aktif/blokir" style="cursor: pointer;">
                                                <?= ($v['status'] === 'active') ? 'Aktif' : 'Diblokir'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php if ((int)$v['has_voted'] === 1): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fas fa-check me-1"></i> Sudah Memilih
                                        </span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            Belum Memilih
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= (!empty($v['voted_at'])) ? date('d/m/Y H:i:s', strtotime($v['voted_at'])) : '-'; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ((int)$v['has_voted'] === 1): ?>
                                        <button type="button" class="btn btn-outline-warning btn-sm btn-reset-voter" data-id="<?= $v['id']; ?>" data-name="<?= htmlspecialchars($v['name']); ?>" title="Reset status hak suara (Jika terjadi kendala teknis)">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </button>
                                        <?php else: ?>
                                        <span class="text-muted small">-</span>
                                        <?php endif; ?>
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

    <!-- Modal Tambah Anggota & Kartu Baru -->
    <div class="modal fade" id="modalAddVoter" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="<?= base_url('admin/voter_save'); ?>" method="POST">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Daftarkan Kartu RFID Pemilih</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="p-3 bg-light rounded-3 border mb-3 small text-muted">
                            <i class="fas fa-info-circle text-primary me-1"></i>
                            Tempelkan kartu pada reader RFID saat kursor berada di kotak <strong>UID Kartu RFID</strong> untuk membaca nomor kartu secara otomatis.
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">UID Kartu RFID</label>
                            <input type="text" name="rfid_uid" id="modalRfidInput" class="form-control font-monospace" placeholder="Tempelkan kartu RFID ke reader..." required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nomor Anggota Koperasi</label>
                            <input type="text" name="member_number" class="form-control" placeholder="Contoh: A-1006" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lengkap Anggota</label>
                            <input type="text" name="name" class="form-control" placeholder="Nama lengkap sesuai data koperasi" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-kop-primary btn-sm">Simpan Anggota</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="actionPostForm" method="POST" style="display:none;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    </form>

    <script>
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(s) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[s];
        });
    }

    $(document).ready(function() {
        const modalEl = document.getElementById('modalAddVoter');
        modalEl.addEventListener('shown.bs.modal', function () {
            document.getElementById('modalRfidInput').focus();
        });

        $('.btn-reset-voter').on('click', function() {
            const id = $(this).data('id');
            const name = escapeHtml(String($(this).data('name') || ''));

            Swal.fire({
                title: 'Reset Status Pemilih?',
                html: `Apakah Anda yakin ingin mereset hak suara untuk <b>"${name}"</b> agar dapat mencoblos kembali?<br><br><span class="text-danger small">Tindakan ini diawasi dan akan dicatat secara permanen ke jejak audit pengawas.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Reset Hak Suara',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    const $form = $('#actionPostForm');
                    $form.attr('action', '<?= base_url("admin/voter_reset/"); ?>' + id);
                    $form.submit();
                }
            });
        });
    });
    </script>
</body>
</html>
