# Analisis Bisnis dan Roadmap Jangka Panjang WebKelas

**Tanggal:** 1 Oktober 2026  
**Visi produk:** platform akademik yang dapat digunakan banyak universitas, sekolah, dan komunitas belajar oleh mahasiswa/siswa, dosen/guru, serta pengelola institusi.  
**Konteks awal:** fitur WebKelas saat ini berangkat dari kebutuhan kelas 06TPLE004. Fitur dan data kelas tersebut adalah titik awal produk, bukan batas target pasar.

## Ringkasan eksekutif

WebKelas sudah punya fondasi yang relevan: data mata kuliah dan pertemuan, dashboard, rekap pengumuman kelas, checklist personal, penanda baca, halaman profil, serta tautan login sekali pakai. Alur ketua kelas menulis pengumuman dan checklist dalam satu komposer cocok untuk kebutuhan mingguan. Untuk dipakai lintas institusi dan peran, produk perlu berkembang dari aplikasi satu kelas menjadi layanan multi-institusi dengan pengelolaan kelas, semester, mata pelajaran/mata kuliah, dan akses yang terpisah.

Untuk bertahan dan menjangkau lebih banyak pengguna, arah produk yang disarankan adalah **platform koordinasi dan informasi akademik yang tepercaya untuk banyak institusi**. Prioritasnya bukan memperbanyak menu, melainkan memastikan informasi tugas benar, jelas sumbernya, tidak terlambat, mudah ditemukan, dan data suatu institusi tidak terlihat oleh institusi lain.

Urutan fokus:

1. Keandalan data dan keamanan akun.
2. Pengelolaan tugas mingguan yang terstruktur dan dapat dilacak.
3. Pengalaman mahasiswa: pencarian, pengingat, dan aksesibilitas.
4. Perluasan ke banyak kelas dan institusi dengan isolasi data dan konfigurasi masing-masing.
5. Q&A berbasis sumber yang mengikuti konteks institusi, kelas, dan mata kuliah pengguna.

## Status implementasi roadmap

Perubahan yang sudah diterapkan pada pekerjaan ini: akses halaman setup tidak lagi memakai kredensial tetap di source code; login setup memerlukan username dan hash password dari environment server, memverifikasi hash, dan mengganti session ID. Aksi keluar dan seed ulang juga memakai POST, dan instruksi setup diperbarui.

Perubahan tersebut mengamankan satu bagian operasional aplikasi. **Aplikasi belum menjadi platform multi-institusi:** login dan data akademik utama masih bergantung pada roster UNPAM dan kelas TPLE004. Skema multi-tenant, akun guru/dosen lintas institusi, pemisahan data API, dan onboarding institusi tetap menjadi pekerjaan berikutnya dan perlu dirancang serta dimigrasikan sebagai satu perubahan teruji.

## Kondisi saat ini dan implikasi bisnis

| Area | Yang sudah ada | Implikasi / penyesuaian |
|---|---|---|
| Informasi kelas | Mata kuliah, jadwal, materi/link, catatan, pertemuan, tugas | Data awal membantu kelas berjalan, tetapi perlu pemilik, sumber, semester, dan tanggal pembaruan agar tidak dianggap selalu resmi. |
| Rekap mingguan | Ketua kelas dapat menerbitkan judul, pengumuman, dan butir checklist; judul minggu berikutnya dapat disarankan | Pertahankan alur ini. Tambahkan status draf/publikasi dan pencegahan rekap ganda atau keliru. |
| Checklist | Status checklist per mahasiswa dan hitungan personal | Perlu status yang lebih bermakna dari centang saja: belum mulai, dikerjakan, selesai, dan bila perlu perlu bantuan. Jangan tampilkan progres individu ke kelas tanpa persetujuan. |
| Tautan dan deadline | URL dapat dikaitkan ke butir; label tenggat dapat dibaca | Simpan tenggat pasti dengan zona waktu bila diketahui, sambil mempertahankan label teks untuk kasus yang belum pasti. Validasi URL dan tampilkan domain tujuan. |
| Identitas dan akses | Login NIM, profil, tautan cepat sekali pakai dengan masa berlaku | Tautan bearer setara kredensial. Pertahankan hash di server, sekali pakai, masa berlaku singkat, pencabutan, pembatasan penerbitan, dan jangan tampilkan token di log/analitik. Evaluasi OTP atau passkey sebagai opsi jangka panjang. |
| Arsitektur | CodeIgniter 3 dan MySQL; sebagian perilaku didokumentasikan di STYLEGUIDE dan TODO | Catat versi runtime dan dependensi, tambah backup serta pemantauan, dan rencanakan peningkatan framework secara bertahap sebelum pertumbuhan pengguna membebani pemeliharaan. |
| Batas produk | Repo juga berisi direktori applicatione dengan fitur pengelolaan keuangan | Tegaskan apakah itu bagian WebKelas atau aplikasi lain. Jika berbeda, pisahkan rute, konfigurasi, data, akses, dan roadmap agar pengguna dan pengelola tidak bingung. |
| Pasar dan peran | Fondasi dan data saat ini berfokus pada satu kelas | Bentuk produk untuk beragam institusi. Pisahkan pengalaman mahasiswa/siswa, dosen/guru, ketua kelas, dan pengelola institusi tanpa menganggap semua kampus memakai istilah atau proses yang sama. |

