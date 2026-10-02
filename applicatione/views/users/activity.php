<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">Aktivitas User</h4>
            <div class="text-muted small">Riwayat halaman yang diakses user.</div>
        </div>
        <a href="<?= site_url('pengaturan/user') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke User
        </a>
    </div>

    <?php if ($this->session->flashdata('activity_message')): ?>
        <div class="alert alert-success py-2"><?= html_escape($this->session->flashdata('activity_message')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="get" action="<?= site_url('pengaturan/user/activity') ?>" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">Cari user / halaman</label>
                    <input type="search" name="q" class="form-control form-control-sm" value="<?= html_escape($filters['q']) ?>" placeholder="Username, nama, URL...">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small mb-1">User</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Semua user</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= (int) $user->id ?>" <?= (int) $filters['user_id'] === (int) $user->id ? 'selected' : '' ?>><?= html_escape($user->username) ?><?= $user->fullname ? ' - ' . html_escape($user->fullname) : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Dari tanggal</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= html_escape($filters['start_date']) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Sampai tanggal</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= html_escape($filters['end_date']) ?>">
                </div>
                <div class="col-8 col-md-2">
                    <label class="form-label small mb-1">Urutan tanggal</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="DESC" <?= $filters['sort'] === 'DESC' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="ASC" <?= $filters['sort'] === 'ASC' ? 'selected' : '' ?>>Terlama</option>
                    </select>
                </div>
                <div class="col-4 col-md-1 d-grid">
                    <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-2">
        <form method="post" action="<?= site_url('pengaturan/user/activity/clear') ?>" onsubmit="return confirm('Hapus aktivitas yang lebih lama dari 3 bulan?')">
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-broom me-1"></i>Sisakan 3 bulan terakhir</button>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 activity-table">
                <thead>
                    <tr>
                        <th>Tanggal aktivitas</th>
                        <th>User</th>
                        <th>Halaman</th>
                        <th>Detail akses</th>
                        <th>Method / IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activities)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada aktivitas.</td></tr>
                    <?php else: foreach ($activities as $activity): ?>
                        <tr>
                            <td data-label="Tanggal aktivitas" class="text-nowrap"><?= date('d-m-Y H:i:s', strtotime($activity->accessed_at)) ?></td>
                            <td data-label="User"><strong><?= html_escape($activity->fullname ?: $activity->username) ?></strong><br><small class="text-muted">@<?= html_escape($activity->username) ?></small></td>
                            <td data-label="Halaman"><span class="badge bg-light text-dark"><?= html_escape($activity->page_name) ?></span></td>
                            <td data-label="Detail akses"><code><?= html_escape($activity->page_url) ?></code><br><small class="text-muted text-break"><?= html_escape($activity->user_agent ?: '-') ?></small></td>
                            <td data-label="Method / IP"><?= html_escape($activity->http_method) ?><br><small class="text-muted"><?= html_escape($activity->ip_address ?: '-') ?></small></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav class="mt-3" aria-label="Pagination aktivitas">
            <ul class="pagination pagination-sm justify-content-end">
                <?php for ($i = 1; $i <= $total_pages; $i++): $query = array_merge($filters, ['page' => $i]); ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= site_url('pengaturan/user/activity') . '?' . http_build_query($query) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<style>
@media (max-width: 767.98px) {
    .activity-table thead { display: none; }
    .activity-table, .activity-table tbody, .activity-table tr, .activity-table td { display: block; width: 100%; }
    .activity-table tr { border-bottom: 1px solid #e5e7eb; padding: 10px 4px; }
    .activity-table td { border: 0; padding: 3px 10px 3px 125px; position: relative; min-height: 26px; }
    .activity-table td::before { content: attr(data-label); position: absolute; left: 10px; width: 110px; color: #64748b; font-size: 11px; font-weight: 600; }
}
</style>
