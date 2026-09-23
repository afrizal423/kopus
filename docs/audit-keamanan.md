# Laporan Audit Keamanan — Sistem E-Voting Koperasi

**Framework:** CodeIgniter 3 · **Database:** MySQL (mysqli) · **Lingkungan:** Intranet/LAN (HTTP, tanpa HTTPS/HSTS)
**Standar:** OWASP Top 10 (2021) + OWASP CI3/Intranet Checklist
**Status review:** Deep scan (controller, model, view, config, entry, schema)

---

## 1. Ringkasan Eksekutif

| Severity | Jumlah |
|:--|:--:|
| 🔴 CRITICAL | 2 |
| 🟠 HIGH | 5 |
| 🟡 MEDIUM | 4 |
| 🟢 PENDEKATAN BAIK | 7 |

**Kesimpulan:** Arsitektur keamanan inti **kuat** — 100% prepared statement, `password_verify` (bcrypt), CSRF aktif, session regenerasi, proteksi double-vote berbasis transaksi, dan ledger HMAC-SHA256 anti-tamper. Namun ada **2 temuan kritis konfigurasi** dan **5 temuan tinggi permukaan** (XSS, CSRF-on-GET, salt fallback, CDN) yang sebaiknya ditangani sebelum produksi.

---

## 2. Cakupan Audit

| Lapisan | File yang diperiksa |
|:--|:--|
| Controller | `Voting.php`, `Admin.php`, `Welcome.php` |
| Model | `Voting_model.php`, `Admin_model.php` |
| View | `voting/scanner.php`, `voting/ballot.php`, `voting/success.php`, `admin/{login,dashboard,candidates,voters,export_report}.php` |
| Config | `config.php`, `database.php`, `routes.php`, `autoload.php`, `constants.php` |
| Entry | `index.php`, `.htaccess` (root), `application/.htaccess`, `system/.htaccess` |
| Schema | `database/migration.sql` |

---

## 3. Ringkasan Temuan

| No | Severity | OWASP | Judul |
|:--:|:--|:--|:--|
| 1 | 🔴 | A05 | `ENVIRONMENT` default `development` → error DB bocor |
| 2 | 🔴 | A05 | Kredensial DB hardcoded `root` / `123456` |
| 3 | 🟠 | A03 | XSS stored via `flashdata()` tanpa `htmlspecialchars` |
| 4 | 🟠 | A03 | XSS via jQuery `.html()` + SweetAlert `html:` (nama kandidat) |
| 5 | 🟠 | A01 | Aksi mutasi via GET (CSRF lintas situs) |
| 6 | 🟠 | A08 | Fallback salt ledger hardcoded di source |
| 7 | 🟠 | A06/Keandalan | Dependensi 4 CDN eksternal (single point of failure) |
| 8 | 🟡 | A04 | `submit_vote` tidak cek `election_status` |
| 9 | 🟡 | A09 | `log_threshold = 0` (log error mati) |
| 10 | 🟡 | A03 | `global_xss_filtering = FALSE` (lapisan kedua kosong) |
| 11 | 🟡 | A10 | Tanpa rate-limit brute-force login admin |

---

## 4. Detail Temuan

### 🔴 [1] `ENVIRONMENT` default `development` — A05 (Security Misconfiguration)

**Lokasi:** `index.php:56`, berantai ke `index.php:66-71` dan `database.php:85`.

```php
// index.php:56
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
```
```php
// index.php:68-71 (case 'development')
error_reporting(-1);
ini_set('display_errors', 1);
```
```php
// database.php:85
'db_debug' => (ENVIRONMENT !== 'production'),
```

**Dampak:** Jika `CI_ENV` tidak diset pada server, aplikasi berjalan **development** → `display_errors=1`, `error_reporting(-1)`, dan `db_debug=TRUE`. Saat ada error, detail PHP + **SQL mentah, nama tabel/kolom, kredensial** dapat terekspose di layar kiosk ke pemilih.

**Rekomendasi (patch):**
```php
// index.php:56 — ubah default
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');
```
Atau pastikan `CI_ENV=production` diset di konfigurasi Apache (`.htaccess` → `SetEnv CI_ENV production`).

---

### 🔴 [2] Kredensial DB hardcoded `root` / `123456` — A05

**Lokasi:** `database.php:79-80`.

```php
'username' => 'root',
'password' => '123456',
```

**Dampak:** User `root` over-privileged (bisa DROP seluruh database) dan password lemah. Proteksi lapisan fisik (`application/.htaccess` deny-all) sudah ada, tapi kredensial di file yang sama dengan app masih anti-pattern bila ada RCE/loophole lain.

