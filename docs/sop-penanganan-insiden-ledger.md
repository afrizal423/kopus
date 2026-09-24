# Standar Operasional Prosedur (SOP) Penanganan Insiden Keamanan & Kecurangan Database (Ledger Tampering)

**Sistem:** E-Voting Koperasi (KOPUS)  
**Komponen Pengaman:** HMAC-SHA256 Cryptographic Hash Chaining (`Voting_model::verify_ledger_integrity`)  
**Sasaran Dokumen:** Panitia Pemilihan, Badan Pengawas Independen, Saksi Calon, dan Administrator Sistem / DBA.  

---

> [!CAUTION]
> **PRINSIP UTAMA: JANGAN LANGSUNG ME-RESET DATABASE!**  
> Melakukan reset database secara terburu-buru akan **menghapus seluruh barang bukti digital (digital forensic evidence)**. Tanpa bukti forensik, panitia tidak dapat membuktikan siapa pelakunya, suara siapa yang diubah atau dihapus, dan proses pemilihan akan kehilangan legitimasi hukum di hadapan anggota koperasi.

---

## 1. Indikasi & Pemicu Insiden (Kapan SOP Ini Berlaku)

SOP ini wajib diaktifkan seketika jika salah satu kondisi berikut terpenuhi:

| Indikasi / Gejala | Lokasi Temuan | Kategori Ancaman |
| :--- | :--- | :--- |
| **Badge Merah:** `Manipulasi Terdeteksi!` | [Admin Dashboard](/application/views/admin/dashboard.php) (Widget Integritas Ledger) | Nilai hash data suara tidak sesuai perhitungan HMAC |
| **Pesan Alert:** `Kecurangan terdeteksi pada Baris #X...` | Pop-up SweetAlert2 tombol **Uji Keutuhan Ledger** | Isi data kandidat atau timestamp di database telah diedit manual |
| **Pesan Alert:** `Integritas rantai terputus pada Baris #X...` | Pop-up SweetAlert2 tombol **Uji Keutuhan Ledger** | Terdapat baris suara yang dihapus atau disisipkan secara ilegal |
| **Status Berita Acara:** `PERINGATAN INTEGRITAS` | Cetak Berita Acara ([export_report.php](/application/views/admin/export_report.php)) | Hasil pemilihan tidak sah untuk ditandatangani |
| **Selisih Jumlah:** DPT Hadir ≠ Total Suara Masuk | Dashboard Admin / Query Audit | Anomali penggelembungan atau pengurangan suara |

---

## 2. Alur Kerja Penanganan Insiden (5 Fase Kritis)

```
[ INDIKASI KECURANGAN TERDETEKSI ]
                 │
                 ▼
┌─────────────────────────────────┐
│ FASE 1: Pembekuan Sistem        │ ──► Ubah status pemilihan ke 'PAUSED'
└─────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────┐
│ FASE 2: Amankan Barang Bukti    │ ──► mysqldump, screenshot, salin audit_logs
└─────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────┐
│ FASE 3: Investigasi Forensik    │ ──► Cek ID rusak, rekonsiliasi token, cek query log
└─────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────┐
│ FASE 4: Sidang Pleno Bersama    │ ──► Panitia + Pengawas + Saksi tentukan opsi
└─────────────────────────────────┘
                 │
       ┌─────────┴─────────┐
       ▼                   ▼
 [ OPSI A: Koreksi ]  [ OPSI B: Reset & PSU ]
 (Jika bukti jelas    (Jika kerusakan masif
  & terisolasi)        & data hilang)
```

---

## 3. Rincian Langkah Penanganan

### Fase 1: Pembekuan Sistem (*Freeze & Pause*) — Waktu Respon < 3 Menit
Tujuan: Menghentikan masuknya suara baru agar rantai hash tidak semakin rusak dan pemilih lain tidak dirugikan.

