<div class="row g-3">
    <div class="col-12">
        <button class="btn-add-full" id="tambahPengingatBtn">
            <i class="fas fa-plus me-2"></i>Tambah Pengingat
        </button>
    </div>

    <div class="col-12">
        <?php if ($pengingat): ?>
            <div class="reminder-list-grid">
            <?php foreach ($pengingat as $p): ?>
                <div class="reminder-item">
                    <div class="reminder-main">
                        <div class="reminder-icon">
                            <i class="fas <?= $p->kategori === 'kendaraan' ? 'fa-car' : 'fa-bell' ?>"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($p->nama) ?></h6>
                            <?php if (!empty($p->is_dummy)): ?>
                                <span class="badge bg-secondary reminder-dummy-badge">Dummy</span>
                            <?php endif; ?>
                            <small class="text-muted">
                                <?= date('d-m-Y', strtotime($p->next_date)) ?> &bull; <?= $p->kategori === 'kendaraan' ? 'Kendaraan' : 'Lainnya' ?> &bull; tiap <?= (int) $p->cycle_months ?> bulan
                            </small>
                            <div><small class="text-muted">Mulai: <?= date('d-m-Y', strtotime($p->tanggal_mulai)) ?></small></div>
                            <div class="reminder-remaining <?= $p->remaining_days <= 0 ? 'is-due' : '' ?>">
                                <?php if ($p->remaining_days < 0): ?>
                                    Lewat <?= abs((int) $p->remaining_days) ?> hari
                                <?php elseif ((int) $p->remaining_days === 0): ?>
                                    Jatuh tempo hari ini
                                <?php else: ?>
                                    Sisa <?= (int) $p->remaining_days ?> hari
                                <?php endif; ?>
                            </div>
                            <?php if ($p->kategori === 'kendaraan' && $p->km_terakhir !== null): ?>
                                <div><small class="text-muted"><i class="fas fa-gauge-high me-1"></i>KM terakhir: <?= number_format((int) $p->km_terakhir, 0, ',', '.') ?></small></div>
                            <?php endif; ?>
                            <?php if (!empty($p->catatan)): ?>
                                <div><small><?= htmlspecialchars($p->catatan) ?></small></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="reminder-actions">
                        <span class="badge reminder-badge bg-<?= $p->status_badge['class'] ?>"><?= $p->status_badge['label'] ?></span>
                        <button type="button" class="btn reminder-action-btn reminder-action-done selesai-pengingat" data-id="<?= $p->id ?>" data-history-count="<?= (int) $p->history_count ?>" data-start-date="<?= htmlspecialchars($p->tanggal_mulai, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="btn reminder-action-btn reminder-action-history riwayat-pengingat" data-id="<?= $p->id ?>" data-nama="<?= htmlspecialchars($p->nama, ENT_QUOTES, 'UTF-8') ?>" title="<?= $p->history_count ? 'Riwayat' : 'Belum ada riwayat' ?>" <?= !$p->history_count ? 'disabled' : '' ?> >
                            <i class="fas fa-clock-rotate-left"></i>
                        </button>
                        <button type="button" class="btn reminder-action-btn reminder-action-edit edit-pengingat" data-id="<?= $p->id ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn reminder-action-btn reminder-action-delete hapus-pengingat" data-id="<?= $p->id ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                <p>Belum ada pengingat</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalPengingat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengingat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPengingat">
                    <input type="hidden" id="pengingat_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control-modern" id="nama" name="nama" placeholder="Ganti oli" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-control-modern" id="kategori" name="kategori">
                            <option value="lainnya">Lainnya</option>
                            <option value="kendaraan">Kendaraan</option>
                        </select>
                    </div>
                    <div class="mb-3" id="kmTerakhirGroup" style="display:none">
                        <label class="form-label">KM Terakhir <small class="text-muted" id="kmRequiredHint">(opsional)</small></label>
                        <input type="number" class="form-control-modern" id="km_terakhir" name="km_terakhir" min="0" step="1" placeholder="Contoh: 12500">
                        <small class="text-muted">Dipakai sebagai track record kendaraan.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control-modern" id="tanggal_mulai" name="tanggal_mulai" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Siklus Bulanan</label>
                        <input type="number" class="form-control-modern" id="cycle_months" name="cycle_months" value="1" min="1" max="36" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control-modern" id="catatan" name="catatan" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-save btn-compact" id="simpanPengingat">Simpan</button>
            </div>
        </div>
    </div>
