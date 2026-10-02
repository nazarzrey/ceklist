<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hutang extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_hutang');
        $this->load->model('M_dompet');
        $this->load->model('M_kategori');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        $this->M_hutang->ensure_default_categories($user_id);
        $keyword = trim($this->input->get('q'));
        $tab = $this->input->get('tab') == 'piutang' ? 'piutang' : 'hutang';
        $page = max(1, (int) $this->input->get('page'));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $total_rows = $this->M_hutang->count_all_filtered($user_id, $keyword, $tab);
        
        $data = [
            'title' => 'Hutang & Piutang',
            'active_menu' => 'hutang',
            'hutang' => $this->M_hutang->get_all_paginated($user_id, $limit, $offset, $keyword, $tab),
            'total_hutang' => $this->M_hutang->get_total_sisa_hutang($user_id),
            'total_piutang' => $this->M_hutang->get_total_sisa_piutang($user_id),
            'total_kasbon' => $this->M_hutang->get_total_sisa_kasbon($user_id),
            'pihak_kasbon' => $this->M_hutang->get_frequent_kasbon_parties($user_id),
            'keyword' => $keyword,
            'tab' => $tab,
            'count_hutang' => $this->M_hutang->count_unpaid_filtered($user_id, $keyword, 'hutang'),
            'count_piutang' => $this->M_hutang->count_unpaid_filtered($user_id, $keyword, 'piutang'),
            'page' => $page,
            'total_pages' => max(1, (int) ceil($total_rows / $limit))
            , 'dompet' => $this->M_dompet->get_by_user($user_id)
            , 'kategori_pemasukan' => $this->M_kategori->get_pemasukan($user_id)
            , 'kategori_pengeluaran' => $this->M_kategori->get_pengeluaran($user_id)
            , 'hutang_belum_lunas' => $this->M_hutang->get_unpaid($user_id, $tab)
        ];
        
        $this->render('hutang/index', $data);
    }

    public function data() {
        if (!$this->input->is_ajax_request()) { show_404(); return; }
        $user_id = $this->session->userdata('user_id');
        $tab = $this->input->get('tab') === 'piutang' ? 'piutang' : 'hutang';
        $keyword = trim($this->input->get('q'));
        $page = max(1, (int) $this->input->get('page'));
        $limit = 10;
        $total_rows = $this->M_hutang->count_all_filtered($user_id, $keyword, $tab);
        echo json_encode([
            'status' => true,
            'tab' => $tab,
            'rows' => $this->M_hutang->get_all_paginated($user_id, $limit, ($page - 1) * $limit, $keyword, $tab),
            'total_hutang' => $this->M_hutang->get_total_sisa_hutang($user_id),
            'total_piutang' => $this->M_hutang->get_total_sisa_piutang($user_id),
            'total_kasbon' => $this->M_hutang->get_total_sisa_kasbon($user_id),
            'wallets' => $this->M_dompet->get_by_user($user_id),
            'pihak_kasbon' => $this->M_hutang->get_frequent_kasbon_parties($user_id),
            'count_hutang' => $this->M_hutang->count_unpaid_filtered($user_id, $keyword, 'hutang'),
            'count_piutang' => $this->M_hutang->count_unpaid_filtered($user_id, $keyword, 'piutang'),
            'total_pages' => max(1, (int) ceil($total_rows / $limit))
        ]);
    }

    public function pihak_kasbon() {
        if (!$this->input->is_ajax_request()) { show_404(); return; }
        echo json_encode(['status' => true, 'data' => $this->M_hutang->get_frequent_kasbon_parties($this->session->userdata('user_id'))]);
    }

    public function simpan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $tipe = $this->input->post('tipe') === 'piutang' ? 'piutang' : 'hutang';
        $wallet_id = (int) $this->input->post('wallet_id');
        $is_kasbon = ($tipe === 'hutang' && (int) $this->input->post('is_kasbon') === 1) ? 1 : 0;
        
        $data = [
            'user_id' => $user_id,
            'tipe' => $tipe,
            'is_kasbon' => $is_kasbon,
            'nama_pihak' => $this->input->post('nama_pihak'),
            'jumlah_total' => str_replace(['Rp', '.', ' '], '', $this->input->post('jumlah_total')),
            'sisa' => str_replace(['Rp', '.', ' '], '', $this->input->post('jumlah_total')),
            'jatuh_tempo' => null,
            'keterangan' => $this->input->post('keterangan'),
            'status' => 'belum_lunas',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!$data['nama_pihak']) {
            echo json_encode(['status' => false, 'message' => 'Nama pihak wajib diisi']);
            return;
        }
        if ($is_kasbon && !trim((string) $data['keterangan'])) {
            echo json_encode(['status' => false, 'message' => 'Keterangan wajib diisi untuk Kasbon']);
            return;
        }
        
        if ($tipe === 'piutang' || !$is_kasbon) {
            if (!$wallet_id) {
                echo json_encode(['status' => false, 'message' => 'Dompet wajib dipilih untuk transaksi yang memengaruhi kas']);
                return;
            }
            $result = $this->M_hutang->insert_with_initial_transaction($data, $wallet_id);
            echo json_encode($result);
            return;
        }

        $id = $this->M_hutang->insert($data);
        
        echo json_encode(['status' => true, 'message' => 'Data berhasil disimpan', 'id' => $id]);
    }

    public function edit() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        $old = $this->M_hutang->get_by_id($id, $user_id);
        if (!$old || $old->status === 'lunas') {
            echo json_encode(['status' => false, 'message' => 'Data yang sudah lunas hanya dapat dibaca']);
            return;
        }
        
        $data = [
            'tipe' => $old->tipe,
            'is_kasbon' => (int) ($old->is_kasbon ?? 0),
            'nama_pihak' => $this->input->post('nama_pihak'),
            'jumlah_total' => str_replace(['Rp', '.', ' '], '', $this->input->post('jumlah_total')),
            'jatuh_tempo' => null,
            'keterangan' => $this->input->post('keterangan')
        ];
        
        // Update sisa if total changed
        if ($old && $old->jumlah_total != $data['jumlah_total']) {
            $selisih = $data['jumlah_total'] - $old->jumlah_total;
            $data['sisa'] = $old->sisa + $selisih;
            $data['status'] = ($data['sisa'] <= 0) ? 'lunas' : ($data['sisa'] < $data['jumlah_total'] ? 'cicilan' : 'belum_lunas');
        }
        
        $this->M_hutang->update($id, $data, $user_id);
        $this->M_hutang->sync_initial_transaction($old, $data, $user_id);
        
        echo json_encode(['status' => true, 'message' => 'Data berhasil diupdate']);
    }

    public function hapus() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        $debt = $this->M_hutang->get_by_id($id, $user_id);
        if (!$debt || $debt->status === 'lunas') {
            echo json_encode(['status' => false, 'message' => 'Data yang sudah lunas hanya dapat dibaca']);
            return;
        }
        
        if (!$this->M_hutang->delete($id, $user_id)) {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }
        
        echo json_encode(['status' => true, 'message' => 'Data berhasil dihapus']);
    }

    public function bayar_cicilan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $debt_id = $this->input->post('debt_id');
        $nominal = (float) str_replace(['Rp', '.', ' '], '', $this->input->post('nominal'));
        $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
        $catatan = $this->input->post('catatan');
        $wallet_id = (int) $this->input->post('wallet_id');
        
        // Verify debt belongs to user
        $debt = $this->M_hutang->get_by_id($debt_id, $user_id);
        if (!$debt) {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }
        
        if ($nominal > $debt->sisa) {
            echo json_encode(['status' => false, 'message' => 'Nominal melebihi sisa hutang/piutang']);
            return;
        }
        
        if (!$wallet_id) {
            echo json_encode(['status' => false, 'message' => 'Dompet wajib dipilih']);
            return;
        }

        $result = $this->M_hutang->pay_selected($user_id, [$debt_id => $nominal], $wallet_id, 0, $tanggal, $catatan);
        
        echo json_encode($result);
    }

    /** Bayar beberapa hutang/piutang sekaligus dan buat transaksi jurnalnya. */
    public function bayar_terpilih() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $payments = $this->input->post('payments');
        $payments = is_array($payments) ? $payments : json_decode((string) $payments, true);
        $payments = is_array($payments) ? $payments : [];
        $wallet_id = (int) $this->input->post('wallet_id');
        $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
        $catatan = trim((string) $this->input->post('catatan'));

        if (!$wallet_id || !$payments) {
            echo json_encode(['status' => false, 'message' => 'Pilih data dan dompet terlebih dahulu']);
            return;
        }

        $result = $this->M_hutang->pay_selected($user_id, $payments, $wallet_id, 0, $tanggal, $catatan);
        echo json_encode($result);
    }

    public function get_cicilan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $debt_id = $this->input->post('debt_id');
        
        $cicilan = $this->M_hutang->get_installments($debt_id, $user_id);
        
        echo json_encode(['status' => true, 'data' => $cicilan]);
    }

    public function get_data() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $data = $this->M_hutang->get_by_id($id, $user_id);
        
        if ($data) {
            echo json_encode(['status' => true, 'data' => $data]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
        }
    }
}
