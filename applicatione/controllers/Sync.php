<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_dompet');
        $this->load->model('M_kategori');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        $wallets = $this->M_dompet->get_by_user($user_id);

        $data = [
            'title' => 'Sinkronisasi Saldo',
            'active_menu' => 'sync',
            'wallets' => $wallets,
        ];
        $this->render('sync/index', $data);
    }

    public function calculate() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $wallet_id = (int) $this->input->post('wallet_id');

        if (!$wallet_id) {
            echo json_encode(['status' => false, 'message' => 'ID dompet tidak valid']);
            return;
        }

        $wallet = $this->M_dompet->get_by_id($wallet_id, $user_id);
        if (!$wallet) {
            echo json_encode(['status' => false, 'message' => 'Dompet tidak ditemukan']);
            return;
        }

        $result = $this->calculate_wallet_balance($wallet_id, $user_id);
        $current = (float) ($wallet->saldo_awal ?? 0);
        $calculated = (float) ($result->calculated ?? 0);

        echo json_encode([
            'status' => true,
            'result' => [
                'id' => (int) $wallet->id,
                'nama' => $wallet->nama_wallet,
                'saldo_awal' => $current,
                'pemasukan' => (float) ($result->pemasukan ?? 0),
                'pengeluaran' => (float) ($result->pengeluaran ?? 0),
                'outgoing' => (float) ($result->outgoing ?? 0),
                'transfer_masuk' => (float) ($result->transfer_masuk ?? 0),
                'calculated' => $calculated,
                'diff' => $calculated - $current,
            ],
        ]);
        return;
    }

    private function calculate_wallet_balance($wallet_id, $user_id) {
        $wallet = $this->M_dompet->get_by_id($wallet_id, $user_id);
        if (!$wallet) return (object) ['calculated' => 0.0];

        $tbl = 'jurnal_new_transactions';

        $pemasukan = (float) $this->db->select('SUM(IFNULL(nominal,0)) as nominal', false)
            ->where('user_id', $user_id)
            ->where('wallet_id', $wallet_id)
            ->where('tipe', 'pemasukan')
            ->get($tbl)->row()->nominal ?? 0;
        tracelog("sync 3",vdump(lQ()));

        $pengeluaran = (float) $this->db->select('SUM(IFNULL(nominal,0)) as nominal', false)
            ->where('user_id', $user_id)
            ->where('wallet_id', $wallet_id)
            ->where('tipe', 'pengeluaran')
            ->get($tbl)->row()->nominal ?? 0;
        tracelog("sync 4",vdump(lQ()));

        $virtual_ids = [];
        $vw_rows = $this->db->select('id')
            ->where('user_id', $user_id)
            ->where('is_virtual', 1)
            ->get('jurnal_new_wallets')
            ->result();
        foreach ($vw_rows as $vw) {
            $virtual_ids[] = (int)$vw->id;
        }

        $transfer_keluar = $this->db->select('SUM(IFNULL(nominal,0)) as total_nominal', false)
            ->select('SUM(IFNULL(fee,0)) as total_fee', false)
            ->where('user_id', $user_id)
            ->where('wallet_id', $wallet_id)
            ->where('tipe', 'transfer');
        if (!empty($virtual_ids)) {
            $this->db->where_not_in('wallet_tujuan_id', $virtual_ids);
        }
        $transfer_keluar = $this->db->get($tbl)->row();
        tracelog("sync 5",vdump(lQ()));
        $outgoing = (float) ($transfer_keluar->total_nominal ?? 0)
                  + (float) ($transfer_keluar->total_fee ?? 0);
        tracelog("sync 6",vdump($outgoing));

        $transfer_masuk = $this->db->select('SUM(IFNULL(nominal,0)) as nominal', false)
            ->where('user_id', $user_id)
            ->where('wallet_tujuan_id', $wallet_id)
            ->where('tipe', 'transfer');
        if (!empty($virtual_ids)) {
            $this->db->where_not_in('wallet_id', $virtual_ids);
        }
        $transfer_masuk = (float) $this->db->get($tbl)->row()->nominal ?? 0;
        tracelog("sync 7",vdump(lQ()));

        $sum = ($pemasukan - $pengeluaran - $outgoing) + $transfer_masuk;
        return (object) [
            'calculated' => $sum,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'outgoing' => $outgoing,
            'transfer_masuk' => $transfer_masuk,
        ];
    }

    public function apply_option1() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $wallets = json_decode($this->input->post('wallets'), true);

        if (!$wallets) {
            echo json_encode(['status' => false, 'message' => 'Data tidak valid']);
            return;
        }

        $this->db->trans_start();
        foreach ($wallets as $w) {
            $this->M_dompet->update($w['id'], ['saldo_awal' => $w['calculated']], $user_id);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            echo json_encode(['status' => true, 'message' => 'Saldo berhasil disinkronkan']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal menyimpan']);
        }
    }

    public function apply_option2() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $wallets = json_decode($this->input->post('wallets'), true);

        if (!$wallets) {
            echo json_encode(['status' => false, 'message' => 'Data tidak valid']);
            return;
        }

        // Cari atau buat kategori Sinkronisasi untuk pemasukan dan pengeluaran
        $kat_pemasukan = $this->get_or_create_sync_category($user_id, 'pemasukan');
        $kat_pengeluaran = $this->get_or_create_sync_category($user_id, 'pengeluaran');

        $this->db->trans_start();
        foreach ($wallets as $w) {
            $saldo_awal = (float) ($w['saldo_awal'] ?? 0);
            $calculated = (float) ($w['calculated'] ?? 0);
            $diff = $saldo_awal - $calculated;
            if (abs($diff) < 0.01) continue;

            // Acuan pokok adalah saldo dompet.
            // diff > 0: saldo > transaksi → transaksi kurang → tambah pemasukan
            // diff < 0: saldo < transaksi → transaksi lebih → tambah pengeluaran
            $tipe = $diff > 0 ? 'pemasukan' : 'pengeluaran';
            $kat_id = $diff > 0 ? $kat_pemasukan : $kat_pengeluaran;
            $this->db->insert('jurnal_new_transactions', [
                'user_id' => $user_id,
                'wallet_id' => $w['id'],
                'category_id' => $kat_id,
                'nominal' => abs($diff),
                'tipe' => $tipe,
                'deskripsi' => 'Sinkronisasi saldo',
                'tanggal' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Jangan ubah saldo_awal — acuan tetap wallet
        }
        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            echo json_encode(['status' => true, 'message' => 'Transaksi sinkronisasi berhasil dicatat']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal menyimpan']);
        }
    }

    private function get_or_create_sync_category($user_id, $tipe) {
        $existing = $this->db->where('user_id', $user_id)
            ->where('nama_kategori', 'Sinkronisasi Saldo')
            ->where('tipe', $tipe)
            ->get('jurnal_new_categories')
            ->row();

        if ($existing) {
            return (int) $existing->id;
        }

        $this->db->insert('jurnal_new_categories', [
            'user_id' => $user_id,
            'nama_kategori' => 'Sinkronisasi Saldo',
            'tipe' => $tipe,
            'icon' => 'fa-sync',
            'warna' => '#6366f1',
            'is_active' => 1,
        ]);
        return (int) $this->db->insert_id();
    }
}
