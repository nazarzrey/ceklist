<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Halaman Tidak Ditemukan</title>
<style>
body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    font-family: Inter, Arial, sans-serif;
    background: linear-gradient(180deg, #f8fafc 0%, #eef4f8 100%);
    color: #243447;
}

.soft-404 {
    width: 100%;
    max-width: 560px;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 20px;
    box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
    padding: 32px 28px;
}

.soft-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 999px;
    background: #e2e8f0;
    color: #475569;
    font-size: 13px;
    margin-bottom: 16px;
}

h1 {
    margin: 0 0 12px;
    font-size: 28px;
    line-height: 1.2;
}

p {
    margin: 0 0 12px;
    color: #526071;
    line-height: 1.6;
}

.soft-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 24px;
}

.soft-actions a {
    text-decoration: none;
    padding: 12px 16px;
    border-radius: 12px;
    font-weight: 600;
}

.primary-link {
    background: #2d3e50;
    color: #ffffff;
}

.secondary-link {
    background: #f1f5f9;
    color: #334155;
}
</style>
</head>
<body>
    <section class="soft-404">
        <div class="soft-badge">404</div>
        <h1>Halaman yang dicari belum ada</h1>
        <p>URL yang dibuka tidak ditemukan atau sudah berubah. Coba kembali ke halaman utama atau lanjut dari menu aplikasi.</p>
        <p>Jika ini link lama, kemungkinan rutenya sudah dipindahkan.</p>
        <div class="soft-actions">
            <a href="<?= site_url('dashboard') ?>" class="primary-link">Ke Dashboard</a>
            <a href="<?= site_url() ?>" class="secondary-link">Ke Halaman Awal</a>
        </div>
    </section>
</body>
</html>
