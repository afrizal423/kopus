<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undian Doorprize RAT - <?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi'); ?></title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/koperasi.css'); ?>">
    <script src="<?= base_url('assets/vendor/jquery/jquery-3.6.0.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <style>
        .lucky-screen {
            background-color: #0f172a;
            border-radius: 12px;
            color: #f8fafc;
            padding: 2.5rem 1.5rem;
            text-align: center;
            border: 1px solid #334155;
            position: relative;
            overflow: hidden;
        }
        .lucky-roller {
            font-size: 2.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #38bdf8;
            font-family: system-ui, -apple-system, sans-serif;
            text-shadow: 0 2px 10px rgba(56, 189, 248, 0.2);
        }
        .lucky-sub {
            font-size: 1.15rem;
            color: #94a3b8;
            min-height: 32px;
            font-family: monospace;
        }
        .winner-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .winner-tag {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            border: 1px solid #fde68a;
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
                        <h1 class="h3 fw-bold mb-1">Undian Doorprize RAT</h1>
                        <p class="text-muted small mb-0">Pengundian hadiah resmi untuk anggota yang hadir dan telah menggunakan hak suaranya</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3" data-bs-toggle="modal" data-bs-target="#modalLuckyDraw">
                            <i class="fas fa-trophy me-1"></i> Putar Undian Doorprize
                        </button>
                        <a href="<?= base_url('admin/export_doorprize_excel'); ?>" class="btn btn-success btn-sm fw-semibold">
                            <i class="fas fa-file-excel me-1"></i> Unduh Excel (.xlsx)
                        </a>
                    </div>
                </div>

                <!-- Doorprize Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="admin-card h-100">
                            <span class="text-muted small fw-semibold text-uppercase">Peserta Sah Undian</span>
                            <h3 class="fw-bold text-dark mt-2 mb-0"><?= number_format(count($participants), 0, ',', '.'); ?> Orang</h3>
                            <small class="text-muted">Anggota berstatus aktif yang telah memilih</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="admin-card h-100">
                            <span class="text-muted small fw-semibold text-uppercase">Total Kupon / Token Terbit</span>
                            <h3 class="fw-bold text-success mt-2 mb-0"><?= number_format(count($participants), 0, ',', '.'); ?> Kupon</h3>
                            <small class="text-muted">Kupon bernomor unik dari struk suara</small>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-4">
                        <div class="admin-card h-100 bg-light border">
                            <span class="text-muted small fw-semibold text-uppercase">Aturan Doorprize RAT</span>
                            <p class="small text-dark mt-2 mb-0">Hanya anggota yang terdaftar di DPT dan telah memberikan suara di bilik suara yang sah mengikuti pengundian.</p>
                        </div>
                    </div>
                </div>

                <!-- Winner Log Section -->
                <div class="admin-card mb-4" id="winnerSection" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-award text-warning fs-5"></i>
                            <h5 class="fw-bold mb-0">Daftar Pemenang Terpilih (Sesi Ini)</h5>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="btnResetWinners">
                            <i class="fas fa-redo me-1"></i> Reset Pemenang
                        </button>
                    </div>
                    <div id="winnerListContainer"></div>
                </div>

                <!-- Table of Eligible Voters -->
                <div class="admin-card">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">Daftar Kupon Peserta Sah</h5>
                            <p class="text-muted small mb-0">Daftar anggota yang berhak mendapatkan nomor undian</p>
                        </div>

                        <form method="GET" action="<?= base_url('admin/doorprize'); ?>" class="d-flex gap-2">
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nomor kupon, nama, no. anggota..." value="<?= htmlspecialchars($search ?? ''); ?>" style="width: 280px;">
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (!empty($search)): ?>
                            <a href="<?= base_url('admin/doorprize'); ?>" class="btn btn-outline-danger btn-sm" title="Hapus Filter">
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
                                    <th style="width: 150px;" class="text-center">No. Kupon / Token</th>
                                    <th style="width: 140px;">No. Anggota</th>
                                    <th>Nama Anggota</th>
                                    <th style="width: 160px;">Waktu Memilih</th>
                                    <th style="width: 140px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($participants)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <?php if (!empty($search)): ?>
                                        Tidak ditemukan peserta dengan kata kunci "<strong><?= htmlspecialchars($search); ?></strong>".
                                        <?php else: ?>
                                        Belum ada anggota yang melakukan voting. Pengundian dapat dimulai setelah suara masuk.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php $no = 1; foreach ($participants as $p): ?>
                                <tr>
                                    <td class="text-center text-muted small"><?= $no++; ?></td>
                                    <td class="text-center">
                                        <code class="px-2 py-1 bg-light border rounded text-dark fw-bold font-monospace"><?= htmlspecialchars($p['receipt_token']); ?></code>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($p['member_number']); ?></span>
                                    </td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($p['name']); ?></td>
                                    <td class="small text-muted font-monospace">
                                        <?= $p['voted_at'] ? date('d/m/Y H:i:s', strtotime($p['voted_at'])) : '-'; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle fw-semibold">
                                            <i class="fas fa-check-circle me-1"></i> HADIR &amp; MEMILIH
                                        </span>
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

    <!-- Modal Live Lucky Draw Screen -->
    <div class="modal fade" id="modalLuckyDraw" tabindex="-1" aria-labelledby="modalLuckyDrawLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white border-bottom border-secondary">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-gift text-warning fs-5"></i>
                        <h5 class="modal-title fw-bold" id="modalLuckyDrawLabel">Pengundian Doorprize Anggota RAT</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="btnCloseLuckyModal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Config Controls -->
                    <div class="row g-2 mb-3 align-items-center">
                        <div class="col-md-7">
                            <label class="form-label small text-muted mb-1 fw-semibold">Nama Hadiah / Doorprize</label>
                            <input type="text" id="prizeTitle" class="form-control form-control-sm" placeholder="Contoh: Doorprize Utama - TV LED 43 Inch" value="Doorprize RAT Koperasi">
                        </div>
                        <div class="col-md-5 text-md-end">
                            <span class="small text-muted d-block mb-1">Peserta Tersedia</span>
                            <span class="badge bg-primary fs-6 px-3" id="availableParticipantBadge"><?= count($participants); ?> Orang</span>
                        </div>
                    </div>

                    <!-- Screen Display -->
                    <div class="lucky-screen mb-3">
                        <canvas id="confettiCanvas" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:2; display:none;"></canvas>
                        <div class="text-uppercase small tracking-wide text-warning fw-bold mb-2" id="screenPrizeLabel">Doorprize RAT Koperasi</div>
                        <div class="lucky-roller" id="rollerName">--- SIAP DIUNDI ---</div>
                        <div class="lucky-sub" id="rollerSub">Tekan tombol putar di bawah untuk memilih pemenang</div>
                    </div>

                    <!-- Action Controls -->
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-warning btn-lg fw-bold text-dark px-4" id="btnStartDraw" <?= empty($participants) ? 'disabled' : ''; ?>>
                            <i class="fas fa-play me-2"></i> Putar Undian Sekarang
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <small class="text-muted me-auto">Pemenang terpilih akan tersimpan otomatis dan tidak akan diundi ganda.</small>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup Layar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script for Live Roulette Logic and Offline Canvas Confetti -->
    <script>
    (function() {
        const rawParticipants = <?= json_encode($participants, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        let drawnWinnerIds = new Set();
        let isSpinning = false;
        let spinInterval = null;

        const rollerName = document.getElementById('rollerName');
        const rollerSub = document.getElementById('rollerSub');
        const prizeTitle = document.getElementById('prizeTitle');
        const screenPrizeLabel = document.getElementById('screenPrizeLabel');
        const btnStartDraw = document.getElementById('btnStartDraw');
        const btnCloseLuckyModal = document.getElementById('btnCloseLuckyModal');
        const availableBadge = document.getElementById('availableParticipantBadge');
        const winnerSection = document.getElementById('winnerSection');
        const winnerListContainer = document.getElementById('winnerListContainer');
        const confettiCanvas = document.getElementById('confettiCanvas');

        prizeTitle.addEventListener('input', function() {
            screenPrizeLabel.textContent = this.value || 'Doorprize RAT Koperasi';
        });

        function getEligiblePool() {
            return rawParticipants.filter(p => !drawnWinnerIds.has(p.voter_id));
        }

        function updateAvailableBadge() {
            const pool = getEligiblePool();
            availableBadge.textContent = pool.length + ' Orang';
            if (pool.length === 0) {
                btnStartDraw.disabled = true;
            }
        }

        btnStartDraw.addEventListener('click', function() {
            if (isSpinning) return;

            const pool = getEligiblePool();
            if (pool.length === 0) {
                alert('Semua peserta telah terpilih menjadi pemenang dalam sesi ini.');
                return;
            }

            isSpinning = true;
            btnStartDraw.disabled = true;
            btnCloseLuckyModal.disabled = true;
            confettiCanvas.style.display = 'none';

            let speed = 40;
            let elapsed = 0;
            const duration = 3500; // 3.5 seconds spin
            const startTime = Date.now();

            function tick() {
                const randomPick = pool[Math.floor(Math.random() * pool.length)];
                rollerName.textContent = randomPick.name;
                rollerSub.textContent = 'No. Anggota: ' + randomPick.member_number + ' | Tiket: ' + randomPick.receipt_token;

                elapsed = Date.now() - startTime;
                if (elapsed < duration) {
                    spinInterval = setTimeout(tick, speed);
                } else {
                    // Winner selected
                    const finalWinner = pool[Math.floor(Math.random() * pool.length)];
                    finalizeWinner(finalWinner);
                }
            }

            tick();
        });

        function finalizeWinner(winner) {
            drawnWinnerIds.add(winner.voter_id);
            rollerName.textContent = winner.name;
            rollerSub.textContent = 'No. Anggota: ' + winner.member_number + ' | Tiket: ' + winner.receipt_token;
            
            isSpinning = false;
            btnStartDraw.disabled = false;
            btnCloseLuckyModal.disabled = false;
            updateAvailableBadge();

            // Run simple canvas confetti
            runConfetti();

            // Append to Winner List in Main Screen
            recordWinnerCard(winner, prizeTitle.value || 'Doorprize RAT');
        }

        function recordWinnerCard(winner, prize) {
            winnerSection.style.display = 'block';

            const card = document.createElement('div');
            card.className = 'winner-card';
            card.innerHTML = `
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="winner-tag">${escapeHtml(prize)}</span>
                        <strong class="text-dark fs-6">${escapeHtml(winner.name)}</strong>
                    </div>
                    <div class="small text-muted font-monospace">
                        No. Anggota: <strong>${escapeHtml(winner.member_number)}</strong> | Kupon Sah: <code>${escapeHtml(winner.receipt_token)}</code>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">
                        <i class="fas fa-check-circle me-1"></i> Sah
                    </span>
                </div>
            `;
            winnerListContainer.prepend(card);
        }

        document.getElementById('btnResetWinners').addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin mereset daftar pemenang sesi ini? Anggota yang sudah terpilih dapat diundi kembali.')) {
                drawnWinnerIds.clear();
                winnerListContainer.innerHTML = '';
                winnerSection.style.display = 'none';
                updateAvailableBadge();
                rollerName.textContent = '--- SIAP DIUNDI ---';
                rollerSub.textContent = 'Tekan tombol putar di bawah untuk memilih pemenang';
            }
        });

        function escapeHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Lightweight offline canvas particle burst
        function runConfetti() {
            confettiCanvas.style.display = 'block';
            const ctx = confettiCanvas.getContext('2d');
            const w = confettiCanvas.width = confettiCanvas.offsetWidth;
            const h = confettiCanvas.height = confettiCanvas.offsetHeight;

            const particles = [];
            const colors = ['#38bdf8', '#fbbf24', '#34d399', '#f87171', '#a78bfa', '#f472b6'];

            for (let i = 0; i < 70; i++) {
                particles.push({
                    x: w / 2,
                    y: h / 2,
                    vx: (Math.random() - 0.5) * 12,
                    vy: (Math.random() - 0.7) * 12,
                    size: Math.random() * 8 + 4,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    rot: Math.random() * 360,
                    alpha: 1
                });
            }

            let frames = 0;
            function anim() {
                ctx.clearRect(0, 0, w, h);
                let alive = false;
                particles.forEach(p => {
                    p.x += p.vx;
                    p.y += p.vy;
                    p.vy += 0.25; // gravity
                    p.alpha -= 0.015;
                    if (p.alpha > 0) {
                        alive = true;
                        ctx.save();
                        ctx.globalAlpha = p.alpha;
                        ctx.fillStyle = p.color;
                        ctx.translate(p.x, p.y);
                        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                        ctx.restore();
                    }
                });

                frames++;
                if (alive && frames < 80) {
                    requestAnimationFrame(anim);
                } else {
                    ctx.clearRect(0, 0, w, h);
                    confettiCanvas.style.display = 'none';
                }
            }
            requestAnimationFrame(anim);
        }
    })();
    </script>

    <?php $this->load->view('admin/modal_change_password', array('redirect_to' => 'admin/doorprize')); ?>
</body>
</html>
