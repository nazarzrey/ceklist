<?php
//ddbg($can_access_loan,"r");
?>

<!-- SIDEBAR (Desktop) -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <button type="button" class="sidebar-close-btn sidebar-toggle-btn d-none" id="sidebarCloseBtn" aria-label="Tutup menu">
                <i class="fas fa-times"></i>
            </button>
            <img src="<?= base_url('assets/images/icon-app-192.png?v2') ?>" alt="AZurnal" class="app-brand-icon" >
            <span>AZurnal</span>
        </div>
        
        <nav class="sidebar-nav mb-3">
            <a href="<?= site_url('dashboard') ?>" class="nav-link <?= ($active_menu ?? '') == 'dashboard' ? 'active' : '' ?>" data-page="dashboard">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
            <a href="<?= site_url('transaksi') ?>" class="nav-link <?= ($active_menu ?? '') == 'transaksi' ? 'active' : '' ?>" data-page="transaksi" id="navTrans">
                <i class="fas fa-exchange-alt"></i>
                <span>Transaksi</span>
            </a>
            <a href="<?= site_url('hutang') ?>" class="nav-link <?= ($active_menu ?? '') == 'hutang' ? 'active' : '' ?>" data-page="hutang">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Hutang/Piutang</span>
            </a>
            <a href="<?= site_url('laporan') ?>" class="nav-link <?= ($active_menu ?? '') == 'laporan' ? 'active' : '' ?>" data-page="laporan">
                <i class="fas fa-chart-pie"></i>
                <span>Laporan</span>
            </a>
            <!-- Group: Kasbon -->
            <?php if (!empty($can_access_loan ?? false) || !empty($can_access_loan_report ?? false)): ?>
            <div class="sidebar-group">
                <button type="button" class="sidebar-group-toggle" data-target="groupKasbon">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Kasbon</span>
                    <i class="fas fa-chevron-down sidebar-group-arrow"></i>
                </button>
                <div class="sidebar-group-collapse" id="groupKasbon">
                    <?php if (!empty($can_access_loan ?? false)): ?>
                    <a href="<?= site_url('loan') ?>" class="nav-link <?= ($active_menu ?? '') == 'loan' ? 'active' : '' ?>" data-page="loan">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Kasbon</span>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($can_access_loan_report ?? false)): ?>
                    <a href="<?= site_url('loan/laporan') ?>" class="nav-link <?= ($active_menu ?? '') == 'loan_report' ? 'active' : '' ?>" data-page="loan_report">
                        <i class="fas fa-chart-line"></i>
                        <span>Lap. Kasbon</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Group: Dompet & Target -->
            <div class="sidebar-group">
                <button type="button" class="sidebar-group-toggle" data-target="groupDompet">
                    <i class="fas fa-wallet"></i>
                    <span>Master</span>
                    <i class="fas fa-chevron-down sidebar-group-arrow"></i>
                </button>
                <div class="sidebar-group-collapse" id="groupDompet">
                    <a href="<?= site_url('dompet') ?>" class="nav-link <?= ($active_menu ?? '') == 'dompet' ? 'active' : '' ?>" data-page="dompet">
                        <i class="fas fa-wallet"></i>
                        <span>Dompet</span>
                    </a>
                    <a href="<?= site_url('target') ?>" class="nav-link <?= ($active_menu ?? '') == 'target' ? 'active' : '' ?>" data-page="target">
                        <i class="fas fa-bullseye"></i>
                        <span>Target</span>
                    </a>
                    <a href="<?= site_url('kategori') ?>" class="nav-link <?= ($active_menu ?? '') == 'kategori' ? 'active' : '' ?>" data-page="kategori">
                        <i class="fas fa-tags"></i>
                        <span>Kategori</span>
                    </a>
                    
                    <a href="<?= site_url('budget') ?>" class="nav-link <?= ($active_menu ?? '') == 'budget' ? 'active' : '' ?>" data-page="budget">
                        <i class="fas fa-chart-line"></i>
                        <span>Budget</span>
                    </a>

                </div>
            </div>


            <!-- Group: Pengaturan -->
            <div class="sidebar-group">
                <button type="button" class="sidebar-group-toggle" data-target="groupSetting">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                    <i class="fas fa-chevron-down sidebar-group-arrow"></i>
                </button>
                <div class="sidebar-group-collapse" id="groupSetting">
                    <a href="<?= site_url('pengingat') ?>" class="nav-link <?= ($active_menu ?? '') == 'pengingat' ? 'active' : '' ?>" data-page="pengingat">
                        <i class="fas fa-bell"></i>
                        <span>Pengingat</span>
                    </a>
                    <a href="<?= site_url('setting') ?>" class="nav-link <?= ($active_menu ?? '') == 'setting' ? 'active' : '' ?>" data-page="setting">
                        <i class="fas fa-sliders-h"></i>
                        <span>Setting</span>
                    </a>
                    <a href="<?= site_url('sync') ?>" class="nav-link <?= ($active_menu ?? '') == 'sync' ? 'active' : '' ?>" data-page="sync">
                        <i class="fas fa-sync"></i>
                        <span>Sinkronisasi</span>
                    </a>
                    <?php if ($this->session->userdata('role') === 'admin'): ?>
                    <a href="<?= site_url('pengaturan/user') ?>" class="nav-link <?= ($active_menu ?? '') == 'users' ? 'active' : '' ?>" data-page="users">
                        <i class="fas fa-users-cog"></i>
                        <span>User</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <a href="<?= site_url('profile') ?>" class="nav-link <?= ($active_menu ?? '') == 'profile' ? 'active' : '' ?>" data-page="profile">
                <i class="fas fa-user-circle"></i>
                <span>Profil</span>
            </a>
            <a href="<?= site_url('auth/logout') ?>" class="nav-link logout-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar</span>
            </a>
            <div class="sidebar-wallet-cards" id="sidebarWalletCards" data-loan="<?= $can_access_loan_report===true?"1":"0" ?>"></div>
        </nav>
        
        <div class="sidebar-footer">
            <small>v<?= config_item('appVersion') ?> | <?= $this->session->userdata('fullname') ?></small>
            <button type="button" class="sidebar-wallet-toggle" id="desktopWalletToggle" aria-label="Tampilkan dompet" aria-pressed="false">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </aside>
    
    <!-- OVERLAY UNTUK MOBILE -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- Header Mobile -->
        <div class="mobile-header d-lg-none">
            <button class="mobile-menu-btn sidebar-toggle-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <h5 id="mobilePageTitle"><?= $title ?? 'Dashboard' ?></h5>
            <div class="mobile-header-actions">
                <button type="button" class="mobile-wallet-toggle wallet-summary-toggle" id="mobileWalletToggle" aria-label="Tampilkan ringkasan dompet" aria-pressed="false">
                    <i class="fas fa-eye"></i>
                </button>
                <a href="<?= site_url('auth/logout') ?>" class="mobile-logout-btn" aria-label="Keluar">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
        
        <div class="content-wrapper">
