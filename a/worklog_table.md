# worklog_table.md — Panduan Inisialisasi & Tindakan Agent

Dokumen ini menjelaskan apa yang harus dilakukan oleh agent lain yang masuk ke proyek ini,
terutama terkait tabel database dan cara menggunakan halaman `/init`.

Semua timestamp menggunakan zona waktu Asia/Bangkok (UTC+07:00).

---

## 1. Keberadaan halaman init

- **URL:** `http://localhost/ci3_webkelas/index.php/init` (atau `/init` jika rewrite aktif).
- **Login:**
  - username: `nazar`
  - password: `[removed setup password]`
- Setelah login berhasil, session akan menyimpan flag `init_logged_in` dan halaman
  langsung menampilkan status tabel serta menjalankan inisialisasi otomatis.

---

## 2. Tujuan halaman init

Halaman `/init` dibuat agar:

1. **Tidak perlu menebak skema atau seed manual.** Agent cukup membuka satu URL
   dan melihat status setiap tabel.
2. **Tabel yang belum ada akan dibuat otomatis** melalui `Init_model::ensure_tables()`.
3. **Seed data mata kuliah dan relasinya** (`courses`, `course_links`, `course_notes`,
   `course_meetings`) akan diisi otomatis hanya jika tabel masih kosong.
4. **Tabel `tasks` dan `worklogs`** tidak di-seed oleh halaman init; data awalnya
   berasal dari `database/webkelas.sql` sesuai perancangan projek.

---

## 3. Ringkasan tabel yang diharapkan proyek ini

### 3.1 Tabel utama dan relasi

| Tabel | Pemilik / pengguna | Kegunaan |
|---|---|---|
| `courses` | `Course_model`, `Api::courses`, view dashboard, jadwal, katalog | Data 8 mata kuliah kelas 06TPLE004 |
| `course_links` | `Course_model::hydrate` (links per mata kuliah) | Link materi, kelompok, download |
| `course_notes` | `Course_model::hydrate` (notes per mata kuliah) | Catatan penting dari dokumen tugas |
| `course_meetings` | `Course_model::hydrate` (pertemuan per mata kuliah) | Judul dan detail pertemuan |
| `tasks` | `Task_model`, `Api::tasks` (CRUD tugas) | Tugas, catatan, dan status yang bisa diedit dari frontend |
| `worklogs` | `Api::worklog`, view worklog | Riwayat pekerjaan dan prompt yang sudah dilakukan |

### 3.2 Relasi utama

- `course_links.course_id` → `courses.id` (ON DELETE CASCADE)
- `course_notes.course_id` → `courses.id` (ON DELETE CASCADE)
- `course_meetings.course_id` → `courses.id` (ON DELETE CASCADE)
- `tasks.course_id` → `courses.id` (ON DELETE CASCADE)

---

## 4. Cara penggunaan untuk agent lain

### 4.1 Cek kondisi database

1. Buka `/init`.
2. Login dengan `nazar` / `[removed setup password]`.
3. Baca tabel status di halaman dashboard:
   - Kolom **Ada?** menunjukkan apakah tabel sudah ada di database.
   - Kolom **Baris** menunjukkan jumlah data saat ini.
   - Kolom **Tindakan** memberi petunjuk singkat: sudah berisi data, kosong, atau belum ada.

Jika tabel belum ada, halaman init akan membuatnya secara otomatis saat dibuka
(setelah login), lalu me-refresh status.

### 4.2 Menjalankan seed ulang

- Klik tombol **Jalankan seed ulang** di halaman init **setelah login**.
- Endpoint: `POST /init/seed`.
- Seed hanya mengisi `courses`, `course_links`, `course_notes`, `course_meetings`
  jika tabel masing-masing masih kosong, jadi data yang sudah ada tidak akan hilang.
- Jika perlu memaksa ulang seluruh data, jalankan `database/webkelas.sql` dari awal
  (drop database, buat ulang).

### 4.3 Cara agent lain mengembangkan fitur baru

1. **Tentukan apakah tabel baru diperlukan.**
   - Jika fitur butuh tabel baru, tambahkan skema ke `Init_model::$schemas` dan definisi
     SQL ke `database/webkelas.sql` agar konsisten.
2. **Sematkan seed data ke model init atau file SQL.**
   - Data statis seperti daftar mata kuliah dan relasinya sebaiknya berada di satu tempat
     agar tidak ada dua versi seed yang berbeda.
3. **Update `worklog_table.md` dan `worklog.md`.**
   - Tambahkan baris di worklog sesuai format yang sudah ada di `worklog.md`.
   - Kaitkan perubahan tabel atau fitur baru dengan baris worklog sehingga penelusuran
     mudah.

---

## 5. Apa yang harus dilakukan agent saat memasuki proyek ini

Urutan pengerjaan yang disarankan:

1. **Buka `/init` dan login.**
   - Pastikan database sudah berjalan dan konfigurasi di
     `application/config/database.php` sesuai (localhost, port 3308, user webkelas,
     database webkelas).
2. **Verifikasi keenam tabel utama.**
   - Cek status di halaman init. Jika ada tabel yang belum ada atau kosong, biarkan
     halaman init membuat/mengisi semuanya.
3. **Cek API dasar.**
   - `GET /index.php/api/courses` → harus mengembalikan 8 mata kuliah.
   - `GET /index.php/api/tasks` → harus mengembalal 8 tugas awal dari seed SQL.
   - `GET /index.php/api/worklog` → harus mengembalikan worklog pertama.
4. **Buka dashboard utama.**
   - `/` atau `/index.php` → halaman dashboard harus terisi data dari API tanpa error.
5. **Lacak pekerjaan berikutnya di `worklog.md`.**
   - Setiap prompt dan tindakan besar harus dicatat di `worklog.md` dengan format:
     ```
     YYYY-MM-DD HH:mm:ss +07:00 | Jenis | Judul singkat | Perintah/hasil
     ```
   - Jika ada perubahan tabel, tambahkan juga ke `worklog_table.md` bagian tabel atau
     tambahkan catatan pendek.

---

## 6. File-f dll yang dirujuk oleh halaman init

- `application/controllers/Init.php` — controller halaman login dan dashboard init.
- `application/models/Init_model.php` — skema, seed, dan inspeksi tabel.
- `application/views/init/login.php` — form login.
- `application/views/init/dashboard.php` — tampilan status tabel.
- `assets/css/init.css` — styled khusus halaman init.
- `application/config/routes.php` — rute `/init`, `/init/login_post`, `/init/logout`,
  `/init/seed`.
- `database/webkelas.sql` — sumber kebenaran skema dan seed utama (courses, relasi,
  tasks awal, worklog awal).

---

## 7. Keterangan keamanan (pengembangan lokal)

- Login init menggunakan kredensial statis yang ditulis langsung di
  `application/controllers/Init.php`:
  - `const legacy init user constant = 'nazar'`
  - `const legacy init credential constant = '[removed setup password]'`
- Untuk produksi atau lingkungan lain, pindahkan ke konfigurasi/database dan jangan
  hardcode di controller.
- Session yang digunakan hanya berisi flag `init_logged_in` biasa tanpa data sensitif.

---

*File ini diperbarui bersamaan dengan pembuatan halaman `/init`.*
*Tanggal pembaruan: 2026-09-19.*
