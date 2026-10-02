<!-- Modal Notifikasi Request Partner -->
<div class="modal fade" id="partnerRequestModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="request-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h5 class="modal-title">Permintaan Relasi Baru</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="request-avatar mb-3">
                    <i class="fas fa-user-plus fa-3x text-primary"></i>
                </div>
                <p class="request-message mb-2" id="requestMessage">Memuat...</p>
                <small class="text-muted" id="requestDate"></small>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-3">
                <button type="button" class="btn-reject-request" id="rejectRequestBtn">
                    <i class="fas fa-times me-2"></i>Tolak
                </button>
                <button type="button" class="btn-approve-request" id="approveRequestBtn">
                    <i class="fas fa-check me-2"></i>Setujui
                </button>
            </div>
        </div>
    </div>
</div>  

<!-- jQuery 4.0.0 -->
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Custom JS -->
<script src="<?= base_url('assets/js/app.js') ?>?v=<?= config_item("versionJsCss") ?>"></script>

<style>
    .swal-soft-toast {
        border-radius: 16px;
        padding: 0.85rem 1rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    }

    .swal-soft-title {
        color: #243447;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .swal-soft-text {
        color: #5b6776;
        line-height: 1.45;
        font-size: 0.82rem;
    }

    .swal-soft-confirm,
    .swal-soft-cancel {
        border: 0;
        border-radius: 10px;
        padding: 8px 14px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .swal-soft-confirm {
        background: #2d3e50;
        color: #fff;
    }

    .swal-soft-cancel {
        background: #e2e8f0;
        color: #475569;
    }

    .swal-soft-toast .swal2-actions {
        gap: 8px;
    }
        
    .request-icon {
        width: 40px;
        height: 40px;
        background: #e8f0fe;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .request-icon i {
        font-size: 1.2rem;
        color: #2d3e50;
    }

    .request-avatar {
        width: 70px;
        height: 70px;
        background: #e8f0fe;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .request-message {
        font-size: 1rem;
        font-weight: 500;
    }

    .btn-approve-request {
        background: #22c55e;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 40px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-approve-request:hover {
        background: #16a34a;
        transform: translateY(-1px);
    }

    .btn-reject-request {
        background: #f1f5f9;
        color: #ef4444;
        border: 1px solid #e2e8f0;
        padding: 10px 24px;
        border-radius: 40px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-reject-request:hover {
        background: #fee2e2;
        border-color: #ef4444;
    }
</style>

<script>
    (function() {
        if (typeof window.Swal === 'undefined') {
            return;
        }

        var nativeFire = window.Swal.fire.bind(window.Swal);

        function normalizeSwalOptions(arg1, arg2, arg3) {
            return typeof arg1 === 'object'
                ? $.extend(true, {}, arg1)
                : { title: arg1, text: arg2, icon: arg3 };
        }

        function withSoftDefaults(options) {
            var isFormDialog = !!(options && options.html && /<input\b|<select\b|<textarea\b/i.test(String(options.html)));
            var isInteractive = !!(options && (options.showCancelButton || options.showDenyButton || options.input || isFormDialog));
            var icon = options && options.icon ? String(options.icon).toLowerCase() : '';
            var toastTimer = 1600;

            if (icon === 'success') {
                toastTimer = 1400;
            } else if (icon === 'error') {
                toastTimer = 2200;
            } else if (icon === 'info' || icon === 'warning') {
                toastTimer = 1800;
            }

            var merged = $.extend(true, {
                background: '#ffffff',
                color: '#243447',
                confirmButtonText: 'OK',
                buttonsStyling: false,
                reverseButtons: true,
                toast: isFormDialog ? false : true,
                position: isFormDialog ? 'center' : (isInteractive ? 'top' : 'top-end'),
                timer: isInteractive ? undefined : toastTimer,
                timerProgressBar: !isInteractive,
                showConfirmButton: isInteractive,
                showCloseButton: !isInteractive,
                customClass: {
                    popup: 'swal-soft-toast',
                    title: 'swal-soft-title',
                    htmlContainer: 'swal-soft-text',
                    confirmButton: 'swal-soft-confirm',
                    cancelButton: 'swal-soft-cancel'
                }
            }, options || {});

            if (merged.showCancelButton || merged.showDenyButton || merged.input || isFormDialog) {
                merged.toast = isFormDialog ? false : true;
                merged.position = isFormDialog ? 'center' : (merged.position || 'top');
                merged.timer = undefined;
                merged.timerProgressBar = false;
                merged.showConfirmButton = true;
                merged.showCloseButton = false;
            }

            return merged;
        }

        window.Swal.fire = function(arg1, arg2, arg3) {
            return nativeFire(withSoftDefaults(normalizeSwalOptions(arg1, arg2, arg3)));
        };
    })();

    // Set active menu dari controller
    var activeMenu = '<?= $active_menu ?? "" ?>';
    if (activeMenu) {
        $('.nav-link, .bottom-nav .nav-item, .more-item').removeClass('active');
        $('.nav-link[data-page="'+activeMenu+'"], .bottom-nav .nav-item[href*="'+activeMenu+'"], .more-item[href*="'+activeMenu+'"]').addClass('active');
        if ($('.more-item[href*="'+activeMenu+'"]').length) {
            $('#mobileMoreBtn').addClass('active');
        }
    }

    function checkReminderDue() {
        if (typeof Swal === 'undefined') return;

        $.ajax({
            url: '<?= site_url('pengingat/due') ?>',
            type: 'POST',
            data: '',
            dataType: 'json',
            success: function(res) {
                if (!res.status || !res.data || !res.data.length) return;

                var firstItem = res.data[0];
                var message = firstItem.nama + ' jatuh tempo ' + firstItem.next_date;
                if (res.data.length > 1) {
                    message += ' dan ' + (res.data.length - 1) + ' pengingat lain';
                }
                Swal.fire({
                    title: 'Pengingat jatuh tempo',
                    text: message,
                    icon: 'warning',
                    timer: 2000,
                    showConfirmButton: false,
                    didOpen: function(popup) {
                        popup.style.cursor = 'pointer';
                        popup.addEventListener('click', function() {
                            window.location.href = '<?= site_url('pengingat') ?>';
                        });
                    }
                });
            }
        });
    }

    checkReminderDue();
    setInterval(checkReminderDue, 10 * 60 * 1000);
$(document).ready(function() {
    let currentRequestId = null;
    let requestCheckInterval = null;
    let hasShownModal = false; // Flag agar modal hanya muncul sekali per session
    function checkPendingRequests() {
        // alert($("#navTrans").find(class)) 
        // Jangan cek lagi jika modal sudah pernah ditampilkan di session ini
        if (hasShownModal) return;
        <?php
            if(!$can_access_loan_report){            
        ?>
        $.ajax({
            url: base_url + 'partner/check_pending_requests',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status && res.has_pending && res.request) {
                    // Tampilkan modal jika ada request pending
                    currentRequestId = res.request.id;
                    $('#requestMessage').html('<strong>' + escapeHtml(res.request.fullname || res.request.username) + '</strong> mengirimkan permintaan relasi kepada Anda');
                    $('#requestDate').text(res.request.created_at_formatted || 'Baru saja');
                    $('#partnerRequestModal').modal('show');
                    hasShownModal = true; // Tandai sudah ditampilkan
                    
                    // Hentikan pengecekan
                    if (requestCheckInterval) {
                        clearInterval(requestCheckInterval);
                        requestCheckInterval = null;
                    }
                }
            }
        });
        <?php } ?>
    }
    
    function startRequestChecking() {
        if (requestCheckInterval) return;
        // Hanya di halaman transaksi
        if (!$("#formTransaksi").length) return;
        requestCheckInterval = setInterval(checkPendingRequests, 300000);
        checkPendingRequests();
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
    
    // Approve request
    $('#approveRequestBtn').click(function() {
        if (!currentRequestId) return;
        
        $('#approveRequestBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
        
        $.ajax({
            url: base_url + 'partner/approve',
            type: 'POST',
            data: { request_id: currentRequestId },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#partnerRequestModal').modal('hide');
                    
                    // Refresh halaman untuk update menu
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                    $('#approveRequestBtn').prop('disabled', false).html('<i class="fas fa-check me-2"></i>Setujui');
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
                $('#approveRequestBtn').prop('disabled', false).html('<i class="fas fa-check me-2"></i>Setujui');
            }
        });
    });
    
    // Reject request
    $('#rejectRequestBtn').click(function() {
        if (!currentRequestId) return;
        
        Swal.fire({
            title: 'Tolak permintaan?',
            text: 'Anda yakin menolak permintaan relasi ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, tolak!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#rejectRequestBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
                
                $.ajax({
                    url: base_url + 'partner/reject',
                    type: 'POST',
                    data: { request_id: currentRequestId },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Ditolak', res.message, 'success');
                            $('#partnerRequestModal').modal('hide');
                            hasShownModal = false; // Reset flag
                            startRequestChecking(); // Cek lagi nanti
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                        $('#rejectRequestBtn').prop('disabled', false).html('<i class="fas fa-times me-2"></i>Tolak');
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                        $('#rejectRequestBtn').prop('disabled', false).html('<i class="fas fa-times me-2"></i>Tolak');
                    }
                });
            }
        });
    });
    
    // Reset flag ketika modal ditutup manual oleh user
    $('#partnerRequestModal').on('hidden.bs.modal', function() {
        // Tidak reset flag, biar tidak muncul lagi di page yang sama
    });
    
    // Mulai pengecekan
    startRequestChecking();
});
</script>
</body>
</html>
