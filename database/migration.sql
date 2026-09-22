-- =====================================================================
--  SISTEM E-VOTING KOPERASI - MIGRASI DATABASE LENGKAP (MySQL / InnoDB)
--  Fitur Kriptografis Anti-Tamper, Admin Panel & Audit Trail
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `vote_koperasi`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE `vote_koperasi`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `election_settings`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `votes`;
DROP TABLE IF EXISTS `candidates`;
DROP TABLE IF EXISTS `voters`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Tabel: voters (DPT Anggota Koperasi)
-- ---------------------------------------------------------------------
CREATE TABLE `voters` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `rfid_uid`      VARCHAR(64)      NOT NULL COMMENT 'UID kartu RFID (HID Keyboard Wedge)',
  `name`          VARCHAR(120)     NOT NULL,
  `member_number` VARCHAR(40)      NOT NULL COMMENT 'Nomor anggota koperasi',
  `status`        ENUM('active','blocked') NOT NULL DEFAULT 'active',
  `has_voted`     TINYINT(1)       NOT NULL DEFAULT 0,
  `voted_at`      DATETIME         NULL DEFAULT NULL,
  `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME         NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_voters_rfid_uid` (`rfid_uid`),
  KEY `idx_voters_status` (`status`),
  KEY `idx_voters_has_voted` (`has_voted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Tabel: candidates (Calon Ketua & Pengawas Koperasi)
-- ---------------------------------------------------------------------
CREATE TABLE `candidates` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category`         ENUM('ketua','pengawas') NOT NULL,
  `candidate_number` INT UNSIGNED NOT NULL,
  `name`             VARCHAR(120) NOT NULL,
  `photo`            VARCHAR(255) NULL DEFAULT NULL COMMENT 'Path URL foto kandidat',
  `vision`           TEXT         NULL,
  `mission`          TEXT         NULL,
  `is_active`        TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_candidate_number_per_category` (`category`,`candidate_number`),
  KEY `idx_candidates_category` (`category`),
  KEY `idx_candidates_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Tabel: votes (Ledger Suara Kriptografis & Anonim)
-- Anonim (tanpa voter_id) & Anti-Tamper (Hash Chaining HMAC SHA-256)
-- ---------------------------------------------------------------------
CREATE TABLE `votes` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidate_id`  INT UNSIGNED    NOT NULL,
  `previous_hash` VARCHAR(64)     NOT NULL DEFAULT '0000000000000000000000000000000000000000000000000000000000000000',
  `vote_hash`     VARCHAR(64)     NOT NULL DEFAULT '',
  `receipt_token` VARCHAR(32)     NOT NULL DEFAULT '',
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_votes_candidate` (`candidate_id`),
  KEY `idx_votes_created` (`created_at`),
  CONSTRAINT `fk_votes_candidate_id`
    FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Tabel: admin_users (Panitia & Pengawas Pemilihan)
-- ---------------------------------------------------------------------
CREATE TABLE `admin_users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name`     VARCHAR(120) NOT NULL,
  `role`          ENUM('admin','pengawas') NOT NULL DEFAULT 'pengawas',
  `last_login`    DATETIME NULL DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Tabel: election_settings
-- ---------------------------------------------------------------------
CREATE TABLE `election_settings` (
  `setting_key`   VARCHAR(64) NOT NULL,
  `setting_value` TEXT NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Tabel: audit_logs (Jejak Rekam Aktivitas Administrasi)
-- ---------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` VARCHAR(64) NOT NULL,
  `actor`      VARCHAR(120) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `details`    TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_event` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- DATA AWAL (SEED)
-- ---------------------------------------------------------------------

INSERT INTO `candidates` (`category`,`candidate_number`,`name`,`photo`,`vision`,`mission`,`is_active`) VALUES
('ketua', 1, 'Budi Santoso, S.E.',
 'assets/foto/ketua-1.svg',
 'Menjadikan koperasi sebagai rumah tumbuh ekonomi anggota yang sehat, transparan, dan berkeadilan bagi seluruh anggota.',
 '1. Digitalisasi seluruh layanan simpan pinjam koperasi.\n2. Program pelatihan dan pendampingan UMKM berkala.\n3. Audit keuangan berkala yang dipublikasikan terbuka.\n4. Peningkatan Sisa Hasil Usaha (SHU) secara proporsional.', 1),
('ketua', 2, 'Sari Wulandari, M.M.',
 'assets/foto/ketua-2.svg',
 'Mewujudkan koperasi modern yang berdaya saing, memperkuat keadilan ekonomi, serta memprioritaskan kesejahteraan anggota.',
 '1. Inkubasi unit usaha kreatif anggota muda.\n2. Efisiensi operasional dan transparansi berbasis TI.\n3. Kerjasama strategis pasokan bahan baku anggota.\n4. Kemudahan akses permodalan usaha mikro.', 1),
('pengawas', 1, 'Drs. Agus Prasetyo',
 'assets/foto/pengawas-1.svg',
 'Pengawasan yang independen, objektif, tegas, dan berintegritas demi menjaga kepatuhan tata kelola koperasi.',
 '1. Penegakan tata kelola koperasi yang akuntabel.\n2. Kanal pelaporan pengaduan anggota yang aman dan rahasia.\n3. Audit kepatuhan operasional berkala.\n4. Pendampingan tindak lanjut rekomendasi audit.', 1),
('pengawas', 2, 'Ir. Nina Kurniawati',
 'assets/foto/pengawas-2.svg',
 'Mendorong pengawasan partisipatif berbasis sistem informasi demi melindungi aset dan hak setiap anggota koperasi.',
 '1. Edukasi hak dan kewajiban pengawasan bagi anggota.\n2. Verifikasi fisik berkala atas aset unit usaha.\n3. Publikasi ringkasan pengawasan triwulan.\n4. Evaluasi performa manajemen pengurus.', 1);

INSERT INTO `voters` (`rfid_uid`,`name`,`member_number`,`status`,`has_voted`,`voted_at`) VALUES
('0044 0202 2019 0505', 'Andi Wijaya',      'A-1001', 'active', 0, NULL),
('0044 0202 2019 0512', 'Rina Marlina',     'A-1002', 'active', 0, NULL),
('0044 0202 2019 0530', 'Joko Susilo',      'A-1003', 'active', 0, NULL),
('0044 0202 2019 0541', 'Dewi Lestari',     'A-1004', 'active', 0, NULL),
('0044 0202 2019 0588', 'Bambang Hartono', 'A-1005', 'blocked', 0, NULL);

INSERT INTO `admin_users` (`username`, `password_hash`, `full_name`, `role`) VALUES
('admin', '$2y$10$8oUOTu9W5SytJQEf.TXENOaJREAdu0YETzdz/KO9l6NaOEQurdKTq', 'Ketua Panitia Pemilihan', 'admin'),
('pengawas', '$2y$10$0a1LNo8dyFAZuoAhB/.o1uYgmP9BxfRDKrLYTRC.lPAqkMkH8FzHG', 'Pengawas Independen', 'pengawas');

INSERT INTO `election_settings` (`setting_key`, `setting_value`) VALUES
('election_title', 'Pemilihan Pengurus & Pengawas Koperasi'),
('cooperative_name', 'Koperasi Simpan Pinjam Sejahtera Bersama'),
('election_period', 'Periode 2026 - 2029'),
('election_status', 'open'),
('quick_count_public', '1'),
('booth_timeout_seconds', '120'),
('ledger_secret_salt', 'd873f2a1e94b407bb09c623910c2837f');
