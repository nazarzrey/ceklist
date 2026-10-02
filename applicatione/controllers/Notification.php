<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_notification');
    }

    public function get_unread() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        
        $unread = $this->M_notification->get_unread($user_id);
        $count = $this->M_notification->count_unread($user_id);
        
        echo json_encode([
            'status' => true,
            'count' => $count,
            'notifications' => $unread
        ]);
    }

    public function mark_read() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        if ($id) {
            $this->M_notification->mark_as_read($id, $user_id);
        } else {
            $this->M_notification->mark_all_read($user_id);
        }
        
        echo json_encode(['status' => true]);
    }
}