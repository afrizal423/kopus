# Rencana: Tulis Laporan Audit Keamanan ke `docs/laporan-audit-keamanan.md`

## Status
**Menunggu exit Plan Mode** untuk eksekusi `write`.

## Konteks
- User memilih: `docs/laporan-audit-keamanan.md`
- User memilih: "Exit & tulis file"
- Konten lengkap laporan sudah disiapkan (lihat di bawah)
- Folder `docs/` sudah dibuat

## Eksekusi
```bash
# Saat plan mode sudah di-close:
write("C:\\xampp7\\htdocs\\vote-koperasi\\docs\\laporan-audit-keamanan.md", KONTEN)
```

## Konten Laporan Lengkap

# Laporan Audit Keamanan
**Sistem E-Voting Koperasi**  
**Tanggal:** 23 September 2026  
**Framework:** CodeIgniter 3 | **Database:** MySQL (mysqli)  
**Standar:** OWASP Top 10 (2021) + OWASP Security Checklist CI3/Intranet  

---

## 1. Ringkasan Eksekutif

| Skala | Jumlah |
| :--- | :---: |
| CRITICAL | 2 |
| HIGH | 5 |
| MEDIUM | 6 |
| LOW | 4 |
| **Total** | **17** |

**Keamanan umumnya baik** - prepared statement 100% di model, password_verify, CSRF aktif global, header keamanan diterapkan, dan ledger kriptografis anti-tamper. Namun beberapa celah tetap perlu ditangani sebelum produksi.

---

## 2. Cakupan

| Tipe | File |
|:-----|:-----|
| Controller | Voting.php, Admin.php, Welcome.php |
| Model | Voting_model.php, Admin_model.php |
| Config | config.php, database.php, routes.php, autoload.php, constants.php |
| View | voting/scanner.php, voting/ballot.php, voting/success.php, admin/dashboard.php, admin/candidates.php, admin/voters.php, admin/export_report.php, admin/login.php |
| Entry | index.php, .htaccess (root & system) |
| Database | migration.sql |
| Assets | assets/css/koperasi.css |

---

## 3. Peta Temuan per OWASP

| No | OWASP | Temuan | Level |
| :--: | :--: | :--- | :---: |
| 1 | A05 | Kredensial DB hardcoded root / 123456 | CRITICAL |
| 2 | A05 | ENVIRONMENT default development - display_errors aktif | CRITICAL |
| 3 | A03 | XSS tersimpan di ballot.php (jQuery .html + Swal html) | HIGH |
| 4 | A03 | XSS tersimpan di view admin (flashdata tanpa htmlspecialchars) | HIGH |
| 5 | A01 | Aksi mutasi via GET (candidate_toggle/delete, voter_toggle/reset) | HIGH |
| 6 | A08 | Fallback salt ledger hardcoded di source code | HIGH |
| 7 | A04 | submit_vote tidak memeriksa election_status | HIGH |
| 8 | A09 | log_threshold = 0 (semua error log nonaktif) | MEDIUM |
| 9 | A05 | db_debug aktif saat ENVIRONMENT non-production | MEDIUM |
| 10 | A03 | dashboard.php - $integrity message tanpa htmlspecialchars | MEDIUM |
| 11 | A05 | stricton FALSE (MySQL tidak strict) | MEDIUM |
| 12 | A05 | global_xss_filtering = FALSE | MEDIUM |
| 13 | A01 | Sesi tidak terikat IP (sess_match_ip = FALSE) | MEDIUM |
| 14 | A05 | success.php - double htmlspecialchars (bug tampilan) | LOW |
| 15 | A08 | Rate limiter RFID tersimpan di sesi (bisa di-reset) | LOW |
| 16 | A08 | votes.receipt_token tidak ada UNIQUE constraint di DB | LOW |
| 17 | A08 | voters.member_number hanya dicek di app level | LOW |

---

## 4. Detail Temuan

### CRITICAL-1: Kredensial Database Hardcoded

**File:** `config/database.php:79-80`

```php
'username' => 'root',
'password' => '123456',
```

**Dampak:** Jika application/ terbuka (mod_rewrite bermasalah), kredensial MySQL terekspos. User root terlalu berkuasa untuk aplikasi e-voting.

**Rekomendasi:** Ganti dengan user dedicated (misal vote_koper_app) + password kuat.

---

### CRITICAL-2: ENVIRONMENT Default "development"

**File:** `index.php:56`

```php
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
```

**Dampak:** Jika CI_ENV tidak diset saat deploy, error PHP + SQL tampil terbuka ke user (display_errors=1 + error_reporting(-1)).

**Rekomendasi:** Set CI_ENV=production di Apache, atau ubah default menjadi 'production'.

---

### HIGH-3: XSS Tersimpan di Bilik Suara (ballot.php)

**File:** `ballot.php:236, 282`

```javascript
$summary.html(`<b>${selectedKetuaName}</b> ...`);
Swal.fire({ html: `...${selectedKetuaName}...` })
```

**Skenario:** Admin buat kandidat bernama `<img src=x onerror=alert(document.cookie)>` -> ter-render saat pemilih memilih -> cookie/CSRF token dibajak.

**Rekomendasi:** Escape di JS pakai function escapeHtml() atau swap .html() -> .text() + SweetAlert html: -> text:

---

