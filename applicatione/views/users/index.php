<div class="row g-4">
    <div class="col-12">
        <div class="card-form">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                <div>
                    <h6 class="fw-bold mb-1"><i class="fas fa-users-cog me-2"></i>Kelola User</h6>
                    <small class="text-muted">Tambah, edit, dan hapus akun aplikasi.</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= site_url('pengaturan/user/activity') ?>" class="btn-period"><i class="fas fa-clock-rotate-left me-1"></i>Aktivitas</a>
                    <button type="button" class="btn-save btn-compact" id="tambahUserBtn"><i class="fas fa-plus me-2"></i>Tambah User</button>
                </div>
            </div>

            <form method="get" class="table-toolbar mb-3">
                <input type="search" name="q" class="form-control-modern" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari username, nama, email, role...">
                <button type="submit" class="btn-period">Cari</button>
                <?php if ($keyword !== ''): ?>
                    <a href="<?= site_url('users') ?>" class="btn-period">Reset</a>
                <?php endif; ?>
            </form>

            <?php if ($users): ?>
            <div class="table-responsive table-card">
                <table class="table table-hover align-middle mb-0 users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Nama & Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $u): ?>
                        <tr class="user-main-row">
                            <td data-label="User"><strong><?= (($page - 1) * 10) + $index + 1 ?>. <?= htmlspecialchars($u->username) ?></strong></td>
                            <td data-label="Nama & Email"><?= htmlspecialchars($u->fullname ?: '-') ?><small class="d-block text-muted"><?= htmlspecialchars($u->email ?: '-') ?></small></td>
                            <td data-label="Role"><span class="badge <?= $u->role == 'admin' ? 'badge-soft-danger' : 'badge-soft-success' ?>"><?= htmlspecialchars($u->role ?: 'user') ?></span></td>
                            <td data-label="Status">
                                <?php if (!empty($u->pwa_installed_at)): ?>
                                    <span class="badge badge-soft-success">Terpasang</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-secondary">Belum</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Aksi" class="text-end action-icons">
                                <i class="fas fa-edit text-primary edit-user" data-id="<?= $u->id ?>" role="button"></i>
                                <?php if ((int) $u->id !== (int) $this->session->userdata('user_id')): ?>
                                    <i class="fas fa-trash text-danger ms-2 delete-user" data-id="<?= $u->id ?>" data-name="<?= htmlspecialchars($u->username, ENT_QUOTES, 'UTF-8') ?>" role="button"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr class="user-detail-row">
                            <td colspan="5">
                                <div class="user-detail-grid">
                                    <span><small>Reset</small><strong><?= $u->reset_date ? date('d-m-y', strtotime($u->reset_date)) : '-' ?></strong></span>
                                    <span><small>Grafik</small><strong><?= $u->grafik == 'N' ? 'Tidak' : 'Ya' ?></strong></span>
                                    <span><small>Batas transaksi</small><strong>Rp <?= number_format($u->batas_transaksi ?? 100000000, 0, ',', '.') ?></strong></span>
                                    <span><small>Batas total</small><strong>Rp <?= number_format($u->batas_total_transaksi ?? 100000000, 0, ',', '.') ?></strong></span>
                                    <span><small>Terakhir login</small><strong><?= !empty($u->last_login) ? date('d-m-y H:i', strtotime($u->last_login)) : '-' ?></strong></span>
                                    <span><small>Install PWA</small><strong><?= !empty($u->pwa_installed_at) ? date('d-m-y H:i', strtotime($u->pwa_installed_at)) : '-' ?></strong></span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <nav class="mt-3" aria-label="Pagination user">
                <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link js-ajax-page-link" href="<?= site_url('users') . '?' . http_build_query(['q' => $keyword, 'page' => max(1, $page - 1)]) ?>">Prev</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link js-ajax-page-link" href="<?= site_url('users') . '?' . http_build_query(['q' => $keyword, 'page' => $i]) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link js-ajax-page-link" href="<?= site_url('users') . '?' . http_build_query(['q' => $keyword, 'page' => min($total_pages, $page + 1)]) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p>Belum ada user yang cocok</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.user-detail-row td {
    background: #f8fafc;
    border-top: 0;
    padding-top: 4px;
    padding-bottom: 10px;
}
.user-detail-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 8px 16px;
    padding: 0 12px 0 38px;
}
.user-detail-grid span { display: flex; flex-direction: column; gap: 2px; }
.user-detail-grid small { color: #64748b; font-size: 11px; }
.user-detail-grid strong { font-size: 12px; font-weight: 600; }
@media (max-width: 767.98px) {
    .user-detail-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 0 4px 0 28px; }
}
</style>

<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserTitle">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUser">
                    <input type="hidden" id="user_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control-modern" id="user_username" name="username" autocomplete="off" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control-modern" id="user_fullname" name="fullname">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control-modern" id="user_email" name="email">
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Role</label>
                            <select class="form-select-modern" id="user_role" name="role">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Grafik</label>
                            <select class="form-select-modern" id="user_grafik" name="grafik">
                                <option value="Y">Ya</option>
                                <option value="N">Tidak</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <label class="form-label">Batas per Transaksi</label>
                            <input type="text" class="form-control-modern rupiah-input" id="user_batas_transaksi" name="batas_transaksi" value="100.000.000" inputmode="numeric" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Batas Total Transaksi</label>
                            <input type="text" class="form-control-modern rupiah-input" id="user_batas_total_transaksi" name="batas_total_transaksi" value="100.000.000" inputmode="numeric" required>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Berlaku untuk role User. Admin tidak dibatasi.</small>
                    <div class="mt-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control-modern" id="user_password" name="password">
                        <small class="text-muted">Kosongkan saat edit jika password tidak diganti.</small>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Tanggal Reset</label>
                        <input type="date" class="form-control-modern" id="user_reset_date" name="reset_date">
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <label class="form-label">Warna BG</label>
                            <input type="text" class="form-control-modern" id="user_bg_color" name="bg_color" placeholder="#ffffff">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Warna Teks</label>
                            <input type="text" class="form-control-modern" id="user_txt_color" name="txt_color" placeholder="#1a2a3a">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-period" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-save btn-compact" id="simpanUserBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>
