<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Reset password akun AZurnal dengan aman.">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AZurnal">
    <meta property="og:title" content="AZurnal - Reset Password">
    <meta property="og:description" content="Kelola catatan keuangan pribadi dengan lebih teratur bersama AZurnal.">
    <meta property="og:url" content="<?= htmlspecialchars(current_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= base_url('assets/images/icon-app-512.png?v4') ?>">
    <meta property="og:image:secure_url" content="<?= base_url('assets/images/icon-app-512.png?v4') ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="AZurnal - Reset Password">
    <meta name="twitter:description" content="Reset password akun AZurnal dengan aman.">
    <meta name="twitter:image" content="<?= base_url('assets/images/icon-app-512.png?v4') ?>">
    <title>Reset Password - AZurnal</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/images/favicon-32.png?v2') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/images/favicon-16.png?v2') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f5f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            width: 100%;
            max-width: 480px;
        }

        .reset-card {
            background: white;
            border-radius: 40px;
            padding: 48px 40px;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.05);
            border: 1px solid #eef2f6;
        }

        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a2a3a;
            margin-bottom: 6px;
        }

        .brand p {
            color: #7f8c8d;
            font-size: 0.85rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: #1a2a3a;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper > i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1rem;
        }

        .input-field {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid #e2e8f0;
            border-radius: 28px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background: #fafbfc;
        }

        .input-field:focus {
            outline: none;
            border-color: #2d3e50;
            background: white;
            box-shadow: 0 0 0 3px rgba(45, 62, 80, 0.05);
        }

        .input-field.is-match {
            border-color: #16a34a;
            background: #f7fef9;
        }

        .input-field.is-mismatch {
            border-color: #dc2626;
            background: #fff7f7;
        }

        .input-field.has-toggle {
            padding-right: 52px;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: color 0.2s ease, background 0.2s ease;
        }

        .password-toggle:hover,
        .password-toggle:focus {
            color: #2d3e50;
            background: #eef2f6;
            outline: none;
        }

        .password-match-message {
            min-height: 18px;
            margin-top: 6px;
            font-size: 0.76rem;
            font-weight: 500;
        }

        .password-match-message.is-match {
            color: #15803d;
        }

        .password-match-message.is-mismatch {
            color: #dc2626;
        }

        .btn-reset {
            width: 100%;
            padding: 14px;
            background: #2d3e50;
            border: none;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            margin-top: 8px;
        }

        .btn-reset:hover {
            background: #1a2a3a;
            transform: translateY(-2px);
        }

        .btn-reset:active {
            transform: translateY(0);
        }

        .btn-reset.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-reset .spinner {
            display: none;
        }

        .btn-reset.loading .btn-text {
            display: none;
        }

        .btn-reset.loading .spinner {
            display: inline;
        }

        .reset-form {
            display: none;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #eef2f6;
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #475569;
            font-size: 0.82rem;
            text-decoration: none;
        }

        .login-link:hover {
            color: #1a2a3a;
        }

        @media (max-width: 480px) {
            .reset-card {
                padding: 32px 24px;
            }

            .brand h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="reset-container">
    <div class="reset-card">
        <div class="brand">
            <div class="logo-icon">
                <img src="<?= base_url('assets/images/icon-app-192.png?v2') ?>" alt="AZurnal">
            </div>
            <h1>Reset Password</h1>
            <p>AZurnal</p>
        </div>

        <form id="codeForm">
            <input type="hidden" id="csrfToken" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="form-group">
                <label class="form-label">Kode kombinasi</label>
                <div class="input-wrapper">
                    <i class="fas fa-key"></i>
                    <input type="number" class="input-field" id="resetCode" name="code" placeholder="Masukkan kode reset" autocomplete="off">
                </div>
            </div>

            <button type="submit" class="btn-reset" id="codeBtn">
                <span class="btn-text"><i class="fas fa-unlock-alt me-2"></i>Lanjut</span>
                <span class="spinner"><i class="fas fa-spinner fa-spin me-2"></i>Memeriksa...</span>
            </button>
        </form>

        <form id="passwordForm" class="reset-form">
            <div class="form-group">
                <label class="form-label">Username atau email</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" class="input-field" id="identifier" name="identifier" placeholder="Masukkan username atau email" autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password baru</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="input-field has-toggle" id="password" name="password" placeholder="Minimal 4 karakter">
                    <button type="button" class="password-toggle" data-target="#password" aria-label="Lihat password baru" title="Lihat password baru">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="input-field has-toggle" id="confirmPassword" name="confirm_password" placeholder="Ulangi password baru">
                    <button type="button" class="password-toggle" data-target="#confirmPassword" aria-label="Lihat konfirmasi password" title="Lihat konfirmasi password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="passwordMatchMessage" class="password-match-message" aria-live="polite"></div>
            </div>

            <button type="submit" class="btn-reset" id="passwordBtn">
                <span class="btn-text"><i class="fas fa-save me-2"></i>Simpan Password</span>
                <span class="spinner"><i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...</span>
            </button>
        </form>

        <a class="login-link" href="<?= site_url('auth') ?>">Kembali ke login</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    function csrfData(data) {
        data[$('#csrfToken').attr('name')] = $('#csrfToken').val();
        return data;
    }

    function updateCsrf(res) {
        if (res && res.csrf) {
            $('#csrfToken').attr('name', res.csrf.name).val(res.csrf.hash);
        }
    }

    function toast(options) {
        return Swal.fire($.extend(true, {
            background: '#ffffff',
            color: '#243447',
            buttonsStyling: false,
            confirmButtonText: 'OK',
            toast: true,
            position: 'top-end',
            timer: 1800,
            timerProgressBar: true,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: {
                popup: 'swal-soft-toast',
                title: 'swal-soft-title',
                htmlContainer: 'swal-soft-text',
                confirmButton: 'swal-soft-confirm'
            }
        }, options || {}));
    }

    function validatePasswordMatch(showEmpty) {
        var password = $('#password').val();
        var confirmPassword = $('#confirmPassword').val();
        var $message = $('#passwordMatchMessage');
        var $fields = $('#password, #confirmPassword');

        $fields.removeClass('is-match is-mismatch');
        $message.removeClass('is-match is-mismatch').text('');

        if (!password && !confirmPassword) {
            return true;
        }

        if ((!password || !confirmPassword) && !showEmpty) {
            return true;
        }

        if (!password || !confirmPassword) {
            $fields.addClass('is-mismatch');
            $message.addClass('is-mismatch').text('Password dan konfirmasi wajib diisi');
            return false;
        }

        if (password !== confirmPassword) {
            $fields.addClass('is-mismatch');
            $message.addClass('is-mismatch').text('Password belum cocok');
            return false;
        }

        $fields.addClass('is-match');
        $message.addClass('is-match').text('Password cocok');
        return true;
    }

    $('#password, #confirmPassword').on('input', function() {
        validatePasswordMatch(false);
    });

    $('#codeForm').on('submit', function(e) {
        e.preventDefault();

        var code = $('#resetCode').val().trim();
        if (!code) {
            toast({ icon: 'warning', title: 'Kode kosong', text: 'Masukkan kode reset lebih dulu' });
            $('#resetCode').focus();
            return;
        }

        $('#codeBtn').addClass('loading').prop('disabled', true);

        $.ajax({
            url: '<?= site_url("auth/verify_reset_code") ?>',
            type: 'POST',
            data: csrfData({ code: code }),
            dataType: 'json',
            success: function(res) {
                updateCsrf(res);
                if (res.status) {
                    toast({ icon: 'success', title: 'Kode benar', text: res.message });
                    $('#passwordForm').slideDown(180);
                    $('#identifier').focus();
                } else {
                    toast({ icon: 'error', title: 'Gagal', text: res.message });
                    $('#passwordForm').hide();
                }
            },
            error: function() {
                toast({ icon: 'error', title: 'Gangguan koneksi', text: 'Permintaan tidak dapat diproses' });
            },
            complete: function() {
                $('#codeBtn').removeClass('loading').prop('disabled', false);
            }
        });
    });

    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();

        var identifier = $('#identifier').val().trim();
        var password = $('#password').val();
        var confirmPassword = $('#confirmPassword').val();

        if (!identifier) {
            toast({ icon: 'warning', title: 'Akun kosong', text: 'Masukkan username atau email' });
            $('#identifier').focus();
            return;
        }

        if (password.length < 4) {
            toast({ icon: 'warning', title: 'Password pendek', text: 'Password minimal 4 karakter' });
            $('#password').focus();
            return;
        }

        if (!validatePasswordMatch(true)) {
            toast({ icon: 'warning', title: 'Tidak sama', text: 'Konfirmasi password tidak sesuai' });
            $('#confirmPassword').focus();
            return;
        }

        $('#passwordBtn').addClass('loading').prop('disabled', true);

        $.ajax({
            url: '<?= site_url("auth/reset_password") ?>',
            type: 'POST',
            data: csrfData({
                identifier: identifier,
                password: password,
                confirm_password: confirmPassword
            }),
            dataType: 'json',
            success: function(res) {
                updateCsrf(res);
                if (res.status) {
                    toast({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1400 }).then(function() {
                        window.location.href = res.redirect;
                    });
                } else {
                    toast({ icon: 'error', title: 'Gagal', text: res.message });
                    $('#passwordBtn').removeClass('loading').prop('disabled', false);
                }
            },
            error: function() {
                toast({ icon: 'error', title: 'Gangguan koneksi', text: 'Password belum tersimpan' });
                $('#passwordBtn').removeClass('loading').prop('disabled', false);
            }
        });
    });

    $('.password-toggle').on('click', function() {
        var $button = $(this);
        var $input = $($button.data('target'));
        var isHidden = $input.attr('type') === 'password';
        var hiddenLabel = $button.data('hidden-label') || $button.attr('aria-label');
        var shownLabel = hiddenLabel.replace('Lihat', 'Sembunyikan');

        $button.data('hidden-label', hiddenLabel);
        $input.attr('type', isHidden ? 'text' : 'password');
        $button.attr('aria-label', isHidden ? shownLabel : hiddenLabel);
        $button.attr('title', isHidden ? shownLabel : hiddenLabel);
        $button.find('i').toggleClass('fa-eye', !isHidden).toggleClass('fa-eye-slash', isHidden);
        $input.focus();
    });
});
</script>
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

.swal-soft-confirm {
    border: 0;
    border-radius: 10px;
    padding: 8px 14px;
    font-weight: 600;
    font-size: 0.8rem;
    background: #2d3e50;
    color: #fff;
}
</style>
</body>
</html>
