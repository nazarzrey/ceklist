<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_user');
        $this->load->model('M_partner');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        $user = $this->M_user->get_by_id($user_id);
        $loan_child_ids = $this->parse_id_list($user->loan_child ?? '');
        
        $role = $this->session->userdata('role') ?: 'user';
        $is_admin = ($role === 'admin');
        
        $data = [
            'title' => 'Profil Saya',
            'active_menu' => 'profile',
            'user' => $user,
            'is_admin' => $is_admin,
            'pending_requests' => $is_admin ? $this->M_partner->get_pending_requests($user_id) : [],
            'sent_requests' => $is_admin ? $this->M_partner->get_sent_requests($user_id) : [],
            'approved_partners' => $is_admin ? $this->M_partner->get_approved_partners($user_id) : [],
            'request_history' => $is_admin ? $this->M_partner->get_request_history($user_id) : [],
            'loan_parent_id' => $is_admin ? (int) ($user->loan_parent ?? 0) : 0,
            'loan_child_ids' => $is_admin ? $loan_child_ids : []
        ];
        
        $this->render('profile/index', $data);
    }

    public function update_profile() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        
        $data = [
            'fullname' => $this->input->post('fullname'),
            'email' => $this->input->post('email'),
            'bg' => $this->input->post('bg') ?: '#f5f7fb',
            'txt' => $this->input->post('txt') ?: '#1a2a3a'
        ];

        $role = $this->session->userdata('role') ?: 'user';
        $is_admin = ($role === 'admin');

        if (!$is_admin) {
            $this->M_user->update($user_id, $data);
            $this->session->set_userdata('fullname', $data['fullname']);
            $this->session->set_userdata('bg', $data['bg']);
            $this->session->set_userdata('txt', $data['txt']);
            echo json_encode(['status' => true, 'message' => 'Profil berhasil diupdate']);
            return;
        }

        $current_user = $this->M_user->get_by_id($user_id);
        $old_parent_id = (int) ($current_user->loan_parent ?? 0);
        $old_child_ids = $this->parse_id_list($current_user->loan_child ?? '');
        $new_parent_id = (int) ($this->input->post('loan_parent') ?: 0);
        $new_child_ids = $this->parse_id_list($this->input->post('loan_child'));

        if ($new_parent_id === $user_id) {
            echo json_encode(['status' => false, 'message' => 'Parent kasbon tidak boleh diri sendiri']);
            return;
        }

        if (in_array($user_id, $new_child_ids, true)) {
            echo json_encode(['status' => false, 'message' => 'Child kasbon tidak boleh diri sendiri']);
            return;
        }

        if ($new_parent_id && !$this->M_partner->is_partner_approved($user_id, $new_parent_id)) {
            echo json_encode(['status' => false, 'message' => 'Parent kasbon harus partner aktif']);
            return;
        }

        foreach ($new_child_ids as $child_id) {
            if (!$this->M_partner->is_partner_approved($user_id, $child_id)) {
                echo json_encode(['status' => false, 'message' => 'Child kasbon harus partner aktif']);
                return;
            }
        }

        $data['loan_parent'] = $new_parent_id ?: null;
        $data['loan_child'] = !empty($new_child_ids) ? implode(',', $new_child_ids) : null;

        $this->M_user->update($user_id, $data);

        if ($old_parent_id && $old_parent_id !== $new_parent_id) {
            $this->remove_user_from_child_list($old_parent_id, $user_id);
        }
        if ($new_parent_id && $new_parent_id !== $old_parent_id) {
            $this->add_user_to_child_list($new_parent_id, $user_id);
        }

        $removed_child_ids = array_values(array_diff($old_child_ids, $new_child_ids));
        $added_child_ids = array_values(array_diff($new_child_ids, $old_child_ids));

        foreach ($removed_child_ids as $child_id) {
            $child = $this->M_user->get_by_id($child_id);
            if ($child && (int) ($child->loan_parent ?? 0) === $user_id) {
                $this->M_user->update($child_id, ['loan_parent' => null]);
            }
        }

        foreach ($added_child_ids as $child_id) {
            $child = $this->M_user->get_by_id($child_id);
            if (!$child) {
                continue;
            }

            $current_parent_id = (int) ($child->loan_parent ?? 0);
            if ($current_parent_id && $current_parent_id !== $user_id) {
                $this->remove_user_from_child_list($current_parent_id, $child_id);
            }

            $this->M_user->update($child_id, ['loan_parent' => $user_id]);
        }
        
        // Update session
        $this->session->set_userdata('fullname', $data['fullname']);
        $this->session->set_userdata('bg', $data['bg']);
        $this->session->set_userdata('txt', $data['txt']);
        
        echo json_encode(['status' => true, 'message' => 'Profil berhasil diupdate']);
    }

    protected function parse_id_list($value) {
        if (is_array($value)) {
            $ids = $value;
        } else {
            $ids = preg_split('/[, ]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        })));

        return $ids;
    }

    private function remove_user_from_child_list($parent_id, $child_id) {
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

    private function add_user_to_child_list($parent_id, $child_id) {
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

    public function change_password() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $old_password = $this->input->post('old_password');
        $new_password = $this->input->post('new_password');
        $confirm_password = $this->input->post('confirm_password');
        
        $user = $this->M_user->get_by_id($user_id);
        
        if (!$this->M_user->verify_password($old_password, $user->password)) {
            echo json_encode(['status' => false, 'message' => 'Password lama salah']);
            return;
        }
        
        if (strlen($new_password) < 4) {
            echo json_encode(['status' => false, 'message' => 'Password baru minimal 4 karakter']);
            return;
        }
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['status' => false, 'message' => 'Konfirmasi password tidak sesuai']);
            return;
        }
        
        $this->M_user->update($user_id, ['password' => $new_password]);
        
        echo json_encode(['status' => true, 'message' => 'Password berhasil diubah']);
    }
}
