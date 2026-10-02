# Pemeriksaan WebKelas

Jalankan dari direktori proyek setelah database lokal aktif dan konfigurasi aplikasi tersedia.

1. Jalankan server uji: `rtk php -S 127.0.0.1:8765 index.php`.
2. Di terminal lain, jalankan `rtk node tests/blackbox-smoke.mjs`.
3. Jalankan pemeriksaan source `rtk node tests/whitebox-guards.mjs` dan sintaks dengan `rtk php -l` / `rtk node --check` pada file yang diubah.
4. Jalankan `rtk php tests/schema-check.php` untuk memastikan migrasi kolom sumber tersedia pada database terkonfigurasi.

Smoke test tidak membuat akun atau mengubah pengumuman. Pengujian belum mengklaim cakupan autentikasi dosen/mahasiswa lintas institusi atau isolasi tenant karena fitur tersebut belum diimplementasikan.
