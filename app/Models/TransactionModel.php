<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    // Pastikan 'attachment' ada di sini agar file foto bisa disimpan
    protected $allowedFields    = [
        'user_id', 
        'wallet_id', 
        'category_id', 
        'amount', 
        'type', 
        'description', 
        'attachment', // Field baru untuk bukti struk/nota
        'transaction_date', 
        'created_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // Tidak butuh update field untuk transaksi (biasanya immutable)

    // Callbacks: Logika Otomatis berjalan saat Save/Delete
    protected $beforeInsert = ['checkModalSafety'];
    protected $afterInsert  = ['updateWalletBalance'];
    
    // Note: Untuk delete, kita menangani update saldo manual di Controller 
    // agar lebih aman dan terkontrol (lihat Transactions Controller -> delete)

    /**
     * Fitur Proteksi Modal (Anti Boncos)
     * Mencegah pengambilan Prive jika (Total Uang - Modal Terkunci) < Jumlah Ambil
     */
    protected function checkModalSafety(array $data)
    {
        $db = \Config\Database::connect();
        
        // 1. Cek apakah kategori transaksi ini adalah "PRIVE" (Pengambilan Pribadi)
        // Kita perlu query manual karena $data['data'] hanya berisi ID kategori
        $category = $db->table('categories')->where('id', $data['data']['category_id'])->get()->getRow();
        
        if ($category && $category->is_prive == 1) {
            $userId = $data['data']['user_id'];
            $amountToTake = $data['data']['amount'];

            // 2. Ambil Total Uang Cash saat ini (Semua Dompet User Ini)
            $queryWallet = $db->table('wallets')->selectSum('balance')->where('user_id', $userId)->get()->getRow();
            $totalCash = $queryWallet->balance ?? 0;

            // 3. Ambil Batas Modal Terkunci (Locked Capital) dari User
            $user = $db->table('users')->where('id', $userId)->get()->getRow();
            $lockedCapital = $user->locked_capital_amount ?? 0;

            // 4. Hitung Uang Bebas (Free Cash / Laba Ditahan)
            $freeCash = $totalCash - $lockedCapital;

            // 5. Validasi: Jika uang bebas kurang dari yang mau diambil, TOLAK.
            if ($freeCash < $amountToTake) {
                // Pesan Error Keras untuk User
                throw new Exception("BAHAYA: Transaksi Ditolak! Anda mencoba mengambil Rp " . number_format($amountToTake) . 
                    ", padahal Sisa Laba Bersih yang aman hanya Rp " . number_format($freeCash) . 
                    ". Jangan ganggu Modal Usaha!");
            }
        }

        return $data;
    }

    /**
     * Otomatis Update Saldo Dompet setelah transaksi disimpan
     */
    protected function updateWalletBalance(array $data)
    {
        $db = \Config\Database::connect();
        $trx = $data['data']; // Data transaksi yang baru masuk
        
        // Logika Aritmatika Saldo
        // Jika Pemasukan (income) -> Saldo Nambah (+)
        // Jika Pengeluaran (expense) -> Saldo Berkurang (-)
        $operator = ($trx['type'] == 'income') ? '+' : '-';
        
        // Jalankan Query Update Saldo Dompet Terkait
        $sql = "UPDATE wallets SET balance = balance $operator ? WHERE id = ?";
        $db->query($sql, [$trx['amount'], $trx['wallet_id']]);

        return $data;
    }

    // --- Helper Functions untuk Dashboard & Laporan ---

    public function getSaldoKas($userId)
    {
        $db = \Config\Database::connect();
        $query = $db->table('wallets')->selectSum('balance')->where('user_id', $userId)->get()->getRow();
        return $query->balance ?? 0;
    }

    public function getLabaBersih($userId)
    {
        $totalKas = $this->getSaldoKas($userId);
        
        $db = \Config\Database::connect();
        $user = $db->table('users')->select('locked_capital_amount')->where('id', $userId)->get()->getRow();
        $modalTerkunci = $user->locked_capital_amount ?? 0;

        return $totalKas - $modalTerkunci;
    }
    
    public function getRecentTransactions($userId, $limit = 5)
    {
        return $this->select('transactions.*, categories.name as category_name, wallets.name as wallet_name')
                    ->join('categories', 'categories.id = transactions.category_id')
                    ->join('wallets', 'wallets.id = transactions.wallet_id')
                    ->where('transactions.user_id', $userId)
                    ->orderBy('transaction_date', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit);
    }
}