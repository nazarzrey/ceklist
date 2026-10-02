<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        $this->load->model('M_setting');
    }

    public function index() {
        $user_id = (int) $this->session->userdata('user_id');
        $role = $this->session->userdata('role') ?: 'user';
        $setting = $this->M_setting->get_user_setting($user_id, $role);

        $data = [
            'title' => 'Setting',
            'active_menu' => 'setting',
            'setting' => $setting
        ];

        $this->render('setting/index', $data);
    }

    public function simpan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = (int) $this->session->userdata('user_id');
        $role = $this->session->userdata('role') ?: 'user';
        $background_color = trim($this->input->post('background_color')) ?: '#f5f7fb';
        $text_color = trim($this->input->post('text_color')) ?: '#1a2a3a';
        $menu_items = json_decode((string) $this->input->post('menu_items'), true);

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $background_color)) {
            echo json_encode(['status' => false, 'message' => 'Warna background tidak valid']);
            return;
        }

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $text_color)) {
            echo json_encode(['status' => false, 'message' => 'Warna teks tidak valid']);
            return;
        }

        $this->M_setting->save_user_setting($user_id, $role, $background_color, $text_color, $menu_items);

        $this->session->set_userdata('bg', $background_color);
        $this->session->set_userdata('txt', $text_color);

        echo json_encode(['status' => true, 'message' => 'Setting berhasil disimpan']);
    }
}
