<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        if ($this->session->userdata('role') !== 'admin') {
            show_error('Akses hanya untuk admin', 403);
        }

        $this->load->model('M_user');
        $this->load->model('M_user_activity');
    }

    public function index() {
        $keyword = trim($this->input->get('q'));
        $page = max(1, (int) $this->input->get('page'));
        $limit = 10;
        $total = $this->M_user->count_filtered($keyword);

        $data = [
            'title' => 'Kelola User',
            'active_menu' => 'users',
            'keyword' => $keyword,
            'users' => $this->M_user->get_filtered($keyword, $limit, ($page - 1) * $limit),
            'page' => $page,
            'total_pages' => max(1, (int) ceil($total / $limit))
        ];

        $this->render('users/index', $data);
    }

    public function simpan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $id = (int) $this->input->post('id');
        $username = trim($this->input->post('username'));
        $email = trim($this->input->post('email'));
        $password = $this->input->post('password');

        if ($username === '') {
            echo json_encode(['status' => false, 'message' => 'Username wajib diisi']);
            return;
        }

        if (!$id && strlen($password) < 4) {
            echo json_encode(['status' => false, 'message' => 'Password minimal 4 karakter']);
            return;
        }

        if ($this->M_user->username_exists($username, $id ?: null)) {
            echo json_encode(['status' => false, 'message' => 'Username sudah dipakai']);
            return;
        }

        if ($this->M_user->email_exists($email, $id ?: null)) {
            echo json_encode(['status' => false, 'message' => 'Email sudah dipakai']);
            return;
        }

        $data = [
            'username' => $username,
            'fullname' => trim($this->input->post('fullname')),
            'email' => $email,
            'role' => $this->input->post('role') === 'admin' ? 'admin' : 'user',
            'reset_date' => $this->input->post('reset_date') ?: null,
            'grafik' => $this->input->post('grafik') === 'N' ? 'N' : 'Y',
            'bg_color' => trim($this->input->post('bg_color')),
            'txt_color' => trim($this->input->post('txt_color'))
        ];
        $data['batas_transaksi'] = $this->parse_limit($this->input->post('batas_transaksi'));
        $data['batas_total_transaksi'] = $this->parse_limit($this->input->post('batas_total_transaksi'));
        if ($data['batas_transaksi'] <= 0 || $data['batas_total_transaksi'] <= 0) {
            echo json_encode(['status' => false, 'message' => 'Batas transaksi harus lebih besar dari 0']);
            return;
        }

        if ($password !== '') {
            $data['password'] = $password;
        }

        if ($id) {
            $this->M_user->update($id, $data);
            $message = 'User berhasil diupdate';
        } else {
            $this->M_user->insert($data);
            $message = 'User berhasil ditambahkan';
        }

        echo json_encode(['status' => true, 'message' => $message]);
    }

    private function parse_limit($value) {
        return (float) preg_replace('/[^0-9]/', '', (string) $value);
    }

    public function get_data() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user = $this->M_user->get_by_id((int) $this->input->post('id'));
        if (!$user) {
            echo json_encode(['status' => false, 'message' => 'User tidak ditemukan']);
            return;
        }

        unset($user->password);
        echo json_encode(['status' => true, 'data' => $user]);
    }

    public function hapus() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $id = (int) $this->input->post('id');
        if ($id === (int) $this->session->userdata('user_id')) {
            echo json_encode(['status' => false, 'message' => 'User yang sedang login tidak bisa dihapus']);
            return;
        }

        $this->M_user->delete($id);
        echo json_encode(['status' => true, 'message' => 'User berhasil dihapus']);
    }

    public function activity() {
        $filters = [
            'q' => trim($this->input->get('q')),
            'user_id' => (int) $this->input->get('user_id'),
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date'),
            'sort' => strtoupper($this->input->get('sort')) === 'ASC' ? 'ASC' : 'DESC'
        ];
        $page = max(1, (int) $this->input->get('page'));
        $limit = 20;
        $total = $this->M_user_activity->count_filtered($filters);

        $this->render('users/activity', [
            'title' => 'Aktivitas User',
            'active_menu' => 'users',
            'filters' => $filters,
            'users' => $this->M_user_activity->get_users(),
            'activities' => $this->M_user_activity->get_filtered($filters, $limit, ($page - 1) * $limit),
            'page' => $page,
            'total_pages' => max(1, (int) ceil($total / $limit))
        ]);
    }

    public function activity_clear() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $deleted = $this->M_user_activity->delete_older_than_three_months();
        $this->session->set_flashdata('activity_message', $deleted . ' aktivitas lebih dari 3 bulan dihapus.');
        redirect('pengaturan/user/activity');
    }
}
