<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Hasil Pemilihan - <?= htmlspecialchars($settings['cooperative_name']); ?></title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: "Times New Roman", Times, serif;
            color: #000;
        }
        .page-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .kop-header {
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 25px;
            text-align: center;
        }
        .table-report th, .table-report td {
            border: 1px solid #000 !important;
            padding: 6px 10px;
        }
        @media print {
            body { background: #fff; }
            .page-container { box-shadow: none; padding: 0; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print text-center py-3 bg-light border-bottom mb-3">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
            Cetak Berita Acara (PDF / Print)
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3 ms-2">
            Tutup
        </button>
    </div>

    <div class="page-container">
        <!-- Kop Surat -->
        <div class="kop-header">
            <h4 class="fw-bold mb-1 text-uppercase"><?= htmlspecialchars($settings['cooperative_name']); ?></h4>
            <h5 class="fw-bold mb-1 text-uppercase">PANITIA PEMILIHAN PENGURUS &amp; PENGAWAS</h5>
            <p class="mb-0 small">Sistem Pemungutan Suara Elektronik (E-Voting) Berbasis Kartu RFID</p>
        </div>

        <div class="text-center mb-4">
            <h5 class="fw-bold text-decoration-underline mb-1">BERITA ACARA HASIL PEMUNGUTAN SUARA</h5>
            <p class="small text-muted">Nomor: BA-EVOTE/<?= date('Y/m/d'); ?>/01</p>
        </div>

        <p style="text-indent: 30px; text-align: justify;">
            Pada hari ini, <strong><?= date('l, d F Y'); ?></strong>, telah diselenggarakan rapat pemilihan secara elektronik untuk menetapkan kepengurusan dan kepengawasan <strong><?= htmlspecialchars($settings['cooperative_name']); ?></strong> periode berkenaan, dengan rekapitulasi data sebagai berikut:
        </p>

        <!-- Ringkasan Kehadiran DPT -->
        <h6 class="fw-bold mt-4 mb-2">I. DATA PEMILIH TETAP (DPT) &amp; PARTISIPASI</h6>
        <table class="table table-report table-sm mb-4">
            <tr>
                <td style="width: 70%;">Jumlah Anggota dalam DPT</td>
                <td class="fw-bold text-end"><?= number_format($stats['total_voters']); ?> orang</td>
            </tr>
            <tr>
                <td>Jumlah Pemilih yang Menggunakan Hak Suara</td>
                <td class="fw-bold text-end"><?= number_format($stats['voted_count']); ?> orang (<?= $stats['turnout_percentage']; ?>%)</td>
            </tr>
            <tr>
                <td>Jumlah Pemilih yang Tidak Hadir / Belum Memilih</td>
                <td class="fw-bold text-end"><?= number_format($stats['remaining_voters']); ?> orang</td>
            </tr>
            <tr>
                <td>Total Suara Sah Tercatat dalam Ledger Kriptografis</td>
                <td class="fw-bold text-end"><?= number_format($stats['total_votes_recorded']); ?> suara</td>
            </tr>
            <tr>
                <td>Status Integritas Audit Ledger</td>
                <td class="fw-bold text-end"><?= ($integrity['is_valid']) ? 'SAH & UTUH TANPA MANIPULASI' : 'PERINGATAN INTEGRITAS'; ?></td>
            </tr>
        </table>

        <!-- Rekapitulasi Ketua -->
        <h6 class="fw-bold mt-4 mb-2">II. PEROLEHAN SUARA CALON KETUA KOPERASI</h6>
        <table class="table table-report table-sm mb-4">
            <thead>
                <tr class="table-light">
                    <th style="width: 10%;">No.</th>
                    <th>Nama Calon Ketua</th>
                    <th style="width: 25%;" class="text-end">Jumlah Suara</th>
                    <th style="width: 20%;" class="text-end">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['results']['ketua']['candidates'] as $c): ?>
                <tr>
                    <td class="text-center"><?= $c['candidate_number']; ?></td>
                    <td><?= htmlspecialchars($c['name']); ?></td>
                    <td class="text-end fw-bold"><?= number_format($c['vote_count']); ?> suara</td>
                    <td class="text-end"><?= $c['percentage']; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Rekapitulasi Pengawas -->
        <h6 class="fw-bold mt-4 mb-2">III. PEROLEHAN SUARA CALON PENGAWAS KOPERASI</h6>
        <table class="table table-report table-sm mb-4">
            <thead>
                <tr class="table-light">
                    <th style="width: 10%;">No.</th>
                    <th>Nama Calon Pengawas</th>
                    <th style="width: 25%;" class="text-end">Jumlah Suara</th>
                    <th style="width: 20%;" class="text-end">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['results']['pengawas']['candidates'] as $c): ?>
                <tr>
                    <td class="text-center"><?= $c['candidate_number']; ?></td>
                    <td><?= htmlspecialchars($c['name']); ?></td>
                    <td class="text-end fw-bold"><?= number_format($c['vote_count']); ?> suara</td>
                    <td class="text-end"><?= $c['percentage']; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="text-indent: 30px; text-align: justify;" class="mt-4">
            Demikian Berita Acara ini dibuat dengan sebenarnya dan ditandatangani secara sah oleh Panitia Pemilihan serta Pengawas Independen untuk dipergunakan sebagaimana mestinya.
        </p>

        <!-- Tanda Tangan -->
        <div class="row mt-5 text-center">
            <div class="col-6">
                <p class="mb-5">Pengawas Independen,</p>
                <p class="fw-bold text-decoration-underline mb-0">( .................................................... )</p>
                <small class="text-muted">NIP/Nomor Anggota</small>
            </div>
            <div class="col-6">
                <p class="mb-5">Ketua Panitia Pemilihan,</p>
                <p class="fw-bold text-decoration-underline mb-0">( <?= htmlspecialchars($this->session->userdata('admin_name') ?? 'Ketua Panitia'); ?> )</p>
                <small class="text-muted">NIP/Nomor Anggota</small>
            </div>
        </div>
    </div>

</body>
</html>
