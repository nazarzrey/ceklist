<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Init_model — pemeriksaan dan seed tabel untuk WebKelas 06TPLE004.
 */
class Init_model extends CI_Model
{
    /**
     * Daftar skema tabel yang diharapkan aplikasi CI3.
     * Digunakan untuk CREATE IF NOT EXISTS.
     */
    private $schemas = [
        'courses' => <<<SQL
CREATE TABLE IF NOT EXISTS `courses` (
  `id` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL,
  `short_name` varchar(80) NOT NULL,
  `lecturer` varchar(120) NOT NULL,
  `sks` tinyint unsigned NOT NULL DEFAULT 0,
  `group_no` tinyint unsigned NOT NULL DEFAULT 1,
  `day` varchar(20) NOT NULL DEFAULT 'Sabtu',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `start_date` varchar(40) NOT NULL,
  `end_date` varchar(40) NOT NULL,
  `room` varchar(80) NOT NULL,
  `uts_mode` varchar(30) NOT NULL,
  `uas_mode` varchar(30) NOT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `color` varchar(20) NOT NULL DEFAULT 'purple',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_courses_code_group` (`code`, `group_no`),
  KEY `idx_courses_start_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL
        ,
        'course_links' => <<<SQL
CREATE TABLE IF NOT EXISTS `course_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` varchar(50) NOT NULL,
  `label` varchar(160) NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_links_course` (`course_id`),
  CONSTRAINT `fk_course_links_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL
        ,
        'course_notes' => <<<SQL
CREATE TABLE IF NOT EXISTS `course_notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` varchar(50) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_notes_course` (`course_id`),
  CONSTRAINT `fk_course_notes_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL
        ,
        'course_meetings' => <<<SQL
CREATE TABLE IF NOT EXISTS `course_meetings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` varchar(50) NOT NULL,
  `meeting_no` tinyint unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `detail` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_meetings_course` (`course_id`),
  CONSTRAINT `fk_course_meetings_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL
        ,
        'tasks' => <<<SQL
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` varchar(50) NOT NULL,
  `title` varchar(180) NOT NULL,
  `type` varchar(40) NOT NULL DEFAULT 'Tugas',
  `due_label` varchar(80) NOT NULL DEFAULT 'Tanpa deadline',
  `priority` enum('high','medium','low') NOT NULL DEFAULT 'medium',
  `status` enum('todo','progress','done') NOT NULL DEFAULT 'todo',
  `note` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tasks_course` (`course_id`),
  KEY `idx_tasks_status` (`status`),
  CONSTRAINT `fk_tasks_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL
        ,
        'worklogs' => <<<SQL
CREATE TABLE IF NOT EXISTS `worklogs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `logged_at` varchar(35) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(180) NOT NULL,
  `detail` text NOT NULL,
  `command` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_worklogs_logged_at` (`logged_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL
        ,
    ];

    /**
     * Daftar seed data courses / course_links / course_notes / course_meetings.
     * Seluruh baris menggunakan INSERT IGNORE agar aman dijalankan berulang.
     */
    private $seed_courses = [
        ['id'=>'teknik-kompilasi','code'=>'22TIF3012','name'=>'Teknik Kompilasi','short_name'=>'Teknik Kompilasi','lecturer'=>'Aulia Ikhsan M.Kom','sks'=>2,'group_no'=>1,'day'=>'Sabtu','start_time'=>'09:20:00','end_time'=>'11:00:00','start_date'=>'31 Agustus 2026','end_date'=>'31 Januari 2027','room'=>'VIKTOR, V.708','uts_mode'=>'TATAP MUKA','uas_mode'=>'ONLINE','whatsapp'=>'081290167520','color'=>'purple'],
        ['id'=>'spk','code'=>'22TIF2012','name'=>'Sistem Pendukung Keputusan','short_name'=>'SPK','lecturer'=>'Ahmad Fauzi S.Kom., M.Kom.','sks'=>2,'group_no'=>1,'day'=>'Sabtu','start_time'=>'11:00:00','end_time'=>'13:50:00','start_date'=>'31 Agustus 2026','end_date'=>'31 Januari 2027','room'=>'VIKTOR, V.708','uts_mode'=>'TATAP MUKA','uas_mode'=>'ONLINE','whatsapp'=>'081210094491','color'=>'teal'],
        ['id'=>'pemrograman-2','code'=>'22TIF0353','name'=>'Pemrograman II','short_name'=>'Pemrograman II','lecturer'=>'Niki Ratama M.Kom','sks'=>3,'group_no'=>1,'day'=>'Sabtu','start_time'=>'13:50:00','end_time'=>'15:30:00','start_date'=>'31 Agustus 2026','end_date'=>'31 Januari 2027','room'=>'VIKTOR, V.708','uts_mode'=>'TATAP MUKA','uas_mode'=>'ONLINE','whatsapp'=>'081294507444','color'=>'orange'],
        ['id'=>'basis-data-2','code'=>'22TIF0363','name'=>'Basis Data II','short_name'=>'Basis Data II','lecturer'=>'Dola Irwanto S.Kom., M.Msi','sks'=>3,'group_no'=>2,'day'=>'Sabtu','start_time'=>'07:40:00','end_time'=>'09:20:00','start_date'=>'31 Agustus 2026','end_date'=>'31 Januari 2027','room'=>'VIKTOR, V.708','uts_mode'=>'ONLINE','uas_mode'=>'TATAP MUKA','whatsapp'=>NULL,'color'=>'blue'],
        ['id'=>'iot','code'=>'22TIF0342','name'=>'Teknologi Internet of Things','short_name'=>'Internet of Things','lecturer'=>'Fitri Nurlaela S.T., M.Kom.','sks'=>2,'group_no'=>2,'day'=>'Sabtu','start_time'=>'11:00:00','end_time'=>'13:50:00','start_date'=>'31 Agustus 2026','end_date'=>'31 Januari 2027','room'=>'VIKTOR, V.708','uts_mode'=>'ONLINE','uas_mode'=>'TATAP MUKA','whatsapp'=>NULL,'color'=>'pink'],
        ['id'=>'kerja-praktek','code'=>'22TIF0332','name'=>'Kerja Praktek','short_name'=>'Kerja Praktek','lecturer'=>'Wasis Haryono M.Kom','sks'=>2,'group_no'=>2,'day'=>'Sabtu','start_time'=>'13:50:00','end_time'=>'15:30:00','start_date'=>'31 Agustus 2026','end_date'=>'31 Januari 2027','room'=>'VIKTOR, V.708','uts_mode'=>'ONLINE','uas_mode'=>'TATAP MUKA','whatsapp'=>NULL,'color'=>'teal'],
        ['id'=>'mobile-programming','code'=>'22TIF0443','name'=>'Mobile Programming','short_name'=>'Mobile Programming','lecturer'=>'Septa S.Kom., M.Kom.','sks'=>3,'group_no'=>2,'day'=>'Sabtu','start_time'=>'16:00:00','end_time'=>'17:40:00','start_date'=>'31 Agustus 2026','end_date'=>'31 Januari 2027','room'=>'VIKTOR, V.708','uts_mode'=>'ONLINE','uas_mode'=>'TATAP MUKA','whatsapp'=>NULL,'color'=>'purple'],
        ['id'=>'rpl','code'=>'22TIF0323','name'=>'Rekayasa Perangkat Lunak','short_name'=>'RPL','lecturer'=>'Joko Suwarno S.Kom., M.Kom.','sks'=>3,'group_no'=>1,'day'=>'Sabtu','start_time'=>'16:00:00','end_time'=>'17:40:00','start_date'=>'31 Agustus 2026','end_date'=>'31 Januari 2027','room'=>'VIKTOR, V.708','uts_mode'=>'TATAP MUKA','uas_mode'=>'ONLINE','whatsapp'=>'081280616831','color'=>'orange'],
    ];

    private $seed_course_links = [
        ['course_id'=>'teknik-kompilasi','label'=>'Folder materi di Google Drive','url'=>'https://drive.google.com/drive/folders/1jID0YzwVQZDknrvOrUmrNdZ-USX-RfTk?usp=sharing'],
        ['course_id'=>'spk','label'=>'Link grup WhatsApp','url'=>'https://chat.whatsapp.com/CatGzZeLCLjBZiOQj71Rur?s=qs&p=i&mlu=4&ilr=4'],
        ['course_id'=>'spk','label'=>'Materi pertemuan 1','url'=>'https://online.fliphtml5.com/fvmbf/bab1_filosofi_keputusan/#google_vignette'],
        ['course_id'=>'spk','label'=>'Materi pertemuan 2','url'=>'https://online.fliphtml5.com/fvmbf/bab2_arsitektur_dss_v2/#google_vignette'],
        ['course_id'=>'pemrograman-2','label'=>'Download NetBeans','url'=>'https://www.codelerity.com/netbeans/'],
        ['course_id'=>'pemrograman-2','label'=>'Materi di Google Drive','url'=>'https://drive.google.com/drive/folders/1DksbApYoXC748yt1UnzmsappYHiJQZ6-'],
        ['course_id'=>'pemrograman-2','label'=>'Folder laporan pertemuan','url'=>'https://drive.google.com/drive/folders/16acGEdQFWXIaTyuG5NbNyoCUSU9Py7Cd?usp=sharing'],
        ['course_id'=>'rpl','label'=>'Spreadsheet pengumpulan tugas kelompok','url'=>'https://docs.google.com/spreadsheets/d/15B_NbOVLHFwQIdGzhE-XevQ7ZL06xx_y48swdOV-dSM/edit?usp=sharing'],
    ];

    private $seed_course_notes = [
        ['course_id'=>'teknik-kompilasi','note'=>'Utamakan adab dan keaktifan di kelas.'],
        ['course_id'=>'teknik-kompilasi','note'=>'Saat e-learning, jawab topik forum diskusi dosen terlebih dahulu sebelum bertanya atau menjawab teman.'],
        ['course_id'=>'teknik-kompilasi','note'=>'Quiz dadakan dapat menghasilkan Bintang. 5 Bintang berarti boleh tidak mengikuti UAS; 3 Bintang berarti cukup menjawab 2 dari 5 soal UAS.'],
        ['course_id'=>'teknik-kompilasi','note'=>'Pengajuan izin kerja atau sakit perlu disertai bukti yang relevan dan dikirim ke nomor dosen.'],
        ['course_id'=>'spk','note'=>'Tugas berupa kelompok yang terdiri dari 5 orang.'],
        ['course_id'=>'spk','note'=>'Buat project aplikasi menggunakan metode apa pun; kumpulkan link repository GitHub dan makalah sebelum UAS.'],
        ['course_id'=>'spk','note'=>'Konfirmasi kepada dosen terkait tugas membaca jurnal ilmiah.'],
        ['course_id'=>'pemrograman-2','note'=>'Wajib membawa laptop saat perkuliahan.'],
        ['course_id'=>'pemrograman-2','note'=>'Laporan tugas dikumpulkan dalam bentuk PDF ke folder Google Drive tiap pertemuan.'],
        ['course_id'=>'pemrograman-2','note'=>'Siapkan NetBeans dan XAMPP atau Laragon untuk implementasi dengan MySQL.'],
        ['course_id'=>'pemrograman-2','note'=>'AI diperbolehkan sebagai bagian dari observasi materi/tugas, tetapi mahasiswa tetap harus memahami kodenya.'],
        ['course_id'=>'rpl','note'=>'Pengumpulan tugas project per kelompok mengikuti kelompok Kerja Praktek.'],
    ];

    private $seed_course_meetings = [
        ['course_id'=>'teknik-kompilasi','meeting_no'=>1,'title'=>'Tahapan kompilasi','detail'=>'Source program → analisa leksikal → sintaks → semantik → intermediate code → optimasi → code generator → target program.'],
        ['course_id'=>'teknik-kompilasi','meeting_no'=>2,'title'=>'Translator','detail'=>'Assembler, compiler, dan interpreter.'],
        ['course_id'=>'teknik-kompilasi','meeting_no'=>3,'title'=>'Catatan belajar','detail'=>'Siapkan catatan materi karena jawaban UTS/UAS banyak merujuk pada catatan kelas.'],
        ['course_id'=>'spk','meeting_no'=>1,'title'=>'Pertemuan 1','detail'=>'Baca materi filosofi keputusan dan tunggu pembagian kelompok oleh ketua kelas.'],
        ['course_id'=>'spk','meeting_no'=>2,'title'=>'Pertemuan 2','detail'=>'Baca jurnal ilmiah dan materi arsitektur DSS; konfirmasi detail tugas ke dosen.'],
        ['course_id'=>'pemrograman-2','meeting_no'=>1,'title'=>'Pertemuan 1','detail'=>'Buat flowchart dari code do-while dengan 3 statement dan 1 kondisi menggunakan Java.'],
        ['course_id'=>'pemrograman-2','meeting_no'=>2,'title'=>'Pertemuan 2','detail'=>'Buat laporan struktur proyek Java dan JFrame Form pada package latihan.'],
        ['course_id'=>'pemrograman-2','meeting_no'=>3,'title'=>'Format proyek','detail'=>'Gunakan penamaan proyek/package sesuai format dosen dan upload repository GitHub per pertemuan.'],
    ];

    /**
     * Pastikan seluruh tabel yang dibutuhkan aplikasi sudah ada.
     */
    public function ensure_tables()
    {
        foreach ($this->schemas as $sql) {
            $this->db->query($sql);
        }
    }

    /**
     * Isi seed data jika tabel masih kosong (INSERT IGNORE).
     */
    public function seed_data()
    {
        if ($this->db->count_all('courses') === 0) {
            foreach ($this->seed_courses as $row) {
                $this->db->insert('courses', $row);
            }
        }

        if ($this->db->count_all('course_links') === 0) {
            foreach ($this->seed_course_links as $row) {
                $this->db->insert('course_links', $row);
            }
        }

        if ($this->db->count_all('course_notes') === 0) {
            foreach ($this->seed_course_notes as $row) {
                $this->db->insert('course_notes', $row);
            }
        }

        if ($this->db->count_all('course_meetings') === 0) {
            foreach ($this->seed_course_meetings as $row) {
                $this->db->insert('course_meetings', $row);
            }
        }

        // Tasks dan worklogs di-seed melalui file database/webkelas.sql,
        // bukan di sini, agar tidak tertimpa saat inisialisasi ulang.
    }

    /**
     * Kembalikan status inspeksi cepat untuk tampilan dashboard init.
     */
    public function inspect_state()
    {
        $tables = [
            'courses'          => 'Diperlukan oleh Course_model, Api::courses, dashboard, jadwal, katalog.',
            'course_links'     => 'Diperlukan oleh Course_model::hydrate (links per mata kuliah).',
            'course_notes'     => 'Diperlukan oleh Course_model::hydrate (notes per mata kuliah).',
            'course_meetings'  => 'Diperlukan oleh Course_model::hydrate (pertemuan per mata kuliah).',
            'tasks'            => 'Diperlukan oleh Task_model, Api::tasks (CRUD tugas).',
            'worklogs'         => 'Diperlukan oleh Api::worklog dan view worklog.',
            'unpam_mahasiswa' => 'Sumber login dan profil dasar mahasiswa kelas TPLE004; harus tersedia dari database kampus.',
            'webkelas_profiles' => 'Nama tampilan dan password baru khusus WebKelas; tidak mengubah roster kampus.',
            'webkelas_login_links' => 'Hash tautan masuk cepat profil yang sekali pakai dan kedaluwarsa dalam 15 menit.',
            'class_announcements' => 'Broadcast kelas dari akun dengan tipe super.',
            'class_tasks' => 'Item checklist tugas bersama, dapat dikaitkan ke mata kuliah atau info global.',
            'class_task_checks' => 'Tanda selesai per mahasiswa dan item checklist.',
            'class_announcement_reads' => 'Status baca info kelas per mahasiswa.',
        ];

        $state = [];
        foreach (array_keys($tables) as $name) {
            $exists = $this->db->table_exists($name);
            $count  = $exists ? (int) $this->db->count_all($name) : 0;
            $state[$name] = [
                'exists'  => $exists,
                'count'   => $count,
                'purpose' => $tables[$name],
                'action'  => $exists ? ($count > 0 ? 'Sudah ada dan berisi data' : 'Ada, namun kosong — perlu diisi') : 'Belum ada — akan dibuat saat halaman ini dibuka',
            ];
        }

        return $state;
    }
}
