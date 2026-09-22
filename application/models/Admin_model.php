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
}
