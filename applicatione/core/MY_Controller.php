<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public $data = [];

    public function __construct() {
        parent::__construct();
        
        if ($this->session->userdata('logged_in')) {
            $this->load->model('M_partner');
            $this->load->model('M_user');
            $this->load->model('M_user_activity');

            $user_id = (int) $this->session->userdata('user_id');
            $current_user = $this->M_user->get_by_id($user_id);
            $has_partner = $this->M_partner->has_approved_partner($user_id);
            $loan_parent_id = (int) ($current_user->loan_parent ?? 0);
            $loan_child_ids = $this->parse_id_list($current_user->loan_child ?? '');
            $this->data['has_partner'] = $this->M_partner->has_approved_partner($this->session->userdata('user_id'));
            $this->data['user_id'] = $user_id;
            $this->data['username'] = $this->session->userdata('username');
            $this->data['fullname'] = $this->session->userdata('fullname');
            $this->data['loan_parent_id'] = $loan_parent_id;
            $this->data['loan_child_ids'] = $loan_child_ids;
            $this->data['has_loan_relation'] = ($loan_parent_id > 0 || !empty($loan_child_ids));
            $this->data['can_access_loan'] = !empty($loan_child_ids);
            $this->data['can_access_loan_report'] = ($loan_parent_id > 0 || !empty($loan_child_ids));
            $this->data['is_loan_child'] = $loan_parent_id > 0;

            if (!$this->input->is_ajax_request()) {
                $page_url = uri_string() ?: 'dashboard';
                $this->M_user_activity->log_page(
                    $user_id, $page_url, ucwords(str_replace(['/', '_', '-'], ' ', $page_url)),
                    $this->input->method(TRUE), $this->input->ip_address(), $this->input->user_agent()
                );
            }
        }
    }

    protected function render($view, $custom_data = []) {
        $data = array_merge($this->data, $custom_data);
        $this->load->view('template/header', $data);
        $this->load->view('template/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('template/bottom_nav', $data);
        $this->load->view('template/footer', $data);
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
