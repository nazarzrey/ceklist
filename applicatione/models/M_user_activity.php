<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_user_activity extends CI_Model {

    private $table = 'jurnal_new_user_activity';

    public function __construct() {
        parent::__construct();
        $this->ensure_table();
    }

    private function ensure_table() {
        if ($this->db->table_exists($this->table)) return;

        $this->load->dbforge();
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'null' => FALSE],
            'page_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => FALSE],
            'page_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => FALSE],
            'http_method' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => FALSE],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => TRUE],
            'user_agent' => ['type' => 'TEXT', 'null' => TRUE],
            'accessed_at' => ['type' => 'DATETIME', 'null' => FALSE]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key(['user_id', 'accessed_at']);
        $this->dbforge->create_table($this->table, TRUE);
    }

    public function log_page($user_id, $page_url, $page_name, $method = 'GET', $ip = null, $agent = null) {
        return $this->db->insert($this->table, [
            'user_id' => (int) $user_id,
            'page_url' => substr((string) $page_url, 0, 255),
            'page_name' => substr((string) $page_name, 0, 150),
            'http_method' => substr((string) $method, 0, 10),
            'ip_address' => $ip,
            'user_agent' => $agent,
            'accessed_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function get_users() {
        return $this->db->select('id, username, fullname')->order_by('username', 'ASC')->get('jurnal_new_users')->result();
    }

    private function apply_filters($filters) {
        $this->db->join('jurnal_new_users u', 'u.id = a.user_id', 'left');
        if (!empty($filters['user_id'])) $this->db->where('a.user_id', (int) $filters['user_id']);
        if (!empty($filters['q'])) {
            $this->db->group_start()->like('a.page_name', $filters['q'])->or_like('a.page_url', $filters['q'])->or_like('u.username', $filters['q'])->or_like('u.fullname', $filters['q'])->group_end();
        }
        if (!empty($filters['start_date'])) $this->db->where('a.accessed_at >=', $filters['start_date'] . ' 00:00:00');
        if (!empty($filters['end_date'])) $this->db->where('a.accessed_at <=', $filters['end_date'] . ' 23:59:59');
    }

    public function count_filtered($filters) {
        $this->apply_filters($filters);
        return $this->db->count_all_results($this->table . ' a');
    }

    public function get_filtered($filters, $limit, $offset) {
        $this->apply_filters($filters);
        $direction = strtoupper($filters['sort'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        return $this->db->select('a.*, u.username, u.fullname')
            ->order_by('a.accessed_at', $direction)->order_by('a.id', $direction)
            ->limit((int) $limit, (int) $offset)->get($this->table . ' a')->result();
    }

    public function delete_older_than_three_months() {
        $this->db->where('accessed_at <', date('Y-m-d H:i:s', strtotime('-3 months')))->delete($this->table);
        return $this->db->affected_rows();
    }
}