</div>

<button type="button" class="reminder-refresh-fab" id="refreshPengingatBtn" title="Refresh pengingat" aria-label="Refresh pengingat" onclick="window.location.reload(); return false;">
    <i class="fas fa-refresh"></i>
</button>

<div class="modal fade" id="modalRiwayatPengingat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Riwayat Pengingat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="riwayatPengingatList"><div class="text-center text-muted">Memuat...</div></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Riwayat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editHistoryId">
                <div class="mb-3">
                    <label for="editHistoryDate" class="form-label">Tanggal selesai</label>
                    <input type="date" class="form-control-modern" id="editHistoryDate" required>
                </div>
                <div class="mb-2">
                    <label for="editHistoryKm" class="form-label">KM terakhir</label>
                    <input type="number" class="form-control-modern" id="editHistoryKm" min="0" step="1" inputmode="numeric" autocomplete="off" placeholder="Boleh dikosongkan">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="editHistoryClearKm">
                    <label class="form-check-label" for="editHistoryClearKm">Kosongkan KM</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveHistoryEdit">Simpan</button>
            </div>
        </div>
    </div>
</div>

<style>
.reminder-item {
    background: #ffffff;
    border: 1px solid #eef2f6;
    border-radius: 18px;
    padding: 14px;
    margin-bottom: 0;
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    gap: 14px;
    min-height: 178px;
    flex-direction: column;
}

.reminder-list-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.reminder-main {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.reminder-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #fff7e6;
    color: #d97706;
    display: flex;
    align-items: center;
    justify-content: center;
}

.reminder-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: flex-end;
    margin-top: auto;
}

.reminder-remaining {
    color: #2563eb;
    font-size: 12px;
    font-weight: 700;
    margin-top: 4px;
}

.reminder-remaining.is-due {
    color: #dc2626;
}

.reminder-badge {
    border-radius: 999px;
    padding: 8px 12px;
    font-weight: 700;
}

.reminder-action-btn {
    width: 38px;
    height: 38px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.reminder-action-done {
    background: #22c55e;
    color: #ffffff;
}

.reminder-action-edit {
    background: #2563eb;
    color: #ffffff;
}

.reminder-action-history { background: #64748b; color: #ffffff; }

.reminder-dummy-badge {
    display: inline-block;
    margin-bottom: 5px;
    font-size: 10px;
    letter-spacing: .2px;
}

.history-table-wrap {
    border: 1px solid #eef2f6;
    border-radius: 12px;
}

.history-table-wrap th {
    background: #f8fafc;
    color: #64748b;
    font-size: 12px;
    white-space: nowrap;
}

.history-table-wrap td {
    font-size: 13px;
}

.history-km-value {
    border: 0;
    background: transparent;
    padding: 2px 4px;
    color: #64748b;
    cursor: pointer;
}

.history-km-value:hover {
    color: #2563eb;
    text-decoration: underline;
}

#editHistoryKm {
    appearance: auto;
    -webkit-appearance: auto;
    pointer-events: auto;
    user-select: text;
}

#editHistoryKm::-webkit-inner-spin-button,
#editHistoryKm::-webkit-outer-spin-button {
    opacity: 1;
}

.reminder-refresh-fab {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 1040;
    width: 48px;
    height: 48px;
    border: 0;
    border-radius: 50%;
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
    font-size: 18px;
}

