<?php

namespace App\Controllers;

use App\Models\TransactionModel;

class Reports extends BaseController
{
    protected $transactionModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
    }

    public function index()
    {
        $userId = session()->get('id');

        // Filter Bulan & Tahun (Default: Bulan Ini)
        $month = $this->request->getGet('month') ?? date('m');
        $year  = $this->request->getGet('year') ?? date('Y');

        // 1. Ambil Total Pemasukan
        $incomeTotal = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->where('MONTH(transaction_date)', $month)
            ->where('YEAR(transaction_date)', $year)
            ->selectSum('amount')
            ->first();

        // 2. Ambil Total Pengeluaran (Operasional + HPP) - KECUALI Prive
        // Kita exclude Prive dari perhitungan Laba Rugi Bisnis karena Prive itu pengambilan modal, bukan biaya bisnis.
        $expenseTotal = $this->transactionModel
            ->join('categories', 'categories.id = transactions.category_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->where('categories.is_prive', 0) // HANYA Biaya Bisnis Murni
            ->where('MONTH(transaction_date)', $month)
            ->where('YEAR(transaction_date)', $year)
            ->selectSum('amount')
            ->first();
            
        // 3. Ambil Detail Pengeluaran per Kategori (Untuk Breakdown)
        $expenseBreakdown = $this->transactionModel
            ->select('categories.name as category_name, SUM(transactions.amount) as total')
            ->join('categories', 'categories.id = transactions.category_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->where('categories.is_prive', 0)
            ->where('MONTH(transaction_date)', $month)
            ->where('YEAR(transaction_date)', $year)
            ->groupBy('categories.id')
            ->orderBy('total', 'DESC')
            ->findAll();

        $income = $incomeTotal['amount'] ?? 0;
        $expense = $expenseTotal['amount'] ?? 0;
        $netProfit = $income - $expense;

        $data = [
            'title'       => 'Laporan Laba Rugi',
            'month'       => $month,
            'year'        => $year,
            'income'      => $income,
            'expense'     => $expense,
            'net_profit'  => $netProfit,
            'breakdown'   => $expenseBreakdown
        ];

        return view('reports/profit_loss', $data);
    }
}