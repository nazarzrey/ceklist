<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth routes
$route['login'] = 'auth/index';
$route['reset'] = 'auth/reset';
$route['logout'] = 'auth/logout';
$route['auth/google'] = 'auth/google';
$route['auth/google/callback'] = 'auth/google_callback';
$route['auth/mark_install'] = 'auth/mark_install';

// Main routes
$route['dashboard'] = 'dashboard/index';
$route['transaksi'] = 'transaksi/index';
$route['hutang'] = 'hutang/index';
$route['laporan'] = 'laporan/index';
$route['dompet'] = 'dompet/index';
$route['dompet/simpan'] = 'dompet/simpan';
$route['dompet/edit'] = 'dompet/edit';
$route['dompet/hapus'] = 'dompet/hapus';
$route['dompet/aktifkan'] = 'dompet/aktifkan';
$route['dompet/get_data'] = 'dompet/get_data';

$route['budget'] = 'budget/index';
$route['pengingat'] = 'pengingat/index';
$route['pengingat/simpan'] = 'pengingat/simpan';
$route['pengingat/edit'] = 'pengingat/edit';
$route['pengingat/selesai'] = 'pengingat/selesai';
$route['pengingat/hapus'] = 'pengingat/hapus';
$route['pengingat/get_data'] = 'pengingat/get_data';
$route['pengingat/due'] = 'pengingat/due';
$route['pengingat/riwayat'] = 'pengingat/riwayat';
$route['pengingat/riwayat_edit'] = 'pengingat/riwayat_edit';
$route['pengingat/riwayat_hapus'] = 'pengingat/riwayat_hapus';
$route['profile'] = 'profile/index';
$route['profile/update_profile'] = 'profile/update_profile';
$route['profile/change_password'] = 'profile/change_password';
$route['setting'] = 'setting/index';
$route['setting/simpan'] = 'setting/simpan';
$route['users'] = 'users/index';
$route['users/activity'] = 'users/activity';
$route['users/activity/clear'] = 'users/activity_clear';
$route['pengaturan/user'] = 'users/index';
$route['pengaturan/user/activity'] = 'users/activity';
$route['pengaturan/user/activity/clear'] = 'users/activity_clear';
$route['kategori'] = 'kategori/index';
$route['kategori/simpan'] = 'kategori/simpan';
$route['kategori/edit'] = 'kategori/edit';
$route['kategori/hapus'] = 'kategori/hapus';
$route['kategori/aktifkan'] = 'kategori/aktifkan';
$route['kategori/get_data'] = 'kategori/get_data';

// API routes (for AJAX)
$route['api/get_kategori'] = 'api/get_kategori';
$route['api/get_wallets'] = 'api/get_wallets';
$route['api/suggest_transaksi'] = 'api/suggest_transaksi';
$route['transaksi/suggest'] = 'transaksi/suggest';

// Partner routes
$route['partner'] = 'partner/index';
$route['partner/cari_user'] = 'partner/cari_user';
$route['partner/kirim_request'] = 'partner/kirim_request';
$route['partner/approve'] = 'partner/approve';
$route['partner/reject'] = 'partner/reject';
$route['partner/hapus'] = 'partner/hapus';

// Loan (Kasbon) routes
$route['loan'] = 'loan/index';
$route['loan/bayar'] = 'loan/bayar';
$route['loan/batalkan'] = 'loan/batalkan';
$route['loan/laporan'] = 'loan/laporan';
$route['loan/pulihkan'] = 'loan/pulihkan';
$route['loan/get_total_kasbon'] = 'loan/get_total_kasbon';

// Notification routes
$route['notification/get_unread'] = 'notification/get_unread';
$route['notification/mark_read'] = 'notification/mark_read';
$route['partner/check_pending_requests'] = 'partner/check_pending_requests';
$route['partner/kirim_ulang_request'] = 'partner/kirim_ulang_request';
