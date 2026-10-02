<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_user extends CI_Model {

    private $table = 'jurnal_new_users';
    private static $columns_checked = false;

    public function __construct() {
        parent::__construct();
        $this->ensure_jurnal_new_columns();
    }

    private function ensure_jurnal_new_columns() {
        if (self::$columns_checked) {
            return;
        }

        $this->load->dbforge();

        foreach ($this->db->list_tables() as $table) {
            if (strpos($table, 'jurnal_new_') === 0 && !$this->db->field_exists('is_active', $table)) {
                $this->dbforge->add_column($table, [
                    'is_active' => [
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 1,
                        'null' => FALSE
                    ]
                ]);
            }
        }

        $theme_columns = [];
        if (!$this->db->field_exists('bg', $this->table)) {
            $theme_columns['bg'] = [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => '#f5f7fb',
                'null' => FALSE
            ];
        }
        if (!$this->db->field_exists('txt', $this->table)) {
            $theme_columns['txt'] = [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => '#1a2a3a',
                'null' => FALSE
            ];
        }
        if (!$this->db->field_exists('last_login', $this->table)) {
            $theme_columns['last_login'] = [
                'type' => 'DATETIME',
                'null' => TRUE
            ];
        }
        if (!$this->db->field_exists('loan_parent', $this->table)) {
            $theme_columns['loan_parent'] = [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ];
        }
        if (!$this->db->field_exists('loan_child', $this->table)) {
            $theme_columns['loan_child'] = [
                'type' => 'TEXT',
                'null' => TRUE
            ];
        }
        if (!$this->db->field_exists('batas_transaksi', $this->table)) {
            $theme_columns['batas_transaksi'] = [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 100000000,
                'null' => FALSE
            ];
        }
        if (!$this->db->field_exists('batas_total_transaksi', $this->table)) {
            $theme_columns['batas_total_transaksi'] = [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 100000000,
                'null' => FALSE
            ];
        }
        if (!$this->db->field_exists('pwa_installed_at', $this->table)) {
            $theme_columns['pwa_installed_at'] = [
                'type' => 'DATETIME',
                'null' => TRUE
            ];
        }
        if ($theme_columns) {
            $this->dbforge->add_column($this->table, $theme_columns);
        }

        self::$columns_checked = true;
    }

    public function get_by_username($username) {
        return $this->db
            ->where('username', $username)
            ->get($this->table)
            ->row();
    }

    public function get_by_email($email) {
        return $this->db
            ->where('email', $email)
            ->get($this->table)
            ->row();
    }

    public function get_by_username_or_email($identifier) {
        return $this->db
            ->group_start()
                ->where('username', $identifier)
                ->or_where('email', $identifier)
            ->group_end()
            ->get($this->table)
            ->row();
    }

    public function get_by_id($id) {
        return $this->db
            ->where('id', $id)
            ->get($this->table)
            ->row();
    }

    public function get_all() {
        return $this->db
            ->order_by('username', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_filtered($keyword = '', $limit = null, $offset = 0) {
        if ($keyword !== '') {
            $this->db->group_start()
                ->like('username', $keyword)
                ->or_like('fullname', $keyword)
                ->or_like('email', $keyword)
                ->or_like('role', $keyword)
            ->group_end();
        }

        $query = $this->db
            ->order_by('role', 'ASC')
            ->order_by('username', 'ASC');
        if ($limit !== null) $query->limit((int) $limit, (int) $offset);
        return $query->get($this->table)->result();
    }

    public function count_filtered($keyword = '') {
        if ($keyword !== '') {
            $this->db->group_start()
                ->like('username', $keyword)
                ->or_like('fullname', $keyword)
                ->or_like('email', $keyword)
                ->or_like('role', $keyword)
            ->group_end();
        }
        return $this->db->count_all_results($this->table);
    }

    public function username_exists($username, $exclude_id = null) {
        $this->db->where('username', $username);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function email_exists($email, $exclude_id = null) {
        if (!$email) {
            return false;
        }

        $this->db->where('email', $email);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function insert($data) {
        // Hash password jika ada
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id)->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function mark_pwa_installed($id) {
        $this->db->where('id', (int) $id)->update($this->table, [
            'pwa_installed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id)->delete($this->table);
        return $this->db->affected_rows();
    }

    public function verify_password($plain_password, $stored_password) {
        return password_verify($plain_password, $stored_password);
    }

    public function update_reset_date($user_id, $reset_date) {
        $this->db->where('id', $user_id)->update($this->table, ['reset_date' => $reset_date]);
        return $this->db->affected_rows();
    }
}
