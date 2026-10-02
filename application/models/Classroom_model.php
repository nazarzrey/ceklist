<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Classroom_model extends CI_Model
{
    const CLASS_CODE = 'TPLE004';

    public function ensure_schema()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS webkelas_profiles (
            nim VARCHAR(50) NOT NULL,
            display_name VARCHAR(120) DEFAULT NULL,
            password_hash VARCHAR(255) DEFAULT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (nim)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS webkelas_login_links (
            nim VARCHAR(50) NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (nim), UNIQUE KEY uq_webkelas_login_token (token_hash), KEY idx_webkelas_login_expiry (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS class_announcements (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            course_id VARCHAR(50) DEFAULT NULL,
            title VARCHAR(180) NOT NULL,
            body TEXT NOT NULL,
            deadline_at DATETIME DEFAULT NULL,
            reference_url VARCHAR(500) DEFAULT NULL,
            source_url VARCHAR(500) DEFAULT NULL,
            created_by VARCHAR(50) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id), KEY idx_class_announcement_course (course_id), KEY idx_class_announcement_date (created_at),
            CONSTRAINT fk_class_announcement_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        if (!$this->db->field_exists('source_url', 'class_announcements')) {
            $this->db->query('ALTER TABLE class_announcements ADD source_url VARCHAR(500) DEFAULT NULL AFTER reference_url');
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS class_tasks (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            announcement_id INT UNSIGNED DEFAULT NULL,
            course_id VARCHAR(50) DEFAULT NULL,
            title VARCHAR(240) NOT NULL,
            due_label VARCHAR(100) DEFAULT NULL,
            reference_url VARCHAR(500) DEFAULT NULL,
            created_by VARCHAR(50) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id), KEY idx_class_task_announcement (announcement_id), KEY idx_class_task_course (course_id), KEY idx_class_task_creator (created_by),
            CONSTRAINT fk_class_task_announcement FOREIGN KEY (announcement_id) REFERENCES class_announcements(id) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_class_task_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS class_task_checks (
            task_id INT UNSIGNED NOT NULL,
            nim VARCHAR(50) NOT NULL,
            checked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (task_id, nim), KEY idx_class_task_check_nim (nim),
            CONSTRAINT fk_class_check_task FOREIGN KEY (task_id) REFERENCES class_tasks(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS class_announcement_reads (
            announcement_id INT UNSIGNED NOT NULL,
            nim VARCHAR(50) NOT NULL,
            read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (announcement_id, nim), KEY idx_announcement_read_nim (nim),
            CONSTRAINT fk_announcement_read_announcement FOREIGN KEY (announcement_id) REFERENCES class_announcements(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->seed_demo_data();
    }

    private function seed_demo_data()
    {
        $leader = $this->db->where(array('kelas' => self::CLASS_CODE, 'aktif' => '1', 'recid' => '1', 'tipe' => 'super'))->get('unpam_mahasiswa')->row_array();
        if (!$leader || !$this->db->table_exists('courses') || $this->db->count_all('courses') === 0) return;
        $leader_nim = trim($leader['nim']);

        if (!$this->db->where('title', 'Rekapan e-learning minggu ini')->count_all_results('class_announcements')) {
            $this->db->insert('class_announcements', array('course_id' => NULL, 'title' => 'Rekapan e-learning minggu ini', 'body' => "REKAPAN E-LEARNING MINGGU INI\n\n• Rekayasa Perangkat Lunak (Pertemuan 6)\n• Kerja Praktek (Pertemuan 4)\n• Teknologi Internet of Things (Pertemuan 4) — tenggat Jumat pukul 23.00 WIB\n• Basis Data II (Pertemuan 5 dan 6)\n• Mobile Programming (Pertemuan 5 dan 6)\n• Pemrograman II (Pertemuan 6)\n\nCatatan: untuk IoT, kerjakan tugas dan unggah forum diskusi sebagai laporan PDF. Untuk Basis Data II, gunakan SQL Server. Tanyakan ke ketua kelas jika instruksi belum jelas.", 'created_by' => $leader_nim));
            $announcement_id = $this->db->insert_id();
            $this->seed_task_rows($announcement_id, array(
                array('course_id' => NULL, 'title' => 'Buka dan cek rekap e-learning minggu ini', 'due_label' => 'Minggu ini'),
                array('course_id' => 'rpl', 'title' => 'Periksa tugas Rekayasa Perangkat Lunak pertemuan 6', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'kerja-praktek', 'title' => 'Periksa tugas Kerja Praktek pertemuan 4', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'iot', 'title' => 'Kerjakan tugas IoT pertemuan 4 dan unggah forum diskusi sebagai PDF', 'due_label' => 'Jumat 23.00 WIB'),
                array('course_id' => 'basis-data-2', 'title' => 'Selesaikan tugas Basis Data II pertemuan 5 menggunakan SQL Server', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'basis-data-2', 'title' => 'Selesaikan tugas Basis Data II pertemuan 6 menggunakan SQL Server', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'mobile-programming', 'title' => 'Selesaikan tugas Mobile Programming pertemuan 5', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'mobile-programming', 'title' => 'Selesaikan tugas Mobile Programming pertemuan 6', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'pemrograman-2', 'title' => 'Selesaikan tugas Pemrograman II pertemuan 6', 'due_label' => 'Jumat')
            ), $leader_nim);
        }
        if (!$this->db->where('title', 'Pemrograman II: aplikasi biaya sewa futsal')->count_all_results('class_announcements')) {
            $url = 'https://drive.google.com/drive/folders/1_EVXKVLRnH2i7Yh-SPeXYM_hnl-mCtz_?usp=sharing';
            $this->db->insert('class_announcements', array('course_id' => 'pemrograman-2', 'title' => 'Pemrograman II: aplikasi biaya sewa futsal', 'body' => 'Buat tampilan dan fungsi perhitungan biaya sewa lapangan futsal menggunakan Java Apache NetBeans. Referensi tampilan ada di grup mata kuliah yang dibagikan dosen. Kumpulkan melalui link di bawah sebelum hari Jumat.', 'deadline_at' => date('Y-m-d 23:00:00', strtotime('next Friday')), 'reference_url' => $url, 'created_by' => $leader_nim));
            $this->seed_task_rows($this->db->insert_id(), array(
                array('course_id' => 'pemrograman-2', 'title' => 'Buat tampilan form perhitungan sewa lapangan futsal', 'due_label' => 'Jumat', 'reference_url' => $url),
                array('course_id' => 'pemrograman-2', 'title' => 'Buat fungsi perhitungan biaya sewa menggunakan Java', 'due_label' => 'Jumat', 'reference_url' => $url),
                array('course_id' => 'pemrograman-2', 'title' => 'Uji beberapa skenario durasi dan biaya sewa', 'due_label' => 'Jumat', 'reference_url' => $url),
                array('course_id' => 'pemrograman-2', 'title' => 'Unggah hasil tugas ke Google Drive kelas', 'due_label' => 'Jumat', 'reference_url' => $url)
            ), $leader_nim);
        }
        if (!$this->db->where('title', 'Rekayasa Perangkat Lunak: makalah project KP')->count_all_results('class_announcements')) {
            $url = 'https://docs.google.com/spreadsheets/d/1ANEUjEOVsZ-hnZsyoN-3PZcgzd0zhyOr/edit?usp=sharing';
            $this->db->insert('class_announcements', array('course_id' => 'rpl', 'title' => 'Rekayasa Perangkat Lunak: makalah project KP', 'body' => 'Buat makalah dari project Kerja Praktek. Isi makalah mencakup judul, rumusan masalah, identifikasi masalah, latar belakang, metode, sampai model. Buat link Google Docs lalu masukkan ke kolom link pada spreadsheet kelas.', 'deadline_at' => date('Y-m-d 23:00:00', strtotime('next Friday')), 'reference_url' => $url, 'created_by' => $leader_nim));
            $this->seed_task_rows($this->db->insert_id(), array(
                array('course_id' => 'rpl', 'title' => 'Susun makalah project KP: judul hingga model', 'due_label' => 'Jumat', 'reference_url' => $url),
                array('course_id' => 'rpl', 'title' => 'Buat link Google Docs dan tambahkan ke kolom link GSheet', 'due_label' => 'Jumat', 'reference_url' => $url),
                array('course_id' => 'rpl', 'title' => 'Ketik tugas terstruktur pertemuan 6 dan unggah PDF ke Mentari', 'due_label' => 'Jumat')
            ), $leader_nim);
        }
        if (!$this->db->where('title', 'Tugas Teknik Kompilasi: 10 langkah belajar')->count_all_results('class_announcements')) {
            $this->db->insert('class_announcements', array('course_id' => 'teknik-kompilasi', 'title' => 'Tugas Teknik Kompilasi: 10 langkah belajar', 'body' => 'Checklist demo. Tanda selesai tersimpan untuk akun masing-masing mahasiswa dan tidak mengubah checklist teman sekelas.', 'created_by' => $leader_nim));
            $this->seed_task_rows($this->db->insert_id(), array(
                array('course_id' => 'teknik-kompilasi', 'title' => 'Baca materi analisis leksikal', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'teknik-kompilasi', 'title' => 'Buat ringkasan analisis sintaks', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'teknik-kompilasi', 'title' => 'Pelajari analisis semantik', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'teknik-kompilasi', 'title' => 'Gambar alur proses compiler', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'teknik-kompilasi', 'title' => 'Buat contoh token dan lexeme', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'teknik-kompilasi', 'title' => 'Jelaskan peran parser', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'teknik-kompilasi', 'title' => 'Coba contoh intermediate code', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'teknik-kompilasi', 'title' => 'Catat tahapan optimasi kode', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'teknik-kompilasi', 'title' => 'Pelajari code generator', 'due_label' => 'Sebelum pertemuan berikutnya'),
                array('course_id' => 'teknik-kompilasi', 'title' => 'Siapkan pertanyaan untuk diskusi kelas', 'due_label' => 'Sebelum pertemuan berikutnya')
            ), $leader_nim);
        }
    }

    private function seed_task_rows($announcement_id, $tasks, $leader_nim)
    {
        foreach ($tasks as $task) $this->db->insert('class_tasks', array('announcement_id' => $announcement_id, 'course_id' => $task['course_id'], 'title' => $task['title'], 'due_label' => $task['due_label'], 'reference_url' => $task['reference_url'] ?? NULL, 'created_by' => $leader_nim));
    }

    public function user_by_nim($nim)
    {
        $user = $this->db->where('nim', $nim)->where('kelas', self::CLASS_CODE)->where('aktif', '1')->where('recid', '1')->get('unpam_mahasiswa')->row_array();
        if (!$user) return NULL;
        $profile = $this->db->where('nim', $nim)->get('webkelas_profiles')->row_array();
        $user['nim'] = trim($user['nim']);
        $user['display_name'] = $profile && $profile['display_name'] ? $profile['display_name'] : trim($user['nama']);
        $user['password_hash'] = $profile ? $profile['password_hash'] : NULL;
        $user['role'] = trim($user['tipe']) === 'super' ? 'leader' : 'student';
        return $user;
    }

    public function public_user($user)
    {
        if (!$user) return NULL;
        return array('nim' => trim($user['nim']), 'display_name' => $user['display_name'], 'role' => $user['role']);
    }

    public function announcements()
    {
        return $this->db->select('a.*, c.name AS course_name, c.short_name AS course_short, COALESCE(p.display_name, m.nama) AS author, (SELECT COUNT(*) FROM class_tasks t WHERE t.announcement_id = a.id) AS task_total')
            ->from('class_announcements a')->join('courses c', 'c.id = a.course_id', 'left')->join('unpam_mahasiswa m', 'm.nim = a.created_by AND m.kelas = "' . self::CLASS_CODE . '"', 'left', FALSE)->join('webkelas_profiles p', 'p.nim = a.created_by', 'left')
            ->order_by('a.created_at', 'DESC')->get()->result_array();
    }

    public function announcements_for_user($nim)
    {
        return $this->db->select('a.*, c.name AS course_name, c.short_name AS course_short, COALESCE(p.display_name, m.nama) AS author, (SELECT COUNT(*) FROM class_tasks t WHERE t.announcement_id = a.id) AS task_total, IF(r.nim IS NULL, 0, 1) AS is_read')
            ->from('class_announcements a')->join('courses c', 'c.id = a.course_id', 'left')->join('unpam_mahasiswa m', 'm.nim = a.created_by AND m.kelas = "' . self::CLASS_CODE . '"', 'left', FALSE)->join('webkelas_profiles p', 'p.nim = a.created_by', 'left')->join('class_announcement_reads r', 'r.announcement_id = a.id AND r.nim = ' . $this->db->escape($nim), 'left', FALSE)
            ->order_by('a.created_at', 'DESC')->get()->result_array();
    }

    public function mark_announcement_read($announcement_id, $nim)
    {
        $this->db->query('INSERT IGNORE INTO class_announcement_reads (announcement_id, nim, read_at) VALUES (?, ?, NOW())', array((int) $announcement_id, $nim));
    }

    public function tasks_for_user($nim)
    {
        return $this->db->select('t.id, t.announcement_id, t.course_id, t.title, t.due_label, t.reference_url, t.created_at, c.name AS course_name, c.short_name AS course_short, a.title AS announcement_title, IF(ch.nim IS NULL, 0, 1) AS checked, (SELECT COUNT(*) FROM class_task_checks cc WHERE cc.task_id = t.id) AS checked_count, (SELECT COUNT(*) FROM unpam_mahasiswa sm WHERE sm.kelas = "' . self::CLASS_CODE . '" AND sm.aktif = "1" AND sm.recid = "1" AND sm.tipe <> "super") AS student_total')
            ->from('class_tasks t')->join('courses c', 'c.id = t.course_id', 'left')->join('class_announcements a', 'a.id = t.announcement_id', 'left')->join('class_task_checks ch', 'ch.task_id = t.id AND ch.nim = ' . $this->db->escape($nim), 'left', FALSE)
            ->order_by('t.created_at', 'DESC')->order_by('t.id', 'DESC')->get()->result_array();
    }

    public function create_announcement($payload, $nim)
    {
        $this->db->trans_start();
        $this->db->insert('class_announcements', array('course_id' => $payload['course_id'] ?: NULL, 'title' => $payload['title'], 'body' => $payload['body'], 'deadline_at' => $payload['deadline_at'], 'reference_url' => $payload['reference_url'] ?: NULL, 'source_url' => $payload['source_url'] ?: NULL, 'created_by' => $nim));
        $announcement_id = $this->db->insert_id();
        foreach ($payload['tasks'] as $task) {
            if (!is_array($task)) $task = array('title' => (string) $task);
            $this->db->insert('class_tasks', array(
                'announcement_id' => $announcement_id,
                'course_id' => !empty($task['course_id']) ? $task['course_id'] : ($payload['course_id'] ?: NULL),
                'title' => $task['title'],
                'due_label' => !empty($task['due_label']) ? $task['due_label'] : $payload['due_label'],
                'reference_url' => !empty($task['reference_url']) ? $task['reference_url'] : ($payload['reference_url'] ?: NULL),
                'created_by' => $nim
            ));
        }
        $this->db->trans_complete();
        return $this->db->trans_status() ? $announcement_id : FALSE;
    }

    public function toggle_check($task_id, $nim, $checked)
    {
        if ($checked) $this->db->query('INSERT IGNORE INTO class_task_checks (task_id, nim, checked_at) VALUES (?, ?, NOW())', array((int) $task_id, $nim));
        else $this->db->delete('class_task_checks', array('task_id' => (int) $task_id, 'nim' => $nim));
        return $this->db->where(array('task_id' => (int) $task_id, 'nim' => $nim))->count_all_results('class_task_checks') > 0;
    }

    public function task_exists($task_id)
    {
        return $this->db->where('id', (int) $task_id)->count_all_results('class_tasks') > 0;
    }

    public function update_profile($nim, $display_name, $password_hash = NULL)
    {
        $profile = $this->db->where('nim', $nim)->get('webkelas_profiles')->row_array();
        if ($profile) {
            $data = array('display_name' => $display_name);
            if ($password_hash) $data['password_hash'] = $password_hash;
            return $this->db->where('nim', $nim)->update('webkelas_profiles', $data);
        }
        return $this->db->insert('webkelas_profiles', array('nim' => $nim, 'display_name' => $display_name, 'password_hash' => $password_hash));
    }

    public function replace_profile_login_link($nim, $token_hash)
    {
        $this->db->query('DELETE FROM webkelas_login_links WHERE expires_at <= NOW()');
        $this->db->query('REPLACE INTO webkelas_login_links (nim, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))', array($nim, $token_hash));
        if ($this->db->affected_rows() < 1) return FALSE;
        $row = $this->db->select('expires_at')->where('nim', $nim)->get('webkelas_login_links')->row_array();
        return $row ? $row['expires_at'] : FALSE;
    }

    public function consume_profile_login_link($token)
    {
        if (!preg_match('/^[a-f0-9]{64}$/', (string) $token)) return NULL;
        $hash = hash('sha256', $token);
        $row = $this->db->query('SELECT nim FROM webkelas_login_links WHERE token_hash = ? AND expires_at > NOW() LIMIT 1', array($hash))->row_array();
        if (!$row) return NULL;
        $this->db->query('DELETE FROM webkelas_login_links WHERE nim = ? AND token_hash = ? AND expires_at > NOW()', array($row['nim'], $hash));
        return $this->db->affected_rows() === 1 ? $row['nim'] : NULL;
    }

    public function revoke_profile_login_link($nim)
    {
        return $this->db->delete('webkelas_login_links', array('nim' => $nim));
    }
}
