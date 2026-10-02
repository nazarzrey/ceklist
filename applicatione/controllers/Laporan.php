<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_transaksi');
        $this->load->model('M_kategori');
        $this->load->model('M_dompet');
        $this->load->model('M_hutang');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        $data = [
            'title' => 'Laporan Keuangan',
            'active_menu' => 'laporan',
            'dompet' => $this->M_dompet->get_by_user($user_id),
            'kategori' => $this->M_kategori->get_all($user_id),
            'tanggal_mulai' => date('Y-m-01'),
            'tanggal_akhir' => date('Y-m-d')
        ];
        
        $this->render('laporan/index', $data);
    }

    public function get_data() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_akhir = $this->input->post('tanggal_akhir');
        $dompet_id = $this->input->post('dompet_id');
        $kategori_id = $this->input->post('kategori_id');
        $keyword = trim($this->input->post('q'));
        $page = max(1, (int) $this->input->post('page'));
        $limit = (int) $this->input->post('limit') ?: 10;
        $offset = ($page - 1) * $limit;
        
        $numpang_ids = $this->M_transaksi->get_numpang_kategori_ids();

        $this->db->select('COUNT(*) as total_rows,
                IFNULL(SUM(CASE WHEN t.tipe = "pemasukan" THEN t.nominal ELSE 0 END),0) as total_masuk,
                IFNULL(SUM(CASE WHEN t.tipe = "pengeluaran" THEN t.nominal WHEN t.tipe = "transfer" THEN t.fee ELSE 0 END),0) as total_keluar')
            ->from('jurnal_new_transactions t')
            ->join('jurnal_new_wallets w', 'w.id = t.wallet_id')
            ->join('jurnal_new_wallets wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->join('jurnal_new_categories c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id)
            ->where('t.tanggal >=', $tanggal_mulai)
            ->where('t.tanggal <=', $tanggal_akhir);

        if ($dompet_id) {
            $this->db->group_start()
                ->where('t.wallet_id', $dompet_id)
                ->or_where('t.wallet_tujuan_id', $dompet_id)
            ->group_end();
        }

        if ($kategori_id) {
            $this->db->where('t.category_id', $kategori_id);
        }

        if ($keyword !== '') {
            $this->db->group_start()
                ->like('t.deskripsi', $keyword)
                ->or_like('c.nama_kategori', $keyword)
                ->or_like('w.nama_wallet', $keyword)
                ->or_like('wt.nama_wallet', $keyword)
                ->or_like('t.tipe', $keyword)
            ->group_end();
        }

        $summary = $this->db->get()->row();

        $this->db->select('t.*, w.nama_wallet, wt.nama_wallet as nama_wallet_tujuan, c.nama_kategori, c.icon')
            ->from('jurnal_new_transactions t')
            ->join('jurnal_new_wallets w', 'w.id = t.wallet_id')
            ->join('jurnal_new_wallets wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->join('jurnal_new_categories c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id)
            ->where('t.tanggal >=', $tanggal_mulai)
            ->where('t.tanggal <=', $tanggal_akhir);
        if (!empty($numpang_ids)) $this->db->where_not_in('t.category_id', $numpang_ids);

        if ($dompet_id) {
            $this->db->group_start()
                ->where('t.wallet_id', $dompet_id)
                ->or_where('t.wallet_tujuan_id', $dompet_id)
            ->group_end();
        }

        if ($kategori_id) {
            $this->db->where('t.category_id', $kategori_id);
        }

        if ($keyword !== '') {
            $this->db->group_start()
                ->like('t.deskripsi', $keyword)
                ->or_like('c.nama_kategori', $keyword)
                ->or_like('w.nama_wallet', $keyword)
                ->or_like('wt.nama_wallet', $keyword)
                ->or_like('t.tipe', $keyword)
            ->group_end();
        }

        $transaksi = $this->db->order_by('t.tanggal', 'DESC')
            ->order_by('t.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
        
        // Data untuk chart
        $chart_data = $this->get_chart_data($user_id, $tanggal_mulai, $tanggal_akhir, $dompet_id, $numpang_ids);
        $total_masuk = (float) ($summary->total_masuk ?? 0);
        $total_keluar = (float) ($summary->total_keluar ?? 0);
        $total_rows = (int) ($summary->total_rows ?? 0);
        
        $loan_summary = $this->M_dompet->get_wallet_summary_loan($user_id);
        $virtual_row = $this->db->select('COUNT(*) as cnt, SUM(IFNULL(saldo_awal,0)) as saldo', false)
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->where('is_virtual', 1)
            ->get('jurnal_new_wallets')
            ->row();
        $has_virtual = (int) ($virtual_row->cnt ?? 0) > 0;
        $total_saldo_virtual = (float) ($virtual_row->saldo ?? 0);
        
        // Data bar chart: per-kategori pemasukan & pengeluaran
        $bar_pengeluaran = [];
        $bar_pemasukan = [];
        foreach (['pengeluaran', 'pemasukan'] as $btipe) {
            $q = $this->db->select('c.nama_kategori, SUM(IFNULL(t.nominal,0)) as total')
                ->from('jurnal_new_transactions t')
                ->join('jurnal_new_categories c', 'c.id = t.category_id')
                ->where('t.user_id', $user_id)
                ->where('t.tipe', $btipe)
                ->where('t.tanggal >=', $tanggal_mulai)
                ->where('t.tanggal <=', $tanggal_akhir);
            if (!empty($numpang_ids)) $q->where_not_in('t.category_id', $numpang_ids);
            if ($dompet_id) $q->where('t.wallet_id', $dompet_id);
            $q->group_by('t.category_id')->order_by('total', 'DESC')->limit(20);
            $rows = $q->get()->result();
            if ($btipe === 'pengeluaran') {
                $bar_pengeluaran = $rows;
            } else {
                $bar_pemasukan = $rows;
            }
        }
        
        echo json_encode([
            'status' => true,
            'transaksi' => $transaksi,
            'total_masuk' => $total_masuk,
            'total_keluar' => $total_keluar,
            'saldo_bersih' => $total_masuk - $total_keluar - $total_saldo_virtual,
            'has_virtual' => $has_virtual,
            'virtual_saldo' => $total_saldo_virtual,
            'loan_summary' => $loan_summary,
            'pagination' => [
                'page' => $page,
                'total_pages' => max(1, (int) ceil($total_rows / $limit)),
                'total_rows' => $total_rows
            ],
            'chart' => $chart_data,
            'bar_pengeluaran' => $bar_pengeluaran,
            'bar_pemasukan' => $bar_pemasukan
        ]);
    }

    private function get_chart_data($user_id, $tanggal_mulai, $tanggal_akhir, $dompet_id = null, $numpang_ids = []) {
        // Data untuk pie chart (kategori pengeluaran)
        $this->db->select('c.nama_kategori, SUM(IFNULL(t.nominal,0)) as total')
            ->from('jurnal_new_transactions t')
            ->join('jurnal_new_categories c', 'c.id = t.category_id')
            ->where('t.user_id', $user_id)
            ->where('t.tipe', 'pengeluaran')
            ->where('t.tanggal >=', $tanggal_mulai)
            ->where('t.tanggal <=', $tanggal_akhir);
        if (!empty($numpang_ids)) $this->db->where_not_in('t.category_id', $numpang_ids);
        $this->db->group_by('t.category_id')
            ->order_by('total', 'DESC')
            ->limit(5);
        
        if ($dompet_id) {
            $this->db->where('t.wallet_id', $dompet_id);
        }
        
        $kategori = $this->db->get()->result();
        
        // Data untuk line chart (trend harian)
        $this->db->select('DATE(t.tanggal) as tanggal, 
                IFNULL(SUM(CASE WHEN t.tipe = "pemasukan" THEN t.nominal ELSE 0 END),0) as pemasukan,
                IFNULL(SUM(CASE WHEN t.tipe = "pengeluaran" THEN t.nominal WHEN t.tipe = "transfer" THEN t.fee ELSE 0 END),0) as pengeluaran')
            ->from('jurnal_new_transactions t')
            ->where('t.user_id', $user_id)
            ->where('t.tanggal >=', $tanggal_mulai)
            ->where('t.tanggal <=', $tanggal_akhir);
        if (!empty($numpang_ids)) $this->db->where_not_in('t.category_id', $numpang_ids);
        $this->db->group_by('DATE(t.tanggal)')
            ->order_by('tanggal', 'ASC');
        
        if ($dompet_id) {
            $this->db->where('t.wallet_id', $dompet_id);
        }
        
        $trend = $this->db->get()->result();
        
        return [
            'kategori' => $kategori,
            'trend' => $trend
        ];
    }

    public function export_excel() {
        $user_id = $this->session->userdata('user_id');
        $tanggal_mulai = $this->input->get('tanggal_mulai');
        $tanggal_akhir = $this->input->get('tanggal_akhir');
        
        $this->db->select('t.tanggal, t.deskripsi, c.nama_kategori, w.nama_wallet, wt.nama_wallet as nama_wallet_tujuan, t.tipe, t.nominal, t.fee, t.catatan')
            ->from('jurnal_new_transactions t')
            ->join('jurnal_new_categories c', 'c.id = t.category_id', 'left')
            ->join('jurnal_new_wallets w', 'w.id = t.wallet_id')
            ->join('jurnal_new_wallets wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->where('t.user_id', $user_id)
            ->where('t.tanggal >=', $tanggal_mulai)
            ->where('t.tanggal <=', $tanggal_akhir)
            ->order_by('t.tanggal', 'ASC');
        
        $transaksi = $this->db->get()->result();
        
        // Load library excel (pastikan sudah install phpexcel)
        $this->load->library('excel');
        
        $sheet = $this->excel->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');
        
        // Header
        $sheet->setCellValue('A1', 'Tanggal');
        $sheet->setCellValue('B1', 'Deskripsi');
        $sheet->setCellValue('C1', 'Kategori');
        $sheet->setCellValue('D1', 'Dompet Asal');
        $sheet->setCellValue('E1', 'Dompet Tujuan');
        $sheet->setCellValue('F1', 'Tipe');
        $sheet->setCellValue('G1', 'Nominal');
        $sheet->setCellValue('H1', 'Fee');
        $sheet->setCellValue('I1', 'Catatan');
        
        $row = 2;
        foreach ($transaksi as $t) {
            $sheet->setCellValue('A' . $row, $t->tanggal);
            $sheet->setCellValue('B' . $row, $t->deskripsi);
            $sheet->setCellValue('C' . $row, $t->nama_kategori);
            $sheet->setCellValue('D' . $row, $t->nama_wallet);
            $sheet->setCellValue('E' . $row, $t->nama_wallet_tujuan);
            $sheet->setCellValue('F' . $row, $t->tipe);
            $sheet->setCellValue('G' . $row, $t->nominal);
            $sheet->setCellValue('H' . $row, $t->fee);
            $sheet->setCellValue('I' . $row, $t->catatan);
            $row++;
        }
        
        $filename = 'laporan_keuangan_' . $tanggal_mulai . '_sd_' . $tanggal_akhir . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // $writer = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        // $writer->save('php://output');
    }
}
