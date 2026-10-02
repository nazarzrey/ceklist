<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dompet extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_dompet');
        $this->load->model('M_kategori');
        $this->load->model('M_transaksi');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        $data = [
            'title' => 'Dompet Saya',
            'active_menu' => 'dompet',
            'dompet' => $this->M_dompet->get_by_user($user_id, false),
            'total_saldo' => $this->M_dompet->get_total_saldo($user_id)
        ];
        
        $this->render('dompet/index', $data);
    }

    public function simpan() {
        if (!$this->input->is_ajax_request()) show_404();
        
        $user_id = $this->session->userdata('user_id');
        $saldo_awal = (float) str_replace(['Rp', '.', ' '], '', $this->input->post('saldo_awal'));
        
        $wallet_id = $this->M_dompet->insert([
            'user_id' => $user_id,
            'nama_wallet' => $this->input->post('nama_wallet'),
            'icon' => $this->input->post('icon') ?: 'fa-wallet',
            'warna' => $this->input->post('warna') ?: '#2d3e50',
            'cash_count' => ($this->input->post('cash_count') === null || $this->input->post('cash_count') === '') ? 1 : $this->input->post('cash_count'),
            'is_cash' => $this->input->post('is_cash') === '1' ? 1 : 0,
            'is_virtual' => $this->input->post('is_virtual') === '1' ? 1 : 0,
            'urutan' => (int) $this->input->post('urutan') ?: 0
        ]);
        
        if ($saldo_awal > 0) {
            $kategori = $this->db
                ->where('user_id', $user_id)
                ->where('nama_kategori', 'Saldo Awal')
                ->where('tipe', 'pemasukan')
                ->get('jurnal_new_categories')
                ->row();
            
            if (!$kategori) {
                $kat_id = $this->M_kategori->insert([
                    'user_id' => $user_id,
                    'nama_kategori' => 'Saldo Awal',
                    'tipe' => 'pemasukan',
                    'icon' => 'fa-coins',
                    'warna' => '#f59e0b',
                ]);
            } else {
                $kat_id = (int) $kategori->id;
            }
            
            $this->M_transaksi->insert([
                'user_id' => $user_id,
                'wallet_id' => $wallet_id,
                'category_id' => $kat_id,
                'nominal' => $saldo_awal,
                'tipe' => 'pemasukan',
                'deskripsi' => 'Saldo awal ' . $this->input->post('nama_wallet'),
                'tanggal' => date('Y-m-d'),
            ]);
        }
        
        echo json_encode(['status' => true, 'message' => 'Dompet berhasil ditambahkan']);
    }

    public function edit() {
        if (!$this->input->is_ajax_request()) show_404();
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $data = [
            'nama_wallet' => $this->input->post('nama_wallet'),
            'saldo_awal' => str_replace(['Rp', '.', ' '], '', $this->input->post('saldo_awal')),
            'icon' => $this->input->post('icon'),
            'warna' => $this->input->post('warna'),
            'cash_count' => ($this->input->post('cash_count') === null || $this->input->post('cash_count') === '') ? 1: $this->input->post('cash_count'),
            'is_cash' => $this->input->post('is_cash') === '1' ? 1 : 0,
            'is_virtual' => $this->input->post('is_virtual') === '1' ? 1 : 0,
            'urutan' => (int) $this->input->post('urutan') ?: 0
        ];
        
        $this->M_dompet->update($id, $data, $user_id);
        echo json_encode(['status' => true, 'message' => 'Dompet berhasil diupdate']);
    }

    public function hapus() {
        if (!$this->input->is_ajax_request()) show_404();
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $result = $this->M_dompet->delete($id, $user_id);
        
        if ($result == -1) {
            echo json_encode(['status' => true, 'message' => 'Dompet sudah dipakai transaksi, jadi hanya dinonaktifkan']);
        } else {
            echo json_encode(['status' => true, 'message' => 'Dompet berhasil dihapus']);
        }
    }

    public function get_data() {
        if (!$this->input->is_ajax_request()) show_404();
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $data = $this->M_dompet->get_by_id($id, $user_id);
        echo json_encode(['status' => true, 'data' => $data]);
    }

    public function aktifkan() {
        if (!$this->input->is_ajax_request()) show_404();

        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');

        $this->M_dompet->activate($id, $user_id);
        echo json_encode(['status' => true, 'message' => 'Dompet berhasil diaktifkan kembali']);
    }
}
