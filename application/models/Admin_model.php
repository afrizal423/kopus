<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function authenticate($username, $password) {
        $query = $this->db->query(
            "SELECT id, username, password_hash, full_name, role FROM admin_users WHERE username = ? LIMIT 1",
            array($username)
        );

        $admin = $query->row_array();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $this->db->query(
                "UPDATE admin_users SET last_login = NOW() WHERE id = ?",
                array($admin['id'])
            );
            return $admin;
        }
        return null;
    }

    public function get_dashboard_stats() {
        $total_voters = (int)$this->db->query("SELECT COUNT(*) AS total FROM voters")->row()->total;
        $voted_count  = (int)$this->db->query("SELECT COUNT(*) AS total FROM voters WHERE has_voted = 1")->row()->total;
        $total_votes  = (int)$this->db->query("SELECT COUNT(*) AS total FROM votes")->row()->total;

        $turnout = ($total_voters > 0) ? round(($voted_count / $total_voters) * 100, 1) : 0;

        $results = array(
            'ketua' => $this->get_category_results('ketua'),
            'pengawas' => $this->get_category_results('pengawas')
        );

        return array(
            'total_voters' => $total_voters,
            'voted_count' => $voted_count,
            'remaining_voters' => $total_voters - $voted_count,
            'turnout_percentage' => $turnout,
            'total_votes_recorded' => $total_votes,
            'results' => $results
        );
    }

    public function get_category_results($category) {
        $query = $this->db->query(
            "SELECT c.id, c.candidate_number, c.name, c.photo, c.is_active,
                    COUNT(v.id) AS vote_count
             FROM candidates c
             LEFT JOIN votes v ON v.candidate_id = c.id
             WHERE c.category = ?
             GROUP BY c.id, c.candidate_number, c.name, c.photo, c.is_active
             ORDER BY c.candidate_number ASC",
            array($category)
        );

        $rows = $query->result_array();
        $cat_total = 0;
        foreach ($rows as $r) {
            $cat_total += (int)$r['vote_count'];
        }

        foreach ($rows as &$r) {
            $cnt = (int)$r['vote_count'];
            $r['percentage'] = ($cat_total > 0) ? round(($cnt / $cat_total) * 100, 1) : 0;
        }

        return array(
            'candidates' => $rows,
            'total_category_votes' => $cat_total
        );
    }

    public function get_all_candidates() {
        $query = $this->db->query(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM votes WHERE candidate_id = c.id) AS vote_count
             FROM candidates c
             ORDER BY c.category DESC, c.candidate_number ASC"
        );
        return $query->result_array();
    }

    public function get_candidate($id) {
        $query = $this->db->query(
            "SELECT * FROM candidates WHERE id = ? LIMIT 1",
            array((int)$id)
        );
        return $query->row_array();
    }

    public function create_candidate($data) {
        return $this->db->insert('candidates', $data);
    }

    public function update_candidate($id, $data) {
        $this->db->where('id', (int)$id);
        return $this->db->update('candidates', $data);
    }

    public function toggle_candidate($id) {
        $cand = $this->get_candidate($id);
        if (!$cand) {
            return false;
        }
        $new_status = ($cand['is_active'] == 1) ? 0 : 1;
        return $this->update_candidate($id, array('is_active' => $new_status));
    }

    // Prevents breaking foreign key constraints and ledger integrity if votes already exist.
    public function delete_candidate($id) {
        $cid = (int)$id;
        $check = $this->db->query("SELECT COUNT(*) AS total FROM votes WHERE candidate_id = ?", array($cid))->row();
        if ($check && (int)$check->total > 0) {
            return array('status' => false, 'message' => 'Calon tidak dapat dihapus karena sudah memiliki suara tercatat di bilik suara.');
        }

        $this->db->query("DELETE FROM candidates WHERE id = ?", array($cid));
        return array('status' => true, 'message' => 'Calon berhasil dihapus.');
    }

    public function get_voters($search = '') {
        if (!empty($search)) {
            $like = '%' . $this->db->escape_like_str($search) . '%';
            $query = $this->db->query(
                "SELECT * FROM voters WHERE name LIKE ? OR member_number LIKE ? OR rfid_uid LIKE ? ORDER BY id DESC LIMIT 100",
                array($like, $like, $like)
            );
        } else {
            $query = $this->db->query("SELECT * FROM voters ORDER BY id DESC LIMIT 100");
        }
        return $query->result_array();
    }

    public function create_voter($data) {
        $check = $this->db->query("SELECT id FROM voters WHERE rfid_uid = ?", array($data['rfid_uid']));
        if ($check->num_rows() > 0) {
            return array('status' => false, 'message' => 'Kartu RFID ini sudah terdaftar.');
        }

        $check_no = $this->db->query("SELECT id FROM voters WHERE member_number = ?", array($data['member_number']));
        if ($check_no->num_rows() > 0) {
            return array('status' => false, 'message' => 'Nomor anggota ini sudah terdaftar.');
        }

        $this->db->insert('voters', $data);
        return array('status' => true, 'message' => 'Anggota berhasil ditambahkan ke DPT.');
    }

    public function toggle_voter_status($id) {
        $query = $this->db->query("SELECT status FROM voters WHERE id = ? LIMIT 1", array((int)$id));
        $row = $query->row_array();
        if (!$row) {
            return false;
        }

        $new_status = ($row['status'] === 'active') ? 'blocked' : 'active';
        $this->db->query("UPDATE voters SET status = ? WHERE id = ?", array($new_status, (int)$id));
        return $new_status;
    }

    // Reset status is strictly audited.
    public function reset_voter_vote($id, $actor_username) {
        $this->db->query("UPDATE voters SET has_voted = 0, voted_at = NULL WHERE id = ?", array((int)$id));
        $this->log_audit('RESET_VOTER', $actor_username, 'Reset status suara pemilih ID #' . (int)$id);
        return true;
    }

    public function log_audit($event_type, $actor, $details = '') {
        $ip = $this->input->ip_address();
        $this->db->query(
            "INSERT INTO audit_logs (event_type, actor, ip_address, details, created_at) VALUES (?, ?, ?, ?, NOW())",
            array($event_type, $actor, $ip, $details)
        );
    }

    public function get_audit_logs($limit = 30) {
        $query = $this->db->query(
            "SELECT * FROM audit_logs ORDER BY id DESC LIMIT ?",
            array((int)$limit)
        );
        return $query->result_array();
    }

    public function get_settings() {
        $query = $this->db->query("SELECT setting_key, setting_value FROM election_settings");
        $settings = array();
        foreach ($query->result_array() as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        return $settings;
    }

    public function update_setting($key, $value) {
        $check = $this->db->query("SELECT setting_key FROM election_settings WHERE setting_key = ?", array($key));
        if ($check->num_rows() > 0) {
            $this->db->query("UPDATE election_settings SET setting_value = ? WHERE setting_key = ?", array($value, $key));
        } else {
            $this->db->query("INSERT INTO election_settings (setting_key, setting_value) VALUES (?, ?)", array($key, $value));
        }
    }

    public function get_admin_by_id($id) {
        $query = $this->db->query(
            "SELECT id, username, password_hash, full_name, role FROM admin_users WHERE id = ? LIMIT 1",
            array((int)$id)
        );
        return $query->row_array();
    }

    public function update_password($id, $new_hash) {
        return $this->db->query(
            "UPDATE admin_users SET password_hash = ? WHERE id = ?",
            array($new_hash, (int)$id)
        );
    }

    public function get_turnout_stats() {
        $total_voters  = (int)$this->db->query("SELECT COUNT(*) AS total FROM voters")->row()->total;
        $voted_count   = (int)$this->db->query("SELECT COUNT(*) AS total FROM voters WHERE has_voted = 1")->row()->total;
        $unvoted_count = $total_voters - $voted_count;
        $voted_pct     = ($total_voters > 0) ? round(($voted_count / $total_voters) * 100, 1) : 0;
        $unvoted_pct   = ($total_voters > 0) ? round(($unvoted_count / $total_voters) * 100, 1) : 0;

        $hourly = $this->db->query(
            "SELECT DATE_FORMAT(voted_at, '%H:00') AS time_slot, COUNT(*) AS count 
             FROM voters 
             WHERE has_voted = 1 AND voted_at IS NOT NULL 
             GROUP BY DATE_FORMAT(voted_at, '%H:00') 
             ORDER BY time_slot ASC"
        )->result_array();

        return array(
            'total_voters'  => $total_voters,
            'voted_count'   => $voted_count,
            'unvoted_count' => $unvoted_count,
            'voted_pct'     => $voted_pct,
            'unvoted_pct'   => $unvoted_pct,
            'hourly'        => $hourly
        );
    }

    public function get_unvoted_voters($search = '') {
        $clean = trim((string)$search);
        if (!empty($clean)) {
            $q = $this->db->query(
                "SELECT id, member_number, name, status, created_at 
                 FROM voters 
                 WHERE has_voted = 0 AND (name LIKE ? OR member_number LIKE ?) 
                 ORDER BY member_number ASC",
                array('%' . $clean . '%', '%' . $clean . '%')
            );
        } else {
            $q = $this->db->query(
                "SELECT id, member_number, name, status, created_at 
                 FROM voters 
                 WHERE has_voted = 0 
                 ORDER BY member_number ASC"
            );
        }
        return $q->result_array();
    }

    public function get_candidate_voters($candidate_id) {
        $q = $this->db->query(
            "SELECT v.id AS vote_id, v.created_at AS voted_at, v.receipt_token,
                    vt.id AS voter_id, vt.member_number, vt.name AS voter_name
             FROM votes v
             LEFT JOIN voters vt ON vt.id = v.voter_id
             WHERE v.candidate_id = ?
             ORDER BY v.created_at ASC, v.id ASC",
            array((int)$candidate_id)
        );
        return $q->result_array();
    }

    public function get_voter_ballot_history($search = '') {
        $clean = trim((string)$search);
        $sql = "SELECT vt.id AS voter_id, vt.member_number, vt.name AS voter_name, vt.voted_at,
                       v.id AS vote_id, v.receipt_token, v.vote_hash,
                       c.id AS candidate_id, c.name AS candidate_name, c.category, c.candidate_number
                FROM voters vt
                JOIN votes v ON v.voter_id = vt.id
                JOIN candidates c ON c.id = v.candidate_id";

        if (!empty($clean)) {
            $sql .= " WHERE (vt.name LIKE ? OR vt.member_number LIKE ? OR v.receipt_token LIKE ?)";
            $params = array('%' . $clean . '%', '%' . $clean . '%', '%' . $clean . '%');
        } else {
            $params = array();
        }
        $sql .= " ORDER BY vt.voted_at DESC, vt.id DESC, c.category DESC";

        $rows = $this->db->query($sql, $params)->result_array();

        $grouped = array();
        foreach ($rows as $r) {
            $vid = $r['voter_id'];
            if (!isset($grouped[$vid])) {
                $grouped[$vid] = array(
                    'voter_id'      => $r['voter_id'],
                    'member_number' => $r['member_number'],
                    'voter_name'    => $r['voter_name'],
                    'voted_at'      => $r['voted_at'],
                    'receipt_token' => $r['receipt_token'],
                    'votes'         => array()
                );
            }
            $grouped[$vid]['votes'][$r['category']] = array(
                'candidate_id'     => $r['candidate_id'],
                'candidate_number' => $r['candidate_number'],
                'candidate_name'   => $r['candidate_name'],
                'category'         => $r['category']
            );
        }
        return array_values($grouped);
    }

    public function get_doorprize_participants($search = '') {
        $clean = trim((string)$search);
        $sql = "SELECT vt.id AS voter_id, vt.member_number, vt.name AS voter_name, vt.voted_at,
                       COALESCE((SELECT receipt_token FROM votes WHERE voter_id = vt.id LIMIT 1), CONCAT('KOP-', UPPER(SUBSTRING(MD5(vt.id), 1, 8)))) AS receipt_token
                FROM voters vt
                WHERE vt.has_voted = 1 AND vt.status = 'active'";

        if (!empty($clean)) {
            $sql .= " AND (vt.name LIKE ? OR vt.member_number LIKE ?)";
            $params = array('%' . $clean . '%', '%' . $clean . '%');
        } else {
            $params = array();
        }
        $sql .= " ORDER BY vt.voted_at ASC, vt.id ASC";

        return $this->db->query($sql, $params)->result_array();
    }
}

