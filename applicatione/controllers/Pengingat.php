<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengingat extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_pengingat');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');

        $data = [
            'title' => 'Pengingat',
            'active_menu' => 'pengingat',
            'pengingat' => $this->M_pengingat->get_all($user_id)
        ];

        $this->render('pengingat/index', $data);
    }

    public function simpan() {
        if (!$this->input->is_ajax_request()) show_404();

        $data = $this->input_data();
        if (!$data['nama'] || !$data['tanggal_mulai']) {
            echo json_encode(['status' => false, 'message' => 'Nama dan tanggal wajib diisi']);
            return;
        }

        $data['user_id'] = $this->session->userdata('user_id');
        $this->M_pengingat->insert($data);
        echo json_encode(['status' => true, 'message' => 'Pengingat berhasil ditambahkan']);
    }

    public function edit() {
        if (!$this->input->is_ajax_request()) show_404();

        $id = $this->input->post('id');
        $data = $this->input_data();
        $this->M_pengingat->update($id, $data, $this->session->userdata('user_id'));
        echo json_encode(['status' => true, 'message' => 'Pengingat berhasil diupdate']);
    }

    public function selesai() {
        if (!$this->input->is_ajax_request()) show_404();

        $km = $this->input->post('km_terakhir');
        $km = ($km === '' || $km === null) ? null : max(0, (int) $km);
        $ok = $this->M_pengingat->mark_done($this->input->post('id'), $this->session->userdata('user_id'), $km);
        echo json_encode(['status' => $ok, 'message' => $ok ? 'Pengingat selesai, jadwal maju ke siklus berikutnya' : 'Data tidak ditemukan']);
    }

    public function hapus() {
        if (!$this->input->is_ajax_request()) show_404();

        $this->M_pengingat->delete($this->input->post('id'), $this->session->userdata('user_id'));
        echo json_encode(['status' => true, 'message' => 'Pengingat berhasil dinonaktifkan']);
    }

    public function get_data() {
        if (!$this->input->is_ajax_request()) show_404();

        $data = $this->M_pengingat->get_by_id($this->input->post('id'), $this->session->userdata('user_id'));
        echo json_encode(['status' => (bool) $data, 'data' => $data]);
    }

    public function due() {
        if (!$this->input->is_ajax_request()) show_404();

        echo json_encode([
            'status' => true,
            'data' => $this->M_pengingat->get_due($this->session->userdata('user_id'))
        ]);
    }

    public function riwayat() {
        if (!$this->input->is_ajax_request()) show_404();

        echo json_encode([
            'status' => true,
            'data' => $this->M_pengingat->get_history($this->input->post('id'), $this->session->userdata('user_id'))
        ]);
    }

    public function riwayat_edit() {
        if (!$this->input->is_ajax_request()) show_404();

        $km = $this->input->post('km_terakhir');
        $km = ($km === '' || $km === null) ? null : max(0, (int) $km);
        $ok = $this->M_pengingat->update_history(
            $this->input->post('id'), $this->session->userdata('user_id'),
            $km, $this->input->post('completed_at')
        );
        echo json_encode(['status' => $ok, 'message' => $ok ? 'Riwayat berhasil diubah' : 'Data riwayat tidak ditemukan']);
    }

    public function riwayat_hapus() {
        if (!$this->input->is_ajax_request()) show_404();

        $ok = $this->M_pengingat->delete_history($this->input->post('id'), $this->session->userdata('user_id'));
        echo json_encode(['status' => $ok, 'message' => $ok ? 'Riwayat berhasil dihapus' : 'Data riwayat tidak ditemukan']);
    }

    private function input_data() {
        return [
            'nama' => trim($this->input->post('nama')),
            'kategori' => in_array($this->input->post('kategori'), ['kendaraan', 'lainnya'], true) ? $this->input->post('kategori') : 'lainnya',
            'km_terakhir' => $this->input->post('km_terakhir') === '' ? null : max(0, (int) $this->input->post('km_terakhir')),
            'catatan' => trim($this->input->post('catatan')),
            'tanggal_mulai' => $this->input->post('tanggal_mulai'),
            'cycle_months' => (int) $this->input->post('cycle_months') ?: 1
        ];
    }
}
