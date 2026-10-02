        </div> <!-- closing content-wrapper -->
        
        <?php
        $CI =& get_instance();
        $CI->load->model('M_setting');
        
        $user_id = (int) $this->session->userdata('user_id');
        $role = $this->session->userdata('role') ?: 'user';
        $has_partner = !empty($has_partner ?? false);
        $has_loan_relation = !empty($has_loan_relation ?? false);
        $can_access_loan = !empty($can_access_loan ?? false);
        $can_access_loan_report = !empty($can_access_loan_report ?? false);
        
        $mobile_setting = $CI->M_setting->get_user_setting($user_id, $role);
        $mobile_menus = array_values(array_filter($mobile_setting['mobile_menu'] ?? [], function($item) {
            return !empty($item['visible']);
        }));
        
        // Filter menu berdasarkan has_partner
        $filtered_menus = array_filter($mobile_menus, function($menu) use ($has_partner, $can_access_loan, $can_access_loan_report) {
            // Sembunyikan menu kasbon jika tidak punya partner
            if ($menu['key'] == 'loan' && !$can_access_loan) {
                return false;
            }
            if ($menu['key'] == 'loan_report' && !$can_access_loan_report) {
                return false;
            }
            return true;
        });
        
        $direct_menus = array_slice($filtered_menus, 0, 4);
        $more_menus = array_slice($filtered_menus, 4);
        $more_active = in_array(($active_menu ?? ''), array_column($more_menus, 'key'));
        ?>
        
        <!-- BOTTOM NAVIGATION (Mobile only) -->
        <div class="mobile-more-panel d-lg-none" id="mobileMorePanel" aria-hidden="true">
            <div class="mobile-more-handle"></div>
            <div class="mobile-more-grid">
                <?php foreach ($more_menus as $menu): ?>
                <a href="<?= site_url($menu['url']) ?>" class="more-item <?= ($active_menu ?? '') == $menu['key'] ? 'active' : '' ?>">
                    <i class="<?= htmlspecialchars($menu['icon']) ?>"></i>
                    <span><?= htmlspecialchars($menu['label']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="mobile-more-backdrop d-lg-none" id="mobileMoreBackdrop"></div>
        
        <!-- PWA Install Button -->
        <button type="button" class="pwa-install-btn" id="pwaInstallBtn">
            <i class="fas fa-download"></i>
            <span>Install</span>
        </button>

        <!-- Bottom Navigation Bar -->
        <div class="bottom-nav d-lg-none">
            <?php foreach ($direct_menus as $menu): ?>
            <a href="<?= site_url($menu['url']) ?>" class="nav-item <?= ($active_menu ?? '') == $menu['key'] ? 'active' : '' ?>">
                <i class="<?= htmlspecialchars($menu['icon']) ?>"></i>
                <span><?= htmlspecialchars($menu['label']) ?></span>
            </a>
            <?php endforeach; ?>
            
            <?php if (count($more_menus) > 0): ?>
            <button type="button" class="nav-item more-toggle <?= $more_active ? 'active' : '' ?>" id="mobileMoreBtn" aria-label="Menu lainnya">
                <i class="fas fa-ellipsis-h"></i>
                <span>More</span>
            </button>
            <?php endif; ?>
        </div>
        
        
        <!-- Mobile Wallet Summary -->
        <div class="mobile-wallet-summary" id="mobileWalletSummary" aria-hidden="true" data-has-partner="<?= ($has_partner || $has_loan_relation) ? '1' : '0' ?>">
            <div class="mobile-wallet-summary-grid" id="mobileWalletSummaryGrid">
                <div class="mobile-wallet-summary-empty">Memuat dompet...</div>
            </div>
        </div>
    </main>
</div>

<style>
/* Mobile More Panel */
.mobile-more-panel {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-radius: 24px 24px 0 0;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    z-index: 1050;
    transform: translateY(100%);
    transition: transform 0.3s ease;
    visibility: hidden;
    max-height: 80vh;
    overflow-y: auto;
}

.mobile-more-panel.show {
    transform: translateY(0);
    visibility: visible;
}

.mobile-more-handle {
    width: 40px;
    height: 4px;
    background: #cbd5e1;
    border-radius: 2px;
    margin: 12px auto;
}

.mobile-more-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    padding: 8px 20px 24px;
}

.more-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    color: #64748b;
    padding: 8px;
    border-radius: 12px;
    transition: all 0.2s;
}

.more-item i {
    font-size: 1.4rem;
}

.more-item span {
    font-size: 0.7rem;
}

.more-item.active {
    color: #2d3e50;
    background: #f1f5f9;
}

.mobile-more-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.mobile-more-backdrop.show {
    visibility: visible;
    opacity: 1;
}

/* PWA Install Button */
.pwa-install-btn {
    position: fixed;
    bottom: 80px;
    right: 16px;
    background: #2d3e50;
    color: white;
    border: none;
    border-radius: 40px;
    padding: 10px 18px;
    font-size: 0.8rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.pwa-install-btn:hover {
    background: #1a2a3a;
    transform: translateY(-2px);
}

/* Bottom Navigation */
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 8px 12px 12px;
    border-top: 1px solid #eef2f6;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    z-index: 100;
}

.bottom-nav .nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    color: #94a3b8;
    font-size: 0.7rem;
    transition: all 0.2s;
    background: #f1f5f9;
    border: none;
    border-radius: 10px;
    padding: 6px 10px;
}

.bottom-nav .nav-item i {
    font-size: 1.3rem;
}

.bottom-nav .nav-item.active {
    color: #1a56db;
    background: #eef4ff;
    font-weight: 600;
}

.bottom-nav .nav-item span {
    font-size: 0.65rem;
}

/* Mobile Header Actions */
.mobile-header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.mobile-wallet-toggle {
    background: none;
    border: none;
    color: #64748b;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s;
}

.mobile-wallet-toggle:hover,
.mobile-wallet-toggle:active {
    background: #f1f5f9;
    color: #2d3e50;
}

.mobile-logout-btn {
    color: #ef4444;
    font-size: 1.2rem;
    text-decoration: none;
    padding: 8px;
    border-radius: 50%;
}

.mobile-logout-btn:hover {
    background: #fef2f2;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .mobile-more-grid {
        gap: 12px;
        padding: 8px 16px 20px;
    }
    
    .more-item i {
        font-size: 1.2rem;
    }
    
    .wallet-summary-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
    }
    
    .wallet-balance {
        font-size: 0.8rem;
    }
}
</style>
