<div class="row g-3">
    <div class="col-12">
        <div class="toggle-card">
            <div class="toggle-card-header" id="toggleSummary">
                <span><i class="fas fa-chart-simple me-2"></i>Ringkasan</span>
                <i class="fas fa-chevron-up toggle-card-icon"></i>
            </div>
            <div id="summaryWrap">
                <div class="toggle-card-body">
                    <div class="summary-laporan">
                        <div class="summary-item">
                            <span>Pemasukan</span>
                            <strong class="text-success" id="totalMasuk">Rp 0</strong>
                        </div>
                        <div class="summary-item">
                            <span>Pengeluaran</span>
                            <strong class="text-danger" id="totalKeluar">Rp 0</strong>
                        </div>
                        <div class="summary-item">
                            <span>Saldo Bersih</span>
                            <strong id="saldoBersih">Rp 0</strong>
                        </div>
                        <div class="summary-item d-none" id="virtualSaldoWrap">
                            <span>Virtual</span>
                            <strong class="text-warning" id="virtualSaldo">Rp 0</strong>
                        </div>
                        <div class="summary-item">
                            <span>Sisa Kasbon</span>
                            <strong class="text-primary" id="totalKasbonNet">Rp 0</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="toggle-card">
            <div class="toggle-card-header" id="toggleBarChart">
                <span><i class="fas fa-chart-bar me-2"></i>Rincian per Kategori</span>
                <i class="fas fa-chevron-down toggle-card-icon"></i>
            </div>
            <div id="barChartWrap" class="d-none">
                <div class="toggle-card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="chart-card bar-chart-card">
                                <h6 class="fw-bold mb-3 text-danger"><i class="fas fa-arrow-down me-2"></i>Pengeluaran</h6>
                                <div class="bar-chart-scroll" id="barPengeluaranList"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="chart-card bar-chart-card">
                                <h6 class="fw-bold mb-3 text-success"><i class="fas fa-arrow-up me-2"></i>Pemasukan</h6>
                                <div class="bar-chart-scroll" id="barPemasukanList"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="toggle-card">
            <div class="toggle-card-header" id="toggleChart">
                <span><i class="fas fa-chart-pie me-2"></i>Grafik</span>
                <i class="fas fa-chevron-down toggle-card-icon"></i>
            </div>
            <div id="chartWrap" class="d-none">
                <div class="toggle-card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="chart-card">
                                <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie me-2"></i>Pengeluaran per Kategori</h6>
                                <canvas id="chartKategori" height="200"></canvas>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="chart-card">
                                <h6 class="fw-bold mb-3"><i class="fas fa-chart-line me-2"></i>Tren Keuangan</h6>
                                <canvas id="chartTrend" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card-filter mt-3 py-2 px-3">
            <div class="row g-2 mb-2">
                <div class="col-6 col-md-8">
                    <input type="search" class="form-control-modern control-sm w-100" id="searchLaporan" placeholder="Cari transaksi..." style="min-width:0">
                </div>
                <div class="col-6 col-md-4">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="period-nav d-flex gap-1 flex-fill" role="group">
                            <button type="button" class="btn-period" id="laporanPrev" style="width:34px;padding:0"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" class="btn-period flex-fill" id="laporanToday" style="padding:6px 10px;font-size:0.75rem">Today</button>
                            <button type="button" class="btn-period" id="laporanNext" style="width:34px;padding:0"><i class="fas fa-chevron-right"></i></button>
                            <button type="button" class="btn-period bg-primary text-white border-0" id="laporanReset" style="width:34px;padding:0"><i class="fas fa-undo"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-grid d-grid gap-2">
                <input type="date" class="form-control-modern control-sm" id="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai) ?>" style="min-width:0">
                <input type="date" class="form-control-modern control-sm" id="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>" style="min-width:0">
                <select class="form-select-modern control-sm" id="filter_kategori" style="min-width:0">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori as $k): ?>
                        <option value="<?= $k->id ?>"><?= htmlspecialchars($k->nama_kategori) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-select-modern control-sm" id="filter_dompet" style="min-width:0">
                    <option value="">Semua Dompet</option>
                    <?php foreach ($dompet as $d): ?>
                        <option value="<?= $d->id ?>"><?= htmlspecialchars($d->nama_wallet) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-save btn-filter-submit" id="filterLaporanBtn" style="padding:8px 16px">Submit</button>
            </div>
        </div>
        <div class="section-header mt-4">
            <h6><i class="fas fa-list me-2"></i>Detail Transaksi</h6>
        </div>
        <div class="table-responsive table-card">
            <table class="table table-striped table-hover align-middle mb-0 laporan-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Dompet</th>
                        <th>Tipe</th>
                        <th class="text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody id="laporanDetailList">
                    <tr><td colspan="6" class="text-center text-muted py-5">Memuat laporan bulan ini...</td></tr>
                </tbody>
            </table>
        </div>
        <nav class="mt-3" aria-label="Pagination laporan">
            <ul class="pagination pagination-sm justify-content-end mb-0" id="laporanPagination"></ul>
        </nav>
    </div>
</div>