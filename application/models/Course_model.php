<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course_model extends CI_Model
{
    public function all()
    {
        $rows = $this->db->order_by('start_time', 'ASC')->get('courses')->result_array();
        return array_map(array($this, 'hydrate'), $rows);
    }

    public function find($id)
    {
        $row = $this->db->where('id', $id)->get('courses')->row_array();
        return $row ? $this->hydrate($row) : NULL;
    }

    private function hydrate($row)
    {
        $row['time'] = substr($row['start_time'], 0, 5) . ' - ' . substr($row['end_time'], 0, 5);
        $row['group'] = (int) $row['group_no'];
        $row['sks'] = (int) $row['sks'];
        $row['uts'] = $row['uts_mode'];
        $row['uas'] = $row['uas_mode'];
        $row['links'] = $this->db->select('label, url')->where('course_id', $row['id'])->order_by('id', 'ASC')->get('course_links')->result_array();
        $row['notes'] = array_column($this->db->select('note')->where('course_id', $row['id'])->order_by('id', 'ASC')->get('course_notes')->result_array(), 'note');
        $row['meetings'] = $this->db->select('title, detail')->where('course_id', $row['id'])->order_by('meeting_no', 'ASC')->get('course_meetings')->result_array();
        return $row;
    }
}
