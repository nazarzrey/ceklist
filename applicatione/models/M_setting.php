<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_setting extends CI_Model {

    private $table = 'jurnal_new_settings';
    private static $table_checked = false;
    private static $settings_cache = [];

    public function __construct() {
        parent::__construct();
        $this->ensure_table();
    }

    private function ensure_table() {
        if (self::$table_checked) {
            return;
        }

        $this->load->dbforge();

        if (!$this->db->table_exists($this->table)) {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => FALSE
                ],
                'background_color' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => '#f5f7fb',
                    'null' => FALSE
                ],
                'text_color' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => '#1a2a3a',
                    'null' => FALSE
                ],
                'mobile_menu_json' => [
                    'type' => 'LONGTEXT',
                    'null' => TRUE
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => FALSE
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => FALSE
                ]
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('user_id');
            $this->dbforge->create_table($this->table, TRUE);
        }

        self::$table_checked = true;
    }

    public function get_available_mobile_menus() {
        $menus = [
            [
                'key' => 'dashboard',
                'label' => 'Beranda',
                'url' => 'dashboard',
                'icon' => 'fas fa-home',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => false
            ],
            [
                'key' => 'transaksi',
                'label' => 'Transaksi',
                'url' => 'transaksi',
                'icon' => 'fas fa-exchange-alt',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'hutang',
                'label' => 'Hutang',
                'url' => 'hutang',
                'icon' => 'fas fa-file-invoice-dollar',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'laporan',
                'label' => 'Laporan',
                'url' => 'laporan',
                'icon' => 'fas fa-chart-pie',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'dompet',
                'label' => 'Dompet',
                'url' => 'dompet',
                'icon' => 'fas fa-wallet',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'target',
                'label' => 'Target',
                'url' => 'target',
                'icon' => 'fas fa-bullseye',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'budget',
                'label' => 'Budget',
                'url' => 'budget',
                'icon' => 'fas fa-chart-line',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'loan',
                'label' => 'Kasbon',
                'url' => 'loan',
                'icon' => 'fas fa-hand-holding-usd',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'loan_report',
                'label' => 'Lap. Kasbon',
                'url' => 'loan/laporan',
                'icon' => 'fas fa-chart-line',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'kategori',
                'label' => 'Kategori',
                'url' => 'kategori',
                'icon' => 'fas fa-tags',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'pengingat',
                'label' => 'Pengingat',
                'url' => 'pengingat',
                'icon' => 'fas fa-bell',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'profile',
                'label' => 'Profil',
                'url' => 'profile',
                'icon' => 'fas fa-user-circle',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'setting',
                'label' => 'Setting',
                'url' => 'setting',
                'icon' => 'fas fa-sliders-h',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => false
            ],
            [
                'key' => 'sync',
                'label' => 'Sinkronisasi',
                'url' => 'sync',
                'icon' => 'fas fa-sync',
                'admin_only' => false,
                'default_visible' => true,
                'can_edit' => true
            ],
            [
                'key' => 'users',
                'label' => 'User',
                'url' => 'users',
                'icon' => 'fas fa-users-cog',
                'admin_only' => true,
                'default_visible' => true,
                'can_edit' => true
            ]
        ];

        return $menus;
    }

    public function get_user_setting($user_id, $role = 'user') {
        $cache_key = $user_id . ':' . $role;
        if (isset(self::$settings_cache[$cache_key])) {
            return self::$settings_cache[$cache_key];
        }

        $row = $this->db->where('user_id', (int) $user_id)->get($this->table)->row_array();
        $default_bg = '#f5f7fb';
        $default_txt = '#1a2a3a';

        $setting = [
            'background_color' => $row['background_color'] ?? $default_bg,
            'text_color' => $row['text_color'] ?? $default_txt,
            'mobile_menu' => $this->build_mobile_menu($row['mobile_menu_json'] ?? null, $role)
        ];

        self::$settings_cache[$cache_key] = $setting;
        return $setting;
    }

    private function build_mobile_menu($menu_json, $role = 'user') {
        $available = [];
        foreach ($this->get_available_mobile_menus() as $menu) {
            if ($menu['admin_only'] && $role !== 'admin') {
                continue;
            }
            $available[$menu['key']] = $menu;
        }

        $saved = json_decode((string) $menu_json, true);
        if (!is_array($saved)) {
            $saved = [];
        }

        $ordered = [];
        foreach ($saved as $item) {
            $key = $item['key'] ?? '';
            if ($key === '' || !isset($available[$key])) {
                continue;
            }

            $menu = $available[$key];
            $menu['visible'] = !isset($item['visible']) || (int) $item['visible'] === 1;
            if (isset($menu['can_edit']) && !$menu['can_edit']) {
                $menu['visible'] = true;
            }
            $menu['sort_order'] = isset($item['sort_order']) ? (int) $item['sort_order'] : 999;
            $ordered[$key] = $menu;
        }

        $sort_order = count($ordered) + 1;
        foreach ($available as $key => $menu) {
            if (isset($ordered[$key])) {
                continue;
            }

            $menu['visible'] = !empty($menu['default_visible']);
            $menu['sort_order'] = $sort_order++;
            $ordered[$key] = $menu;
        }

        uasort($ordered, function($a, $b) {
            if ($a['sort_order'] === $b['sort_order']) {
                return strcmp($a['label'], $b['label']);
            }
            return $a['sort_order'] <=> $b['sort_order'];
        });

        return array_values($ordered);
    }

    public function save_user_setting($user_id, $role, $background_color, $text_color, $menu_items) {
        $normalized = $this->normalize_menu_input($menu_items, $role);
        $now = date('Y-m-d H:i:s');
        $data = [
            'background_color' => $background_color,
            'text_color' => $text_color,
            'mobile_menu_json' => json_encode($normalized),
            'updated_at' => $now
        ];

        $existing = $this->db->where('user_id', (int) $user_id)->get($this->table)->row();
        if ($existing) {
            $this->db->where('user_id', (int) $user_id)->update($this->table, $data);
        } else {
            $data['user_id'] = (int) $user_id;
            $data['created_at'] = $now;
            $this->db->insert($this->table, $data);
        }

        unset(self::$settings_cache[$user_id . ':' . $role]);
        return true;
    }

    private function normalize_menu_input($menu_items, $role) {
        $available = [];
        foreach ($this->get_available_mobile_menus() as $menu) {
            if ($menu['admin_only'] && $role !== 'admin') {
                continue;
            }
            $available[$menu['key']] = true;
        }

        $normalized = [];
        if (!is_array($menu_items)) {
            $menu_items = [];
        }

        foreach ($menu_items as $index => $item) {
            $key = trim($item['key'] ?? '');
            if ($key === '' || !isset($available[$key])) {
                continue;
            }

            $editable = $this->is_menu_editable($key, $role);

            $normalized[] = [
                'key' => $key,
                'visible' => $editable ? (!empty($item['visible']) ? 1 : 0) : 1,
                'sort_order' => isset($item['sort_order']) ? (int) $item['sort_order'] : ((int) $index + 1)
            ];
        }

        return $normalized;
    }

    private function is_menu_editable($key, $role) {
        foreach ($this->get_available_mobile_menus() as $menu) {
            if ($menu['key'] !== $key) {
                continue;
            }

            if ($menu['admin_only'] && $role !== 'admin') {
                return false;
            }

            return !empty($menu['can_edit']);
        }

        return false;
    }
}
