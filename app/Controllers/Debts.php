<?php

namespace App\Controllers;

use App\Models\DebtModel;
use App\Models\TransactionModel;
use App\Models\WalletModel;
use App\Models\CategoryModel;

class Debts extends BaseController
{
    protected $debtModel;
    protected $transactionModel;
    protected $walletModel;

    public function __construct()
    {
        $this->debtModel = new DebtModel();
        $this->transactionModel = new TransactionModel();
        $this->walletModel = new WalletModel();
    }

    public function index()
    {
        $userId = session()->get('id');

        // Pisahkan Hutang (Payable) dan Piutang (Receivable)
        $piutang = $this->debtModel->where('user_id', $userId)->where('type', 'receivable')->orderBy('status', 'ASC')->findAll();
        $hutang  = $this->debtModel->where('user_id', $userId)->where('type', 'payable')->orderBy('status', 'ASC')->findAll();

        $data = [
            'title'   => 'Catatan Hutang & Piutang',
            'piutang' => $piutang,
            'hutang'  => $hutang,
            'wallets' => $this->walletModel->where('user_id', $userId)->findAll() // Untuk modal bayar
        ];

        return view('debts/index', $data);
    }

    public function save()
    {
        $userId = session()->get('id');
        
        $data = [
            'user_id'     => $userId,
            'type'        => $this->request->getPost('type'),
            'party_name'  => $this->request->getPost('party_name'),
            'amount'      => $this->request->getPost('amount'),
            'due_date'    => $this->request->getPost('due_date'),
            'description' => $this->request->getPost('description'),
            'status'      => 'unpaid'
        ];

        $this->debtModel->insert($data);
        return redirect()->to('/debts')->with('success', 'Catatan berhasil disimpan.');
    }

    // Fitur PRO: Bayar/Lunasi Hutang Otomatis Catat Transaksi
    public function mark_paid($id)
    {
        $userId = session()->get('id');
        $debt = $this->debtModel->where('id', $id)->where('user_id', $userId)->first();
        
        if (!$debt) return redirect()->back()->with('error', 'Data tidak ditemukan.');
        if ($debt['status'] == 'paid') return redirect()->back()->with('error', 'Sudah lunas.');

        $walletId = $this->request->getPost('wallet_id'); // Dompet yang dipakai bayar/terima

        // 1. Update Status Hutang -> Paid
        $this->debtModel->update($id, ['status' => 'paid', 'updated_at' => date('Y-m-d H:i:s')]);

        // 2. Buat Transaksi Otomatis di Laporan Keuangan
        // Jika Piutang (Receivable) Dibayar -> Uang Masuk (Income)
        // Jika Hutang (Payable) Dibayar -> Uang Keluar (Expense)
        
        $trxType = ($debt['type'] == 'receivable') ? 'income' : 'expense';
        
        // Cari/Buat kategori otomatis "Pelunasan Hutang/Piutang"
        $catModel = new \App\Models\CategoryModel();
        $catName = ($debt['type'] == 'receivable') ? 'Pelunasan Piutang' : 'Pembayaran Hutang';
        $category = $catModel->where('user_id', $userId)->where('name', $catName)->first();
        
        if (!$category) {
            $catModel->insert(['user_id' => $userId, 'name' => $catName, 'type' => $trxType, 'is_prive' => 0]);
            $categoryId = $catModel->getInsertID();
        } else {
            $categoryId = $category['id'];
        }

        $trxData = [
            'user_id'          => $userId,
            'wallet_id'        => $walletId,
            'category_id'      => $categoryId,
            'amount'           => $debt['amount'],
            'type'             => $trxType,
            'description'      => "Pelunasan: " . $debt['party_name'] . " (" . $debt['description'] . ")",
            'transaction_date' => date('Y-m-d H:i:s')
        ];

        // Simpan transaksi (Model Transaksi akan otomatis update saldo dompet)
        $this->transactionModel->insert($trxData);

        return redirect()->to('/debts')->with('success', 'Berhasil dilunasi! Saldo dompet otomatis diperbarui.');
    }
}