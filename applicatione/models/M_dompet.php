<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_dompet extends CI_Model {

    private $table = 'jurnal_new_wallets';
    private $table_categories = 'jurnal_new_categories';

    public function __construct() {
        parent::__construct();
        $this->ensure_is_active_column();
        $this->ensure_cash_count_column();
        $this->ensure_is_cash_column();
        $this->ensure_is_virtual_column();
        $this->ensure_urutan_column();
    }

    private function ensure_is_active_column() {
        if (!$this->db->field_exists('is_active', $this->table)) {
            $this->load->dbforge();
            $this->dbforge->add_column($this->table, [
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                    'null' => FALSE
                ]
            ]);
        }
    }

    private function ensure_cash_count_column() {
        if (!$this->db->field_exists('cash_count', $this->table)) {
            $this->load->dbforge();
            $this->dbforge->add_column($this->table, [
                'cash_count' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                    'null' => FALSE
                ]
            ]);
        }
    }

    public function get_by_user($user_id, $active_only = true) {
        $this->db->where('user_id', $user_id);

        if ($active_only) {
            $this->db->where('is_active', 1);
        }

        return $this->db->order_by('is_active', 'DESC')
            ->order_by('urutan', 'ASC')
            ->order_by('nama_wallet', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id, $user_id) {
        return $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->get($this->table)
            ->row();
    }

    public function get_total_saldo($user_id) {
        $plus = (float) $this->db->select('SUM(IFNULL(saldo_awal,0)) as saldo', false)
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->where('cash_count', 1)
            ->where('is_virtual', 0)
            ->get($this->table)
            ->row()
            ->saldo ?? 0;
        $minus = (float) $this->db->select('SUM(IFNULL(saldo_awal,0)) as saldo', false)
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->where('is_virtual', 1)
            ->get($this->table)
            ->row()
            ->saldo ?? 0;
        return $plus - $minus;
    }

    private function ensure_is_cash_column() {
        if (!$this->db->field_exists('is_cash', $this->table)) {
            $this->load->dbforge();
            $this->dbforge->add_column($this->table, [
                'is_cash' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE
                ]
            ]);
        }
    }

    private function ensure_is_virtual_column() {
        if (!$this->db->field_exists('is_virtual', $this->table)) {
            $this->load->dbforge();
            $this->dbforge->add_column($this->table, [
                'is_virtual' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE
                ]
            ]);
        }
    }

    private function ensure_urutan_column() {
        if (!$this->db->field_exists('urutan', $this->table)) {
            $this->load->dbforge();
            $this->dbforge->add_column($this->table, [
                'urutan' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                    'null' => FALSE
                ]
            ]);
        }
    }

    public function get_total_saldo_cash($user_id) {
        $plus = (float) $this->db->select('SUM(IFNULL(saldo_awal,0)) as saldo', false)
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->where('is_cash', 1)
            ->where('cash_count', 1)
            ->where('is_virtual', 0)
            ->get($this->table)
            ->row()
            ->saldo ?? 0;
        $minus = (float) $this->db->select('SUM(IFNULL(saldo_awal,0)) as saldo', false)
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->where('is_cash', 1)
            ->where('is_virtual', 1)
            ->get($this->table)
            ->row()
            ->saldo ?? 0;
        return $plus - $minus;
    }

    public function get_total_saldo_online($user_id) {
        $plus = (float) $this->db->select('SUM(IFNULL(saldo_awal,0)) as saldo', false)
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->where('is_cash', 0)
            ->where('cash_count', 1)
            ->where('is_virtual', 0)
            ->get($this->table)
            ->row()
            ->saldo ?? 0;
        $minus = (float) $this->db->select('SUM(IFNULL(saldo_awal,0)) as saldo', false)
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->where('is_cash', 0)
            ->where('is_virtual', 1)
            ->get($this->table)
            ->row()
            ->saldo ?? 0;
        return $plus - $minus;
    }

    public function insert($data) {
        $data['is_active'] = 1;
        $data['cash_count'] = $data['cash_count'] ?? 1;
        $data['is_cash'] = $data['is_cash'] ?? 0;
        $data['is_virtual'] = $data['is_virtual'] ?? 0;
        $data['urutan'] = $data['urutan'] ?? 0;
        $data['saldo_awal'] = 0;
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function ensure_default_wallets($user_id) {
        $defaults = [
            ['nama_wallet' => 'Cash', 'icon' => 'fa-money-bill-wave', 'warna' => '#27ae60', 'is_cash' => 1, 'urutan' => 1],
            ['nama_wallet' => 'Bank', 'icon' => 'fa-university', 'warna' => '#2563eb', 'is_cash' => 0, 'urutan' => 2]
        ];

        foreach ($defaults as $wallet) {
            $exists = $this->db
                ->where('user_id', (int) $user_id)
                ->where('nama_wallet', $wallet['nama_wallet'])
                ->count_all_results($this->table) > 0;
            if (!$exists) {
                $this->insert(array_merge($wallet, [
                    'user_id' => (int) $user_id,
                    'cash_count' => 1,
                    'is_virtual' => 0
                ]));
            }
        }
    }

    public function update($id, $data, $user_id) {
        $this->db->where('id', $id)->where('user_id', $user_id)->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id, $user_id) {
        // Check if wallet has transactions
        $count = $this->db->group_start()
                ->where('wallet_id', $id)
                ->or_where('wallet_tujuan_id', $id)
            ->group_end()
            ->count_all_results('jurnal_new_transactions');
        if ($count > 0) {
            $this->db->where('id', $id)
                ->where('user_id', $user_id)
                ->update($this->table, ['is_active' => 0]);
            return -1; // Dipakai transaksi, hanya dinonaktifkan
        }
        
        $this->db->where('id', $id)->where('user_id', $user_id)->delete($this->table);
        return $this->db->affected_rows();
    }

    public function activate($id, $user_id) {
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table, ['is_active' => 1]);

        return $this->db->affected_rows();
    }
    // Tambahkan method ini untuk ambil dompet multi user (untuk payment)

    public function get_wallets_by_user_ids($user_ids) {
        if (empty($user_ids)) return [];
        
        $this->db->where_in('user_id', $user_ids)
            ->where('is_active', 1)
            ->order_by('user_id', 'ASC')
            ->order_by('nama_wallet', 'ASC');
        
        return $this->db->get($this->table)->result();
    }

    private function get_loan_payment_category_id() {
        $existing = $this->db->where($this->table_categories . '.user_id', 0)
            ->where('nama_kategori', 'Pembayaran Kasbon')
            ->get($this->table_categories)
            ->row();

        if ($existing) {
            return (int) $existing->id;
        }

        $data = [
            'user_id' => 0,
            'nama_kategori' => 'Pembayaran Kasbon',
            'tipe' => 'pemasukan',
            'icon' => 'fa-hand-holding-usd',
            'warna' => '#27ae60',
            'is_default' => 1,
            'is_active' => 1
        ];
        $this->db->insert($this->table_categories, $data);
        return (int) $this->db->insert_id();
    }

    public function get_wallet_summary_loan($user_id) {
        $CI =& get_instance();
        $CI->load->model('M_loan');


        $summary = $CI->M_loan->get_loan_summary($user_id);
        return (object) [
            'total_loan_out' => (float) ($summary->total_loan_out ?? 0),
            'total_loan_in' => (float) ($summary->total_loan_in ?? 0),
            'net_loan' => (float) ($summary->net_loan ?? 0),
            'remaining' => (float) ($summary->remaining ?? $summary->net_loan ?? 0)
        ];
    }
}
