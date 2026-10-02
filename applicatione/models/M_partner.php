<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_partner extends CI_Model {

    private $table = 'jurnal_new_partners';

    public function __construct() {
        parent::__construct();
    }
    public function send_request($user_parents, $user_child, $role_a_to_b = null, $role_b_to_a = null, $loan_relation_type = null) {
      // Cek apakah sudah ada request
      $existing = $this->get_existing_request($user_parents, $user_child);
      $loan_relation_type = in_array($loan_relation_type, ['parent', 'child'], true) ? $loan_relation_type : null;
      
      if ($existing) {
          // Jika status rejected, update jadi pending
          if ($existing->status == 'rejected') {
              $this->db->where('id', $existing->id)->update($this->table, [
                  'status' => 'pending',
                  'role_a_to_b' => $role_a_to_b,
                  'role_b_to_a' => $role_b_to_a,
                  'loan_relation_type' => $loan_relation_type,
                  'updated_at' => date('Y-m-d H:i:s'),
                  'rejected_at' => NULL
              ]);
              return $existing->id;
          }
          
          // Jika status approved atau pending, tidak boleh
          return false;
      }
      
      // Buat request baru
      $data = [
          'user_parents' => $user_parents,
          'user_child' => $user_child,
          'status' => 'pending',
          'role_a_to_b' => $role_a_to_b,
          'role_b_to_a' => $role_b_to_a,
          'loan_relation_type' => $loan_relation_type,
          'created_at' => date('Y-m-d H:i:s')
      ];
      
      $this->db->insert($this->table, $data);
      return $this->db->insert_id();
  }
    public function approve_request($id, $user_id) {
        // Only user_b can approve
        $partner = $this->get_by_id($id);
        if (!$partner || $partner->user_child != $user_id) {
            return false;
        }

        $this->db->where('id', $id)->update($this->table, [
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        return true;
    }

    public function reject_request($id, $user_id) {
        $partner = $this->get_by_id($id);
        if (!$partner || $partner->user_child != $user_id) {
            return false;
        }

        $this->db->where('id', $id)->update($this->table, [
            'status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s')
        ]);
        return true;
    }

    public function get_by_id($id) {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function get_pending_requests($user_id) {
        return $this->db
            ->select('p.*, u.username, u.fullname')
            ->from($this->table . ' p')
            ->join('jurnal_new_users u', 'u.id = p.user_parents')
            ->where('p.user_child', $user_id)
            ->where('p.status', 'pending')
            ->order_by('p.created_at', 'DESC')
            ->get()
            ->result();
    }

    public function get_sent_requests($user_id) {
        return $this->db
            ->select('p.*, u.username, u.fullname')
            ->from($this->table . ' p')
            ->join('jurnal_new_users u', 'u.id = p.user_child')
            ->where('p.user_parents', $user_id)
            ->where('p.status', 'pending')
            ->order_by('p.created_at', 'DESC')
            ->get()
            ->result();
    }
public function get_approved_partners($user_id) {
    $sql = "SELECT 
                p.*,
                CASE 
                    WHEN p.user_parents = ? THEN u2.username 
                    ELSE u1.username 
                END AS partner_username,
                CASE 
                    WHEN p.user_parents = ? THEN u2.fullname 
                    ELSE u1.fullname 
                END AS partner_fullname,
                CASE 
                    WHEN p.user_parents = ? THEN p.role_a_to_b 
                    ELSE p.role_b_to_a 
                END AS role,
                CASE 
                    WHEN p.user_parents = ? THEN p.user_child 
                    ELSE p.user_parents 
                END AS partner_id
            FROM " . $this->table . " p
            JOIN jurnal_new_users u1 ON u1.id = p.user_parents
            JOIN jurnal_new_users u2 ON u2.id = p.user_child
            WHERE (p.user_parents = ? OR p.user_child = ?)
            AND p.status = 'approved'
            ORDER BY partner_fullname ASC";
    
    $query = $this->db->query($sql, [$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    return $query->result();
}

    public function has_approved_partner($user_id) {
        $count = $this->db
            ->where('(user_parents = ' . (int)$user_id . ' OR user_child = ' . (int)$user_id . ')')
            ->where('status', 'approved')
            ->count_all_results($this->table);
        return $count > 0;
    }
    public function get_users_except_self($user_id, $keyword = '') {
      // Ambil semua user yang sudah menjadi partner dengan status APPROVED
      // (yang rejected tetap boleh dipilih ulang)
      $approved_user_ids = $this->db
          ->select('user_parents as uid')
          ->from($this->table)
          ->where('user_child', $user_id)
          ->where('status', 'approved')
          ->get()
          ->result_array();
      
      $approved_user_ids2 = $this->db
          ->select('user_child as uid')
          ->from($this->table)
          ->where('user_parents', $user_id)
          ->where('status', 'approved')
          ->get()
          ->result_array();
      
      $exclude_ids = [$user_id];
      foreach ($approved_user_ids as $p) $exclude_ids[] = $p['uid'];
      foreach ($approved_user_ids2 as $p) $exclude_ids[] = $p['uid'];
      $exclude_ids = array_unique($exclude_ids);
      
      $this->db->select('id, username, fullname, email')
          ->from('jurnal_new_users')
          ->where_not_in('id', $exclude_ids)
          ->where('is_active', 1);
      
      if ($keyword) {
          $this->db->group_start()
              ->like('username', $keyword)
              ->or_like('fullname', $keyword)
              ->or_like('email', $keyword)
          ->group_end();
      }
      
      return $this->db->order_by('fullname', 'ASC')->limit(20)->get()->result();
  }
  public function get_request_history($user_id) {
    // Request yang diterima (user sebagai penerima)
    $received = $this->db
        ->select('p.*, u.username, u.fullname, "received" as direction')
        ->from($this->table . ' p')
        ->join('jurnal_new_users u', 'u.id = p.user_parents')
        ->where('p.user_child', $user_id)
        ->where_in('p.status', ['approved', 'rejected'])
        ->order_by('p.updated_at', 'DESC')
        ->get()
        ->result();
    
    // Request yang dikirim (user sebagai pengirim)
    $sent = $this->db
        ->select('p.*, u.username, u.fullname, "sent" as direction')
        ->from($this->table . ' p')
        ->join('jurnal_new_users u', 'u.id = p.user_child')
        ->where('p.user_parents', $user_id)
        ->where_in('p.status', ['approved', 'rejected'])
        ->order_by('p.updated_at', 'DESC')
        ->get()
        ->result();
    
    return array_merge($received, $sent);
}

    public function is_partner_approved($user_id, $partner_id) {
        $count = $this->db
            ->where('status', 'approved')
            ->group_start()
                ->group_start()
                    ->where('user_parents', $user_id)
                    ->where('user_child', $partner_id)
                ->group_end()
                ->or_group_start()
                    ->where('user_parents', $partner_id)
                    ->where('user_child', $user_id)
                ->group_end()
            ->group_end()
            ->count_all_results($this->table);
        return $count > 0;
    }

    public function delete_partner($id, $user_id) {
        $partner = $this->get_by_id($id);
        if (!$partner) return false;
        if ($partner->user_parents != $user_id && $partner->user_child != $user_id) return false;

        $this->db->where('id', $id)->delete($this->table);
        return true;
    }
    public function update_partner($id, $user_id) {
        $partner = $this->get_by_id($id);
        if (!$partner) return false;
        if ($partner->user_parents != $user_id && $partner->user_child != $user_id) return false;

        $relation_type = $partner->loan_relation_type ?? '';
        if ($relation_type === 'parent') {
            $this->clear_loan_relation($partner->user_child, $partner->user_parents);
        } elseif ($relation_type === 'child') {
            $this->clear_loan_relation($partner->user_parents, $partner->user_child);
        } else {
            $this->clear_loan_relation($partner->user_parents, $partner->user_child);
            $this->clear_loan_relation($partner->user_child, $partner->user_parents);
        }

        return true;
    }

    private function clear_loan_relation($parent_id, $child_id) {
        $parent_id = (int) $parent_id;
        $child_id = (int) $child_id;
        if (!$parent_id || !$child_id) {
            return;
        }

        $parent = $this->db->where('id', $parent_id)->get('jurnal_new_users')->row();
        if ($parent) {
            $child_ids = $this->parse_id_list($parent->loan_child ?? '');
            $child_ids = array_values(array_filter($child_ids, function ($id) use ($child_id) {
                return (int) $id !== $child_id;
            }));

            $this->db->where('id', $parent_id)->update('jurnal_new_users', [
                'loan_child' => !empty($child_ids) ? implode(',', $child_ids) : null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $child = $this->db->where('id', $child_id)->get('jurnal_new_users')->row();
        if ($child && (int) ($child->loan_parent ?? 0) === $parent_id) {
            $this->db->where('id', $child_id)->update('jurnal_new_users', [
                'loan_parent' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function parse_id_list($value) {
        if (is_array($value)) {
            $ids = $value;
        } else {
            $ids = preg_split('/[, ]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        })));
    }
    public function get_existing_request($user_id, $partner_id) {
      return $this->db
          ->group_start()
              ->where('user_parents', $user_id)
              ->where('user_child', $partner_id)
          ->group_end()
          ->or_group_start()
              ->where('user_parents', $partner_id)
              ->where('user_child', $user_id)
          ->group_end()
          ->get($this->table)
          ->row();
  }
}