**Rekomendasi (patch):**
```php
'username' => 'vote_koperasi_app',   // user khusus, minimal privilege
'password' => getenv('DB_PASSWORD') ?: 'GANTI_DENGAN_PASSWORD_KUAT',
```
Gunakan user DB khusus dengan grant terbatas (`SELECT/INSERT/UPDATE/DELETE` pada db `vote_koperasi` saja).

---

### 🟠 [3] XSS stored via `flashdata()` tanpa `htmlspecialchars` — A03

**Lokasi:**
- `admin/candidates.php:75,82`
- `admin/voters.php:75,82`
- `admin/dashboard.php:81-88`
- `admin/login.php:26`

Contoh:
```php
<!-- voters.php:75 -->
<i class="fas fa-check-circle me-1"></i> <?= $this->session->flashdata('success'); ?>
```

**Sumber data berisiko:** `Admin.php:217-218` — flash message memuat `$name` dari input admin:
```php
$this->Admin_model->log_audit('CREATE_VOTER', ..., 'Mendaftarkan kartu RFID baru untuk ' . $name);
$this->session->set_flashdata('success', $res['message']);
```
Karena `global_xss_filtering=FALSE`, escaping sepenuhnya mengandalkan output. Flashdata yang mengandung karakter khusus (atau bila admin kompromi, input HTML) akan ter-render.

**Rekomendasi (patch — terapkan di semua view admin):**
```php
<?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
<?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?>
```

---

### 🟠 [4] XSS via jQuery `.html()` + SweetAlert `html:` (nama kandidat) — A03

**Lokasi:**
- `ballot.php:236` → `$summary.html(...${selectedKetuaName}...)`
- `ballot.php:282-288` → `Swal.fire({ html: `...${selectedKetuaName}...` })`
- `voters.php:234` → `Swal.fire({ html: `...<b>"${name}"</b>...` })`
- `candidates.php:325` → `Swal.fire({ text: `..."${name}"...` })`

**Mekanisme:** `data-name` di-render dengan `htmlspecialchars` saat server-side, tetapi jQuery `.data('name')` **meng-decode HTML entity** menjadi string mentah. Saat di-inject via `.html()` / SweetAlert `html:`, tag bisa tereksekusi.

```js
// ballot.php:215 → 236
const name = $card.data('name');          // entity sudah di-decode jQuery
$summary.html(`<b>${name}</b> ...`);      // ← sink HTML
```

**Dampak (skenario):** Admin kompromi → buat calon bernama `<img src=x onerror=alert(document.cookie)>` → saat pemilih memilih, token CSRF/cookie bilik terekspose.

**Rekomendasi (patch):** Tambah helper escape di front-end dan pakai sink aman:
```js
function esc(s){ return String(s).replace(/[&<>"']/g, c =>
  ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
$summary.html(`<b>${esc(name)}</b> ...`);
// dan/atau untuk pesan sederhana, pakai Swal.fire({ text: ... }) tanpa html:
```

---

### 🟠 [5] Aksi mutasi via GET (CSRF lintas situs) — A01

**Endpoint berisiko** (route `(:num)`, dipanggil via `<a href>` / `window.location`):
- `admin/candidate_toggle/{id}` → `candidates.php:140`
- `admin/candidate_delete/{id}` → `candidates.php:334`
- `admin/voter_toggle/{id}` → `voters.php:140`
- `admin/voter_reset/{id}` → `voters.php:243`

**Dampak:** CI3 CSRF filter **tidak memvalidasi GET**. Attacker cukup membuat admin (sesi valid) membuka URL, mis. lewat `<img src="http://vote-koperasi/admin/voter_reset/5">` di halaman/email lain → aksi mutasi terkirim tanpa token.

**Rekomendasi (patch):**
```php
// Di masing-masing endpoint, paksa POST
public function voter_reset($id) {
    $this->require_auth();
    if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
        $this->output->set_status_header(405);
        $this->session->set_flashdata('error', 'Aksi harus dikirim via form (POST).');
        redirect('admin/voters');
        return;
    }
    // ... proses seperti semula
}
```
Sisi view: ganti `<a href>` / `window.location` dengan **form POST** + hidden CSRF field (atau AJAX + header `X-CSRF-TOKEN`).

---

### 🟠 [6] Fallback salt ledger hardcoded di source — A08 (Data Integrity)

**Lokasi:** `Voting_model.php:167`.

```php
private function get_ledger_secret() {
    $q = $this->db->query("SELECT setting_value FROM election_settings WHERE setting_key = 'ledger_secret_salt' LIMIT 1");
    $row = $q->row_array();
    return (!empty($row['setting_value'])) ? $row['setting_value'] : 'koperasi_static_fallback_salt_2026';
}
```

