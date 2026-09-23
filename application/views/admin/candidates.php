<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Calon - E-Voting Koperasi</title>
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
                    <a href="<?= base_url('admin/candidates'); ?>" class="active">
                        <i class="fas fa-users-cog"></i>
                        <span>Manajemen Calon</span>
                    </a>
                    <a href="<?= base_url('admin/voters'); ?>">
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
                        <h1 class="h3 fw-bold mb-1">Manajemen Calon Pengurus &amp; Pengawas</h1>
                        <p class="text-muted small mb-0">Kelola daftar calon kandidat, nomor urut, foto profil, serta visi dan misi.</p>
                    </div>

                    <button type="button" class="btn btn-kop-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddCandidate">
                        <i class="fas fa-user-plus me-1"></i> Tambah Calon Baru
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

                <!-- Candidates Table Card -->
                <div class="admin-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;">No. Urut</th>
                                    <th style="width: 70px;">Foto</th>
                                    <th>Nama Calon</th>
                                    <th>Kategori</th>
                                    <th>Visi &amp; Misi</th>
                                    <th style="width: 100px;">Suara Masuk</th>
                                    <th style="width: 100px;">Status</th>
                                    <th style="width: 120px;" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($candidates)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Belum ada data calon yang didaftarkan.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($candidates as $c): ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge <?= ($c['category'] === 'ketua') ? 'bg-warning text-dark' : 'bg-info text-dark'; ?> fs-6 fw-bold">
                                            #<?= $c['candidate_number']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <img src="<?= base_url($c['photo']); ?>" alt="<?= htmlspecialchars($c['name']); ?>" class="rounded-circle border" style="width: 48px; height: 48px; object-fit: cover;" onerror="this.src='<?= base_url('assets/foto/default-avatar.svg'); ?>'">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($c['name']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($c['category'] === 'ketua') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle'; ?> text-capitalize">
                                            <?= $c['category']; ?> Koperasi
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-muted small text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($c['vision']); ?>">
                                            <strong>Visi:</strong> <?= htmlspecialchars($c['vision']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= number_format($c['vote_count']); ?> suara
                                        </span>
                                    </td>
                                    <td>
                                        <form action="<?= base_url('admin/candidate_toggle/' . $c['id']); ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                            <button type="submit" class="badge <?= ($c['is_active'] == 1) ? 'bg-success' : 'bg-secondary'; ?> border-0" title="Klik untuk mengubah status aktif" style="cursor: pointer;">
                                                <?= ($c['is_active'] == 1) ? 'Aktif' : 'Nonaktif'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-edit-candidate"
                                                    data-id="<?= $c['id']; ?>"
                                                    data-category="<?= $c['category']; ?>"
                                                    data-number="<?= $c['candidate_number']; ?>"
                                                    data-name="<?= htmlspecialchars($c['name']); ?>"
                                                    data-vision="<?= htmlspecialchars($c['vision']); ?>"
                                                    data-mission="<?= htmlspecialchars($c['mission']); ?>"
                                                    title="Edit Data">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <?php if ((int)$c['vote_count'] > 0): ?>
                                            <button type="button" class="btn btn-outline-secondary disabled" title="Tidak dapat dihapus karena sudah ada suara masuk" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-outline-danger btn-delete-candidate" data-id="<?= $c['id']; ?>" data-name="<?= htmlspecialchars($c['name']); ?>" title="Hapus Calon">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
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

    <!-- Modal Tambah Calon Baru -->
    <div class="modal fade" id="modalAddCandidate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="<?= base_url('admin/candidate_save'); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="id" value="0">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Calon Kandidat Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Kategori Pemilihan</label>
                                <select name="category" class="form-select" required>
                                    <option value="ketua">Ketua Koperasi</option>
                                    <option value="pengawas">Pengawas Koperasi</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Urut</label>
                                <input type="number" name="candidate_number" class="form-control" min="1" max="99" placeholder="Contoh: 1" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lengkap Calon (Beserta Gelar)</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso, S.E." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Unggah Foto Profil Calon</label>
                            <input type="file" name="photo" class="form-control" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                            <small class="text-muted">Format yang diizinkan: JPG, PNG, WEBP, SVG. Maksimal 2MB. Jika dikosongkan, avatar default akan digunakan.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Visi Calon</label>
                            <textarea name="vision" class="form-control" rows="2" placeholder="Tuliskan visi singkat calon..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Misi Calon</label>
                            <textarea name="mission" class="form-control" rows="3" placeholder="Tuliskan poin-poin misi calon..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-kop-primary btn-sm">Simpan Calon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Calon -->
    <div class="modal fade" id="modalEditCandidate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="<?= base_url('admin/candidate_save'); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="id" id="editCandId" value="0">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Ubah Data Calon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Kategori Pemilihan</label>
                                <select name="category" id="editCandCategory" class="form-select" required>
                                    <option value="ketua">Ketua Koperasi</option>
                                    <option value="pengawas">Pengawas Koperasi</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Urut</label>
                                <input type="number" name="candidate_number" id="editCandNumber" class="form-control" min="1" max="99" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lengkap Calon</label>
                            <input type="text" name="name" id="editCandName" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Ganti Foto Profil (Opsional)</label>
                            <input type="file" name="photo" class="form-control" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto yang ada.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Visi Calon</label>
                            <textarea name="vision" id="editCandVision" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Misi Calon</label>
                            <textarea name="mission" id="editCandMission" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-kop-primary btn-sm">Perbarui Calon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="actionPostForm" method="POST" style="display:none;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    </form>

    <script>
    $(document).ready(function() {
        const modalEdit = new bootstrap.Modal(document.getElementById('modalEditCandidate'));

        $('.btn-edit-candidate').on('click', function() {
            const id = $(this).data('id');
            const cat = $(this).data('category');
            const num = $(this).data('number');
            const name = $(this).data('name');
            const vision = $(this).data('vision');
            const mission = $(this).data('mission');

            $('#editCandId').val(id);
            $('#editCandCategory').val(cat);
            $('#editCandNumber').val(num);
            $('#editCandName').val(name);
            $('#editCandVision').val(vision);
            $('#editCandMission').val(mission);

            modalEdit.show();
        });

        $('.btn-delete-candidate').on('click', function() {
            const id = $(this).data('id');
            const name = String($(this).data('name') || '');

            Swal.fire({
                title: 'Hapus Calon?',
                text: 'Apakah Anda yakin ingin menghapus "' + name + '" dari daftar calon?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const $form = $('#actionPostForm');
                    $form.attr('action', '<?= base_url("admin/candidate_delete/"); ?>' + id);
                    $form.submit();
                }
            });
        });
    });
    </script>
</body>
</html>
