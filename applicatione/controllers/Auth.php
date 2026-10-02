<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_user');
        $this->load->model('M_dompet');
    }

    public function index() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $google = $this->google_config();
        $this->load->view('auth/login', [
            'recaptcha_site_key' => config_item('app_online') ? ($google['recaptcha_site_key'] ?? '') : '',
            'app_online' => (bool) config_item('app_online')
        ]);
    }

    public function google() {
        $google = $this->google_config();
        if (empty($google['client_id']) || empty($google['client_secret'])) {
            show_error('Konfigurasi Login with Google belum tersedia.', 500);
            return;
        }
        if (config_item('app_online') && !$this->verify_recaptcha($this->input->get('captcha_token'))) {
            $this->session->set_flashdata('error', 'Verifikasi keamanan Google gagal. Silakan coba lagi.');
            redirect('auth');
            return;
        }

        $state = bin2hex(openssl_random_pseudo_bytes(16));
        $this->session->set_userdata('google_oauth_state', $state);
        redirect($google['auth_uri'] . '?' . http_build_query([
            'client_id' => $google['client_id'],
            'redirect_uri' => $google['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'state' => $state,
            'prompt' => 'select_account'
        ]));
    }

    public function google_callback() {
        if ($this->input->get('error')) {
            $this->session->unset_userdata('google_oauth_state');
            $this->session->set_flashdata('error', 'Login Google dibatalkan.');
            redirect('auth');
            return;
        }

        $state = (string) $this->input->get('state');
        $saved_state = (string) $this->session->userdata('google_oauth_state');
        $this->session->unset_userdata('google_oauth_state');
        if ($saved_state === '' || $state === '' || !hash_equals($saved_state, $state)) {
            show_error('Sesi Login Google tidak valid. Silakan coba lagi.', 400);
            return;
        }

        $code = trim((string) $this->input->get('code'));
        if ($code === '') {
            show_error('Kode Login Google tidak ditemukan.', 400);
            return;
        }

        $google = $this->google_config();
        $token = $this->google_request($google['token_uri'], [
            'code' => $code,
            'client_id' => $google['client_id'],
            'client_secret' => $google['client_secret'],
            'redirect_uri' => $google['redirect_uri'],
            'grant_type' => 'authorization_code'
        ]);
        if (empty($token['access_token'])) {
            log_message('error', 'Google token exchange failed: ' . json_encode($token));
            show_error('Gagal mengambil akses Login Google.', 502);
            return;
        }

        $profile = $this->google_request('https://www.googleapis.com/oauth2/v3/userinfo', null, [
            'Authorization: Bearer ' . $token['access_token']
        ]);
        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        if ($email === '' || empty($profile['email_verified'])) {
            show_error('Akun Google tidak memiliki email terverifikasi.', 403);
            return;
        }

        // Email yang sudah terdaftar memakai akun lama dan langsung ke dashboard.
        $user = $this->M_user->get_by_email($email);
        if (!$user) {
            $username = $this->unique_google_username($email);
            $this->M_user->insert([
                'username' => $username,
                'fullname' => trim((string) ($profile['name'] ?? $username)),
                'email' => $email,
                'password' => bin2hex(openssl_random_pseudo_bytes(24)),
                'role' => 'user',
                'avatar' => trim((string) ($profile['picture'] ?? ''))
            ]);
            $user = $this->M_user->get_by_username($username);
            if ($user) {
                $this->M_dompet->ensure_default_wallets($user->id);
            }
        }

        if (!$user) {
            show_error('Akun Google gagal disiapkan.', 500);
            return;
        }
        $this->login_user($user);
        redirect('dashboard');
    }

    private function google_config() {
        $this->config->load('google');
        return (array) config_item('google_oauth');
    }

    private function verify_recaptcha($token) {
        $google = $this->google_config();
        if (empty($google['recaptcha_secret_key'])) {
            log_message('error', 'reCAPTCHA secret key tidak terbaca dari konfigurasi.');
            return false;
        }
        if (trim((string) $token) === '' || !function_exists('curl_init')) {
            log_message('error', 'reCAPTCHA token kosong atau cURL tidak aktif.');
            return false;
        }
        $result = $this->google_request('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $google['recaptcha_secret_key'],
            'response' => $token
        ]);
        $valid = !empty($result['success']) && (float) ($result['score'] ?? 0) >= 0.5 && ($result['action'] ?? '') === 'login';
        if (!$valid) {
            log_message('error', 'reCAPTCHA ditolak: ' . json_encode([
                'success' => $result['success'] ?? false,
                'score' => $result['score'] ?? null,
                'action' => $result['action'] ?? null,
                'hostname' => $result['hostname'] ?? null,
                'error_codes' => $result['error-codes'] ?? []
            ]));
        }
        return $valid;
    }

    private function google_request($url, $post = null, $headers = []) {
        if (!function_exists('curl_init')) return ['error' => 'Ekstensi cURL PHP belum aktif'];
        $curl = curl_init($url);
        if (is_array($post)) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers
        ]);
        $response = curl_exec($curl);
        $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $decoded = json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : ['error' => 'Respons Google tidak valid', 'http_code' => $http_code];
    }

    private function unique_google_username($email) {
        $base = preg_replace('/[^a-z0-9_]+/i', '_', (string) strstr($email, '@', true));
        $base = trim(strtolower($base), '_');
        $base = $base !== '' ? substr($base, 0, 24) : 'google_user';
        $username = $base;
        $suffix = 1;
        while ($this->M_user->username_exists($username)) {
            $username = substr($base, 0, 20) . '_' . $suffix++;
        }
        return $username;
    }

    private function login_user($user) {
        $this->session->set_userdata([
            'logged_in' => true,
            'user_id' => $user->id,
            'username' => $user->username,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'bg' => $user->bg ?? '#f5f7fb',
            'txt' => $user->txt ?? '#1a2a3a'
        ]);
        $this->db->where('id', $user->id)->update('jurnal_new_users', ['last_login' => date('Y-m-d H:i:s')]);
    }

    private function get_reset_code() {
        return (int) date('d') + ((int) date('m') * (int) date('m')) + (int) date('Y') + (int) date('H');
    }

    private function json_response($payload) {
        $payload['csrf'] = [
            'name' => $this->security->get_csrf_token_name(),
            'hash' => $this->security->get_csrf_hash()
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    public function reset() {
        $this->load->view('auth/reset');
    }

    public function verify_reset_code() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        if (!$this->reset_attempt_allowed('verify')) {
            $this->output->set_status_header(429);
            $this->json_response(['status' => false, 'message' => 'Terlalu banyak percobaan kode reset. Coba lagi beberapa menit.']);
            return;
        }

        $code = trim($this->input->post('code'));
        if ($code === '') {
            $this->json_response(['status' => false, 'message' => 'Kode reset wajib diisi']);
            return;
        }

        if (!ctype_digit($code) || (int) $code !== $this->get_reset_code()) {
            $this->session->unset_userdata('reset_password_verified');
            $this->json_response(['status' => false, 'message' => 'Kode reset tidak sesuai']);
            return;
        }

        $this->clear_reset_attempts('verify');
        $this->session->set_userdata([
            'reset_password_verified' => true,
            'reset_password_verified_at' => time()
        ]);

        $this->json_response(['status' => true, 'message' => 'Kode benar. Silakan isi akun dan password baru.']);
    }

    public function reset_password() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        if (!$this->reset_attempt_allowed('password')) {
            $this->output->set_status_header(429);
            $this->json_response(['status' => false, 'message' => 'Terlalu banyak percobaan reset password. Coba lagi beberapa menit.']);
            return;
        }

        if (!$this->session->userdata('reset_password_verified')) {
            $this->json_response(['status' => false, 'message' => 'Masukkan kode reset lebih dulu']);
            return;
        }

        $verified_at = (int) $this->session->userdata('reset_password_verified_at');
        if ($verified_at <= 0 || $verified_at < (time() - 600)) {
            $this->session->unset_userdata(['reset_password_verified', 'reset_password_verified_at']);
            $this->json_response(['status' => false, 'message' => 'Sesi reset kadaluarsa. Masukkan kode lagi.']);
            return;
        }

        $identifier = trim($this->input->post('identifier'));
        $password = (string) $this->input->post('password');
        $confirm_password = (string) $this->input->post('confirm_password');

        if ($identifier === '') {
            $this->json_response(['status' => false, 'message' => 'Username atau email wajib diisi']);
            return;
        }

        if (strlen($password) < 4) {
            $this->json_response(['status' => false, 'message' => 'Password minimal 4 karakter']);
            return;
        }

        if ($password !== $confirm_password) {
            $this->json_response(['status' => false, 'message' => 'Konfirmasi password tidak sesuai']);
            return;
        }

        $user = $this->M_user->get_by_username_or_email($identifier);
        if (!$user) {
            $this->json_response(['status' => false, 'message' => 'Username atau email tidak ditemukan']);
            return;
        }

        $this->M_user->update($user->id, ['password' => $password]);
        $this->clear_reset_attempts('password');
        $this->session->unset_userdata(['reset_password_verified', 'reset_password_verified_at']);

        $this->json_response(['status' => true, 'message' => 'Password berhasil direset', 'redirect' => site_url('auth')]);
    }

    private function reset_attempt_file($action) {
        $dir = APPPATH . 'cache/reset-rate';
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        return $dir . DIRECTORY_SEPARATOR . hash('sha256', $action . '|' . $this->input->ip_address()) . '.json';
    }

    private function reset_attempt_allowed($action) {
        $file = $this->reset_attempt_file($action);
        $now = time();
        $window = 600;
        $max_attempts = 5;
        $attempts = [];
        if (is_readable($file)) {
            $stored = json_decode(file_get_contents($file), true);
            if (is_array($stored)) $attempts = $stored;
        }
        $attempts = array_values(array_filter($attempts, function ($timestamp) use ($now, $window) {
            return ((int) $timestamp) > ($now - $window);
        }));
        if (count($attempts) >= $max_attempts) {
            file_put_contents($file, json_encode($attempts), LOCK_EX);
            return false;
        }
        $attempts[] = $now;
        file_put_contents($file, json_encode($attempts), LOCK_EX);
        return true;
    }

    private function clear_reset_attempts($action) {
        $file = $this->reset_attempt_file($action);
        if (is_file($file)) @unlink($file);
    }

    public function do_login() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $username = trim($this->input->post('username'));
        $password = trim($this->input->post('password'));

        if (!$this->login_attempt_allowed()) {
            $this->output->set_status_header(429);
            $this->json_response(['status' => false, 'message' => 'Terlalu banyak percobaan login. Coba lagi beberapa menit.']);
            return;
        }

        if (config_item('app_online') && !$this->verify_recaptcha($this->input->post('recaptcha_token'))) {
            $this->json_response(['status' => false, 'message' => 'Verifikasi keamanan gagal. Silakan coba lagi.']);
            return;
        }

        if (empty($username) || empty($password)) {
            $this->json_response(['status' => false, 'message' => 'Username dan password wajib diisi']);
            return;
        }

        $user = $this->M_user->get_by_username($username);

        if (!$user) {
            $this->json_response(['status' => false, 'message' => 'Username tidak ditemukan']);
            return;
        }
        //die (password_hash('admin123', PASSWORD_DEFAULT));
        if (!$this->M_user->verify_password($password, $user->password)) {
            $this->json_response(['status' => false, 'message' => 'Password salah']);
            return;
        }

        // Set session
        $this->session->set_userdata([
            'logged_in' => true,
            'user_id' => $user->id,
            'username' => $user->username,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'bg' => $user->bg ?? '#f5f7fb',
            'txt' => $user->txt ?? '#1a2a3a'
        ]);

        $this->db->where('id', $user->id)->update('jurnal_new_users', ['last_login' => date('Y-m-d H:i:s')]);

        $this->clear_login_attempts();
        $this->json_response(['status' => true, 'message' => 'Login berhasil', 'redirect' => site_url('dashboard')]);
    }

    private function login_attempt_file() {
        $dir = APPPATH . 'cache/login-rate';
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        return $dir . DIRECTORY_SEPARATOR . hash('sha256', $this->input->ip_address()) . '.json';
    }

    private function login_attempt_allowed() {
        $file = $this->login_attempt_file();
        $now = time();
        $window = 600;
        $max_attempts = 10;
        $attempts = [];
        if (is_readable($file)) {
            $stored = json_decode(file_get_contents($file), true);
            if (is_array($stored)) $attempts = $stored;
        }
        $attempts = array_values(array_filter($attempts, function ($timestamp) use ($now, $window) {
            return ((int) $timestamp) > ($now - $window);
        }));
        if (count($attempts) >= $max_attempts) {
            file_put_contents($file, json_encode($attempts), LOCK_EX);
            return false;
        }
        $attempts[] = $now;
        file_put_contents($file, json_encode($attempts), LOCK_EX);
        return true;
    }

    private function clear_login_attempts() {
        $file = $this->login_attempt_file();
        if (is_file($file)) @unlink($file);
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth');
    }

    public function mark_install() {
        if (!$this->input->is_ajax_request() || !$this->session->userdata('logged_in')) {
            show_404();
            return;
        }
        $this->M_user->mark_pwa_installed((int) $this->session->userdata('user_id'));
        $this->json_response(['status' => true]);
    }
}
