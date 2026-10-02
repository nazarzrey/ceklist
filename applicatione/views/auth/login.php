<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="AZurnal membantu mencatat pemasukan, pengeluaran, dompet, hutang, piutang, kasbon, dan target keuangan dalam satu tempat.">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AZurnal">
    <meta property="og:title" content="AZurnal - Kelola Keuangan Lebih Teratur">
    <meta property="og:description" content="Catat pemasukan, pengeluaran, dompet, hutang, piutang, kasbon, dan target keuangan dalam satu aplikasi.">
    <meta property="og:url" content="<?= htmlspecialchars(current_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= base_url('assets/images/icon-app-512.png?v4') ?>">
    <meta property="og:image:secure_url" content="<?= base_url('assets/images/icon-app-512.png?v4') ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="AZurnal - Kelola Keuangan Lebih Teratur">
    <meta name="twitter:description" content="Catat dan pantau keuangan pribadi dalam satu aplikasi.">
    <meta name="twitter:image" content="<?= base_url('assets/images/icon-app-512.png?v4') ?>">
    <title>Login - AZurnal</title>
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

        .login-container {
            width: 100%;
            max-width: 480px;
        }

        .login-card {
            background: white;
            border-radius: 40px;
            padding: 48px 40px;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.05);
            border: 1px solid #eef2f6;
        }

        /* Logo/Brand */
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            /* background: #ffffff; */
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            /* margin-bottom: 20px; */
            /* box-shadow: 0 10px 24px rgba(26,42,58,0.12); */
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

        /* Form */
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

        /* Button */
        .btn-login {
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

        .btn-login:hover {
            background: #1a2a3a;
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .google-login {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 14px;
            margin-top: 12px;
            background: #ffffff;
            color: #334155;
            border: 1px solid #dbe3ec;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .google-login i {
            color: #4285f4;
        }

        .google-login:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
            transform: translateY(-1px);
        }

        /* Alert */
        .alert-message {
            background: #fee2e2;
            border-radius: 16px;
            padding: 12px 16px;
            font-size: 0.8rem;
            color: #dc2626;
            margin-bottom: 20px;
            display: none;
        }

        /* Demo section */
        .demo-section {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #eef2f6;
            text-align: center;
        }

        .demo-title {
            font-size: 0.7rem;
            color: #a0aec0;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .demo-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .demo-btn {
            background: #f1f5f9;
            border: none;
            border-radius: 40px;
            padding: 8px 18px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .demo-btn:hover {
            background: #e2e8f0;
        }

        .demo-btn i {
            margin-right: 6px;
            font-size: 0.7rem;
        }

        /* Loading state */
        .btn-login.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-login .spinner {
            display: none;
        }

        .btn-login.loading .btn-text {
            display: none;
        }

        .btn-login.loading .spinner {
            display: inline;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }
            
            .brand h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <!-- Brand -->
        <div class="brand">
                <div class="logo-icon">
                    <img src="<?= base_url('assets/images/icon-app-192.png?v2') ?>" alt="AZurnal" style="width:64px;height:64px;">
                </div>
                <h1>AZurnal</h1>
            <p>Manajemen Keuangan Personal</p>
        </div>

        <!-- Alert -->
        <div id="loginAlert" class="alert-message">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span id="alertText"></span>
        </div>

        <!-- Form -->
        <form id="loginForm">
            <input type="hidden" id="csrfToken" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" class="input-field" id="username" name="username" placeholder="Masukkan username" autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="input-field has-toggle" id="password" name="password" placeholder="Masukkan password">
                    <button type="button" class="password-toggle" data-target="#password" aria-label="Lihat password" title="Lihat password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <span class="btn-text"><i class="fas fa-sign-in-alt me-2"></i>Masuk</span>
                <span class="spinner"><i class="fas fa-spinner fa-spin me-2"></i>Memproses...</span>
            </button>

            <a href="<?= site_url('auth/google') ?>" class="google-login">
                <i class="fab fa-google"></i> Masuk dengan Google
            </a>

            <a href="<?= site_url('reset') ?>" class="reset-link">Reset password</a>
        </form>

        <?php if (!empty($app_online)): ?>
            <div class="app-status login-status"><span class="app-status-dot"></span>Online</div>
        <?php endif; ?>
    </div>
</div>

<style>
.login-status {
    margin-top: 18px;
    text-align: center;
    color: #16a34a;
    font-size: 12px;
    font-weight: 700;
}
.app-status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    margin-right: 6px;
    border-radius: 50%;
    background: currentColor;
    vertical-align: 1px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($recaptcha_site_key)): ?><script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars($recaptcha_site_key, ENT_QUOTES, 'UTF-8') ?>"></script><?php endif; ?>

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

    const softLoginSwal = {
        fire: function(options) {
            var icon = options && options.icon ? String(options.icon).toLowerCase() : '';
            var toastTimer = 1600;

            if (icon === 'success') {
                toastTimer = 1400;
            } else if (icon === 'error') {
                toastTimer = 2200;
            } else if (icon === 'info' || icon === 'warning') {
                toastTimer = 1800;
            }

            return Swal.fire($.extend(true, {
                background: '#ffffff',
                color: '#243447',
                buttonsStyling: false,
                confirmButtonText: 'OK',
                toast: true,
                position: 'top-end',
                timer: toastTimer,
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
    };

    // Demo admin login
    $('#demoAdminBtn').click(function() {
        $('#username').val('admin');
        $('#password').val('admin123');
        $('#username').css('border-color', '#2d3e50');
        $('#password').css('border-color', '#2d3e50');
    });

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        var username = $('#username').val().trim();
        var password = $('#password').val();
        
        $('#loginAlert').hide();
        
        if (!username) {
            showAlert('Username tidak boleh kosong');
            $('#username').focus();
            return;
        }
        
        if (!password) {
            showAlert('Password tidak boleh kosong');
            $('#password').focus();
            return;
        }
        
        $('#loginBtn').addClass('loading').prop('disabled', true);

        function sendLogin(recaptchaToken) {
        $.ajax({
            url: '<?= site_url("auth/do_login") ?>',
            type: 'POST',
            data: csrfData({ username: username, password: password, recaptcha_token: recaptchaToken }),
            dataType: 'json',
            success: function(res) {
                updateCsrf(res);
                if (res.status) {
                    softLoginSwal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1400,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = res.redirect;
                    });
                } else {
                    softLoginSwal.fire({
                        icon: 'error',
                        title: 'Login gagal',
                        text: res.message
                    });
                    $('#loginBtn').removeClass('loading').prop('disabled', false);
                }
            },
            error: function() {
                softLoginSwal.fire({
                    icon: 'error',
                    title: 'Gangguan koneksi',
                    text: 'Terjadi kesalahan, coba lagi nanti'
                });
                $('#loginBtn').removeClass('loading').prop('disabled', false);
            }
        });
        }

        if (typeof grecaptcha !== 'undefined' && '<?= !empty($recaptcha_site_key) ? '1' : '0' ?>' === '1') {
            grecaptcha.ready(function() {
                grecaptcha.execute('<?= htmlspecialchars($recaptcha_site_key ?? '', ENT_QUOTES, 'UTF-8') ?>', { action: 'login' })
                    .then(sendLogin)
                    .catch(function() {
                        $('#loginBtn').removeClass('loading').prop('disabled', false);
                        showAlert('Verifikasi keamanan gagal. Silakan coba lagi.');
                    });
            });
        } else {
            sendLogin('');
        }
    });

    $('.google-login').on('click', function(e) {
        if (typeof grecaptcha === 'undefined' || '<?= !empty($recaptcha_site_key) ? '1' : '0' ?>' !== '1') return;
        e.preventDefault();
        var target = this.href;
        grecaptcha.ready(function() {
            grecaptcha.execute('<?= htmlspecialchars($recaptcha_site_key ?? '', ENT_QUOTES, 'UTF-8') ?>', { action: 'login' })
                .then(function(token) { window.location.href = target + '?captcha_token=' + encodeURIComponent(token); });
        });
    });

    $('.password-toggle').on('click', function() {
        var $button = $(this);
        var $input = $($button.data('target'));
        var isHidden = $input.attr('type') === 'password';

        $input.attr('type', isHidden ? 'text' : 'password');
        $button.attr('aria-label', isHidden ? 'Sembunyikan password' : 'Lihat password');
        $button.attr('title', isHidden ? 'Sembunyikan password' : 'Lihat password');
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

.reset-link {
    display: block;
    text-align: center;
    margin-top: 18px;
    color: #64748b;
    font-size: 0.82rem;
    text-decoration: none;
}

.reset-link:hover {
    color: #1a2a3a;
}
</style>
</body>
</html>
