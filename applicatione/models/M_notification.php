<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_notification extends CI_Model {

    private $table = 'jurnal_new_notifications';

    public function __construct() {
        parent::__construct();
    }

    public function add($user_id, $type, $title, $message, $related_id = null, $data = null) {
        $notif = [
            'user_id' => $user_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_id' => $related_id,
            'data' => $data ? json_encode($data) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert($this->table, $notif);
        return $this->db->insert_id();
    }

    public function get_unread($user_id, $limit = 20) {
        return $this->db->where('user_id', $user_id)
            ->where('is_read', 0)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get($this->table)
            ->result();
    }

    public function get_all($user_id, $limit = 50, $offset = 0) {
        return $this->db->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get($this->table)
            ->result();
    }

    public function count_unread($user_id) {
        return $this->db->where('user_id', $user_id)
            ->where('is_read', 0)
            ->count_all_results($this->table);
    }

    public function mark_as_read($id, $user_id) {
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table, ['is_read' => 1]);
        return $this->db->affected_rows();
    }

    public function mark_all_read($user_id) {
        $this->db->where('user_id', $user_id)
            ->update($this->table, ['is_read' => 1]);
        return $this->db->affected_rows();
    }
}