## Rekomendasi produk

### 1. Jadikan tugas sebagai data yang rapi

Saat ini cara menempelkan daftar lalu mengenali baris checklist sangat cepat. Tetap sediakan cara tersebut, tetapi setelah parsing tampilkan langkah konfirmasi sebelum publikasi. Untuk setiap butir, simpan atribut terstruktur:

- mata kuliah dan nomor pertemuan (dapat lebih dari satu pertemuan);
- jenis aktivitas: forum diskusi, tugas, kuis, laporan, atau lainnya;
- instruksi dan hasil yang harus dikumpulkan, misalnya PDF;
- tenggat waktu, zona waktu, dan apakah waktu itu pasti atau hanya label;
- tautan sumber/pengumpulan serta nama domain;
- sumber informasi dan siapa yang mengonfirmasi;
- status publikasi dan riwayat koreksi.

Autocomplete @mata kuliah membantu pengelompokan, tetapi sebaiknya sistem tidak menyimpulkan instruksi penting hanya dari nama matkul. Ketua kelas tetap mengonfirmasi hasil ekstraksi, pertemuan, deadline, serta lampiran/link sebelum mengirim.

### 2. Kelola informasi yang berubah

Buat siklus hidup untuk informasi: draf → ditinjau → diterbitkan → dikoreksi/diarsipkan. Perubahan setelah publikasi perlu mencatat siapa, kapan, dan apa yang berubah; mahasiswa yang telah membaca dapat melihat tanda “diperbarui”. Simpan sumber, misalnya tautan forum e-learning atau dokumen dosen, sehingga rekap bukan klaim tanpa rujukan.

Tambahkan revisi atau pembatalan yang jelas; hindari menghapus pengumuman penting secara diam-diam. Untuk data jadwal, materi, dan deadline, tampilkan terakhir diperbarui dan minta ketua kelas meninjau ulang setiap awal semester.

### 3. Perluas manfaat harian untuk mahasiswa

- Filter tugas berdasarkan mata kuliah, minggu, status, dan tenggat.
- Tampilan “minggu ini”, “mendatang”, “terlambat”, serta ringkasan beban tugas.
- Pengingat yang dapat dipilih pengguna; mulai dari notifikasi dalam aplikasi/email, lalu pertimbangkan kanal lain berdasarkan persetujuan dan biaya.
- Pencarian global atas pengumuman, tugas, materi, dan pertemuan.
- Riwayat checklist pribadi dan penanda tugas yang perlu ditanyakan.
- Akses ramah ponsel, keyboard, pembaca layar, dan koneksi lambat; sediakan keadaan kosong, gagal, dan loading yang jelas.

Jangan mengubah jumlah checklist yang belum selesai menjadi alat penilaian atau papan peringkat. Data itu untuk membantu mahasiswa sendiri.

### 4. Rancang platform multi-institusi

Sebelum onboarding institusi kedua, tetapkan struktur data bertingkat: institusi → tahun akademik/semester → program → kelas/rombel → mata kuliah atau mata pelajaran → pertemuan/tugas. Istilah dan struktur harus dapat dikonfigurasi karena universitas, sekolah, dan program nonformal bisa berbeda. Setiap data dan query harus dibatasi berdasarkan institusi serta ruang kelas/semester yang sedang dipilih.

Rancang peran minimum dan cakupannya: siswa/mahasiswa, guru/dosen, ketua kelas/asisten, pengelola kelas/program, dan admin institusi. Jangan memberi akses lintas kelas atau institusi hanya karena seseorang berstatus pengajar; akses harus ditautkan ke kelas yang ditugaskan. Aksi administratif perlu tercatat.

