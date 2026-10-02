<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Partner extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_partner');
        $this->load->model('M_notification');
        $this->load->model('M_user');
    }

    public function index() {
        $role = $this->session->userdata('role') ?: 'user';
        if ($role !== 'admin') {
            redirect('dashboard');
            return;
        }

        $user_id = $this->session->userdata('user_id');
        
        $data = [
            'title' => 'Manajemen Relasi',
            'active_menu' => 'partner',
            'pending_requests' => $this->M_partner->get_pending_requests($user_id),
            'sent_requests' => $this->M_partner->get_sent_requests($user_id),
            'approved_partners' => $this->M_partner->get_approved_partners($user_id)
        ];
        
        $this->render('partner/index', $data);
    }

    public function cari_user() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $keyword = $this->input->post('keyword');
        
        $users = $this->M_partner->get_users_except_self($user_id, $keyword);
        
        echo json_encode(['status' => true, 'data' => $users]);
    }

    public function kirim_request() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $role = $this->session->userdata('role') ?: 'user';
        if ($role !== 'admin') {
            echo json_encode(['status' => false, 'message' => 'Hanya admin yang bisa mengelola relasi']);
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $partner_id = $this->input->post('partner_id');
        $role = $this->input->post('role');
        $loan_as = $this->input->post('loan_as');
        
        if (!$partner_id) {
            echo json_encode(['status' => false, 'message' => 'Pilih user terlebih dahulu']);
            return;
        }

        if (!in_array($loan_as, ['parent', 'child'], true)) {
            echo json_encode(['status' => false, 'message' => 'Pilih jadikan sebagai Parent atau Child']);
            return;
        }
        
        // Cek apakah sudah jadi partner
        if ($this->M_partner->is_partner_approved($user_id, $partner_id)) {
            echo json_encode(['status' => false, 'message' => 'User sudah menjadi relasi Anda']);
            return;
        }
        
        $partner_user = $this->M_user->get_by_id($partner_id);
        if (!$partner_user) {
            echo json_encode(['status' => false, 'message' => 'User tidak ditemukan']);
            return;
        }
        
        $result = $this->M_partner->send_request($user_id, $partner_id, $role, null, $loan_as);
        
        if ($result) {
            // Kirim notifikasi ke partner
            $this->M_notification->add(
                $partner_id,
                'partner_request',
                'Permintaan Relasi Baru',
                $this->session->userdata('fullname') . ' ingin menjadi relasi Anda',
                $result
            );
            
            echo json_encode(['status' => true, 'message' => 'Permintaan relasi terkirim']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal mengirim permintaan']);
        }
    }

    public function approve() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        // $role = $this->session->userdata('role') ?: 'user';
        // if ($role !== 'admin') {
        //     echo json_encode(['status' => false, 'message' => 'Hanya admin yang bisa mengelola relasi']);
        //     return;
        // }

        $user_id = $this->session->userdata('user_id');
        $request_id = $this->input->post('request_id');
        
        $request = $this->M_partner->get_by_id($request_id);
        if (!$request || $request->user_child != $user_id) {
            echo json_encode(['status' => false, 'message' => 'Permintaan tidak valid']);
            return;
        }

        $relation_type = $request->loan_relation_type ?? '';
        if (!in_array($relation_type, ['parent', 'child'], true)) {
            echo json_encode(['status' => false, 'message' => 'Tipe relasi kasbon tidak valid, kirim ulang permintaan']);
            return;
        }

        $result = $this->M_partner->approve_request($request_id, $user_id);
        
        if ($result) {
            $this->sync_loan_relation_after_approve($request->user_parents, $request->user_child, $relation_type);
            // Dapatkan data partner untuk notifikasi
            $partner = $this->M_partner->get_by_id($request_id);
            if ($partner) {
                $sender_id = ($partner->user_parents == $user_id) ? $partner->user_child : $partner->user_parents;
                $this->M_notification->add(
                    $sender_id,
                    'partner_approved',
                    'Relasi Disetujui',
                    $this->session->userdata('fullname') . ' menyetujui relasi Anda',
                    $request_id
                );
            }
            echo json_encode(['status' => true, 'message' => 'Relasi disetujui']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal menyetujui']);
        }
    }

    public function reject() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        // $role = $this->session->userdata('role') ?: 'user';
        // if ($role !== 'admin') {
        //     echo json_encode(['status' => false, 'message' => 'Hanya admin yang bisa mengelola relasi']);
        //     return;
        // }

        $user_id = $this->session->userdata('user_id');
        $request_id = $this->input->post('request_id');
        
        $result = $this->M_partner->reject_request($request_id, $user_id);
        
        if ($result) {
            echo json_encode(['status' => true, 'message' => 'Permintaan ditolak']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal menolak']);
        }
    }

    public function hapus() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $role = $this->session->userdata('role') ?: 'user';
        if ($role !== 'admin') {
            echo json_encode(['status' => false, 'message' => 'Hanya admin yang bisa mengelola relasi']);
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $partner_id = $this->input->post('partner_id');
        
        $updated = $this->M_partner->update_partner($partner_id, $user_id);
        $result = $updated ? $this->M_partner->delete_partner($partner_id, $user_id) : false;
        
        if ($result) {
            echo json_encode(['status' => true, 'message' => 'Relasi dihapus']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal menghapus']);
        }
    }
    public function check_pending_requests() {
      if (!$this->input->is_ajax_request()) {
          show_404();
          return;
      }
      
      $user_id = $this->session->userdata('user_id');
      $pending = $this->M_partner->get_pending_requests($user_id);
      
      if (!empty($pending)) {
          $request = $pending[0];
          echo json_encode([
              'status' => true,
              'has_pending' => true,
              'request' => [
                  'id' => $request->id,
                  'username' => $request->username,
                  'fullname' => $request->fullname,
                  'created_at' => $request->created_at,
                  'created_at_formatted' => date('d M Y H:i', strtotime($request->created_at))
              ]
          ]);
      } else {
          echo json_encode([
              'status' => true,
              'has_pending' => false
          ]);
      }
  }
  public function kirim_ulang_request() {
      if (!$this->input->is_ajax_request()) {
          show_404();
          return;
      }
      
      $role = $this->session->userdata('role') ?: 'user';
      if ($role !== 'admin') {
          echo json_encode(['status' => false, 'message' => 'Hanya admin yang bisa mengelola relasi']);
          return;
      }

      $user_id = $this->session->userdata('user_id');
      $partner_id = $this->input->post('partner_id');
      $role = $this->input->post('role');
      $loan_as = $this->input->post('loan_as');
      
      if (!$partner_id) {
          echo json_encode(['status' => false, 'message' => 'User tidak valid']);
          return;
      }

      if (!in_array($loan_as, ['parent', 'child'], true)) {
          echo json_encode(['status' => false, 'message' => 'Pilih jadikan sebagai Parent atau Child']);
          return;
      }
      
      // Cek apakah user yang dikirimi masih ada
      $partner_user = $this->M_user->get_by_id($partner_id);
      if (!$partner_user) {
          echo json_encode(['status' => false, 'message' => 'User tujuan tidak ditemukan']);
          return;
      }
      
      // Gunakan method dari model
      $existing = $this->M_partner->get_existing_request($user_id, $partner_id);
      
      if ($existing && $existing->status == 'approved') {
          echo json_encode(['status' => false, 'message' => 'User sudah menjadi relasi Anda']);
          return;
      }
      
      // Kirim request (update jika ada yang rejected, atau buat baru)
      $result = $this->M_partner->send_request($user_id, $partner_id, $role, null, $loan_as);
      
      if ($result) {
          // Kirim notifikasi ke partner
          $this->load->model('M_notification');
          $this->M_notification->add(
              $partner_id,
              'partner_request',
              'Permintaan Relasi Baru',
              $this->session->userdata('fullname') . ' mengirimkan permintaan relasi',
              $result
          );
          
          echo json_encode(['status' => true, 'message' => 'Permintaan dikirim ulang']);
      } else {
          echo json_encode(['status' => false, 'message' => 'Gagal mengirim ulang permintaan']);
      }
  }

  private function sync_loan_relation_after_approve($user_parents, $user_child, $loan_as) {
      $this->load->model('M_user');

      if ($loan_as === 'parent') {
          $sender = $this->M_user->get_by_id($user_parents);
          $receiver = $this->M_user->get_by_id($user_child);

          if ($sender) {
              $old_parent_id = (int) ($sender->loan_parent ?? 0);
              if ($old_parent_id && $old_parent_id !== (int) $user_child) {
                  $this->remove_child_from_parent($old_parent_id, $user_parents);
              }
              $this->M_user->update($user_parents, ['loan_parent' => $user_child]);
          }

          if ($receiver) {
              $this->append_child_to_parent($user_child, $user_parents);
          }
          return;
      }

      if ($loan_as === 'child') {
          $sender = $this->M_user->get_by_id($user_parents);
          $receiver = $this->M_user->get_by_id($user_child);

          if ($receiver) {
              $old_parent_id = (int) ($receiver->loan_parent ?? 0);
              if ($old_parent_id && $old_parent_id !== (int) $user_parents) {
                  $this->remove_child_from_parent($old_parent_id, $user_child);
              }
              $this->M_user->update($user_child, ['loan_parent' => $user_parents]);
          }

          if ($sender) {
              $this->append_child_to_parent($user_parents, $user_child);
          }
      }
  }

  private function append_child_to_parent($parent_id, $child_id) {
      $parent = $this->M_user->get_by_id($parent_id);
      if (!$parent) {
          return;
      }

      $child_ids = $this->parse_id_list($parent->loan_child ?? '');
      if (!in_array((int) $child_id, $child_ids, true)) {
          $child_ids[] = (int) $child_id;
      }

      $this->M_user->update($parent_id, [
          'loan_child' => !empty($child_ids) ? implode(',', array_values(array_unique($child_ids))) : null
      ]);
  }

  private function remove_child_from_parent($parent_id, $child_id) {
      $parent = $this->M_user->get_by_id($parent_id);
      if (!$parent) {
          return;
      }

      $child_ids = $this->parse_id_list($parent->loan_child ?? '');
      $child_ids = array_values(array_filter($child_ids, function ($id) use ($child_id) {
          return (int) $id !== (int) $child_id;
      }));

      $this->M_user->update($parent_id, [
          'loan_child' => !empty($child_ids) ? implode(',', $child_ids) : null
      ]);
  }

  protected function parse_id_list($value) {
      if (is_array($value)) {
          $ids = $value;
      } else {
          $ids = preg_split('/[, ]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
      }

      return array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
          return $id > 0;
      })));
  }


}
