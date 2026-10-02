# Weekly Checklist and Student Profile Links

## Roadmap extension: platform multi-institusi (1 Okt 2026)

### Fondasi keamanan yang sudah disesuaikan

- [x] Hapus kredensial statis halaman `/init`; akses sekarang fail-closed kecuali `WEBKELAS_INIT_USER` dan `WEBKELAS_INIT_PASSWORD_HASH` tersedia di environment.
- [x] Verifikasi password setup memakai `password_verify()` dan regenerasi session setelah login.
- [x] Ubah aksi keluar dan seed ulang menjadi POST agar tidak berjalan dari tautan GET.
- [x] Hapus kredensial dari layar status inisialisasi dan dokumentasikan konfigurasi environment.
- [x] Tambahkan URL sumber resmi opsional ke pengumuman; validasi skema/ukuran URL di API, simpan di database, dan tampilkan terpisah dari link pengumpulan.
- [x] Perketat validasi API untuk panjang URL/label tenggat dan tanggal lokal yang tidak valid.
- [x] Aktifkan atribut cookie `Secure` otomatis saat aplikasi diakses melalui HTTPS.
- [x] Terapkan perubahan akses setup dan hapus kredensial lama dari salinan arsip `a/` serta catatan implementasi lama.

### Pekerjaan roadmap yang masih harus dilakukan

- [ ] Rancang dan migrasikan model institusi → tahun akademik/semester → program → kelas → mata kuliah/mata pelajaran.
- [ ] Ganti login yang hanya membaca roster UNPAM kelas TPLE004 dengan akun dan keanggotaan yang mendukung siswa/mahasiswa serta guru/dosen di beberapa institusi.
- [ ] Terapkan pemeriksaan akses tenant dan peran pada setiap API, query, ekspor, dan hasil pencarian; uji agar data antar-institusi tidak bocor.
- [ ] Sediakan onboarding institusi, impor roster tervalidasi, penugasan pengajar, dan pengelolaan semester.
- [ ] Simpan sumber, status publikasi, serta riwayat koreksi untuk pengumuman dan tugas.
- [ ] Buat pencarian/FAQ yang menunjukkan sumber; baru evaluasi Q&A generatif setelah batas akses dan evaluasi kualitas siap.
- [ ] Tentukan status direktori `applicatione` (fitur keuangan) apakah produk terpisah atau bagian WebKelas.
- [ ] Tentukan operasi layanan: privasi, backup/restore, ekspor dan penghapusan data, dukungan, dan model pembiayaan.

Catatan cakupan: perubahan keamanan halaman setup di atas tidak menjadikan aplikasi multi-tenant. Login dan data akademik utama masih terikat pada roster UNPAM dan kelas TPLE004 sampai desain keanggotaan serta isolasi tenant selesai.

### Pemeriksaan perubahan 1 Okt 2026

- PHP lint lulus untuk controller/model/config utama dan salinan controller arsip; JavaScript dashboard lolos `node --check`.
- Black-box smoke checks lulus: halaman utama, session cookie HttpOnly, penolakan API anonim, GET logout admin ditolak, form setup menghasilkan CSRF token, login setup gagal tertutup, dan token CSRF tidak bisa dipakai ulang.
- White-box guard checks lulus untuk autentikasi environment/hash, regenerasi session, CSRF, validasi URL/tenggat, migrasi kolom sumber, dan tautan sumber frontend.
- Pemeriksaan metadata database lokal memastikan kolom sumber pengumuman berhasil dibuat.
- Belum ada pengujian lintas institusi karena skema dan alur multi-tenant belum tersedia; skenario itu harus dibuat sebelum data institusi lain dimasukkan.

## Work checklist

- [x] Simplify the class leader composer so a weekly recap title can be suggested from the first missing week number already in announcements.
- [x] Parse `-`, `*`, and `#` lines from the pasted recap into personal checklist items while retaining the surrounding note as announcement detail.
- [x] Detect each item’s course from its text, keep `@course` autocomplete for explicit single-course posts, and save every task into the correct course group.
- [x] Detect deadlines and URLs in checklist lines; preserve readable due labels and make all reference links open in a new tab.
- [x] Add a full profile page with name/password editing and copy/revoke controls for a short-lived, one-time personal login link.
- [x] Store only a hash of each login-link token; expire it after 15 minutes, consume it once, and regenerate the session on successful use.
- [x] Verify syntax, API validation, the relevant UI hooks, security behavior, and the project completion hook; update this list with outcomes.

## Verification notes

- `node --check assets/js/ci-webkelas.js` and PHP syntax checks passed for the changed controller/model/view files.
- Parser smoke check converted the sample recap into eight rows across six course groups, split double meetings, retained the IoT deadline/link, and preserved the `NOTE` paragraph.
- Authenticated API smoke check stored a global recap with two tasks tagged to different courses and saved each due label/reference URL; the temporary test announcement was deleted afterward.
- Profile-link smoke check confirmed successful first-use login, anonymous rejection after reuse, 15-minute expiry based on the database clock, and revoke cleanup. No active test link remains.
- The project completion hook returned `{"ok":true,"affected":1}`.

## Composer preview and share copy (26 Sep 2026)

- [x] Keep the course autocomplete as a compact picker in the class-message header; allow Tab/Enter selection and keep `@` out of message text.
- [x] Show a live pre-save checklist preview using the same task-card visual language as `/ceklist`, including course, due label, and reference link.
- [x] Add a broadcast-card action that copies the full title/message/checklist plus detail-checklist URL and classroom website URL for sharing.
- [x] Add responsive styles for the composer and preview, then run JavaScript syntax and project completion checks.

## Latest verification

- `node --check assets/js/ci-webkelas.js` passed for the live preview, autocomplete keyboard handling, and share-copy changes.
- `rtk cmd /c task` completed successfully and returned `{"ok":true,"affected":1}`.

## Inline course tags and checklist review flow (26 Sep 2026)

- [x] Remove standalone course picker; trigger current-semester course suggestions at the caret when typing `@` in either message textarea.
- [x] Assign each checklist row only to its explicit `@course`; rows without a course stay global, while plain text remains announcement info.
- [x] Make preview a separate review step with tasks in source order, info text, edit, and publish actions.
- [x] Copy announcement title as WhatsApp bold, bold parenthetical text, and retain full checklist/detail/site URLs.
- [x] Run JavaScript/PHP checks, parser behavior smoke check, and `rtk cmd /c task`.

## Inline-flow verification

- `node --check assets/js/ci-webkelas.js` passed; PHP lint reports no syntax errors in `application/views/dashboard.php` (local CLI emitted a missing `mysqli` startup warning).
- Parser smoke check preserved message notes, returned tasks in text order, assigned Basis Data 2 and IoT from their inline tags, kept an untagged task global, and retained the IoT due label.
- `rtk cmd /c task` returned `{"ok":true,"affected":1}`.
