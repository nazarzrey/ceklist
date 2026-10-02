<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_budget extends CI_Model {

    private $table_budgets = 'jurnal_new_budgets';
    private $table_categories = 'jurnal_new_categories';

    public function get_by_user_bulan($user_id, $bulan, $tahun) {
        return $this->db->select('b.*, c.nama_kategori, c.icon')
            ->from($this->table_budgets.' b')
            ->join($this->table_categories.' c', 'c.id = b.category_id')
            ->where('b.user_id', $user_id)
            ->where('b.bulan', $bulan)
            ->where('b.tahun', $tahun)
            ->get()
            ->result();
    }

    public function get_or_create($user_id, $category_id, $bulan, $tahun) {
        $budget = $this->db->where('user_id', $user_id)
            ->from($this->table_budgets)
            ->where('category_id', $category_id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->row();
        
        if (!$budget) {
            $data = [
                'user_id' => $user_id,
                'category_id' => $category_id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nominal_anggaran' => 0
            ];
            $this->db->insert($this->table_budgets, $data);
            $budget = (object) array_merge($data, ['id' => $this->db->insert_id()]);
        }
        
        return $budget;
    }

    public function set_budget($user_id, $category_id, $bulan, $tahun, $nominal) {
        $exists = $this->db->where('user_id', $user_id)
            ->from($this->table_budgets)
            ->where('category_id', $category_id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->row();
        
        if ($exists) {
            $this->db->where('id', $exists->id)->update($this->table_budgets, ['nominal_anggaran' => $nominal]);
        } else {
            $this->db->insert($this->table_budgets, [
                'user_id' => $user_id,
                'category_id' => $category_id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nominal_anggaran' => $nominal
            ]);
        }
        
        return true;
    }

    public function get_budget_alert($user_id) {
        $bulan_ini = date('n');
        $tahun_ini = date('Y');
        
        $budgets = $this->get_by_user_bulan($user_id, $bulan_ini, $tahun_ini);
        $alerts = [];
        
        foreach ($budgets as $b) {
            if ($b->nominal_anggaran > 0) {
                // Get real spending for this category this month
                $terpakai = $this->db->select('SUM(IFNULL(nominal,0)) as nominal', false)
                    ->where('user_id', $user_id)
                    ->where('category_id', $b->category_id)
                    ->where('tipe', 'pengeluaran')
                    ->where('MONTH(tanggal)', $bulan_ini)
                    ->where('YEAR(tanggal)', $tahun_ini)
                    ->get('jurnal_new_transactions')
                    ->row()
                    ->nominal ?? 0;
                
                $persen = ($terpakai / $b->nominal_anggaran) * 100;
                
                if ($persen >= 80) {
                    $alerts[] = (object) [
                        'kategori' => $b->nama_kategori,
                        'anggaran' => $b->nominal_anggaran,
                        'terpakai' => $terpakai,
                        'persen' => round($persen)
                    ];
                }
            }
        }
        
        return $alerts;
    }
}
