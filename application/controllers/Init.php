<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Init.php — Halaman inisialisasi database untuk WebKelas 06TPLE004.
 *
 * Akses setup hanya tersedia jika WEBKELAS_INIT_USER dan
 * WEBKELAS_INIT_PASSWORD_HASH dikonfigurasi di environment server.
 *
 * Setelah login berhasil, halaman ini:
 *   1. Memeriksa keberadaan seluruh tabel yang diharapkan skrip CI3.
 *   2. Membuat tabel yang belum ada (CREATE IF NOT EXISTS).
 *   3. Mengisi seed data courses / course_links / course_notes / course_meetings
 *      hanya jika tabel kosong (INSERT IGNORE).
 *   4. Menampilkan ringkasan status setiap tabel dan apakah perlu tindak lanjut.
 *
 * Dipanggil melalui route: /init
 */

class Init extends CI_Controller
{
    private $sessionKey = 'init_logged_in';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('init_model');
    }

    /**
     * Halaman utama: cetak form login atau dashboard inisialisasi
     * setelah kredensial divalidasi.
     */
    public function index()
    {
        if ($this->session->userdata($this->sessionKey)) {
            $this->show_init_page();
            return;
        }

        $error = $this->session->flashdata('init_error');
        $this->load->view('init/login', [
            'error' => $error ?: '',
            'csrf_token' => $this->new_init_csrf_token(),
        ]);
    }

    /**
     * Endpoint POST /init/login — validasi kredensial dan set session.
     */
    public function login_post()
    {
        if (strtoupper($this->input->method(TRUE)) !== 'POST') { show_404(); return; }
        if (!$this->verify_init_csrf()) {
            $this->session->set_flashdata('init_error', 'Sesi form berakhir. Silakan coba lagi.');
            redirect('init');
            return;
        }

        $user     = trim($this->input->post('username', TRUE));
        $password = (string) $this->input->post('password');
        $expectedUser = getenv('WEBKELAS_INIT_USER');
        $passwordHash = getenv('WEBKELAS_INIT_PASSWORD_HASH');

        if (is_string($expectedUser) && $expectedUser !== '' && is_string($passwordHash) && $passwordHash !== ''
            && hash_equals($expectedUser, $user) && password_verify($password, $passwordHash)) {
            $this->session->sess_regenerate(TRUE);
            $this->session->set_userdata($this->sessionKey, TRUE);
            redirect('init');
            return;
        }

        $this->session->set_flashdata('init_error', 'Akses setup tidak tersedia atau kredensial tidak cocok.');
        redirect('init');
    }

    /**
     * Endpoint POST /init/logout — hapus session dan kembali ke login.
     */
    public function logout()
    {
        if (strtoupper($this->input->method(TRUE)) !== 'POST') { show_404(); return; }
        if (!$this->verify_init_csrf()) { show_error('Sesi form berakhir.', 403); return; }
        $this->session->unset_userdata($this->sessionKey);
        redirect('init');
    }

    /**
     * Endpoint POST /init/seed — jalankan seed ulang (opsional).
     * Hanya bisa diakses setelah login.
     */
    public function seed()
    {
        if (!$this->session->userdata($this->sessionKey)) {
            show_404();
            return;
        }

        if (strtoupper($this->input->method(TRUE)) !== 'POST') { show_404(); return; }
        if (!$this->verify_init_csrf()) { show_error('Sesi form berakhir.', 403); return; }

        $this->init_model->ensure_tables();
        $this->init_model->seed_data();

        $this->session->set_flashdata('init_message', 'Seed data berhasil dijalankan ulang.');
        redirect('init');
    }

    /**
     * Cetak halaman dashboard inisialisasi.
     */
    private function show_init_page()
    {
        // jalankan inisialisasi otomatis saat pertama kali dibuka
        $this->init_model->ensure_tables();
        $this->init_model->seed_data();
        $this->load->model('classroom_model');
        $this->classroom_model->ensure_schema();
        $tables = $this->init_model->inspect_state();

        $this->load->view('init/dashboard', [
            'tables'       => $tables,
            'base_url'     => base_url(),
            'site_url_init'=> site_url('init'),
            'csrf_token'   => $this->new_init_csrf_token(),
        ]);
    }

    private function new_init_csrf_token()
    {
        try { $token = bin2hex(random_bytes(32)); }
        catch (Exception $error) { show_error('Token sesi tidak dapat dibuat.', 500); return ''; }
        $this->session->set_userdata('init_csrf_token', $token);
        return $token;
    }

    private function verify_init_csrf()
    {
        $expected = (string) $this->session->userdata('init_csrf_token');
        $provided = (string) $this->input->post('init_csrf', TRUE);
        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) return FALSE;
        $this->new_init_csrf_token();
        return TRUE;
    }
}
