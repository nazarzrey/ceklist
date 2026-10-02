<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            echo json_encode(['status' => false, 'message' => 'Unauthorized']);
            exit;
        }
        $this->load->model('M_kategori');
        $this->load->model('M_dompet');
        $this->load->model('M_transaksi');
    }

    public function get_kategori() {
        $user_id = $this->session->userdata('user_id');
        $tipe = $this->input->post('tipe');
        
        $kategori = $this->M_kategori->get_all($user_id, $tipe);
        
        echo json_encode(['status' => true, 'data' => $kategori]);
    }

    public function get_wallets() {
        $user_id = $this->session->userdata('user_id');
        $show_all = $this->input->post('show_all') ? true : false;
        
        $wallets = $this->M_dompet->get_by_user($user_id, !$show_all);
        
        echo json_encode(['status' => true, 'data' => $wallets]);
    }

    public function suggest_transaksi() {
        $user_id = $this->session->userdata('user_id');
        $keyword = trim($this->input->post('keyword'));
        $tipe = $this->input->post('tipe');

        if (strlen($keyword) < 2) {
            echo json_encode(['status' => true, 'data' => []]);
            return;
        }
        $hasil = $this->M_transaksi->suggest_deskripsi($user_id, $keyword, 8, $tipe);
        echo json_encode([
            'status' => true,
            'data' => $hasil
        ]);
    }
}
