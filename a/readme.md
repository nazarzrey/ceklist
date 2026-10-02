#configurasi

config database
localhost:3308
user webkelas
pwd 123
db webkelas
db ref data mahasiswa unpam.unpam_mahasiswa

config web app
pakai codeigniter 3
pakai url routing spya enk dibaca
pakai bootstrap terbaru
pakai jquery terbaru
semua CRUD pakai AJAX rest api
di body tempelkan aja base_url spya javascript itnggal baca id nya jadi ga perlu tulis di file js

web yg modern ui dan yg bagus lalu webapp mudah di gunakan, user freiendly, dan bagi yg gaptek pun gampang menggunakannya
pastikan responsive

## Hasil implementasi

Web app dibangun dengan PHP CodeIgniter 3 sesuai konfigurasi di atas. Entry point utama adalah `index.php`; `index.html` hanya mengarahkan pengguna ke entry point CI3. Framework CI3 berada di `system/system` hasil instalasi CodeIgniter 3.1.13.

Struktur utama:

- `application/controllers/Welcome.php` — halaman dashboard;
- `application/controllers/Api.php` — REST API mata kuliah, tugas, dan worklog;
- `application/models/Course_model.php` dan `Task_model.php` — akses data MySQL;
- `application/views/dashboard.php` — view Bootstrap responsif;
- `assets/js/ci-webkelas.js` — frontend jQuery dengan AJAX CRUD;
- `database/webkelas.sql` — schema dan seed dari DOCX, `info_matkul.txt`, serta `table_jadwal.html`;
- `worklog.md` — riwayat pekerjaan dan prompt pengguna.

## Cara menjalankan

1. Pastikan Apache dan MySQL aktif melalui XAMPP/Laragon.
2. Pastikan MySQL berjalan di port `3308`, lalu import `database/webkelas.sql`.
3. Pastikan user/database sesuai README: `webkelas` / `123` / `webkelas`.
4. Buka `http://localhost/webkelas/`.

### Akses halaman inisialisasi

Halaman `/init` tidak memiliki kredensial bawaan. Jika perlu menggunakannya, atur environment server `WEBKELAS_INIT_USER` dan `WEBKELAS_INIT_PASSWORD_HASH`. Nilai hash harus dibuat dengan `password_hash()` PHP; aplikasi memverifikasinya dengan `password_verify()`. Jika salah satu variabel tidak tersedia, login setup ditolak. Jangan menyimpan password asli atau hash produksi di source control.

Keluar dan menjalankan seed ulang menggunakan metode POST. Batasi akses jaringan ke halaman setup dan matikan kredensial environment setelah pekerjaan administrasi selesai.

Endpoint yang digunakan frontend:

- `GET /api/courses` dan `GET /api/courses/{id}`;
- `GET /api/tasks`;
- `POST /api/tasks`;
- `PUT /api/tasks/{id}`;
- `DELETE /api/tasks/{id}`;
- `GET /api/worklog`.
