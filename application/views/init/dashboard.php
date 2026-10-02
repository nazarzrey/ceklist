<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <title>Init — WebKelas 06TPLE004</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/init.css'); ?>">
</head>
<body class="init-shell">
  <div class="init-shell-inner">
    <header class="init-topbar">
      <div class="init-brand">
        <a href="<?= site_url('init'); ?>" class="init-brand-link">
          <span class="init-mark"><i class="bi bi-gear-fill"></i></span>
          <span>WebKelas <small>06TPLE004</small></span>
        </a>
      </div>
      <div class="init-topbar-actions">
        <form method="post" action="<?= site_url('init/logout'); ?>">
          <input type="hidden" name="init_csrf" value="<?= html_escape($csrf_token); ?>">
          <button type="submit" class="btn btn-outline-light"><i class="bi bi-box-arrow-right"></i> Keluar</button>
        </form>
      </div>
    </header>

    <main class="init-main">
      <div class="init-card">
        <div class="init-card-head">
          <span class="init-mark"><i class="bi bi-database-check"></i></span>
          <div>
            <h1>Status Inisialisasi Database</h1>
            <small>Tabel yang diharapkan skrip CI3 dan kondisi saat ini</small>
          </div>
        </div>

        <?php if ($this->session->flashdata('init_message')): ?>
          <div class="alert alert-success mb-4">
            <i class="bi bi-check-circle-fill"></i>
            <?= html_escape($this->session->flashdata('init_message')); ?>
          </div>
        <?php endif; ?>

        <p class="text-muted small mb-4">
          Halaman ini otomatis memeriksa keberadaan tabel dan melakukan seed data
          saat dibuka. Agent lain yang memasuki proyek ini cukup membuka
          <code>/init</code> untuk melihat kondisi database tanpa perlu menebak
          skema atau seed manual.
        </p>

        <?php if (!empty($tables) && is_array($tables)): ?>
          <div class="table-responsive init-table-wrap">
            <table class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width: 22%">Tabel</th>
                  <th style="width: 12%" class="text-center">Ada?</th>
                  <th style="width: 12%" class="text-center">Baris</th>
                  <th>Kebutuhan di proyek ini</th>
                  <th style="width: 26%">Tindakan</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tables as $name => $row): ?>
                  <?php
                      $existsClass = $row['exists'] ? 'badge-success' : 'badge-danger';
                      $existsLabel = $row['exists'] ? 'Ya' : 'Tidak';
                  ?>
                  <tr>
                    <td>
                      <strong><?= html_escape($name); ?></strong>
                      <?php if ($name === 'courses'): ?>
                        <span class="badge bg-secondary ms-1">utama</span>
                      <?php elseif (str_starts_with($name, 'course_')): ?>
                        <span class="badge bg-secondary ms-1">relasi</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <span class="badge <?= $existsClass; ?>"><?= $existsLabel; ?></span>
                    </td>
                    <td class="text-center">
                      <?php if ($row['exists']): ?>
                        <span class="fw-semibold"><?= (int) $row['count']; ?></span>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td><?= html_escape($row['purpose']); ?></td>
                    <td>
                      <span class="init-action badge
                        <?php
                          if ($row['exists'] && $row['count'] > 0) {
                            echo 'bg-success';
                          } elseif ($row['exists']) {
                            echo 'bg-warning text-dark';
                          } else {
                            echo 'bg-info';
                          }
                        ?>">
                        <?= html_escape($row['action']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="init-footer mt-4">
            <div class="d-flex flex-wrap gap-2">
              <form method="post" action="<?= site_url('init/seed'); ?>" onsubmit="return confirm('Jalankan seed ulang? Tidak akan menghapus data yang sudah ada.');">
                <input type="hidden" name="init_csrf" value="<?= html_escape($csrf_token); ?>">
                <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-repeat"></i> Jalankan seed ulang</button>
              </form>
              <a href="<?= site_url(); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-house-door"></i> Kembali ke dashboard
              </a>
            </div>
            <p class="text-muted small mt-3">Akses setup dikonfigurasi melalui environment server dan dinonaktifkan jika kredensial belum disetel.</p>
          </div>
        <?php else: ?>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Tidak dapat membaca status tabel. Pastikan koneksi database sudah tepat
            di <code>application/config/database.php</code>.
          </div>
        <?php endif; ?>
      </div>
    </main>

    <footer class="init-footer-bottom">
      <small>WebKelas 06TPLE004 • Init page</small>
    </footer>
  </div>
</body>
</html>