.reminder-refresh-fab:hover {
    background: #1d4ed8;
    color: #ffffff;
}

.reminder-action-delete {
    background: #ef4444;
    color: #ffffff;
}

.reminder-action-btn:hover {
    color: #ffffff;
    filter: brightness(0.94);
}

.reminder-action-btn:disabled {
    cursor: not-allowed;
    opacity: .45;
    filter: grayscale(1);
}

@media (max-width: 991.98px) {
    .reminder-list-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 575.98px) {
    .reminder-list-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .reminder-item {
        min-height: 0;
        padding: 10px;
        gap: 8px;
        border-radius: 14px;
    }

    .reminder-main {
        gap: 8px;
    }

    .reminder-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        flex: 0 0 34px;
    }

    .reminder-actions {
        gap: 4px;
    }

    .reminder-action-btn {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }
}

@media (max-width: 767.98px) {
    .reminder-refresh-fab {
        right: 16px;
        bottom: 80px;
    }

    body.pwa-install-available .reminder-refresh-fab {
        bottom: 132px;
    }

    .history-table-wrap {
        border: 0;
    }

    .history-table-wrap table,
    .history-table-wrap tbody,
    .history-table-wrap tr,
    .history-table-wrap td {
        display: block;
        width: 100%;
    }

    .history-table-wrap thead {
        display: none;
    }

    .history-table-wrap tr {
        margin-bottom: 10px;
        padding: 8px 10px;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        background: #ffffff;
    }

    .history-table-wrap td {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 6px 0;
        border: 0;
        text-align: right !important;
    }

    .history-table-wrap td::before {
        content: attr(data-label);
        color: #64748b;
        font-weight: 600;
        text-align: left;
    }
}
</style>

