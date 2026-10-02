<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kategori extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_kategori');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        $data = [
            'title' => 'Kelola Kategori',
            'active_menu' => 'kategori',
            'kategori_pemasukan' => $this->M_kategori->get_all($user_id, 'pemasukan', false),
            'kategori_pengeluaran' => $this->M_kategori->get_all($user_id, 'pengeluaran', false)
        ];
        
        $this->render('kategori/index', $data);
    }

    public function simpan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        
        $nama = trim($this->input->post('nama_kategori'));
        $tipe = $this->input->post('tipe');
        $icon = $this->input->post('icon') ?: 'fa-tag';
        $warna = $this->input->post('warna') ?: '#6c5ce7';
        
        if (empty($nama) || empty($tipe)) {
            echo json_encode(['status' => false, 'message' => 'Nama dan tipe kategori wajib diisi']);
            return;
        }
        
        $data = [
            'user_id' => $user_id,
            'nama_kategori' => $nama,
            'tipe' => $tipe,
            'icon' => $icon,
            'warna' => $warna,
            'is_default' => 0
        ];
        
        $this->M_kategori->insert($data);
        echo json_encode(['status' => true, 'message' => 'Kategori berhasil ditambahkan']);
    }

    public function edit() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $data = [
            'nama_kategori' => trim($this->input->post('nama_kategori')),
            'icon' => $this->input->post('icon'),
            'warna' => $this->input->post('warna')
        ];
        
        $this->M_kategori->update($id, $data, $user_id);
        echo json_encode(['status' => true, 'message' => 'Kategori berhasil diupdate']);
    }

    public function hapus() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $result = $this->M_kategori->delete($id, $user_id);
        
        if ($result == -1) {
            echo json_encode(['status' => true, 'message' => 'Kategori sudah dipakai transaksi, jadi hanya dinonaktifkan']);
        } elseif ($result == -2) {
            echo json_encode(['status' => false, 'message' => 'Kategori default tidak bisa dihapus']);
        } else {
            echo json_encode(['status' => true, 'message' => 'Kategori berhasil dihapus']);
        }
    }

    public function get_data() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $data = $this->M_kategori->get_by_id($id, $user_id);
        echo json_encode(['status' => true, 'data' => $data]);
    }

    public function aktifkan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');

        $this->M_kategori->activate($id, $user_id);
        echo json_encode(['status' => true, 'message' => 'Kategori berhasil diaktifkan kembali']);
    }
}
