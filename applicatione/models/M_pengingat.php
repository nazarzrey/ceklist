<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_pengingat extends CI_Model {

    private $table = 'jurnal_new_reminders';
    private $history_table = 'jurnal_new_reminder_history';

    public function __construct() {
        parent::__construct();
        $this->ensure_table();
    }

    private function ensure_table() {
        $this->load->dbforge();

        if (!$this->db->table_exists($this->table)) {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ],
                'nama' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => FALSE
                ],
                'catatan' => [
                    'type' => 'TEXT',
                    'null' => TRUE
                ],
                'kategori' => [
                    'type' => 'VARCHAR', 'constraint' => 20, 'default' => 'lainnya', 'null' => FALSE
                ],
                'km_terakhir' => [
                    'type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE
                ],
                'km_awal' => [
                    'type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE
                ],
                'tanggal_mulai' => [
                    'type' => 'DATE',
                    'null' => FALSE
                ],
                'next_date' => [
                    'type' => 'DATE',
                    'null' => FALSE
                ],
                'cycle_months' => [
                    'type' => 'INT',
                    'constraint' => 3,
                    'default' => 1,
                    'null' => FALSE
                ],
                'last_done_at' => [
                    'type' => 'DATETIME',
                    'null' => TRUE
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                    'null' => FALSE
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => FALSE
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => TRUE
                ]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table($this->table, TRUE);
        }

        if (!$this->db->field_exists('is_active', $this->table)) {
            $this->dbforge->add_column($this->table, [
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                    'null' => FALSE
                ]
            ]);
        }
        if (!$this->db->field_exists('kategori', $this->table)) {
            $this->dbforge->add_column($this->table, ['kategori' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'lainnya', 'null' => FALSE]]);
        }
        if (!$this->db->field_exists('km_terakhir', $this->table)) {
            $this->dbforge->add_column($this->table, ['km_terakhir' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE]]);
        }
        if (!$this->db->field_exists('km_awal', $this->table)) {
            $this->dbforge->add_column($this->table, ['km_awal' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE]]);
        }
        if ($this->db->table_exists($this->history_table) && !$this->db->field_exists('checklist_only', $this->history_table)) {
            $this->dbforge->add_column($this->history_table, ['checklist_only' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE]]);
        }
        if ($this->db->table_exists($this->history_table) && !$this->db->field_exists('is_baseline', $this->history_table)) {
            $this->dbforge->add_column($this->history_table, ['is_baseline' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE]]);
        }
        if (!$this->db->table_exists($this->history_table)) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'reminder_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'null' => FALSE],
                'nama' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => FALSE],
                'kategori' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => FALSE],
                'km_terakhir' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
                'checklist_only' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE],
                'is_baseline' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE],
                'due_date' => ['type' => 'DATE', 'null' => FALSE],
                'completed_at' => ['type' => 'DATETIME', 'null' => FALSE]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table($this->history_table, TRUE);
        }
    }

    public function get_all($user_id) {
        $rows = $this->db->where('user_id', $user_id)
            ->where('is_active', 1)
            ->order_by('next_date', 'ASC')
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result();

        foreach ($rows as $row) {
            $row->status_badge = $this->get_status_badge($row->next_date);
            $row->remaining_days = (int) (new DateTime(date('Y-m-d')))->diff(new DateTime($row->next_date))->format('%r%a');
            $row->history_count = $this->db->where('reminder_id', $row->id)
                ->where('user_id', $user_id)->count_all_results($this->history_table);
            $row->is_dummy = strpos((string) $row->catatan, 'Dummy ') === 0;
        }

        return $rows;
    }

    public function get_by_id($id, $user_id) {
        return $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->get($this->table)
            ->row();
    }

    public function get_due($user_id) {
        return $this->db->where('user_id', $user_id)
            ->where('is_active', 1)
            ->where('next_date <=', date('Y-m-d'))
            ->order_by('next_date', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_history($id, $user_id) {
        $rows = $this->db->where('reminder_id', $id)->where('user_id', $user_id)
            ->order_by('completed_at', 'DESC')->get($this->history_table)->result();
        $reminder = $this->db->where('id', $id)->where('user_id', $user_id)->get($this->table)->row();

        $previous_completed_date = null;
        foreach ($rows as $row) {
            $row->tanggal_mulai = $reminder ? $reminder->tanggal_mulai : null;
            $row->selisih_km = null;
            $row->selisih_hari = null;
            $row->selisih_bulan = null;
            $row->sisa_hari = null;
            $completed_date = !empty($row->checklist_only)
                ? $row->due_date
                : substr($row->completed_at, 0, 10);
            if ($previous_completed_date !== null) {
                $date_diff = (new DateTime($completed_date))->diff(new DateTime($previous_completed_date));
                $row->selisih_hari = $date_diff->days;
                $row->selisih_bulan = ($date_diff->y * 12) + $date_diff->m;
                $row->sisa_hari = $date_diff->d;
            }
            $previous_completed_date = $completed_date;
        }

        // Hitung dari urutan terlama ke terbaru agar riwayat tanpa KM
        // tidak memutus perbandingan dengan KM numerik terakhir.
        $previous_km = $reminder && $reminder->km_awal !== null ? (int) $reminder->km_awal : null;
        foreach (array_reverse($rows) as $row) {
            if ($row->km_terakhir !== null && $previous_km !== null) {
                $row->selisih_km = abs((int) $row->km_terakhir - $previous_km);
            }
            if ($row->km_terakhir !== null) $previous_km = (int) $row->km_terakhir;
        }
        return $rows;
    }

    public function update_history($id, $user_id, $km_terakhir, $completed_at) {
        $row = $this->db->where('id', $id)->where('user_id', $user_id)->get($this->history_table)->row();
        if (!$row) return false;

        $timestamp = strtotime($completed_at);
        $completed_at = $timestamp ? date('Y-m-d H:i:s', $timestamp) : $row->completed_at;
        $this->db->where('id', $id)->where('user_id', $user_id)->update($this->history_table, [
            'km_terakhir' => $km_terakhir, 'checklist_only' => $km_terakhir === null ? 1 : 0, 'completed_at' => $completed_at
        ]);
        $this->sync_reminder_after_history_change($row->reminder_id, $user_id);
        return true;
    }

    public function delete_history($id, $user_id) {
        $row = $this->db->where('id', $id)->where('user_id', $user_id)->get($this->history_table)->row();
        if (!$row) return false;

        $this->db->where('id', $id)->where('user_id', $user_id)->delete($this->history_table);
        $this->sync_reminder_after_history_change($row->reminder_id, $user_id);
        return true;
    }

    private function sync_reminder_after_history_change($reminder_id, $user_id) {
        $latest = $this->db->where('reminder_id', $reminder_id)->where('user_id', $user_id)
            ->order_by('completed_at', 'DESC')->get($this->history_table)->row();
        if (!$latest) return;

        $reminder = $this->db->where('id', $reminder_id)->where('user_id', $user_id)->get($this->table)->row();
        if (!$reminder) return;

        $latest_base_date = !empty($latest->checklist_only)
            ? $latest->due_date
            : substr($latest->completed_at, 0, 10);
        $next = new DateTime($latest_base_date);
        $next->modify('+' . max(1, (int) $reminder->cycle_months) . ' month');

        $this->db->where('id', $reminder_id)->where('user_id', $user_id)
            ->update($this->table, [
                'next_date' => $next->format('Y-m-d'),
                'last_done_at' => $latest->completed_at,
                'km_terakhir' => $latest->km_terakhir !== null ? $latest->km_terakhir : $reminder->km_awal,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function insert($data) {
        $data['cycle_months'] = max(1, (int) $data['cycle_months']);
        $data['next_date'] = $this->normalize_next_date($data['tanggal_mulai'], $data['cycle_months']);
        $data['is_active'] = 1;
        $data['km_awal'] = ($data['kategori'] === 'kendaraan' && $data['km_terakhir'] !== null) ? $data['km_terakhir'] : null;
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();
        if ($data['kategori'] === 'kendaraan' && $data['km_terakhir'] !== null) {
            $this->db->insert($this->history_table, [
                'reminder_id' => $id, 'user_id' => $data['user_id'], 'nama' => $data['nama'],
                'kategori' => $data['kategori'], 'km_terakhir' => $data['km_terakhir'],
                'checklist_only' => 0, 'is_baseline' => 1,
                'due_date' => $data['tanggal_mulai'],
                'completed_at' => $data['tanggal_mulai'] . ' 00:00:00'
            ]);
        }
        return $id;
    }

    public function update($id, $data, $user_id) {
        $current = $this->get_by_id($id, $user_id);
        $data['cycle_months'] = max(1, (int) $data['cycle_months']);
        $data['next_date'] = $this->normalize_next_date($data['tanggal_mulai'], $data['cycle_months']);
        if ($current && $current->km_awal === null && $data['kategori'] === 'kendaraan' && $data['km_terakhir'] !== null) {
            $data['km_awal'] = $data['km_terakhir'];
            $this->db->insert($this->history_table, [
                'reminder_id' => $id, 'user_id' => $user_id, 'nama' => $current->nama,
                'kategori' => 'kendaraan', 'km_terakhir' => $data['km_terakhir'],
                'checklist_only' => 0, 'is_baseline' => 1,
                'due_date' => $current->tanggal_mulai,
                'completed_at' => $current->tanggal_mulai . ' 00:00:00'
            ]);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function mark_done($id, $user_id, $km_terakhir = null) {
        $row = $this->get_by_id($id, $user_id);
        if (!$row) {
            return false;
        }

        $next = new DateTime(date('Y-m-d'));
        $months = max(1, (int) $row->cycle_months);

        $completed_at = date('Y-m-d H:i:s');
        $this->db->insert($this->history_table, [
            'reminder_id' => $row->id, 'user_id' => $user_id, 'nama' => $row->nama,
            'kategori' => $row->kategori, 'km_terakhir' => $km_terakhir,
            'checklist_only' => $km_terakhir === null ? 1 : 0,
            'due_date' => $row->next_date, 'completed_at' => $completed_at
        ]);

        $next->modify('+' . $months . ' month');

        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table, [
                'next_date' => $next->format('Y-m-d'),
                'last_done_at' => $completed_at,
                'km_terakhir' => $km_terakhir === null ? $row->km_terakhir : $km_terakhir,
                'updated_at' => $completed_at
            ]);

        return true;
    }

    private function normalize_next_date($tanggal_mulai, $cycle_months) {
        $next = new DateTime($tanggal_mulai);
        $today = new DateTime(date('Y-m-d'));
        while ($next < $today) {
            $next->modify('+' . max(1, (int) $cycle_months) . ' month');
        }
        return $next->format('Y-m-d');
    }

    public function delete($id, $user_id) {
        $this->db->where('reminder_id', $id)
            ->where('user_id', $user_id)
            ->delete($this->history_table);
        $this->db->where('id', $id)
            ->where('user_id', $user_id)
            ->update($this->table, ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        return $this->db->affected_rows();
    }

    private function get_status_badge($next_date) {
        $today = new DateTime(date('Y-m-d'));
        $due = new DateTime($next_date);
        $diff = (int) $today->diff($due)->format('%r%a');

        if ($diff <= 0) {
            return ['class' => 'danger', 'label' => $diff < 0 ? 'Lewat' : 'Hari ini'];
        }

        if ($diff <= 3) {
            return ['class' => 'warning', 'label' => $diff . ' hari lagi'];
        }

        return ['class' => 'success', 'label' => $diff . ' hari lagi'];
    }
}
