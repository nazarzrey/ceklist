<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_tracelog extends CI_Model {

    private $table = 'tracelog';

    public function __construct() {
        parent::__construct();
        $this->ensure_table();
    }

    private function ensure_table() {
        if (!$this->db->table_exists($this->table)) {
            $this->load->dbforge();
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ],
                'tanggal' => [
                    'type' => 'DATETIME',
                    'default' => NULL
                ],
                'page' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE
                ],
                'lognya' => [
                    'type' => 'TEXT',
                    'null' => FALSE
                ],
                'ipaddress' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => TRUE
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table($this->table, TRUE);
        }
    }

    public function log($lognya, $page = '') {
        $CI =& get_instance();

        if (empty($page)) {
            $page = $CI->uri->uri_string();
            if (empty($page)) {
                $page = $CI->router->class . '/' . $CI->router->method;
            }
        }

        $data = [
            'tanggal' => date('Y-m-d H:i:s'),
            'page' => $page,
            'lognya' => $lognya,
            'ipaddress' => $CI->input->ip_address(),
            'user_id' => $CI->session->userdata('user_id')
        ];

        return $this->db->insert($this->table, $data);
    }
}
