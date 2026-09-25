<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Voting extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Voting_model');
        $this->apply_security_headers();
    }

    private function apply_security_headers() {
        $this->output->set_header('X-Content-Type-Options: nosniff');
        $this->output->set_header('X-Frame-Options: DENY');
        $this->output->set_header('X-XSS-Protection: 1; mode=block');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');
    }

    public function index() {
        $data['settings'] = $this->Voting_model->get_election_status();
        $data['ketua_candidates'] = $this->Voting_model->get_candidates('ketua');
        $data['pengawas_candidates'] = $this->Voting_model->get_candidates('pengawas');
        $data['all_candidates'] = array_merge($data['ketua_candidates'], $data['pengawas_candidates']);
        $this->load->view('voting/scanner', $data);
    }

    public function verify_rfid() {
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Metode request tidak diizinkan.')));
        }

        // Rate limiter prevents automated RFID brute-forcing.
        $this->check_rate_limit();

        $raw_uid = $this->input->post('rfid_uid', TRUE);
        $clean_uid = trim(str_replace(chr(0), '', (string)$raw_uid));

        if (empty($clean_uid) || strlen($clean_uid) < 4 || strlen($clean_uid) > 64) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Format kartu RFID tidak valid atau kosong.'
                )));
        }

        if (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $clean_uid)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Karakter RFID tidak valid.'
                )));
        }

        $settings = $this->Voting_model->get_election_status();
        if ($settings['election_status'] !== 'open') {
            $msg = ($settings['election_status'] === 'paused') 
                ? 'Sesi pemilihan sedang dijeda sementara oleh panitia.' 
                : 'Sesi pemilihan telah ditutup.';
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => $msg)));
        }

        $voter = $this->Voting_model->verify_voter($clean_uid);

        if (!$voter) {
            $this->increment_rate_limit();
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Kartu belum terdaftar dalam Daftar Pemilih Tetap (DPT).'
                )));
        }

        if ($voter['status'] !== 'active') {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Status anggota diblokir. Harap hubungi panitia pemilihan.'
                )));
        }

        if ((int)$voter['has_voted'] === 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Hak suara Anda sudah digunakan sebelumnya pada ' . date('d/m/Y H:i', strtotime($voter['voted_at'])) . ' WIB.'
                )));
        }

        // Regenerate session identifier to mitigate session fixation before entering ballot booth.
        $this->session->sess_regenerate(TRUE);
        $this->session->set_userdata(array(
            'voter_id' => $voter['id'],
            'voter_name' => $voter['name'],
            'member_number' => $voter['member_number'],
            'booth_entry_time' => time()
        ));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => 'success', 
                'redirect' => base_url('voting/ballot')
            )));
    }

    public function ballot() {
        if (!$this->session->userdata('voter_id')) {
            redirect('voting');
            return;
        }

        $settings = $this->Voting_model->get_election_status();
        if ($settings['election_status'] !== 'open') {
            $this->session->sess_destroy();
            redirect('voting');
            return;
        }

        $data['settings'] = $settings;
        $data['ketua_candidates'] = $this->Voting_model->get_candidates('ketua');
        $data['pengawas_candidates'] = $this->Voting_model->get_candidates('pengawas');
        $data['voter_name'] = $this->session->userdata('voter_name');
        $data['member_number'] = $this->session->userdata('member_number');
        $data['timeout_seconds'] = (int)$settings['booth_timeout_seconds'];

        $this->load->view('voting/ballot', $data);
    }

    public function submit_vote() {
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Metode request tidak diizinkan.')));
        }

        $voter_id = $this->session->userdata('voter_id');
        if (!$voter_id) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Sesi bilik suara telah kedaluwarsa atau tidak valid.'
                )));
        }

        $entry_time = $this->session->userdata('booth_entry_time');
        $settings = $this->Voting_model->get_election_status();

        if (empty($settings['election_status']) || $settings['election_status'] !== 'open') {
            $this->session->sess_destroy();
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Pemilihan sedang tidak aktif atau telah ditutup.'
                )));
        }

        $timeout = (int)$settings['booth_timeout_seconds'];

        // Enforce session timeout on submission to reject abandoned booth submissions.
        if ($entry_time && (time() - $entry_time) > ($timeout + 30)) {
            $this->session->sess_destroy();
            return $this->output
                ->set_status_header(408)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Waktu bilik suara telah habis. Silakan tap kartu kembali.'
                )));
        }

        $selections = $this->input->post('selections', TRUE);
        if (empty($selections['ketua']) || empty($selections['pengawas'])) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Pilihan tidak lengkap. Wajib memilih 1 Ketua dan 1 Pengawas.'
                )));
        }

        $receipt_token = $this->Voting_model->submit_vote($voter_id, array(
            'ketua' => (int)$selections['ketua'],
            'pengawas' => (int)$selections['pengawas']
        ));

        // Immediately purge voter session so back button or reload cannot access ballot.
        $this->session->sess_destroy();

        if ($receipt_token !== false) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'success',
                    'receipt_token' => $receipt_token,
                    'redirect' => base_url('voting/success?receipt=' . urlencode($receipt_token))
                )));
        }

        return $this->output
            ->set_status_header(500)
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => 'error', 
                'message' => 'Gagal merekam suara. Hak suara mungkin telah digunakan atau terjadi kendala sistem.'
            )));
    }

    public function cancel() {
        $this->session->sess_destroy();
        redirect('voting');
    }

    /**
     * Development helper to preview ballot without physical RFID reader.
     * Only accessible in non-production environments.
     */
    public function dev_booth() {
        $client_ip = $this->input->ip_address();
        $is_dev_host = in_array($client_ip, array('127.0.0.1', '::1'), true) 
                    || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
                    || ENVIRONMENT !== 'production';

        if (!$is_dev_host) {
            show_404();
            return;
        }

        // Pick the first active voter who has not voted
        $voter = $this->db->query("SELECT id, name, member_number FROM voters WHERE has_voted = 0 AND status = 'active' ORDER BY id ASC LIMIT 1")->row_array();
        if (!$voter) {
            // If all have voted during testing, temporarily reset the first voter for testing
            $voter = $this->db->query("SELECT id, name, member_number FROM voters WHERE status = 'active' ORDER BY id ASC LIMIT 1")->row_array();
            if ($voter) {
                $this->db->query("UPDATE voters SET has_voted = 0, voted_at = NULL WHERE id = ?", array($voter['id']));
            }
        }

        if (!$voter) {
            die('Tidak ada data pemilih aktif di tabel voters.');
        }

        $this->session->sess_regenerate(TRUE);
        $this->session->set_userdata(array(
            'voter_id' => $voter['id'],
            'voter_name' => $voter['name'],
            'member_number' => $voter['member_number'],
            'booth_entry_time' => time()
        ));

        redirect('voting/ballot');
    }

    public function success() {
        $receipt = $this->input->get('receipt', TRUE);
        $clean_receipt = trim((string)$receipt);
        $data['receipt_token'] = (!empty($clean_receipt)) ? htmlspecialchars($clean_receipt, ENT_QUOTES, 'UTF-8') : '-';
        $this->load->view('voting/success', $data);
    }

    private function check_rate_limit() {
        $ip = $this->input->ip_address();
        $key = 'rate_limit_rfid_' . md5($ip);
        $attempts = (int)$this->session->userdata($key . '_attempts');
        $last_attempt = (int)$this->session->userdata($key . '_time');

        if ($attempts >= 8 && (time() - $last_attempt) < 30) {
            $this->output
                ->set_status_header(429)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error', 
                    'message' => 'Terlalu banyak percobaan tap kartu. Mohon tunggu 30 detik.'
                )))
                ->_display();
            exit;
        }
    }

    private function increment_rate_limit() {
        $ip = $this->input->ip_address();
        $key = 'rate_limit_rfid_' . md5($ip);
        $attempts = (int)$this->session->userdata($key . '_attempts');
        $this->session->set_userdata(array(
            $key . '_attempts' => $attempts + 1,
            $key . '_time' => time()
        ));
    }
}
