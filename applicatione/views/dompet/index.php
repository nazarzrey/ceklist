<div class="row">
    <div class="col-12 mb-4">
        <div class="card-balance">
            <div class="balance-header">
                <span>Total Seluruh Saldo</span>
                <button type="button" class="privacy-toggle text-white" data-privacy-target="saldo" aria-label="Toggle saldo">
                    <i class="fas fa-eye-slash"></i>
                </button>
            </div>
            <div class="balance-amount privacy-value fw-bold" data-privacy-key="saldo"><?= number_format($total_saldo, 0, ',', '.') ?></div>
        </div>
    </div>

    <div class="col-12 mb-3">
        <button class="btn-add-full" id="tambahDompetBtn">
            <i class="fas fa-plus me-2"></i>Tambah Dompet Baru
        </button>
    </div>

    <div class="col-12">
        <div id="dompetList">
            <?php if ($dompet): ?>
                <?php foreach ($dompet as $d): ?>
                <?php $is_active = !isset($d->is_active) || (int) $d->is_active === 1; ?>
                <div class="wallet-card-item <?= $is_active ? '' : 'wallet-inactive' ?>" data-id="<?= $d->id ?>" data-urutan="<?= (int) ($d->urutan ?? 0) ?>">
                    <div class="wallet-icon" style="background: <?= $d->warna ?>20">
                        <i class="fas <?= $d->icon ?>" style="color: <?= $d->warna ?>"></i>
                    </div>
                    <div class="wallet-info">
                        <div class="wallet-info-top">
                            <h6 class="mb-0"><?= htmlspecialchars($d->nama_wallet) ?></h6>
                            <div class="wallet-saldo-mobile">
                                <h6 class="mb-0 fw-bold privacy-value" data-privacy-key="saldo"><?= number_format($d->saldo_awal, 0, ',', '.') ?></h6>
                            </div>
                        </div>
                        <div class="wallet-badges">
                            <small class="text-muted"><?= $is_active ? 'Saldo' : 'Nonaktif' ?></small>
                            <span class="badge bg-secondary" style="font-size:9px">#<?= (int) ($d->urutan ?? 0) ?></span>
                            <span class="badge bg-info" style="font-size:9px"><?= ($d->is_cash ?? 0) ? 'Cash' : 'Online' ?></span>
                            <?php if (!empty($d->is_virtual)): ?>
                            <span class="badge bg-warning text-dark" style="font-size:9px">Virtual</span>
                            <?php endif; ?>
                            <span class="badge <?= ($d->cash_count ?? 1) ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-size:9px">
                                <?= ($d->cash_count ?? 1) ? 'Masuk Hitungan' : 'Tidak Dihitung' ?>
                            </span>
                        </div>
                    </div>
                    <div class="wallet-saldo">
                        <h6 class="mb-0 fw-bold privacy-value" data-privacy-key="saldo"><?= number_format($d->saldo_awal, 0, ',', '.') ?></h6>
                    </div>
                    <div class="wallet-actions">
                        <i class="fas fa-edit text-primary edit-dompet" data-id="<?= $d->id ?>" style="cursor:pointer"></i>
                        <?php if ($is_active): ?>
                            <i class="fas fa-trash text-danger ms-2 delete-dompet" data-id="<?= $d->id ?>" style="cursor:pointer"></i>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-success activate-dompet" data-id="<?= $d->id ?>">
                                Aktifkan
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                    <p>Belum ada dompet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Dompet -->
<div class="modal fade" id="modalDompet" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Dompet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formDompet">
                    <input type="hidden" name="id" id="dompet_id">
                    <div class="mb-3">
                        <input type="text" class="form-control-modern" id="nama_wallet" name="nama_wallet" placeholder="Nama Dompet" required>
                    </div>
                    <div class="mb-3">
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="saldo_awal" name="saldo_awal" placeholder="Saldo Awal (Rp)" inputmode="numeric" pattern="[0-9.]*">
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <select class="form-select-modern" id="icon" name="icon">
                            <option value="fa-wallet">💰 Dompet</option>
                            <option value="fa-money-bill-wave">💵 Uang Tunai</option>
                            <option value="fa-university">🏦 Bank</option>
                            <option value="fa-mobile-alt">📱 E-Wallet</option>
                            <option value="fa-credit-card">💳 Kartu Kredit</option>
                            <option value="fa-piggy-bank">🐷 Tabungan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <input type="color" class="form-control-modern" id="warna" name="warna" value="#2d3e50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Dompet</label>
                        <select class="form-select-modern" id="is_cash" name="is_cash">
                            <option value="0">Online (E-Wallet, Bank, dll)</option>
                            <option value="1">Cash (Uang Tunai)</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="cash_count" name="cash_count" value="1" checked>
                        <label class="form-check-label" for="cash_count">Masuk ke Hitungan Keuangan</label>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="is_virtual" name="is_virtual" value="1">
                        <label class="form-check-label" for="is_virtual">Virtual (hanya pembukuan, tidak mempengaruhi saldo)</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" class="form-control-modern" id="urutan" name="urutan" value="0" min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="simpanDompet">Simpan</button>
            </div>
        </div>
    </div>
