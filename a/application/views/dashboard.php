<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#08111f">
  <meta name="description" content="Web app informasi tugas, jadwal, dan materi kelas 06TPLE004.">
  <title>WebKelas — 06TPLE004</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('styles.css'); ?>">
</head>
<body data-base-url="<?= html_escape(rtrim(base_url(), '/') . '/'); ?>" data-api-url="<?= html_escape(rtrim(site_url('api'), '/') . '/'); ?>">
  <div class="app-shell">
    <aside class="sidebar" id="sidebar">
      <div class="brand-row"><a class="brand" href="<?= site_url(); ?>"><span class="brand-mark"><i class="bi bi-grid-1x2-fill"></i></span><span>web<span>kelas</span></span></a><button class="icon-btn sidebar-close d-lg-none" id="sidebarClose" aria-label="Tutup menu"><i class="bi bi-x-lg"></i></button></div>
      <div class="class-switcher"><span class="class-icon"><i class="bi bi-mortarboard-fill"></i></span><span><small>Ruang kelas</small><strong>06TPLE004</strong></span><i class="bi bi-chevron-down ms-auto"></i></div>
      <p class="nav-label">Workspace</p>
      <nav class="main-nav" aria-label="Navigasi utama"><a href="#overview" class="nav-item-link active" data-view="overview"><i class="bi bi-grid-1x2"></i><span>Ringkasan</span></a><a href="#tasks" class="nav-item-link" data-view="tasks"><i class="bi bi-check2-square"></i><span>Tugas &amp; catatan</span><span class="nav-count" id="taskCount">0</span></a><a href="#schedule" class="nav-item-link" data-view="schedule"><i class="bi bi-calendar3"></i><span>Jadwal kuliah</span></a><a href="#courses" class="nav-item-link" data-view="courses"><i class="bi bi-book"></i><span>Mata kuliah</span></a></nav>
      <p class="nav-label nav-label-spaced">Lainnya</p><nav class="main-nav"><a href="#worklog" class="nav-item-link" data-view="worklog"><i class="bi bi-clock-history"></i><span>Worklog</span><span class="new-dot"></span></a></nav>
      <div class="sidebar-bottom"><div class="semester-card"><span class="mini-spark"><i class="bi bi-stars"></i></span><div><small>Semester aktif</small><strong>Ganjil 2026/2027</strong></div></div><div class="sidebar-user"><div class="avatar avatar-sm">AF</div><div><strong>Mahasiswa</strong><small>Kelas 06TPLE004</small></div><i class="bi bi-three-dots ms-auto"></i></div></div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-shell">
      <header class="topbar"><div class="topbar-left"><button class="icon-btn d-lg-none" id="sidebarOpen" aria-label="Buka menu"><i class="bi bi-list"></i></button><div class="page-crumb"><span>Ruang Kelas</span><i class="bi bi-chevron-right"></i><strong id="pageTitle">Ringkasan kelas</strong></div></div><div class="topbar-actions"><label class="search-box" for="globalSearch"><i class="bi bi-search"></i><input id="globalSearch" type="search" placeholder="Cari tugas atau mata kuliah" autocomplete="off"><kbd>⌘ K</kbd></label><button class="icon-btn" id="themeToggle" aria-label="Ganti tema"><i class="bi bi-moon-stars"></i></button><button class="avatar avatar-md" aria-label="Profil mahasiswa">AF</button></div></header>
      <main class="page-content" id="app" tabindex="-1"><div class="loading-state"><i class="bi bi-arrow-repeat"></i><p>Memuat data kelas...</p></div></main>
      <footer class="site-footer"><span><i class="bi bi-shield-check"></i> Data kelas dari REST API CI3</span><span>WebKelas <b>•</b> 06TPLE004</span></footer>
    </div>
  </div>
  <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
  <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content app-modal"><div class="modal-header border-0"><div><span class="eyebrow">Workspace</span><h2 class="modal-title" id="taskModalTitle">Tambah tugas baru</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><form id="taskForm"><div class="modal-body pt-0"><div class="form-field"><label for="taskTitleInput">Nama tugas</label><input class="form-control" id="taskTitleInput" name="title" required placeholder="Contoh: Upload laporan pertemuan 2"></div><div class="row g-3"><div class="col-md-7 form-field"><label for="taskCourseInput">Mata kuliah</label><select class="form-select" id="taskCourseInput" name="course_id" required></select></div><div class="col-md-5 form-field"><label for="taskTypeInput">Jenis</label><select class="form-select" id="taskTypeInput" name="type"><option>Tugas</option><option>Catatan</option><option>Pengingat</option></select></div></div><div class="row g-3"><div class="col-md-7 form-field"><label for="taskDueInput">Deadline</label><input class="form-control" id="taskDueInput" name="due" placeholder="Contoh: Sebelum UAS"></div><div class="col-md-5 form-field"><label for="taskPriorityInput">Prioritas</label><select class="form-select" id="taskPriorityInput" name="priority"><option value="high">Tinggi</option><option value="medium" selected>Sedang</option><option value="low">Rendah</option></select></div></div><div class="form-field mb-0"><label for="taskNoteInput">Catatan singkat <span>(opsional)</span></label><textarea class="form-control" id="taskNoteInput" name="note" rows="3" placeholder="Tambahkan konteks atau link yang perlu diingat"></textarea></div></div><div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Simpan tugas</button></div></form></div></div></div>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="<?= base_url('assets/js/ci-webkelas.js'); ?>"></script>
</body>
</html>
