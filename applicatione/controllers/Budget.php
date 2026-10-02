<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Budget extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_budget');
        $this->load->model('M_kategori');
        $this->load->model('M_transaksi');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        $bulan = $this->input->get('bulan') ?: date('n');
        $tahun = $this->input->get('tahun') ?: date('Y');
        
        $data = [
            'title' => 'Anggaran Bulanan',
            'active_menu' => 'budget',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $this->M_kategori->get_pengeluaran($user_id),
            'budget' => $this->M_budget->get_by_user_bulan($user_id, $bulan, $tahun),
            'realisasi' => $this->get_realisasi($user_id, $bulan, $tahun)
        ];
        
        $this->render('budget/index', $data);
    }

    private function get_realisasi($user_id, $bulan, $tahun) {
        $realisasi = [];
        $start_date = sprintf('%04d-%02d-01', (int) $tahun, (int) $bulan);
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $transaksi = $this->M_transaksi->get_laporan($user_id, $start_date, $end_date);
        
        foreach ($transaksi as $t) {
            if ($t->tipe == 'pengeluaran') {
                if (!isset($realisasi[$t->category_id])) {
                    $realisasi[$t->category_id] = 0;
                }
                $realisasi[$t->category_id] += $t->nominal;
            }
        }
        
        return $realisasi;
    }

    public function simpan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $budgets = $this->input->post('budget');
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');
        
        foreach ($budgets as $category_id => $nominal) {
            $nominal = str_replace(['Rp', '.', ' '], '', $nominal);
            $nominal = empty($nominal) ? 0 : (float)$nominal;
            
            $this->M_budget->set_budget($user_id, $category_id, $bulan, $tahun, $nominal);
        }
        
        echo json_encode(['status' => true, 'message' => 'Anggaran berhasil disimpan']);
    }

    public function reset() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');
        
        $kategori = $this->M_kategori->get_pengeluaran($user_id);
        foreach ($kategori as $k) {
            $this->M_budget->set_budget($user_id, $k->id, $bulan, $tahun, 0);
        }
        
        echo json_encode(['status' => true, 'message' => 'Semua anggaran direset ke 0']);
    }
}
