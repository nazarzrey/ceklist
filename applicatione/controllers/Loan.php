<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_loan');
        $this->load->model('M_partner');
        $this->load->model('M_dompet');
        $this->load->model('M_notification');
    }
    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        // Cek apakah punya partner
        $partners = $this->M_loan->get_loan_partners($user_id);
        if (empty($partners)) {
            redirect('partner');
            return;
        }
        
        $partner_id = $this->input->get('partner_id');
        if (!$partner_id && !empty($partners)) {
            $partner_id = $partners[0]->partner_id;
        }
        $selected_partner = $this->get_partner_by_id($partners, $partner_id);
        if (!$selected_partner && !empty($partners)) {
            $selected_partner = $partners[0];
            $partner_id = $selected_partner->partner_id;
        }
        
        // ========== PERBAIKAN: Tentukan arah pembayaran ==========
        // Cek relasi dari tabel users (loan_parent / loan_child)
        $current_user = $this->M_user->get_by_id($user_id);
        $payment_direction = 'outgoing'; // default: user bayar ke partner (keluar dari user)
        
        if ($current_user) {
            // Jika user adalah PARENT (user_id = loan_parent dari partner)
            // Maka user memberi uang ke child → outgoing
            $is_parent = false;
            $is_child = false;
            
            // Cek apakah partner adalah child dari user ini
            $child_ids = !empty($current_user->loan_child) ? explode(',', $current_user->loan_child) : [];
            if (in_array($partner_id, $child_ids)) {
                $is_parent = true; // User adalah parent, partner adalah child
            }
            
            // Cek apakah user adalah child dari partner
            $partner_user = $this->M_user->get_by_id($partner_id);
            if ($partner_user && !empty($partner_user->loan_child)) {
                $partner_child_ids = explode(',', $partner_user->loan_child);
                if (in_array($user_id, $partner_child_ids)) {
                    $is_child = true; // User adalah child, partner adalah parent
                }
            }
            
            // Atau cek langsung dari loan_parent user
            if ((int)($current_user->loan_parent ?? 0) === (int)$partner_id) {
                $is_child = true; // User adalah child, partner adalah parent
            }
            
            // Tentukan arah pembayaran
            if ($is_child) {
                // User adalah CHILD → user MENERIMA uang dari parent
                $payment_direction = 'incoming';
            } elseif ($is_parent) {
                // User adalah PARENT → user MEMBAYAR ke child
                $payment_direction = 'outgoing';
            } else {
                // Fallback: cek dari partner relation di tabel partners
                $relation = $this->M_partner->get_existing_request($user_id, $partner_id);
                if ($relation && $relation->loan_relation_type === 'parent') {
                    // Jika user_a yang kirim request sebagai parent, maka user_a adalah parent
                    if ((int)$relation->user_parents === (int)$user_id) {
                        $payment_direction = 'outgoing';
                    } else {
                        $payment_direction = 'incoming';
                    }
                } elseif ($relation && $relation->loan_relation_type === 'child') {
                    if ((int)$relation->user_parents === (int)$user_id) {
                        $payment_direction = 'incoming';
                    } else {
                        $payment_direction = 'outgoing';
                    }
                }
            }
        }
        
        $summary = $this->M_loan->get_loan_summary($user_id, $partner_id);
        $loan_page = max(1, (int) $this->input->get('page'));
        $loan_limit = 10;
        $loan_total_rows = $this->M_loan->count_loan_transactions($user_id, $partner_id);
        $loan_transactions = $this->decorate_loan_rows(
            $this->M_loan->get_loan_transactions($user_id, $partner_id, 'all', $loan_limit, ($loan_page - 1) * $loan_limit),
            $selected_partner
        );
        $payment_history = $this->M_loan->get_loan_payments_history($user_id, $partner_id, 10);
        
        // Ambil dompet user dan dompet partner
        $my_wallets = $this->M_dompet->get_by_user($user_id);
        $partner_wallets = [];
        
        if ($partner_id) {
            $partner_wallets = $this->M_dompet->get_by_user($partner_id);
        }
        
        $data = [
            'title' => 'Kasbon',
            'active_menu' => 'loan',
            'partners' => $partners,
            'selected_partner_id' => $partner_id,
            'selected_partner' => $selected_partner,
            'summary' => $summary,
            'loan_transactions' => $loan_transactions,
            'payment_history' => $payment_history,
            'my_wallets' => $my_wallets,
            'partner_wallets' => $partner_wallets,
            'payment_direction' => $payment_direction
            , 'loan_page' => $loan_page
            , 'loan_total_pages' => max(1, (int) ceil($loan_total_rows / $loan_limit))
        ];
        
        $this->render('loan/index', $data);
    }

    private function get_partner_by_id($partners, $partner_id) {
        foreach ($partners as $p) {
            if ($p->partner_id == $partner_id) {
                return $p;
            }
        }
        return null;
    }

    private function decorate_loan_rows($rows, $selected_partner) {
        if (empty($rows) || !$selected_partner) {
            return $rows;
        }

        $partner_username = $selected_partner->partner_username ?? $selected_partner->username ?? '-';
        $partner_fullname = $selected_partner->partner_fullname ?? $selected_partner->fullname ?? $partner_username;

        foreach ($rows as $row) {
            $row->partner_username = $partner_username;
            $row->partner_fullname = $partner_fullname;
        }

        return $rows;
    }

    private function get_partner_relation($user_id, $partner_user_id) {
        if (!$partner_user_id) {
            return null;
        }

        return $this->get_partner_by_id($this->M_loan->get_loan_partners($user_id), $partner_user_id);
    }
    public function bayar() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        
        $partner_id = $this->input->post('partner_id');
        $payment_direction = $this->input->post('payment_direction') ?: 'outgoing';
        $amount = str_replace(['Rp', '.', ' '], '', $this->input->post('amount'));
        $wallet_id_payer = $this->input->post('wallet_id_payer');
        $wallet_id_receiver = $this->input->post('wallet_id_receiver');
        $note = $this->input->post('note');
        $payment_date = $this->input->post('payment_date') ?: date('Y-m-d');
        
        // Validasi
        if (!$partner_id || !$amount || $amount <= 0) {
            echo json_encode(['status' => false, 'message' => 'Data tidak lengkap']);
            return;
        }
        
        if (!$wallet_id_payer) {
            echo json_encode(['status' => false, 'message' => 'Pilih dompet asal']);
            return;
        }
        
        if (!$wallet_id_receiver) {
            echo json_encode(['status' => false, 'message' => 'Pilih dompet tujuan partner']);
            return;
        }
        
        // Cek partner
        $partners = $this->M_loan->get_loan_partners($user_id);
        $selected_partner = null;
        foreach ($partners as $p) {
            if ($p->partner_id == $partner_id) {
                $selected_partner = $p;
                break;
            }
        }
        
        if (!$selected_partner) {
            echo json_encode(['status' => false, 'message' => 'Partner tidak ditemukan']);
            return;
        }
        
        // ========== PERBAIKAN: Tentukan payer dan receiver berdasarkan relasi ==========
        $current_user = $this->M_user->get_by_id($user_id);
        $is_child = false;
        $is_parent = false;
        
        if ($current_user) {
            // Cek apakah user adalah child dari partner
            if ((int)($current_user->loan_parent ?? 0) === (int)$partner_id) {
                $is_child = true;
            }
            
            // Cek apakah partner adalah child dari user
            $child_ids = !empty($current_user->loan_child) ? explode(',', $current_user->loan_child) : [];
            if (in_array($partner_id, $child_ids)) {
                $is_parent = true;
            }
        }
        
        // Tentukan siapa payer dan receiver
        if ($is_child) {
            // User adalah CHILD → user MENERIMA uang dari parent
            // Parent (partner) yang membayar ke user
            $expected_payer_id = (int) $partner_id;   // Partner yang bayar (parent)
            $expected_receiver_id = (int) $user_id;   // User yang terima (child)
            $is_incoming_payment = true;
        } elseif ($is_parent) {
            // User adalah PARENT → user MEMBAYAR ke child
            $expected_payer_id = (int) $user_id;      // User yang bayar (parent)
            $expected_receiver_id = (int) $partner_id; // Partner yang terima (child)
            $is_incoming_payment = false;
        } else {
            // Fallback ke payment_direction dari form
            $is_incoming_payment = ($payment_direction === 'incoming');
            $expected_payer_id = $is_incoming_payment ? (int) $partner_id : (int) $user_id;
            $expected_receiver_id = $is_incoming_payment ? (int) $user_id : (int) $partner_id;
        }
        
        // Validasi wallet_payer milik expected_payer_id
        $wallet_payer = $this->M_dompet->get_by_id($wallet_id_payer, $expected_payer_id);
        if (!$wallet_payer || $wallet_payer->saldo_awal < $amount) {
            echo json_encode(['status' => false, 'message' => 'Saldo dompet asal tidak cukup']);
            return;
        }
        
        // Validasi wallet_receiver milik expected_receiver_id
        $wallet_receiver = $this->M_dompet->get_by_id($wallet_id_receiver, $expected_receiver_id);
        if (!$wallet_receiver) {
            echo json_encode(['status' => false, 'message' => 'Dompet tujuan tidak valid']);
            return;
        }
        
        $payer_name = $is_incoming_payment
            ? ($selected_partner->partner_fullname ?: $selected_partner->partner_username)
            : ($this->session->userdata('fullname') ?: $this->session->userdata('username'));
        $receiver_name = $is_incoming_payment
            ? ($this->session->userdata('fullname') ?: $this->session->userdata('username'))
            : ($selected_partner->partner_fullname ?: $selected_partner->partner_username);
        
        $payment_data = [
            'payer_id' => $expected_payer_id,
            'receiver_id' => $expected_receiver_id,
            'partner_id' => $selected_partner->id,
            'amount' => $amount,
            'payment_date' => $payment_date,
            'note' => $note,
            'wallet_id_payer' => $wallet_id_payer,
            'wallet_id_receiver' => $wallet_id_receiver,
            'payer_name' => $payer_name,
            'receiver_name' => $receiver_name
        ];
        
        $result = $this->M_loan->process_payment($payment_data);
        
        if ($result) {
            // Notifikasi ke partner
            $this->M_notification->add(
                $partner_id,
                'loan_paid',
                'Pembayaran Kasbon',
                $payer_name . ' membayar kasbon sebesar Rp ' . number_format($amount, 0, ',', '.'),
                $result,
                ['amount' => $amount, 'payment_date' => $payment_date]
            );
            
            echo json_encode(['status' => true, 'message' => 'Pembayaran berhasil']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Pembayaran gagal']);
        }
    }

    public function batalkan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $transaction_id = $this->input->post('transaction_id');
        $reason = trim($this->input->post('reason'));
        
        if (!$transaction_id || empty($reason)) {
            echo json_encode(['status' => false, 'message' => 'Alasan pembatalan wajib diisi']);
            return;
        }
        
        $result = $this->M_loan->cancel_loan($transaction_id, $user_id, $reason);
        
        if ($result) {
            // Dapatkan transaksi untuk notifikasi ke partner
            $transaction = $this->db->where('id', $transaction_id)->get('jurnal_new_transactions')->row();
            if ($transaction && $transaction->partner_id) {
                $partner_rel = $this->M_partner->get_by_id($transaction->partner_id);
                if ($partner_rel) {
                    $partner_id = ($partner_rel->user_parents == $user_id) ? $partner_rel->user_child : $partner_rel->user_parents;
                    $this->M_notification->add(
                        $partner_id,
                        'loan_cancelled',
                        'Kasbon Dibatalkan',
                        $this->session->userdata('fullname') . ' membatalkan kasbon: ' . $reason,
                        $transaction_id
                    );
                }
            }
            
            echo json_encode(['status' => true, 'message' => 'Kasbon dibatalkan']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal membatalkan']);
        }
    }

    public function laporan() {
        $user_id = $this->session->userdata('user_id');

        if (empty($this->data['can_access_loan_report'])) {
            redirect('partner');
            return;
        }
        
        $partners = $this->M_loan->get_loan_partners($user_id);
        if (empty($partners)) {
            redirect('partner');
            return;
        }
        
        $partner_id = $this->input->get('partner_id');
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-d');
        $page = max(1, (int) $this->input->get('page'));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Jika partner_id = 'all', tampilkan semua
        $filter_partner_id = ($partner_id && $partner_id != 'all') ? $partner_id : null;
        
        $total_rows = $this->M_loan->count_loan_report($user_id, $filter_partner_id, $start_date, $end_date);
        $selected_partner = $this->get_partner_by_id($partners, $partner_id);
        $summary = $this->M_loan->get_loan_summary($user_id, $filter_partner_id);
        $loan_transactions = $this->decorate_loan_rows($this->M_loan->get_loan_report($user_id, $filter_partner_id, $start_date, $end_date, $limit, $offset), $selected_partner);
        $category_stats = $this->M_loan->get_loan_by_category($user_id, $filter_partner_id, $start_date, $end_date);
        
        $data = [
            'title' => 'Laporan Kasbon',
            'active_menu' => 'loan_report',
            'partners' => $partners,
            'selected_partner_id' => $partner_id,
            'selected_partner' => $selected_partner,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'summary' => $summary,
            'loan_transactions' => $loan_transactions,
            'category_stats' => $category_stats,
            'page' => $page,
            'total_pages' => max(1, (int) ceil($total_rows / $limit)),
            'total_rows' => $total_rows
        ];
        
        $this->render('loan/report', $data);
    }

    public function pulihkan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $transaction_id = $this->input->post('transaction_id');

        if (!$transaction_id) {
            echo json_encode(['status' => false, 'message' => 'ID transaksi tidak valid']);
            return;
        }

        $result = $this->M_loan->restore_loan($transaction_id, $user_id);

        if ($result) {
            echo json_encode(['status' => true, 'message' => 'Kasbon dipulihkan']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal memulihkan kasbon']);
        }
    }

    public function get_total_kasbon() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $partner_user_id = $this->input->get('partner_id');
        $partner_relation = $this->get_partner_relation($user_id, $partner_user_id);

        if ($partner_user_id && !$partner_relation) {
            echo json_encode([
                'status' => false,
                'message' => 'Partner tidak valid'
            ]);
            return;
        }
        
        $loan_summary = $this->M_loan->get_loan_summary($user_id, $partner_user_id);
        echo json_encode([
            'status' => true,
            'total_loan_out' => $loan_summary->total_loan_out ?? 0,
            'total_loan_in' => $loan_summary->total_loan_in ?? 0,
            'net_loan' => $loan_summary->net_loan ?? 0,
            'remaining' => $loan_summary->net_loan ?? 0
        ]);
    }
}
