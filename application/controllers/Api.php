<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('course_model');
        $this->load->model('task_model');
        $this->load->model('classroom_model');
        $this->current_user = $this->session->userdata('webkelas_user');
    }

    public function session()
    {
        if (!$this->current_user) return $this->respond(array('message' => 'Silakan masuk ke WebKelas.'), 401);
        return $this->respond(array('data' => $this->current_user));
    }

    public function announcements()
    {
        if (!$this->require_user()) return;
        if ($this->request_method() === 'GET') return $this->respond(array('data' => $this->classroom_model->announcements_for_user($this->current_user['nim'])));
        if ($this->request_method() !== 'POST') return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
        if ($this->current_user['role'] !== 'leader') return $this->respond(array('message' => 'Hanya ketua kelas yang dapat mengirim broadcast.'), 403);
        $payload = $this->payload();
        $title = trim((string) ($payload['title'] ?? ''));
        $body = trim((string) ($payload['body'] ?? ''));
        $course_id = trim((string) ($payload['course_id'] ?? ''));
        if ($title === '' || mb_strlen($title) > 180 || $body === '') return $this->respond(array('message' => 'Judul dan isi informasi wajib diisi.'), 422);
        if ($course_id !== '' && !$this->course_model->find($course_id)) return $this->respond(array('message' => 'Mata kuliah tidak ditemukan.'), 422);
        $reference = trim((string) ($payload['reference_url'] ?? ''));
        if ($reference !== '') {
            $parts = parse_url($reference);
            if (!filter_var($reference, FILTER_VALIDATE_URL) || !$parts || !isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), array('http', 'https'), TRUE)) return $this->respond(array('message' => 'Link referensi harus berupa URL http atau https.'), 422);
            if (mb_strlen($reference) > 500) return $this->respond(array('message' => 'Link referensi maksimal 500 karakter.'), 422);
        }
        $source = trim((string) ($payload['source_url'] ?? ''));
        if ($source !== '') {
            $parts = parse_url($source);
            if (!filter_var($source, FILTER_VALIDATE_URL) || !$parts || !isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), array('http', 'https'), TRUE)) return $this->respond(array('message' => 'Link sumber harus berupa URL http atau https.'), 422);
            if (mb_strlen($source) > 500) return $this->respond(array('message' => 'Link sumber maksimal 500 karakter.'), 422);
        }
        $raw_tasks = $payload['tasks'] ?? array();
        if (is_string($raw_tasks)) $raw_tasks = preg_split('/\r\n|\r|\n/', $raw_tasks);
        if (!is_array($raw_tasks)) $raw_tasks = array();
        $tasks = array();
        foreach ($raw_tasks as $task) {
            if (is_array($task)) {
                $task_title = trim(strip_tags((string) ($task['title'] ?? '')));
                if ($task_title === '' || mb_strlen($task_title) > 240) continue;
                $task_course_id = trim((string) ($task['course_id'] ?? $course_id));
                if ($task_course_id !== '' && !$this->course_model->find($task_course_id)) return $this->respond(array('message' => 'Mata kuliah checklist tidak ditemukan.'), 422);
                $task_reference = trim((string) ($task['reference_url'] ?? ''));
                if ($task_reference !== '') {
                    $task_parts = parse_url($task_reference);
                    if (!filter_var($task_reference, FILTER_VALIDATE_URL) || !$task_parts || !isset($task_parts['scheme']) || !in_array(strtolower($task_parts['scheme']), array('http', 'https'), TRUE)) return $this->respond(array('message' => 'Link checklist harus berupa URL http atau https.'), 422);
                    if (mb_strlen($task_reference) > 500) return $this->respond(array('message' => 'Link checklist maksimal 500 karakter.'), 422);
                }
                $task_due_label = trim(strip_tags((string) ($task['due_label'] ?? '')));
                if (mb_strlen($task_due_label) > 100) return $this->respond(array('message' => 'Label tenggat checklist maksimal 100 karakter.'), 422);
                $tasks[] = array('title' => $task_title, 'course_id' => $task_course_id, 'due_label' => $task_due_label, 'reference_url' => $task_reference);
                if (count($tasks) >= 50) break;
                continue;
            }
            $task = trim(preg_replace('/^\s*(?:[-*•]+|\d+[.)])\s*/u', '', strip_tags((string) $task)));
            if ($task !== '' && mb_strlen($task) <= 240) $tasks[] = $task;
            if (count($tasks) >= 50) break;
        }
        $due_label = trim((string) ($payload['due_label'] ?? ''));
        $deadline = trim((string) ($payload['deadline_at'] ?? ''));
        $deadline_at = NULL;
        if ($deadline !== '') {
            $date = DateTime::createFromFormat('Y-m-d\TH:i', $deadline);
            if (!$date || $date->format('Y-m-d\TH:i') !== $deadline) return $this->respond(array('message' => 'Format tenggat tidak valid.'), 422);
            $deadline_at = $date->format('Y-m-d H:i:00');
        }
        $id = $this->classroom_model->create_announcement(array('course_id' => $course_id, 'title' => $title, 'body' => $body, 'tasks' => $tasks, 'due_label' => $due_label, 'deadline_at' => $deadline_at, 'reference_url' => $reference, 'source_url' => $source), $this->current_user['nim']);
        if (!$id) return $this->respond(array('message' => 'Broadcast belum berhasil disimpan.'), 500);
        return $this->respond(array('data' => array('id' => $id), 'message' => 'Broadcast dan checklist berhasil diterbitkan.'), 201);
    }

    public function class_tasks()
    {
        if (!$this->require_user()) return;
        if ($this->request_method() !== 'GET') return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
        return $this->respond(array('data' => $this->classroom_model->tasks_for_user($this->current_user['nim'])));
    }

    public function class_task_check($id)
    {
        if (!$this->require_user()) return;
        if ($this->request_method() !== 'POST') return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
        if (!$this->classroom_model->task_exists($id)) return $this->respond(array('message' => 'Item checklist tidak ditemukan.'), 404);
        $payload = $this->payload();
        $checked = !empty($payload['checked']);
        $checked = $this->classroom_model->toggle_check($id, $this->current_user['nim'], $checked);
        return $this->respond(array('data' => array('checked' => $checked), 'message' => $checked ? 'Checklist selesai.' : 'Checklist dibuka kembali.'));
    }

    public function announcement_read($id)
    {
        if (!$this->require_user()) return;
        if ($this->request_method() !== 'POST') return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
        $exists = $this->db->where('id', (int) $id)->count_all_results('class_announcements');
        if (!$exists) return $this->respond(array('message' => 'Info kelas tidak ditemukan.'), 404);
        $this->classroom_model->mark_announcement_read($id, $this->current_user['nim']);
        return $this->respond(array('message' => 'Info ditandai sudah dibaca.'));
    }

    public function profile()
    {
        if (!$this->require_user()) return;
        if ($this->request_method() !== 'PUT' && $this->request_method() !== 'POST') return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
        $payload = $this->payload();
        $name = trim(strip_tags((string) ($payload['display_name'] ?? '')));
        if ($name === '' || mb_strlen($name) > 120) return $this->respond(array('message' => 'Nama tampilan wajib diisi dan maksimal 120 karakter.'), 422);
        $new_password = (string) ($payload['new_password'] ?? '');
        $hash = NULL;
        if ($new_password !== '') {
            $current = (string) ($payload['current_password'] ?? '');
            $record = $this->classroom_model->user_by_nim($this->current_user['nim']);
            $valid = !empty($record['password_hash']) ? password_verify($current, $record['password_hash']) : hash_equals(substr(trim($record['nim']), -6), $current);
            if (!$valid) return $this->respond(array('message' => 'Password saat ini tidak cocok.'), 422);
            if (strlen($new_password) < 8) return $this->respond(array('message' => 'Password baru minimal 8 karakter.'), 422);
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
        }
        $this->classroom_model->update_profile($this->current_user['nim'], $name, $hash);
        if ($hash) $this->classroom_model->revoke_profile_login_link($this->current_user['nim']);
        $this->current_user['display_name'] = $name;
        $this->session->set_userdata('webkelas_user', $this->current_user);
        return $this->respond(array('data' => $this->current_user, 'message' => 'Profil berhasil diperbarui.'));
    }

    public function profile_link()
    {
        if (!$this->require_user()) return;
        if ($this->request_method() !== 'POST') return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
        $payload = $this->payload();
        $action = (string) ($payload['action'] ?? 'create');
        if ($action === 'revoke') {
            $this->classroom_model->revoke_profile_login_link($this->current_user['nim']);
            return $this->respond(array('message' => 'Tautan masuk cepat sudah dicabut.'));
        }
        if ($action !== 'create') return $this->respond(array('message' => 'Aksi tautan tidak dikenal.'), 422);
        try { $token = bin2hex(random_bytes(32)); }
        catch (Exception $error) { return $this->respond(array('message' => 'Tautan aman belum dapat dibuat.'), 500); }
        $expires_at = $this->classroom_model->replace_profile_login_link($this->current_user['nim'], hash('sha256', $token));
        if (!$expires_at) return $this->respond(array('message' => 'Tautan masuk cepat belum dapat disimpan.'), 500);
        $this->output->set_header('Cache-Control: no-store, private');
        return $this->respond(array('data' => array('url' => site_url('auth/magic/' . $token), 'expires_at' => $expires_at), 'message' => 'Tautan berlaku 15 menit dan hanya dapat digunakan sekali.'));
    }

    private function require_user()
    {
        if ($this->current_user) return TRUE;
        $this->respond(array('message' => 'Sesi login berakhir. Silakan masuk kembali.'), 401);
        return FALSE;
    }

    public function courses($id = NULL)
    {
        if (!$this->require_user()) return;
        if ($this->request_method() !== 'GET') {
            return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
        }

        if ($id === NULL) {
            return $this->respond(array('data' => $this->course_model->all()));
        }

        $course = $this->course_model->find($id);
        return $course ? $this->respond(array('data' => $course)) : $this->respond(array('message' => 'Mata kuliah tidak ditemukan.'), 404);
    }

    public function tasks($id = NULL)
    {
        if (!$this->require_user()) return;
        $method = $this->request_method();

        if ($id === NULL && $method === 'GET') {
            $filters = array(
                'course_id' => $this->input->get('course_id', TRUE),
                'status' => $this->input->get('status', TRUE)
            );
            return $this->respond(array('data' => $this->task_model->all($filters)));
        }

        if ($id === NULL && $method === 'POST') {
            if ($this->current_user['role'] !== 'leader') return $this->respond(array('message' => 'Hanya ketua kelas yang dapat membuat tugas.'), 403);
            $payload = $this->payload();
            if (empty($payload['course_id']) || empty(trim((string) ($payload['title'] ?? '')))) {
                return $this->respond(array('message' => 'course_id dan title wajib diisi.'), 422);
            }

            $taskId = $this->task_model->create(array(
                'course_id' => $payload['course_id'],
                'title' => trim($payload['title']),
                'type' => trim($payload['type'] ?? 'Tugas'),
                'due_label' => trim($payload['due'] ?? 'Tanpa deadline'),
                'priority' => in_array($payload['priority'] ?? 'medium', array('high', 'medium', 'low'), TRUE) ? $payload['priority'] : 'medium',
                'status' => 'todo',
                'note' => trim($payload['note'] ?? '')
            ));
            return $this->respond(array('data' => $this->task_model->find($taskId)), 201);
        }

        if ($id !== NULL && in_array($method, array('PUT', 'PATCH'), TRUE)) {
            if ($this->current_user['role'] !== 'leader') return $this->respond(array('message' => 'Hanya ketua kelas yang dapat mengubah tugas.'), 403);
            $payload = $this->payload();
            $allowed = array();
            if (isset($payload['title'])) $allowed['title'] = trim($payload['title']);
            if (isset($payload['course_id'])) $allowed['course_id'] = $payload['course_id'];
            if (isset($payload['due'])) $allowed['due_label'] = trim($payload['due']);
            if (isset($payload['type'])) $allowed['type'] = trim($payload['type']);
            if (isset($payload['note'])) $allowed['note'] = trim($payload['note']);
            if (isset($payload['priority']) && in_array($payload['priority'], array('high', 'medium', 'low'), TRUE)) $allowed['priority'] = $payload['priority'];
            if (isset($payload['status']) && in_array($payload['status'], array('todo', 'progress', 'done'), TRUE)) $allowed['status'] = $payload['status'];
            if (!$allowed || !$this->task_model->update($id, $allowed)) {
                return $this->respond(array('message' => 'Tugas tidak ditemukan atau tidak ada perubahan valid.'), 422);
            }
            return $this->respond(array('data' => $this->task_model->find($id)));
        }

        if ($id !== NULL && $method === 'DELETE') {
            if ($this->current_user['role'] !== 'leader') return $this->respond(array('message' => 'Hanya ketua kelas yang dapat menghapus tugas.'), 403);
            if (!$this->task_model->delete($id)) {
                return $this->respond(array('message' => 'Tugas tidak ditemukan.'), 404);
            }
            return $this->respond(array('message' => 'Tugas berhasil dihapus.'));
        }

        return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
    }

    public function worklog()
    {
        if (!$this->require_user()) return;
        if ($this->request_method() !== 'GET') {
            return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
        }
        $rows = $this->db->order_by('logged_at', 'ASC')->get('worklogs')->result_array();
        return $this->respond(array('data' => $rows));
    }

    private function request_method()
    {
        return strtoupper($this->input->method(TRUE));
    }

    private function payload()
    {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, TRUE);
        return is_array($json) ? $json : ($this->input->post(NULL, TRUE) ?: array());
    }

    private function respond($payload, $status = 200)
    {
        return $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
