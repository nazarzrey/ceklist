<?php
$keyword = $keyword ?? '';
$tab = $tab ?? 'hutang';
$base_params = ['q' => $keyword, 'tab' => $tab];
$hutang_tab_url = site_url('hutang') . '?' . http_build_query(['q' => $keyword, 'tab' => 'hutang']);
$piutang_tab_url = site_url('hutang') . '?' . http_build_query(['q' => $keyword, 'tab' => 'piutang']);
?>
<script>
window.hutangPaymentType = <?= json_encode($tab === 'piutang' ? 'piutang' : 'hutang') ?>;
window.kasbonParties = <?= json_encode($pihak_kasbon ?? [], JSON_UNESCAPED_UNICODE) ?>;
</script>
<div class="row hutang-page">
    <div class="col-12 mb-4">
        <div class="row g-3">
            <div class="col-12 col-sm-4">
                <div class="summary-card hutang">
                    <i class="fas fa-hand-holding-usd"></i>
                    <div class="summary-label">Total Hutang</div>
                    <div class="summary-value" id="totalHutangValue">Rp <?= number_format($total_hutang, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="summary-card kasbon">
                    <i class="fas fa-store"></i>
                    <div class="summary-label">Total Kasbon Hutang</div>
                    <div class="summary-value" id="totalKasbonValue">Rp <?= number_format($total_kasbon ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="summary-card piutang">
                    <i class="fas fa-hand-holding-heart"></i>
                    <div class="summary-label">Total Piutang</div>
                    <div class="summary-value" id="totalPiutangValue">Rp <?= number_format($total_piutang, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-3">
        <button class="btn-add-full" id="tambahHutangBtn">
            <i class="fas fa-plus me-2"></i>Tambah Hutang/Piutang
        </button>
    </div>

    <div class="col-12">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <div class="d-flex flex-column flex-md-row gap-2 w-100 debt-search-bar">
                <div class="debt-search-input-group">
                    <input type="search" id="searchHutang" class="form-control-modern control-sm" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari Data Hutang/Piutang" data-page-url="<?= site_url('hutang') ?>" data-param="q">
                    <button type="button" class="btn-save btn-compact text-nowrap" id="cariHutangBtn"><i class="fas fa-search me-2"></i>Cari</button>
                </div>
                <button class="btn-save btn-compact text-nowrap <?= empty($hutang_belum_lunas) ? 'd-none' : '' ?>" id="pelunasanTerpilihBtn" type="button"><i class="fas fa-check-double me-2"></i>Pelunasan Terpilih</button>
            </div>
        </div>
        <ul class="nav nav-tabs debt-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link js-ajax-page-link <?= $tab == 'hutang' ? 'active' : '' ?>" aria-current="<?= $tab == 'hutang' ? 'page' : 'false' ?>" href="<?= $hutang_tab_url ?>">
                    Hutang <span class="badge badge-soft-danger ms-1" id="countHutang"><?= (int) $count_hutang ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link js-ajax-page-link <?= $tab == 'piutang' ? 'active' : '' ?>" aria-current="<?= $tab == 'piutang' ? 'page' : 'false' ?>" href="<?= $piutang_tab_url ?>">
                    Piutang <span class="badge badge-soft-success ms-1" id="countPiutang"><?= (int) $count_piutang ?></span>
                </a>
            </li>
        </ul>
        <?php if ($hutang): ?>
        <div class="table-responsive table-card tab-<?= htmlspecialchars($tab) ?>">
            <table class="table table-hover align-middle mb-0 debt-table hutang-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pihak</th>
                        <th>Catatan</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Sisa</th>
                        <th>Tgl. Pelunasan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="debtTableBody">
                    <?php foreach ($hutang as $h): ?>
                    <?php 
                    $belum_lunas = $h->status != 'lunas'; 
                    $ada_cicilan = $h->sisa < $h->jumlah_total;
                    ?>
                    <tr class="<?= $belum_lunas ? 'debt-row-unpaid' : '' ?>" data-id="<?= (int) $h->id ?>">
                        <td data-label="Tanggal"><strong><?= $h->created_at ? date('d-m-Y', strtotime($h->created_at)) : '-' ?></strong></td>
                        <td data-label="Pihak"><strong><?= htmlspecialchars($h->nama_pihak) ?></strong></td>
                        <td data-label="Catatan"><?= htmlspecialchars($h->keterangan ?: '-') ?></td>
                        <td data-label="Total" class="text-end">Rp <?= number_format($h->jumlah_total, 0, ',', '.') ?></td>
                        <td data-label="Sisa" class="text-end fw-semibold <?= $ada_cicilan ? 'text-primary' : 'text-dark' ?>">Rp <?= number_format($h->sisa, 0, ',', '.') ?></td>
                        <td data-label="Pelunasan"><?= !empty($h->tanggal_pelunasan) ? date('d-m-Y', strtotime($h->tanggal_pelunasan)) : '-' ?></td>
                        <td data-label="Status">
                            <span class="badge <?= $belum_lunas ? 'badge-soft-danger' : 'badge-soft-success' ?>">
                                <?= $belum_lunas ? 'Belum' : 'Lunas' ?>
                            </span>
                            <?php if (!$belum_lunas && !empty($h->tanggal_pelunasan)): ?><small class="status-lunas-date">• <?= date('d-m-Y', strtotime($h->tanggal_pelunasan)) ?></small><?php endif; ?>
                            <?php if ((int) ($h->is_kasbon ?? 0) === 1): ?><small class="d-block text-warning mt-1" style="font-size:10px">Kasbon</small><?php endif; ?>
                            <?php if ($ada_cicilan): ?><small class="d-block text-primary mt-1" style="font-size:10px">Cicilan</small><?php endif; ?>
                        </td>
                        <td data-label="Aksi" class="text-end action-icons">
                            <?php if ($belum_lunas): ?><i class="fas fa-hand-holding-usd text-success btn-cicil-icon" data-id="<?= $h->id ?>" role="button"></i><?php endif; ?>
                            <i class="fas fa-clock text-secondary ms-2 btn-riwayat" data-id="<?= $h->id ?>" role="button"></i>
                            <?php if ($belum_lunas): ?><i class="fas fa-edit text-primary ms-2 edit-hutang" data-id="<?= $h->id ?>" role="button"></i><i class="fas fa-trash text-danger ms-2 delete-hutang" data-id="<?= $h->id ?>" role="button"></i><?php endif; ?>
                            <i class="fas fa-ellipsis-v text-secondary ms-2 toggle-detail" role="button"></i>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <nav class="mt-3" aria-label="Pagination hutang piutang">
            <ul class="pagination pagination-sm justify-content-end mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link js-ajax-page-link" href="<?= site_url('hutang') . '?' . http_build_query($base_params + ['page' => max(1, $page - 1)]) ?>">Prev</a>
                </li>
                <?php
                $last_printed = 0;
                for ($i = 1; $i <= $total_pages; $i++):
                    if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2):
                        if ($last_printed && $i > $last_printed + 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link js-ajax-page-link" href="<?= site_url('hutang') . '?' . http_build_query($base_params + ['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                        <?php $last_printed = $i;
                    endif;
                endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link js-ajax-page-link" href="<?= site_url('hutang') . '?' . http_build_query($base_params + ['page' => min($total_pages, $page + 1)]) ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                <p>Belum ada data hutang/piutang pada periode ini</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalHutang" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHutangTitle">Tambah Hutang/Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formHutang">
                    <input type="hidden" id="hutang_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Tipe</label>
                        <div class="tipe-selector-debt">
                            <button type="button" class="debt-tipe-btn active" data-tipe="hutang"><i class="fas fa-hand-holding-usd"></i> Hutang</button>
                            <button type="button" class="debt-tipe-btn" data-tipe="piutang"><i class="fas fa-hand-holding-heart"></i> Piutang</button>
                        </div>
                    </div>
                    <div class="mb-3" id="hutangKasbonWrap">
                        <label class="form-check debt-kasbon-option">
                            <input class="form-check-input" type="checkbox" id="is_kasbon" value="1">
                            <span><strong>Kasbon</strong><small class="d-block text-muted">Tidak memengaruhi dompet sekarang, dibayar belakangan.</small></span>
                        </label>
                    </div>
                    <div class="mb-3" id="namaPihakTextWrap"><input type="text" class="form-control-modern" id="nama_pihak" name="nama_pihak" placeholder="Nama pihak" required></div>
                    <div class="mb-3 d-none" id="namaPihakSelectWrap">
                        <label class="form-label">Pihak Kasbon</label>
                        <select class="form-select-modern" id="nama_pihak_select"></select>
                        <input type="text" class="form-control-modern mt-2 d-none" id="nama_pihak_custom" placeholder="Nama pihak baru">
                    </div>
                    <div class="mb-3">
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="jumlah_total" name="jumlah_total" placeholder="Jumlah total (Rp)" inputmode="numeric" pattern="[0-9.]*" required>
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3" id="hutangKasWrap">
                        <label class="form-label">Dompet penerima hutang</label>
                        <select class="form-select-modern" id="hutang_wallet_id">
                            <option value="">Pilih dompet</option>
                            <?php foreach ($dompet as $w): ?><option value="<?= (int) $w->id ?>"><?= htmlspecialchars($w->nama_wallet) ?> — Rp <?= number_format($w->saldo_awal, 0, ',', '.') ?></option><?php endforeach; ?>
                        </select>
                        <small class="text-muted">Saldo dompet bertambah saat hutang dicatat.</small>
                    </div>
                    <div class="mb-3 d-none" id="piutangKasWrap">
                        <label class="form-label">Dompet pemberi piutang</label>
                        <select class="form-select-modern" id="piutang_wallet_id" name="wallet_id">
                            <option value="">Pilih dompet</option>
                            <?php foreach ($dompet as $w): ?><option value="<?= (int) $w->id ?>"><?= htmlspecialchars($w->nama_wallet) ?> — Rp <?= number_format($w->saldo_awal, 0, ',', '.') ?></option><?php endforeach; ?>
                        </select>
                        <small class="text-muted">Saldo dompet berkurang saat piutang dibuat.</small>
                    </div>
                    <div class="mb-3"><textarea class="form-control-modern" id="keterangan" name="keterangan" rows="2" placeholder="Keterangan"></textarea></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-save btn-compact" id="simpanHutang">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCicilan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bayar Cicilan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCicilan">
                    <input type="hidden" id="cicilan_debt_id" name="debt_id">
                    <div class="mb-3">
                        <label class="form-label">Dompet</label>
                        <select class="form-select-modern" id="cicilan_wallet_id" name="wallet_id" required>
                            <option value="">Pilih dompet</option>
                            <?php foreach ($dompet as $w): ?><option value="<?= (int) $w->id ?>"><?= htmlspecialchars($w->nama_wallet) ?> — Rp <?= number_format($w->saldo_awal, 0, ',', '.') ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nominal Bayar</label>
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="nominal_bayar" name="nominal" inputmode="numeric" pattern="[0-9.]*" required>
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control-modern" id="tanggal_bayar" name="tanggal" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3"><textarea class="form-control-modern" id="catatan_cicilan" name="catatan" rows="2" placeholder="Catatan"></textarea></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-save btn-compact" id="prosesBayar">Bayar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPelunasanTerpilih" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="pelunasanModalTitle">Pelunasan Hutang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <p class="text-muted small mb-0" id="pelunasanModalHint">Centang semua atau beberapa data. Nominal dapat diubah untuk pembayaran sebagian.</p>
                    <div class="debt-selection-summary" aria-live="polite">
                        <span id="pelunasanSelectedCount">0 data dipilih</span>
                        <strong id="pelunasanSelectedTotal">Rp 0</strong>
                    </div>
                </div>
                <div class="table-responsive pelunasan-table-wrap"><table class="table table-sm align-middle pelunasan-table"><thead><tr><th><input type="checkbox" id="pelunasanCheckAll"></th><th>Pihak</th><th>Sisa</th><th>Bayar</th></tr></thead><tbody id="pelunasanTableBody">
                <?php foreach ($hutang_belum_lunas as $h): ?>
                    <tr><td><input type="checkbox" class="pelunasan-check" data-id="<?= (int) $h->id ?>"></td><td><?= htmlspecialchars($h->nama_pihak) ?><small class="d-block text-muted"><?= htmlspecialchars($h->keterangan ?: '-') ?></small></td><td>Rp <?= number_format($h->sisa, 0, ',', '.') ?></td><td><input type="text" class="form-control-modern form-control-sm rupiah-input pelunasan-nominal" data-id="<?= (int) $h->id ?>" value="<?= number_format($h->sisa, 0, ',', '.') ?>" disabled></td></tr>
                <?php endforeach; ?></tbody></table></div>
                <div class="row g-3 pelunasan-fields"><div class="col-12 col-md-6"><label class="form-label">Dompet</label><select class="form-select-modern" id="pelunasan_wallet_id"><option value="">Pilih dompet</option><?php foreach ($dompet as $w): ?><option value="<?= (int) $w->id ?>"><?= htmlspecialchars($w->nama_wallet) ?> — Rp <?= number_format($w->saldo_awal, 0, ',', '.') ?></option><?php endforeach; ?></select></div><div class="col-12 col-md-6"><label class="form-label">Tanggal Bayar</label><input type="date" class="form-control-modern" id="pelunasan_tanggal" value="<?= date('Y-m-d') ?>"></div><div class="col-12"><label class="form-label">Catatan</label><input type="text" class="form-control-modern" id="pelunasan_catatan" placeholder="Catatan pelunasan (opsional)"></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="button" class="btn-save btn-compact" id="prosesPelunasan">Bayar Terpilih</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRiwayat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Riwayat Cicilan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><div id="riwayatList"></div></div>
        </div>
    </div>
</div>