**Dampak:** Jika baris `ledger_secret_salt` hilang/di-reset, seluruh HMAC dihitung dengan **string literal di source** → siapa pun yang membaca source dapat merekomputasi seluruh rantai hash dan membuat ledger palsu yang valid.

**Rekomendasi (patch):**
```php
private function get_ledger_secret() {
    $q = $this->db->query("SELECT setting_value FROM election_settings WHERE setting_key = 'ledger_secret_salt' LIMIT 1");
    $row = $q->row_array();
    if (empty($row['setting_value'])) {
        throw new \RuntimeException('Ledger secret salt tidak ditemukan.');
    }
    return $row['setting_value'];
}
```

---

### 🟠 [7] Dependensi 4 CDN eksternal — A06 / Keandalan

**Lokasi:** semua view (`ballot.php:7-12`, `scanner.php:7-11`, `admin/*.php:7-12`, dll):
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/...">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/...">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/.../bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

**Dampak:** Bila kiosk intranet tidak punya gateway ke internet pada hari H, seluruh interaksi (focus RFID, modal, submit, countdown) **gagal total** — aplikasi tampak mati. Ini *single point of failure* operasional.

> Catatan: juga berkaitan dengan integrity (A06). Jika ingin sertifikasi kuat, self-host memungkinkan pin versi & audit lokal.

**Rekomendasi (patch):** Unduh keempat library ke `assets/vendor/` dan acuan lokal:
```html
<script src="<?= base_url('assets/vendor/jquery-3.6.0.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/sweetalert2.min.js'); ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap.min.css'); ?>">
```

---

### 🟡 [8] `submit_vote` tidak cek `election_status` — A04 (Insecure Design)

**Lokasi:** `Voting.php:139-210`.

`submit_vote()` memvalidasi method, session, timeout, kelengkapan pilihan — tapi **tidak** memeriksa apakah pemilihan masih `open`. Bila status diubah jadi `paused`/`closed` pas di antara open-nya `ballot()` dan submit, suara tetap terekam.

**Rekomendasi (patch):**
```php
$settings = $this->Voting_model->get_election_status();
if ($settings['election_status'] !== 'open') {
    $this->session->sess_destroy();
    return $this->output->set_status_header(403)
        ->set_content_type('application/json')
        ->set_output(json_encode(['status'=>'error','message'=>'Pemilihan sedang tidak aktif.']));
}
// ... baru lanjut proses
```
> Catatan: race kecil tetap mungkin; mitigasi terkuat dengan cek `status='active'` + `election_status` **di dalam transaksi** pada model.

---

### 🟡 [9] `log_threshold = 0` — A09 (Logging & Monitoring)

**Lokasi:** `config.php:229`.

```php
$config['log_threshold'] = 0;   // log error CI3 nonaktif
```

**Dampak:** Tidak ada jejak forensik dari framework (error PHP, warning). Audit trail aplikasi hidup di tabel `audit_logs`, tapi itu khusus administrasi — tidak menangkap error teknis.

**Rekomendasi (patch):**
```php
$config['log_threshold'] = 1;   // catat Error Messages di production
```

---

### 🟡 [10] `global_xss_filtering = FALSE` — A03

**Lokasi:** `config.php:445`.

