# GEMINI.md - KOPUS (Koperasi UBS) E-Voting System

Welcome to **KOPUS (Koperasi UBS)**, a high-security, enterprise-grade electronic voting (*E-Voting*) system powered by RFID card authentication (*HID Keyboard Wedge*) built for cooperative elections (Chairperson and Supervisory Board) under the democratic principles of **LUBER JURDIL** (*Direct, General, Free, Confidential, Honest, and Fair*).

This document serves as the primary technical context, architectural reference, and operational playbook for AI coding agents (Gemini / Antigravity) working on this codebase.

---

## 1. Technology Stack & Environment

| Component | Technology / Specification | Notes |
|---|---|---|
| **Language & Runtime** | PHP 7.4.x / PHP 8.x | CodeIgniter 3 (MVC Architecture) |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ | InnoDB engine, strict foreign keys, transactions |
| **Web Server** | Apache (XAMPP / Linux `mod_rewrite`) | Intranet HTTP environment (default port `8080` locally) |
| **CSS & Design System** | Vanilla CSS (`assets/css/koperasi.css`) | Custom enterprise design system (Bootstrap 5.3 base) |
| **Frontend Libraries** | jQuery 3.6.0, SweetAlert2, FontAwesome 6 | **100% Offline / Intranet-First** (Zero external CDN calls) |
| **3D Graphics Engine** | Three.js (r128 offline bundle) | Tablet-optimized low-poly WebGL rendering |
| **Hardware Integration**| USB RFID Reader | Keyboard Wedge Emulation (EM4100 / Mifare 13.56 MHz) |

---

## 2. Core Architectural Pillars

### A. Cryptographic Anti-Tamper Ledger (Blockchain-like HMAC Hash Chaining)
To eliminate risks of internal tampering (e.g., rogue administrators with phpMyAdmin / direct SQL console access modifying vote counts):
- Every vote recorded in `votes` table is bound into an immutable cryptographic chain using **HMAC SHA-256**:
  $$\text{vote\_hash} = \text{HMAC-SHA256}(\text{previous\_hash} \parallel \text{candidate\_id} \parallel \text{created\_at} \parallel \text{receipt\_token}, \text{secret\_salt})$$
- The very first vote binds to the *Genesis Hash* (`0000000000000000000000000000000000000000000000000000000000000000`).
- Integrity validation engine in `Voting_model::verify_ledger_integrity()` recompiles and verifies every hash from row 1 to $N$. If any row or `candidate_id` is modified, inserted, or deleted, the system immediately flags the exact corrupted row index as **"TAMPER DETECTED"**.
- Anonymous Audit Token: Voters receive a cryptographically generated audit token (e.g., `KOP-5EEA0142`) proving their vote was cast without exposing candidate choices.

### B. Anti-Double Voting & Race Condition Mitigation
To guarantee that a voter card cannot cast duplicate ballots even via high-frequency concurrent requests:
- **Atomic Conditional SQL Update**:
  ```sql
  UPDATE voters 
  SET has_voted = 1, voted_at = ? 
  WHERE id = ? AND has_voted = 0 AND status = 'active'
  ```
- Evaluates `affected_rows()`. If not strictly `1`, the entire database transaction is immediately rolled back (`ROLLBACK`).

### C. OWASP Top 10 Enterprise Intranet Security
- **Strict Parameter Binding**: Zero string concatenation in SQL queries. All database queries must use CI3 Active Record query binding.
- **CSRF Token Enforcement**: Cross-Site Request Forgery tokens enforced on all POST/AJAX endpoints.
- **Session Hardening**: `HttpOnly = TRUE`, `SameSite = Lax`, session regeneration on RFID tap, instant session destruction (`sess_destroy()`) immediately upon vote confirmation.
- **Rate Limiting**: Scan rate limiter rejects more than 8 invalid tap attempts per 30 seconds per IP to thwart automated brute-force attacks.
- **Intranet Exemption**: HSTS header (`Strict-Transport-Security`) is deliberately **disabled** to avoid breaking local corporate intranet HTTP workflows.

---

## 3. Specialized Features & UI Standards

### A. Web Accessibility Suite (Inklusif & Ramah Lansia)
Implemented specifically for kiosk voting terminals ([`scanner.php`](application/views/voting/scanner.php) and [`ballot.php`](application/views/voting/ballot.php)) via partial view [`accessibility_widget.php`](application/views/voting/accessibility_widget.php):
- **Universal Access FAB**: Fixed floating action button with 48px touch target (`fas fa-universal-access`) placed bottom-left, positioned adaptively above sticky bottom navigation.
- **Text Resizer (4 Stepped Scales)**:
  - `normal` (100% default)
  - `large` (114% comfortable)
  - `xlarge` (128% low-vision)
  - `xxlarge` (142% maximum scale for elderly voters)
- **High Contrast Modes**:
  - **Standard**: Clean emerald cooperative theme.
  - **Dark Mode**: Anti-glare deep obsidian (`#0b0f19`) with crisp light typography.
  - **Yellow on Black (WCAG AAA)**: Ultra-high contrast mode (pitch black `#000000` + vivid `#facc15` golden yellow) compliant with WCAG AAA for low-vision and elderly voters.
  - **Monochrome (Grayscale)**: High-contrast monochrome filter for voters with color vision deficiency / color blindness.
