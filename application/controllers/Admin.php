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

        if ($this->check_login_rate_limit() >= 20) {
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

    public function change_password() {
        $this->require_auth();
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('text/plain')
                ->set_output('405 Method Not Allowed - Aksi mutasi wajib menggunakan method POST');
        }

        $admin_id = (int)$this->session->userdata('admin_id');
        $current_password = (string)$this->input->post('current_password');
        $new_password = (string)$this->input->post('new_password');
        $confirm_password = (string)$this->input->post('confirm_password');
        $redirect_to = $this->input->post('redirect_to', TRUE);
        $safe_redirect = (!empty($redirect_to) && in_array($redirect_to, array('admin', 'admin/candidates', 'admin/voters'))) ? $redirect_to : 'admin';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $this->session->set_flashdata('error', 'Semua kolom password wajib diisi.');
            redirect($safe_redirect);
            return;
        }

        $admin = $this->Admin_model->get_admin_by_id($admin_id);
        if (!$admin || !password_verify($current_password, $admin['password_hash'])) {
            $this->session->set_flashdata('error', 'Password lama yang Anda masukkan tidak sesuai.');
            redirect($safe_redirect);
            return;
        }

        if ($new_password !== $confirm_password) {
            $this->session->set_flashdata('error', 'Konfirmasi password baru tidak cocok.');
            redirect($safe_redirect);
            return;
        }

        if ($current_password === $new_password) {
            $this->session->set_flashdata('error', 'Password baru tidak boleh sama dengan password lama saat ini.');
            redirect($safe_redirect);
            return;
        }

        if (strlen($new_password) < 8) {
            $this->session->set_flashdata('error', 'Password baru harus memiliki panjang minimal 8 karakter.');
            redirect($safe_redirect);
            return;
        }

        if (!preg_match('/[A-Z]/', $new_password)) {
            $this->session->set_flashdata('error', 'Password baru harus mengandung setidaknya satu huruf besar (A-Z).');
            redirect($safe_redirect);
            return;
        }

        if (!preg_match('/[a-z]/', $new_password)) {
            $this->session->set_flashdata('error', 'Password baru harus mengandung setidaknya satu huruf kecil (a-z).');
            redirect($safe_redirect);
            return;
        }

        if (!preg_match('/[0-9]/', $new_password)) {
            $this->session->set_flashdata('error', 'Password baru harus mengandung setidaknya satu angka (0-9).');
            redirect($safe_redirect);
            return;
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
            $this->session->set_flashdata('error', 'Password baru harus mengandung setidaknya satu simbol atau karakter khusus (!@#$%^&* dll).');
            redirect($safe_redirect);
            return;
        }

        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $this->Admin_model->update_password($admin_id, $new_hash);
        $this->Admin_model->log_audit('CHANGE_PASSWORD', $admin['username'], 'Berhasil memperbarui kata sandi akun');

        $this->session->set_flashdata('success', 'Kata sandi berhasil diperbarui. Silakan gunakan password baru ini pada sesi masuk berikutnya.');
        redirect($safe_redirect);
    }

    public function report_turnout() {
        $this->require_auth();
        $search = trim((string)$this->input->get('q', TRUE));

        $data['stats'] = $this->Admin_model->get_turnout_stats();
        $data['unvoted_voters'] = $this->Admin_model->get_unvoted_voters($search);
        $data['search'] = $search;
        $data['settings'] = $this->Admin_model->get_settings();
        $data['current_page'] = 'report_turnout';

        $this->Admin_model->log_audit('VIEW_REPORT', $this->session->userdata('admin_username'), 'Membuka laporan partisipasi pemilih');
        $this->load->view('admin/report_turnout', $data);
    }

    public function export_turnout_excel() {
        $this->require_auth();
        require_once APPPATH . 'libraries/PHPExcel.php';

        $stats = $this->Admin_model->get_turnout_stats();
        $unvoted = $this->Admin_model->get_unvoted_voters();
        $settings = $this->Admin_model->get_settings();

        $excel = new PHPExcel();
        $excel->getProperties()
            ->setCreator("E-Voting " . ($settings['cooperative_name'] ?? 'Koperasi'))
            ->setTitle("Laporan Partisipasi Pemilih");

        $sheet1 = $excel->setActiveSheetIndex(0);
        $sheet1->setTitle('Ringkasan Partisipasi');

        $sheet1->setCellValue('A1', 'LAPORAN TINGKAT PARTISIPASI PEMILIH');
        $sheet1->setCellValue('A2', ($settings['election_title'] ?? 'Pemilihan') . ' - ' . ($settings['cooperative_name'] ?? 'Koperasi'));
        $sheet1->setCellValue('A3', 'Dicetak pada: ' . date('d/m/Y H:i:s') . ' oleh ' . $this->session->userdata('admin_name'));
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet1->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet1->getStyle('A3')->getFont()->setItalic(true)->setSize(9);

        $sheet1->setCellValue('A5', 'Statistik Ringkasan:');
        $sheet1->getStyle('A5')->getFont()->setBold(true);

        $summaryData = array(
            array('Total Hak Suara (DPT)', $stats['total_voters'] . ' Orang'),
            array('Sudah Memilih', $stats['voted_count'] . ' Orang (' . $stats['voted_pct'] . '%)'),
            array('Belum Memilih', $stats['unvoted_count'] . ' Orang (' . $stats['unvoted_pct'] . '%)')
        );

        $r = 6;
        foreach ($summaryData as $row) {
            $sheet1->setCellValue('A' . $r, $row[0]);
            $sheet1->setCellValue('B' . $r, $row[1]);
            $sheet1->getStyle('A' . $r)->getFont()->setBold(true);
            $sheet1->getStyle('A' . $r . ':B' . $r)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $r++;
        }

        $r += 2;
        $sheet1->setCellValue('A' . $r, 'Sebaran Waktu Kehadiran Pemilih:');
        $sheet1->getStyle('A' . $r)->getFont()->setBold(true);
        $r++;

        $sheet1->setCellValue('A' . $r, 'Rentang Waktu');
        $sheet1->setCellValue('B' . $r, 'Jumlah Pemilih');
        $sheet1->getStyle('A' . $r . ':B' . $r)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('A' . $r . ':B' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $r++;

        if (!empty($stats['hourly'])) {
            foreach ($stats['hourly'] as $h) {
                $sheet1->setCellValue('A' . $r, $h['time_slot'] . ' - ' . date('H:00', strtotime($h['time_slot'] . ' +1 hour')));
                $sheet1->setCellValue('B' . $r, (int)$h['count']);
                $sheet1->getStyle('A' . $r . ':B' . $r)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $r++;
            }
        } else {
            $sheet1->setCellValue('A' . $r, 'Belum ada data kehadiran tercatat');
            $sheet1->mergeCells('A' . $r . ':B' . $r);
            $sheet1->getStyle('A' . $r . ':B' . $r)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }

        $sheet1->getColumnDimension('A')->setAutoSize(true);
        $sheet1->getColumnDimension('B')->setAutoSize(true);

        $sheet2 = $excel->createSheet(1);
        $sheet2->setTitle('Daftar Belum Memilih');

        $sheet2->setCellValue('A1', 'DAFTAR ANGGOTA BELUM MENGGUNAKAN HAK SUARA');
        $sheet2->setCellValue('A2', 'Total: ' . count($unvoted) . ' Anggota');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet2->getStyle('A2')->getFont()->setSize(10);

        $headers = array('No', 'No. Anggota', 'Nama Anggota', 'Status Akun');
        $cols = array('A', 'B', 'C', 'D');

        for ($i = 0; $i < count($headers); $i++) {
            $sheet2->setCellValue($cols[$i] . '4', $headers[$i]);
        }
        $sheet2->getStyle('A4:D4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet2->getStyle('A4:D4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet2->getStyle('A4:D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $rowIdx = 5;
        $no = 1;
        foreach ($unvoted as $item) {
            $sheet2->setCellValue('A' . $rowIdx, $no++);
            $sheet2->setCellValueExplicit('B' . $rowIdx, (string)$item['member_number'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValue('C' . $rowIdx, $item['name']);
            $sheet2->setCellValue('D' . $rowIdx, strtoupper($item['status']));

            $sheet2->getStyle('A' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('B' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('D' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('A' . $rowIdx . ':D' . $rowIdx)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $rowIdx++;
        }

        foreach ($cols as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $excel->setActiveSheetIndex(0);

        $this->Admin_model->log_audit('EXPORT_EXCEL', $this->session->userdata('admin_username'), 'Mengekspor laporan partisipasi pemilih ke Excel');

        $filename = 'Laporan_Partisipasi_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }

    public function audit_votes() {
        $this->require_auth();
        $candidate_id = (int)$this->input->get('candidate_id');
        $search_voter = trim((string)$this->input->get('q', TRUE));

        $candidates = $this->Admin_model->get_all_candidates();
        if ($candidate_id <= 0 && !empty($candidates)) {
            $candidate_id = (int)$candidates[0]['id'];
        }

        $data['candidates'] = $candidates;
        $data['selected_candidate_id'] = $candidate_id;
        $data['candidate_voters'] = ($candidate_id > 0) ? $this->Admin_model->get_candidate_voters($candidate_id) : array();
        $data['voter_history'] = $this->Admin_model->get_voter_ballot_history($search_voter);
        $data['search_voter'] = $search_voter;
        $data['settings'] = $this->Admin_model->get_settings();
        $data['current_page'] = 'audit_votes';

        $this->Admin_model->log_audit('VIEW_AUDIT', $this->session->userdata('admin_username'), 'Membuka laporan audit jejak suara');
        $this->load->view('admin/audit_votes', $data);
    }

    public function export_audit_excel() {
        $this->require_auth();
        require_once APPPATH . 'libraries/PHPExcel.php';

        $settings = $this->Admin_model->get_settings();
        $voter_history = $this->Admin_model->get_voter_ballot_history();
        $candidates = $this->Admin_model->get_all_candidates();

        $excel = new PHPExcel();
        $excel->getProperties()
            ->setCreator("E-Voting " . ($settings['cooperative_name'] ?? 'Koperasi'))
            ->setTitle("Laporan Audit Jejak Pilihan Suara");

        $sheet1 = $excel->setActiveSheetIndex(0);
        $sheet1->setTitle('Jejak Pilihan Pemilih');

        $sheet1->setCellValue('A1', 'AUDIT TRAIL JEJAK PILIHAN PEMILIH');
        $sheet1->setCellValue('A2', ($settings['election_title'] ?? 'Pemilihan') . ' - Rahasia untuk Kebutuhan Audit Resmi');
        $sheet1->setCellValue('A3', 'Dicetak pada: ' . date('d/m/Y H:i:s') . ' oleh ' . $this->session->userdata('admin_name'));
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet1->getStyle('A2')->getFont()->setSize(10);

        $headers = array('No', 'Waktu Memilih', 'No. Anggota', 'Nama Pemilih', 'Pilihan Ketua', 'Pilihan Pengawas', 'Token Struk Verifikasi');
        $cols = array('A', 'B', 'C', 'D', 'E', 'F', 'G');

        for ($i = 0; $i < count($headers); $i++) {
            $sheet1->setCellValue($cols[$i] . '5', $headers[$i]);
        }
        $sheet1->getStyle('A5:G5')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('A5:G5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet1->getStyle('A5:G5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $rowIdx = 6;
        $no = 1;
        foreach ($voter_history as $item) {
            $ketuaText = isset($item['votes']['ketua'])
                ? '#' . $item['votes']['ketua']['candidate_number'] . ' - ' . $item['votes']['ketua']['candidate_name']
                : '-';
            $pengawasText = isset($item['votes']['pengawas'])
                ? '#' . $item['votes']['pengawas']['candidate_number'] . ' - ' . $item['votes']['pengawas']['candidate_name']
                : '-';

            $sheet1->setCellValue('A' . $rowIdx, $no++);
            $sheet1->setCellValue('B' . $rowIdx, $item['voted_at'] ? date('d/m/Y H:i:s', strtotime($item['voted_at'])) : '-');
            $sheet1->setCellValueExplicit('C' . $rowIdx, (string)$item['member_number'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet1->setCellValue('D' . $rowIdx, $item['voter_name']);
            $sheet1->setCellValue('E' . $rowIdx, $ketuaText);
            $sheet1->setCellValue('F' . $rowIdx, $pengawasText);
            $sheet1->setCellValueExplicit('G' . $rowIdx, (string)$item['receipt_token'], PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet1->getStyle('A' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('B' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('C' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('G' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('A' . $rowIdx . ':G' . $rowIdx)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $rowIdx++;
        }

        foreach ($cols as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet2 = $excel->createSheet(1);
        $sheet2->setTitle('Pemilih per Calon');

        $sheet2->setCellValue('A1', 'REKAP DAFTAR PEMILIH PER CALON');
        $sheet2->setCellValue('A2', 'Rincian pemilih yang memberikan suara untuk masing-masing kandidat');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $headers2 = array('No', 'Kategori', 'No. Urut', 'Nama Calon', 'No. Anggota Pemilih', 'Nama Pemilih', 'Waktu Memilih', 'Token Struk');
        $cols2 = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

        for ($i = 0; $i < count($headers2); $i++) {
            $sheet2->setCellValue($cols2[$i] . '4', $headers2[$i]);
        }
        $sheet2->getStyle('A4:H4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet2->getStyle('A4:H4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet2->getStyle('A4:H4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $rowIdx2 = 5;
        $no2 = 1;
        foreach ($candidates as $cand) {
            $voters = $this->Admin_model->get_candidate_voters($cand['id']);
            foreach ($voters as $cv) {
                $sheet2->setCellValue('A' . $rowIdx2, $no2++);
                $sheet2->setCellValue('B' . $rowIdx2, strtoupper($cand['category']));
                $sheet2->setCellValue('C' . $rowIdx2, '#' . $cand['candidate_number']);
                $sheet2->setCellValue('D' . $rowIdx2, $cand['name']);
                $sheet2->setCellValueExplicit('E' . $rowIdx2, (string)($cv['member_number'] ?? '-'), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet2->setCellValue('F' . $rowIdx2, $cv['voter_name'] ?? 'Data Historis (Anonim)');
                $sheet2->setCellValue('G' . $rowIdx2, $cv['voted_at'] ? date('d/m/Y H:i:s', strtotime($cv['voted_at'])) : '-');
                $sheet2->setCellValueExplicit('H' . $rowIdx2, (string)$cv['receipt_token'], PHPExcel_Cell_DataType::TYPE_STRING);

                $sheet2->getStyle('A' . $rowIdx2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('B' . $rowIdx2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('C' . $rowIdx2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('E' . $rowIdx2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('G' . $rowIdx2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('H' . $rowIdx2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('A' . $rowIdx2 . ':H' . $rowIdx2)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $rowIdx2++;
            }
        }

        foreach ($cols2 as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $excel->setActiveSheetIndex(0);

        $this->Admin_model->log_audit('EXPORT_EXCEL', $this->session->userdata('admin_username'), 'Mengekspor laporan audit jejak suara ke Excel');

        $filename = 'Laporan_Audit_Suara_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }

    public function doorprize() {
        $this->require_auth();
        $search = trim((string)$this->input->get('q', TRUE));

        $data['participants'] = $this->Admin_model->get_doorprize_participants($search);
        $data['search'] = $search;
        $data['settings'] = $this->Admin_model->get_settings();
        $data['current_page'] = 'doorprize';

        $this->Admin_model->log_audit('VIEW_DOORPRIZE', $this->session->userdata('admin_username'), 'Membuka data peserta undian doorprize');
        $this->load->view('admin/doorprize', $data);
    }

    public function export_doorprize_excel() {
        $this->require_auth();
        require_once APPPATH . 'libraries/PHPExcel.php';

        $settings = $this->Admin_model->get_settings();
        $participants = $this->Admin_model->get_doorprize_participants();

        $excel = new PHPExcel();
        $excel->getProperties()
            ->setCreator("E-Voting " . ($settings['cooperative_name'] ?? 'Koperasi'))
            ->setTitle("Daftar Peserta Sah Undian Doorprize");

        $sheet = $excel->setActiveSheetIndex(0);
        $sheet->setTitle('Peserta Doorprize');

        $sheet->setCellValue('A1', 'DAFTAR PESERTA SAH UNDIAN DOORPRIZE RAT');
        $sheet->setCellValue('A2', ($settings['cooperative_name'] ?? 'Koperasi') . ' - Khusus Anggota yang Telah Menggunakan Hak Suara');
        $sheet->setCellValue('A3', 'Total Kupon Sah: ' . count($participants) . ' Peserta | Dicetak: ' . date('d/m/Y H:i:s'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A2')->getFont()->setSize(10);

        $headers = array('No', 'No. Kupon / Token Struk', 'No. Anggota', 'Nama Anggota', 'Waktu Memilih', 'Status Kehadiran');
        $cols = array('A', 'B', 'C', 'D', 'E', 'F');

        for ($i = 0; $i < count($headers); $i++) {
            $sheet->setCellValue($cols[$i] . '5', $headers[$i]);
        }
        $sheet->getStyle('A5:F5')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A5:F5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $rowIdx = 6;
        $no = 1;
        foreach ($participants as $item) {
            $sheet->setCellValue('A' . $rowIdx, $no++);
            $sheet->setCellValueExplicit('B' . $rowIdx, (string)$item['receipt_token'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowIdx, (string)$item['member_number'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowIdx, $item['name']);
            $sheet->setCellValue('E' . $rowIdx, $item['voted_at'] ? date('d/m/Y H:i:s', strtotime($item['voted_at'])) : '-');
            $sheet->setCellValue('F' . $rowIdx, 'HADIR & MEMILIH');

            $sheet->getStyle('A' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowIdx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $rowIdx . ':F' . $rowIdx)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $rowIdx++;
        }

        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $this->Admin_model->log_audit('EXPORT_EXCEL', $this->session->userdata('admin_username'), 'Mengekspor daftar kupon undian doorprize ke Excel');

        $filename = 'Peserta_Doorprize_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }
}

