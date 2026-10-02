<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#041b90cc">
  <meta name="description" content="Info kelas, checklist pribadi, jadwal, dan materi WebKelas 06TPLE004.">
  <title>WebKelas 06TPLE004</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css'); ?>">
</head>
<body data-base-url="<?= html_escape(rtrim(base_url(), '/') . '/'); ?>" data-api-url="<?= html_escape(rtrim(site_url('api'), '/') . '/'); ?>">
  <div class="app-shell">
    <aside class="sidebar" id="sidebar">
      <div class="brand-row"><a class="brand" href="<?= site_url(); ?>"><span class="brand-mark"><i class="bi bi-grid-1x2-fill"></i></span><span>web<span>kelas</span></span></a><button class="icon-btn sidebar-close d-lg-none" id="sidebarClose" aria-label="Tutup menu"><i class="bi bi-x-lg"></i></button></div>
      <div class="class-switcher"><span class="class-icon"><i class="bi bi-mortarboard-fill"></i></span><span><small>Ruang kelas</small><strong>06TPLE004</strong></span><i class="bi bi-chevron-down ms-auto"></i></div>
      <p class="nav-label">Workspace</p>
      <nav class="main-nav" aria-label="Navigasi utama">
        <a href="#overview" class="nav-item-link" data-view="overview"><i class="bi bi-grid-1x2"></i><span>Ringkasan</span></a>
        <a href="#announcements" class="nav-item-link" data-view="announcements"><i class="bi bi-megaphone"></i><span>Info kelas</span><span class="nav-count" id="unreadCount">0</span></a>
        <a href="#tasks" class="nav-item-link" data-view="tasks"><i class="bi bi-check2-square"></i><span>Checklist saya</span><span class="nav-count" id="taskCount">0</span></a>
        <a href="#schedule" class="nav-item-link" data-view="schedule"><i class="bi bi-calendar3"></i><span>Jadwal kuliah</span></a>
        <a href="#courses" class="nav-item-link" data-view="courses"><i class="bi bi-book"></i><span>Mata kuliah</span></a>
      </nav>
      <p class="nav-label nav-label-spaced">Lainnya</p>
      <nav class="main-nav"><a href="#worklog" class="nav-item-link" data-view="worklog"><i class="bi bi-clock-history"></i><span>Worklog</span></a><a href="#profile" class="nav-item-link" data-view="profile"><i class="bi bi-person-circle"></i><span>Profil saya</span></a></nav>
      <div class="sidebar-bottom">
        <div class="semester-card"><span class="mini-spark"><i class="bi bi-stars"></i></span><div><small>Semester aktif</small><strong>Ganjil 2026/2027</strong></div></div>
        <button class="sidebar-user" id="profileOpen"><span class="avatar avatar-sm" id="sidebarAvatar">WK</span><span class="sidebar-user-copy"><strong id="sidebarName">Memuat profil</strong><small id="sidebarRole">Mahasiswa · 06TPLE004</small></span><i class="bi bi-gear ms-auto"></i></button>
      </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-shell">
      <header class="topbar">
        <div class="topbar-left"><button class="icon-btn d-lg-none" id="sidebarOpen" aria-label="Buka menu"><i class="bi bi-list"></i></button><div class="page-crumb"><span>Ruang Kelas</span><i class="bi bi-chevron-right"></i><strong id="pageTitle">Ringkasan kelas</strong></div></div>
        <div class="topbar-actions"><label class="search-box" for="globalSearch"><i class="bi bi-search"></i><input id="globalSearch" type="search" placeholder="Cari info, tugas, atau matkul" autocomplete="off"><kbd>Ctrl K</kbd></label><button class="icon-btn" id="themeToggle" aria-label="Ganti tema"><i class="bi bi-moon-stars"></i></button><button class="avatar avatar-md" id="profileOpenTop" aria-label="Buka profil">WK</button><button class="btn btn-outline-primary topbar-logout" id="logoutButton"><i class="bi bi-box-arrow-right"></i><span>Keluar</span></button></div>
      </header>
      <main class="page-content" id="app" tabindex="-1"><div class="loading-state"><i class="bi bi-arrow-repeat"></i><p>Memuat ruang kelas...</p></div></main>
      <footer class="site-footer"><span><i class="bi bi-shield-check"></i> Checklist pribadi · info kelas tersimpan</span><span>WebKelas <b>·</b> 06TPLE004</span></footer>
    </div>
  </div>
  <nav class="mobile-bottom-nav" aria-label="Navigasi utama mobile">
    <a href="#overview" class="mobile-nav-item" data-view="overview"><i class="bi bi-grid-1x2"></i><span>Ringkasan</span></a>
    <a href="#announcements" class="mobile-nav-item" data-view="announcements"><i class="bi bi-megaphone"></i><span>Info kelas</span></a>
    <a href="#tasks" class="mobile-nav-item" data-view="tasks"><i class="bi bi-check2-square"></i><span>Checklist</span></a>
    <a href="#schedule" class="mobile-nav-item" data-view="schedule"><i class="bi bi-calendar3"></i><span>Jadwal</span></a>
    <a href="#courses" class="mobile-nav-item" data-view="courses"><i class="bi bi-book"></i><span>Matkul</span></a>
  </nav>
  <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>

  <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content app-modal"><div class="modal-header border-0"><div><span class="eyebrow">Khusus ketua kelas</span><h2 class="modal-title" id="announcementModalTitle">Buat info &amp; checklist</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><form id="announcementForm"><div class="modal-body pt-0"><div id="announcementComposePane">
    <div class="form-field"><label for="announcementTitle">Judul info</label><input class="form-control" id="announcementTitle" name="title" maxlength="180" required placeholder="Contoh: Tugas Pemrograman II pertemuan 6"></div>
    <div class="form-field"><label for="announcementBody">Pesan kelas &amp; checklist <span>(awali tugas dengan -, * atau #; ketik @ di depan matkul)</span></label><div class="announcement-textarea-wrap"><textarea class="form-control" id="announcementBody" name="body" rows="9" placeholder="REKAPAN E-LEARNING MINGGU INI!!!&#10;*menu ceklist*&#10;- @Basis Data 2 (Pertemuan 5 &amp; Pertemuan 6)&#10;- @Teknologi Internet of Things (Pertemuan 4) - DATELINE: Jumat jam 23.00 WIB&#10;NOTE: Teks biasa tetap menjadi info kelas."></textarea><div class="mention-menu" id="courseMentionMenu" hidden></div></div><small class="form-text text-secondary">Setiap baris checklist boleh memakai matkul berbeda. Tanpa @matkul, item masuk checklist global. Teks biasa tetap tampil sebagai info.</small></div>
    <div class="form-field"><label for="announcementTasks">Pesan tambahan <span>(opsional, juga bisa berisi baris checklist)</span></label><div class="announcement-textarea-wrap"><textarea class="form-control" id="announcementTasks" name="tasks" rows="3" placeholder="Catatan tambahan atau - checklist tambahan"></textarea><div class="mention-menu" id="extraCourseMentionMenu" hidden></div></div></div>
    <div class="row g-3"><div class="col-md-6 form-field"><label for="announcementDeadline">Tenggat tanggal</label><input class="form-control" id="announcementDeadline" name="deadline_at" type="datetime-local"></div><div class="col-md-6 form-field"><label for="announcementDueLabel">Label pengingat</label><input class="form-control" id="announcementDueLabel" name="due_label" placeholder="Jumat, 23.00 WIB"></div></div>
    <div class="form-field"><label for="announcementSource">Sumber resmi (forum e-learning/dosen)</label><input class="form-control" id="announcementSource" name="source_url" type="url" maxlength="500" placeholder="https://... (tautan sumber instruksi)"></div>
    <div class="form-field mb-0"><label for="announcementReference">Link referensi/pengumpulan</label><input class="form-control" id="announcementReference" name="reference_url" type="url" placeholder="https://drive.google.com/..."></div>
  </div><div id="announcementPreviewPane" hidden></div></div><div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button><button type="button" class="btn btn-primary" id="announcementPreviewButton"><i class="bi bi-eye me-1"></i> Pratinjau checklist</button><button type="button" class="btn btn-outline-primary" id="announcementEditButton" hidden><i class="bi bi-pencil me-1"></i> Edit</button><button type="submit" class="btn btn-primary" id="announcementPublishButton" hidden><i class="bi bi-send me-1"></i> Terbitkan info</button></div></form></div></div></div>

  <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content app-modal"><div class="modal-header border-0"><div><span class="eyebrow">Akun WebKelas</span><h2 class="modal-title" id="profileModalTitle">Profil mahasiswa</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><form id="profileForm"><div class="modal-body pt-0"><div class="profile-identity"><span class="avatar avatar-md" id="profileAvatar">WK</span><div><strong id="profileNim">NIM</strong><small id="profileRole">Mahasiswa</small></div></div><div class="form-field"><label for="profileName">Nama tampilan</label><input class="form-control" id="profileName" name="display_name" maxlength="120" required></div><hr><p class="profile-password-note">Biarkan bagian password kosong jika tidak ingin menggantinya.</p><div class="form-field"><label for="currentPassword">Password saat ini</label><input class="form-control" id="currentPassword" name="current_password" type="password" autocomplete="current-password"></div><div class="form-field mb-0"><label for="newPassword">Password baru <span>(minimal 8 karakter)</span></label><input class="form-control" id="newPassword" name="new_password" type="password" autocomplete="new-password" minlength="8"></div></div><div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan profil</button></div></form></div></div></div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="<?= base_url('assets/js/ci-webkelas.js'); ?>"></script>
</body>
</html>
