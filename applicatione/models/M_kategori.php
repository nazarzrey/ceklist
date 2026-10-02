<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_kategori extends CI_Model {

    private $table = 'jurnal_new_categories';

    public function __construct() {
        parent::__construct();
        $this->ensure_is_active_column();
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

    public function get_all($user_id, $tipe = null, $active_only = true) {
        $this->db->group_start()
                ->where('user_id', $user_id)
                ->or_where('user_id', 0)
            ->group_end();

        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        
        if ($tipe) {
            $this->db->where('tipe', $tipe);
        }
        
        return $this->db->order_by('is_active', 'DESC')
            ->order_by('is_default', 'ASC')
            ->order_by('nama_kategori', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id, $user_id) {
        return $this->db
            ->where('id', $id)
            ->group_start()
                ->where('user_id', $user_id)
                ->or_where('user_id', 0)
            ->group_end()
            ->get($this->table)
            ->row();
    }

    public function insert($data) {
        $data['is_active'] = 1;
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data, $user_id) {
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id, $user_id) {
        // Cek apakah kategori default
        $kategori = $this->get_by_id($id, $user_id);
        if ($kategori && $kategori->is_default == 1) {
            return -2; // Tidak bisa hapus kategori default
        }
        
        // Cek apakah ada transaksi
        $count = $this->db->where('category_id', $id)->count_all_results('jurnal_new_transactions');
        if ($count > 0) {
            $this->db->where('id', $id)
                ->where('user_id', $user_id)
                ->update($this->table, ['is_active' => 0]);
            return -1;
        }
        
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->delete($this->table);
        return $this->db->affected_rows();
    }

    public function activate($id, $user_id) {
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table, ['is_active' => 1]);

        return $this->db->affected_rows();
    }

    public function get_pemasukan($user_id) {
        return $this->get_all($user_id, 'pemasukan');
    }

    public function get_pengeluaran($user_id) {
        return $this->get_all($user_id, 'pengeluaran');
    }
}
