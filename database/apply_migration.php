<?php
$mysqli = @new mysqli('localhost', 'root', '123456');
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

$mysqli->query("CREATE DATABASE IF NOT EXISTS `vote_koperasi` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
$mysqli->select_db('vote_koperasi');

// 1. Ensure voters table exists with proper columns
$mysqli->query("
CREATE TABLE IF NOT EXISTS `voters` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rfid_uid`      VARCHAR(64) NOT NULL,
  `name`          VARCHAR(120) NOT NULL,
  `member_number` VARCHAR(40) NOT NULL,
  `status`        ENUM('active','blocked') NOT NULL DEFAULT 'active',
  `has_voted`     TINYINT(1) NOT NULL DEFAULT 0,
  `voted_at`      DATETIME NULL DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_voters_rfid_uid` (`rfid_uid`),
  KEY `idx_voters_status` (`status`),
  KEY `idx_voters_has_voted` (`has_voted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
");

// 2. Ensure candidates table exists
$mysqli->query("
CREATE TABLE IF NOT EXISTS `candidates` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category`         ENUM('ketua','pengawas') NOT NULL,
  `candidate_number` INT UNSIGNED NOT NULL,
  `name`             VARCHAR(120) NOT NULL,
  `photo`            VARCHAR(255) NULL DEFAULT NULL,
  `vision`           TEXT NULL,
  `mission`          TEXT NULL,
  `is_active`        TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_candidate_number_per_category` (`category`,`candidate_number`),
  KEY `idx_candidates_category` (`category`),
  KEY `idx_candidates_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
");

// 3. Ensure votes table has cryptographic ledger columns
$mysqli->query("
CREATE TABLE IF NOT EXISTS `votes` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidate_id`  INT UNSIGNED NOT NULL,
  `previous_hash` VARCHAR(64) NOT NULL DEFAULT '0000000000000000000000000000000000000000000000000000000000000000',
  `vote_hash`     VARCHAR(64) NOT NULL DEFAULT '',
  `receipt_token` VARCHAR(32) NOT NULL DEFAULT '',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_votes_candidate` (`candidate_id`),
  KEY `idx_votes_created` (`created_at`),
  CONSTRAINT `fk_votes_candidate_id`
    FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
");

// Check if columns exist in votes table (in case it existed already without previous_hash)
$cols = [];
$res = $mysqli->query("SHOW COLUMNS FROM `votes`");
while ($r = $res->fetch_assoc()) {
    $cols[] = $r['Field'];
}

if (!in_array('previous_hash', $cols)) {
    $mysqli->query("ALTER TABLE `votes` ADD COLUMN `previous_hash` VARCHAR(64) NOT NULL DEFAULT '0000000000000000000000000000000000000000000000000000000000000000' AFTER `candidate_id`");
    echo "Added column previous_hash to votes\n";
}
if (!in_array('vote_hash', $cols)) {
    $mysqli->query("ALTER TABLE `votes` ADD COLUMN `vote_hash` VARCHAR(64) NOT NULL DEFAULT '' AFTER `previous_hash`");
    echo "Added column vote_hash to votes\n";
}
if (!in_array('receipt_token', $cols)) {
    $mysqli->query("ALTER TABLE `votes` ADD COLUMN `receipt_token` VARCHAR(32) NOT NULL DEFAULT '' AFTER `vote_hash`");
    echo "Added column receipt_token to votes\n";
}

// 4. Admin Users table
$mysqli->query("
CREATE TABLE IF NOT EXISTS `admin_users` (
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
");

// Insert default admin and pengawas accounts if not exist
$admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
$pengawas_pass = password_hash('pengawas123', PASSWORD_BCRYPT);

$mysqli->query("
INSERT IGNORE INTO `admin_users` (`username`, `password_hash`, `full_name`, `role`) VALUES
('admin', '$admin_pass', 'Ketua Panitia Pemilihan', 'admin'),
('pengawas', '$pengawas_pass', 'Pengawas Independen', 'pengawas');
");

// 5. Election Settings table
$mysqli->query("
CREATE TABLE IF NOT EXISTS `election_settings` (
  `setting_key`   VARCHAR(64) NOT NULL,
  `setting_value` TEXT NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
");

$settings = [
    'election_title' => 'Pemilihan Pengurus & Pengawas KOPUS',
    'cooperative_name' => 'KOPUS (Koperasi UBS)',
    'election_period' => 'Periode 2026 - 2029',
    'election_status' => 'open',
    'quick_count_public' => '1',
    'booth_timeout_seconds' => '120',
    'ledger_secret_salt' => 'd873f2a1e94b407bb09c623910c2837f'
];

foreach ($settings as $k => $v) {
    $k_esc = $mysqli->real_escape_string($k);
    $v_esc = $mysqli->real_escape_string($v);
    $mysqli->query("INSERT INTO `election_settings` (`setting_key`, `setting_value`) VALUES ('$k_esc', '$v_esc') ON DUPLICATE KEY UPDATE `setting_value` = '$v_esc'");
}

// 6. Audit Logs table
$mysqli->query("
CREATE TABLE IF NOT EXISTS `audit_logs` (
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
");

$mysqli->query("UPDATE `candidates` SET `photo` = 'assets/foto/ketua-1.svg' WHERE `category`='ketua' AND `candidate_number`=1");
$mysqli->query("UPDATE `candidates` SET `photo` = 'assets/foto/ketua-2.svg' WHERE `category`='ketua' AND `candidate_number`=2");
$mysqli->query("UPDATE `candidates` SET `photo` = 'assets/foto/pengawas-1.svg' WHERE `category`='pengawas' AND `candidate_number`=1");
$mysqli->query("UPDATE `candidates` SET `photo` = 'assets/foto/pengawas-2.svg' WHERE `category`='pengawas' AND `candidate_number`=2");

echo "Database migration completed successfully.\n";
