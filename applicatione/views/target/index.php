<div class="row">
    <!-- Tombol Tambah -->
    <div class="col-12 mb-3">
        <button class="btn-add-full" id="tambahTargetBtn">
            <i class="fas fa-plus me-2"></i>Tambah Target Tabungan
        </button>
    </div>

    <!-- Daftar Target -->
    <div class="col-12">
        <div id="targetList">
            <?php if ($target): ?>
                <?php foreach ($target as $t): ?>
                    <?php 
                    $persen = ($t->terkumpul / $t->target_nominal) * 100;
                    $status_class = $t->status == 'tercapai' ? 'success' : ($t->status == 'dibatalkan' ? 'danger' : 'primary');
                    ?>
                    <div class="target-card" data-id="<?= $t->id ?>">
                        <div class="target-header">
                            <div>
                                <i class="fas fa-piggy-bank me-2"></i>
                                <strong><?= htmlspecialchars($t->nama_target) ?></strong>
                                <span class="badge bg-<?= $status_class ?> ms-2"><?= $t->status ?></span>
                            </div>
                            <div class="target-actions">
                                <i class="fas fa-trash text-danger delete-target" data-id="<?= $t->id ?>" style="cursor:pointer"></i>
                            </div>
                        </div>
                        <div class="target-body">
                            <div class="target-amount">
                                <span>Target: Rp <?= number_format($t->target_nominal, 0, ',', '.') ?></span>
                                <span>Terkumpul: Rp <?= number_format($t->terkumpul, 0, ',', '.') ?></span>
                            </div>
                            <div class="progress mt-2 mb-2" style="height: 10px;">
                                <div class="progress-bar bg-<?= $status_class ?>" style="width: <?= $persen ?>%"></div>
                            </div>
                            <?php if ($t->deadline): ?>
                                <div class="target-deadline">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Deadline: <?= date('d M Y', strtotime($t->deadline)) ?>
                                    <?php 
                                    $sisa_hari = ceil((strtotime($t->deadline) - time()) / 86400);
                                    if ($sisa_hari > 0 && $sisa_hari <= 30 && $t->status == 'aktif'):
                                    ?>
                                        <span class="badge bg-warning ms-2">Sisa <?= $sisa_hari ?> hari</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($t->status == 'aktif'): ?>
                        <div class="target-footer">
                            <button class="btn-tambah-dana" data-id="<?= $t->id ?>">
                                <i class="fas fa-plus-circle me-1"></i>Tambah Dana
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-piggy-bank fa-3x text-muted mb-3"></i>
                    <p>Belum ada target tabungan</p>
                    <p class="text-muted small">Buat target tabungan untuk memotivasi menabung</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Target -->
<div class="modal fade" id="modalTarget" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Target Tabungan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTarget">
                    <div class="mb-3">
                        <input type="text" class="form-control-modern" id="nama_target" name="nama_target" placeholder="Nama target (contoh: Liburan Bali)" required>
                    </div>
                    <div class="mb-3">
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="target_nominal" name="target_nominal" placeholder="Target nominal (Rp)" inputmode="numeric" pattern="[0-9.]*" required>
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="date" class="form-control-modern" id="deadline" name="deadline">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="simpanTarget">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Dana -->
<div class="modal fade" id="modalTambahDana" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Dana Tabungan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahDana">
                    <input type="hidden" id="target_id" name="id">
                    <div class="mb-3">
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="nominal_dana" name="nominal" placeholder="Nominal tambahan (Rp)" inputmode="numeric" pattern="[0-9.]*" required>
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="prosesTambahDana">Tambah</button>
            </div>
        </div>
    </div>
</div>

<style>
.target-card {
    background: white;
    border-radius: 20px;
    padding: 16px;
    margin-bottom: 12px;
    border: 1px solid #eef2f6;
    transition: all 0.2s;
}

.target-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.target-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.target-amount {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.target-deadline {
    font-size: 0.75rem;
    color: #7f8c8d;
    margin-top: 8px;
}

.target-footer {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #eef2f6;
}

.btn-tambah-dana {
    width: 100%;
    padding: 8px;
    background: #e8f0fe;
    border: none;
    border-radius: 40px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #2d3e50;
    transition: all 0.2s;
}

.btn-tambah-dana:hover {
    background: #d0e0f8;
}
</style>

<script>
$(document).ready(function() {
    $('#tambahTargetBtn').click(function() {
        $('#formTarget')[0].reset();
        $('#modalTarget').modal('show');
    });
    
    $('#simpanTarget').click(function() {
        let nominal = $('#target_nominal').val().replace(/\./g, '');
        let data = {
            nama_target: $('#nama_target').val(),
            target_nominal: nominal,
            deadline: $('#deadline').val()
        };
        
        if (!data.nama_target || !data.target_nominal) {
            Swal.fire('Oops', 'Nama target dan nominal wajib diisi', 'warning');
            return;
        }
        
        $.ajax({
            url: base_url + 'target/simpan',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#modalTarget').modal('hide');
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                }
            }
        });
    });
    
    $('.delete-target').click(function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Yakin hapus?',
            text: 'Target tabungan akan dihapus permanen',
            icon: 'warning',
            timer:500,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'target/hapus',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Terhapus!', res.message, 'success');
                            refreshCurrentMainContent(window.location.href, { pushState: false });
                        }
                    }
                });
            }
        });
    });
    
    $('.btn-tambah-dana').click(function() {
        let id = $(this).data('id');
        $('#target_id').val(id);
        $('#formTambahDana')[0].reset();
        $('#modalTambahDana').modal('show');
    });
    
    $('#prosesTambahDana').click(function() {
        let nominal = $('#nominal_dana').val().replace(/\./g, '');
        let data = {
            id: $('#target_id').val(),
            nominal: nominal
        };
        
        if (!data.nominal) {
            Swal.fire('Oops', 'Nominal wajib diisi', 'warning');
            return;
        }
        
        $.ajax({
            url: base_url + 'target/tambah_dana',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#modalTambahDana').modal('hide');
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                }
            }
        });
    });
});
</script>