<script>
$(document).ready(function() {
    $('#refreshPengingatBtn').click(function() {
        $(this).find('i').addClass('fa-spin');
        refreshCurrentMainContent(window.location.href, { pushState: false });
    });

    function toggleKm() {
        let isVehicle = $('#kategori').val() === 'kendaraan';
        $('#kmTerakhirGroup').toggle(isVehicle);
        $('#km_terakhir').prop('required', false);
        $('#kmRequiredHint').text('(opsional)');
        if ($('#kategori').val() !== 'kendaraan') $('#km_terakhir').val('');
    }

    $('#kategori').change(toggleKm);
    $('#tanggal_mulai').change(toggleKm);
    $('#tambahPengingatBtn').click(function() {
        $('#formPengingat')[0].reset();
        $('#pengingat_id').val('');
        $('#tanggal_mulai').val('<?= date('Y-m-d') ?>');
        $('#cycle_months').val(1);
        $('#kategori').val('lainnya');
        $('#km_terakhir').val('');
        toggleKm();
        $('#modalPengingat .modal-title').text('Tambah Pengingat');
        $('#modalPengingat').modal('show');
    });

    $('#simpanPengingat').click(function() {
        let id = $('#pengingat_id').val();
        $.ajax({
            url: base_url + (id ? 'pengingat/edit' : 'pengingat/simpan'),
            type: 'POST',
            data: {
                id: id,
                nama: $('#nama').val(),
                tanggal_mulai: $('#tanggal_mulai').val(),
                cycle_months: $('#cycle_months').val(),
                kategori: $('#kategori').val(),
                km_terakhir: $('#km_terakhir').val(),
                catatan: $('#catatan').val()
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#modalPengingat').modal('hide');
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });

    $('.edit-pengingat').click(function() {
        $.ajax({
            url: base_url + 'pengingat/get_data',
            type: 'POST',
            data: { id: $(this).data('id') },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#pengingat_id').val(res.data.id);
                    $('#nama').val(res.data.nama);
                    $('#tanggal_mulai').val(res.data.next_date);
                    $('#cycle_months').val(res.data.cycle_months);
                    $('#kategori').val(res.data.kategori || 'lainnya');
                    $('#km_terakhir').val(res.data.km_terakhir || '');
                    toggleKm();
                    $('#catatan').val(res.data.catatan);
                    $('#modalPengingat .modal-title').text('Edit Pengingat');
                    $('#modalPengingat').modal('show');
                }
            }
        });
    });

    $('.selesai-pengingat').click(function() {
        let id = $(this).data('id');
        let kmInput = '';
        let item = $(this).closest('.reminder-item');
        let isVehicle = item.find('.fa-car').length > 0;
        let complete = function() {
        $.ajax({
            url: base_url + 'pengingat/selesai',
            type: 'POST',
            data: { id: id, km_terakhir: kmInput },
            dataType: 'json',
            success: function(res) {
                if (res.code === 'km_required') {
                    Swal.fire('KM wajib diisi', res.message, 'warning');
                    return;
                }
                Swal.fire(res.status ? 'Berhasil' : 'Gagal', res.message, res.status ? 'success' : 'error')
                    .then(() => refreshCurrentMainContent(window.location.href, { pushState: false }));
            }
        });
        };
        Swal.fire({
            title: 'Tandai pengingat selesai?',
            text: 'Jadwal berikutnya akan dibuat sesuai siklus.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, selesai',
            cancelButtonText: 'Batal'
        }).then(function(confirmResult) {
            if (!confirmResult.isConfirmed) return;
            if (isVehicle) {
                Swal.fire({ title: 'KM terakhir kendaraan', text: 'Masukkan KM jika ingin mencatat odometer (opsional).', input: 'number', inputAttributes: { min: 0, step: 1 }, inputValue: '', showCancelButton: true, confirmButtonText: 'Selesai' }).then(function(result) {
                    if (result.isConfirmed) { kmInput = result.value || ''; complete(); }
                });
            } else {
                complete();
            }
        });
    });

    $('.riwayat-pengingat').click(function() {
        let id = $(this).data('id');
        $('#modalRiwayatPengingat .modal-title').text('Riwayat: ' + $(this).data('nama'));
        $('#riwayatPengingatList').html('<div class="text-center text-muted">Memuat...</div>');
        $('#modalRiwayatPengingat').modal('show');
        $.post(base_url + 'pengingat/riwayat', { id: id }, function(res) {
            if (!res.data || !res.data.length) {
                $('#riwayatPengingatList').html('<div class="text-center text-muted py-3">Belum ada riwayat.</div>'); return;
            }
            let html = '<div class="table-responsive history-table-wrap"><table class="table align-middle mb-0"><thead><tr><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Jatuh Tempo</th><th>Selisih Waktu</th><th>KM</th><th>Keterangan</th><th class="text-end">Aksi</th></tr></thead><tbody>';
            res.data.forEach(function(row) {
                let completed = new Date(row.completed_at.replace(' ', 'T'));
                let interval = '-';
                if (row.selisih_hari !== null) {
                    let months = Number(row.selisih_bulan || 0);
                    let days = Number(row.sisa_hari || 0);
                    interval = (months ? months + ' bulan ' : '') + (days ? days + ' hari' : (!months ? '0 hari' : ''));
                }
                let keterangan = Number(row.is_baseline) === 1 ? '<span class="badge bg-info">KM awal</span>' : (Number(row.checklist_only) === 1 ? '<span class="badge bg-secondary">Hanya checklist</span>' : '<span class="badge bg-success">Ada KM</span>');
                let historyKm = row.km_terakhir === null ? '' : row.km_terakhir;
                let historyEditData = ' data-id="' + row.id + '" data-km="' + historyKm + '" data-date="' + row.completed_at.substring(0, 10) + '"';
                let kmButton = historyKm === '' ? '<span class="text-muted">-</span>' : '<button type="button" class="history-km-value"' + historyEditData + ' title="Klik untuk edit KM"><i class="fas fa-gauge-high me-1"></i>' + Number(historyKm).toLocaleString('id-ID') + ' KM' + (row.selisih_km !== null && row.selisih_km !== undefined ? ' <span class="text-primary">(+' + Number(row.selisih_km).toLocaleString('id-ID') + ')</span>' : '') + '</button>';
                html += '<tr class="history-row" data-id="' + row.id + '"><td data-label="Tanggal Mulai">' + (row.tanggal_mulai ? new Date(row.tanggal_mulai + 'T00:00:00').toLocaleDateString('id-ID') : '-') + '</td><td data-label="Tanggal Selesai"><strong>' + completed.toLocaleDateString('id-ID') + '</strong></td><td data-label="Jatuh Tempo">' + new Date(row.due_date + 'T00:00:00').toLocaleDateString('id-ID') + '</td><td data-label="Selisih Waktu"><span class="text-muted"><i class="fas fa-calendar-days me-1"></i>' + interval + '</span></td><td data-label="KM">' + kmButton + '</td><td data-label="Keterangan">' + keterangan + '</td><td data-label="Aksi" class="text-end"><button type="button" class="btn btn-sm btn-outline-primary edit-history"' + historyEditData + '><i class="fas fa-edit me-1"></i>Edit</button> <button type="button" class="btn btn-sm btn-outline-danger delete-history" data-id="' + row.id + '"><i class="fas fa-trash me-1"></i>Hapus</button></td></tr>';
            });
            $('#riwayatPengingatList').html(html + '</tbody></table></div>');
        }, 'json');
    });

    $(document).on('click', '.edit-history, .history-km-value', function() {
        let button = $(this);
        $('#editHistoryId').val(button.attr('data-id'));
        $('#editHistoryDate').val(button.attr('data-date'));
        $('#editHistoryKm').val(button.attr('data-km') || '').prop('disabled', false);
        $('#editHistoryClearKm').prop('checked', false);
        $('#modalEditHistory').one('shown.bs.modal', function() { $('#editHistoryKm').trigger('focus').select(); });
        $('#modalEditHistory').modal('show');
    });

    $('#editHistoryClearKm').on('change', function() {
        $('#editHistoryKm').prop('disabled', $(this).is(':checked'));
        if ($(this).is(':checked')) $('#editHistoryKm').val('');
    });

    $('#saveHistoryEdit').on('click', function() {
        let button = $(this);
        let km = $('#editHistoryClearKm').is(':checked') ? '' : $('#editHistoryKm').val();
        let completedAt = $('#editHistoryDate').val();
        if (!completedAt) {
            $('#editHistoryDate').trigger('focus');
            return;
        }
        button.prop('disabled', true);
        $.post(base_url + 'pengingat/riwayat_edit', {
            id: $('#editHistoryId').val(), completed_at: completedAt, km_terakhir: km
        }, function(res) {
            $('#modalEditHistory').modal('hide');
            if (res.status) window.location.reload();
            else Swal.fire('Gagal', res.message, 'error');
        }, 'json').always(function() { button.prop('disabled', false); });
    });

    $(document).on('click', '.delete-history', function() {
        let id = $(this).data('id');
        Swal.fire({ title: 'Hapus riwayat?', text: 'Data riwayat ini akan dihapus.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, hapus' }).then(function(result) {
            if (!result.isConfirmed) return;
            $.post(base_url + 'pengingat/riwayat_hapus', { id: id }, function(res) {
                Swal.fire(res.status ? 'Berhasil' : 'Gagal', res.message, res.status ? 'success' : 'error').then(function() { if (res.status) window.location.reload(); });
            }, 'json');
        });
    });

    $('.hapus-pengingat').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Yakin hapus?',
            text: 'Pengingat akan dinonaktifkan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'pengingat/hapus',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        refreshCurrentMainContent(window.location.href, { pushState: false });
                    }
                });
            }
        });
    });
});
</script>
