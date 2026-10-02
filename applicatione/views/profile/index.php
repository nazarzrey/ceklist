<div class="row g-4">
    <div class="col-12 col-lg-4">
        <!-- Kartu Profil -->
        <div class="card-form">
            <div class="text-center mb-4">
                <div class="profile-avatar mx-auto">
                    <i class="fas fa-user-circle fa-4x text-primary"></i>
                </div>
                <h5 class="mt-2"><?= htmlspecialchars($user->fullname ?: $user->username) ?></h5>
                <span class="badge bg-primary"><?= $user->role ?></span>
                <p class="text-muted small mt-2">@<?= htmlspecialchars($user->username) ?></p>
            </div>
            
            <form id="formProfile">
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control-modern" id="fullname" name="fullname" value="<?= htmlspecialchars($user->fullname) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control-modern" id="email" name="email" value="<?= htmlspecialchars($user->email) ?>">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Warna Background</label>
                        <input type="color" class="form-control-modern" id="bg" name="bg" value="<?= htmlspecialchars($user->bg ?? '#f5f7fb') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Warna Teks</label>
                        <input type="color" class="form-control-modern" id="txt" name="txt" value="<?= htmlspecialchars($user->txt ?? '#1a2a3a') ?>">
                    </div>
                </div>
                <button type="submit" class="btn-save w-100" id="updateProfileBtn">
                    <i class="fas fa-save me-2"></i>Update Profil
                </button>
            </form>
        </div>
        
        <!-- Kartu Ganti Password -->
        <div class="card-form mt-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-key me-2"></i>Ganti Password</h6>
            <form id="formPassword">
                <div class="mb-3">
                    <label class="form-label">Password Lama</label>
                    <div class="profile-password-wrapper">
                        <input type="password" class="form-control-modern profile-password-input" id="old_password" name="old_password" required>
                        <button type="button" class="profile-password-toggle" data-target="#old_password" aria-label="Lihat password lama">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <div class="profile-password-wrapper">
                        <input type="password" class="form-control-modern profile-password-input" id="new_password" name="new_password" required>
                        <button type="button" class="profile-password-toggle" data-target="#new_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted">Minimal 4 karakter</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <div class="profile-password-wrapper">
                        <input type="password" class="form-control-modern profile-password-input" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="profile-password-toggle" data-target="#confirm_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="profilePasswordMatchMessage" class="profile-password-match"></div>
                </div>
                <button type="submit" class="btn-save w-100" id="changePasswordBtn" style="background: #e74c3c;">
                    <i class="fas fa-key me-2"></i>Ganti Password
                </button>
            </form>
        </div>
    </div>
    
    <div class="col-12 col-lg-8">
        <?php if ($is_admin): ?>
        <!-- ===================================================== -->
        <!-- MANAJEMEN RELASI / KASBON -->
        <!-- ===================================================== -->
        <div class="card-form">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold mb-0"><i class="fas fa-handshake me-2"></i>Manajemen Relasi Kasbon</h6>
                    <small class="text-muted">Kelola pasangan/keluarga untuk fitur kasbon</small>
                </div>
                <button type="button" class="btn-save btn-compact" id="tambahRelasiBtn">
                    <i class="fas fa-plus me-2"></i>Tambah Relasi
                </button>
            </div>
            
            <!-- Pending Requests (Incoming) -->
            <?php if (!empty($pending_requests)): ?>
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-bell text-warning"></i>
                    <span class="fw-semibold">Permintaan Masuk</span>
                    <span class="badge bg-warning text-dark"><?= count($pending_requests) ?></span>
                </div>
                <?php foreach ($pending_requests as $req): ?>
                <div class="partner-request-item">
                    <div class="partner-info">
                        <div class="partner-avatar bg-primary text-white">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($req->fullname ?: $req->username) ?></div>
                            <small class="text-muted">Meminta menjadi relasi Anda</small>
                            <small class="text-muted d-block"><?= date('d M Y H:i', strtotime($req->created_at)) ?></small>
                        </div>
                    </div>
                    <div class="partner-actions">
                        <button class="btn-approve" data-id="<?= $req->id ?>">
                            <i class="fas fa-check"></i> Setuju
                        </button>
                        <button class="btn-reject" data-id="<?= $req->id ?>">
                            <i class="fas fa-times"></i> Tolak
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Sent Requests -->
            <?php if (!empty($sent_requests)): ?>
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-paper-plane text-info"></i>
                    <span class="fw-semibold">Permintaan Terkirim</span>
                </div>
                <?php foreach ($sent_requests as $req): ?>
                <div class="partner-request-item sent">
                    <div class="partner-info">
                        <div class="partner-avatar bg-secondary text-white">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($req->fullname ?: $req->username) ?></div>
                            <small class="text-muted">Menunggu persetujuan</small>
                            <small class="text-muted d-block"><?= date('d M Y H:i', strtotime($req->created_at)) ?></small>
                        </div>
                    </div>
                    <div>
                        <span class="badge-pending">Pending</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Active Partners -->
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-users text-success"></i>
                    <span class="fw-semibold">Relasi Aktif</span>
                    <span class="badge-active"><?= count($approved_partners) ?></span>
                </div>
                
                <?php if (empty($approved_partners)): ?>
                    <div class="empty-partner">
                        <i class="fas fa-user-friends fa-3x text-muted"></i>
                        <p>Belum ada relasi aktif</p>
                        <button class="btn-add-small" id="emptyAddRelasiBtn">Tambah Relasi</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($approved_partners as $p): ?>
                    <div class="partner-item">
                        <div class="partner-info">
                            <div class="partner-avatar bg-success text-white">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($p->partner_fullname) ?></div>
                                <div class="partner-role">
                                    <i class="fas fa-tag me-1"></i><?= htmlspecialchars($p->role ?: 'Partner') ?>
                                </div>
                                <small class="text-muted">Terhubung sejak <?= date('d M Y', strtotime($p->approved_at)) ?></small>
                            </div>
                        </div>
                        <div class="partner-stats">
                            <div class="stat-badge">
                                <i class="fas fa-hand-holding-usd"></i>
                                <span id="loanStat_<?= $p->partner_id ?>">...</span>
                            </div>
                        </div>
                        <div class="partner-actions">
                            <a href="<?= site_url('loan?partner_id=' . $p->partner_id) ?>" class="btn-kasbon">
                                <i class="fas fa-money-bill-wave me-1"></i>Kasbon
                            </a>
                            <button class="btn-delete-partner" data-id="<?= $p->id ?>" data-name="<?= htmlspecialchars($p->partner_fullname) ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card-form mt-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-sitemap me-2"></i>Pengaturan Kasbon</h6>
                <div class="mb-3">
                    <label class="form-label">Parent Kasbon</label>
                    <div class="loan-profile-grid">
                        <label class="loan-profile-option">
                            <input type="radio" name="loan_parent" value="" <?= empty($loan_parent_id) ? 'checked' : '' ?>>
                            <span class="loan-profile-card">
                                <strong>Tidak ada parent</strong>
                                <small class="text-muted">Kasbon tidak diarahkan ke user lain</small>
                            </span>
                        </label>
                        <?php foreach ($approved_partners as $p): ?>
                        <label class="loan-profile-option">
                            <input type="radio" name="loan_parent" value="<?= $p->partner_id ?>" <?= (int) $loan_parent_id === (int) $p->partner_id ? 'checked' : '' ?>>
                            <span class="loan-profile-card">
                                <strong><?= htmlspecialchars($p->partner_fullname) ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($p->role ?: 'Partner') ?></small>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Child Kasbon</label>
                    <div class="loan-profile-grid">
                        <?php foreach ($approved_partners as $p): ?>
                        <label class="loan-profile-option">
                            <input type="checkbox" class="loan-child-check" value="<?= $p->partner_id ?>" <?= in_array((int) $p->partner_id, $loan_child_ids, true) ? 'checked' : '' ?>>
                            <span class="loan-profile-card">
                                <strong><?= htmlspecialchars($p->partner_fullname) ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($p->role ?: 'Partner') ?></small>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Riwayat Request -->
            <div class="card-form mt-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-history me-2"></i>Riwayat Permintaan Relasi</h6>
                
                <?php if (empty($request_history)): ?>
                    <div class="text-muted text-center py-3">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        <small>Belum ada riwayat permintaan</small>
                    </div>
                <?php else: ?>
                    <div class="history-list">
                        <?php foreach ($request_history as $req): ?>
                        <div class="history-item-mobile">
                            <div class="history-header">
                                <div class="history-avatar-mobile <?= $req->direction == 'sent' ? 'bg-secondary' : ($req->status == 'approved' ? 'bg-success' : 'bg-danger') ?>">
                                    <i class="fas <?= $req->direction == 'sent' ? 'fa-paper-plane' : ($req->status == 'approved' ? 'fa-check' : 'fa-times') ?>"></i>
                                </div>
                                <div class="history-info-mobile">
                                    <div class="history-name">
                                        <?= htmlspecialchars($req->fullname ?: $req->username) ?>
                                        <?php if ($req->direction == 'sent'): ?>
                                            <span class="badge-role-mobile">(Anda kirim)</span>
                                        <?php else: ?>
                                            <span class="badge-role-mobile">(Kirim ke Anda)</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="history-status-mobile">
                                        <?php if ($req->status == 'approved'): ?>
                                            <span class="badge-approved-mobile"><i class="fas fa-check-circle"></i> Disetujui</span>
                                        <?php else: ?>
                                            <span class="badge-rejected-mobile"><i class="fas fa-times-circle"></i> Ditolak</span>
                                        <?php endif; ?>
                                        <span class="history-date-mobile">• <?= date('d M Y H:i', strtotime($req->updated_at ?: $req->created_at)) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php 
                            // Tombol request ulang HANYA jika: ditolak + user adalah PENGIRIM
                            if ($req->status == 'rejected' && $req->direction == 'sent'): 
                            ?>
                            <div class="history-action-mobile">
                                <button class="btn-retry-request-mobile" data-user-id="<?= $req->user_child ?>" data-user-name="<?= htmlspecialchars($req->fullname ?: $req->username) ?>" data-loan-as="<?= htmlspecialchars($req->loan_relation_type ?? '') ?>">
                                    <i class="fas fa-redo me-1"></i>Kirim Ulang
                                </button>
                            </div>
                            <?php elseif ($req->status == 'rejected' && $req->direction == 'received'): ?>
                            <div class="history-action-mobile rejected-by-me">
                                <span><i class="fas fa-ban me-1"></i>Anda menolak</span>
                            </div>
                            <?php elseif ($req->status == 'approved'): ?>
                            <div class="history-action-mobile approved-status">
                                <span><i class="fas fa-check-circle me-1 text-success"></i>Terhubung</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Informasi Kasbon -->
        <div class="card-form mt-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Tentang Kasbon</h6>
            <div class="info-grid">
                <div class="info-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <div>
                        <strong>Transaksi Kasbon</strong>
                        <small>Cukup centang "Kasbon" saat tambah transaksi, pilih relasi</small>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-money-bill-wave text-primary"></i>
                    <div>
                        <strong>Pembayaran</strong>
                        <small>Bayar kasbon di menu Kasbon, bisa dicicil</small>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-chart-line text-warning"></i>
                    <div>
                        <strong>Laporan</strong>
                        <small>Lihat laporan kasbon per periode & per kategori</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Relasi -->
