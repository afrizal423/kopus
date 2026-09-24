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
                        <h1 class="h3 fw-bold mb-1">Manajemen Calon Pengurus &amp; Pengawas</h1>
                        <p class="text-muted small mb-0">Kelola daftar calon kandidat, nomor urut, foto profil, serta visi dan misi.</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalChangePassword">
                            <i class="fas fa-key me-1"></i> Ganti Password
                        </button>
                        <button type="button" class="btn btn-kop-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddCandidate">
                            <i class="fas fa-user-plus me-1"></i> Tambah Calon Baru
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
                                            <script type="application/json" class="cand-raw-json"><?= json_encode($c, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
                                            <button type="button" class="btn btn-outline-secondary btn-edit-candidate"
                                                    data-id="<?= $c['id']; ?>"
                                                    data-category="<?= $c['category']; ?>"
                                                    data-number="<?= $c['candidate_number']; ?>"
                                                    data-name="<?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?>"
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
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <form class="modal-content" action="<?= base_url('admin/candidate_save'); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="id" value="0">

                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success p-2 rounded-circle">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <h5 class="modal-title fw-bold mb-0">Tambah Calon Kandidat Baru</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Panduan Format How-To -->
                    <div class="admin-guide-box mb-4" id="addGuideBox">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-circle">
                                    <i class="fas fa-lightbulb"></i>
                                </span>
                                <span class="fw-bold text-dark">Panduan Format Visi &amp; Misi (Agar Tampil Rapi di Bilik Suara)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-white text-secondary border small">Tips Penulisan</span>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" data-bs-toggle="collapse" data-bs-target="#addGuideCollapse" aria-expanded="true" title="Sembunyikan / Tampilkan Panduan" style="font-size: 0.725rem;">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                            </div>
                        </div>

                        <div class="collapse show mt-3" id="addGuideCollapse">
                            <p class="text-muted small mb-3">
                                Teks yang Anda simpan akan diolah otomatis oleh sistem bilik suara pemilih (kiosk). Agar tampil proporsional, terstruktur, dan nyaman dibaca anggota, ikuti panduan berikut:
                            </p>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <div class="admin-guide-card">
                                        <div class="fw-semibold text-dark small mb-1">
                                            <i class="fas fa-quote-left text-success me-1"></i> Format Visi (Arah Utama)
                                        </div>
                                        <ul class="text-muted small mb-0 ps-3" style="font-size: 0.8rem; line-height: 1.5;">
                                            <li>Tulis <strong>1 - 2 kalimat padat</strong> yang jelas dan berbobot.</li>
                                            <li>Hindari menulis paragraf panjang tanpa jeda.</li>
                                            <li><span class="text-success fw-medium">Tampilan:</span> Muncul sebagai kutipan 2 baris pada kartu pemilih dan tampil utuh dengan ikon kutip elegan pada modal detail calon.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-guide-card">
                                        <div class="fw-semibold text-dark small mb-1">
                                            <i class="fas fa-list-ol text-primary me-1"></i> Format Misi (Poin Terstruktur)
                                        </div>
                                        <ul class="text-muted small mb-0 ps-3" style="font-size: 0.8rem; line-height: 1.5;">
                                            <li>Gunakan <strong>nomor per baris baru</strong> (<code>1.</code>, <code>2.</code>, <code>3.</code>, dst).</li>
                                            <li>Sertakan <strong>Judul Program:</strong> diakhiri titik dua (<code>:</code>) sebelum penjelasan.</li>
                                            <li><span class="text-primary fw-medium">Tampilan:</span> Sistem otomatis membuat <strong>lencana nomor bulat</strong> dan judul program <strong>dicetak tebal</strong>.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white border rounded-2 p-2 px-3 small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-secondary fw-semibold" style="font-size: 0.725rem; letter-spacing: 0.04em;">CONTOH FORMAT MISI YANG DIANJURKAN:</span>
                                    <button type="button" class="btn btn-outline-success btn-sm py-0 px-2 btn-insert-example" data-target="#addCandMission" style="font-size: 0.725rem;">
                                        <i class="fas fa-paste me-1"></i> Terapkan Contoh ke Form
                                    </button>
                                </div>
                                <pre class="admin-code-example">1. Akselerasi Digitalisasi: Mengembangkan sistem e-voting dan administrasi simpan pinjam berbasis web.
2. Penguatan Permodalan: Meningkatkan alokasi plafon pinjaman produktif bagi usaha anggota UMKM.
3. Transparansi Finansial: Mempublikasikan laporan neraca dan SHU secara realtime setiap kuartal.</pre>
                            </div>
                        </div>
                    </div>

                    <!-- Form Kolom Calon -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kategori Pemilihan</label>
                            <select name="category" id="addCandCategory" class="form-select" required>
                                <option value="ketua">Ketua Koperasi</option>
                                <option value="pengawas">Pengawas Koperasi</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor Urut</label>
                            <input type="number" name="candidate_number" id="addCandNumber" class="form-control" min="1" max="99" placeholder="Contoh: 1" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap Calon (Beserta Gelar)</label>
                        <input type="text" name="name" id="addCandName" class="form-control" placeholder="Contoh: Budi Santoso, S.E." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Unggah Foto Profil Calon</label>
                        <input type="file" name="photo" class="form-control" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                        <small class="text-muted">Format yang diizinkan: JPG, PNG, WEBP, SVG. Maksimal 2MB. Jika dikosongkan, avatar default akan digunakan.</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Visi Calon</label>
                            <span class="text-muted small" style="font-size: 0.75rem;">Disarankan: 1 - 2 kalimat padat</span>
                        </div>
                        <textarea name="vision" id="addCandVision" class="form-control" rows="3" placeholder="Contoh: Terwujudnya Koperasi KOPUS yang mandiri, transparan, dan mampu menyejahterakan seluruh anggotanya melalui inovasi berkelanjutan."></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Misi Calon</label>
                            <span class="text-muted small" style="font-size: 0.75rem;">Format per baris: <code>1. Judul Program: Penjelasan...</code></span>
                        </div>
                        <textarea name="mission" id="addCandMission" class="form-control" rows="6" placeholder="1. Akselerasi Digitalisasi: Penjelasan program kerja...&#10;2. Penguatan Permodalan: Penjelasan program kerja...&#10;3. Transparansi Finansial: Penjelasan program kerja..."></textarea>
                    </div>

                    <!-- Live Preview Box -->
                    <div class="card border rounded-3 mt-3">
                        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-eye text-primary"></i>
                                <span class="fw-bold small text-dark">Pratinjau Tampilan Bilik Suara (Live Preview)</span>
                            </div>
                            <span class="badge bg-light text-muted border small" style="font-size: 0.7rem;">Real-time Render</span>
                        </div>
                        <div class="card-body p-3 bg-light-subtle">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-secondary text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Visi di Bilik Suara:</span>
                                    <span class="badge bg-success-subtle text-success small" style="font-size: 0.675rem;">Modal Detail</span>
                                </div>
                                <div class="modal-vision-box m-0" id="addVisionPreviewBox">
                                    <i class="fas fa-quote-left modal-vision-icon"></i>
                                    <div class="modal-vision-text" id="addVisionPreviewText">(Visi calon belum diisi)</div>
                                </div>
                            </div>

                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-secondary text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Daftar Program Misi:</span>
                                    <span class="badge bg-light text-dark border small" id="addMissionPreviewCount">0 Program</span>
                                </div>
                                <div id="addMissionPreviewList">
                                    <div class="text-muted fst-italic p-2 bg-white border rounded-2 small text-center">Belum ada poin misi yang diketik.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-kop-primary btn-sm">Simpan Calon</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Calon -->
    <div class="modal fade" id="modalEditCandidate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <form class="modal-content" action="<?= base_url('admin/candidate_save'); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="id" id="editCandId" value="0">

                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                            <i class="fas fa-user-edit"></i>
                        </span>
                        <h5 class="modal-title fw-bold mb-0">Ubah Data Calon</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Panduan Format How-To -->
                    <div class="admin-guide-box mb-4" id="editGuideBox">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-circle">
                                    <i class="fas fa-lightbulb"></i>
                                </span>
                                <span class="fw-bold text-dark">Panduan Penulisan Visi &amp; Misi (Agar Tampil Rapi di Bilik Suara)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-white text-secondary border small">Tips Penulisan</span>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" data-bs-toggle="collapse" data-bs-target="#editGuideCollapse" aria-expanded="true" title="Sembunyikan / Tampilkan Panduan" style="font-size: 0.725rem;">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                            </div>
                        </div>

                        <div class="collapse show mt-3" id="editGuideCollapse">
                            <p class="text-muted small mb-3">
                                Format teks yang Anda simpan akan diolah otomatis oleh sistem bilik suara pemilih (kiosk). Agar tampil proporsional, terstruktur, dan tidak berantakan, perhatikan petunjuk di bawah:
                            </p>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <div class="admin-guide-card">
                                        <div class="fw-semibold text-dark small mb-1">
                                            <i class="fas fa-quote-left text-success me-1"></i> Tips Penulisan Visi
                                        </div>
                                        <ul class="text-muted small mb-0 ps-3" style="font-size: 0.8rem; line-height: 1.5;">
                                            <li>Tuliskan <strong>1 - 2 kalimat padat</strong> yang jelas dan berbobot.</li>
                                            <li>Hindari menulis paragraf panjang tanpa jeda titik.</li>
                                            <li><span class="text-success fw-medium">Tampilan:</span> Muncul sebagai kutipan 2 baris pada kartu pemilih dan tampil utuh dengan ikon kutip elegan pada modal detail calon.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-guide-card">
                                        <div class="fw-semibold text-dark small mb-1">
                                            <i class="fas fa-list-ol text-primary me-1"></i> Tips Penulisan Misi
                                        </div>
                                        <ul class="text-muted small mb-0 ps-3" style="font-size: 0.8rem; line-height: 1.5;">
                                            <li>Gunakan <strong>nomor per baris baru</strong> (<code>1.</code>, <code>2.</code>, <code>3.</code>, dst).</li>
                                            <li>Sertakan <strong>Judul Program:</strong> diakhiri titik dua (<code>:</code>) sebelum penjelasan.</li>
                                            <li><span class="text-primary fw-medium">Tampilan:</span> Sistem otomatis membuat <strong>lencana nomor bulat</strong> dan judul program <strong>dicetak tebal</strong>.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white border rounded-2 p-2 px-3 small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-secondary fw-semibold" style="font-size: 0.725rem; letter-spacing: 0.04em;">CONTOH FORMAT MISI YANG DIANJURKAN:</span>
                                    <button type="button" class="btn btn-outline-success btn-sm py-0 px-2 btn-insert-example" data-target="#editCandMission" style="font-size: 0.725rem;">
                                        <i class="fas fa-paste me-1"></i> Terapkan Contoh ke Form
                                    </button>
                                </div>
                                <pre class="admin-code-example">1. Akselerasi Digitalisasi: Mengembangkan super-app e-voting dan administrasi simpan pinjam berbasis web.
2. Penguatan Permodalan: Meningkatkan alokasi plafon pinjaman produktif bagi usaha anggota UMKM.
3. Transparansi Finansial: Mempublikasikan laporan neraca dan SHU secara realtime setiap kuartal.</pre>
                            </div>
                        </div>
                    </div>

                    <!-- Form Kolom Calon -->
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
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Visi Calon</label>
                            <span class="text-muted small" style="font-size: 0.75rem;">Disarankan: 1 - 2 kalimat padat</span>
                        </div>
                        <textarea name="vision" id="editCandVision" class="form-control" rows="3" placeholder="Tuliskan visi singkat calon..."></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Misi Calon</label>
                            <span class="text-muted small" style="font-size: 0.75rem;">Format per baris: <code>1. Judul Program: Penjelasan...</code></span>
                        </div>
                        <textarea name="mission" id="editCandMission" class="form-control" rows="6" placeholder="1. Judul Program: Penjelasan..."></textarea>
                    </div>

                    <!-- Live Preview Box -->
                    <div class="card border rounded-3 mt-3">
                        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-eye text-primary"></i>
                                <span class="fw-bold small text-dark">Pratinjau Tampilan Bilik Suara (Live Preview)</span>
                            </div>
                            <span class="badge bg-light text-muted border small" style="font-size: 0.7rem;">Real-time Render</span>
                        </div>
                        <div class="card-body p-3 bg-light-subtle">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-secondary text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Visi di Bilik Suara:</span>
                                    <span class="badge bg-success-subtle text-success small" style="font-size: 0.675rem;">Modal Detail</span>
                                </div>
                                <div class="modal-vision-box m-0" id="editVisionPreviewBox">
                                    <i class="fas fa-quote-left modal-vision-icon"></i>
                                    <div class="modal-vision-text" id="editVisionPreviewText">(Visi calon belum diisi)</div>
                                </div>
                            </div>

                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-secondary text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Daftar Program Misi:</span>
                                    <span class="badge bg-light text-dark border small" id="editMissionPreviewCount">0 Program</span>
                                </div>
                                <div id="editMissionPreviewList">
                                    <div class="text-muted fst-italic p-2 bg-white border rounded-2 small text-center">Belum ada poin misi yang diketik.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-kop-primary btn-sm">Perbarui Calon</button>
                </div>
            </form>
        </div>
    </div>

    <form id="actionPostForm" method="POST" style="display:none;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    </form>

    <script>
    $(document).ready(function() {
        const modalEdit = new bootstrap.Modal(document.getElementById('modalEditCandidate'));

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderMissionPreview(missionRaw, isKetua) {
            if (!missionRaw || !missionRaw.trim()) {
                return {
                    countText: '0 Program',
                    html: '<div class="text-muted fst-italic p-2 bg-white border rounded-2 small text-center">Belum ada poin misi yang diketik.</div>'
                };
            }

            const lines = missionRaw.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
            const countText = lines.length + ' Program Misi';

            let html = '<div class="modal-mission-list">';
            let itemIndex = 1;
            const badgeClass = isKetua ? 'mission-num-badge badge-ketua' : 'mission-num-badge';

            lines.forEach(line => {
                let cleanLine = line.replace(/^(\d+[\.\)]|\-|\*)\s*/, '');
                let badgeNum = itemIndex++;

                let titlePart = '';
                let descPart = cleanLine;
                const colonIdx = cleanLine.indexOf(':');
                if (colonIdx > 0 && colonIdx < 55) {
                    titlePart = cleanLine.substring(0, colonIdx);
                    descPart = cleanLine.substring(colonIdx + 1).trim();
                }

                html += `
                    <div class="modal-mission-item py-2 px-2.5">
                        <span class="${badgeClass}">${badgeNum}</span>
                        <div class="mission-item-text" style="font-size: 0.85rem;">
                            ${titlePart ? `<span class="mission-lead-title">${escapeHtml(titlePart)}:</span> ` : ''}
                            <span>${escapeHtml(descPart)}</span>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            return { countText: countText, html: html };
        }

        function updateLivePreview(mode) {
            const isEdit = (mode === 'edit');
            const prefix = isEdit ? 'edit' : 'add';
            const catVal = $(`#${prefix}CandCategory`).val() || 'ketua';
            const isKetua = (catVal === 'ketua');

            const visionVal = $(`#${prefix}CandVision`).val() || '';
            const missionVal = $(`#${prefix}CandMission`).val() || '';

            // Update vision preview
            const $visText = $(`#${prefix}VisionPreviewText`);
            if (visionVal.trim()) {
                $visText.text('"' + visionVal.trim() + '"').removeClass('text-muted fst-italic');
            } else {
                $visText.text('(Visi calon belum diisi)').addClass('text-muted fst-italic');
            }

            // Update mission preview
            const res = renderMissionPreview(missionVal, isKetua);
            $(`#${prefix}MissionPreviewCount`).text(res.countText);
            $(`#${prefix}MissionPreviewList`).html(res.html);

            // Update border indicator
            const $guideBox = $(`#${prefix}GuideBox`);
            if (isKetua) {
                $guideBox.removeClass('guide-pengawas');
            } else {
                $guideBox.addClass('guide-pengawas');
            }
        }

        // Live input handlers
        $('#addCandVision, #addCandMission, #addCandCategory').on('input change', function() {
            updateLivePreview('add');
        });

        $('#editCandVision, #editCandMission, #editCandCategory').on('input change', function() {
            updateLivePreview('edit');
        });

        $('#modalAddCandidate').on('show.bs.modal', function() {
            updateLivePreview('add');
        });

        // Insert example button handler
        $('.btn-insert-example').on('click', function() {
            const targetSelector = $(this).data('target');
            const $target = $(targetSelector);
            const exampleText = `1. Akselerasi Digitalisasi: Mengembangkan super-app e-voting dan administrasi simpan pinjam berbasis web.\n2. Penguatan Permodalan: Meningkatkan alokasi plafon pinjaman produktif bagi usaha anggota UMKM.\n3. Transparansi Finansial: Mempublikasikan laporan neraca dan SHU secara realtime setiap kuartal.`;

            if ($target.val().trim().length > 0) {
                Swal.fire({
                    title: 'Terapkan Format Contoh?',
                    text: 'Teks misi yang sudah ada akan digantikan dengan struktur contoh rekomendasi.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0f5132',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Terapkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $target.val(exampleText).trigger('input');
                    }
                });
            } else {
                $target.val(exampleText).trigger('input');
            }
        });

        // Edit button click handler
        $('.btn-edit-candidate').on('click', function() {
            let candData = {};
            try {
                const rawJson = $(this).closest('tr').find('.cand-raw-json').text();
                if (rawJson) {
                    candData = JSON.parse(rawJson);
                }
            } catch (e) {
                console.error('Error parsing candidate JSON', e);
            }

            const id = candData.id || $(this).data('id');
            const cat = candData.category || $(this).data('category');
            const num = candData.candidate_number || $(this).data('number');
            const name = candData.name || $(this).data('name');
            const vision = (candData.vision !== undefined) ? candData.vision : $(this).data('vision');
            const mission = (candData.mission !== undefined) ? candData.mission : $(this).data('mission');

            $('#editCandId').val(id);
            $('#editCandCategory').val(cat);
            $('#editCandNumber').val(num);
            $('#editCandName').val(name);
            $('#editCandVision').val(vision);
            $('#editCandMission').val(mission);

            updateLivePreview('edit');
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
    <?php $this->load->view('admin/modal_change_password', array('current_page' => 'admin/candidates')); ?>
</body>
</html>
