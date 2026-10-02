<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('course_model');
        $this->load->model('task_model');
    }

    public function courses($id = NULL)
    {
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
        $method = $this->request_method();

        if ($id === NULL && $method === 'GET') {
            $filters = array(
                'course_id' => $this->input->get('course_id', TRUE),
                'status' => $this->input->get('status', TRUE)
            );
            return $this->respond(array('data' => $this->task_model->all($filters)));
        }

        if ($id === NULL && $method === 'POST') {
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
            if (!$this->task_model->delete($id)) {
                return $this->respond(array('message' => 'Tugas tidak ditemukan.'), 404);
            }
            return $this->respond(array('message' => 'Tugas berhasil dihapus.'));
        }

        return $this->respond(array('message' => 'Method tidak diizinkan.'), 405);
    }

    public function worklog()
    {
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
