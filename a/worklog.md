# Worklog WebKelas 06TPLE004

Semua timestamp menggunakan zona waktu Asia/Bangkok (UTC+07:00).

| Timestamp | Jenis | Pekerjaan / prompt | Perintah atau hasil |
|---|---|---|---|
| 2026-09-11 20:49:24 +07:00 | Prompt pengguna | Tujuan utama: membangun web app informasi tugas dan menggabungkan file info mata kuliah serta tabel jadwal. | `prompt: tujuan utama web app + combine info matkul & table jadwal` |
| 2026-09-11 20:49:24 +07:00 | Analisis sumber | Membaca README, `info_matkul.txt`, `table_jadwal.html`, dan isi DOCX tugas. | `rtk rg --files`; membaca 4 file sumber |
| 2026-09-11 20:49:24 +07:00 | Perancangan | Menyusun model 8 mata kuliah, jadwal kelompok 1/2, serta tugas dan catatan yang berasal dari DOCX. | Model data siap digunakan di `app.js` |
| 2026-09-11 20:49:24 +07:00 | Implementasi | Membuat dashboard responsif, katalog mata kuliah, detail materi, jadwal, filter tugas, tambah tugas, status tugas, tema gelap, dan worklog. | `index.html`, `styles.css`, `app.js` |
| 2026-09-11 20:54:34 +07:00 | Prompt pengguna | Meminta pengerjaan langsung, penggabungan bahan sumber, serta worklog dengan perintah dan timestamp untuk prompt tambahan. | `prompt: langsung kerjakan + tambahkan worklog + catat prompt berikutnya` |
| 2026-09-11 20:56:06 +07:00 | Verifikasi akhir | Memastikan file implementasi utama tersedia dan pemeriksaan sintaks JavaScript berhasil. | `rtk node --check app.js` → lulus |
| 2026-09-11 21:22:24 +07:00 | Prompt pengguna | Menegaskan bahwa proyek wajib menggunakan PHP CodeIgniter 3. | `prompt: jadi projeknya pake php ci3` |
| 2026-09-11 21:22:24 +07:00 | Arsitektur | Mengubah prototype menjadi scaffold CodeIgniter 3 dengan controller, model, view, REST API AJAX, konfigurasi MySQL, dan seed SQL. | `index.php`, `application/`, `database/webkelas.sql`, `assets/js/ci-webkelas.js` |
| 2026-09-11 21:23:58 +07:00 | Verifikasi CI3 | Memastikan core CI3, seed SQL, dan frontend AJAX tersedia; lint PHP serta JavaScript lulus. | `CI3 core=True`; `SQL seed=True`; `AJAX frontend=True` |
| 2026-09-11 21:24:41 +07:00 | Verifikasi akhir | Mengecek ulang entry point CI3, controller API, view dashboard, JavaScript AJAX, dan integritas baris seed worklog SQL. | `php -l` lulus; `node --check` lulus; `system/system/core/CodeIgniter.php=True` |
| 2026-09-19 00:30:35 +07:00 | Tes aplikasi | Menjalankan halaman dashboard CI3 dan mencatat error database di log. | Tabel belum ada saat tes pertama; error masuk ke `application/logs/log-2026-09-19.php` |
| 2026-09-19 08:00:00 +07:00 | Prompt pengguna | Meminta halaman init khusus dengan login nazar / [removed setup password], plus worklog_table.md agar agent lain tahu apa yang harus dilakukan. | `prompt: buatkan page khusus init dg login nazar pwd [removed setup password]... + worklog_table.md` |
| 2026-09-19 08:00:00 +07:00 | Implementasi init page | Membuat halaman inisialisasi database terproteksi login dengan form login, dashboard status tabel, dan endpoint seed. | File baru: `application/controllers/Init.php`, `application/models/Init_model.php`, `application/views/init/login.php`, `application/views/init/dashboard.php`, `assets/css/init.css`; route baru di `application/config/routes.php` |
| 2026-09-19 08:00:00 +07:00 | Verifikasi init page | Memastikan keenam file baru tanpa syntax error PHP dan route init terdaftar. | `php -l` lulus untuk Init.php, Init_model.php, login.php, dashboard.php, routes.php |
| 2026-09-19 08:00:00 +07:00 | Dokumentasi agent | Membuat `worklog_table.md` berisi tujuan halaman init, ringkasan tabel yang diharapkan, cara penggunaan, dan langkah yang harus dilakukan agent lain saat memasuki proyek ini. | File baru: `worklog_table.md` |

## Format pencatatan prompt berikutnya

Tambahkan baris baru dengan format berikut setiap ada prompt tambahan dari user:

```text
| YYYY-MM-DD HH:mm:ss +07:00 | Jenis | Judul singkat | Perintah/hasil yang dikerjakan |
```

## Ringkasan file init yang baru dibuat

- **Halaman login:** `application/views/init/login.php` — form dengan username dan password.
- **Halaman dashboard inisialisasi:** `application/views/init/dashboard.php` — tabel status keenam tabel utama dan tombol seed ulang.
- **Controller:** `application/controllers/Init.php` — menangani login, logout, seed, dan tampilan.
- **Model:** `application/models/Init_model.php` — skema CREATE IF NOT EXISTS, seed data courses/relasi, dan inspeksi status tabel.
- **CSS:** `assets/css/init.css` — tampilan khusus halaman init.
- **Route:** `/init`, `/init/login_post`, `/init/logout`, `/init/seed`.

## Kredensial init

- Username: `nazar`
- Password: `[removed setup password]`
- Terletak di `application/controllers/Init.php` sebagai konstanta statis — ganti sebelum produksi.

## Cara cepat untuk agent lain

1. Buka `/init`, login, dan halaman akan otomatis memeriksa serta menyiapkan tabel.
2. Baca `worklog_table.md` untuk tahu tabel apa yang dibutuhkan proyek ini dan apa yang harus dilakukan.
3. Cek API: `/index.php/api/courses`, `/index.php/api/tasks`, `/index.php/api/worklog`.
4. Catat pekerjaan besar di `worklog.md` sesuai format di atas.
