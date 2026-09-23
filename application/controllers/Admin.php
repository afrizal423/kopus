<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Admin_model');
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

    private function require_auth() {
        if (!$this->session->userdata('admin_id')) {
            redirect('admin/login');
            exit;
        }
    }

    public function login() {
        if ($this->session->userdata('admin_id')) {
            redirect('admin');
            return;
        }
        $data['settings'] = $this->Admin_model->get_settings();
        $this->load->view('admin/login', $data);
    }

    public function authenticate() {
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('admin/login');
            return;
        }

        if ($this->check_login_rate_limit() >= 5) {
            $this->session->set_flashdata('error', 'Terlalu banyak percobaan login gagal dari perangkat ini. Silakan tunggu 1 menit.');
            redirect('admin/login');
            return;
        }

        $username = trim((string)$this->input->post('username', TRUE));
        $password = (string)$this->input->post('password');

        if (empty($username) || empty($password)) {
            $this->session->set_flashdata('error', 'Username dan password wajib diisi.');
            redirect('admin/login');
            return;
        }

        $admin = $this->Admin_model->authenticate($username, $password);
        if ($admin) {
            $this->session->sess_regenerate(TRUE);
            $this->session->set_userdata(array(
                'admin_id' => $admin['id'],
                'admin_username' => $admin['username'],
                'admin_name' => $admin['full_name'],
                'admin_role' => $admin['role']
            ));
            $this->Admin_model->log_audit('LOGIN', $admin['username'], 'Berhasil login ke sistem');
            redirect('admin');
        } else {
            $this->Admin_model->log_audit('LOGIN_FAILED', !empty($username) ? $username : 'unknown', 'Percobaan login gagal dengan kredensial salah');
            $this->session->set_flashdata('error', 'Kombinasi username atau password salah.');
            redirect('admin/login');
        }
    }

    private function check_login_rate_limit() {
        $ip = $this->input->ip_address();
        $since = date('Y-m-d H:i:s', time() - 60);
        $q = $this->db->query(
            "SELECT COUNT(*) AS total FROM audit_logs WHERE event_type = 'LOGIN_FAILED' AND ip_address = ? AND created_at >= ?",
            array($ip, $since)
        );
        $row = $q->row_array();
        return (int)($row['total'] ?? 0);
    }

    public function logout() {
        if ($this->session->userdata('admin_username')) {
            $this->Admin_model->log_audit('LOGOUT', $this->session->userdata('admin_username'), 'Logout dari sistem');
        }
        $this->session->sess_destroy();
        redirect('admin/login');
    }

    public function index() {
        $this->require_auth();
        $data['stats'] = $this->Admin_model->get_dashboard_stats();
        $data['settings'] = $this->Admin_model->get_settings();
        $data['audit_logs'] = $this->Admin_model->get_audit_logs(15);
        $data['integrity'] = $this->Voting_model->verify_ledger_integrity();
        $data['current_page'] = 'dashboard';
        $this->load->view('admin/dashboard', $data);
    }

    public function candidates() {
        $this->require_auth();
        $data['candidates'] = $this->Admin_model->get_all_candidates();
        $data['settings'] = $this->Admin_model->get_settings();
        $data['current_page'] = 'candidates';
        $this->load->view('admin/candidates', $data);
    }

    public function candidate_save() {
        $this->require_auth();
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('admin/candidates');
            return;
        }

        $id = (int)$this->input->post('id');
        $category = $this->input->post('category', TRUE);
        $candidate_number = (int)$this->input->post('candidate_number');
        $name = trim((string)$this->input->post('name', TRUE));
        $vision = trim((string)$this->input->post('vision', TRUE));
        $mission = trim((string)$this->input->post('mission', TRUE));

        if (!in_array($category, array('ketua', 'pengawas')) || $candidate_number <= 0 || empty($name)) {
            $this->session->set_flashdata('error', 'Data kandidat tidak valid atau belum lengkap.');
            redirect('admin/candidates');
            return;
        }

        $photo_path = null;
        if (!empty($_FILES['photo']['name'])) {
            $config['upload_path']   = './assets/uploads/candidates/';
            $config['allowed_types'] = 'gif|jpg|jpeg|png|webp|svg';
            $config['max_size']      = 2048; // 2MB
            $config['encrypt_name']  = TRUE;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('photo')) {
                $upload_data = $this->upload->data();
                $photo_path = 'assets/uploads/candidates/' . $upload_data['file_name'];
            } else {
                $this->session->set_flashdata('error', 'Upload foto gagal: ' . strip_tags($this->upload->display_errors()));
                redirect('admin/candidates');
                return;
            }
        }

        $data = array(
            'category' => $category,
            'candidate_number' => $candidate_number,
            'name' => $name,
            'vision' => $vision,
            'mission' => $mission
        );

        if ($photo_path) {
            $data['photo'] = $photo_path;
        }

        if ($id > 0) {
            $this->Admin_model->update_candidate($id, $data);
            $this->Admin_model->log_audit('UPDATE_CANDIDATE', $this->session->userdata('admin_username'), 'Mengubah calon #' . $id . ' (' . $name . ')');
            $this->session->set_flashdata('success', 'Data calon berhasil diperbarui.');
        } else {
            if (!$photo_path) {
                $data['photo'] = 'assets/foto/default-avatar.svg';
            }
            $this->Admin_model->create_candidate($data);
            $this->Admin_model->log_audit('CREATE_CANDIDATE', $this->session->userdata('admin_username'), 'Menambah calon baru (' . $name . ')');
            $this->session->set_flashdata('success', 'Calon baru berhasil ditambahkan.');
        }

        redirect('admin/candidates');
    }

    public function candidate_toggle($id) {
        $this->require_auth();
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('text/plain')
                ->set_output('405 Method Not Allowed - Aksi mutasi wajib menggunakan method POST');
        }
        $this->Admin_model->toggle_candidate((int)$id);
        $this->Admin_model->log_audit('TOGGLE_CANDIDATE', $this->session->userdata('admin_username'), 'Mengubah status aktif calon #' . (int)$id);
        $this->session->set_flashdata('success', 'Status calon berhasil diubah.');
        redirect('admin/candidates');
    }

    public function candidate_delete($id) {
        $this->require_auth();
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('text/plain')
                ->set_output('405 Method Not Allowed - Aksi mutasi wajib menggunakan method POST');
        }
        $res = $this->Admin_model->delete_candidate((int)$id);
        if ($res['status']) {
            $this->Admin_model->log_audit('DELETE_CANDIDATE', $this->session->userdata('admin_username'), 'Menghapus calon ID #' . (int)$id);
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('admin/candidates');
    }

    public function voters() {
        $this->require_auth();
        $search = trim((string)$this->input->get('q', TRUE));
        $data['voters'] = $this->Admin_model->get_voters($search);
        $data['search'] = $search;
        $data['settings'] = $this->Admin_model->get_settings();
        $data['current_page'] = 'voters';
        $this->load->view('admin/voters', $data);
    }

    public function voter_save() {
        $this->require_auth();
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('admin/voters');
            return;
        }

        $rfid_uid = trim((string)$this->input->post('rfid_uid', TRUE));
        $name = trim((string)$this->input->post('name', TRUE));
        $member_number = trim((string)$this->input->post('member_number', TRUE));

        if (empty($rfid_uid) || empty($name) || empty($member_number)) {
            $this->session->set_flashdata('error', 'Semua kolom data pemilih wajib diisi.');
            redirect('admin/voters');
            return;
        }

        $res = $this->Admin_model->create_voter(array(
            'rfid_uid' => $rfid_uid,
            'name' => $name,
            'member_number' => $member_number,
            'status' => 'active'
        ));

        if ($res['status']) {
            $this->Admin_model->log_audit('CREATE_VOTER', $this->session->userdata('admin_username'), 'Mendaftarkan kartu RFID baru untuk ' . $name);
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }

        redirect('admin/voters');
    }

    public function voter_toggle($id) {
        $this->require_auth();
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('text/plain')
                ->set_output('405 Method Not Allowed - Aksi mutasi wajib menggunakan method POST');
        }
        $status = $this->Admin_model->toggle_voter_status((int)$id);
        $this->Admin_model->log_audit('TOGGLE_VOTER', $this->session->userdata('admin_username'), 'Mengubah status pemilih ID #' . (int)$id . ' menjadi ' . $status);
        $this->session->set_flashdata('success', 'Status pemilih berhasil diubah menjadi ' . $status . '.');
        redirect('admin/voters');
    }

    public function voter_reset($id) {
        $this->require_auth();
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('text/plain')
                ->set_output('405 Method Not Allowed - Aksi mutasi wajib menggunakan method POST');
        }
        $this->Admin_model->reset_voter_vote((int)$id, $this->session->userdata('admin_username'));
        $this->session->set_flashdata('success', 'Status pemilih telah direset (belum memilih). Aktivitas ini telah dicatat ke audit log.');
        redirect('admin/voters');
    }

    public function verify_tamper() {
        $this->require_auth();
        $result = $this->Voting_model->verify_ledger_integrity();
        $this->Admin_model->log_audit('VERIFY_TAMPER', $this->session->userdata('admin_username'), 'Menjalankan verifikasi integritas database: ' . ($result['is_valid'] ? 'VALID' : 'RUSAK'));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function settings_save() {
        $this->require_auth();
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect('admin');
            return;
        }

        $election_title = trim((string)$this->input->post('election_title', TRUE));
        $cooperative_name = trim((string)$this->input->post('cooperative_name', TRUE));
        $election_status = $this->input->post('election_status', TRUE);
        $quick_count_public = $this->input->post('quick_count_public', TRUE);
        $booth_timeout_seconds = (int)$this->input->post('booth_timeout_seconds');

        if (!empty($election_title)) $this->Admin_model->update_setting('election_title', $election_title);
        if (!empty($cooperative_name)) $this->Admin_model->update_setting('cooperative_name', $cooperative_name);
        if (in_array($election_status, array('open', 'paused', 'closed'))) {
            $this->Admin_model->update_setting('election_status', $election_status);
        }
        $this->Admin_model->update_setting('quick_count_public', ($quick_count_public === '1') ? '1' : '0');
        if ($booth_timeout_seconds >= 30) {
            $this->Admin_model->update_setting('booth_timeout_seconds', (string)$booth_timeout_seconds);
        }

        $this->Admin_model->log_audit('UPDATE_SETTINGS', $this->session->userdata('admin_username'), 'Memperbarui pengaturan sesi pemilihan');
        $this->session->set_flashdata('success', 'Pengaturan pemilihan berhasil disimpan.');
        redirect('admin');
    }

    public function export_results() {
        $this->require_auth();
        $data['stats'] = $this->Admin_model->get_dashboard_stats();
        $data['settings'] = $this->Admin_model->get_settings();
        $data['integrity'] = $this->Voting_model->verify_ledger_integrity();
        $this->load->view('admin/export_report', $data);
    }
}
