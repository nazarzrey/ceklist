<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <title>Init — WebKelas 06TPLE004</title>
  <link rel="stylesheet" href="<?= base_url('assets/css/init.css'); ?>">
</head>
<body class="init-shell">
  <div class="init-card">
    <div class="init-card-head">
      <span class="init-mark"><i class="bi bi-gear-fill"></i></span>
      <div>
        <h1>Inisialisasi WebKelas</h1>
        <small>Kelas 06TPLE004 · Semester Ganjil 2026/2027</small>
      </div>
    </div>

    <form method="post" action="<?= site_url('init/login_post'); ?>" class="init-form">
      <input type="hidden" name="init_csrf" value="<?= html_escape($csrf_token); ?>">
      <?php if (!empty($error)): ?>
        <div class="init-alert init-alert-error">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span><?= html_escape($error); ?></span>
        </div>
      <?php endif; ?>

      <div class="form-field">
        <label for="username">Username</label>
        <input class="form-control" type="text" id="username" name="username"
               placeholder="Username administrator" required autocomplete="username">
      </div>

      <div class="form-field">
        <label for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password"
               placeholder="Masukkan password" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary init-btn">Masuk</button>
    </form>

    <p class="init-foot">
      Halaman ini digunakan untuk memeriksa dan menyiapkan tabel database
      sebelum aplikasi WebKelas dijalankan.
    </p>
  </div>
</body>
</html>