- **Readability Enhancements**: Extra bold font toggle, relaxed line spacing, and interactive horizontal reading guide bar (`#a11yReadingGuide`).
- **Zero-Flicker Pre-render**: Synchronous IIFE in `<head>` reads `localStorage.getItem('kopus_a11y_prefs')` and applies root classes to `<html>` prior to browser rendering.

### B. Wizard Stepper & Candidate Selection Flow
- **3-Step Stepper Navigation**:
  - **Step 1**: Choose Chairperson Candidate (*Calon Ketua*).
  - **Step 2**: Choose Supervisory Board Candidate (*Calon Pengawas*).
  - **Step 3**: Review & Final Confirmation (*Tinjau Pilihan Suara* side-by-side).
- **Direct View Focus**: Tapping RFID instantly navigates to `ballot.php` and focuses directly on active candidate cards without manual scrolling.
- **Soft Carousel Slider**: Candidates render side-by-side horizontally with touch-friendly navigation buttons and auto-centering for few candidates.
- **Vision & Mission Modal**: Fully styled scrollable modal with complete contrast support in all visual modes.

### C. 3D WebGL Graphics & Real-Time Sync (Three.js Offline)
- **Floating RFID Smartcard**: Interactive 3D membership card on kiosk standby terminal ([`scanner.php`](application/views/voting/scanner.php)) responding to mouse/touch angles and scan triggers.
- **3D Cylinder Quick Count Pillars**: 3D bar cylinder chart on admin dashboard ([`dashboard.php`](application/views/admin/dashboard.php)) with interactive rotate, zoom in/out, and full-screen projector view.
- **Live Sync Polling**: Real-time asynchronous polling every 4 seconds to sync vote totals and voter turnout metrics without page reloads.

---

## 4. Directory Structure & Key Files

```
vote-koperasi/
├── application/
│   ├── config/
│   │   ├── config.php                 # OWASP hardening, CSRF, cookie params
│   │   ├── database.php               # MySQL database connection settings
│   │   └── routes.php                 # URL routing for voting and admin
│   ├── controllers/
│   │   ├── Admin.php                  # Committee & Auditor controller
│   │   └── Voting.php                 # Kiosk voting booth controller
│   ├── models/
│   │   ├── Admin_model.php            # Quick count analytics, candidate & voter CRUD
│   │   └── Voting_model.php           # HMAC hash chaining, atomic lock, tamper verifier
│   └── views/
│       ├── admin/
│       │   ├── candidates.php         # Candidate management & photo upload
│       │   ├── dashboard.php          # Quick count, 3D cylinder pillars, ledger audit
│       │   ├── export_report.php      # Printable official election report (Berita Acara)
│       │   ├── login.php              # Committee & auditor authentication
│       │   └── voters.php             # Voter registry (DPT) & RFID card enrollment
│       └── voting/
│           ├── accessibility_widget.php # Floating FAB & Accessibility Drawer/Modal
│           ├── ballot.php             # Digital ballot booth (Wizard Stepper)
│           ├── scanner.php            # Standby RFID tap screen (3D Smartcard)
│           └── success.php            # Audit token receipt & auto-reset screen
├── assets/
│   ├── css/
│   │   └── koperasi.css               # Core CSS design system & accessibility rules
│   ├── vendor/                        # Offline vendor assets (Bootstrap, Three.js, etc.)
│   └── uploads/candidates/            # Candidate photo uploads
├── database/
│   ├── apply_migration.php            # CLI database migration script
│   └── migration.sql                  # MySQL schema definition & seed data
├── docs/
│   ├── audit-keamanan.md              # OWASP security compliance audit report
│   └── sop-penanganan-insiden-ledger.md # Incident response SOP for ledger tampering
└── README.md                          # Comprehensive user & admin documentation
```

---

## 5. Development & Agent Etiquette Rules

1. **Intranet & Offline-First Strict Constraint**:
   - **NEVER** link or load external CDN resources (e.g., `cdnjs.cloudflare.com`, `jsdelivr.net`, `unpkg.com`, `fonts.googleapis.com`).
   - All styles, scripts, fonts, and 3D libraries must exist locally under `assets/vendor/` or `assets/css/`.

2. **Windows PowerShell Compatibility**:
   - **NEVER** run `cd` commands. Always pass `Cwd` or use absolute paths.
   - Shell commands must be compatible with Windows PowerShell 5.1+.

3. **Git Etiquette**:
   - **DO NOT** execute `git commit` or `git push` unless the user explicitly asks for it (e.g., *"oke git commit"*).
   - Keep working directory clean, and verify changes via `git status` when asked.

4. **Security & Data Integrity**:
   - Never tamper with or bypass the HMAC Ledger logic in `Voting_model.php`.
   - Never disable CSRF protection on forms or AJAX requests.
   - Always sanitize output with `htmlspecialchars()` or `escapeHtml()`.

5. **Anti-Slop UI & Ergonomics**:
   - Maintain brand colors: Emerald Green (`#0f5132`) for Chairperson, Azure/Royal Blue (`#2563eb`) for Supervisory Board, and Gold (`#d97706`) for numbered badges.
   - Avoid generic AI badges, meaningless gradient overload, and unnecessary animation loops.
