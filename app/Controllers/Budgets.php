<?php

namespace App\Controllers;

use App\Models\BudgetModel;
use App\Models\CategoryModel;
use App\Models\TransactionModel;

class Budgets extends BaseController
{
    protected $budgetModel;
    protected $categoryModel;
    protected $transactionModel;

    public function __construct()
    {
        $this->budgetModel = new BudgetModel();
        $this->categoryModel = new CategoryModel();
        $this->transactionModel = new TransactionModel();
    }

    public function index()
    {
        $userId = session()->get('id');

        // 1. Ambil semua anggaran yang diset user
        $budgets = $this->budgetModel
            ->select('budgets.*, categories.name as category_name')
            ->join('categories', 'categories.id = budgets.category_id')
            ->where('budgets.user_id', $userId)
            ->findAll();

        // 2. Hitung realisasi pengeluaran bulan ini untuk setiap anggaran
        $dataBudgets = [];
        foreach ($budgets as $b) {
            $spent = $this->transactionModel
                ->where('user_id', $userId)
                ->where('category_id', $b['category_id'])
                ->where('type', 'expense')
                ->where('MONTH(transaction_date)', date('m'))
                ->where('YEAR(transaction_date)', date('Y'))
                ->selectSum('amount')
                ->first();
            
            $realization = $spent['amount'] ?? 0;
            $percentage = ($realization / $b['amount_limit']) * 100;

            $dataBudgets[] = [
                'id' => $b['id'],
                'category_name' => $b['category_name'],
                'limit' => $b['amount_limit'],
                'spent' => $realization,
                'percentage' => $percentage
            ];
        }

        // Ambil kategori expense yang belum punya budget (untuk dropdown tambah)
        // Logika: Ambil semua expense user, filter yg ID-nya tidak ada di tabel budgets
        $allCategories = $this->categoryModel->where('user_id', $userId)->where('type', 'expense')->findAll();
        // (Simplifikasi: Tampilkan semua kategori expense di dropdown)

        $data = [
            'title' => 'Anggaran Belanja (Budgeting)',
            'budgets' => $dataBudgets,
            'categories' => $allCategories
        ];

        return view('budgets/index', $data);
    }

    public function save()
    {
        $userId = session()->get('id');
        $categoryId = $this->request->getPost('category_id');
        $amount = $this->request->getPost('amount_limit');

        // Cek apakah budget untuk kategori ini sudah ada?
        $exist = $this->budgetModel->where('user_id', $userId)->where('category_id', $categoryId)->first();

        if ($exist) {
            // Update
            $this->budgetModel->update($exist['id'], ['amount_limit' => $amount]);
        } else {
            // Insert Baru
            $this->budgetModel->insert([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'amount_limit' => $amount
            ]);
        }

        return redirect()->to('/budgets')->with('success', 'Anggaran berhasil disimpan.');
    }

    public function delete($id)
    {
        $userId = session()->get('id');
        // Pastikan punya user
        $check = $this->budgetModel->where('id', $id)->where('user_id', $userId)->first();
        if($check) {
            $this->budgetModel->delete($id);
        }
        return redirect()->to('/budgets')->with('success', 'Anggaran dihapus.');
    }
}