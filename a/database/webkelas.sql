-- WebKelas 06TPLE004 | MySQL 8 / MariaDB
-- Konfigurasi mengikuti readme.md: localhost:3308, user webkelas, database webkelas.

CREATE DATABASE IF NOT EXISTS `webkelas` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `webkelas`;

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

CREATE TABLE IF NOT EXISTS `course_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` varchar(50) NOT NULL,
  `label` varchar(160) NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_links_course` (`course_id`),
  CONSTRAINT `fk_course_links_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `course_notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` varchar(50) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_notes_course` (`course_id`),
  CONSTRAINT `fk_course_notes_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

INSERT IGNORE INTO `courses` (`id`, `code`, `name`, `short_name`, `lecturer`, `sks`, `group_no`, `day`, `start_time`, `end_time`, `start_date`, `end_date`, `room`, `uts_mode`, `uas_mode`, `whatsapp`, `color`) VALUES
('teknik-kompilasi', '22TIF3012', 'Teknik Kompilasi', 'Teknik Kompilasi', 'Aulia Ikhsan M.Kom', 2, 1, 'Sabtu', '09:20:00', '11:00:00', '31 Agustus 2026', '31 Januari 2027', 'VIKTOR, V.708', 'TATAP MUKA', 'ONLINE', '081290167520', 'purple'),
('spk', '22TIF2012', 'Sistem Pendukung Keputusan', 'SPK', 'Ahmad Fauzi S.Kom., M.Kom.', 2, 1, 'Sabtu', '11:00:00', '13:50:00', '31 Agustus 2026', '31 Januari 2027', 'VIKTOR, V.708', 'TATAP MUKA', 'ONLINE', '081210094491', 'teal'),
('pemrograman-2', '22TIF0353', 'Pemrograman II', 'Pemrograman II', 'Niki Ratama M.Kom', 3, 1, 'Sabtu', '13:50:00', '15:30:00', '31 Agustus 2026', '31 Januari 2027', 'VIKTOR, V.708', 'TATAP MUKA', 'ONLINE', '081294507444', 'orange'),
('basis-data-2', '22TIF0363', 'Basis Data II', 'Basis Data II', 'Dola Irwanto S.Kom., M.Msi', 3, 2, 'Sabtu', '07:40:00', '09:20:00', '31 Agustus 2026', '31 Januari 2027', 'VIKTOR, V.708', 'ONLINE', 'TATAP MUKA', NULL, 'blue'),
('iot', '22TIF0342', 'Teknologi Internet of Things', 'Internet of Things', 'Fitri Nurlaela S.T., M.Kom.', 2, 2, 'Sabtu', '11:00:00', '13:50:00', '31 Agustus 2026', '31 Januari 2027', 'VIKTOR, V.708', 'ONLINE', 'TATAP MUKA', NULL, 'pink'),
('kerja-praktek', '22TIF0332', 'Kerja Praktek', 'Kerja Praktek', 'Wasis Haryono M.Kom', 2, 2, 'Sabtu', '13:50:00', '15:30:00', '31 Agustus 2026', '31 Januari 2027', 'VIKTOR, V.708', 'ONLINE', 'TATAP MUKA', NULL, 'teal'),
('mobile-programming', '22TIF0443', 'Mobile Programming', 'Mobile Programming', 'Septa S.Kom., M.Kom.', 3, 2, 'Sabtu', '16:00:00', '17:40:00', '31 Agustus 2026', '31 Januari 2027', 'VIKTOR, V.708', 'ONLINE', 'TATAP MUKA', NULL, 'purple'),
('rpl', '22TIF0323', 'Rekayasa Perangkat Lunak', 'RPL', 'Joko Suwarno S.Kom., M.Kom.', 3, 1, 'Sabtu', '16:00:00', '17:40:00', '31 Agustus 2026', '31 Januari 2027', 'VIKTOR, V.708', 'TATAP MUKA', 'ONLINE', '081280616831', 'orange');

INSERT IGNORE INTO `course_links` (`course_id`, `label`, `url`) VALUES
('teknik-kompilasi', 'Folder materi di Google Drive', 'https://drive.google.com/drive/folders/1jID0YzwVQZDknrvOrUmrNdZ-USX-RfTk?usp=sharing'),
('spk', 'Link grup WhatsApp', 'https://chat.whatsapp.com/CatGzZeLCLjBZiOQj71Rur?s=qs&p=i&mlu=4&ilr=4'),
('spk', 'Materi pertemuan 1', 'https://online.fliphtml5.com/fvmbf/bab1_filosofi_keputusan/#google_vignette'),
('spk', 'Materi pertemuan 2', 'https://online.fliphtml5.com/fvmbf/bab2_arsitektur_dss_v2/#google_vignette'),
('pemrograman-2', 'Download NetBeans', 'https://www.codelerity.com/netbeans/'),
('pemrograman-2', 'Materi di Google Drive', 'https://drive.google.com/drive/folders/1DksbApYoXC748yt1UnzmsappYHiJQZ6-'),
('pemrograman-2', 'Folder laporan pertemuan', 'https://drive.google.com/drive/folders/16acGEdQFWXIaTyuG5NbNyoCUSU9Py7Cd?usp=sharing'),
('rpl', 'Spreadsheet pengumpulan tugas kelompok', 'https://docs.google.com/spreadsheets/d/15B_NbOVLHFwQIdGzhE-XevQ7ZL06xx_y48swdOV-dSM/edit?usp=sharing');

