<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_hutang extends CI_Model {

    private $table = 'jurnal_new_debts';
    private $table_installments = 'jurnal_new_installments';

    public function __construct() {
        parent::__construct();
        $this->ensure_ledger_columns();
    }

    private function ensure_ledger_columns() {
        $this->load->dbforge();
        $columns = [];
        if (!$this->db->field_exists('is_kasbon', $this->table)) {
            $columns['is_kasbon'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE];
        }
        if (!$this->db->field_exists('transaksi_awal_id', $this->table)) {
            $columns['transaksi_awal_id'] = ['type' => 'INT', 'constraint' => 11, 'null' => TRUE];
        }
        if ($columns) $this->dbforge->add_column($this->table, $columns);
        if (!$this->db->field_exists('transaksi_id', $this->table_installments)) {
            $this->dbforge->add_column($this->table_installments, [
                'transaksi_id' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE]
            ]);
        }
    }
    
    public function get_all($user_id) {
        return $this->db
            ->from($this->table)
            ->where('user_id', $user_id)
            ->order_by('jatuh_tempo', 'ASC')
            ->order_by('id', 'DESC')
            ->get()
            ->result();
    }

    private function apply_search($keyword, $prefix = '') {
        if ($keyword !== null && $keyword !== '') {
            $this->db->group_start()
                ->like($prefix . 'nama_pihak', $keyword)
                ->or_like($prefix . 'tipe', $keyword)
                ->or_like($prefix . 'status', $keyword)
                ->or_like($prefix . 'keterangan', $keyword)
            ->group_end();
        }
    }

    public function get_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $limit = null, $offset = null, $keyword = '', $tipe = null) {
        $this->db->from($this->table)
            ->where('user_id', $user_id)
            ->where('DATE(created_at) >=', $this->db->escape($tanggal_mulai), FALSE)
            ->where('DATE(created_at) <=', $this->db->escape($tanggal_akhir), FALSE);

        if ($tipe) {
            $this->db->where('tipe', $tipe);
        }

        $this->apply_search($keyword);

        $this->db->order_by('created_at', 'DESC')
            ->order_by('id', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function count_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $keyword = '', $tipe = null) {
        $this->db->from($this->table)
            ->where('user_id', $user_id)
            ->where('DATE(created_at) >=', $this->db->escape($tanggal_mulai), FALSE)
            ->where('DATE(created_at) <=', $this->db->escape($tanggal_akhir), FALSE);

        if ($tipe) {
            $this->db->where('tipe', $tipe);
        }

        $this->apply_search($keyword);

        return $this->db->count_all_results();
    }

    public function get_all_paginated($user_id, $limit, $offset, $keyword = '', $tipe = null) {
        $this->db->select('d.*, lp.tanggal_pelunasan')
            ->from($this->table . ' d')
            ->join('(SELECT debt_id, MAX(tanggal_bayar) AS tanggal_pelunasan FROM ' . $this->table_installments . ' GROUP BY debt_id) lp', 'lp.debt_id = d.id', 'left', false)
            ->where('d.user_id', $user_id);
        if ($tipe) $this->db->where('d.tipe', $tipe);
        $this->apply_search($keyword, 'd.');
        return $this->db->order_by('d.created_at', 'DESC')->order_by('d.id', 'DESC')
            ->limit($limit, $offset)->get()->result();
    }

    public function count_all_filtered($user_id, $keyword = '', $tipe = null) {
        $this->db->from($this->table)->where('user_id', $user_id);
        if ($tipe) $this->db->where('tipe', $tipe);
        $this->apply_search($keyword);
        return $this->db->count_all_results();
    }

    public function count_unpaid_filtered($user_id, $keyword = '', $tipe = null) {
        $this->db->from($this->table)
            ->where('user_id', $user_id)
            ->where('status !=', 'lunas')
            ->where('sisa >', 0);
        if ($tipe) $this->db->where('tipe', $tipe);
        $this->apply_search($keyword);
        return $this->db->count_all_results();
    }

    public function get_by_id($id, $user_id) {
        return $this->db
            ->from($this->table)
            ->where('id', $id)
            ->where('user_id', $user_id)
            ->get()
            ->row();
    }

    public function get_unpaid($user_id, $tipe = 'hutang') {
        return $this->db->from($this->table)
            ->where('user_id', $user_id)
            ->where('tipe', $tipe)
            ->where('status !=', 'lunas')
            ->where('sisa >', 0)
            ->order_by('jatuh_tempo', 'ASC')
            ->order_by('id', 'ASC')
            ->get()->result();
    }

    public function get_total_sisa_hutang($user_id) {
        $result = $this->db->select('SUM(IFNULL(sisa,0)) as sisa', false)
            ->from($this->table)
            ->where('user_id', $user_id)
            ->where('tipe', 'hutang')
            ->where('status !=', 'lunas')
            ->get()
            ->row();
        
        return $result->sisa ?? 0;
    }

    public function get_total_sisa_piutang($user_id) {
        $result = $this->db->select('SUM(IFNULL(sisa,0)) as sisa', false)        
            ->from($this->table)
            ->where('user_id', $user_id)
            ->where('tipe', 'piutang')
            ->where('status !=', 'lunas')
            ->get()
            ->row();
        
        return $result->sisa ?? 0;
    }

    public function get_total_sisa_kasbon($user_id) {
        $result = $this->db->select('SUM(IFNULL(sisa,0)) as sisa', false)
            ->from($this->table)->where('user_id', $user_id)->where('tipe', 'hutang')
            ->where('is_kasbon', 1)->where('status !=', 'lunas')->get()->row();
        return $result->sisa ?? 0;
    }

    public function get_frequent_kasbon_parties($user_id, $limit = 100) {
        return $this->db->select('nama_pihak, COUNT(*) as jumlah', false)
            ->from($this->table)->where('user_id', $user_id)->where('tipe', 'hutang')
            ->where('is_kasbon', 1)->where('nama_pihak !=', '')
            ->group_by('nama_pihak')->order_by('jumlah', 'DESC')->order_by('nama_pihak', 'ASC')
            ->limit($limit)->get()->result();
    }

    public function insert($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    private function get_or_create_category($user_id, $name, $tipe) {
        $category = $this->db->group_start()
            ->where('user_id', (int) $user_id)->or_where('user_id', 0)
            ->group_end()->where('nama_kategori', $name)->where('tipe', $tipe)
            ->where('is_active', 1)->order_by('user_id', 'DESC')->get('jurnal_new_categories')->row();
        if ($category) return (int) $category->id;

        $this->db->insert('jurnal_new_categories', [
            'user_id' => 0,
            'nama_kategori' => $name,
            'tipe' => $tipe,
            'icon' => 'fa-hand-holding-dollar',
            'warna' => $tipe === 'pemasukan' ? '#16a34a' : '#dc2626',
            'is_default' => 1,
            'is_active' => 1
        ]);
        return (int) $this->db->insert_id();
    }

    public function get_default_payment_category_id($user_id, $tipe) {
        return $this->get_or_create_category(
            $user_id,
            $tipe === 'hutang' ? 'Hutang' : 'Piutang',
            $tipe === 'hutang' ? 'pengeluaran' : 'pemasukan'
        );
    }

    public function get_default_receivable_category_id($user_id) {
        return $this->get_or_create_category($user_id, 'Pemberian Piutang', 'pengeluaran');
    }

    public function get_default_debt_category_id($user_id) {
        return $this->get_or_create_category($user_id, 'Penerimaan Hutang', 'pemasukan');
    }

    public function ensure_default_categories($user_id) {
        $this->get_default_payment_category_id($user_id, 'hutang');
        $this->get_default_payment_category_id($user_id, 'piutang');
        $this->get_default_receivable_category_id($user_id);
        $this->get_default_debt_category_id($user_id);
    }

    public function insert_with_initial_transaction($data, $wallet_id) {
        $this->load->model('M_transaksi');
        $wallet = $this->db->where('id', (int) $wallet_id)->where('user_id', (int) $data['user_id'])->get('jurnal_new_wallets')->row();
        if (!$wallet) return ['status' => false, 'message' => 'Dompet tidak ditemukan'];
        $is_piutang = $data['tipe'] === 'piutang';
        if ($is_piutang && (float) $data['jumlah_total'] > (float) $wallet->saldo_awal) {
            return ['status' => false, 'message' => 'Saldo dompet tidak cukup untuk memberikan piutang'];
        }
        $category_id = $is_piutang ? $this->get_default_receivable_category_id($data['user_id']) : $this->get_default_debt_category_id($data['user_id']);
        $this->db->trans_start();
        $this->db->insert($this->table, $data);
        $debt_id = $this->db->insert_id();
        $transaction_id = $this->M_transaksi->insert([
            'user_id' => $data['user_id'], 'wallet_id' => (int) $wallet_id,
            'category_id' => $category_id, 'wallet_tujuan_id' => null,
            'nominal' => (float) $data['jumlah_total'], 'fee' => 0,
            'tipe' => $is_piutang ? 'pengeluaran' : 'pemasukan',
            'deskripsi' => ($is_piutang ? 'Pemberian piutang: ' : 'Penerimaan hutang: ') . $data['nama_pihak'],
            'tanggal' => date('Y-m-d'), 'catatan' => $data['keterangan'],
            'is_loan' => 0, 'partner_id' => null
        ]);
        $this->db->where('id', $debt_id)->update($this->table, ['transaksi_awal_id' => $transaction_id]);
        $this->db->trans_complete();
        return ['status' => $this->db->trans_status(), 'id' => $debt_id, 'message' => $this->db->trans_status() ? 'Data dan transaksi kas berhasil dicatat' : 'Gagal mencatat data'];
    }

    public function sync_initial_transaction($debt, $data, $user_id) {
        if (empty($debt->transaksi_awal_id) || (int) $debt->is_kasbon === 1) return;
        $this->load->model('M_transaksi');
        $this->M_transaksi->update((int) $debt->transaksi_awal_id, [
            'nominal' => (float) $data['jumlah_total'],
            'deskripsi' => ($debt->tipe === 'piutang' ? 'Pemberian piutang: ' : 'Penerimaan hutang: ') . $data['nama_pihak'],
            'catatan' => $data['keterangan']
        ], $user_id);
    }

    public function insert_with_receivable_transaction($data, $wallet_id, $category_id) {
        $this->load->model('M_transaksi');
        $wallet = $this->db->where('id', (int) $wallet_id)->where('user_id', (int) $data['user_id'])->get('jurnal_new_wallets')->row();
        if (!$wallet) return ['status' => false, 'message' => 'Dompet piutang tidak ditemukan'];
        if ((float) $data['jumlah_total'] > (float) $wallet->saldo_awal) {
            return ['status' => false, 'message' => 'Saldo dompet tidak cukup untuk memberikan piutang'];
        }

        $category_id = $this->get_default_receivable_category_id($data['user_id']);
        $category = $this->db->where('id', $category_id)->where('tipe', 'pengeluaran')->where('is_active', 1)
            ->get('jurnal_new_categories')->row();
        if (!$category) return ['status' => false, 'message' => 'Kategori piutang harus bertipe pengeluaran'];

        $this->db->trans_start();
        $this->db->insert($this->table, $data);
        $debt_id = $this->db->insert_id();
        $this->M_transaksi->insert([
            'user_id' => $data['user_id'],
            'wallet_id' => (int) $wallet_id,
            'category_id' => (int) $category_id,
            'wallet_tujuan_id' => null,
            'nominal' => (float) $data['jumlah_total'],
            'fee' => 0,
            'tipe' => 'pengeluaran',
            'deskripsi' => 'Pemberian piutang: ' . $data['nama_pihak'],
            'tanggal' => date('Y-m-d'),
            'catatan' => $data['keterangan'],
            'is_loan' => 0,
            'partner_id' => null
        ]);
        $this->db->trans_complete();

        return ['status' => $this->db->trans_status(), 'id' => $debt_id, 'message' => $this->db->trans_status() ? 'Piutang dan pengeluaran berhasil dicatat' : 'Gagal mencatat piutang'];
    }

    public function update($id, $data, $user_id) {
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id, $user_id) {
        $debt = $this->get_by_id($id, $user_id);
        if (!$debt) return 0;

        $this->load->model('M_transaksi');
        $installments = $this->db->select('transaksi_id')->where('debt_id', $id)->get($this->table_installments)->result();
        $this->db->trans_start();
        foreach ($installments as $installment) {
            if (!empty($installment->transaksi_id)) {
                $this->M_transaksi->delete((int) $installment->transaksi_id, $user_id);
            }
        }
        if (!empty($debt->transaksi_awal_id)) {
            $this->M_transaksi->delete((int) $debt->transaksi_awal_id, $user_id);
        }
        $this->db->where('debt_id', $id)->delete($this->table_installments);
        $this->db->where('id', $id)->where('user_id', $user_id)->delete($this->table);
        $this->db->trans_complete();
        return $this->db->trans_status() ? 1 : 0;
    }

    public function get_installments($debt_id, $user_id = null) {
        $this->db->select('i.*')
            ->from($this->table_installments . ' i')
            ->join($this->table . ' d', 'd.id = i.debt_id')
            ->where('i.debt_id', $debt_id);
        
        if ($user_id) {
            $this->db->where('d.user_id', $user_id);
        }
        
        return $this->db->order_by('i.tanggal_bayar', 'DESC')->get()->result();
    }

    public function add_installment($debt_id, $nominal, $tanggal, $catatan = null) {
        // Get current debt
        $debt = $this->db->where('id', $debt_id)->get($this->table)->row();
        
        if (!$debt) return false;
        
        $sisa_baru = $debt->sisa - $nominal;
        $status = ($sisa_baru <= 0) ? 'lunas' : 'cicilan';
        
        // Start transaction
        $this->db->trans_start();
        
        // Insert installment
        $this->db->insert($this->table_installments, [
            'debt_id' => $debt_id,
            'nominal_bayar' => $nominal,
            'tanggal_bayar' => $tanggal,
            'catatan' => $catatan
        ]);
        
        // Update debt
        $this->db->where('id', $debt_id)->update($this->table, [
            'sisa' => max(0, $sisa_baru),
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    public function pay_selected($user_id, $payments, $wallet_id, $category_id, $tanggal, $catatan = '') {
        $this->load->model('M_transaksi');
        $wallet = $this->db->where('id', $wallet_id)->where('user_id', $user_id)->get('jurnal_new_wallets')->row();
        if (!$wallet) return ['status' => false, 'message' => 'Dompet tidak ditemukan'];

        $rows = [];
        foreach ($payments as $debt_id => $nominal) {
            $debt = $this->get_by_id((int) $debt_id, $user_id);
            $nominal = (float) str_replace(['Rp', '.', ' ', ','], ['', '', '', ''], (string) $nominal);
            if (!$debt || $debt->status === 'lunas' || $nominal <= 0 || $nominal > (float) $debt->sisa) {
                return ['status' => false, 'message' => 'Data pelunasan tidak valid atau melebihi sisa hutang'];
            }
            $rows[] = [$debt, $nominal];
        }

        $types = array_unique(array_map(function ($row) { return $row[0]->tipe; }, $rows));
        if (count($types) !== 1) {
            return ['status' => false, 'message' => 'Hutang dan piutang harus diproses terpisah'];
        }

        $total_payment = array_sum(array_map(function ($row) { return $row[1]; }, $rows));
        if ($types[0] === 'hutang' && $total_payment > (float) $wallet->saldo_awal) {
            return ['status' => false, 'message' => 'Saldo dompet tidak cukup untuk membayar hutang'];
        }

        $category_id = $this->get_default_payment_category_id($user_id, $types[0]);
        $category = $this->db->where('id', $category_id)->where('is_active', 1)
            ->get('jurnal_new_categories')->row();
        if (!$category) return ['status' => false, 'message' => 'Kategori jurnal tidak ditemukan'];
        $expected_type = $rows[0][0]->tipe === 'hutang' ? 'pengeluaran' : 'pemasukan';
        if ($category->tipe !== $expected_type) return ['status' => false, 'message' => 'Kategori tidak sesuai dengan tipe pelunasan'];

        $this->db->trans_start();
        foreach ($rows as [$debt, $nominal]) {
            $sisa_baru = max(0, (float) $debt->sisa - $nominal);
            $transaction_id = $this->M_transaksi->insert([
                'user_id' => $user_id,
                'wallet_id' => $wallet_id,
                'category_id' => $category_id,
                'wallet_tujuan_id' => null,
                'nominal' => $nominal,
                'fee' => 0,
                'tipe' => $debt->tipe === 'hutang' ? 'pengeluaran' : 'pemasukan',
                'deskripsi' => ($debt->tipe === 'hutang' ? 'Pelunasan hutang: ' : 'Penerimaan piutang: ') . $debt->nama_pihak,
                'tanggal' => $tanggal,
                'catatan' => $catatan,
                'is_loan' => 0,
                'partner_id' => null
            ]);
            $this->db->insert($this->table_installments, [
                'debt_id' => $debt->id,
                'nominal_bayar' => $nominal,
                'tanggal_bayar' => $tanggal,
                'catatan' => $catatan,
                'transaksi_id' => $transaction_id
            ]);
            $this->db->where('id', $debt->id)->where('user_id', $user_id)->update($this->table, [
                'sisa' => $sisa_baru,
                'status' => $sisa_baru <= 0 ? 'lunas' : 'cicilan',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        $this->db->trans_complete();
        return ['status' => $this->db->trans_status(), 'message' => $this->db->trans_status() ? 'Pelunasan berhasil dicatat ke jurnal' : 'Gagal memproses pelunasan'];
    }

    public function get_jatuh_tempo_terdekat($user_id, $limit = 5) {
        $today = date('Y-m-d');
        $next_month = date('Y-m-d', strtotime('+30 days'));
        
        return $this->db
            ->from($this->table)
            ->where('user_id', $user_id)
            ->where('status !=', 'lunas')
            ->where('jatuh_tempo >=', $today)
            ->where('jatuh_tempo <=', $next_month)
            ->order_by('jatuh_tempo', 'ASC')
            ->limit($limit)
            ->get()
            ->result();
    }
}