1. **Login ke Akun Admin / Pengawas.**
2. Buka menu **Pengaturan Sesi Pemilihan** di Dashboard Admin.
3. Ubah kolom **Status Pemilihan** dari `Buka (open)` menjadi **`Ditunda (paused)`**.
4. Klik **Simpan Pengaturan**.
5. **Dampak Langsung:** 
   * Mesin bilik suara ([Voting_model.php:63-67](/application/models/Voting_model.php#L63-L67)) otomatis menolak setiap request coblos dengan rollback transaksi database.
   * Layar bilik suara akan menampilkan status bahwa pemilihan sedang ditangguhkan panitia.

---

### Fase 2: Pengamanan Barang Bukti Forensik (*Preservation of Evidence*)
Tujuan: Mengamankan salinan data orisinal pada saat insiden sebelum dilakukan intervensi teknis apa pun.

1. **Ambil Tangkapan Layar (Screenshot):**
   * Tangkap layar widget *"Manipulasi Terdeteksi!"* lengkap dengan nomor baris `corrupted_id` dan jam sistem.
2. **Lakukan Dump Database Utuh (Beri cap waktu presisi):**
   Buka terminal server/command prompt (pastikan tidak menimpa database utama):
   ```bash
   mysqldump -u root -p vote_koperasi > incident_evidence_%DATE:~10,4%%DATE:~4,2%%DATE:~7,2%_%TIME:~0,2%%TIME:~3,2%.sql
   ```
3. **Ekspor Tabel Audit Log & Verifikasi Tamper:**
   Eksekusi query berikut untuk menyimpan riwayat aksi user terakhir:
   ```sql
   SELECT * FROM audit_logs ORDER BY id DESC LIMIT 100;
   ```
4. **Kunci Akses Server / phpMyAdmin:**
   * Cabut sementara akses publik/intranet ke phpMyAdmin atau ganti kredensial database root jika dicurigai adanya kebocoran akses.

---

### Fase 3: Investigasi Forensik & Audit Data (*Forensic Analysis*)
Tujuan: Mengetahui metode manipulasi, baris terdampak, dan siapa yang bertanggung jawab.

#### A. Identifikasi Baris yang Rusak
Periksa baris ID yang dilaporkan oleh pesan error:
```sql
SELECT id, candidate_id, previous_hash, vote_hash, receipt_token, created_at 
FROM votes 
WHERE id >= (BARIS_CORRUPTED_ID - 1) 
ORDER BY id ASC 
LIMIT 5;
```

#### B. Rekonsiliasi Jumlah Pemilih vs Jumlah Suara
Jalankan query audit berikut:
```sql
SELECT 
    (SELECT COUNT(*) FROM voters WHERE has_voted = 1) AS total_pemilih_hadir,
    (SELECT COUNT(*) FROM votes) AS total_suara_dalam_ledger,
    ((SELECT COUNT(*) FROM voters WHERE has_voted = 1) - (SELECT COUNT(*) FROM votes)) AS selisih_suara;
```
* **Selisih > 0:** Ada baris suara yang **dihapus paksa** dari database.
* **Selisih < 0:** Ada baris suara fiktif yang **disuntikkan tanpa melalui bilik suara resmi**.
* **Selisih = 0:** Jumlah data cocok, namun isi pilihan kandidat atau waktu coblos **telah diedit**.

#### C. Validasi Token Tanda Terima Pemilih (*Receipt Token*)
Setiap pemilih yang sah memegang bukti token coblos fisik/digital (format: `KOP-XXXXXXXX`).
* Cari apakah token pada baris `corrupted_id` cocok dengan token yang dipegang pemilih:
  ```sql
  SELECT * FROM votes WHERE receipt_token = 'KOP-XXXXXXXX';
  ```

#### D. Penelusuran Jejak Akses (Audit Trail)
Cek tabel `audit_logs` dan log server Apache/MySQL untuk memeriksa aktivitas pada menit kejadian:
```sql
SELECT * FROM audit_logs 
WHERE created_at >= (NOW() - INTERVAL 2 HOUR) 
ORDER BY id DESC;
```

---

### Fase 4: Rapat Pleno & Matriks Keputusan
Kumpulkan **Ketua Panitia**, **Seluruh Saksi Calon**, dan **Badan Pengawas Koperasi**. Paparkan temuan forensik secara transparan.

Tentukan langkah tindak lanjut berdasarkan matriks berikut:

```
                                 APAKAH TERJADI 
                             KERUSAKAN SISTEMIK?
                            (Banyak suara hilang /
                            pelaku tidak terlacak /
                            token tidak sinkron)
                                   /       \
                              YA  /         \ TIDAK (Hanya 1 baris terisolasi,
                                 /           \ bukti receipt token fisik cocok)
                                /             \
                               ▼               ▼
                       [ PILIH OPSI B ]  [ PILIH OPSI A ]
                       Pemungutan Suara   Rekonstruksi Baris
                       Ulang (PSU Total)  Terisolasi
```

#### Opsi A: Rekonstruksi Baris Terisolasi
* **Syarat:** Kerusakan hanya terjadi pada 1–2 baris akibat kesalahan teknis/pengujian manual, bukti token fisik pemilih sah tersedia, dan disepakati secara aklamasi oleh semua saksi.
* **Tindakan:**
  1. Kembalikan data baris ke nilai aslinya.
  2. Jalankan ulang tombol **"Uji Keutuhan Ledger"** di dashboard.
  3. Pastikan status berubah hijau kembali (`Database Utuh & Valid`).
  4. Terbitkan **Berita Acara Rekonstruksi Insiden** yang ditandatangani seluruh saksi.
  5. Ubah status pemilihan kembali ke `open`.

#### Opsi B: Pembatalan Putaran & Pemungutan Suara Ulang (PSU)
* **Syarat:** Terjadi manipulasi masif, penghapusan baris suara yang tidak diketahui pemiliknya, atau saksi menolak legitimasi data yang tercemar.
* **Tindakan:** Lakukan Reset Terkontrol sesuai Fase 5.

---

### Fase 5: Prosedur Reset Terkontrol (Khusus Skenario PSU)

> [!WARNING]
> Prosedur ini **hanya boleh dieksekusi** setelah Berita Acara Pembatalan ditandatangani oleh Ketua Panitia, Badan Pengawas, dan para Saksi.

Eksekusi skrip SQL reset berikut secara berurutan:

```sql
-- 1. Kosongkan seluruh rekaman suara yang tercemar
TRUNCATE TABLE votes;

-- 2. Kembalikan hak suara seluruh anggota DPT (status belum memilih)
UPDATE voters 
SET has_voted = 0, voted_at = NULL;

-- 3. Rotasi Salt Kunci Rahasia Ledger Baru
-- Menjamin pelaku yang memiliki salt lama tidak dapat memanipulasi ledger baru
UPDATE election_settings 
SET setting_value = MD5(CONCAT(NOW(), UUID(), RAND())) 
WHERE setting_key = 'ledger_secret_salt';

-- 4. Catat peristiwa Reset Resmi ke dalam Audit Log
INSERT INTO audit_logs (event_type, actor, ip_address, details, created_at)
VALUES (
    'RESET_ELECTION_PSU', 
    'PANITIA_PLENO', 
    '127.0.0.1', 
    'Pelaksanaan Reset Database untuk Pemungutan Suara Ulang (PSU) sesuai Berita Acara', 
    NOW()
);

-- 5. Buka kembali sesi pemilihan
UPDATE election_settings 
SET setting_value = 'open' 
WHERE setting_key = 'election_status';
```

---

## 4. Format Lampiran Berita Acara Insiden (Template)

Ketika insiden terjadi, panitia wajib menyusun dokumen fisik rangkap 3 (Panitia, Pengawas, Saksi) dengan format minimal berikut:

```text
================================================================================
               BERITA ACARA INSIDEN INTEGRITAS DATA PEMILIHAN
                        KOPERASI [NAMA KOPERASI]
================================================================================

Pada hari ini, ................. Tanggal ..... Bulan ................. Tahun .......
Pukul ............. WIB, bertempat di ............................................,
telah ditemukan indikasi ketidaksesuaian integritas data pada sistem e-voting KOPUS
dengan rincian forensik sebagai berikut:

1. Nomor Baris Suara Terdampak (Corrupted ID) : Baris #.........
2. Pesan Peringatan Sistem                   : .................................
3. Jumlah DPT Tercatat Hadir                 : ......... orang
4. Total Suara dalam Ledger Kriptografis     : ......... suara
5. Selisih Suara                             : ......... suara
6. Waktu Deteksi Terakhir                    : Pukul ......... WIB

TINDAKAN YANG DIAMBIL:
[  ] OPSI A: Rekonstruksi Baris Terisolasi (Rantai Hash kembali Valid)
[  ] OPSI B: Pembatalan Putaran Pemilihan & Pelaksanaan Pemungutan Suara Ulang (PSU)

Demikian Berita Acara ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana
mestinya demi menjamin asas pemilihan yang Langsung, Umum, Bebas, Rahasia, Jujur,
dan Adil.

Pihak yang Menyetujui & Menandatangani:

    Ketua Panitia                  Badan Pengawas                Saksi Calon 1
(....................)         (....................)        (....................)

     Saksi Calon 2                  Saksi Calon 3              Administrator Sistem
(....................)         (....................)        (....................)
================================================================================
```

---

## 5. Rekomendasi Mitigasi Pencegahan (Preventive Controls)

Agar insiden manipulasi database tidak terulang:
1. **Batasi Hak Akses Database (Principle of Least Privilege):** Akun aplikasi e-voting di [database.php](/application/config/database.php) sebaiknya bukan akun `root`, melainkan user khusus yang hanya memiliki hak `SELECT`, `INSERT`, `UPDATE` (tanpa `DROP` atau `DELETE`).
2. **Matikan Akses phpMyAdmin dari Luar:** Hanya izinkan phpMyAdmin diakses dari `localhost` atau matikan layanan phpMyAdmin selama jam pemilihan berlangsung.
3. **Pengawasan Berkala:** Tugaskan Pengawas Independen untuk menekan tombol **"Uji Keutuhan Ledger"** secara berkala (misal setiap 30 menit atau setiap 50 suara masuk).
