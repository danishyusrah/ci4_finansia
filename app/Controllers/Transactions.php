<?php

namespace App\Controllers;

use App\Models\TransactionModel;

class Transactions extends BaseController
{
    protected $transactionModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
    }

    public function index()
    {
        $userId = session()->get('id');

        // Ambil filter dari request (jika ada)
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01'); // Default awal bulan ini
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');    // Default hari ini

        // Query Data dengan Filter
        $transactions = $this->transactionModel
            ->select('transactions.*, categories.name as category_name, wallets.name as wallet_name')
            ->join('categories', 'categories.id = transactions.category_id')
            ->join('wallets', 'wallets.id = transactions.wallet_id')
            ->where('transactions.user_id', $userId)
            ->where('transaction_date >=', $startDate)
            ->where('transaction_date <=', $endDate)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title'        => 'Riwayat Transaksi',
            'transactions' => $transactions,
            'start_date'   => $startDate,
            'end_date'     => $endDate
        ];

        return view('transactions/index', $data);
    }
    
    // Fitur Hapus Transaksi (Opsional namun penting)
    public function delete($id)
    {
        // Pastikan hanya milik user yang sedang login
        $trx = $this->transactionModel->find($id);
        if ($trx) {
             // Logic update saldo saat delete ada di Model (afterDelete) 
             // atau kita manualkan di sini jika model belum support revert
             
             // Untuk keamanan saldo, kita lakukan manual revert saldo di sini saja agar pasti
             $db = \Config\Database::connect();
             $operator = ($trx['type'] == 'income') ? '-' : '+'; // Kebalikan
             $db->query("UPDATE wallets SET balance = balance $operator ? WHERE id = ?", [$trx['amount'], $trx['wallet_id']]);

             $this->transactionModel->delete($id);
             return redirect()->to('/transactions')->with('success', 'Transaksi berhasil dihapus.');
        }
        
        return redirect()->to('/transactions')->with('error', 'Transaksi tidak ditemukan.');
    }
}