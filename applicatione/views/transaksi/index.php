<?php
$keyword = $keyword ?? '';
$has_loan_summary = !empty($has_loan_relation) || (!empty($loan_summary) && (
    (float) ($loan_summary->total_loan_out ?? 0) > 0 ||
    (float) ($loan_summary->total_loan_in ?? 0) > 0 ||
    (float) ($loan_summary->net_loan ?? 0) !== 0.0
));
$base_params = ['start' => $tanggal_mulai, 'end' => $tanggal_akhir, 'q' => $keyword, 'tipe' => $tipe ?? ''];
$prev_day = date('Y-m-d', strtotime($tanggal_mulai . ' -1 day'));
$next_day = date('Y-m-d', strtotime($tanggal_akhir . ' +1 day'));
$wallet_seed = array_map(function ($d) {
    return [
        'id' => (int) $d->id,
        'nama_wallet' => $d->nama_wallet,
        'saldo_awal' => (float) ($d->saldo_awal ?? 0),
        'warna' => $d->warna ?? '',
        'icon' => $d->icon ?? 'fa-wallet',
        'is_active' => (int) ($d->is_active ?? 1)
    ];
}, $dompet ?? []);
// echo "<alert>alert('$has_loan_relation')</alert>";
if (!empty($has_loan_relation)){
    $colPart = "col-6";
}else{    
    $colPart = "col-12";
}

