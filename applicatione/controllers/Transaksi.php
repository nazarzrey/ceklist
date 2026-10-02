<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('M_transaksi');
        $this->load->model('M_kategori');
        $this->load->model('M_dompet');
        $this->load->model('M_partner');
        $this->load->model('M_user');
    }

    private function validate_wallet_not_negative($user_id, $new_data, $old_transaction = null) {
        $new_effects = $this->M_transaksi->get_transaction_wallet_effects((object) $new_data);
        $old_effects = $old_transaction ? $this->M_transaksi->get_transaction_wallet_effects($old_transaction) : [];

        $wallet_ids = array_keys(array_merge($new_effects, $old_effects));

        foreach ($wallet_ids as $wid) {
            $wallet = $this->M_dompet->get_by_id($wid, $user_id);
            if ($wallet && !empty($wallet->is_virtual)) continue;

            $net_effect = ($new_effects[$wid] ?? 0) - ($old_effects[$wid] ?? 0);
            if ($net_effect >= 0) continue;

            $balance = $this->M_transaksi->get_wallet_balance($wid, $user_id);
            if ($balance + $net_effect < 0) return false;
        }

        return true;
    }

    private function build_transfer_description($user_id, $wallet_id, $wallet_tujuan_id) {
        $wallet_asal = $this->M_dompet->get_by_id($wallet_id, $user_id);
        $wallet_tujuan = $this->M_dompet->get_by_id($wallet_tujuan_id, $user_id);

        $nama_asal = $wallet_asal ? $wallet_asal->nama_wallet : 'Dompet Asal';
        $nama_tujuan = $wallet_tujuan ? $wallet_tujuan->nama_wallet : 'Dompet Tujuan';

        return 'Transfer ' . $nama_asal . ' ke ' . $nama_tujuan;
    }

    private function get_partner_relation($user_id, $partner_relation_id) {
        if (!$partner_relation_id) {
            return null;
        }

        $relation = $this->M_partner->get_by_id($partner_relation_id);
        if (!$relation || $relation->status !== 'approved') {
            return null;
        }

        if ((int) $relation->user_parents !== (int) $user_id && (int) $relation->user_child !== (int) $user_id) {
            return null;
        }

        return $relation;
    }

    private function get_loan_parent_partner($user_id) {
        $current_user = $this->M_user->get_by_id($user_id);
        if (!$current_user || empty($current_user->loan_parent)) {
            return null;
        }

        $parent = $this->M_user->get_by_id($current_user->loan_parent);
        if (!$parent) {
            return null;
        }

        return (object) [
            'id' => (int) $parent->id,
            'partner_id' => (int) $parent->id,
            'partner_username' => $parent->username,
            'partner_fullname' => $parent->fullname ?: $parent->username,
            'relation_label' => 'Parent'
        ];
    }

    private function resolve_loan_partner_relation($user_id, $partner_ref_id) {
        if (!$partner_ref_id) {
            return null;
        }

        $partner_ref_id = (int) $partner_ref_id;
        $current_user = $this->M_user->get_by_id($user_id);
        if ($current_user && (int) ($current_user->loan_parent ?? 0) === $partner_ref_id) {
            return (object) [
                'id' => $partner_ref_id,
                'user_parents' => $partner_ref_id,
                'user_child' => (int) $user_id,
                'status' => 'approved'
            ];
        }

        $relation = $this->M_partner->get_by_id($partner_ref_id);
        if ($relation && $relation->status === 'approved' && ((int) $relation->user_parents === (int) $user_id || (int) $relation->user_child === (int) $user_id)) {
            return $relation;
        }

        $relation = $this->M_partner->get_existing_request($user_id, $partner_ref_id);
        if ($relation && $relation->status === 'approved') {
            return $relation;
        }

        return null;
    }

    private function get_current_loan_parent_relation($user_id) {
        $current_user = $this->M_user->get_by_id($user_id);
        if (!$current_user || empty($current_user->loan_parent)) {
            return null;
        }

        return $this->resolve_loan_partner_relation($user_id, $current_user->loan_parent);
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        $tanggal_mulai = $this->input->get('start') ?: date('Y-m-01');
        $tanggal_akhir = $this->input->get('end') ?: date('Y-m-d');
        $keyword = trim($this->input->get('q'));
        $tipe = $this->input->get('tipe');
        if ($tipe && !in_array($tipe, ['pemasukan', 'pengeluaran', 'transfer'], true)) {
            $tipe = null;
        }
        $page = max(1, (int) $this->input->get('page'));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $total_rows = $this->M_transaksi->count_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $keyword, null, null, $tipe);
        $current_user = $this->M_user->get_by_id($user_id);
        $loan_relation_partners = [];
        $loan_parent_partner = $this->get_loan_parent_partner($user_id);
        if ($loan_parent_partner) {
            $loan_relation_partners[] = $loan_parent_partner;
        }
        $has_loan_relation = !empty($loan_relation_partners);
        
        $data = [
            'title' => 'Transaksi',
            'active_menu' => 'transaksi',
            'transaksi' => $this->M_transaksi->get_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $limit, $offset, $keyword, null, null, $tipe),
            'kategori_pemasukan' => $this->M_kategori->get_pemasukan($user_id),
            'kategori_pengeluaran' => $this->M_kategori->get_pengeluaran($user_id),
            'dompet' => $this->M_dompet->get_by_user($user_id),
            'loan_summary' => $this->M_dompet->get_wallet_summary_loan($user_id),
            'loan_relation_partners' => $loan_relation_partners,
            'has_loan_relation' => $has_loan_relation,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'keyword' => $keyword,
            'tipe' => $tipe,
            'page' => $page,
            'total_pages' => max(1, (int) ceil($total_rows / $limit)),
        ];

        $totals_hari = $this->M_transaksi->get_totals_hari_ini($user_id);
        $totals_bulan = $this->M_transaksi->get_totals_bulan_ini($user_id);
        $data['total_pemasukan_hari_ini'] = (float) ($totals_hari->pemasukan ?? 0);
        $data['total_pengeluaran_hari_ini'] = (float) ($totals_hari->pengeluaran ?? 0);
        $data['total_pemasukan_bulan_ini'] = (float) ($totals_bulan->pemasukan ?? 0);
        $data['total_pengeluaran_bulan_ini'] = (float) ($totals_bulan->pengeluaran ?? 0);
        
        $this->render('transaksi/index', $data);
    }

    public function filter_data() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_akhir = $this->input->post('tanggal_akhir');
        $kategori_id = $this->input->post('kategori_id');
        $wallet_id = $this->input->post('wallet_id');
        $tipe = $this->input->post('tipe');
        if ($tipe && !in_array($tipe, ['pemasukan', 'pengeluaran', 'transfer'], true)) {
            $tipe = null;
        }
        $keyword = trim($this->input->post('q'));
        $page = max(1, (int) $this->input->post('page'));
        $limit = (int) $this->input->post('limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $total_rows = $this->M_transaksi->count_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $keyword, $kategori_id, $wallet_id, $tipe);
        $transaksi = $this->M_transaksi->get_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $limit, $offset, $keyword, $kategori_id, $wallet_id, $tipe);

        $html = '';
        foreach ($transaksi as $t) {
            $isLoan = (int) $t->is_loan === 1;
            $isLoanCancelled = (int) $t->is_loan === 2;
            $tipeLabel = $isLoan
                ? ($t->tipe === 'pemasukan' ? 'in#cashbon' : 'out#cashbon')
                : ($isLoanCancelled ? ($t->tipe === 'pemasukan' ? 'in#cancel' : 'out#cancel') : $t->tipe);
            $tipeClass = $isLoan
                ? 'bg-warning text-dark'
                : ($isLoanCancelled ? 'bg-secondary' : ($t->tipe === 'pemasukan' ? 'bg-success' : ($t->tipe === 'transfer' ? 'bg-primary' : 'bg-danger')));

            $isLoanCancelled = (int) $t->is_loan === 2;
            $html .= '<tr class="' . ($isLoanCancelled ? 'loan-cancelled-warning' : '') . '">';
            $html .= '<td data-label="Tanggal">' . date('d-m-y', strtotime($t->tanggal)) . ' # ' . htmlspecialchars($t->nama_wallet) . '</td>';
            $html .= '<td data-label="Nama">' . htmlspecialchars($t->deskripsi) . '</td>';
            $html .= '<td data-label="Kategori">' . htmlspecialchars($t->tipe === 'transfer' ? 'Transfer' : ($t->nama_kategori ?? '-')) . '</td>';
            $html .= '<td data-label="Dompet">' . htmlspecialchars($t->tipe === 'transfer' ? ($t->nama_wallet . ' -> ' . ($t->nama_wallet_tujuan ?? '-')) : $t->nama_wallet) . '</td>';
            $nominalClass = $t->tipe == 'pemasukan' ? 'text-success' : ($t->tipe == 'transfer' ? 'text-primary' : 'text-danger');
            $html .= '<td data-label="Nominal" class="text-end ' . $nominalClass . ' fw-semibold">';
            if ($t->tipe === 'transfer') {
                $html .= '<span class="d-block">Rp ' . number_format($t->nominal, 0, ',', '.') . '</span>';
                if ((float) $t->fee > 0) {
                    $html .= '<small class="text-muted">Fee Rp ' . number_format($t->fee, 0, ',', '.') . '</small>';
                }
            } else {
                $html .= ($t->tipe == 'pemasukan' ? '+' : '-') . ' Rp ' . number_format($t->nominal, 0, ',', '.');
            }
            $html .= '</td>';
            $html .= '<td data-label="Aksi" class="text-end action-icons">';
            if ($isLoan) {
                $html .= '<i class="fas fa-hand-holding-usd loan-flag-icon me-2" title="Kasbon"></i>';
            } elseif ($isLoanCancelled) {
                $html .= '<i class="fas fa-hand-holding-usd text-danger me-2 loan-cancel-info" data-id="' . $t->id . '" title="Kasbon dibatalkan" style="cursor:pointer"></i>';
            }
            $html .= '<i class="fas fa-edit text-primary edit-transaksi" data-id="' . $t->id . '" role="button"></i>';
            $html .= '<i class="fas fa-trash text-danger ms-2 delete-transaksi" data-id="' . $t->id . '" role="button"></i>';
            $html .= '<i class="fas fa-ellipsis-v text-secondary ms-2 toggle-detail" role="button"></i>';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $total_pages = max(1, (int) ceil($total_rows / $limit));
        $pagination = '';
        $pagination .= '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '"><button type="button" class="page-link" data-page="' . max(1, $page - 1) . '">Prev</button></li>';
        $last_printed = 0;
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2) {
                if ($last_printed && $i > $last_printed + 1) {
                    $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                $pagination .= '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><button type="button" class="page-link" data-page="' . $i . '">' . $i . '</button></li>';
                $last_printed = $i;
            }
        }
        $pagination .= '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '"><button type="button" class="page-link" data-page="' . min($total_pages, $page + 1) . '">Next</button></li>';

        echo json_encode([
            'status' => true,
            'html' => $html,
            'pagination' => $pagination,
            'page' => $page,
            'total_pages' => $total_pages,
        ]);
    }

    public function cek_duplikat() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $deskripsi = trim($this->input->post('deskripsi'));
        $nominal = $this->input->post('nominal');
        $kategori_id = $this->input->post('kategori_id');
        $wallet_id = $this->input->post('wallet_id');
        $tanggal = $this->input->post('tanggal');

        $exists = $this->db->from('jurnal_new_transactions')
            ->where('user_id', $user_id)
            ->where('deskripsi', $deskripsi)
            ->where('nominal', $nominal)
            ->where('category_id', $kategori_id)
            ->where('wallet_id', $wallet_id)
            ->where('tanggal', $tanggal)
            ->get()
            ->row();

        echo json_encode([
            'status' => true,
            'duplicate' => $exists ? true : false,
            'message' => $exists ? 'Transaksi sudah ada' : ''
        ]);
    }

    protected function parse_id_list($value) {
        if (is_array($value)) {
            $ids = $value;
        } else {
            $ids = preg_split('/[, ]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        })));
    }
    public function simpan() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        
        // Validasi data
        $kategori_id = $this->input->post('kategori_id');
        $wallet_id = $this->input->post('wallet_id');
        $wallet_tujuan_id = $this->input->post('wallet_tujuan_id');
        $deskripsi = trim($this->input->post('deskripsi'));
        $nominal = str_replace(['Rp', '.', ' '], '', $this->input->post('nominal'));
        $tipe = $this->input->post('tipe');
        $fee = str_replace(['Rp', '.', ' '], '', $this->input->post('fee'));
        $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
        $catatan = $this->input->post('catatan');
        $nama_transaksi = trim($this->input->post('nama_transaksi'));
        $is_loan = $this->input->post('is_loan') == 1 ? 1 : 0;
        $partner_id = $this->input->post('partner_id');
        $current_user = $this->M_user->get_by_id($user_id);
        $loan_relation = null;
        
        if (!in_array($tipe, ['pemasukan', 'pengeluaran', 'transfer'], true)) {
            echo json_encode(['status' => false, 'message' => 'Tipe transaksi tidak valid']);
            return;
        }

        if ($tipe !== 'transfer' && empty($kategori_id)) {
            echo json_encode(['status' => false, 'message' => 'Kategori wajib dipilih']);
            return;
        }
        
        if (empty($wallet_id)) {
            echo json_encode(['status' => false, 'message' => 'Dompet wajib dipilih']);
            return;
        }
        
        if ($tipe !== 'transfer' && empty($deskripsi)) {
            echo json_encode(['status' => false, 'message' => 'Deskripsi wajib diisi']);
            return;
        }
        
        if (empty($nominal) || $nominal <= 0) {
            echo json_encode(['status' => false, 'message' => 'Nominal wajib diisi']);
            return;
        }

        if ($tipe === 'transfer') {
            $is_loan = 0;
            $partner_id = null;
            if (empty($wallet_tujuan_id)) {
                echo json_encode(['status' => false, 'message' => 'Dompet tujuan wajib dipilih']);
                return;
            }

            if ((int) $wallet_id === (int) $wallet_tujuan_id) {
                echo json_encode(['status' => false, 'message' => 'Dompet asal dan tujuan harus berbeda']);
                return;
            }

            $kategori_id = $this->M_transaksi->get_transfer_category_id();
            $deskripsi = $this->build_transfer_description($user_id, $wallet_id, $wallet_tujuan_id);
            if ($nama_transaksi !== '') {
                $deskripsi .= ' ' . $nama_transaksi;
            }
        } else {
            if ($tipe !== 'pengeluaran') {
                $is_loan = 0;
                $partner_id = null;
            } elseif ($is_loan) {
                $loan_relation = $this->get_current_loan_parent_relation($user_id);
                if (!$loan_relation) {
                    echo json_encode(['status' => false, 'message' => 'Kasbon hanya tersedia jika ada parent kasbon di profil']);
                    return;
                }

                if (!empty($partner_id) && (int) $partner_id !== (int) $loan_relation->id && (int) $partner_id !== (int) $loan_relation->user_parents && (int) $partner_id !== (int) $loan_relation->user_child) {
                    echo json_encode(['status' => false, 'message' => 'Partner kasbon harus parent yang dipilih di profil']);
                    return;
                }

                $partner_id = (int) $loan_relation->id;
            } else {
                $partner_id = null;
            }
        }
        
        $data = [
            'user_id' => $user_id,
            'wallet_id' => $wallet_id,
            'category_id' => $kategori_id,
            'wallet_tujuan_id' => $tipe === 'transfer' ? $wallet_tujuan_id : null,
            'nominal' => $nominal,
            'fee' => $tipe === 'transfer' ? ($fee !== '' ? $fee : 0) : 0,
            'tipe' => $tipe,
            'deskripsi' => $deskripsi,
            'tanggal' => $tanggal,
            'catatan' => $catatan,
            'is_loan' => $is_loan,
            'partner_id' => $is_loan ? $partner_id : null
        ];
        
        if (!$this->validate_wallet_not_negative($user_id, $data)) {
            echo json_encode(['status' => false, 'message' => 'Saldo dompet tidak cukup. Transaksi tidak boleh membuat saldo minus']);
            return;
        }

        $limit_error = $this->validate_transaction_limits($current_user, $data);
        if ($limit_error !== null) {
            echo json_encode(['status' => false, 'message' => $limit_error]);
            return;
        }
        
        $id = $this->M_transaksi->insert($data);
        
        echo json_encode(['status' => true, 'message' => 'Transaksi berhasil disimpan', 'id' => $id]);
    }

    public function edit() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        $old = $this->M_transaksi->get_by_id($id, $user_id);
        $tipe = $this->input->post('tipe') ?: ($old ? $old->tipe : 'pengeluaran');
        $wallet_id = $this->input->post('wallet_id');
        $wallet_tujuan_id = $this->input->post('wallet_tujuan_id');
        $fee = str_replace(['Rp', '.', ' '], '', $this->input->post('fee'));
        $nama_transaksi = trim($this->input->post('nama_transaksi'));
        $is_loan = $this->input->post('is_loan') == 1 ? 1 : 0;
        $partner_id = $this->input->post('partner_id');
        $current_user = $this->M_user->get_by_id($user_id);
        $loan_relation = null;

        if ($tipe === 'transfer') {
            $is_loan = 0;
            $partner_id = null;
            if (empty($wallet_tujuan_id)) {
                echo json_encode(['status' => false, 'message' => 'Dompet tujuan wajib dipilih']);
                return;
            }

            if ((int) $wallet_id === (int) $wallet_tujuan_id) {
                echo json_encode(['status' => false, 'message' => 'Dompet asal dan tujuan harus berbeda']);
                return;
            }

            $deskripsi = $this->build_transfer_description($user_id, $wallet_id, $wallet_tujuan_id);
            if ($nama_transaksi !== '') {
                $deskripsi .= ' ' . $nama_transaksi;
            }
        } else {
            if ($tipe !== 'pengeluaran') {
                $is_loan = 0;
                $partner_id = null;
            } elseif ($is_loan) {
                $loan_relation = $this->get_current_loan_parent_relation($user_id);
                if (!$loan_relation) {
                    echo json_encode(['status' => false, 'message' => 'Kasbon hanya tersedia jika ada parent kasbon di profil']);
                    return;
                }

                if (!empty($partner_id) && (int) $partner_id !== (int) $loan_relation->id && (int) $partner_id !== (int) $loan_relation->user_parents && (int) $partner_id !== (int) $loan_relation->user_child) {
                    echo json_encode(['status' => false, 'message' => 'Partner kasbon harus parent yang dipilih di profil']);
                    return;
                }

                $partner_id = (int) $loan_relation->id;
            } else {
                $partner_id = null;
            }
        }
        
        $data = [
            'wallet_id' => $wallet_id,
            'wallet_tujuan_id' => $tipe === 'transfer' ? $wallet_tujuan_id : null,
            'category_id' => $tipe === 'transfer' ? $this->M_transaksi->get_transfer_category_id() : $this->input->post('category_id'),
            'nominal' => str_replace(['Rp', '.', ' '], '', $this->input->post('nominal')),
            'fee' => $tipe === 'transfer' ? ($fee !== '' ? $fee : 0) : 0,
            'tipe' => $tipe,
            'deskripsi' => trim($this->input->post('deskripsi')),
            'tanggal' => $this->input->post('tanggal'),
            'catatan' => $this->input->post('catatan'),
            'is_loan' => $is_loan,
            'partner_id' => $is_loan ? $partner_id : null
        ];

        if (!$this->validate_wallet_not_negative($user_id, $data, $old)) {
            echo json_encode(['status' => false, 'message' => 'Saldo dompet tidak cukup. Transaksi tidak boleh membuat saldo minus']);
            return;
        }

        $limit_error = $this->validate_transaction_limits($current_user, $data, $old);
        if ($limit_error !== null) {
            echo json_encode(['status' => false, 'message' => $limit_error]);
            return;
        }
        
        $this->M_transaksi->update($id, $data, $user_id);
        
        echo json_encode(['status' => true, 'message' => 'Transaksi berhasil diupdate']);
    }

    private function validate_transaction_limits($user, $data, $old = null) {
        if (!$user || ($user->role ?? 'user') === 'admin') return null;
        $amount = (float) ($data['nominal'] ?? 0) + (float) ($data['fee'] ?? 0);
        $per_transaction = (float) ($user->batas_transaksi ?? 100000000);
        $total_limit = (float) ($user->batas_total_transaksi ?? 100000000);
        if ($amount > $per_transaction) {
            return 'Nominal transaksi melebihi batas per transaksi: Rp ' . number_format($per_transaction, 0, ',', '.');
        }
        $current_total = $this->M_transaksi->get_total_nominal($user->id, $old ? $old->id : null);
        if (($current_total + $amount) > $total_limit) {
            return 'Total akumulasi transaksi melebihi batas: Rp ' . number_format($total_limit, 0, ',', '.');
        }
        return null;
    }

    public function hapus() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $this->M_transaksi->delete($id, $user_id);
        
        echo json_encode(['status' => true, 'message' => 'Transaksi berhasil dihapus']);
    }

    public function get_data() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        
        $transaksi = $this->M_transaksi->get_by_id($id, $user_id);
        
        if ($transaksi) {
            if ((int) ($transaksi->is_loan ?? 0) === 1 && !empty($transaksi->partner_id)) {
                $partner_relation = $this->M_partner->get_by_id($transaksi->partner_id);
                if ($partner_relation && $partner_relation->status === 'approved') {
                    $transaksi->loan_partner_id = $transaksi->partner_id;
                    $transaksi->partner_id = ((int) $partner_relation->user_parents === (int) $user_id) ? $partner_relation->user_child : $partner_relation->user_parents;
                }
            }
            
            $transaksi->nama_transaksi = '';
            if ($transaksi->tipe === 'transfer') {
                $base = $this->build_transfer_description($user_id, $transaksi->wallet_id, $transaksi->wallet_tujuan_id);
                $extra = trim(substr($transaksi->deskripsi, strlen($base)));
                $transaksi->nama_transaksi = $extra;
            }
            
            echo json_encode(['status' => true, 'data' => $transaksi]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
        }
    }

    public function refresh() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $tanggal_mulai = $this->input->post('start') ?: date('Y-m-01');
        $tanggal_akhir = $this->input->post('end') ?: date('Y-m-d');
        $keyword = trim($this->input->post('q'));
        $tipe = $this->input->post('tipe');
        if ($tipe && !in_array($tipe, ['pemasukan', 'pengeluaran', 'transfer'], true)) {
            $tipe = null;
        }
        $kategori_id = $this->input->post('kategori_id');
        $wallet_id = $this->input->post('wallet_id');
        $page = max(1, (int) $this->input->post('page'));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $total_rows = $this->M_transaksi->count_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $keyword, $kategori_id, $wallet_id, $tipe);
        $loan_summary = $this->M_dompet->get_wallet_summary_loan($user_id);
        $has_loan_relation = !empty($this->get_loan_parent_partner($user_id));

        $transaksi = $this->M_transaksi->get_by_period($user_id, $tanggal_mulai, $tanggal_akhir, $limit, $offset, $keyword, $kategori_id, $wallet_id, $tipe);
        $total_pages = max(1, (int) ceil($total_rows / $limit));

        $list = [];
        foreach ($transaksi as $t) {
            $list[] = [
                'id' => (int) $t->id,
                'tanggal' => $t->tanggal,
                'deskripsi' => $t->deskripsi,
                'tipe' => $t->tipe,
                'nominal' => (float) $t->nominal,
                'fee' => (float) ($t->fee ?? 0),
                'kategori' => $t->tipe === 'transfer' ? 'Transfer' : ($t->nama_kategori ?? '-'),
                'dompet' => $t->tipe === 'transfer' ? ($t->nama_wallet . ' -> ' . ($t->nama_wallet_tujuan ?? '-')) : $t->nama_wallet,
                'nama_wallet' => $t->nama_wallet,
                'is_loan' => (int) ($t->is_loan ?? 0)
            ];
        }

        echo json_encode([
            'status' => true,
            'stats' => [
                'pemasukan_hari_ini' => (float) $this->M_transaksi->get_total_by_tipe_hari_ini($user_id, 'pemasukan'),
                'pengeluaran_hari_ini' => (float) $this->M_transaksi->get_total_by_tipe_hari_ini($user_id, 'pengeluaran'),
                'pemasukan_bulan_ini' => (float) $this->M_transaksi->get_total_by_tipe_bulan_ini($user_id, 'pemasukan'),
                'pengeluaran_bulan_ini' => (float) $this->M_transaksi->get_total_by_tipe_bulan_ini($user_id, 'pengeluaran'),
                'loan_net' => (float) ($loan_summary->net_loan ?? 0),
                'has_loan_summary' => $has_loan_relation || (
                    (float) ($loan_summary->total_loan_out ?? 0) > 0 ||
                    (float) ($loan_summary->total_loan_in ?? 0) > 0 ||
                    (float) ($loan_summary->net_loan ?? 0) !== 0.0
                )
            ],
            'transaksi' => $list,
            'pagination' => [
                'page' => $page,
                'total_pages' => $total_pages,
                'start' => $tanggal_mulai,
                'end' => $tanggal_akhir,
                'q' => $keyword,
                'tipe' => $tipe,
                'kategori_id' => $kategori_id,
                'wallet_id' => $wallet_id
            ]
        ]);
    }

    public function suggest() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $keyword = trim($this->input->post('keyword'));
        $tipe = $this->input->post('tipe');
        if (strlen($keyword) < 2) {
            echo json_encode(['status' => true, 'data' => []]);
            return;
        }

        $user_id = $this->session->userdata('user_id');
        echo json_encode([
            'status' => true,
            'data' => $this->M_transaksi->suggest_deskripsi($user_id, $keyword, 8, $tipe)
        ]);
    }
}
