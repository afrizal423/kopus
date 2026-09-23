<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Voting_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function verify_voter($rfid_uid) {
        $clean_uid = trim((string)$rfid_uid);
        $query = $this->db->query(
            "SELECT id, rfid_uid, name, member_number, status, has_voted, voted_at FROM voters WHERE rfid_uid = ? LIMIT 1",
            array($clean_uid)
        );

        if ($query->num_rows() === 1) {
            return $query->row_array();
        }
        return null;
    }

    public function get_candidates($category) {
        $query = $this->db->query(
            "SELECT id, category, candidate_number, name, photo, vision, mission 
             FROM candidates 
             WHERE category = ? AND is_active = 1 
             ORDER BY candidate_number ASC",
            array($category)
        );
        return $query->result_array();
    }

    public function get_election_status() {
        $query = $this->db->query(
            "SELECT setting_key, setting_value FROM election_settings 
             WHERE setting_key IN ('election_status', 'election_title', 'cooperative_name', 'booth_timeout_seconds')"
        );
        
        $settings = array(
            'election_status' => 'open',
            'election_title' => 'Pemilihan Pengurus Koperasi',
            'cooperative_name' => 'Koperasi',
            'booth_timeout_seconds' => 120
        );

        foreach ($query->result_array() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    // Atomic conditional update prevents concurrent double-voting race conditions.
    public function submit_vote($voter_id, $selections) {
        $now = date('Y-m-d H:i:s');
        $secret_salt = $this->get_ledger_secret();
        $genesis_hash = str_repeat('0', 64);
        $receipt_token = 'KOP-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        $this->db->trans_start();

        $status_check = $this->db->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status' LIMIT 1")->row_array();
        if (empty($status_check['setting_value']) || $status_check['setting_value'] !== 'open') {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->query(
            "UPDATE voters SET has_voted = 1, voted_at = ? WHERE id = ? AND has_voted = 0 AND status = 'active'",
            array($now, (int)$voter_id)
        );

        if ($this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            return false;
        }

        // Retrieve last vote hash to maintain cryptographic ledger chain.
        $last_vote = $this->db->query("SELECT vote_hash FROM votes ORDER BY id DESC LIMIT 1")->row_array();
        $prev_hash = (!empty($last_vote['vote_hash'])) ? $last_vote['vote_hash'] : $genesis_hash;

        foreach ($selections as $category => $candidate_id) {
            $cid = (int)$candidate_id;
            if ($cid <= 0) {
                continue;
            }

            // Verify candidate exists and matches category before inserting vote.
            $cand_check = $this->db->query(
                "SELECT id FROM candidates WHERE id = ? AND category = ? AND is_active = 1",
                array($cid, $category)
            );
            if ($cand_check->num_rows() !== 1) {
                $this->db->trans_rollback();
                return false;
            }

            $current_hash = hash_hmac('sha256', $prev_hash . '|' . $cid . '|' . $now . '|' . $receipt_token, $secret_salt);

            $this->db->query(
                "INSERT INTO votes (candidate_id, previous_hash, vote_hash, receipt_token, created_at) VALUES (?, ?, ?, ?, ?)",
                array($cid, $prev_hash, $current_hash, $receipt_token, $now)
            );

            $prev_hash = $current_hash;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $receipt_token;
    }

    public function verify_ledger_integrity() {
        $secret_salt = $this->get_ledger_secret();
        $genesis_hash = str_repeat('0', 64);

        $query = $this->db->query(
            "SELECT id, candidate_id, previous_hash, vote_hash, receipt_token, created_at FROM votes ORDER BY id ASC"
        );
        $votes = $query->result_array();
        $total = count($votes);

        if ($total === 0) {
            return array(
                'is_valid' => true,
                'total_votes' => 0,
                'corrupted_id' => null,
                'message' => 'Belum ada suara masuk. Ledger kosong dan utuh.'
            );
        }

        $expected_prev = $genesis_hash;

        foreach ($votes as $v) {
            if ($v['previous_hash'] !== $expected_prev) {
                return array(
                    'is_valid' => false,
                    'total_votes' => $total,
                    'corrupted_id' => $v['id'],
                    'message' => 'Integritas rantai terputus pada Baris #' . $v['id'] . '. Indikasi perubahan atau penghapusan suara manual.'
                );
            }

            $expected_hash = hash_hmac('sha256', $v['previous_hash'] . '|' . $v['candidate_id'] . '|' . $v['created_at'] . '|' . $v['receipt_token'], $secret_salt);
            if ($v['vote_hash'] !== $expected_hash) {
                return array(
                    'is_valid' => false,
                    'total_votes' => $total,
                    'corrupted_id' => $v['id'],
                    'message' => 'Kecurangan terdeteksi pada Baris #' . $v['id'] . '. Data kandidat atau timestamp di database telah diedit!'
                );
            }

            $expected_prev = $v['vote_hash'];
        }

        return array(
            'is_valid' => true,
            'total_votes' => $total,
            'corrupted_id' => null,
            'message' => 'Semua ' . $total . ' catatan suara diverifikasi valid secara kriptografis tanpa modifikasi.'
        );
    }

    private function get_ledger_secret() {
        $q = $this->db->query("SELECT setting_value FROM election_settings WHERE setting_key = 'ledger_secret_salt' LIMIT 1");
        $row = $q->row_array();
        if (empty($row['setting_value'])) {
            throw new \RuntimeException('Ledger secret salt tidak ditemukan pada database.');
        }
        return $row['setting_value'];
    }
}
