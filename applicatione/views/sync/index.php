<div class="col-12">
    <div class="section-header">
        <h6><i class="fas fa-sync me-2"></i>Sinkronisasi Saldo Dompet</h6>
    </div>

    <!-- Pilih Dompet -->
    <div class="card-filter mt-3 py-3 px-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-bold small">Pilih Dompet</span>
            <label class="small mb-0 cursor-pointer">
                <input type="checkbox" id="checkAll" checked> Pilih Semua
            </label>
        </div>
        <div id="walletList" class="d-flex flex-wrap gap-2">
            <?php foreach ($wallets as $w): ?>
            <label class="wallet-chip">
                <input type="checkbox" class="wallet-check" value="<?= $w->id ?>" checked>
                <span><i class="fas fa-wallet me-1"></i><?= htmlspecialchars($w->nama_wallet) ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <button class="btn-save btn-filter-submit mt-3" id="btnHitung" style="width:auto;padding:10px 24px">
            <i class="fas fa-calculator me-2"></i>Hitung Ulang
        </button>
    </div>

    <!-- Progress + Log Monitor -->
    <div id="progressWrap" class="d-none mt-3">
        <div class="card-filter py-3 px-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold small" id="progressLabel">0 / 0</span>
                <span class="small text-muted" id="progressPct">0%</span>
            </div>
            <div class="progress" style="height:8px;border-radius:99px">
                <div class="progress-bar bg-primary" id="progressBar" style="width:0%;border-radius:99px"></div>
            </div>
            <div id="logContainer" class="mt-3" style="max-height:400px;overflow-y:auto">
                <table class="table table-sm align-middle mb-0" style="font-size:13px">
                    <thead>
                        <tr>
                            <th style="width:40px;text-align:center">Proses</th>
                            <th>Dompet</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="logBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Hasil Summary -->
    <div id="hasilWrap" class="d-none mt-3"></div>
</div>

