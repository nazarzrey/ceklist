<div class="row g-4">
    <!-- Pilih Partner & Filter -->
    <div class="col-12">
        <div class="card-filter">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div class="d-flex gap-2 flex-wrap">
                    <label class="form-label mb-0 me-2 align-self-center">Relasi:</label>
                    <a href="<?= site_url('loan/laporan?partner_id=all&start_date=' . $start_date . '&end_date=' . $end_date) ?>" 
                       class="btn-partner-filter <?= $selected_partner_id == 'all' || !$selected_partner_id ? 'active' : '' ?>">
                        <i class="fas fa-globe me-1"></i>Semua
                    </a>
                    <?php foreach ($partners as $p): ?>
                    <a href="<?= site_url('loan/laporan?partner_id=' . $p->partner_id . '&start_date=' . $start_date . '&end_date=' . $end_date) ?>" 
                       class="btn-partner-filter <?= $selected_partner_id == $p->partner_id ? 'active' : '' ?>">
                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($p->partner_fullname) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <form method="get" class="d-flex gap-2 flex-wrap">
                    <input type="hidden" name="partner_id" value="<?= htmlspecialchars($selected_partner_id) ?>">
                    <input type="date" name="start_date" class="form-control-modern control-sm" value="<?= htmlspecialchars($start_date) ?>">
                    <input type="date" name="end_date" class="form-control-modern control-sm" value="<?= htmlspecialchars($end_date) ?>">
                    <button type="submit" class="btn-save btn-compact">Filter</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Statistik Kategori Kasbon Terbesar -->
    <div class="col-12 col-md-5">
        <div class="card-form">
            <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie me-2"></i>Kasbon Terbesar per Kategori</h6>
            <?php if ($category_stats): ?>
                <div class="category-stats-wrap">
                <?php foreach ($category_stats as $i => $cat): ?>
                <div class="mb-2 category-stat-item <?= $i >= 5 ? 'd-none' : '' ?>">
                    <div class="d-flex justify-content-between small">
                        <span><i class="fas <?= $cat->icon ?? 'fa-tag' ?> me-1"></i> <?= htmlspecialchars($cat->nama_kategori) ?></span>
                        <span class="fw-bold">Rp <?= number_format($cat->total, 0, ',', '.') ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <?php $persen = ($cat->total / ($category_stats[0]->total ?? 1)) * 100; ?>
                        <div class="progress-bar bg-primary" style="width: <?= min($persen, 100) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
                <?php if (count($category_stats) > 5): ?>
                <div class="text-center mt-2 toggle-category-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary toggle-category" data-action="more">Load More <i class="fas fa-chevron-down ms-1"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-category d-none" data-action="less">Hide <i class="fas fa-chevron-up ms-1"></i></button>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-muted text-center py-3">Belum ada data kasbon</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Ringkasan Periode -->
    <div class="col-12 col-md-7">
        <div class="row g-3">
            <div class="col-4">
                <div class="stat-card-soft text-center">
                    <small class="text-muted">Total Kasbon</small>
                    <div class="fw-bold text-primary" id="totalLoan">Rp <?= number_format($summary->total_loan_out ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card-soft text-center">
                    <small class="text-muted">Sudah Dibayar</small>
                    <div class="fw-bold text-success" id="totalPaid">Rp <?= number_format($summary->total_loan_in ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card-soft text-center">
                    <small class="text-muted">Sisa Kasbon!</small>
                    <div class="fw-bold <?= ($summary->remaining ?? 0) > 0 ? 'text-danger' : 'text-primary' ?>" id="remainingLoan">Rp <?= number_format($summary->remaining ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabel Transaksi Kasbon dengan Paginasi -->
    <div class="col-12">
        <div class="section-header">
            <h6><i class="fas fa-table me-2"></i>Detail Transaksi Kasbon</h6>
            <div class="table-toolbar">
                <input type="search" class="form-control-modern control-sm table-search" data-table-target="#loanReportTable" placeholder="Cari tabel...">
            </div>
        </div>
        
        <div class="table-responsive table-card">
            <table class="table table-striped table-hover align-middle mb-0 table-paginated loan-report-table" id="loanReportTable" data-page-size="10">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Partner</th>
                        <th class="text-end">Nominal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_loan = 0;
                    foreach ($loan_transactions as $t): 
                        $is_cancelled = (int)($t->is_loan ?? 0) === 2;
                        if (!$is_cancelled) $total_loan += $t->nominal;
                    ?>
                    <tr class="<?= $is_cancelled ? 'loan-cancelled' : '' ?>">
                        <td data-label="Tanggal"><?= date('d-m-Y', strtotime($t->tanggal)) ?></td>
                        <td data-label="Deskripsi"><?= htmlspecialchars($t->deskripsi) ?></td>
                        <td data-label="Kategori">
                            <i class="fas <?= $t->icon ?? 'fa-tag' ?> me-1"></i>
                            <?= htmlspecialchars($t->nama_kategori ?? '-') ?>
                            <?php if ($is_cancelled && !empty($t->cancelled_reason)): ?>
                            <i class="fas fa-ellipsis-v text-secondary ms-2 toggle-detail" role="button"></i>
                            <?php endif; ?>
                        </td>
                        <td data-label="Partner"><?= htmlspecialchars($t->partner_username ?? '-') ?></td>
                        <td data-label="Nominal" class="text-end fw-semibold <?= $is_cancelled ? 'text-info' : 'text-danger' ?>">
                            - Rp <?= number_format($t->nominal, 0, ',', '.') ?>
                        </td>
                        <td data-label="Status">
                            <span class="badge <?= $is_cancelled ? 'bg-secondary' : 'bg-warning' ?>">
                                <?= $is_cancelled ? 'Dibatalkan' : 'Aktif' ?>
                            </span>
                        </td>
                        <?php if ($is_cancelled && !empty($t->cancelled_reason)): ?>
                        <td data-label="Alasan Batal" class="d-none"><?= htmlspecialchars($t->cancelled_reason) ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($loan_transactions)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">Tidak ada data kasbon pada periode ini</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if ($total_loan > 0): ?>
                <tfoot>
                    <tr class="table-active fw-bold">
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td data-label="Nominal" class="text-end text-danger">- Rp <?= number_format($total_loan, 0, ',', '.') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-3" aria-label="Pagination laporan kasbon">
            <ul class="pagination pagination-sm justify-content-end mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= site_url('loan/laporan') . '?partner_id=' . $selected_partner_id . '&start_date=' . $start_date . '&end_date=' . $end_date . '&page=' . ($page - 1) ?>">Prev</a>
                </li>
                <?php
                $last_printed = 0;
                for ($i = 1; $i <= $total_pages; $i++):
                    if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2):
                        if ($last_printed && $i > $last_printed + 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= site_url('loan/laporan') . '?partner_id=' . $selected_partner_id . '&start_date=' . $start_date . '&end_date=' . $end_date . '&page=' . $i ?>"><?= $i ?></a>
                        </li>
                        <?php $last_printed = $i;
                    endif;
                endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= site_url('loan/laporan') . '?partner_id=' . $selected_partner_id . '&start_date=' . $start_date . '&end_date=' . $end_date . '&page=' . ($page + 1) ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var KEY = 'loan_category_stats_expanded';
    var $wrap = $('.card-form');
    var $items = $wrap.find('.category-stat-item');

    function showAll(btnMore, btnLess) {
        $items.removeClass('d-none');
        btnMore.addClass('d-none');
        btnLess.removeClass('d-none');
        localStorage.setItem(KEY, '1');
    }

    function showFirst5(btnMore, btnLess) {
        $items.each(function (i) { $(this).toggleClass('d-none', i >= 5); });
        btnLess.addClass('d-none');
        btnMore.removeClass('d-none');
        localStorage.setItem(KEY, '');
    }

    if (localStorage.getItem(KEY) === '1') {
        var $more = $wrap.find('.toggle-category[data-action=more]');
        var $less = $wrap.find('.toggle-category[data-action=less]');
        showAll($more, $less);
    }

    $(document).on('click', '.toggle-category', function () {
        var $btn = $(this);
        var $wrap = $btn.closest('.toggle-category-wrap');
        var $more = $wrap.find('[data-action=more]');
        var $less = $wrap.find('[data-action=less]');
        if ($btn.data('action') === 'more') {
            showAll($more, $less);
        } else {
            showFirst5($more, $less);
        }
    });
})();
</script>

<style>
.btn-partner-filter {
    padding: 6px 16px;
    border-radius: 40px;
    background: #f1f5f9;
    color: #475569;
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.2s;
}

.btn-partner-filter.active {
    background: #2d3e50;
    color: white;
}

.table-paginated tbody tr {
    transition: background 0.2s;
}
</style>
