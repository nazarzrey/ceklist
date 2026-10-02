<style>
.dashboard-status {
    color: #16a34a;
    font-size: 11px;
    font-weight: 700;
    margin-right: 10px;
    white-space: nowrap;
}
.dashboard-status .app-status-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    margin-right: 4px;
    border-radius: 50%;
    background: currentColor;
    vertical-align: 1px;
}
</style>

<div class="row g-4">
    <?php
    $has_loan_summary = !empty($loan_summary) && (
        (float) ($loan_summary->total_loan_out ?? 0) > 0 ||
        (float) ($loan_summary->total_loan_in ?? 0) > 0 ||
        (float) ($loan_summary->net_loan ?? 0) !== 0.0
    );
    ?>
    <div class="col-12">
        <div class="dashboard-brand-card">
            <img src="<?= base_url('assets/images/icon-app-192.png?v2') ?>" alt="AZurnal" style="width:36px;height:36px;object-fit:cover;">
            <div>
                <div class="fw-bold">AZurnal</div>
                <small class="text-muted">Ringkasan keuangan pribadi</small>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card-balance">
            <div class="balance-header">
                <span>Total Saldo</span>
                <button type="button" class="privacy-toggle text-white" data-privacy-target="saldo" aria-label="Toggle saldo">
                    <i class="fas fa-eye-slash"></i>
                </button>
            </div>
            <div class="balance-amount privacy-value fw-bold" data-privacy-key="saldo">Rp <?= number_format($total_saldo, 0, ',', '.') ?></div>
            <div class="balance-stats">
                <div class="stat-item">
                    <i class="fas fa-arrow-up text-success"></i>
                    <div>
                        <small>Pemasukan bulan ini</small>
                        <strong class="text-success privacy-value" data-privacy-key="pemasukan">Rp <?= number_format($pemasukan_bulan_ini, 0, ',', '.') ?></strong>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-arrow-down text-danger"></i>
                    <div>
                        <small>Pengeluaran bulan ini</small>
                        <strong class="text-danger privacy-value" data-privacy-key="pengeluaran">Rp <?= number_format($pengeluaran_bulan_ini, 0, ',', '.') ?></strong>
                    </div>
                </div>
                <?php if ($has_loan_summary): ?>
                <div class="stat-item">
                    <i class="fas fa-hand-holding-usd text-primary"></i>
                    <div>
                        <small>Sisa Kasbon.</small>
                        <strong class="privacy-value" data-privacy-key="kasbon">Rp <?= number_format($loan_summary->net_loan ?? 0, 0, ',', '.') ?></strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="row g-3">
            <div class="col-6">
                <div class="stat-card-soft">
                    <div class="d-flex justify-content-between align-items-start">
                        <i class="fas fa-mobile-alt text-primary fa-lg"></i>
                        <button type="button" class="privacy-toggle" data-privacy-target="saldo" aria-label="Toggle saldo">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Saldo Online</small>
                        <div class="fw-bold privacy-value" data-privacy-key="saldo">Rp <?= number_format($saldo_online, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card-soft">
                    <div class="d-flex justify-content-between align-items-start">
                        <i class="fas fa-money-bill-wave text-success fa-lg"></i>
                        <button type="button" class="privacy-toggle" data-privacy-target="saldo" aria-label="Toggle saldo">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Saldo Cash</small>
                        <div class="fw-bold privacy-value" data-privacy-key="saldo">Rp <?= number_format($saldo_cash, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card-soft">
                    <div class="d-flex justify-content-between align-items-start">
                        <i class="fas fa-hand-holding-usd text-warning fa-lg"></i>
                        <button type="button" class="privacy-toggle" data-privacy-target="hutang" aria-label="Toggle hutang">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Total Hutang</small>
                        <div class="fw-bold privacy-value" data-privacy-key="hutang">Rp <?= number_format($total_hutang, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card-soft">
                    <div class="d-flex justify-content-between align-items-start">
                        <i class="fas fa-hand-holding-heart text-info fa-lg"></i>
                        <button type="button" class="privacy-toggle" data-privacy-target="piutang" aria-label="Toggle piutang">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Total Piutang</small>
                        <div class="fw-bold privacy-value" data-privacy-key="piutang">Rp <?= number_format($total_piutang, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-header">
            <h6><i class="fas fa-wallet me-2"></i>Dompet Saya</h6>
            <div>
                <button type="button" class="privacy-toggle me-2" data-privacy-target="dompet" aria-label="Toggle dompet">
                    <i class="fas fa-eye-slash"></i>
                </button>
                <?php if (config_item('app_online')): ?>
                    <span class="app-status dashboard-status"><span class="app-status-dot"></span>Online</span>
                <?php endif; ?>
                <a href="<?= site_url('dompet') ?>" class="text-small">Kelola</a>
            </div>
        </div>
        <div class="row g-3" id="dompetRingkasan">
            <?php foreach ($dompet as $d): ?>
            <div class="col-6 col-md-3">
                <div class="wallet-card"<?= !empty($d->is_virtual) ? ' style="background:#fef3c7;border-color:#f59e0b"' : '' ?>>
                    <i class="fas <?= $d->icon ?> text-primary"></i>
                    <div class="fw-semibold mt-1"><?= htmlspecialchars($d->nama_wallet) ?></div>
                    <small class="privacy-value" data-privacy-key="dompet">Rp <?= number_format($d->saldo_awal, 0, ',', '.') ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($budget_alert)): ?>
    <div class="col-12">
        <div class="alert-card">
            <div class="alert-header">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                <span>Peringatan Anggaran</span>
            </div>
            <?php foreach ($budget_alert as $b): ?>
            <div class="budget-alert-item">
                <div class="d-flex justify-content-between">
                    <span><?= htmlspecialchars($b->kategori) ?></span>
                    <span><?= $b->persen ?>%</span>
                </div>
                <div class="progress mt-1" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: <?= $b->persen ?>%"></div>
                </div>
                <small>Rp <?= number_format($b->terpakai, 0, ',', '.') ?> / Rp <?= number_format($b->anggaran, 0, ',', '.') ?></small>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-12">
        <div class="section-header">
            <h6><i class="fas fa-clock me-2"></i>Transaksi Terbaru</h6>
            <div class="d-flex gap-2 align-items-center">
                <input type="search" class="form-control-modern control-sm table-search" data-table-target="#dashboardRecentTable" placeholder="Cari tabel...">
                <a href="<?= site_url('transaksi') ?>" class="text-small text-nowrap">Lihat semua</a>
            </div>
        </div>
        <?php if ($transaksi_terbaru): ?>
        <div class="table-responsive table-card">
            <table class="table table-striped table-hover align-middle mb-0 table-paginated dashboard-table" id="dashboardRecentTable" data-page-size="5">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th class="text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksi_terbaru as $t): ?>
                    <tr>
                        <td data-label="Tanggal"><?= date('d-m-y', strtotime($t->tanggal)) ?></td>
                        <td data-label="Nama">
                            <?= htmlspecialchars($t->deskripsi) ?>
                            <?php if ((int) ($t->is_loan ?? 0) === 1): ?>
                                <span class="badge bg-primary ms-2">Kasbon</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Kategori"><?= htmlspecialchars($t->tipe === 'transfer' ? 'Transfer' : ($t->nama_kategori ?? '-')) ?> # <?= htmlspecialchars($t->tipe === 'transfer' ? ($t->nama_wallet . ' -> ' . ($t->nama_wallet_tujuan ?? '-')) : $t->nama_wallet) ?></td>
                        <td data-label="Nominal" class="text-end <?= $t->tipe == 'pemasukan' ? 'text-success' : ($t->tipe === 'transfer' ? 'text-primary' : 'text-danger') ?> fw-semibold">
                            <?php if ($t->tipe === 'transfer'): ?>
                                Rp <?= number_format($t->nominal, 0, ',', '.') ?>
                            <?php else: ?>
                                <?= $t->tipe == 'pemasukan' ? '+' : '-' ?> Rp <?= number_format($t->nominal, 0, ',', '.') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <p>Belum ada transaksi</p>
                <a href="<?= site_url('transaksi') ?>" class="btn btn-sm btn-primary">Tambah Transaksi</a>
            </div>
        <?php endif; ?>
    </div>
</div>