INSERT IGNORE INTO `course_notes` (`course_id`, `note`) VALUES
('teknik-kompilasi', 'Utamakan adab dan keaktifan di kelas.'),
('teknik-kompilasi', 'Saat e-learning, jawab topik forum diskusi dosen terlebih dahulu sebelum bertanya atau menjawab teman.'),
('teknik-kompilasi', 'Quiz dadakan dapat menghasilkan Bintang. 5 Bintang berarti boleh tidak mengikuti UAS; 3 Bintang berarti cukup menjawab 2 dari 5 soal UAS.'),
('teknik-kompilasi', 'Pengajuan izin kerja atau sakit perlu disertai bukti yang relevan dan dikirim ke nomor dosen.'),
('spk', 'Tugas berupa kelompok yang terdiri dari 5 orang.'),
('spk', 'Buat project aplikasi menggunakan metode apa pun; kumpulkan link repository GitHub dan makalah sebelum UAS.'),
('spk', 'Konfirmasi kepada dosen terkait tugas membaca jurnal ilmiah.'),
('pemrograman-2', 'Wajib membawa laptop saat perkuliahan.'),
('pemrograman-2', 'Laporan tugas dikumpulkan dalam bentuk PDF ke folder Google Drive tiap pertemuan.'),
('pemrograman-2', 'Siapkan NetBeans dan XAMPP atau Laragon untuk implementasi dengan MySQL.'),
('pemrograman-2', 'AI diperbolehkan sebagai bagian dari observasi materi/tugas, tetapi mahasiswa tetap harus memahami kodenya.'),
('rpl', 'Pengumpulan tugas project per kelompok mengikuti kelompok Kerja Praktek.');

INSERT IGNORE INTO `course_meetings` (`course_id`, `meeting_no`, `title`, `detail`) VALUES
('teknik-kompilasi', 1, 'Tahapan kompilasi', 'Source program → analisa leksikal → sintaks → semantik → intermediate code → optimasi → code generator → target program.'),
('teknik-kompilasi', 2, 'Translator', 'Assembler, compiler, dan interpreter.'),
('teknik-kompilasi', 3, 'Catatan belajar', 'Siapkan catatan materi karena jawaban UTS/UAS banyak merujuk pada catatan kelas.'),
('spk', 1, 'Pertemuan 1', 'Baca materi filosofi keputusan dan tunggu pembagian kelompok oleh ketua kelas.'),
('spk', 2, 'Pertemuan 2', 'Baca jurnal ilmiah dan materi arsitektur DSS; konfirmasi detail tugas ke dosen.'),
('pemrograman-2', 1, 'Pertemuan 1', 'Buat flowchart dari code do-while dengan 3 statement dan 1 kondisi menggunakan Java.'),
('pemrograman-2', 2, 'Pertemuan 2', 'Buat laporan struktur proyek Java dan JFrame Form pada package latihan.'),
('pemrograman-2', 3, 'Format proyek', 'Gunakan penamaan proyek/package sesuai format dosen dan upload repository GitHub per pertemuan.');

INSERT IGNORE INTO `tasks` (`id`, `course_id`, `title`, `type`, `due_label`, `priority`, `status`, `note`) VALUES
(1, 'teknik-kompilasi', 'Balas topik forum diskusi', 'Aktivitas', 'Setiap sesi e-learning', 'high', 'progress', 'Respon topik forum dosen harus menjadi respons pertama sebelum bertanya atau menjawab teman.'),
(2, 'teknik-kompilasi', 'Catat tahapan kompilasi & translator', 'Materi', 'Pertemuan 1', 'medium', 'todo', 'Source program, analisis, intermediate code, optimasi, code generator, target program; assembler, compiler, interpreter.'),
(3, 'spk', 'Bentuk kelompok project (5 orang)', 'Tugas', 'Sebelum UAS', 'high', 'todo', 'Project aplikasi bebas menggunakan metode apa pun; siapkan repository GitHub dan makalah.'),
(4, 'spk', 'Baca jurnal ilmiah & konfirmasi tugas', 'Bacaan', 'Pertemuan 2', 'medium', 'todo', 'Konfirmasi detail tugas membaca jurnal kepada dosen.'),
(5, 'pemrograman-2', 'Buat flowchart do-while Java', 'Tugas', 'Pertemuan 1', 'high', 'todo', '3 statement di dalam perulangan dan 1 kondisi.'),
(6, 'pemrograman-2', 'Laporan struktur proyek & JFrame Form', 'Tugas', 'Pertemuan 2', 'medium', 'todo', 'Buat package latihan dan simpan laporan dalam PDF di folder Drive pertemuan.'),
(7, 'pemrograman-2', 'Upload project ke repository GitHub', 'Pengumpulan', 'Setiap pertemuan', 'high', 'progress', 'Dosen akan mengecek repository dan implementasi project.'),
(8, 'rpl', 'Catat link pengumpulan project kelompok', 'Pengingat', 'Sebelum UAS', 'low', 'todo', 'Pengumpulan mengikuti kelompok Kerja Praktek.');

