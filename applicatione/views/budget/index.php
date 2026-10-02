<div class="row g-4">
    <div class="col-12">
        <div class="card-form">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                <div>
                    <h6 class="fw-bold mb-1"><i class="fas fa-chart-line me-2"></i>Atur Anggaran Bulanan</h6>
                    <small class="text-muted">Pantau anggaran, realisasi, dan sisa per kategori.</small>
                </div>
                <div class="budget-filter">
                    <select class="form-select-modern control-sm" id="filterBulan">
                        <option value="1" <?= $bulan == 1 ? 'selected' : '' ?>>Januari</option>
                        <option value="2" <?= $bulan == 2 ? 'selected' : '' ?>>Februari</option>
                        <option value="3" <?= $bulan == 3 ? 'selected' : '' ?>>Maret</option>
                        <option value="4" <?= $bulan == 4 ? 'selected' : '' ?>>April</option>
                        <option value="5" <?= $bulan == 5 ? 'selected' : '' ?>>Mei</option>
                        <option value="6" <?= $bulan == 6 ? 'selected' : '' ?>>Juni</option>
                        <option value="7" <?= $bulan == 7 ? 'selected' : '' ?>>Juli</option>
                        <option value="8" <?= $bulan == 8 ? 'selected' : '' ?>>Agustus</option>
                        <option value="9" <?= $bulan == 9 ? 'selected' : '' ?>>September</option>
                        <option value="10" <?= $bulan == 10 ? 'selected' : '' ?>>Oktober</option>
                        <option value="11" <?= $bulan == 11 ? 'selected' : '' ?>>November</option>
                        <option value="12" <?= $bulan == 12 ? 'selected' : '' ?>>Desember</option>
                    </select>
                    <select class="form-select-modern control-sm" id="filterTahun">
                        <?php for ($y = date('Y')-2; $y <= date('Y')+2; $y++): ?>
                            <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button class="btn-period" id="applyFilterBtn">Terapkan</button>
                </div>
            </div>
            
            <form id="formBudget">
                <input type="hidden" name="bulan" id="bulan" value="<?= $bulan ?>">
                <input type="hidden" name="tahun" id="tahun" value="<?= $tahun ?>">
                <?php
                $total_anggaran = 0;
                $total_realisasi = 0;
                foreach ($kategori as $k) {
                    $anggaran_tmp = 0;
                    foreach ($budget as $b) {
                        if ($b->category_id == $k->id) {
                            $anggaran_tmp = $b->nominal_anggaran;
                            break;
                        }
                    }
                    $total_anggaran += $anggaran_tmp;
                    $total_realisasi += $realisasi[$k->id] ?? 0;
                }
                $total_realisasi_teranggarkan = 0;
                foreach ($kategori as $k) {
                    $anggaran_tmp = 0;
                    foreach ($budget as $b) {
                        if ($b->category_id == $k->id) {
                            $anggaran_tmp = $b->nominal_anggaran;
                            break;
                        }
                    }
                    if ($anggaran_tmp > 0) {
                        $total_realisasi_teranggarkan += $realisasi[$k->id] ?? 0;
                    }
                }
                $total_sisa = $total_anggaran - $total_realisasi_teranggarkan;
                ?>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-4">
                        <div class="stat-card-soft">
                            <small class="text-muted">Total Anggaran</small>
                            <div class="fw-bold">Rp <?= number_format($total_anggaran, 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-card-soft">
                            <small class="text-muted">Realisasi</small>
                            <div class="fw-bold text-danger">Rp <?= number_format($total_realisasi, 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="stat-card-soft">
                            <small class="text-muted">Sisa</small>
                            <div class="fw-bold <?= $total_sisa >= 0 ? 'text-success' : 'text-danger' ?>">Rp <?= number_format(abs($total_sisa), 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="table-toolbar justify-content-end mb-2">
                    <input type="search" class="form-control-modern control-sm table-search" data-table-target="#budgetTable" placeholder="Cari tabel...">
                </div>
                <div class="table-responsive table-card">
                    <table class="table table-striped table-hover align-middle mb-0 budget-table table-paginated" id="budgetTable" data-page-size="10">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Anggaran</th>
                                <th>Realisasi</th>
                                <th>Sisa</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_anggaran = 0;
                            $total_realisasi = 0;
                            foreach ($kategori as $k): 
                                $anggaran = 0;
                                foreach ($budget as $b) {
                                    if ($b->category_id == $k->id) {
                                        $anggaran = $b->nominal_anggaran;
                                        break;
                                    }
                                }
                                $realisasi_kategori = $realisasi[$k->id] ?? 0;
                                $sisa = $anggaran > 0 ? ($anggaran - $realisasi_kategori) : 0;
                                $persen = $anggaran > 0 ? round(($realisasi_kategori / $anggaran) * 100) : 0;
                                $status_class = $persen >= 100 ? 'danger' : ($persen >= 80 ? 'warning' : 'success');
                                
                                $total_anggaran += $anggaran;
                                $total_realisasi += $realisasi_kategori;
                            ?>
                            <tr>
                                <td data-label="Kategori">
                                    <i class="fas fa-ellipsis-v text-secondary toggle-detail" role="button"></i>
                                    <i class="fas <?= $k->icon ?> me-2" style="color: <?= $k->warna ?>"></i>
                                    <?= htmlspecialchars($k->nama_kategori) ?>
                                </td>
                                <td data-label="Anggaran">
                                    <div class="nominal-input-group">
                                        <input type="text" class="budget-input rupiah-input nominal-shortcut-input text-end" name="budget[<?= $k->id ?>]" value="<?= $anggaran > 0 ? number_format($anggaran, 0, ',', '.') : '' ?>" placeholder="0" inputmode="numeric" pattern="[0-9.]*">
                                        <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </td>
                                <td data-label="Realisasi" class="text-danger fw-bold">Rp <?= number_format($realisasi_kategori, 0, ',', '.') ?></td>
                                <td data-label="Sisa" class="<?= $anggaran <= 0 ? 'text-muted' : ($sisa >= 0 ? 'text-success' : 'text-danger') ?> fw-bold">
                                    <?= $anggaran > 0 ? 'Rp ' . number_format(abs($sisa), 0, ',', '.') : '-' ?>
                                    <?= $anggaran > 0 && $sisa < 0 ? '<small>(Over)</small>' : '' ?>
                                </td>
                                <td data-label="Progress" style="width: 150px;">
                                    <?php if ($anggaran > 0): ?>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-<?= $status_class ?>" style="width: <?= min($persen, 100) ?>%"></div>
                                        </div>
                                        <small><?= $persen ?>%</small>
                                    <?php else: ?>
                                        <span class="badge badge-soft-muted">Belum dianggarkan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active fw-bold">
                                <td>TOTAL</td>
                                <td>Rp <?= number_format($total_anggaran, 0, ',', '.') ?></td>
                                <td class="text-danger">Rp <?= number_format($total_realisasi, 0, ',', '.') ?></td>
                                <td class="<?= $total_sisa >= 0 ? 'text-success' : 'text-danger' ?>">
                                    Rp <?= number_format(abs($total_sisa), 0, ',', '.') ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn-save" id="simpanBudgetBtn">
                        <i class="fas fa-save me-2"></i>Simpan Anggaran
                    </button>
                    <button type="button" class="btn-reset" id="resetBudgetBtn">
                        <i class="fas fa-undo me-2"></i>Reset Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.budget-table th, .budget-table td {
    vertical-align: middle;
    padding: 12px 6px;
}

.budget-input {
    width: 130px;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    text-align: right;
    font-size: 0.85rem;
}

.budget-input:focus {
    outline: none;
    border-color: #2d3e50;
    box-shadow: 0 0 0 2px rgba(45,62,80,0.1);
}

.nominal-input-group .budget-input {
    flex: 1;
    min-width: 60px;
    width: auto;
    border-radius: 0;
    border-right: none;
}

.nominal-input-group .budget-input:focus {
    box-shadow: none;
}

.nominal-input-group:focus-within .budget-input {
    border-color: #2d3e50;
}

.btn-reset {
    padding: 14px 24px;
    background: #f1f5f9;
    color: #e74c3c;
    border: 1px solid #e2e8f0;
    border-radius: 40px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-reset:hover {
    background: #fee2e2;
    border-color: #e74c3c;
}
</style>

<script>
$(document).ready(function() {
    // Format input budget
    $('.budget-input').on('keyup', function() {
        let value = $(this).val().replace(/[^,\d]/g, '');
        if (value !== '') {
            let formatted = formatRupiah(value);
            $(this).val(formatted);
        }
    });
    
    // Filter change
    $('#applyFilterBtn').click(function() {
        let bulan = $('#filterBulan').val();
        let tahun = $('#filterTahun').val();
        refreshCurrentMainContent(base_url + 'budget?bulan=' + bulan + '&tahun=' + tahun, { pushState: true });
    });
    
    // Simpan budget
    $('#formBudget').submit(function(e) {
        e.preventDefault();
        
        let budgets = {};
        $('.budget-input').each(function() {
            let name = $(this).attr('name');
            let value = $(this).val().replace(/\./g, '');
            let categoryId = name.match(/\d+/);
            if (categoryId) {
                budgets[categoryId] = value;
            }
        });
        
        $.ajax({
            url: base_url + 'budget/simpan',
            type: 'POST',
            data: {
                budget: budgets,
                bulan: $('#bulan').val(),
                tahun: $('#tahun').val()
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                }
            }
        });
    });
    
    // Reset budget
    $('#resetBudgetBtn').click(function() {
        Swal.fire({
            title: 'Reset semua anggaran?',
            text: 'Semua nilai anggaran akan direset menjadi 0',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'budget/reset',
                    type: 'POST',
                    data: {
                        bulan: $('#bulan').val(),
                        tahun: $('#tahun').val()
                    },
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
