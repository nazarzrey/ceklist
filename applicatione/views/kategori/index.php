<div class="row">
    <div class="col-12 mb-3">
        <button class="btn-add-full" id="tambahKategoriBtn">
            <i class="fas fa-plus me-2"></i>Tambah Kategori
        </button>
    </div>

    <!-- Kategori Pemasukan -->
    <div class="col-12 col-md-6">
        <div class="card-form">
            <h6 class="fw-bold mb-3"><i class="fas fa-arrow-up text-success me-2"></i>Kategori Pemasukan</h6>
            <div id="kategoriPemasukanList">
                <?php foreach ($kategori_pemasukan as $k): ?>
                    <?php $is_active = !isset($k->is_active) || (int) $k->is_active === 1; ?>
                    <?php if ($k->user_id != 0): ?>
                    <div class="kategori-item <?= $is_active ? '' : 'kategori-inactive' ?>" data-id="<?= $k->id ?>">
                        <div class="kategori-icon" style="background: <?= $k->warna ?>20">
                            <i class="fas <?= $k->icon ?>" style="color: <?= $k->warna ?>"></i>
                        </div>
                        <div class="kategori-nama"><?= htmlspecialchars($k->nama_kategori) ?><?= $is_active ? '' : ' <span class="badge bg-secondary ms-1">Nonaktif</span>' ?></div>
                        <div class="kategori-actions">
                            <i class="fas fa-edit text-primary edit-kategori" data-id="<?= $k->id ?>" data-tipe="<?= $k->tipe ?>" style="cursor:pointer"></i>
                            <?php if ($is_active): ?>
                                <i class="fas fa-trash text-danger ms-2 delete-kategori" data-id="<?= $k->id ?>" style="cursor:pointer"></i>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-success activate-kategori" data-id="<?= $k->id ?>">Aktifkan</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="kategori-item default">
                        <div class="kategori-icon" style="background: <?= $k->warna ?>20">
                            <i class="fas <?= $k->icon ?>" style="color: <?= $k->warna ?>"></i>
                        </div>
                        <div class="kategori-nama"><?= htmlspecialchars($k->nama_kategori) ?></div>
                        <div class="kategori-actions">
                            <span class="badge bg-secondary">Default</span>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Kategori Pengeluaran -->
    <div class="col-12 col-md-6">
        <div class="card-form">
            <h6 class="fw-bold mb-3"><i class="fas fa-arrow-down text-danger me-2"></i>Kategori Pengeluaran</h6>
            <div id="kategoriPengeluaranList">
                <?php foreach ($kategori_pengeluaran as $k): ?>
                    <?php $is_active = !isset($k->is_active) || (int) $k->is_active === 1; ?>
                    <?php if ($k->user_id != 0): ?>
                    <div class="kategori-item <?= $is_active ? '' : 'kategori-inactive' ?>" data-id="<?= $k->id ?>">
                        <div class="kategori-icon" style="background: <?= $k->warna ?>20">
                            <i class="fas <?= $k->icon ?>" style="color: <?= $k->warna ?>"></i>
                        </div>
                        <div class="kategori-nama"><?= htmlspecialchars($k->nama_kategori) ?><?= $is_active ? '' : ' <span class="badge bg-secondary ms-1">Nonaktif</span>' ?></div>
                        <div class="kategori-actions">
                            <i class="fas fa-edit text-primary edit-kategori" data-id="<?= $k->id ?>" data-tipe="<?= $k->tipe ?>" style="cursor:pointer"></i>
                            <?php if ($is_active): ?>
                                <i class="fas fa-trash text-danger ms-2 delete-kategori" data-id="<?= $k->id ?>" style="cursor:pointer"></i>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-success activate-kategori" data-id="<?= $k->id ?>">Aktifkan</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="kategori-item default">
                        <div class="kategori-icon" style="background: <?= $k->warna ?>20">
                            <i class="fas <?= $k->icon ?>" style="color: <?= $k->warna ?>"></i>
                        </div>
                        <div class="kategori-nama"><?= htmlspecialchars($k->nama_kategori) ?></div>
                        <div class="kategori-actions">
                            <span class="badge bg-secondary">Default</span>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Kategori -->