<div class="modal fade" id="modalTambahRelasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Tambah Relasi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Cari User</label>
                    <input type="text" class="form-control-modern" id="searchUser" placeholder="Ketik username atau nama...">
                    <div class="search-result mt-2" id="searchResult"></div>
                </div>
                <div class="mb-3 d-none" id="selectedUserInfo">
                    <label class="form-label">User Dipilih</label>
                    <div class="selected-user-card">
                        <span id="selectedUserName"></span>
                        <button type="button" id="clearSelectedUser">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jadikan sebagai apa</label>
                    <select class="form-select-modern" id="loanRelationType" required>
                        <option value="">Pilih hubungan</option>
                        <option value="parent">Ketua</option>
                        <option value="child">Anggota</option>
                    </select>
                    <small class="text-muted d-block mt-1">Pilihan ini dipakai saat approval untuk mengisi loan_parent / loan_child di tabel user.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Peran Relasi</label>
                    <select class="form-select-modern" id="partnerRole">
                        <option value="">Pilih peran (opsional)</option>
                        <option value="Suami">Suami</option>
                        <option value="Istri">Istri</option>
                        <option value="Ayah">Ayah</option>
                        <option value="Ibu">Ibu</option>
                        <option value="Anak">Anak</option>
                        <option value="Atasan">Atasan</option>
                        <option value="Bawahan">Bawahan</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-modal" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-primary-modal" id="kirimRequestBtn" disabled>Kirim Permintaan</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Profile Avatar */