INSERT IGNORE INTO `worklogs` (`id`, `logged_at`, `type`, `title`, `detail`, `command`) VALUES
(1, '2026-09-11 20:49:24 +07:00', 'Prompt pengguna', 'Permintaan upgrade dokumen menjadi web app', 'Membangun web app informasi tugas, materi, mata kuliah, jadwal, dan worklog untuk kelas 06TPLE004.', 'prompt: tujuan utama web app + combine info matkul & table jadwal'),
(2, '2026-09-11 20:49:24 +07:00', 'Analisis sumber', 'Membaca README dan seluruh bahan kelas', 'Menggabungkan konfigurasi README, info_matkul.txt, table_jadwal.html, dan isi Dokumen Tugas Matkul 06TPLEE004.docx.', 'rtk rg --files; membaca 4 file sumber'),
(3, '2026-09-11 20:49:24 +07:00', 'Perancangan', 'Menyusun informasi kelas menjadi satu workspace', 'Menentukan dashboard, daftar tugas, jadwal kelompok, detail mata kuliah, pencarian, filter, dan penyimpanan tugas lokal.', 'model data 8 mata kuliah + 8 tugas awal'),
(4, '2026-09-11 20:49:24 +07:00', 'Implementasi', 'Membangun WebKelas responsif', 'Membuat UI modern berbasis Bootstrap, jQuery, CSS custom, router hash, modal tambah tugas, status tugas, tema gelap, dan worklog.', 'index.html + styles.css + app.js'),
(5, '2026-09-11 20:54:34 +07:00', 'Prompt pengguna', 'Melanjutkan pengerjaan dan menambahkan aturan worklog', 'Prompt tambahan meminta implementasi langsung, penggabungan semua bahan sumber, serta pencatatan setiap prompt berikutnya dengan perintah dan timestamp.', 'prompt: langsung kerjakan + tambahkan worklog + catat prompt berikutnya'),
(6, '2026-09-11 20:54:34 +07:00', 'Perbaikan & verifikasi', 'Merapikan data dan memvalidasi JavaScript', 'Kode Teknik Kompilasi disesuaikan ke 22TIF3012 berdasarkan tabel jadwal dan selector event diperbaiki.', 'rtk node --check app.js → lulus'),
(7, '2026-09-11 20:56:06 +07:00', 'Verifikasi akhir', 'Memastikan file implementasi siap dibuka', 'File index.html, styles.css, app.js, dan worklog.md tersedia; pemeriksaan sintaks JavaScript berhasil.', 'rtk node --check app.js → lulus'),
(8, '2026-09-11 21:22:24 +07:00', 'Prompt pengguna', 'Menegaskan penggunaan PHP CodeIgniter 3', 'Proyek wajib memakai PHP CodeIgniter 3 sesuai README.', 'prompt: jadi projeknya pake php ci3'),
(9, '2026-09-11 21:22:24 +07:00', 'Arsitektur', 'Mengubah prototype menjadi scaffold CodeIgniter 3', 'Menambahkan controller, model, view, REST API AJAX, konfigurasi MySQL, framework CI3, dan seed SQL.', 'index.php + application/ + database/webkelas.sql + assets/js/ci-webkelas.js'),
(10, '2026-09-11 21:23:58 +07:00', 'Verifikasi CI3', 'Memastikan struktur CI3 siap digunakan', 'Core CI3, seed SQL, dan frontend AJAX tersedia; lint PHP serta JavaScript lulus.', 'CI3 core=True; SQL seed=True; AJAX frontend=True'),
(11, '2026-09-11 21:24:41 +07:00', 'Verifikasi akhir', 'Mengecek ulang entry point dan seed CI3', 'Entry point CI3, controller API, view dashboard, JavaScript AJAX, dan seed worklog SQL sudah dicek.', 'php -l lulus; node --check lulus; system/system/core/CodeIgniter.php=True');
