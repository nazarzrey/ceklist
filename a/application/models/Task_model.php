<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Task_model extends CI_Model
{
    private $select = 'tasks.id, tasks.course_id, tasks.title, tasks.type, tasks.due_label AS due, tasks.priority, tasks.status, tasks.note, tasks.created_at, tasks.updated_at, courses.name AS course_name, courses.short_name AS course_short';

    public function all($filters = array())
    {
        $this->db->select($this->select)->from('tasks')->join('courses', 'courses.id = tasks.course_id');
        if (!empty($filters['course_id'])) $this->db->where('tasks.course_id', $filters['course_id']);
        if (!empty($filters['status'])) $this->db->where('tasks.status', $filters['status']);
        return $this->db->order_by('FIELD(tasks.priority, "high", "medium", "low")', '', FALSE)->order_by('tasks.id', 'DESC')->get()->result_array();
    }

    public function find($id)
    {
        return $this->db->select($this->select)->from('tasks')->join('courses', 'courses.id = tasks.course_id')->where('tasks.id', $id)->get()->row_array();
    }

    public function create($data)
    {
        $this->db->insert('tasks', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('id', $id)->update('tasks', $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete('tasks');
    }
}