.profile-avatar {
    width: 80px;
    height: 80px;
    background: #e8f0fe;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Password Wrapper */
.profile-password-wrapper {
    position: relative;
}

.profile-password-input {
    padding-right: 48px;
}

.profile-password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
}

.profile-password-toggle:hover {
    background: #eef2f6;
    color: #2d3e50;
}

.profile-password-match {
    min-height: 18px;
    margin-top: 6px;
    font-size: 0.75rem;
}

.profile-password-match.is-match { color: #16a34a; }
.profile-password-match.is-mismatch { color: #dc2626; }

/* Partner Items */
.partner-request-item,
.partner-item {
    background: #f8fafc;
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border: 1px solid #eef2f6;
}

.partner-request-item.sent {
    background: #fefce8;
}

.partner-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.partner-avatar {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.partner-role {
    font-size: 0.7rem;
    color: #7f8c8d;
}

.partner-stats .stat-badge {
    background: #eef2f6;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.partner-actions {
    display: flex;
    gap: 8px;
}

.btn-approve {
    background: #22c55e;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 30px;
    font-size: 0.75rem;
    cursor: pointer;
}

.btn-reject {
    background: #ef4444;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 30px;
    font-size: 0.75rem;
    cursor: pointer;
}

.btn-kasbon {
    background: #2d3e50;
    color: white;
    text-decoration: none;
    padding: 8px 14px;
    border-radius: 30px;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
}

.loan-profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 10px;
}

.loan-profile-option {
    display: block;
    cursor: pointer;
}

.loan-profile-option input {
    display: none;
}

.loan-profile-card {
    display: block;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    background: #fff;
    transition: all 0.2s ease;
}

.loan-profile-option input:checked + .loan-profile-card {
    border-color: #2d3e50;
    background: #f8fbff;
    box-shadow: 0 0 0 1px rgba(45, 62, 80, 0.15);
}

.btn-delete-partner {
    background: #fee2e2;
    color: #ef4444;
    border: none;
    padding: 8px 12px;
    border-radius: 30px;
    cursor: pointer;
}

.badge-pending {
    background: #fef3c7;
    color: #d97706;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-active {
    background: #dcfce7;
    color: #16a34a;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-left: 8px;
}

.empty-partner {
    text-align: center;
    padding: 30px;
    background: #f8fafc;
    border-radius: 16px;
}

.empty-partner p {
    margin: 12px 0;
    color: #94a3b8;
}

.btn-add-small {
    background: #2d3e50;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 30px;
    font-size: 0.8rem;
    cursor: pointer;
}

/* Search Result */
.search-result-item {
    padding: 12px;
    border-bottom: 1px solid #eef2f6;
    cursor: pointer;
    transition: background 0.2s;
}

.search-result-item:hover {
    background: #f1f5f9;
}

.selected-user-card {
    background: #e8f0fe;
    padding: 10px 14px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selected-user-card button {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.info-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 12px;
    background: #f8fafc;
    border-radius: 12px;
}

.info-item i {
    font-size: 1.2rem;
    margin-top: 2px;
}

.info-item div {
    display: flex;
    flex-direction: column;
}

.info-item small {
    color: #7f8c8d;
    font-size: 0.7rem;
}

/* Modal Buttons */
.btn-secondary-modal {
    background: #f1f5f9;
    border: none;
    padding: 10px 20px;
    border-radius: 40px;
    color: #475569;
}

.btn-primary-modal {
    background: #2d3e50;
    border: none;
    padding: 10px 20px;
    border-radius: 40px;
    color: white;
}

.btn-primary-modal:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ========== HISTORY LIST MOBILE STYLE ========== */
.history-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.history-item-mobile {
    background: #f8fafc;
    border-radius: 16px;
    padding: 14px;
    border: 1px solid #eef2f6;
    transition: all 0.2s;
}

.history-header {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.history-avatar-mobile {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.history-avatar-mobile.bg-success { background: #22c55e; }
.history-avatar-mobile.bg-danger { background: #ef4444; }
.history-avatar-mobile.bg-secondary { background: #94a3b8; }

.history-info-mobile {
    flex: 1;
    min-width: 0; /* Untuk truncate text */
}

.history-name {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 4px;
    word-break: break-word;
}

.badge-role-mobile {
    font-size: 0.65rem;
    font-weight: normal;
    color: #7f8c8d;
    margin-left: 4px;
}

.history-status-mobile {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    margin-top: 2px;
}

.badge-approved-mobile {
    background: #dcfce7;
    color: #16a34a;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.badge-rejected-mobile {
    background: #fee2e2;
    color: #dc2626;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.history-date-mobile {
    font-size: 0.65rem;
    color: #94a3b8;
}

.history-action-mobile {
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid #e2e8f0;
    text-align: right;
}

.btn-retry-request-mobile {
    background: #e8f0fe;
    border: none;
    padding: 8px 18px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 500;
    color: #2d3e50;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
}

.btn-retry-request-mobile:hover {
    background: #2d3e50;
    color: white;
}

.history-action-mobile.rejected-by-me,
.history-action-mobile.approved-status {
    text-align: left;
    margin-top: 10px;
    padding-top: 8px;
}

.history-action-mobile.rejected-by-me span,
.history-action-mobile.approved-status span {
    font-size: 0.7rem;
    color: #7f8c8d;
}

.history-action-mobile.approved-status span i {
    color: #22c55e;
}

/* Mobile extra small */
@media (max-width: 480px) {
    .history-item-mobile {
        padding: 12px;
    }
    
    .history-avatar-mobile {
        width: 38px;
        height: 38px;
    }
    
    .history-name {
        font-size: 0.85rem;
    }
    
    .badge-approved-mobile,
    .badge-rejected-mobile {
        padding: 2px 8px;
        font-size: 0.65rem;
    }
    
    .btn-retry-request-mobile {
        padding: 7px 16px;
        font-size: 0.7rem;
    }
}
</style>

<script>
$(document).ready(function() {
    let selectedUserId = null;
    let searchTimeout = null;
    
    // ========== PASSWORD VALIDATION ==========
    function validatePasswordMatch(showEmpty) {
        let new_pass = $('#new_password').val();
        let confirm_pass = $('#confirm_password').val();
        let $fields = $('#new_password, #confirm_password');
        let $message = $('#profilePasswordMatchMessage');
        
        $fields.removeClass('is-match is-mismatch');
        $message.removeClass('is-match is-mismatch').text('');
        
        if (!new_pass && !confirm_pass) return true;
        if ((!new_pass || !confirm_pass) && !showEmpty) return true;
        if (!new_pass || !confirm_pass) {
            $fields.addClass('is-mismatch');
            $message.addClass('is-mismatch').text('Password baru dan konfirmasi wajib diisi');
            return false;
        }
        if (new_pass !== confirm_pass) {
            $fields.addClass('is-mismatch');
            $message.addClass('is-mismatch').text('Password belum cocok');
            return false;
        }
        $fields.addClass('is-match');
        $message.addClass('is-match').text('Password cocok');
        return true;
    }
    
    $('#new_password, #confirm_password').on('input', function() {
        validatePasswordMatch(false);
    });
    
    $('.profile-password-toggle').on('click', function() {
        let $input = $($(this).data('target'));
        let isHidden = $input.attr('type') === 'password';
        $input.attr('type', isHidden ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye', !isHidden).toggleClass('fa-eye-slash', isHidden);
    });
    
    // ========== UPDATE PROFILE ==========
    $('#formProfile').submit(function(e) {
        e.preventDefault();
        let loanChildIds = $('.loan-child-check:checked').map(function() {
            return $(this).val();
        }).get();
        $.ajax({
            url: base_url + 'profile/update_profile',
            type: 'POST',
            data: {
                fullname: $('#fullname').val(),
                email: $('#email').val(),
                bg: $('#bg').val(),
                txt: $('#txt').val(),
                loan_parent: $('input[name="loan_parent"]:checked').val() || '',
                loan_child: loanChildIds.join(',')
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });
    
    // ========== CHANGE PASSWORD ==========
    $('#formPassword').submit(function(e) {
        e.preventDefault();
        if (!validatePasswordMatch(true)) {
            Swal.fire('Gagal', 'Konfirmasi password tidak sesuai', 'error');
            return;
        }
        $.ajax({
            url: base_url + 'profile/change_password',
            type: 'POST',
            data: {
                old_password: $('#old_password').val(),
                new_password: $('#new_password').val(),
                confirm_password: $('#confirm_password').val()
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    $('#formPassword')[0].reset();
                    validatePasswordMatch(false);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });
    
    // ========== PARTNER MANAGEMENT ==========
    $('#tambahRelasiBtn, #emptyAddRelasiBtn').click(function() {
        $('#searchUser').val('');
        $('#searchResult').html('');
        $('#selectedUserInfo').addClass('d-none');
        selectedUserId = null;
        $('#partnerRole').val('');
        $('#loanRelationType').val('');
        $('#kirimRequestBtn').prop('disabled', true);
        $('#modalTambahRelasi').modal('show');
    });
    
    $('#searchUser').on('keyup', function() {
        clearTimeout(searchTimeout);
        let keyword = $(this).val();
        if (keyword.length < 2) {
            $('#searchResult').html('');
            return;
        }
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: base_url + 'partner/cari_user',
                type: 'POST',
                data: { keyword: keyword },
                dataType: 'json',
                success: function(res) {
                    if (res.status && res.data.length) {
                        let html = '';
                        $.each(res.data, function(i, user) {
                            html += '<div class="search-result-item" data-id="' + user.id + '" data-name="' + (user.fullname || user.username) + '">';
                            html += '<div class="fw-semibold">' + (user.fullname || user.username) + '</div>';
                            html += '<small class="text-muted">@' + user.username + '</small>';
                            if (user.email) html += '<br><small>' + user.email + '</small>';
                            html += '</div>';
                        });
                        $('#searchResult').html(html);
                    } else {
                        $('#searchResult').html('<div class="text-muted text-center py-3">User tidak ditemukan</div>');
                    }
                }
            });
        }, 500);
    });
    
    $(document).on('click', '.search-result-item', function() {
        selectedUserId = $(this).data('id');
        let userName = $(this).data('name');
        $('#selectedUserName').text(userName);
        $('#selectedUserInfo').removeClass('d-none');
        $('#searchResult').html('');
        $('#searchUser').val('');
        $('#kirimRequestBtn').prop('disabled', false);
    });
    
    $('#clearSelectedUser').click(function() {
        selectedUserId = null;
        $('#selectedUserInfo').addClass('d-none');
        $('#kirimRequestBtn').prop('disabled', true);
    });
    
    $('#kirimRequestBtn').click(function() {
        if (!selectedUserId) return;
        
        $('#kirimRequestBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...');
        
        $.ajax({
            url: base_url + 'partner/kirim_request',
            type: 'POST',
            data: {
                partner_id: selectedUserId,
                role: $('#partnerRole').val(),
                loan_as: $('#loanRelationType').val()
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    $('#modalTambahRelasi').modal('hide');
                    setTimeout(() => refreshCurrentMainContent(window.location.href, { pushState: false }), 1000);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
                $('#kirimRequestBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Kirim Permintaan');
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
                $('#kirimRequestBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Kirim Permintaan');
            }
        });
    });
    
    // ========== APPROVE REQUEST ==========
    $(document).on('click', '.btn-approve', function() {
        let id = $(this).data('id');
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>');
        
        $.ajax({
            url: base_url + 'partner/approve',
            type: 'POST',
            data: { request_id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                    $(this).prop('disabled', false).html('<i class="fas fa-check me-1"></i>Setuju');
                }
            }
        });
    });
    
    // ========== REJECT REQUEST ==========
    $(document).on('click', '.btn-reject', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Tolak permintaan?',
            text: 'Anda yakin menolak permintaan relasi ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, tolak!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'partner/reject',
                    type: 'POST',
                    data: { request_id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Ditolak', res.message, 'success');
                            refreshCurrentMainContent(window.location.href, { pushState: false });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // ========== DELETE PARTNER ==========
    $(document).on('click', '.btn-delete-partner', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        
        Swal.fire({
            title: 'Hapus relasi?',
            text: 'Anda akan menghapus relasi dengan ' + name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'partner/hapus',
                    type: 'POST',
                    data: { partner_id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Berhasil', res.message, 'success');
                            refreshCurrentMainContent(window.location.href, { pushState: false });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // ========== RETRY REQUEST (Kirim Ulang) ==========
    // Untuk versi mobile (.btn-retry-request-mobile)
    $(document).on('click', '.btn-retry-request-mobile', function(e) {
        e.preventDefault();
        
        let userId = $(this).data('user-id');
        let userName = $(this).data('user-name');
        let loanAs = $(this).data('loan-as') || '';
        
        console.log('Retry request - userId:', userId, 'userName:', userName);
        
        if (!userId) {
            Swal.fire('Error', 'User ID tidak ditemukan', 'error');
            return;
        }
        
        Swal.fire({
            title: 'Kirim ulang permintaan?',
            text: 'Kirim ulang permintaan relasi ke ' + userName,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mengirim permintaan',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                url: base_url + 'partner/kirim_ulang_request',
                type: 'POST',
                    data: { partner_id: userId, role: '', loan_as: loanAs },
                dataType: 'json',
                success: function(res) {
                    console.log('Response:', res); // <-- Lihat ini
                    if (res.status) {
                        Swal.fire('Berhasil', res.message, 'success');
                        setTimeout(() => {
                            refreshCurrentMainContent(window.location.href, { pushState: false });
                        }, 1500);
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error:', xhr.responseText); // <-- Lihat ini
                    Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
                }
            });
            }
        });
    });
    
    // Untuk versi desktop (.btn-retry-request)
    $(document).on('click', '.btn-retry-request', function(e) {
        e.preventDefault();
        
        let userId = $(this).data('user-id');
        let userName = $(this).data('user-name');
        let loanAs = $(this).data('loan-as') || '';
        
        if (!userId) {
            Swal.fire('Error', 'User ID tidak ditemukan', 'error');
            return;
        }
        
        Swal.fire({
            title: 'Kirim ulang permintaan?',
            text: 'Kirim ulang permintaan relasi ke ' + userName,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: base_url + 'partner/kirim_ulang_request',
                    type: 'POST',
                    data: { 
                        partner_id: userId,
                        role: '',
                        loan_as: loanAs
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Berhasil', res.message, 'success');
                            setTimeout(() => {
                                refreshCurrentMainContent(window.location.href, { pushState: false });
                            }, 1500);
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
                    }
                });
            }
        });
    });
    
    // ========== LOAD LOAN STATS FOR EACH PARTNER ==========
    <?php if (!empty($approved_partners)): ?>
        <?php foreach ($approved_partners as $p): ?>
        $.ajax({
            url: base_url + 'loan/get_total_kasbon',
            type: 'GET',
            data: { partner_id: <?= $p->partner_id ?> },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#loanStat_<?= $p->partner_id ?>').html('Rp ' + formatRupiah(res.remaining.toString()));
                } else {
                    $('#loanStat_<?= $p->partner_id ?>').html('Rp 0');
                }
            },
            error: function() {
                $('#loanStat_<?= $p->partner_id ?>').html('Rp 0');
            }
        });
        <?php endforeach; ?>
    <?php endif; ?>
});
</script>