<!-- Modal Opsi -->
<div class="modal fade" id="modalOpsi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Pilih Tindakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small" id="modalMsg"></p>
                <div class="d-flex flex-column gap-2">
                    <button class="btn-save" id="btnOpsi1" style="border-radius:12px;padding:12px;text-align:left">
                        <i class="fas fa-pen me-2"></i>
                        <strong>Opsi 1: Ubah saldo_awal</strong>
                        <div class="small fw-normal text-white-50">Saldo dompet disesuaikan dengan hasil hitungan</div>
                    </button>
                    <button class="btn-save" id="btnOpsi2" style="border-radius:12px;padding:12px;text-align:left;background:#6366f1">
                        <i class="fas fa-plus-circle me-2"></i>
                        <strong>Opsi 2: Catat transaksi sinkronisasi</strong>
                        <div class="small fw-normal text-white-50">Buat transaksi penyesuaian (saldo_awal tetap)</div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wallet-chip {
    display:inline-flex;align-items:center;gap:6px;cursor:pointer;
    padding:6px 14px;border-radius:99px;border:1.5px solid #e0e0e0;
    background:#fff;font-size:13px;transition:all .15s;user-select:none
}
.wallet-chip:has(input:checked) {
    border-color:#4f46e5;background:#eef2ff
}
.wallet-chip input { display:none }
#logBody td { vertical-align:middle }
#logBody .status-text { font-family:monospace;font-size:12px }
.status-wait { color:#94a3b8 }
.status-progress { color:#6366f1 }
.status-done { color:#16a34a }
.status-fail { color:#dc2626 }
</style>

<script>
$(document).ready(function() {
    let allResults = [];
    let walletQueue = [];

    // Select all / none
    $("#checkAll").change(function() {
        $(".wallet-check").prop("checked", $(this).prop("checked"));
    });
    $(".wallet-check").change(function() {
        let total = $(".wallet-check").length;
        let checked = $(".wallet-check:checked").length;
        $("#checkAll").prop("checked", checked === total);
        $("#checkAll").prop("indeterminate", checked > 0 && checked < total);
    });

    // Start calculation
    $("#btnHitung").click(function() {
        let ids = [];
        $(".wallet-check:checked").each(function() { ids.push($(this).val()); });
        if (!ids.length) { Swal.fire("Peringatan", "Pilih minimal satu dompet", "warning"); return; }

        let walletNames = {};
        $(".wallet-chip").each(function() {
            let $cb = $(this).find(".wallet-check");
            walletNames[$cb.val()] = $.trim($(this).find("span").text());
        });

        allResults = [];
        walletQueue = ids.slice();

        $("#progressWrap").removeClass("d-none");
        $("#hasilWrap").addClass("d-none").empty();
        $("#logBody").empty();

        // FASE 1: Daftar semua wallet — status "menunggu"
        $.each(ids, function(i, id) {
            let name = walletNames[id] || "ID " + id;
            let row = '<tr data-id="' + id + '">';
            row += '<td class="text-center"><i class="fas fa-spinner fa-spin" style="color:#6366f1"></i></td>';
            row += '<td><strong>' + name + '</strong></td>';
            row += '<td><span class="status-text status-wait">menunggu...</span></td>';
            row += '</tr>';
            $("#logBody").append(row);
        });

        updateProgress(0, ids.length);
        $(this).prop("disabled", true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menghitung...');

        // FASE 2: Proses satu per satu
        processNext();
    });

    function processNext() {
        if (!walletQueue.length) {
            finishCalculation();
            return;
        }
        let id = walletQueue.shift();
        let done = allResults.length;
        let total = done + walletQueue.length + 1;

        // Update status ke "memproses..."
        let $row = $("tr[data-id='" + id + "']");
        $row.find(".status-text").attr("class", "status-text status-progress").text("memproses...");

        $.ajax({
            url: base_url + "sync/calculate",
            type: "POST",
            data: { wallet_id: id },
            dataType: "json",
            success: function(res) {
                if (res.status) {
                    allResults.push(res.result);
                    updateLogRow(res.result);
                } else {
                    let err = { id: parseInt(id), nama: "", saldo_awal: 0, calculated: 0, diff: 0, error: res.message };
                    allResults.push(err);
                    updateLogRow(err);
                }
                updateProgress(allResults.length, total);
                processNext();
            },
            error: function(xhr, status, err) {
                let e = { id: parseInt(id), nama: "", saldo_awal: 0, calculated: 0, diff: 0, error: err || "Gagal" };
                allResults.push(e);
                updateLogRow(e);
                updateProgress(allResults.length, total);
                processNext();
            }
        });
    }

    function updateProgress(done, total) {
        let pct = total > 0 ? Math.round(done / total * 100) : 0;
        $("#progressBar").css("width", pct + "%");
        $("#progressLabel").text(done + " / " + total);
        $("#progressPct").text(pct + "%");
    }

    function updateLogRow(r) {
        let $row = $("tr[data-id='" + r.id + "']");
        if (!$row.length) return;

        let diff = r.diff || 0;
        let absDiff = Math.abs(diff);
        let hasDiff = absDiff > 0.001;

        let iconClass = r.error ? "fa-times-circle status-fail" : "fa-check-circle status-done";
        $row.find("td:first").html('<i class="fas ' + iconClass + '" style="font-size:16px"></i>');

        let status = "";
        if (r.error) {
            status = '<span class="status-text status-fail">Gagal: ' + r.error + '</span>';
        } else if (hasDiff) {
            let sign = diff > 0 ? "+" : "";
            let cls = diff > 0 ? "status-done" : "status-fail";
            status = '<span class="status-text ' + cls + '">';
            status += 'Rp ' + formatRupiah(r.saldo_awal) + ' &rarr; Rp ' + formatRupiah(r.calculated);
            status += ' (' + sign + formatRupiah(diff) + ')</span>';
        } else {
            status = '<span class="status-text status-done">';
            status += 'Rp ' + formatRupiah(r.saldo_awal) + ' &rarr; Rp ' + formatRupiah(r.calculated);
            status += ' (sesuai)</span>';
        }
        status += '<br><span class="small text-muted">';
        status += 'Ms: ' + formatRupiah(r.pemasukan) + ' | ';
        status += 'Kl: ' + formatRupiah(r.pengeluaran) + ' | ';
        status += 'TO: ' + formatRupiah(r.outgoing) + ' | ';
        status += 'TI: ' + formatRupiah(r.transfer_masuk);
        status += '</span>';
        $row.find("td:last").html(status);

        let $container = $("#logContainer");
        $container.scrollTop($container[0].scrollHeight);
    }

    function finishCalculation() {
        let hasDiff = allResults.some(function(r) { return Math.abs(r.diff || 0) > 0.001; });
        let totalDiff = allResults.reduce(function(sum, r) { return sum + (r.diff || 0); }, 0);

        let html = '<div class="card-filter py-3 px-3">';
        html += '<div class="table-responsive"><table class="table table-sm align-middle mb-0" style="font-size:12px"><thead><tr>';
        html += '<th>Dompet</th><th class="text-end">Pemasukan</th><th class="text-end">Pengeluaran</th><th class="text-end">Transfer Out</th><th class="text-end">Transfer In</th><th class="text-end">Calculated</th><th class="text-end">Selisih</th>';
        html += '</tr></thead><tbody>';
        $.each(allResults, function(i, r) {
            let d = r.diff || 0;
            let cls = d > 0 ? 'text-success' : (d < 0 ? 'text-danger' : '');
            html += '<tr>';
            html += '<td><strong>' + r.nama + '</strong></td>';
            html += '<td class="text-end text-success">' + formatRupiah(r.pemasukan) + '</td>';
            html += '<td class="text-end text-danger">' + formatRupiah(r.pengeluaran) + '</td>';
            html += '<td class="text-end text-danger">' + formatRupiah(r.outgoing) + '</td>';
            html += '<td class="text-end text-success">' + formatRupiah(r.transfer_masuk) + '</td>';
            html += '<td class="text-end fw-bold">' + formatRupiah(r.calculated) + '</td>';
            html += '<td class="text-end fw-bold ' + cls + '">' + (d > 0 ? '+' : '') + formatRupiah(d) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        if (hasDiff) {
            html += '<div class="alert alert-warning mt-3 mb-0 py-2 small">Ditemukan selisih total: <strong>Rp ' + formatRupiah(totalDiff) + '</strong></div>';
            html += '<button class="btn-save btn-filter-submit mt-3" id="btnPilihOpsi" style="width:auto;padding:10px 20px"><i class="fas fa-tools me-2"></i>Pilih Tindakan</button>';
        } else {
            html += '<div class="alert alert-success mt-3 mb-0 py-2 small">Saldo semua dompet sudah sesuai. Tidak ada selisih.</div>';
        }
        html += '</div>';
        $("#hasilWrap").html(html).removeClass("d-none");

        $("#btnHitung").html('<i class="fas fa-calculator me-2"></i>Hitung Ulang').prop("disabled", false);
    }

    // Modal actions (use allResults)
    $(document).on("click", "#btnPilihOpsi", function() {
        let totalDiff = allResults.reduce(function(s, r) { return s + (r.diff || 0); }, 0);
        $("#modalMsg").text('Total selisih: Rp ' + formatRupiah(totalDiff) + '. Pilih tindakan:');
        $("#modalOpsi").modal("show");
    });

    $(document).on("click", "#btnOpsi1", function() {
        let $btn = $(this).prop("disabled", true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
        $.ajax({
            url: base_url + "sync/apply_option1",
            type: "POST",
            data: { wallets: JSON.stringify(allResults) },
            dataType: "json",
            success: function(res) {
                if (res.status) { Swal.fire("Berhasil", res.message, "success").then(function() { location.reload(); }); }
                else { Swal.fire("Gagal", res.message, "error"); }
            },
            complete: function() {
                $btn.html('<i class="fas fa-pen me-2"></i> Opsi 1: Ubah saldo_awal').prop("disabled", false);
                $("#modalOpsi").modal("hide");
            }
        });
    });

    $(document).on("click", "#btnOpsi2", function() {
        let $btn = $(this).prop("disabled", true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
        $.ajax({
            url: base_url + "sync/apply_option2",
            type: "POST",
            data: { wallets: JSON.stringify(allResults) },
            dataType: "json",
            success: function(res) {
                if (res.status) { Swal.fire("Berhasil", res.message, "success").then(function() { location.reload(); }); }
                else { Swal.fire("Gagal", res.message, "error"); }
            },
            complete: function() {
                $btn.html('<i class="fas fa-plus-circle me-2"></i> Opsi 2: Catat transaksi sinkronisasi').prop("disabled", false);
                $("#modalOpsi").modal("hide");
            }
        });
    });
});
</script>