</div>

<style>
.wallet-card-item {
    background: white;
    border-radius: 20px;
    padding: 16px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid #eef2f6;
}

.wallet-icon {
    width: 48px;
    height: 48px;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.wallet-icon i {
    font-size: 1.5rem;
}

.wallet-info {
    flex: 2;
    min-width: 0;
}

.wallet-saldo {
    flex: 1;
    text-align: right;
}

.wallet-actions {
    display: flex;
    gap: 8px;
}

.wallet-inactive {
    opacity: 0.65;
}
</style>

<script>
$(document).ready(function() {
    $('#formDompet').on('submit', function(e) {
        e.preventDefault();
    });

    function walletCardHtml(d) {
        var isActive = d.is_active === undefined || parseInt(d.is_active) === 1;
        var cashCount = d.cash_count === undefined ? 1 : parseInt(d.cash_count);
        var isCash = d.is_cash === undefined ? 0 : parseInt(d.is_cash);
        var tipeBadge = isCash ? 'Cash' : 'Online';
        var badgeClass = cashCount ? 'bg-success' : 'bg-warning text-dark';
        var badgeText = cashCount ? 'Masuk Hitungan' : 'Tidak Dihitung';
        var actions = '';
        if (isActive) {
            actions = '<i class="fas fa-edit text-primary edit-dompet" data-id="' + d.id + '" style="cursor:pointer"></i>' +
                      '<i class="fas fa-trash text-danger ms-2 delete-dompet" data-id="' + d.id + '" style="cursor:pointer"></i>';
        } else {
            actions = '<button type="button" class="btn btn-sm btn-success activate-dompet" data-id="' + d.id + '">Aktifkan</button>';
        }
        return '<div class="wallet-card-item ' + (isActive ? '' : 'wallet-inactive') + '" data-id="' + d.id + '" data-urutan="' + (parseInt(d.urutan) || 0) + '">' +
            '<div class="wallet-icon" style="background: ' + d.warna + '20">' +
                '<i class="fas ' + d.icon + '" style="color: ' + d.warna + '"></i>' +
            '</div>' +
            '<div class="wallet-info">' +
                '<div class="wallet-info-top">' +
                    '<h6 class="mb-0">' + d.nama_wallet + '</h6>' +
                    '<div class="wallet-saldo-mobile">' +
                        '<h6 class="mb-0 fw-bold privacy-value" data-privacy-key="saldo">' + formatRupiah(d.saldo_awal) + '</h6>' +
                    '</div>' +
                '</div>' +
                '<div class="wallet-badges">' +
                    '<small class="text-muted">' + (isActive ? 'Saldo' : 'Nonaktif') + '</small>' +
                    '<span class="badge bg-secondary" style="font-size:9px">#' + (parseInt(d.urutan) || 0) + '</span>' +
                    '<span class="badge bg-info" style="font-size:9px">' + tipeBadge + '</span>' +
                    (parseInt(d.is_virtual) === 1 ? '<span class="badge bg-warning text-dark" style="font-size:9px">Virtual</span>' : '') +
                    '<span class="badge ' + badgeClass + '" style="font-size:9px">' + badgeText + '</span>' +
                '</div>' +
            '</div>' +
            '<div class="wallet-saldo">' +
                '<h6 class="mb-0 fw-bold privacy-value" data-privacy-key="saldo">' + formatRupiah(d.saldo_awal) + '</h6>' +
            '</div>' +
            '<div class="wallet-actions">' + actions + '</div>' +
        '</div>';
    }

    function refreshDompetList() {
        $.ajax({
            url: base_url + 'api/get_wallets',
            type: 'POST',
            data: 'show_all=1',
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data) {
                    var html = '';
                    $.each(res.data, function(i, d) {
                        html += walletCardHtml(d);
                    });
                    $('#dompetList').html(html || '<div class="empty-state"><i class="fas fa-wallet fa-3x text-muted mb-3"></i><p>Belum ada dompet</p></div>');
                    if (typeof initPrivacy === 'function') {
                        initPrivacy();
                    }
                }
                if (typeof loadMobileWalletSummary === 'function') {
                    loadMobileWalletSummary();
                }
            }
        });
    }

    $('#tambahDompetBtn').click(function() {
        $('#formDompet')[0].reset();
        $('#dompet_id').val('');
        $('#saldo_awal').val('');
        $('#is_cash').val('0');
        $('#cash_count').prop('checked', true);
        $('#is_virtual').prop('checked', false);
        var maxUrut = 0;
        $('.wallet-card-item').each(function() {
            var u = parseInt($(this).data('urutan')) || 0;
            if (u > maxUrut) maxUrut = u;
        });
        $('#urutan').val(maxUrut + 1);
        $('#modalDompet').modal('show');
    });
    
    $('#simpanDompet').click(function() {
        let id = $('#dompet_id').val();
        let url = id ? base_url + 'dompet/edit' : base_url + 'dompet/simpan';
        let nominal = $('#saldo_awal').val().replace(/\./g, '');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                id: id,
                nama_wallet: $('#nama_wallet').val(),
                saldo_awal: nominal,
                icon: $('#icon').val(),
                warna: $('#warna').val(),
                cash_count: $('#cash_count').is(':checked') ? 1 : 0,
                is_cash: $('#is_cash').val(),
                is_virtual: $('#is_virtual').is(':checked') ? 1 : 0,
                urutan: parseInt($('#urutan').val()) || 0
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#modalDompet').modal('hide');
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshDompetList();
                }
            }
        });
    });
    
    $(document).on('click', '.edit-dompet', function() {
        let id = $(this).data('id');
        $.ajax({
            url: base_url + 'dompet/get_data',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#dompet_id').val(res.data.id);
                    $('#nama_wallet').val(res.data.nama_wallet);
                    $('#saldo_awal').val(formatRupiah(res.data.saldo_awal));
                    $('#icon').val(res.data.icon);
                    $('#warna').val(res.data.warna);
                    $('#is_cash').val(parseInt(res.data.is_cash) === 1 ? '1' : '0');
                    $('#cash_count').prop('checked', parseInt(res.data.cash_count) === 1);
                    $('#is_virtual').prop('checked', parseInt(res.data.is_virtual) === 1);
                    $('#urutan').val(parseInt(res.data.urutan) || 0);
                    $('#modalDompet').modal('show');
                }
            }
        });
    });
    
    $(document).on('click', '.delete-dompet', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus dompet?',
            text: 'Jika dompet sudah dipakai transaksi, dompet hanya akan dinonaktifkan.',
            icon: 'warning',
            timer:500,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'dompet/hapus',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Terhapus!', res.message, 'success');
                            refreshDompetList();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.activate-dompet', function() {
        let id = $(this).data('id');
        $.ajax({
            url: base_url + 'dompet/aktifkan',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshDompetList();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });
});
</script>
