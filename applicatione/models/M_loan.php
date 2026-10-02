<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_loan extends CI_Model {

    private $table_transactions = 'jurnal_new_transactions';
    private $table_wallets = 'jurnal_new_wallets';
    private $table_categories = 'jurnal_new_categories';

    public function __construct() {
        parent::__construct();
    }

    private function parse_id_list($value) {
        if (is_array($value)) {
            $ids = $value;
        } else {
            $ids = preg_split('/[, ]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        })));
    }

    private function get_loan_payment_category_id() {
        $existing = $this->db->where($this->table_categories . '.user_id', 0)
            ->where('nama_kategori', 'Pembayaran Kasbon')
            ->get($this->table_categories)
            ->row();

        if ($existing) {
            return (int) $existing->id;
        }

        $data = [
            'user_id' => 0,
            'nama_kategori' => 'Pembayaran Kasbon',
            'tipe' => 'pemasukan',
            'icon' => 'fa-hand-holding-usd',
            'warna' => '#27ae60',
            'is_default' => 1,
            'is_active' => 1
        ];
        $this->db->insert($this->table_categories, $data);

        return (int) $this->db->insert_id();
    }

    private function get_user_model() {
        $CI =& get_instance();
        $CI->load->model('M_user');
        return $CI->M_user;
    }

    private function get_partner_model() {
        $CI =& get_instance();
        $CI->load->model('M_partner');
        return $CI->M_partner;
    }

    private function build_loan_relations($user_id) {
        $user_model = $this->get_user_model();
        $user = $user_model->get_by_id($user_id);
        if (!$user) {
            return [];
        }

        $relations = [];
        $parent_id = (int) ($user->loan_parent ?? 0);
        if ($parent_id > 0) {
            $parent = $user_model->get_by_id($parent_id);
            if ($parent) {
                $relations[] = [
                    'parent_id' => $parent_id,
                    'child_id' => (int) $user_id,
                    'parent_name' => $parent->fullname ?: $parent->username,
                    'child_name' => $user->fullname ?: $user->username
                ];
            }
        }

        foreach ($this->parse_id_list($user->loan_child ?? '') as $child_id) {
            $child = $user_model->get_by_id($child_id);
            if (!$child) {
                continue;
            }

            $relations[] = [
                'parent_id' => (int) $user_id,
                'child_id' => (int) $child_id,
                'parent_name' => $user->fullname ?: $user->username,
                'child_name' => $child->fullname ?: $child->username
            ];
        }

        $unique = [];
        foreach ($relations as $relation) {
            $unique[$relation['parent_id'] . ':' . $relation['child_id']] = $relation;
        }

        return array_values($unique);
    }

    public function get_loan_partners($user_id) {
        $user_model = $this->get_user_model();
        $partners = [];

        foreach ($this->build_loan_relations($user_id) as $relation) {
            $partner_user_id = ((int) $relation['parent_id'] === (int) $user_id)
                ? (int) $relation['child_id']
                : (int) $relation['parent_id'];
            $partner = $user_model->get_by_id($partner_user_id);
            if (!$partner) {
                continue;
            }

            $partners[] = (object) [
                'id' => $partner_user_id,
                'partner_id' => $partner_user_id,
                'partner_username' => $partner->username,
                'partner_fullname' => $partner->fullname ?: $partner->username,
                'username' => $partner->username,
                'fullname' => $partner->fullname ?: $partner->username,
                'user_parents' => (int) $relation['parent_id'],
                'user_child' => (int) $relation['child_id'],
                'status' => 'approved'
            ];
        }

        return $partners;
    }

    private function get_loan_relation_context($user_id, $partner_user_id = null) {
        $user_model = $this->get_user_model();
        $partner_model = $this->get_partner_model();

        $user = $user_model->get_by_id($user_id);
        if (!$user) {
            return null;
        }

        $context = [
            'user' => $user,
            'mode' => 'all',
            'role' => null,
            'partner_user_id' => null,
            'relation_id' => null,
            'borrower_user_id' => (int) $user_id,
            'lender_user_id' => (int) $user_id,
            'payment_type' => null,
            'partner_user_ids' => [],
            'relation_ids' => [],
            'relations' => []
        ];

        if (empty($partner_user_id) || $partner_user_id === 'all') {
            foreach ($this->build_loan_relations($user_id) as $relation) {
                $other_id = ((int) $relation['parent_id'] === (int) $user_id)
                    ? (int) $relation['child_id']
                    : (int) $relation['parent_id'];
                if ($other_id > 0) {
                    $context['partner_user_ids'][] = $other_id;
                }
                $context['relations'][] = $relation;
            }

            $context['partner_user_ids'] = array_values(array_unique(array_filter($context['partner_user_ids'])));

            return $context;
        }

        $partner_user_id = (int) $partner_user_id;
        $loan_relation = null;
        foreach ($this->build_loan_relations($user_id) as $relation) {
            if (
                ((int) $relation['parent_id'] === (int) $user_id && (int) $relation['child_id'] === (int) $partner_user_id) ||
                ((int) $relation['child_id'] === (int) $user_id && (int) $relation['parent_id'] === (int) $partner_user_id)
            ) {
                $loan_relation = $relation;
                break;
            }
        }

        if (!$loan_relation) {
            return null;
        }

        if ((int) $loan_relation['child_id'] === (int) $user_id) {
            $context['mode'] = 'pair';
            $context['role'] = 'borrower';
            $context['partner_user_id'] = $partner_user_id;
            $context['borrower_user_id'] = (int) $loan_relation['child_id'];
            $context['lender_user_id'] = (int) $loan_relation['parent_id'];
            $context['payment_type'] = 'pengeluaran';
        } else {
            $context['mode'] = 'pair';
            $context['role'] = 'lender';
            $context['partner_user_id'] = $partner_user_id;
            $context['borrower_user_id'] = (int) $loan_relation['child_id'];
            $context['lender_user_id'] = (int) $loan_relation['parent_id'];
            $context['payment_type'] = 'pemasukan';
        }

        $context['partner_user_ids'] = [(int) $partner_user_id];
        $context['relations'] = [$loan_relation];

        return $context;
    }

    private function get_context_relation_filter_ids($context) {
        if (empty($context)) {
            return [[], []];
        }

        $loan_user_ids = [];
        $partner_filter_ids = [];
        if ($context['mode'] === 'pair') {
            $loan_user_ids = [(int) $context['borrower_user_id']];
            $partner_filter_ids = array_values(array_unique(array_filter([
                (int) ($context['relation_id'] ?? 0),
                (int) ($context['partner_user_id'] ?? 0)
            ])));
        } else {
            $loan_user_ids = array_values(array_unique(array_map(function ($relation) {
                return (int) $relation['child_id'];
            }, $context['relations'] ?? [])));
            $partner_filter_ids = array_values(array_unique(array_merge(
                $context['relation_ids'],
                $context['partner_user_ids']
            )));
        }

        $loan_user_ids = array_values(array_filter(array_map('intval', $loan_user_ids), function ($id) {
            return $id > 0;
        }));
        $partner_filter_ids = array_values(array_filter(array_map('intval', $partner_filter_ids), function ($id) {
            return $id > 0;
        }));

        return [$loan_user_ids, $partner_filter_ids];
    }

    private function calculate_pair_summary($user_id, $partner_user_id) {
        $context = $this->get_loan_relation_context($user_id, $partner_user_id);
        if (!$context || empty($context['payment_type'])) {
            return (object) [
                'total_loan' => 0,
                'total_paid' => 0,
                'remaining' => 0,
                'total_loan_out' => 0,
                'total_loan_in' => 0,
                'net_loan' => 0
            ];
        }

        $payment_category_id = $this->get_loan_payment_category_id();

        $loan_row = $this->db->select('SUM(IFNULL(nominal,0)) as total', false)
            ->from($this->table_transactions)
            ->where('user_id', $context['borrower_user_id'])
            ->where('is_loan', 1)
            ->where('tipe', 'pengeluaran')
            ->where('category_id !=', $payment_category_id)
            ->get()
            ->row();
        $total_loan = (float) ($loan_row->total ?? 0);

        $payment_row = $this->db->select('SUM(IFNULL(nominal,0)) as total', false)
            ->from($this->table_transactions)
            ->where('user_id', $context['lender_user_id'])
            ->where('is_loan', 1)
            ->where('tipe', 'pengeluaran')
            ->where('category_id', $payment_category_id)
            ->like('deskripsi', 'ke ' . ($context['relations'][0]['child_name'] ?? ''), 'both')
            ->get()
            ->row();
        $total_paid = (float) ($payment_row->total ?? 0);

        $remaining = $total_loan - $total_paid;

        return (object) [
            'total_loan' => $total_loan,
            'total_paid' => $total_paid,
            'remaining' => $remaining,
            'total_loan_out' => $total_loan,
            'total_loan_in' => $total_paid,
            'net_loan' => $remaining
        ];
    }

    // ==================== LOAN TRANSACTIONS ====================

    public function get_loan_transactions($user_id, $partner_id = null, $status = null, $limit = null, $offset = null) {
        $context = $this->get_loan_relation_context($user_id, $partner_id);
        if (!$context) {
            return [];
        }

        $payment_category_id = $this->get_loan_payment_category_id();
        list($loan_user_ids) = $this->get_context_relation_filter_ids($context);
        if (empty($loan_user_ids)) {
            return [];
        }

        $this->db->select('t.*, w.nama_wallet, c.nama_kategori, c.icon, u.username as partner_username, u.fullname as partner_fullname')
            ->from($this->table_transactions . ' t')
            ->join('jurnal_new_users u', 'u.id = t.user_id', 'left')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id', 'left')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where_in('t.user_id', $loan_user_ids)
            ->where('t.tipe', 'pengeluaran')
            ->where('t.category_id !=', $payment_category_id);

        if ($status === 'active') {
            $this->db->where('t.is_loan', 1);
        } elseif ($status === 'cancelled') {
            $this->db->where('t.is_loan', 2);
        } elseif ($status === 'all') {
            $this->db->where_in('t.is_loan', [1, 2]);
        } else {
            $this->db->where('t.is_loan', 1);
        }

        $this->db->order_by('t.tanggal', 'DESC')
            ->order_by('t.id', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function count_loan_transactions($user_id, $partner_id = null) {
        $context = $this->get_loan_relation_context($user_id, $partner_id);
        if (!$context) {
            return 0;
        }

        $payment_category_id = $this->get_loan_payment_category_id();
        list($loan_user_ids) = $this->get_context_relation_filter_ids($context);
        if (empty($loan_user_ids)) {
            return 0;
        }

        $this->db->from($this->table_transactions . ' t')
            ->where_in('t.user_id', $loan_user_ids)
            ->where('t.tipe', 'pengeluaran')
            ->where_in('t.is_loan', [1, 2])
            ->where('t.category_id !=', $payment_category_id);

        return $this->db->count_all_results();
    }

    public function get_total_loan_amount($user_id, $partner_id = null, $only_active = true) {
        $context = $this->get_loan_relation_context($user_id, $partner_id);
        if (!$context) {
            return 0;
        }

        $payment_category_id = $this->get_loan_payment_category_id();
        list($loan_user_ids) = $this->get_context_relation_filter_ids($context);
        if (empty($loan_user_ids)) {
            return 0;
        }

        $this->db->select('SUM(IFNULL(t.nominal,0)) as nominal', false)
            ->from($this->table_transactions . ' t')
            ->where_in('t.user_id', $loan_user_ids)
            ->where('t.tipe', 'pengeluaran')
            ->where('t.category_id !=', $payment_category_id);

        if ($only_active) {
            $this->db->where('t.is_loan', 1);
        }

        $result = $this->db->get()->row();
        return (float) ($result->nominal ?? 0);
    }

    public function get_loan_summary($user_id, $partner_id = null) {
        if (empty($partner_id) || $partner_id === 'all') {
            $partners = $this->get_loan_partners($user_id);

            $summary = [
                'total_loan_out' => 0,
                'total_loan_in' => 0,
                'net_loan' => 0
            ];

            foreach ($partners as $relation) {
                $partner_user_id = (int) ($relation->partner_id ?? 0);
                if ($partner_user_id <= 0) {
                    continue;
                }


                $pair_summary = $this->calculate_pair_summary($user_id, $partner_user_id);
                $summary['total_loan_out'] += (float) ($pair_summary->total_loan_out ?? 0);
                $summary['total_loan_in'] += (float) ($pair_summary->total_loan_in ?? 0);
                $summary['net_loan'] += (float) ($pair_summary->net_loan ?? 0);
            }
            return (object) [
                'total_loan' => $summary['total_loan_out'],
                'total_paid' => $summary['total_loan_in'],
                'remaining' => $summary['net_loan'],
                'total_loan_out' => $summary['total_loan_out'],
                'total_loan_in' => $summary['total_loan_in'],
                'net_loan' => $summary['net_loan']
            ];
        }
        
        return $this->calculate_pair_summary($user_id, $partner_id);
    }

    public function process_payment($data) {
        $this->db->trans_start();

        $relation_id = (int) ($data['partner_id'] ?? 0);

        // 1. Catat transaksi di payer (pengeluaran)
        $transaction_payer = [
            'user_id' => $data['payer_id'],
            'wallet_id' => $data['wallet_id_payer'],
            'category_id' => $this->get_loan_payment_category_id(),
            'nominal' => $data['amount'],
            'tipe' => 'pengeluaran',
            'deskripsi' => 'Pembayaran kasbon ke ' . $data['receiver_name'],
            'tanggal' => $data['payment_date'],
            'catatan' => $data['note'],
            'is_loan' => 1,
            'partner_id' => $relation_id,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert($this->table_transactions, $transaction_payer);
        $transaction_id_payer = $this->db->insert_id();

        // Update saldo dompet payer
        $this->db->set('saldo_awal', 'saldo_awal - ' . (float) $data['amount'], false)
            ->where('id', $data['wallet_id_payer'])
            ->update($this->table_wallets);

        // 2. Catat transaksi di receiver (pemasukan)
        $transaction_receiver = [
            'user_id' => $data['receiver_id'],
            'wallet_id' => $data['wallet_id_receiver'],
            'category_id' => $this->get_loan_payment_category_id(),
            'nominal' => $data['amount'],
            'tipe' => 'pemasukan',
            'deskripsi' => 'Pembayaran kasbon dari ' . $data['payer_name'],
            'tanggal' => $data['payment_date'],
            'catatan' => $data['note'],
            'is_loan' => 1,
            'partner_id' => $relation_id,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert($this->table_transactions, $transaction_receiver);

        // Update saldo dompet receiver
        $this->db->set('saldo_awal', 'saldo_awal + ' . (float) $data['amount'], false)
            ->where('id', $data['wallet_id_receiver'])
            ->update($this->table_wallets);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $transaction_id_payer;
    }

    // ==================== CANCEL LOAN ====================

    public function cancel_loan($transaction_id, $user_id, $reason) {
        $transaction = $this->db
            ->where('id', $transaction_id)
            ->get($this->table_transactions)
            ->row();

        if (!$transaction || $transaction->is_loan != 1) {
            return false;
        }

        $allowed = (int) $transaction->user_id === (int) $user_id;
        if (!$allowed) {
            foreach ($this->build_loan_relations($user_id) as $relation) {
                if ((int) $relation['parent_id'] === (int) $user_id && (int) $relation['child_id'] === (int) $transaction->user_id) {
                    $allowed = true;
                    break;
                }
            }
        }

        if (!$allowed) {
            return false;
        }

        $this->db->trans_start();
        $this->db->set('saldo_awal', 'saldo_awal + ' . (float) $transaction->nominal, false)
            ->where('id', $transaction->wallet_id)
            ->update($this->table_wallets);
        $this->db->where('id', $transaction_id)->update($this->table_transactions, [
            'is_loan' => 2,
            'cancelled_reason' => $reason,
            'cancelled_by' => $user_id,
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function restore_loan($transaction_id, $user_id) {
        $transaction = $this->db
            ->where('id', $transaction_id)
            ->get($this->table_transactions)
            ->row();

        if (!$transaction || $transaction->is_loan != 2) {
            return false;
        }

        $allowed = (int) $transaction->user_id === (int) $user_id;
        if (!$allowed) {
            foreach ($this->build_loan_relations($user_id) as $relation) {
                if ((int) $relation['parent_id'] === (int) $user_id && (int) $relation['child_id'] === (int) $transaction->user_id) {
                    $allowed = true;
                    break;
                }
            }
        }

        if (!$allowed) {
            return false;
        }

        $this->db->trans_start();
        $this->db->set('saldo_awal', 'saldo_awal - ' . (float) $transaction->nominal, false)
            ->where('id', $transaction->wallet_id)
            ->update($this->table_wallets);
        $this->db->where('id', $transaction_id)->update($this->table_transactions, [
            'is_loan' => 1,
            'cancelled_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null
        ]);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    // ==================== REPORTS ====================

    public function get_loan_report($user_id, $partner_id, $start_date, $end_date, $limit = null, $offset = null) {
        $context = $this->get_loan_relation_context($user_id, $partner_id);
        if (!$context) {
            return [];
        }

        $payment_category_id = $this->get_loan_payment_category_id();
        list($loan_user_ids) = $this->get_context_relation_filter_ids($context);
        if (empty($loan_user_ids)) {
            return [];
        }

        $this->db->select('t.*, w.nama_wallet, c.nama_kategori, c.icon, u.username as partner_username, u.fullname as partner_fullname')
            ->from($this->table_transactions . ' t')
            ->join('jurnal_new_users u', 'u.id = t.user_id', 'left')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id', 'left')
            ->join($this->table_categories . ' c', 'c.id = t.category_id', 'left')
            ->where_in('t.user_id', $loan_user_ids)
            ->where_in('t.is_loan', [1, 2])
            ->where('t.tipe', 'pengeluaran')
            ->where('t.category_id !=', $payment_category_id)
            ->where('t.tanggal >=', $start_date)
            ->where('t.tanggal <=', $end_date);

        $this->db->order_by('t.tanggal', 'DESC')
            ->order_by('t.id', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function count_loan_report($user_id, $partner_id, $start_date, $end_date) {
        $context = $this->get_loan_relation_context($user_id, $partner_id);
        if (!$context) {
            return 0;
        }

        $payment_category_id = $this->get_loan_payment_category_id();
        list($loan_user_ids) = $this->get_context_relation_filter_ids($context);
        if (empty($loan_user_ids)) {
            return 0;
        }

        $this->db->from($this->table_transactions . ' t')
            ->where_in('t.user_id', $loan_user_ids)
            ->where_in('t.is_loan', [1, 2])
            ->where('t.tipe', 'pengeluaran')
            ->where('t.category_id !=', $payment_category_id)
            ->where('t.tanggal >=', $start_date)
            ->where('t.tanggal <=', $end_date);

        return $this->db->count_all_results();
    }

    public function get_loan_by_category($user_id, $partner_id, $start_date, $end_date) {
        $context = $this->get_loan_relation_context($user_id, $partner_id);
        if (!$context) {
            return [];
        }

        $payment_category_id = $this->get_loan_payment_category_id();
        list($loan_user_ids) = $this->get_context_relation_filter_ids($context);
        if (empty($loan_user_ids)) {
            return [];
        }

        $this->db->select('c.nama_kategori, c.warna, c.icon, SUM(IFNULL(t.nominal,0)) as total')
            ->from($this->table_transactions . ' t')
            ->join($this->table_categories . ' c', 'c.id = t.category_id')
            ->where_in('t.user_id', $loan_user_ids)
            ->where('t.is_loan', 1)
            ->where('t.tipe', 'pengeluaran')
            ->where('t.category_id !=', $payment_category_id)
            ->where('t.tanggal >=', $start_date)
            ->where('t.tanggal <=', $end_date)
            ->group_by('t.category_id')
            ->order_by('total', 'DESC');

        return $this->db->get()->result();
    }

    public function get_loan_payments_history($user_id, $partner_id, $limit = null, $offset = null) {
        $context = $this->get_loan_relation_context($user_id, $partner_id);
        if (!$context) {
            return [];
        }

        $payment_category_id = $this->get_loan_payment_category_id();
        if (empty($context['relations'])) {
            return [];
        }

        $this->db->select('t.id, t.tanggal as payment_date, t.nominal as amount, t.tipe, t.deskripsi, t.catatan as note, w.nama_wallet as wallet_name, u.username as user_name, u.fullname as user_fullname')
            ->from($this->table_transactions . ' t')
            ->join($this->table_wallets . ' w', 'w.id = t.wallet_id', 'left')
            ->join('jurnal_new_users u', 'u.id = t.user_id', 'left')
            ->where('t.category_id', $payment_category_id)
            ->like('t.deskripsi', 'Pembayaran kasbon', 'after');

        $this->db->group_start();
        foreach ($context['relations'] as $relation) {
            // Sisi payer: parent keluar (bayar ke child)
            $this->db->or_group_start()
                ->where('t.user_id', (int) $relation['parent_id'])
                ->where('t.tipe', 'pengeluaran')
                ->like('t.deskripsi', 'ke ' . $relation['child_name'], 'both')
            ->group_end();
            // Sisi receiver: child masuk (terima dari parent)
            $this->db->or_group_start()
                ->where('t.user_id', (int) $relation['child_id'])
                ->where('t.tipe', 'pemasukan')
                ->like('t.deskripsi', 'Pembayaran kasbon dari', 'after')
            ->group_end();
        }
        $this->db->group_end();

        $this->db->order_by('t.tanggal', 'DESC')
            ->order_by('t.id', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }
}