Proteksi XSS saat ini **menyeluruh manual** (per output). Valid sebagai strategi, tapi bila ada satu sink terlewat (mis. temuan #3/#4) tidak ada jaring pengaman kedua.

**Rekomendasi:** Pertimbangkan `$config['global_xss_filtering'] = TRUE;` sebagai lapisan kedua — **uji dulu** efeknya ke field password (biasanya aman karena `password_verify` bekerja pada nilai mentah, tetapi wajib verifikasi end-to-end).

---

### 🟡 [11] Tanpa rate-limit brute-force login admin — A10

**Lokasi:** `Admin.php:37-67` (`authenticate()`).

`verify_rfid()` punya rate-limit (`check_rate_limit`, 8×/30dtk berbasis sesi+IP), tetapi **login admin tidak punya**. Attacker dapat trial-and-error password tanpa jeda.

**Dampak:** Brute-force akun `admin`/`pengawas` (terutama password lemah).

**Rekomendasi (patch):** Tambah rate-limit analog di `authenticate()` (mis. max 5× per 60 detik per IP), plus pesan generik (sudah ada: "Kombinasi username atau password salah.") dan waktu response konstan bila memungkinkan.

---

## 5. Aspek yang Sudah Aman (Penanganan Terbaik)

| Aspek | Bukti di kode |
|:--|:--|
| **SQLi** | 100% parameter binding `?` + `array(...)` di seluruh model; `LIKE` memakai `escape_like_str` (`Admin_model.php:128`) |
| **Password hashing** | `password_verify` + seed bcrypt `$2y$10$...` (`Admin_model.php:17`, `migration.sql:148-149`) |
| **CSRF (POST)** | `csrf_protection=TRUE` global; token di semua form & terikut `$form.serialize()` pada AJAX (`csrf_token_name` di view) |
| **Double-voting** | Atomic `UPDATE ... WHERE has_voted=0 AND status='active'` + cek `affected_rows()===1` + transaksi (`Voting_model.php:55-71`) |
| **Integritas ledger** | Rantai `previous_hash → vote_hash` HMAC-SHA256 + `verify_ledger_integrity()` (`Voting_model.php:112-161`) |
| **Session fixation** | `sess_regenerate(TRUE)` pasca-login admin & pasca-verifikasi RFID (`Admin.php:54`, `Voting.php:100`) |
| **Cookie hardening** | `cookie_httponly=TRUE`, `SameSite=Lax` (`config.php:415-417`) |
| **Folder protection** | `application/.htaccess` & `system/.htaccess` deny-all; `permitted_uri_chars` dibatasi (`config.php:164`) |
| **Akses kontrol** | `require_auth()` di semua endpoint admin; validasi `REQUEST_METHOD` pada endpoint mutasi |
| **Anonimitas surat suara** | Tabel `votes` tanpa `voter_id` → tak dapat dikorelasikan ke pemilih (`migration.sql:64-77`) |
| **Constraint DB** | FK `votes.candidate_id → candidates` `ON DELETE RESTRICT`; UNIQUE per kategori+nomor; UNIQUE `rfid_uid` (`migration.sql:55,35`) |

---

## 6. Rencana Perbaikan (Batching)

### Batch 1 — Kritis (sebelum go-live)
1. `index.php:56` → default `production` (atau set `CI_ENV`).
2. `database.php` → user DB khusus + password kuat (bukan `root/123456`).
3. Semua view admin → `htmlspecialchars(flashdata)`.
4. `Voting_model.php:167` → buang fallback salt (throw exception).
5. Front-end → helper `esc()` untuk `.html()` / `Swal html:` (nama kandidat & voter).

### Batch 2 — Tinggi
6. 4 endpoint admin mutasi → POST-only + form/AJAX + CSRF.
7. Self-host 4 CDN ke `assets/vendor/`.
8. `submit_vote()` → cek `election_status` (diidealkan dalam transaksi model).
9. Login admin → tambah rate-limit.

### Batch 3 — Sedang
10. `config.php:229` → `log_threshold = 1`.
11. `config.php:445` → pertimbangkan `global_xss_filtering = TRUE` (uji dulu).

---

## 7. Checklist Verifikasi Pasca-Perbaikan

- [ ] Deploy tanpa `CI_ENV` → tidak ada error PHP/SQL di layar.
- [ ] DB login via user khusus (bukan `root`).
- [ ] Flashdata berisi `<b>x</b>` → tampil sebagai teks (tidak render tag).
- [ ] Salin nama calon `<img src=x onerror=alert(1)>` → tidak eksekusi di bilik/admin (setelah esc).
- [ ] GET `/admin/voter_reset/{id}` → 405/redirect, tidak mutasi.
- [ ] Hapus row `ledger_secret_salt` → aplikasi error jelas (bukan pakai salt fallback).
- [ ] Kiosk tanpa internet → UI & interaksi tetap jalan (CDN lokal).
- [ ] Pilih lalu ubah status `closed` → submit ditolak 403.
- [ ] Login admin 6× cepat → dibatasi rate-limit.
- [ ] `application/logs/` berisi entri error saat error terjadi.
- [ ] Uji concurrent 2 submit → ledger tetap valid (`verify_ledger_integrity` hijau).

---

## 8. Catatan Kontekstual

- **Skill OWASP menyebut Oracle (oci8),** tetapi `database.php` memakai **`mysqli`**. Implikasi: aturan penutupan koneksi Oracle (`$db->close()` / `ORA-12519`) tidak berlaku; aturan lain (prepared statement, header, method check, no-HSTS) tetap valid.
- **HSTS sengaja tidak ada** sesuai aturan intranet — jangan menambah `Strict-Transport-Security`.
- `success.php` menerima `?receipt=...` tanpa validasi DB (hanya display). Ini **bukan** celah keamanan berat karena tidak memicu mutasi, tetapi idealnya dicek terhadap `votes.receipt_token` untuk akurasi pesan.

---

*— Akhir Laporan —*
