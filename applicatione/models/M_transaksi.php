<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_transaksi extends CI_Model {

    private $table = 'jurnal_new_transactions';
    private $table_wallets = 'jurnal_new_wallets';
    private $table_categories = 'jurnal_new_categories';
    private static $columns_checked = false;

    public function __construct() {
        parent::__construct();
        if (!self::$columns_checked) {
            $this->ensure_transfer_columns();
            self::$columns_checked = true;
        }
    }

    private function ensure_transfer_columns() {
        $this->load->dbforge();

        $columns = [];
        if (!$this->db->field_exists('wallet_tujuan_id', $this->table)) {
            $columns['wallet_tujuan_id'] = [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ];
        }
        if (!$this->db->field_exists('fee', $this->table)) {
            $columns['fee'] = [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
                'null' => FALSE
            ];
        }

        if ($columns) {
            $this->dbforge->add_column($this->table, $columns);
        }

        $tipe_column = $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'tipe'")->row();
        if ($tipe_column && stripos((string) $tipe_column->Type, 'transfer') === false) {
            $this->db->query("ALTER TABLE `{$this->table}` MODIFY `tipe` VARCHAR(20) NOT NULL");
        }
    }

    public function get_all($user_id, $limit = null, $offset = null) {
        $this->db->select('t.*, w.nama_wallet, wt.nama_wallet as nama_wallet_tujuan, c.nama_kategori, c.icon as kategori_icon')
            ->from($this->table . ' t')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id')
            ->join($this->table_wallets . ' wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id)
            ->order_by('t.tanggal', 'DESC')
            ->order_by('t.id', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function get_total_nominal($user_id, $exclude_id = null) {
        $this->db->select('SUM(IFNULL(nominal,0) + IFNULL(fee,0)) AS total', false)
            ->where('user_id', (int) $user_id);
        if ($exclude_id) $this->db->where('id !=', (int) $exclude_id);
        $row = $this->db->get($this->table)->row();
        return (float) ($row->total ?? 0);
    }

    private function apply_search($keyword) {
        if ($keyword !== null && $keyword !== '') {
            $this->db->group_start()
                ->like('t.deskripsi', $keyword)
                ->or_like('c.nama_kategori', $keyword)
                ->or_like('w.nama_wallet', $keyword)
                ->or_like('wt.nama_wallet', $keyword)
                ->or_like('t.tipe', $keyword)
            ->group_end();
        }
    }

    public function get_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $limit = null, $offset = null, $keyword = '', $kategori_id = null, $wallet_id = null, $tipe = null) {
        $this->db->select('t.*, w.nama_wallet, wt.nama_wallet as nama_wallet_tujuan, c.nama_kategori, c.icon as kategori_icon')
            ->from($this->table . ' t')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id')
            ->join($this->table_wallets . ' wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id)
            ->where('t.tanggal >=', $tanggal_mulai)
            ->where('t.tanggal <=', $tanggal_akhir);

        if ($tipe) {
            $this->db->where('t.tipe', $tipe);
        }
        if ($kategori_id) {
            $this->db->where('t.category_id', $kategori_id);
        }
        if ($wallet_id) {
            $this->db->where('t.wallet_id', $wallet_id);
        }

        $this->apply_search($keyword);

        $this->db->order_by('t.tanggal', 'DESC')
            ->order_by('t.id', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function count_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $keyword = '', $kategori_id = null, $wallet_id = null, $tipe = null) {
        $this->db->from($this->table . ' t')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id')
            ->join($this->table_wallets . ' wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id)
            ->where('t.tanggal >=', $tanggal_mulai)
            ->where('t.tanggal <=', $tanggal_akhir);

        if ($tipe) {
            $this->db->where('t.tipe', $tipe);
        }
        if ($kategori_id) {
            $this->db->where('t.category_id', $kategori_id);
        }
        if ($wallet_id) {
            $this->db->where('t.wallet_id', $wallet_id);
        }

        $this->apply_search($keyword);

        return $this->db->count_all_results();
    }

    public function get_by_id($id, $user_id) {
        return $this->db->select('t.*, w.nama_wallet, wt.nama_wallet as nama_wallet_tujuan, c.nama_kategori')
            ->from($this->table . ' t')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id')
            ->join($this->table_wallets . ' wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where('t.id', $id)
            ->where('t.user_id', $user_id)
            ->get()
            ->row();
    }

    public function get_recent($user_id, $limit = 5) {
        return $this->db->select('t.*, w.nama_wallet, wt.nama_wallet as nama_wallet_tujuan, c.nama_kategori')
            ->from($this->table . ' t')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id')
            ->join($this->table_wallets . ' wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id)
            ->order_by('t.tanggal', 'DESC')
            ->order_by('t.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    public function get_total_by_tipe_bulan_ini($user_id, $tipe) {
        $bulan_ini = date('Y-m-01');
        $bulan_depan = date('Y-m-01', strtotime('+1 month'));

        if ($tipe === 'pengeluaran') {
            $result = $this->db->select('IFNULL(SUM(CASE WHEN tipe = "pengeluaran" THEN nominal WHEN tipe = "transfer" THEN fee ELSE 0 END),0) as total', false)
                ->where('user_id', $user_id)
                ->where('tanggal >=', $bulan_ini)
                ->where('tanggal <', $bulan_depan)
                ->get($this->table)
                ->row();
            return $result->total ?? 0;
        }

        $result = $this->db->select('SUM(IFNULL(nominal,0)) as nominal', false)
            ->where('user_id', $user_id)
            ->where('tipe', $tipe)
            ->where('tanggal >=', $bulan_ini)
            ->where('tanggal <', $bulan_depan)
            ->get($this->table)
            ->row();

        return $result->nominal ?? 0;
    }

    public function get_total_by_tipe_hari_ini($user_id, $tipe) {
        if ($tipe === 'pengeluaran') {
            $result = $this->db->select('IFNULL(SUM(CASE WHEN tipe = "pengeluaran" THEN nominal WHEN tipe = "transfer" THEN fee ELSE 0 END),0) as total', false)
                ->where('user_id', $user_id)
                ->where('tanggal', date('Y-m-d'))
                ->get($this->table)
                ->row();

            return $result->total ?? 0;
        }

        $result = $this->db->select('SUM(IFNULL(nominal,0)) as nominal', false)
            ->where('user_id', $user_id)
            ->where('tipe', $tipe)
            ->where('tanggal', date('Y-m-d'))
            ->get($this->table)
            ->row();

        return $result->nominal ?? 0;
    }

    private function _get_numpang_kategori_ids() {
        $user_id = $this->session->userdata('user_id');
        $rows = $this->db->select('id')
            ->from('jurnal_new_categories')
            ->where('user_id', $user_id)
            ->group_start()
                ->where('nama_kategori', 'numpang')
                ->or_where('nama_kategori', 'numpang tf')
            ->group_end()
            ->get()
            ->result();
        return array_map(function ($r) { return (int) $r->id; }, $rows);
    }

    public function get_numpang_kategori_ids() {
        return $this->_get_numpang_kategori_ids();
    }

    public function get_totals_hari_ini($user_id) {
        $excluded = $this->_get_numpang_kategori_ids();
        $this->db->select("
            IFNULL(SUM(CASE WHEN tipe = 'pemasukan' THEN nominal ELSE 0 END),0) as pemasukan,
            IFNULL(SUM(CASE WHEN tipe = 'pengeluaran' THEN nominal WHEN tipe = 'transfer' THEN fee ELSE 0 END),0) as pengeluaran
        ", false)
            ->where('user_id', $user_id)
            ->where('tanggal', date('Y-m-d'));
        if (!empty($excluded)) {
            $this->db->where('category_id NOT IN (' . implode(',', $excluded) . ')', null, false);
        }
        return $this->db->get($this->table)->row();
    }

    public function get_totals_bulan_ini($user_id) {
        $excluded = $this->_get_numpang_kategori_ids();
        $this->db->select("
            IFNULL(SUM(CASE WHEN tipe = 'pemasukan' THEN nominal ELSE 0 END),0) as pemasukan,
            IFNULL(SUM(CASE WHEN tipe = 'pengeluaran' THEN nominal WHEN tipe = 'transfer' THEN fee ELSE 0 END),0) as pengeluaran
        ", false)
            ->where('user_id', $user_id)
            ->where('tanggal >=', date('Y-m-01'))
            ->where('tanggal <', date('Y-m-01', strtotime('+1 month')));
        if (!empty($excluded)) {
            $this->db->where('category_id NOT IN (' . implode(',', $excluded) . ')', null, false);
        }
        return $this->db->get($this->table)->row();
    }

    public function suggest_deskripsi($user_id, $keyword, $limit = 8, $tipe = null) {
        if ($tipe && !in_array($tipe, ['pemasukan', 'pengeluaran', 'transfer'])) {
            $tipe = null;
        }

        $keyword = trim(preg_replace('/\s+/', ' ', (string) $keyword));
        $tokens = array_values(array_filter(array_unique(preg_split('/\s+/', $keyword)), function ($token) {
            return mb_strlen($token) >= 2;
        }));
        $wildcardKeyword = implode('%', array_map(function ($token) {
            $token = mb_strtolower($token);
            $token = $this->db->escape_like_str($token);
            return str_replace("'", "''", $token);
        }, $tokens));

        $this->db->select('t.id, t.deskripsi, t.wallet_id, t.wallet_tujuan_id, t.category_id, t.tipe, t.fee, w.nama_wallet, wt.nama_wallet as nama_wallet_tujuan, c.nama_kategori')
            ->from($this->table . ' t')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id')
            ->join($this->table_wallets . ' wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id);

        if ($tipe) {
            $this->db->where('t.tipe', $tipe);
        }

        $this->db->group_start();
        if ($wildcardKeyword !== '') {
            $this->db->where("LOWER(t.deskripsi) LIKE '%{$wildcardKeyword}%'", null, false);
            $this->db->or_where("LOWER(c.nama_kategori) LIKE '%{$wildcardKeyword}%'", null, false);
            $this->db->or_where("LOWER(w.nama_wallet) LIKE '%{$wildcardKeyword}%'", null, false);
            $this->db->or_where("LOWER(wt.nama_wallet) LIKE '%{$wildcardKeyword}%'", null, false);

            foreach ($tokens as $token) {
                $this->db->or_like('t.deskripsi', $token);
                $this->db->or_like('c.nama_kategori', $token);
                $this->db->or_like('w.nama_wallet', $token);
                $this->db->or_like('wt.nama_wallet', $token);
            }
        }
        $this->db->group_end();

        $rows = $this->db->group_by(['t.id', 't.deskripsi', 't.wallet_id', 't.wallet_tujuan_id', 't.category_id', 't.tipe', 't.fee', 'w.nama_wallet', 'wt.nama_wallet', 'c.nama_kategori'])
            ->order_by('t.id', 'DESC')
            ->get()
            ->result();

        if (!$rows) {
            return [];
        }

        $keywordLower = mb_strtolower(implode(' ', $tokens));
        $ranked = array_map(function ($row) use ($tokens, $keywordLower) {
            $haystacks = [
                mb_strtolower((string) ($row->deskripsi ?? '')),
                mb_strtolower((string) ($row->nama_kategori ?? '')),
                mb_strtolower((string) ($row->nama_wallet ?? '')),
                mb_strtolower((string) ($row->nama_wallet_tujuan ?? '')),
            ];

            $score = 0;
            $allText = implode(' ', $haystacks);

            if ($keywordLower !== '' && strpos($allText, $keywordLower) !== false) {
                $score += 60;
            }

            foreach ($tokens as $token) {
                foreach ($haystacks as $field) {
                    if ($token !== '' && strpos($field, mb_strtolower($token)) !== false) {
                        $score += 12;
                    }
                }
            }

            if (!empty($row->deskripsi) && strpos($keywordLower, mb_strtolower((string) $row->deskripsi)) !== false) {
                $score += 8;
            }

            $row->relevance_score = $score;
            return $row;
        }, $rows);

        usort($ranked, function ($a, $b) {
            if ($a->relevance_score === $b->relevance_score) {
                return ((int) $b->id) <=> ((int) $a->id);
            }

            return $b->relevance_score <=> $a->relevance_score;
        });

        return array_slice($ranked, 0, $limit);
    }

    public function get_transfer_category_id() {
        $existing = $this->db->where('user_id', 0)
            ->where('nama_kategori', 'Transfer')
            ->get($this->table_categories)
            ->row();

        if ($existing) {
            if (isset($existing->is_active) && (int) $existing->is_active !== 1) {
                $this->db->where('id', $existing->id)->update($this->table_categories, ['is_active' => 1]);
            }
            return (int) $existing->id;
        }

        $data = [
            'user_id' => 0,
            'nama_kategori' => 'Transfer',
            'tipe' => 'pengeluaran',
            'icon' => 'fa-right-left',
            'warna' => '#0d6efd',
            'is_default' => 1,
            'is_active' => 1
        ];
        $this->db->insert($this->table_categories, $data);
        return (int) $this->db->insert_id();
    }

    public function get_wallet_balance($wallet_id, $user_id) {
        $wallet = $this->db->select('saldo_awal')
            ->from($this->table_wallets)
            ->where('id', $wallet_id)
            ->where('user_id', $user_id)
            ->get()
            ->row();

        return $wallet ? (float) $wallet->saldo_awal : 0.0;
    }

    public function get_transaction_wallet_effects($data) {
        $tipe = $data->tipe ?? '';
        $nominal = (float) ($data->nominal ?? 0);
        $fee = (float) ($data->fee ?? 0);
        $wallet_id = (int) ($data->wallet_id ?? 0);
        $wallet_tujuan_id = (int) ($data->wallet_tujuan_id ?? 0);
        $effects = [];

        if ($tipe === 'transfer') {
            if ($wallet_id > 0) {
                $effects[$wallet_id] = ($effects[$wallet_id] ?? 0) - ($nominal + $fee);
            }
            if ($wallet_tujuan_id > 0) {
                $effects[$wallet_tujuan_id] = ($effects[$wallet_tujuan_id] ?? 0) + $nominal;
            }
            return $effects;
        }

        if ($wallet_id > 0) {
            $effects[$wallet_id] = ($effects[$wallet_id] ?? 0) + ($tipe === 'pemasukan' ? $nominal : -$nominal);
        }

        return $effects;
    }

    private function get_loan_payment_category_id() {
        $existing = $this->db->where('user_id', 0)
            ->where('nama_kategori', 'Pembayaran Kasbon')
            ->get($this->table_categories)
            ->row();

        return $existing ? (int) $existing->id : 0;
    }

    private function get_loan_payment_counterpart($transaction) {
        $payment_category_id = $this->get_loan_payment_category_id();
        if (
            !$transaction ||
            (int) ($transaction->is_loan ?? 0) !== 1 ||
            (int) ($transaction->category_id ?? 0) !== $payment_category_id ||
            strpos((string) ($transaction->deskripsi ?? ''), 'Pembayaran kasbon') !== 0
        ) {
            return null;
        }

        $opposite_type = ($transaction->tipe === 'pengeluaran') ? 'pemasukan' : 'pengeluaran';
        $this->db->select('*')
            ->from($this->table)
            ->where('id !=', (int) $transaction->id)
            ->where('nominal', $transaction->nominal)
            ->where('tanggal', $transaction->tanggal)
            ->where('category_id', $payment_category_id)
            ->where('is_loan', 1)
            ->where('tipe', $opposite_type);

        if (!empty($transaction->partner_id)) {
            $this->db->where('partner_id', $transaction->partner_id);
        } else {
            $this->db->where('partner_id IS NULL', null, false);
        }

        return $this->db->order_by('id', $transaction->tipe === 'pengeluaran' ? 'ASC' : 'DESC')
            ->limit(1)
            ->get()
            ->row();
    }

    public function insert($data) {
        if (empty($data['wallet_id'])) {
            throw new Exception('wallet_id tidak boleh kosong');
        }

        $data['fee'] = isset($data['fee']) ? (float) $data['fee'] : 0;

        if (($data['tipe'] ?? '') === 'transfer') {
            if (empty($data['wallet_tujuan_id'])) {
                throw new Exception('wallet_tujuan_id tidak boleh kosong');
            }
            $data['category_id'] = $data['category_id'] ?: $this->get_transfer_category_id();
        } elseif (empty($data['category_id'])) {
            throw new Exception('category_id tidak boleh kosong');
        }
        
        $data['is_loan'] = isset($data['is_loan']) && $data['is_loan'] == 1 ? 1 : 0;
        if ($data['is_loan'] == 1 && !empty($data['partner_id'])) {
            // Pastikan partner_id adalah ID dari tabel partners
            $data['partner_id'] = $data['partner_id'];
        } else {
            $data['partner_id'] = null;
        }
        
        $this->db->insert($this->table, $data);
        $insert_id = $this->db->insert_id();
        
        $this->apply_wallet_effects((object) $data, false);
        
        return $insert_id;
    }

    public function update($id, $data, $user_id) {
        $old = $this->get_by_id($id, $user_id);
        $data['fee'] = isset($data['fee']) ? (float) $data['fee'] : 0;
        if (($data['tipe'] ?? '') === 'transfer') {
            $data['category_id'] = $data['category_id'] ?: $this->get_transfer_category_id();
        }

        if (isset($data['is_loan'])) {
            $data['is_loan'] = $data['is_loan'] == 1 ? 1 : 0;
        }
        if (($data['is_loan'] ?? 0) != 1 || (isset($data['partner_id']) && empty($data['partner_id']))) {
            $data['partner_id'] = null;
        }
        if ($old) {
            $this->apply_wallet_effects($old, true);
        }
        
        $this->db->where('id', $id)->where('user_id', $user_id)->update($this->table, $data);
        $this->apply_wallet_effects((object) $data, false);
        return $this->db->affected_rows();
    }

    public function delete($id, $user_id) {
        $transaction = $this->get_by_id($id, $user_id);
        
        if ($transaction) {
            $counterpart = $this->get_loan_payment_counterpart($transaction);

            $this->db->trans_start();
            $this->apply_wallet_effects($transaction, true);
            
            $this->db->where('id', $id)->where('user_id', $user_id)->delete($this->table);
            $affected = $this->db->affected_rows();

            if ($counterpart) {
                $this->apply_wallet_effects($counterpart, true);
                $this->db->where('id', $counterpart->id)->delete($this->table);
            }

            $this->db->trans_complete();
            return $this->db->trans_status() ? $affected : 0;
        }
        
        return 0;
    }

    private function update_wallet_balance($wallet_id, $delta) {
        if (!$wallet_id || (float) $delta === 0.0) {
            return;
        }

        $operator = ((float) $delta >= 0) ? '+' : '-';
        $amount = abs((float) $delta);

        $this->db->set('saldo_awal', "saldo_awal $operator $amount", FALSE)
            ->where('id', $wallet_id)
            ->update($this->table_wallets);
    }

    private function _is_virtual_wallet($wallet_id) {
        if (!$wallet_id) return false;
        $row = $this->db->select('is_virtual')
            ->from($this->table_wallets)
            ->where('id', $wallet_id)
            ->get()
            ->row();
        return $row && (int) $row->is_virtual === 1;
    }

    private function apply_wallet_effects($data, $reverse = false) {
        $tipe = $data->tipe ?? '';
        $nominal = (float) ($data->nominal ?? 0);
        $fee = (float) ($data->fee ?? 0);
        $factor = $reverse ? -1 : 1;
        $src_virtual = $this->_is_virtual_wallet($data->wallet_id ?? null);
        $dst_virtual = $this->_is_virtual_wallet($data->wallet_tujuan_id ?? null);

        if ($tipe === 'transfer') {
            $src_delta = -1 * $factor * ($nominal + $fee);
            $dst_delta = 1 * $factor * $nominal;
            if ($src_virtual && !$dst_virtual) {
                $this->update_wallet_balance($data->wallet_id ?? null, $src_delta);
            } elseif (!$src_virtual && $dst_virtual) {
                $this->update_wallet_balance($data->wallet_tujuan_id ?? null, $dst_delta);
            } else {
                $this->update_wallet_balance($data->wallet_id ?? null, $src_delta);
                $this->update_wallet_balance($data->wallet_tujuan_id ?? null, $dst_delta);
            }
            return;
        }

        if ($src_virtual) return;
        $delta = ($tipe === 'pemasukan' ? $nominal : -1 * $nominal) * $factor;
        $this->update_wallet_balance($data->wallet_id ?? null, $delta);
    }

    public function get_laporan($user_id, $tanggal_mulai, $tanggal_akhir) {
        return $this->db->select('t.*, w.nama_wallet, wt.nama_wallet as nama_wallet_tujuan, c.nama_kategori')
            ->from($this->table . ' t')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id')
            ->join($this->table_wallets . ' wt', 'wt.id = t.wallet_tujuan_id', 'left')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id)
            ->where('t.tanggal >=', $tanggal_mulai)
            ->where('t.tanggal <=', $tanggal_akhir)
            ->order_by('t.tanggal', 'ASC')
            ->get()
            ->result();
    }

    public function get_total_by_periode($user_id, $tipe, $tanggal_mulai, $tanggal_akhir) {
        if ($tipe === 'pengeluaran') {
            $result = $this->db->select('IFNULL(SUM(CASE WHEN tipe = "pengeluaran" THEN nominal WHEN tipe = "transfer" THEN fee ELSE 0 END),0) as total', false)
                ->where('user_id', $user_id)
                ->where('tanggal >=', $tanggal_mulai)
                ->where('tanggal <=', $tanggal_akhir)
                ->get($this->table)
                ->row();

            return $result->total ?? 0;
        }

        $result = $this->db->select('SUM(IFNULL(nominal,0)) as nominal', false)
            ->where('user_id', $user_id)
            ->where('tipe', $tipe)
            ->where('tanggal >=', $tanggal_mulai)
            ->where('tanggal <=', $tanggal_akhir)
            ->get($this->table)
            ->row();
        
        return $result->nominal ?? 0;
    }

    /**
     * Get total pemasukan per bulan untuk grafik
     */
    public function get_monthly_income($user_id, $tahun) {
        return $this->db->select('MONTH(tanggal) as bulan, SUM(IFNULL(nominal,0)) as total')
            ->where('user_id', $user_id)
            ->where('tipe', 'pemasukan')
            ->where('YEAR(tanggal)', $tahun)
            ->group_by('MONTH(tanggal)')
            ->order_by('bulan', 'ASC')
            ->get($this->table)
            ->result();
    }

    /**
     * Get total pengeluaran per bulan untuk grafik
     */
    public function get_monthly_expense($user_id, $tahun) {
        return $this->db->select('MONTH(tanggal) as bulan, IFNULL(SUM(CASE WHEN tipe = "pengeluaran" THEN nominal WHEN tipe = "transfer" THEN fee ELSE 0 END),0) as total', false)
            ->where('user_id', $user_id)
            ->where('YEAR(tanggal)', $tahun)
            ->group_by('MONTH(tanggal)')
            ->order_by('bulan', 'ASC')
            ->get($this->table)
            ->result();
    }

    /**
     * Get transaksi by kategori untuk laporan
     */
    public function get_by_kategori($user_id, $tipe, $tanggal_mulai, $tanggal_akhir) {
        return $this->db->select('c.nama_kategori, SUM(IFNULL(t.nominal,0)) as total')
            ->from($this->table . ' t')
            ->join($this->table_categories . ' c', 'c.id = t.category_id')
            ->where('t.user_id', $user_id)
            ->where('t.tipe', $tipe)
            ->where('t.tanggal >=', $tanggal_mulai)
            ->where('t.tanggal <=', $tanggal_akhir)
            ->group_by('t.category_id')
            ->order_by('total', 'DESC')
            ->get()
            ->result();
    }
    // Tambahkan method ini di M_transaksi.php

    public function suggest_loan_deskripsi($user_id, $partner_id, $keyword, $limit = 8) {
        $keyword = trim(preg_replace('/\s+/', ' ', (string) $keyword));
        $tokens = array_values(array_filter(array_unique(preg_split('/\s+/', $keyword)), function ($token) {
            return mb_strlen($token) >= 2;
        }));
        
        $this->db->select('t.deskripsi, t.wallet_id, t.category_id, t.nominal, c.nama_kategori, w.nama_wallet')
            ->from($this->table . ' t')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id)
            ->where('t.is_loan', 1)
            ->where('t.partner_id', $partner_id);
        
        $this->db->group_start();
        foreach ($tokens as $token) {
            $this->db->or_like('t.deskripsi', $token);
            $this->db->or_like('c.nama_kategori', $token);
        }
        $this->db->group_end();
        
        $rows = $this->db->group_by(['t.deskripsi', 't.wallet_id', 't.category_id', 'c.nama_kategori', 'w.nama_wallet'])
            ->order_by('t.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
        
        return $rows;
    }
}
