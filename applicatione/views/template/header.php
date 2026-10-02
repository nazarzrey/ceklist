<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="description" content="AZurnal membantu mencatat pemasukan, pengeluaran, dompet, hutang, piutang, kasbon, dan target keuangan dalam satu tempat.">
    <link rel="canonical" href="<?= htmlspecialchars(current_url(), ENT_QUOTES, 'UTF-8') ?>">
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
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-name" content="<?= $this->security->get_csrf_token_name() ?>">
    <meta name="csrf-hash" content="<?= $this->security->get_csrf_hash() ?>">
    <meta name="theme-color" content="#2d3e50">
    <title>AZurnal - <?= $title ?? 'Dashboard' ?></title>
    <link rel="manifest" href="<?= base_url('manifest.webmanifest?v3') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/images/favicon-32.png?v3') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/images/favicon-16.png?v3') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/icon-app-192.png?v3') ?>">
    
    <!-- Bootstrap 5.2.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script>
        (function() {
            var csrfName = document.querySelector('meta[name="csrf-name"]').content;
            var csrfHash = document.querySelector('meta[name="csrf-hash"]').content;

            window.updateCsrfToken = function(csrf) {
                if (!csrf) {
                    return;
                }

                csrfName = csrf.name || csrfName;
                csrfHash = csrf.hash || csrfHash;
                document.querySelector('meta[name="csrf-name"]').setAttribute('content', csrfName);
                document.querySelector('meta[name="csrf-hash"]').setAttribute('content', csrfHash);
            };

            $.ajaxPrefilter(function(options) {
                var method = (options.type || options.method || 'GET').toUpperCase();
                if (method !== 'POST') {
                    return;
                }

                if (options.data instanceof FormData) {
                    if (!options.data.has(csrfName)) {
                        options.data.append(csrfName, csrfHash);
                    }
                    return;
                }

                if (typeof options.data === 'string') {
                    options.data += (options.data ? '&' : '') + encodeURIComponent(csrfName) + '=' + encodeURIComponent(csrfHash);
                    return;
                }

                options.data = $.extend({}, options.data || {}, (function() {
                    var token = {};
                    token[csrfName] = csrfHash;
                    return token;
                })());
            });

            $(document).ajaxSuccess(function(event, xhr) {
                var response = xhr.responseJSON;
                if (response && response.csrf) {
                    window.updateCsrfToken(response.csrf);
                }
            });
        })();
    </script>
    
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= base_url('assets/css/style.css') ?>?v=<?= config_item("versionJsCss") ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/assets.css') ?>?v=<?= config_item("versionJsCss") ?>" rel="stylesheet">
</head>
<?php
$CI =& get_instance();
$CI->load->model('M_setting');
$app_setting = $CI->M_setting->get_user_setting((int) $this->session->userdata('user_id'), $this->session->userdata('role') ?: 'user');
$theme_bg = $app_setting['background_color'] ?? ($this->session->userdata('bg') ?: ($user->bg ?? '#f5f7fb'));
$theme_txt = $app_setting['text_color'] ?? ($this->session->userdata('txt') ?: ($user->txt ?? '#1a2a3a'));
?>
<body data-user-id="<?= (int) $this->session->userdata('user_id') ?>" style="--user-bg: <?= htmlspecialchars($theme_bg, ENT_QUOTES, 'UTF-8') ?>; --user-txt: <?= htmlspecialchars($theme_txt, ENT_QUOTES, 'UTF-8') ?>;">

<div class="app-container">