Tambahkan onboarding bertahap: daftarkan institusi dan domain/identitasnya, siapkan struktur akademik, impor roster dengan validasi, tautkan pengajar ke kelas, undang pengguna, lalu tinjau data sebelum aktivasi. Sediakan opsi masuk mandiri untuk institusi kecil serta onboarding terbantu untuk institusi besar. Jangan bergantung pada nama tabel, kode kelas, NIM, atau konfigurasi satu universitas yang tertanam di aplikasi.

Model komersial dan operasi juga perlu diuji sebelum ekspansi: apakah layanan gratis per kelas, berlangganan per institusi, atau bertingkat berdasarkan fitur/pengguna; siapa yang membantu onboarding dan dukungan; serta bagaimana institusi dapat mengekspor atau menghapus data mereka.

### 5. Bangun Q&A dengan sumber tepercaya dan batas akses

Q&A dapat mengurangi pertanyaan berulang, tetapi harus menjawab dari sumber yang memang tersedia dan boleh diakses oleh pengguna. Konteks pencarian harus mengikuti institusi, kelas, mata kuliah, peran, dan hak akses. Mulai dengan pencarian kata kunci atas pengumuman, catatan, materi, tugas, dan FAQ yang dikurasi. Tahap berikutnya dapat memakai retrieval berbasis dokumen. Untuk setiap jawaban:

- sertakan tautan ke sumber dan bagian/pertemuan terkait;
- bedakan fakta sumber dari ringkasan atau dugaan;
- katakan bahwa jawaban belum ditemukan bila sumber tidak cukup;
- arahkan pertanyaan deadline/kebijakan yang meragukan ke ketua kelas/dosen;
- sediakan “jawaban membantu/tidak” dan alur koreksi;
- jangan melatih atau mengirim data roster/profil ke layanan eksternal tanpa dasar dan persetujuan yang jelas.
- jangan pernah mengambil sumber dari kelas/institusi lain hanya karena topik pertanyaannya mirip.

Mulai dari FAQ yang disetujui ketua kelas. Q&A generatif baru layak setelah materi, hak akses, kualitas sumber, dan evaluasi jawaban siap. Jangan menjadikan model sebagai sumber kebenaran tunggal atau membiarkannya membuat deadline/instruksi sendiri.

Pengalaman Q&A juga perlu dibedakan menurut peran. Mahasiswa/siswa bertanya tentang tugas dan materi kelasnya; dosen/guru dapat mencari materi dan melihat ringkasan pertanyaan yang sering muncul di kelas yang diajar; pengelola dapat melihat pola penggunaan agregat tanpa membuka percakapan pribadi yang tidak relevan.

## Roadmap bertahap

### Tahap 0 — Fondasi kepercayaan

- Inventarisasi fitur aktif dan tentukan status direktori applicatione.
- Tinjau konfigurasi produksi: kredensial database contoh, mode debug, HTTPS, session cookie, CSRF, backup, dan pemulihan.
- Tambah pencatatan perubahan dan mekanisme koreksi pengumuman.
- Definisikan pemilik data, sumber, semester, zona waktu, dan retensi data.
- Buat kebijakan privasi singkat serta kanal pelaporan kesalahan.

**Selesai bila:** pengelola dapat mengidentifikasi sumber dan pemilik tiap informasi utama, memulihkan backup, serta menelusuri koreksi publikasi.

### Tahap 1 — Pengalaman tugas mingguan

- Pertahankan komposer teks cepat dengan preview konfirmasi.
- Simpan butir checklist sebagai entitas terstruktur dengan course/meeting, type, deadline, link, source, status.
- Tambahkan filter, pencarian, agenda tenggat, dan status pribadi.
- Cegah pengiriman ganda dan dukung draf.
- Validasi link dan beri indikator instruksi/output yang belum terisi.

**Selesai bila:** mahasiswa dapat menemukan tugas dan detail sumbernya tanpa harus membaca semua pengumuman.

### Tahap 2 — Keterlibatan dan Q&A terkurasi

- Tambah preferensi pengingat dan notifikasi dengan kontrol berhenti berlangganan.
- Bangun FAQ dan pencarian yang menampilkan kutipan ringkas beserta sumber internal.
- Ukur jawaban tidak ditemukan dan pertanyaan yang sering perlu klarifikasi.
- Uji Q&A dengan sekumpulan pertanyaan nyata yang dianonimkan dan jawaban rujukan yang telah disetujui.

**Selesai bila:** jawaban yang diberikan dapat ditelusuri, kegagalan terukur, dan pengguna bisa melaporkan jawaban salah.

### Tahap 3 — Pilot lintas institusi

