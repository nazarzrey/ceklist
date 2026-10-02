<?php
$keyword = $keyword ?? '';
$has_loan_summary = !empty($loan_summary) && (
    (float) ($loan_summary->total_loan_out ?? 0) > 0 ||
    (float) ($loan_summary->total_loan_in ?? 0) > 0 ||
    (float) ($loan_summary->net_loan ?? 0) !== 0.0
);
$loan_base_params = ['partner_id' => $selected_partner_id];
$is_incoming_payment = ($payment_direction ?? 'outgoing') === 'incoming';
$payer_label = $is_incoming_payment
    ? 'Dompet ' . htmlspecialchars($selected_partner->partner_fullname) . ' (Asal)'
    : 'Dompet Saya (Asal)';
$receiver_label = $is_incoming_payment
    ? 'Dompet Saya (Tujuan)'
    : 'Dompet ' . htmlspecialchars($selected_partner->partner_fullname) . ' (Tujuan)';
$payer_wallets = $is_incoming_payment ? ($partner_wallets ?? []) : ($my_wallets ?? []);
$receiver_wallets = $is_incoming_payment ? ($my_wallets ?? []) : ($partner_wallets ?? []);
$wallet_seed_source = array_merge($my_wallets ?? [], $partner_wallets ?? []);
$wallet_seed = [];
foreach ($wallet_seed_source as $d) {
    $wallet_seed[(int) $d->id] = [
        'id' => (int) $d->id,
        'nama_wallet' => $d->nama_wallet,
        'saldo_awal' => (float) ($d->saldo_awal ?? 0),
        'warna' => $d->warna ?? '',
        'icon' => $d->icon ?? 'fa-wallet',
        'is_active' => (int) ($d->is_active ?? 1)
    ];
}
$wallet_seed = array_values($wallet_seed);
$is_admin = $this->session->role;
?>
<script>
window.__transaksiWalletSeed = <?= json_encode($wallet_seed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<div class="row g-4">
    <!-- Pilih Partner -->
    <div class="col-12">
        <div class="card-filter">
            <div class="d-flex gap-2 flex-wrap">
                <label class="form-label mb-0 me-2 align-self-center">Relasi:</label>
                <?php foreach ($partners as $p): ?>
                <a href="<?= site_url('loan?partner_id=' . $p->partner_id) ?>" 
                   class="btn-partner-filter <?= $selected_partner_id == $p->partner_id ? 'active' : '' ?>">
                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($p->partner_fullname) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <?php if ($selected_partner): ?>
    <!-- Ringkasan Kasbon -->
    <div class="col-12">
        <div class="row g-3">
            <div class="col-4">
                <div class="stat-card-soft text-center">
                    <small class="text-muted">Total Kasbon</small>
                    <div class="fw-bold text-primary">Rp <?= number_format($summary->total_loan, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card-soft text-center">
                    <small class="text-muted">Sudah Dibayar</small>
                    <div class="fw-bold text-success">Rp <?= number_format($summary->total_paid, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card-soft text-center">
                    <small class="text-muted">Sisa Kasbon..</small>
                    <div class="fw-bold <?= $summary->remaining > 0 ? 'text-danger' : 'text-primary' ?>">
                        Rp <?= number_format($summary->remaining, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Form Bayar Kasbon -->
    <div class="col-12">
        <div class="card-form">
            <h6 class="fw-bold mb-3"><i class="fas fa-money-bill-wave me-2"></i>Bayar Kasbon</h6>
            <form id="formBayarKasbon">
                <input type="hidden" name="partner_id" value="<?= $selected_partner->partner_id ?>">
                <input type="hidden" name="payment_direction" value="<?= htmlspecialchars($payment_direction ?? 'outgoing') ?>">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nominal Bayar</label>
                        <div class="nominal-input-group">
                            <input type="text" class="form-control-modern rupiah-input nominal-shortcut-input text-end" id="amount" name="amount" placeholder="0" inputmode="numeric" required>
                            <button type="button" class="nominal-shortcut-toggle active" aria-pressed="true">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control-modern" name="payment_date" value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <?php if ($payment_direction == 'outgoing'): ?>
                    <!-- User membayar ke partner (parent to child) -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Dompet Saya (Asal)</label>
                        <select class="form-select-modern" id="wallet_id_payer" name="wallet_id_payer" required>
                            <option value="">Pilih dompet</option>
                            <?php foreach ($my_wallets as $w): ?>
                            <option value="<?= $w->id ?>"><?= htmlspecialchars($w->nama_wallet) ?> - <?= number_format($w->saldo_awal, 0, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Dompet <?= htmlspecialchars($selected_partner->partner_fullname) ?> (Tujuan)</label>
                        <select class="form-select-modern" id="wallet_id_receiver" name="wallet_id_receiver" required>
                            <option value="">Pilih dompet tujuan</option>
                            <?php foreach ($partner_wallets as $w): ?>
                            <option value="<?= $w->id ?>"><?= htmlspecialchars($w->nama_wallet) ?> <?= "- ".$is_admin?number_format($w->saldo_awal, 0, ',', '.'):"" ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php elseif ($payment_direction == 'incoming'): ?>
                    <!-- User menerima bayaran dari partner (child to parent) -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Dompet <?= htmlspecialchars($selected_partner->partner_fullname) ?> (Asal)</label>
                        <select class="form-select-modern" id="wallet_id_payer" name="wallet_id_payer" required>
                            <option value="">Pilih dompet</option>
                            <?php foreach ($partner_wallets as $w): ?>
                            <option value="<?= $w->id ?>"><?= htmlspecialchars($w->nama_wallet) ?> (Rp <?= number_format($w->saldo_awal, 0, ',', '.') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Dompet milik <?= htmlspecialchars($selected_partner->partner_fullname) ?> yang akan digunakan untuk membayar</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Dompet Saya (Tujuan)</label>
                        <select class="form-select-modern" id="wallet_id_receiver" name="wallet_id_receiver" required>
                            <option value="">Pilih dompet tujuan</option>
                            <?php foreach ($my_wallets as $w): ?>
                            <option value="<?= $w->id ?>"><?= htmlspecialchars($w->nama_wallet) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Dompet Anda yang akan menerima pembayaran</small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control-modern" name="note" rows="2" placeholder="Catatan pembayaran (opsional)"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn-save w-100" id="bayarKasbonBtn">
                            <i class="fas fa-check-circle me-2"></i>Bayar Kasbon
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Daftar Transaksi Kasbon -->
    <div class="col-12">
        <div class="section-header">
            <h6><i class="fas fa-list me-2"></i>Riwayat Kasbon</h6>
        </div>
        <?php if ($loan_transactions): ?>
        <div class="table-responsive table-card">
            <table class="table table-striped table-hover align-middle mb-0 kasbon-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_loan_display = 0;
                    foreach ($loan_transactions as $t): 
                        $is_cancelled = (int)($t->is_loan ?? 0) === 2;
                        if (!$is_cancelled) $total_loan_display += $t->nominal;
                    ?>
                    <tr class="<?= $is_cancelled ? 'loan-cancelled' : '' ?>">
                        <td data-label="Tanggal"><?= date('d-m-y', strtotime($t->tanggal)) ?> <span class="text-muted fw-normal"># <?= htmlspecialchars($t->nama_kategori ?? '-') ?></span></td>
                        <td data-label="Deskripsi"><?= htmlspecialchars($t->deskripsi) ?></td>
                        <td data-label="Nominal" class="text-end <?= $is_cancelled ? 'text-secondary' : 'text-danger' ?> fw-semibold">- Rp <?= number_format($t->nominal, 0, ',', '.') ?></td>
                        <td data-label="Aksi" class="text-center">
                            <?php if ($is_cancelled): ?>
                            <button class="btn btn-sm btn-outline-success pulihkan-kasbon" data-id="<?= $t->id ?>" title="Pulihkan kasbon">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn btn-sm btn-outline-danger batalkan-kasbon" data-id="<?= $t->id ?>" data-deskripsi="<?= htmlspecialchars($t->deskripsi) ?>" title="Batalkan kasbon">
                                <i class="fas fa-ban"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($loan_total_pages > 1): ?>
        <nav class="mt-3" aria-label="Pagination kasbon">
            <ul class="pagination pagination-sm justify-content-end mb-0">
                <li class="page-item <?= $loan_page <= 1 ? 'disabled' : '' ?>"><a class="page-link js-ajax-page-link" href="<?= site_url('loan') . '?' . http_build_query($loan_base_params + ['page' => max(1, $loan_page - 1)]) ?>">Prev</a></li>
                <?php for ($i = 1; $i <= $loan_total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $loan_total_pages || abs($i - $loan_page) <= 2): ?>
                    <li class="page-item <?= $i == $loan_page ? 'active' : '' ?>"><a class="page-link js-ajax-page-link" href="<?= site_url('loan') . '?' . http_build_query($loan_base_params + ['page' => $i]) ?>"><?= $i ?></a></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?= $loan_page >= $loan_total_pages ? 'disabled' : '' ?>"><a class="page-link js-ajax-page-link" href="<?= site_url('loan') . '?' . http_build_query($loan_base_params + ['page' => min($loan_total_pages, $loan_page + 1)]) ?>">Next</a></li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
            <p>Belum ada transaksi kasbon</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Riwayat Pembayaran -->
    <?php if ($payment_history): ?>
    <div class="col-12">
        <div class="section-header">
            <h6><i class="fas fa-history me-2"></i>Riwayat Pembayaran Kasbon</h6>
        </div>
        <div class="table-responsive table-card">
            <table class="table table-sm align-middle mb-0 kasbon-payment-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th>Dompet</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payment_history as $p): ?>
                    <tr>
                        <td data-label="Tanggal"><?= date('d-m-y', strtotime($p->payment_date)) ?></td>
                        <td data-label="Jenis">
                            <span class="badge <?= $p->tipe === 'pemasukan' ? 'bg-success' : 'bg-danger' ?>">
                                <?= $p->tipe === 'pemasukan' ? 'Masuk' : 'Keluar' ?>
                            </span>
                        </td>
                        <td data-label="Nominal" class="<?= $p->tipe === 'pemasukan' ? 'text-success' : 'text-danger' ?> fw-semibold">
                            <?= $p->tipe === 'pemasukan' ? '+ ' : '- ' ?>Rp <?= number_format($p->amount, 0, ',', '.') ?>
                        </td>
                        <td data-label="Dompet"><?= htmlspecialchars($p->wallet_name ?? '-') ?><i class="fas fa-ellipsis-v text-secondary ms-2 toggle-detail" role="button"></i></td>
                        <td data-label="Catatan"><?= htmlspecialchars($p->note ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</div>

<!-- Modal Batalkan Kasbon -->
<div class="modal fade" id="modalBatalkanKasbon" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batalkan Kasbon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin membatalkan kasbon: <strong id="cancelTransactionDeskripsi"></strong>?</p>
                <div class="mb-3">
                    <label class="form-label">Alasan Pembatalan <span class="text-danger">*</span></label>
                    <textarea class="form-control-modern" id="cancelReason" rows="3" placeholder="Wajib diisi..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger" id="confirmBatalkanBtn">Batalkan Kasbon</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let cancelTransactionId = null;
    let cancelBtnRef = null;

    function swapRowAksi(row, toCancelled) {
        let aksiCell = row.find('td[data-label="Aksi"]');
        let nominalCell = row.find('td[data-label="Nominal"]');
        if (toCancelled) {
            row.addClass('loan-cancelled');
            nominalCell.removeClass('text-danger').addClass('text-secondary');
            aksiCell.html('<button class="btn btn-sm btn-outline-success pulihkan-kasbon" data-id="' + aksiCell.find('.batalkan-kasbon').data('id') + '" title="Pulihkan kasbon"><i class="fas fa-check"></i></button>');
        } else {
            row.removeClass('loan-cancelled');
            nominalCell.removeClass('text-secondary').addClass('text-danger');
            let id = aksiCell.find('.pulihkan-kasbon').data('id');
            aksiCell.html('<button class="btn btn-sm btn-outline-danger batalkan-kasbon" data-id="' + id + '" title="Batalkan kasbon"><i class="fas fa-ban"></i></button>');
        }
    }
    
    $('#formBayarKasbon').submit(function(e) {
        e.preventDefault();
        
        let amount = $('#amount').val().replace(/\./g, '');
        if (!amount || amount <= 0) {
            Swal.fire('Error', 'Nominal bayar wajib diisi', 'error');
            return;
        }
        
        let data = $(this).serialize();
        data += '&amount=' + amount;
        
        $('#bayarKasbonBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
        
        $.ajax({
            url: '<?=  base_url('loan/bayar') ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    setTimeout(() => refreshCurrentMainContent(window.location.href, { pushState: false }), 1000);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                    $('#bayarKasbonBtn').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Bayar Kasbon');
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
                $('#bayarKasbonBtn').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Bayar Kasbon');
            }
        });
    });
    
    $(document).on('click', '.batalkan-kasbon', function() {
        cancelTransactionId = $(this).data('id');
        cancelBtnRef = $(this).closest('tr');
        let deskripsi = $(this).data('deskripsi');
        $('#cancelTransactionDeskripsi').text(deskripsi);
        $('#cancelReason').val('');
        $('#modalBatalkanKasbon').modal('show');
    });

    $(document).on('click', '.pulihkan-kasbon', function() {
        let btn = $(this);
        let row = btn.closest('tr');
        Swal.fire({
            title: 'Pulihkan kasbon?',
            text: 'Kasbon akan dikembalikan ke status aktif dan saldo akan dikurangi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#27ae60',
            confirmButtonText: 'Ya, pulihkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.ajax({
                    url: '<?= base_url('loan/pulihkan') ?>',
                    type: 'POST',
                    data: { transaction_id: btn.data('id') },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            swapRowAksi(row, false);
                            Swal.fire('Berhasil', res.message, 'success');
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                            btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                    }
                });
            }
        });
    });
    
    $('#confirmBatalkanBtn').click(function() {
        let reason = $('#cancelReason').val().trim();
        
        if (!reason) {
            Swal.fire('Error', 'Alasan pembatalan wajib diisi', 'error');
            return;
        }
        
        $.ajax({
            url: '<?=  base_url('loan/batalkan') ?>',
            type: 'POST',
            data: {
                transaction_id: cancelTransactionId,
                reason: reason
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#modalBatalkanKasbon').modal('hide');
                    if (cancelBtnRef && cancelBtnRef.length) {
                        swapRowAksi(cancelBtnRef, true);
                    }
                    Swal.fire('Berhasil', res.message, 'success');
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });
});
</script>

<style>
.btn-partner-filter {
    padding: 8px 20px;
    border-radius: 40px;
    background: #f1f5f9;
    color: #475569;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.btn-partner-filter.active {
    background: #2d3e50;
    color: white;
}

.btn-partner-filter:hover {
    background: #e2e8f0;
}
</style>