<div class="modal fade" id="modalKategori" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formKategori">
                    <input type="hidden" name="id" id="kategori_id">
                    <div class="mb-3">
                        <label class="form-label">Tipe</label>
                        <div class="tipe-selector">
                            <button type="button" class="tipe-kategori-btn active" data-tipe="pemasukan">
                                <i class="fas fa-arrow-up"></i> Pemasukan
                            </button>
                            <button type="button" class="tipe-kategori-btn" data-tipe="pengeluaran">
                                <i class="fas fa-arrow-down"></i> Pengeluaran
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control-modern" id="nama_kategori" name="nama_kategori" placeholder="Nama Kategori" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <select class="form-select-modern" id="icon" name="icon">
                            <option value="fa-tag">🏷️ Tag</option>
                            <option value="fa-utensils">🍽️ Makanan</option>
                            <option value="fa-car">🚗 Transportasi</option>
                            <option value="fa-bolt">⚡ Tagihan</option>
                            <option value="fa-film">🎬 Hiburan</option>
                            <option value="fa-wallet">💰 Gaji</option>
                            <option value="fa-gift">🎁 Bonus</option>
                            <option value="fa-laptop-code">💻 Freelance</option>
                            <option value="fa-chart-line">📈 Investasi</option>
                            <option value="fa-heartbeat">❤️ Kesehatan</option>
                            <option value="fa-shopping-bag">🛍️ Belanja</option>
                            <option value="fa-graduation-cap">🎓 Pendidikan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <input type="color" class="form-control-modern" id="warna" name="warna" value="#6c5ce7">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="simpanKategori">Simpan</button>
            </div>
        </div>
    </div>
</div>

<style>
.kategori-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eef2f6;
}

.kategori-item.default {
    opacity: 0.7;
}

.kategori-inactive {
    opacity: 0.65;
}

.kategori-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
}

.kategori-icon i {
    font-size: 1rem;
}

.kategori-nama {
    flex: 1;
    font-weight: 500;
}

.kategori-actions {
    display: flex;
    gap: 8px;
}

.tipe-kategori-btn {
    flex: 1;
    padding: 10px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 40px;
    cursor: pointer;
    transition: all 0.2s;
}

.tipe-kategori-btn.active {
    background: #2d3e50;
    color: white;
    border-color: #2d3e50;
}

.tipe-kategori-btn[data-tipe="pemasukan"].active {
    background: #27ae60;
    border-color: #27ae60;
}

.tipe-kategori-btn[data-tipe="pengeluaran"].active {
    background: #e74c3c;
    border-color: #e74c3c;
}
</style>

<script>
$(document).ready(function() {
    let currentTipe = 'pemasukan';
    
    $('.tipe-kategori-btn').click(function() {
        $('.tipe-kategori-btn').removeClass('active');
        $(this).addClass('active');
        currentTipe = $(this).data('tipe');
    });
    
    $('#tambahKategoriBtn').click(function() {
        $('#formKategori')[0].reset();
        $('#kategori_id').val('');
        $('.tipe-kategori-btn[data-tipe="pemasukan"]').click();
        $('#modalKategori').modal('show');
    });
    
    $('#simpanKategori').click(function() {
        let id = $('#kategori_id').val();
        let url = id ? base_url + 'kategori/edit' : base_url + 'kategori/simpan';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                id: id,
                nama_kategori: $('#nama_kategori').val(),
                tipe: currentTipe,
                icon: $('#icon').val(),
                warna: $('#warna').val()
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#modalKategori').modal('hide');
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });
    
    $('.edit-kategori').click(function() {
        let id = $(this).data('id');
        let tipe = $(this).data('tipe');
        
        $.ajax({
            url: base_url + 'kategori/get_data',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#kategori_id').val(res.data.id);
                    $('#nama_kategori').val(res.data.nama_kategori);
                    $('#icon').val(res.data.icon);
                    $('#warna').val(res.data.warna);
                    
                    if (res.data.tipe == 'pemasukan') {
                        $('.tipe-kategori-btn[data-tipe="pemasukan"]').click();
                    } else {
                        $('.tipe-kategori-btn[data-tipe="pengeluaran"]').click();
                    }
                    
                    $('#modalKategori').modal('show');
                }
            }
        });
    });
    
    $('.delete-kategori').click(function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Yakin hapus?',
            text: 'Kategori yang memiliki transaksi tidak bisa dihapus',
            icon: 'warning',
            timer:500,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'kategori/hapus',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Terhapus!', res.message, 'success');
                            refreshCurrentMainContent(window.location.href, { pushState: false });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    }
                });
            }
        });
    });

    $('.activate-kategori').click(function() {
        let id = $(this).data('id');

        $.ajax({
            url: base_url + 'kategori/aktifkan',
            type: 'POST',
            data: { id: id },
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
});
</script>
