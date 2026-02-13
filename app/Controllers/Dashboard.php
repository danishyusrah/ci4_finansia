<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\WalletModel;
use App\Models\CategoryModel;

class Dashboard extends BaseController
{
    protected $transactionModel;
    protected $walletModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->walletModel = new WalletModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        // Ambil ID User dari Session (agar data dinamis per user)
        $userId = session()->get('id');

        // 1. Ambil Data Saldo Kas (Real)
        $totalKas = $this->transactionModel->getSaldoKas($userId);

        // 2. Ambil Data Modal Terkunci (Dari tabel Users)
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRow();
        $modalTerkunci = $user->locked_capital_amount ?? 0;
        
        // 3. Hitung Laba Bersih (Safe to Spend)
        $labaBersih = $totalKas - $modalTerkunci;

        // 4. Ambil Transaksi Terakhir
        $recentTrx = $this->transactionModel->getRecentTransactions($userId, 5);

        // 5. Kirim semua data ke View
        $data = [
            'title'          => 'Dashboard Keuangan',
            'user_name'      => session()->get('name'), // Ambil nama dari session
            'total_kas'      => $totalKas,
            'modal_terkunci' => $modalTerkunci,
            'laba_bersih'    => $labaBersih,
            'recent_trx'     => $recentTrx,
            // Kirim data dompet & kategori untuk Modal Tambah Transaksi
            'wallets'        => $this->walletModel->where('user_id', $userId)->findAll(),
            'categories'     => $this->categoryModel->where('user_id', $userId)->findAll()
        ];

        return view('dashboard', $data);
    }

    /**
     * Method untuk memproses Input Transaksi Baru + Upload Bukti
     */
    public function save_transaction()
    {
        $userId = session()->get('id');

        // Validasi Input + File Attachment
        $rules = [
            'amount' => 'required|numeric',
            'attachment' => [
                'label' => 'Bukti Struk',
                'rules' => 'max_size[attachment,2048]|is_image[attachment]|mime_in[attachment,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran file terlalu besar (Maks 2MB)',
                    'is_image' => 'File harus berupa gambar',
                    'mime_in'  => 'Format harus JPG atau PNG'
                ]
            ]
        ];

        // Jika validasi gagal
        if (!$this->validate($rules)) {
            // Ambil error pertama saja agar rapi
            $errors = $this->validator->getErrors();
            $firstError = reset($errors); 
            return redirect()->to('/dashboard')->with('error', 'Gagal: ' . $firstError);
        }

        // Handle File Upload
        $file = $this->request->getFile('attachment');
        $fileName = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Generate nama acak agar tidak bentrok
            $fileName = $file->getRandomName();
            // Pindahkan ke folder public/uploads/struk
            // Pastikan folder ini ada dan writable
            $file->move('uploads/struk', $fileName);
        }

        // Siapkan Data untuk Disimpan
        $data = [
            'user_id'          => $userId,
            'amount'           => $this->request->getPost('amount'),
            'type'             => $this->request->getPost('type'), // income / expense
            'category_id'      => $this->request->getPost('category_id'),
            'wallet_id'        => $this->request->getPost('wallet_id'),
            'description'      => $this->request->getPost('description'),
            'attachment'       => $fileName, // Simpan nama file foto
            'transaction_date' => date('Y-m-d H:i:s'),
        ];

        try {
            // Coba simpan (Model akan otomatis menjalankan logika "Anti Boncos" & Update Saldo)
            $this->transactionModel->insert($data);
            
            return redirect()->to('/dashboard')->with('success', 'Transaksi berhasil disimpan!');
            
        } catch (\Exception $e) {
            // Jika ditolak oleh logic "Anti Boncos" (Exception dilempar dari Model)
            return redirect()->to('/dashboard')->with('error', $e->getMessage());
        }
    }

    /**
     * Fitur Export Excel Native (Tanpa Library)
     * Mengubah data transaksi menjadi file .xls yang bisa dibuka di Excel
     */
    public function export_excel()
    {
        $userId = session()->get('id');

        // Ambil data transaksi lengkap
        $transactions = $this->transactionModel
            ->select('transactions.*, categories.name as category_name, wallets.name as wallet_name')
            ->join('categories', 'categories.id = transactions.category_id')
            ->join('wallets', 'wallets.id = transactions.wallet_id')
            ->where('transactions.user_id', $userId)
            ->orderBy('transaction_date', 'DESC')
            ->findAll();

        // Nama File
        $fileName = 'Laporan_Keuangan_' . date('Y-m-d') . '.xls';

        // Set Headers agar browser mengenali ini sebagai file Excel
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        // Isi Konten Excel (Format HTML Table yang dikenali Excel)
        echo "
        <table border='1'>
            <thead>
                <tr style='background-color: #f2f2f2; font-weight: bold;'>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Kategori</th>
                    <th>Dompet</th>
                    <th>Tipe</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($transactions as $row) {
            $warnaTipe = ($row['type'] == 'income') ? 'blue' : 'red';
            $tipeText = ($row['type'] == 'income') ? 'Pemasukan' : 'Pengeluaran';
            
            echo "
                <tr>
                    <td>" . $row['transaction_date'] . "</td>
                    <td>" . $row['description'] . "</td>
                    <td>" . $row['category_name'] . "</td>
                    <td>" . $row['wallet_name'] . "</td>
                    <td style='color: $warnaTipe'>$tipeText</td>
                    <td style='text-align: right;'>" . $row['amount'] . "</td>
                </tr>";
        }

        echo "</tbody></table>";
        exit;
    }

    /**
     * Fitur Export PDF dengan Tampilan Cetak
     */
    public function export_pdf()
    {
        $userId = session()->get('id');
        
        // Ambil Filter Tanggal
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');

        // Query Data Transaksi Berdasarkan Tanggal
        $transactions = $this->transactionModel
            ->select('transactions.*, categories.name as category_name, wallets.name as wallet_name')
            ->join('categories', 'categories.id = transactions.category_id')
            ->join('wallets', 'wallets.id = transactions.wallet_id')
            ->where('transactions.user_id', $userId)
            ->where('transaction_date >=', $startDate)
            ->where('transaction_date <=', $endDate)
            ->orderBy('transaction_date', 'ASC') // Urutkan dari tanggal lama ke baru
            ->findAll();

        // Data untuk View
        $data = [
            'transactions' => $transactions,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'user_name'    => session()->get('name'),
            'business_name'=> session()->get('business_name')
        ];

        // Load View Khusus PDF (Print View)
        return view('export/pdf_view', $data);
    }
}