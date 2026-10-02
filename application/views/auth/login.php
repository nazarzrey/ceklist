<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#111714">
  <title>Masuk · WebKelas 06TPLE004</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/auth.css'); ?>">
</head>
<body class="auth-shell">
  <main class="auth-layout">
    <section class="auth-aside">
      <a class="brand auth-brand" href="<?= site_url(); ?>"><span class="brand-mark"><i class="bi bi-grid-1x2-fill"></i></span><span>web<span>kelas</span></span></a>
      <div class="auth-aside-copy">
        <span class="eyebrow"><i class="bi bi-stars me-2"></i>RUANG KELAS 06TPLE004</span>
        <h1>Info kelas tetap rapi, tugas tidak tenggelam.</h1>
        <p>Broadcast ketua kelas, materi, dan checklist pribadi ada di satu tempat. Tandai tugasmu tanpa mengubah progres teman.</p>
        <div class="auth-feature-list"><span><i class="bi bi-bell"></i> Pengumuman tersimpan dan mudah dicari</span><span><i class="bi bi-check2-circle"></i> Checklist tugas untuk akunmu sendiri</span><span><i class="bi bi-book"></i> Ringkasan per mata kuliah</span></div>
      </div>
      <div class="auth-aside-foot"><i class="bi bi-shield-check me-2"></i>Data mahasiswa mengikuti daftar kelas UNPAM</div>
    </section>
    <section class="auth-main">
      <form class="auth-card" method="post" action="<?= site_url('auth/login'); ?>">
        <span class="auth-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="eyebrow">SELAMAT DATANG</span>
        <h2>Masuk ke WebKelas</h2>
        <p class="auth-subtitle">Gunakan NIM yang terdaftar di kelas 06TPLE004.</p>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= html_escape($error); ?></div><?php endif; ?>
        <div class="form-field"><label for="nim">NIM</label><input class="form-control" id="nim" name="nim" inputmode="numeric" autocomplete="username" maxlength="20" required placeholder="Masukkan NIM terdaftar"></div>
        <div class="form-field"><label for="password">Password</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required placeholder="6 digit terakhir NIM"></div>
        <input type="hidden" id="returnRoute" name="return_route" value="">
        <button class="btn btn-primary auth-submit" type="submit">Masuk <i class="bi bi-arrow-right"></i></button>
        <div class="auth-hint"><i class="bi bi-info-circle"></i><span>Password awal adalah 6 digit terakhir NIM. Setelah masuk, kamu bisa menggantinya di profil.</span></div>
      </form>
    </section>
  </main>
  <script>document.getElementById('returnRoute').value = window.location.hash.slice(1);</script>
</body>
</html>