?>
<script>
window.__transaksiWalletSeed = <?= json_encode($wallet_seed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

</script>
<div class="row">
    <div class="col-12">
        <div class="card-form transaction-form-card" id="transactionFormCard">
            <h6 class="fw-bold mb-3"><i class="fas fa-plus-circle me-2"></i>Tambah Transaksi</h6>
            <form id="formTransaksi">
                <input type="hidden" id="transaksi_tipe" name="tipe" value="pengeluaran">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="tipe-selector">
                            <button type="button" class="tipe-btn active" data-tipe="pengeluaran">
                                <i class="fas fa-arrow-down"></i> Pengeluaran
                            </button>
                            <button type="button" class="tipe-btn" data-tipe="pemasukan">
                                <i class="fas fa-arrow-up"></i> Pemasukan
                            </button>
                            <button type="button" class="tipe-btn" data-tipe="transfer">
                                <i class="fas fa-right-left"></i> Transfer
                            </button>
                        </div>
                    </div>
                    <div class="col-12 position-relative" id="deskripsiFieldWrap">
                        <input type="text" class="form-control-modern" id="deskripsi" name="deskripsi" placeholder="Nama transaksi" autocomplete="off" autofocus required>
                        <div class="autocomplete-panel d-none" id="deskripsiSuggest"></div>
                    </div>
                    <div class="col-12 d-none" id="transferHintWrap">
                        <div class="small text-muted">Pilih dompet asal lalu dompet tujuan. Dompet tujuan otomatis mengecualikan dompet asal.</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="nominal" name="nominal" placeholder="Nominal (Rp)" inputmode="numeric" pattern="[0-9.]*" required>
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="date" class="form-control-modern" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="<?=  $colPart ?> col-md-6" id="kategoriFieldWrap">
                        <select class="form-select-modern" id="kategori_id" name="kategori_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori_pemasukan as $k): ?>
                                <option value="<?= $k->id ?>" data-tipe="pemasukan"><?= htmlspecialchars($k->nama_kategori) ?> - in</option>
                            <?php endforeach; ?>
                            <?php foreach ($kategori_pengeluaran as $k): ?>
                                <option value="<?= $k->id ?>" data-tipe="pengeluaran"><?= htmlspecialchars($k->nama_kategori) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="<?=  $colPart ?> col-md-6" id="walletAsalFieldWrap">
                        <select class="form-select-modern" id="wallet_id" name="wallet_id" required>
                            <option value="">Pilih Dompet Asal</option>
                            <?php foreach ($dompet as $d): ?>
                                <option value="<?= $d->id ?>"><?= htmlspecialchars($d->nama_wallet) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="small text-muted mt-1 d-none" id="walletAutoSuggestHint"></div>
                    </div>
                    <div class="col-12 col-md-6 d-none" id="walletTujuanFieldWrap">
                        <select class="form-select-modern" id="wallet_tujuan_id" name="wallet_tujuan_id" disabled>
                            <option value="">Pilih Dompet Tujuan</option>
                            <?php foreach ($dompet as $d): ?>
                                <option value="<?= $d->id ?>"><?= htmlspecialchars($d->nama_wallet) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 d-none" id="feeFieldWrap">
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="fee" name="fee" placeholder="Biaya Admin (opsional)" inputmode="numeric" pattern="[0-9.]*">
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 d-none" id="namaTransaksiFieldWrap">
                        <input type="text" class="form-control-modern" id="nama_transaksi" name="nama_transaksi" placeholder="nama transaksi opsional">
                    </div>
                    <div class="col-12 col-md-6 d-none" id="nominalShortcutHintWrap">
                        <div class="small text-muted pt-2">Centang aktif: setelah jeda 500ms, angka otomatis ditambah 000.</div>
                    </div>
                    <div class="col-12 d-none">
                        <textarea class="form-control-modern" id="catatan" name="catatan" rows="2" placeholder="Catatan (opsional)"></textarea>
                    </div>
                    <!-- Setelah field catatan atau sebelum tombol simpan, tambahkan: -->
                    <?php if (!empty($has_loan_relation)): ?>
                    <div class="col-12 d-none" id="loanToggleWrap">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_loan" name="is_loan" value="1">
                            <label class="form-check-label" for="is_loan">
                                <i class="fas fa-hand-holding-usd text-warning me-1"></i>
                                Tandai sebagai Kasbon
                            </label>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="partnerSelectWrap">
                        <input type="hidden" id="partner_id" name="partner_id" value="">
                        <div class="loan-partner-grid" id="loanPartnerGrid">
                            <?php foreach ($loan_relation_partners as $p): ?>
                            <label class="loan-partner-option" data-partner-id="<?= $p->id ?>">
                                <input type="checkbox" class="loan-partner-check" value="<?= $p->id ?>" data-partner-name="<?= htmlspecialchars($p->partner_fullname) ?>" data-partner-role="<?= htmlspecialchars($p->relation_label) ?>">
                                <span class="loan-partner-card">
                                    <span class="loan-partner-card-title"><?= htmlspecialchars($p->partner_fullname) ?></span>
                                    <span class="loan-partner-card-subtitle"><?= htmlspecialchars($p->relation_label) ?></span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted d-none">akan di catat sebagai kasbon</small>
                    </div>
                    <?php endif; ?>
                    <div class="col-12">
                        <button type="submit" class="btn-save" id="simpanTransaksi">
                            <i class="fas fa-save me-2"></i>Simpan Transaksi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12 mt-4">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="stat-card-soft">
                    <small class="text-muted">Pemasukan hari ini</small>
                    <div class="fw-bold text-success">Rp <?= number_format($total_pemasukan_hari_ini, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-soft">
                    <small class="text-muted">Pengeluaran hari ini</small>
                    <div class="fw-bold text-danger">Rp <?= number_format($total_pengeluaran_hari_ini, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-soft">
                    <small class="text-muted">Pemasukan bulan ini</small>
                    <div class="fw-bold text-success">Rp <?= number_format($total_pemasukan_bulan_ini, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-soft">
                    <small class="text-muted">Pengeluaran bulan ini</small>
                    <div class="fw-bold text-danger">Rp <?= number_format($total_pengeluaran_bulan_ini, 0, ',', '.') ?></div>
                </div>
            </div>
            <?php if ($has_loan_summary): ?>
            <div class="col-6 col-md-3">
                <div class="stat-card-soft">
                    <small class="text-muted">Sisa Kasbon</small>
                    <div class="fw-bold text-primary">
                        <i class="fas fa-hand-holding-usd me-1 text-primary"></i>
                        Rp <?= number_format($loan_summary->net_loan ?? 0, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-12 mt-4">
        <?php
        $all_kategori = [];
        if (!empty($kategori_pemasukan)) {
            foreach ($kategori_pemasukan as $k) { $all_kategori[] = $k; }
        }
        if (!empty($kategori_pengeluaran)) {
            foreach ($kategori_pengeluaran as $k) { $all_kategori[] = $k; }
        }
        ?>
        <div class="card-filter mt-3 py-2 px-3">
            <div class="row g-2 mb-2">
                <div class="col-6 col-md-8">
                    <input type="search" id="searchTransaksi" class="form-control-modern control-sm w-100" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari transaksi...">
                </div>
                <div class="col-6 col-md-4">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="period-nav d-flex gap-1 flex-fill" role="group">
                            <button type="button" class="btn-period" id="transPrev" style="width:34px;padding:0"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" class="btn-period flex-fill" id="transToday" style="padding:6px 10px;font-size:0.75rem">Today</button>
                            <button type="button" class="btn-period" id="transNext" style="width:34px;padding:0"><i class="fas fa-chevron-right"></i></button>
                            <button type="button" class="btn-period bg-primary text-white border-0" id="transReset" style="width:34px;padding:0"><i class="fas fa-undo"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-grid d-grid gap-2">
                <input type="date" class="form-control-modern control-sm" id="tgl_mulai_trans" value="<?= htmlspecialchars($tanggal_mulai) ?>" style="min-width:0">
                <input type="date" class="form-control-modern control-sm" id="tgl_akhir_trans" value="<?= htmlspecialchars($tanggal_akhir) ?>" style="min-width:0">
                <select class="form-select-modern control-sm" id="filter_tipe_trans" style="min-width:0">
                    <option value="">Semua Tipe</option>
                    <option value="pemasukan" <?= ($tipe ?? '') === 'pemasukan' ? 'selected' : '' ?>>Pemasukan (in)</option>
                    <option value="pengeluaran" <?= ($tipe ?? '') === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran (out)</option>
                    <option value="transfer" <?= ($tipe ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                </select>
                <select class="form-select-modern control-sm" id="filter_kategori_trans" style="min-width:0">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($all_kategori as $k): ?>
                        <option value="<?= $k->id ?>"><?= htmlspecialchars($k->nama_kategori) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-select-modern control-sm" id="filter_dompet_trans" style="min-width:0">
                    <option value="">Semua Dompet</option>
                    <?php foreach ($dompet as $d): ?>
                        <option value="<?= $d->id ?>"><?= htmlspecialchars($d->nama_wallet) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-save btn-filter-submit" id="filterTransaksiBtn" style="padding:8px 16px">Submit</button>
            </div>
        </div>
        <div id="transaksiTableWrap">
        <?php if ($transaksi): ?>
        <div class="table-responsive table-card">
            <table class="table table-striped table-hover align-middle mb-0 transaksi-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Dompet</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksi as $t): ?>
                    <tr class="<?= (int)($t->is_loan ?? 0) === 2 ? 'loan-cancelled-warning' : '' ?>">
                        <td data-label="Tanggal"><?= date('d-m-y', strtotime($t->tanggal)) ?> # <?= htmlspecialchars($t->nama_wallet) ?></td>
                        <td data-label="Nama"><?= htmlspecialchars($t->deskripsi) ?></td>
                        <td data-label="Kategori"><?= htmlspecialchars($t->tipe === 'transfer' ? 'Transfer' : ($t->nama_kategori ?? '-')) ?></td>
                        <td data-label="Dompet"><?= htmlspecialchars($t->tipe === 'transfer' ? ($t->nama_wallet . ' -> ' . ($t->nama_wallet_tujuan ?? '-')) : $t->nama_wallet) ?></td>
                        <td data-label="Nominal" class="text-end <?= $t->tipe == 'pemasukan' ? 'text-success' : ($t->tipe == 'transfer' ? 'text-primary' : 'text-danger') ?> fw-semibold">
                            <?php if ($t->tipe === 'transfer'): ?>
                                <span class="d-block">Rp <?= number_format($t->nominal, 0, ',', '.') ?></span>
                                <?php if ((float)$t->fee > 0): ?><small class="text-muted">Fee Rp <?= number_format($t->fee, 0, ',', '.') ?></small><?php endif; ?>
                            <?php else: ?>
                                <?= $t->tipe == 'pemasukan' ? '+' : '-' ?> Rp <?= number_format($t->nominal, 0, ',', '.') ?>
                            <?php endif; ?>
                        </td>
                        <td data-label="Aksi" class="text-end action-icons">
                            <?php if ((int) $t->is_loan === 1): ?>
                                <i class="fas fa-hand-holding-usd loan-flag-icon me-2" title="Kasbon"></i>
                            <?php elseif ((int) $t->is_loan === 2): ?>
                                <i class="fas fa-hand-holding-usd text-danger me-2 loan-cancel-info" data-id="<?= $t->id ?>" title="Kasbon dibatalkan" style="cursor:pointer"></i>
                            <?php endif; ?>
                            <i class="fas fa-edit text-primary edit-transaksi" data-id="<?= $t->id ?>" role="button"></i>
                            <i class="fas fa-trash text-danger ms-2 delete-transaksi" data-id="<?= $t->id ?>" role="button"></i>
                            <i class="fas fa-ellipsis-v text-secondary ms-2 toggle-detail" role="button"></i>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <nav class="mt-3" aria-label="Pagination transaksi">
            <ul class="pagination pagination-sm justify-content-end mb-0" id="transaksiPagination">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <button type="button" class="page-link" data-page="<?= max(1, $page - 1) ?>">Prev</button>
                </li>
                <?php
                $last_printed = 0;
                for ($i = 1; $i <= $total_pages; $i++):
                    if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2):
                        if ($last_printed && $i > $last_printed + 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <button type="button" class="page-link" data-page="<?= $i ?>"><?= $i ?></button>
                        </li>
                        <?php $last_printed = $i;
                    endif;
                endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <button type="button" class="page-link" data-page="<?= min($total_pages, $page + 1) ?>">Next</button>
                </li>
            </ul>
        </nav>
        <?php else: ?>
            <div class="empty-state" id="transaksiEmptyState">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <p>Belum ada transaksi pada periode ini</p>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditTransaksi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content edit-transaksi-modal">
            <div class="modal-header">
                <h5 class="modal-title">Edit Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditTransaksi">
                    <input type="hidden" id="edit_id" name="id">
                    <input type="hidden" id="edit_tipe" name="tipe" value="pengeluaran">
                    <div class="mb-3">
                        <div class="tipe-selector">
                            <button type="button" class="edit-tipe-btn active" data-tipe="pengeluaran"><i class="fas fa-arrow-down"></i> Pengeluaran</button>
                            <button type="button" class="edit-tipe-btn" data-tipe="pemasukan"><i class="fas fa-arrow-up"></i> Pemasukan</button>
                            <button type="button" class="edit-tipe-btn" data-tipe="transfer"><i class="fas fa-right-left"></i> Transfer</button>
                        </div>
                    </div>
                    <div class="mb-3"><input type="text" class="form-control-modern" id="edit_deskripsi" name="deskripsi" required></div>
                    <div class="mb-3">
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="edit_nominal" name="nominal" inputmode="numeric" pattern="[0-9.]*" required>
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3"><input type="date" class="form-control-modern" id="edit_tanggal" name="tanggal"></div>
                    <div class="mb-3" id="editKategoriWrap"><select class="form-select-modern" id="edit_kategori_id" name="kategori_id" required></select></div>
                    <div class="mb-3"><select class="form-select-modern" id="edit_wallet_id" name="wallet_id" required></select></div>
                    <div class="mb-3 d-none" id="editWalletTujuanWrap"><select class="form-select-modern" id="edit_wallet_tujuan_id" name="wallet_tujuan_id" disabled></select></div>
                    <div class="mb-3 d-none" id="editFeeWrap">
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="edit_fee" name="fee" inputmode="numeric" pattern="[0-9.]*" placeholder="Biaya Admin (opsional)">
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true" title="Input ribuan aktif">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="editNamaTransaksiWrap">
                        <input type="text" class="form-control-modern" id="edit_nama_transaksi" name="nama_transaksi" placeholder="nama transaksi opsional">
                    </div>
                    <div class="mb-3 d-none"><textarea class="form-control-modern" id="edit_catatan" name="catatan" rows="2"></textarea></div>
                    <?php if (!empty($has_loan_relation)): ?>
                    <div class="mb-3 d-none" id="editLoanToggleWrap">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_loan" name="is_loan" value="1">
                            <label class="form-check-label" for="edit_is_loan">
                                <i class="fas fa-hand-holding-usd text-warning me-1"></i>
                                Tandai sebagai Kasbon
                            </label>
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="editPartnerSelectWrap">
                        <input type="hidden" id="edit_partner_id" name="partner_id" value="">
                        <div class="loan-partner-grid" id="editLoanPartnerGrid">
                            <?php foreach ($loan_relation_partners as $p): ?>
                            <label class="loan-partner-option" data-partner-id="<?= $p->id ?>">
                                <input type="checkbox" class="loan-partner-check" value="<?= $p->id ?>" data-partner-name="<?= htmlspecialchars($p->partner_fullname) ?>" data-partner-role="<?= htmlspecialchars($p->relation_label) ?>">
                                <span class="loan-partner-card">
                                    <span class="loan-partner-card-title"><?= htmlspecialchars($p->partner_fullname) ?></span>
                                    <span class="loan-partner-card-subtitle"><?= htmlspecialchars($p->relation_label) ?></span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-save btn-compact" id="updateTransaksi">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