### HIGH-4: XSS di View Admin - flashdata Tanpa Escape

**File:** `admin/candidates.php:75,82`, `admin/voters.php:75,82`, `admin/login.php:26`, `admin/dashboard.php:81-88`

```php
<?= $this->session->flashdata('success'); ?>  // TIDAK ada htmlspecialchars
```

**Sumber terkontaminasi:** `Admin.php:218` -> 'Mendaftarkan kartu RFID baru untuk ' . $name

**Rekomendasi:** `htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8')`

---

### HIGH-5: Aksi Mutasi via GET

**Endpoint terdampak:**
- admin/candidate_toggle/{id}
- admin/candidate_delete/{id}  
- admin/voter_toggle/{id}
- admin/voter_reset/{id}

**Dampak:** `<img src="http://vote-koperasi/admin/voter_reset/5">` dapat memicu reset tanpa form/CSRF.

**Rekomendasi:** Tolak GET, ubah ke POST + form + CSRF token.

---

### HIGH-6: Fallback Salt Ledger Hardcoded

**File:** `Voting_model.php:167`

```php
return 'koperasi_static_fallback_salt_2026'; // fallback
```

**Dampak:** Jika salt hilang dari DB, HMAC dihitung dengan salt yang terbaca di source -> attacker bisa recompute seluruh hash.

**Rekomendasi:** Buang fallback, ganti dengan `throw new \Exception(...)`

---

### HIGH-7: submit_vote Tidak Cek election_status

**File:** `Voting.php:submit_vote()`

**Dampak:** Suara terekam setelah pemilihan ditutup (race: antara load ballot dan submit).

**Rekomendasi:** Tambah cek `$settings['election_status'] !== 'open'` di awal submit_vote.

---

### MEDIUM-8 s/d 17
(terdokumentasi di peta temuan - detail ringkas)

- 8: `log_threshold = 0` -> set 1
- 9: `db_debug` aktif di dev -> sudah ditangani dengan Critical-2
- 10: `$integrity['message']` tanpa htmlspecialchars di dashboard.php:103
- 11: `stricton = FALSE` -> set TRUE
- 12: `global_xss_filtering = FALSE` -> pertimbangkan TRUE
- 13: `sess_match_ip = FALSE` -> TRUE untuk kiosk IP statis
- 14: Double htmlspecialchars di success.php (hanya tampilan)
- 15: Rate limiter RFID bisa di-reset dengan sesi baru -> pindahkan ke cache
- 16: Tambah UNIQUE pada votes.receipt_token
- 17: Tambah UNIQUE pada voters.member_number

---

## 5. Aspek yang Sudah Aman

| Aspek | Implementasi |
|:------|:-------------|
| SQL Injection | 100% prepared statement (parameter binding `?`) di seluruh model |
| Password Hashing | password_verify + bcrypt ($2y$10$) di seed |
| CSRF (POST) | csrf_protection=TRUE global, token di semua form + AJAX |
| Session Fixation | sess_regenerate(TRUE) setelah login dan verify_rfid |
| Double Voting | Atomic UPDATE + WHERE has_voted=0 + cek affected_rows |
| Ledger Integrity | Hash chain HMAC-SHA256 with verify_ledger_integrity |
| HTTP Method | POST dicek di 6 endpoint |
| Access Control | require_auth() di semua endpoint admin |
| Input Sanitasi RFID | null-byte, length constraint, whitelist regex |
| Header Keamanan | nosniff, X-Frame-Options:DENY, no-cache |
| Folder Protection | .htaccess deny all di application/ & system/ |

---

## 6. Rencana Perbaikan

### Batch 1 - CRITICAL (Wajib sebelum produksi)
1. Set ENVIRONMENT='production' (index.php) atau CI_ENV di Apache
2. Ganti kredensial MySQL (database.php)
3. Tambah htmlspecialchars ke flashdata di semua view admin
4. Escape JS di ballot.php (escapeHtml function)
5. Tambah cek election_status di submit_vote()
6. Buang fallback salt di get_ledger_secret()

### Batch 2 - HIGH
7. Ubah GET action -> POST (4 endpoint admin)
8. Set log_threshold = 1
9. Set stricton = TRUE
10. Tambah htmlspecialchars di $integrity message

### Batch 3 - MEDIUM/LOW
11. Aktifkan global_xss_filtering = TRUE (uji dulu)
12. Tambah UNIQUE constraint di skema DB
13. Pindahkan rate limiter ke cache
14. Set sess_match_ip = TRUE untuk kiosk
15. Hapus/redirect Welcome.php

---

## 7. Checklist Verifikasi Pasca-Perbaikan

- [ ] index.php ENVIRONMENT = production
- [ ] database.php kredensial non-root
- [ ] flashdata() di-escape di semua view
- [ ] ballot.php JS pakai escapeHtml
- [ ] submit_vote cek election_status
- [ ] get_ledger_secret throw exception
- [ ] 4 endpoint admin POST-only
- [ ] log_threshold = 1
- [ ] stricton = TRUE
- [ ] UNIQUE constraint di DB
- [ ] Uji manual: 2 submit concurrent -> ledger valid
- [ ] Uji manual: GET /admin/voter_reset/5 -> redirect + error
- [ ] Uji manual: error DB tidak tampil di layar

---

*Selesai*