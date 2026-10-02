<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('init_model');
        $this->load->model('classroom_model');
        $this->init_model->ensure_tables();
        $this->init_model->seed_data();
        $this->classroom_model->ensure_schema();
    }

    public function login()
    {
        if ($this->input->method(TRUE) !== 'POST') { redirect('/'); return; }
        $nim = preg_replace('/\D+/', '', (string) $this->input->post('nim', TRUE));
        $password = (string) $this->input->post('password');
        $user = $this->classroom_model->user_by_nim($nim);
        $valid = FALSE;
        if ($user) {
            $valid = !empty($user['password_hash'])
                ? password_verify($password, $user['password_hash'])
                : hash_equals(substr(trim($user['nim']), -6), $password);
        }
        if (!$valid) {
            $this->session->set_flashdata('login_error', 'NIM atau password tidak cocok. Password awal adalah 6 digit terakhir NIM.');
            redirect('/');
            return;
        }
        $this->session->sess_regenerate(TRUE);
        $this->session->set_userdata('webkelas_user', $this->classroom_model->public_user($user));
        $route = trim((string) $this->input->post('return_route', TRUE));
        if (!preg_match('#^(overview|tasks|announcements|schedule|courses|worklog|profile|announcement/[0-9]+|checklist/course/[a-z0-9-]+)$#', $route)) $route = 'overview';
        redirect(site_url() . '#' . $route);
    }

    public function magic($token = '')
    {
        $this->output->set_header('Cache-Control: no-store, private');
        $this->output->set_header('Referrer-Policy: no-referrer');
        $this->output->set_header('X-Robots-Tag: noindex, nofollow');
        $nim = $this->classroom_model->consume_profile_login_link((string) $token);
        $user = $nim ? $this->classroom_model->user_by_nim($nim) : NULL;
        if (!$user) {
            $this->session->set_flashdata('login_error', 'Tautan profil tidak valid, sudah dipakai, atau sudah kedaluwarsa. Buat tautan baru dari halaman profil.');
            redirect('/');
            return;
        }
        $this->session->sess_regenerate(TRUE);
        $this->session->set_userdata('webkelas_user', $this->classroom_model->public_user($user));
        redirect(site_url() . '#profile');
    }

    public function logout()
    {
        if ($this->input->method(TRUE) !== 'POST') { show_404(); return; }
        $this->session->unset_userdata('webkelas_user');
        redirect('/');
    }
}
