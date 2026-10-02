<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller
{
    public function index()
    {
        $this->load->model('init_model');
        $this->load->model('classroom_model');
        $this->init_model->ensure_tables();
        $this->init_model->seed_data();
        $this->classroom_model->ensure_schema();
        $user = $this->session->userdata('webkelas_user');
        if (!$user) {
            $this->load->view('auth/login', array('error' => $this->session->flashdata('login_error')));
            return;
        }
        $this->load->view('dashboard');
    }
}
