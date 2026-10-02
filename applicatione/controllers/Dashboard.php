<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_transaksi');
        $this->load->model('M_dompet');
        $this->load->model('M_hutang');
        $this->load->model('M_budget');
        
        // Debug: cek user_id
        $user_id = $this->session->userdata('user_id');
        log_message('debug', 'Dashboard loaded for user_id: ' . $user_id);
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        // Debug query
        // $this->db->enable_debug_query(TRUE);
        
        // Ambil data dengan error handling
        $total_saldo = $this->M_dompet->get_total_saldo($user_id);
        $saldo_cash = $this->M_dompet->get_total_saldo_cash($user_id);
        $saldo_online = $this->M_dompet->get_total_saldo_online($user_id);
        $pemasukan_bulan_ini = $this->M_transaksi->get_total_by_tipe_bulan_ini($user_id, 'pemasukan');
        $pengeluaran_bulan_ini = $this->M_transaksi->get_total_by_tipe_bulan_ini($user_id, 'pengeluaran');
        $total_hutang = $this->M_hutang->get_total_sisa_hutang($user_id);
        $total_piutang = $this->M_hutang->get_total_sisa_piutang($user_id);
        $loan_summary = $this->M_dompet->get_wallet_summary_loan($user_id);
        // ddbg($loan_summary);
        $transaksi_terbaru = $this->M_transaksi->get_recent($user_id, 10);
        $dompet = $this->M_dompet->get_by_user($user_id);
        $budget_alert = $this->M_budget->get_budget_alert($user_id);
        
        // Debug: cek jumlah data
        log_message('debug', 'Total saldo: ' . $total_saldo);
        log_message('debug', 'Jumlah dompet: ' . count($dompet));
        log_message('debug', 'Jumlah transaksi: ' . count($transaksi_terbaru));
        
        $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard',
            'total_saldo' => $total_saldo,
            'saldo_cash' => $saldo_cash,
            'saldo_online' => $saldo_online,
            'pemasukan_bulan_ini' => $pemasukan_bulan_ini,
            'pengeluaran_bulan_ini' => $pengeluaran_bulan_ini,
            'total_hutang' => $total_hutang,
            'total_piutang' => $total_piutang,
            'loan_summary' => $loan_summary,
            'transaksi_terbaru' => $transaksi_terbaru,
            'dompet' => $dompet,
            'budget_alert' => $budget_alert
        ];
        
        $this->render('dashboard/index', $data);
    }
}