- Pisahkan tenant institusi, tahun akademik, kelas, dan mata pelajaran/mata kuliah di skema, otorisasi, API, serta UI.
- Uji bersama beberapa institusi yang berbeda ukuran dan proses akademiknya; jangan menganggap pilot satu kampus mewakili semua.
- Sediakan onboarding/import data, pemetaan struktur akademik, dan peran pengelola institusi.
- Uji beban, backup/restore, pemantauan error, dan proses dukungan.
- Tambah ekspor/penghapusan data institusi, arsip semester, dan panduan keluar dari layanan.

**Selesai bila:** institusi baru bisa disiapkan tanpa menyalin hard-code, pengguna mendapat akses sesuai penugasannya, data antar-institusi terisolasi, dan data dapat diekspor saat kerja sama berakhir.

### Tahap 4 — Siap bertumbuh sebagai layanan

- Tetapkan dukungan operasional, target ketersediaan, jalur pelaporan insiden, dan jadwal pemeliharaan.
- Siapkan paket penggunaan/harga berdasarkan hasil wawancara institusi dan biaya operasi nyata.
- Pantau biaya per institusi dan pertumbuhan penyimpanan/pemrosesan Q&A.
- Buat dokumentasi admin, kebijakan privasi, ketentuan layanan, dan perjanjian pemrosesan data yang sesuai untuk pasar yang dituju.

**Selesai bila:** ada proses yang jelas untuk mendaftar, mengoperasikan, mendukung, menagih (jika berbayar), dan menutup akun institusi dengan aman.

## Ukuran keberhasilan

Ukur manfaat, bukan sekadar jumlah fitur. Baseline-kan dahulu, lalu tetapkan target setelah beberapa minggu penggunaan.

| Ukuran | Definisi praktis |
|---|---|
| Cakupan checklist | Persentase tugas yang dipublikasikan dengan mata kuliah, pertemuan, instruksi, tenggat (atau label alasan), dan sumber/link yang sesuai. |
| Ketepatan waktu informasi | Jarak antara informasi sumber tersedia dan rekap kelas diterbitkan. |
| Koreksi informasi | Jumlah publikasi yang perlu dikoreksi dan penyebabnya. |
| Penggunaan mahasiswa | Mahasiswa aktif mingguan dan porsi yang membuka/menandai tugas; tampilkan agregat, jangan mengekspos perilaku individu ke publik. |
| Penemuan informasi | Keberhasilan pencarian/FAQ dan waktu untuk menemukan detail tugas. |
| Kualitas Q&A | Persentase jawaban dengan sumber valid, tingkat jawaban tidak membantu, dan pertanyaan yang berhasil dialihkan saat bukti kurang. |
| Keandalan | Error aplikasi, waktu pemulihan, keberhasilan backup, dan waktu muat pada ponsel. |
| Adopsi institusi | Institusi aktif, waktu onboarding sampai kelas pertama aktif, dan tingkat institusi yang tetap memakai layanan setelah satu semester. |
| Isolasi akses | Hasil audit bahwa pengguna hanya dapat membaca/mengubah data sesuai institusi, kelas, dan perannya. |
| Kelayakan layanan | Biaya dukungan dan infrastruktur per institusi dibandingkan pendapatan atau anggaran operasional. |

## Keputusan yang perlu ditetapkan pemilik produk

1. Institusi mana yang menjadi pilot berikutnya, dan jenjang apa yang dituju terlebih dahulu: universitas, sekolah, atau keduanya?
2. Apakah dosen/guru dapat menerbitkan langsung, atau informasi harus melalui ketua kelas/admin?
3. Bagaimana pengguna dari institusi berbeda mendaftar dan membuktikan afiliasi: undangan, email/domain, impor roster, atau integrasi identitas kampus?
4. Kanal pengingat mana yang dipakai dan dapat didanai?
5. Apakah applicatione (fitur keuangan) bagian produk ini atau aplikasi terpisah?
6. Sumber Q&A mana yang boleh dijadikan rujukan, siapa yang menyetujuinya, dan bagaimana batas akses tiap institusi dijaga?
7. Apakah targetnya produk gratis, layanan berlangganan, atau model lain; siapa yang menyediakan dukungan kepada institusi?

## Rekomendasi akhir

Jadikan **akurasi, keterlacakan, kemudahan penggunaan, dan perlindungan data antar-institusi** sebagai pembeda utama WebKelas. Rapikan data dan keamanan lebih dulu, validasi kebutuhan dosen/guru serta mahasiswa/siswa lewat pilot, tingkatkan pencarian/pengingat dan Q&A berbasis sumber, lalu perluas onboarding institusi. Bangun konfigurasi yang fleksibel agar perbedaan proses akademik tidak memaksa produk menjadi satu aplikasi khusus untuk satu kampus.
