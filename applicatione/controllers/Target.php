<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Target extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_target');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        $data = [
            'title' => 'Target Tabungan',
            'target' => $this->M_target->get_all($user_id),
            'active_menu' => 'target'
        ];
        
        $this->render('target/index', $data);
    }

    public function simpan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        
        $data = [
            'user_id' => $user_id,
            'nama_target' => $this->input->post('nama_target'),
            'target_nominal' => str_replace(['Rp', '.', ' '], '', $this->input->post('target_nominal')),
            'terkumpul' => 0,
            'deadline' => $this->input->post('deadline'),
            'status' => 'aktif'
        ];
        
        $id = $this->M_target->insert($data);
        
        echo json_encode(['status' => true, 'message' => 'Target berhasil ditambahkan', 'id' => $id]);
    }

    public function tambah_dana() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        $nominal = str_replace(['Rp', '.', ' '], '', $this->input->post('nominal'));
        
        $this->M_target->tambah_dana($id, $nominal, $user_id);
        
        echo json_encode(['status' => true, 'message' => 'Dana berhasil ditambahkan']);
    }

    public function hapus() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $this->M_target->delete($id, $user_id);
        
        echo json_encode(['status' => true, 'message' => 'Target berhasil dihapus']);
    }
}
