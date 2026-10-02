<div class="row g-4">
    <div class="col-12">
        <div class="card-form">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-handshake me-2"></i>Manajemen Relasi</h6>
                <button type="button" class="btn-save btn-compact" data-bs-toggle="modal" data-bs-target="#modalTambahPartner">
                    <i class="fas fa-plus me-2"></i>Tambah Relasi
                </button>
            </div>
            <p class="small text-muted mb-4">Relasi memungkinkan Anda melakukan kasbon dan melihat transaksi satu sama lain.</p>
            
            <!-- Pending Requests (Incoming) -->
            <?php if (!empty($pending_requests)): ?>
            <div class="mb-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-bell text-warning me-2"></i>Permintaan Masuk</h6>
                <?php foreach ($pending_requests as $req): ?>
                <div class="partner-request-item">
                    <div class="partner-info">
                        <div class="partner-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-user fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($req->fullname ?: $req->username) ?></div>
                            <small class="text-muted">Meminta menjadi relasi Anda</small>
                            <small class="text-muted d-block"><?= date('d M Y H:i', strtotime($req->created_at)) ?></small>
                        </div>
                    </div>
                    <div class="partner-actions">
                        <button class="btn btn-sm btn-success approve-request" data-id="<?= $req->id ?>">
                            <i class="fas fa-check me-1"></i>Setuju
                        </button>
                        <button class="btn btn-sm btn-danger reject-request" data-id="<?= $req->id ?>">
                            <i class="fas fa-times me-1"></i>Tolak
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Sent Requests -->
            <?php if (!empty($sent_requests)): ?>
            <div class="mb-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-paper-plane text-info me-2"></i>Permintaan Terkirim</h6>
                <?php foreach ($sent_requests as $req): ?>
                <div class="partner-request-item">
                    <div class="partner-info">
                        <div class="partner-avatar bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-user fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($req->fullname ?: $req->username) ?></div>
                            <small class="text-muted">Menunggu persetujuan</small>
                            <small class="text-muted d-block"><?= date('d M Y H:i', strtotime($req->created_at)) ?></small>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-warning text-dark">Pending</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Approved Partners -->
            <div>
                <h6 class="fw-bold mb-3"><i class="fas fa-users text-success me-2"></i>Relasi Aktif</h6>
                <?php if (empty($approved_partners)): ?>
                    <div class="empty-state py-4">
                        <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                        <p>Belum ada relasi aktif</p>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPartner">Tambah Relasi</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($approved_partners as $p): ?>
                    <div class="partner-item">
                        <div class="partner-info">
                            <div class="partner-avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-user-check fa-lg"></i>
                            </div>
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($p->partner_fullname) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($p->role ?: 'Partner') ?></small>
                                <small class="text-muted d-block">Terhubung sejak <?= date('d M Y', strtotime($p->approved_at)) ?></small>
                            </div>
                        </div>
                        <div class="partner-actions">
                            <a href="<?= site_url('loan?partner_id=' . $p->partner_id) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-hand-holding-usd me-1"></i>Kasbon
                            </a>
                            <button class="btn btn-sm btn-outline-danger delete-partner" data-id="<?= $p->id ?>" data-name="<?= htmlspecialchars($p->partner_fullname) ?>">
                                <i class="fas fa-trash me-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Relasi -->
<div class="modal fade" id="modalTambahPartner" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Relasi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Cari User</label>
                    <input type="text" class="form-control-modern" id="searchUser" placeholder="Ketik username atau nama...">
                    <div class="search-result mt-2" id="searchResult" style="max-height: 300px; overflow-y: auto;"></div>
                </div>
                <div class="mb-3 d-none" id="selectedUserInfo">
                    <label class="form-label">User Dipilih</label>
                    <div class="selected-user-card p-2 border rounded d-flex justify-content-between align-items-center">
                        <span id="selectedUserName"></span>
                        <button type="button" class="btn btn-sm btn-link text-danger" id="clearSelectedUser">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-save btn-compact" id="kirimRequestBtn" disabled>Kirim Permintaan</button>
            </div>
        </div>
    </div>
</div>

<style>
.partner-request-item, .partner-item {
    background: white;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border: 1px solid #eef2f6;
}

.partner-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.partner-avatar {
    flex-shrink: 0;
}

.partner-actions {
    display: flex;
    gap: 8px;
}

.search-result-item {
    padding: 12px;
    border-bottom: 1px solid #eef2f6;
    cursor: pointer;
    transition: background 0.2s;
}

.search-result-item:hover {
    background: #f8fafc;
}

.selected-user-card {
    background: #e8f0fe;
}
</style>

<script>
$(document).ready(function() {
    let selectedUserId = null;
    let searchTimeout = null;
    
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
        
        $.ajax({
            url: base_url + 'partner/kirim_request',
            type: 'POST',
            data: {
                partner_id: selectedUserId,
                role: $('#partnerRole').val()
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    $('#modalTambahPartner').modal('hide');
                    setTimeout(() => refreshCurrentMainContent(window.location.href, { pushState: false }), 1000);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });
    
    $('.approve-request').click(function() {
        let id = $(this).data('id');
        $.ajax({
            url: base_url + 'partner/approve',
            type: 'POST',
            data: { request_id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                }
            }
        });
    });
    
    $('.reject-request').click(function() {
        let id = $(this).data('id');
        $.ajax({
            url: base_url + 'partner/reject',
            type: 'POST',
            data: { request_id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                }
            }
        });
    });
    
    $('.delete-partner').click(function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        
        Swal.fire({
            title: 'Hapus relasi?',
            text: 'Anda akan menghapus relasi dengan ' + name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
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
                        }
                    }
                });
            }
        });
    });
});
</script>