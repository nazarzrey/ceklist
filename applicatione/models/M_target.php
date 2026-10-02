<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_target extends CI_Model {
    
    private $table = 'jurnal_new_savings_goals'; 
     
    public function get_all($user_id) {
        return $this->db
            ->from($this->table)  // HAPUS $this->$table, ganti $this->table
            ->where('user_id', $user_id)
            ->order_by('deadline', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_id($id, $user_id) {
        return $this->db
            ->from($this->table)  // HAPUS $this->$table
            ->where('id', $id)
            ->where('user_id', $user_id)
            ->get()
            ->row();
    }

    public function get_aktif($user_id) {
        return $this->db
            ->from($this->table)  // HAPUS $this->$table
            ->where('user_id', $user_id)
            ->where('status', 'aktif')
            ->order_by('deadline', 'ASC')
            ->get()
            ->result();
    }

    public function insert($data) {
        $this->db->insert($this->table, $data);  // HAPUS $this->$table
        return $this->db->insert_id();
    }

    public function update($id, $data, $user_id) {
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table, $data);  // HAPUS $this->$table
        return $this->db->affected_rows();
    }

    public function delete($id, $user_id) {
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->delete($this->table);  // HAPUS $this->$table
        return $this->db->affected_rows();
    }

    public function tambah_dana($id, $nominal, $user_id) {
        $this->db->set('terkumpul', "terkumpul + $nominal", FALSE)
            ->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table);  // HAPUS $this->$table
        
        // Check if target tercapai
        $goal = $this->get_by_id($id, $user_id);
        if ($goal && $goal->terkumpul >= $goal->target_nominal) {
            $this->db->where('id', $id)
                ->update($this->table, ['status' => 'tercapai']);  // HAPUS $this->$table
        }
        
        return $this->db->affected_rows();
    }
}