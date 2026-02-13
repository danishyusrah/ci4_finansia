<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\WalletModel;
use App\Models\CategoryModel;
use App\Models\DebtModel;

class Dashboard extends BaseController
{
    protected $transactionModel;
    protected $walletModel;
    protected $categoryModel;
    protected $debtModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->walletModel = new WalletModel();
        $this->categoryModel = new CategoryModel();
        // $this->debtModel = new DebtModel(); // Jika diperlukan nanti
    }

    public function index()
    {
        $userId = session()->get('id');

        // 1. Ambil Data Saldo Kas (Real)
        try {
            $totalKas = $this->transactionModel->getSaldoKas($userId);
        } catch (\Throwable $e) {
            $totalKas = 0; 
        }

        // 2. Ambil Data Modal Terkunci (Dari tabel Users)
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRow();
        $modalTerkunci = $user->locked_capital_amount ?? 0;
        
        // 3. Hitung Laba Bersih
        $labaBersih = $totalKas - $modalTerkunci;

        // 4. Ambil Transaksi Terakhir
        try {
            $recentTrx = $this->transactionModel->getRecentTransactions($userId, 5);
        } catch (\Throwable $e) {
            $recentTrx = $this->transactionModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->limit(5)->find();
        }

        // 5. Statistik Bulan Ini
        $currentMonth = date('m');
        $currentYear = date('Y');
        
        $incomeThisMonth = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->where('MONTH(created_at)', $currentMonth)
            ->where('YEAR(created_at)', $currentYear)
            ->selectSum('amount')
            ->first()['amount'] ?? 0;

        $expenseThisMonth = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->where('MONTH(created_at)', $currentMonth)
            ->where('YEAR(created_at)', $currentYear)
            ->selectSum('amount')
            ->first()['amount'] ?? 0;

        // Kirim data ke View
        $data = [
            'title'              => 'Dashboard Keuangan',
            'user_name'          => session()->get('name'),
            'total_kas'          => $totalKas,
            'modal_terkunci'     => $modalTerkunci,
            'laba_bersih'        => $labaBersih,
            'income_month'       => $incomeThisMonth,
            'expense_month'      => $expenseThisMonth,
            'recent_trx'         => $recentTrx,
            'wallets'            => $this->walletModel->where('user_id', $userId)->findAll(),
            'categories'         => $this->categoryModel->where('user_id', $userId)->findAll()
        ];

        return view('dashboard', $data);
    }

    /**
     * API Realtime Chart
     */
    public function chartData()
    {
        $userId = session()->get('id');
        $currentMonth = date('m');
        $currentYear = date('Y');

        $transactions = $this->transactionModel
            ->select('DATE(created_at) as date, type, SUM(amount) as total')
            ->where('user_id', $userId)
            ->where('MONTH(created_at)', $currentMonth)
            ->where('YEAR(created_at)', $currentYear)
            ->groupBy('DATE(created_at), type')
            ->orderBy('date', 'ASC')
            ->findAll();

        $dates = [];
        $incomeData = [];
        $expenseData = [];
        $daysInMonth = date('t');

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf("%04d-%02d-%02d", $currentYear, $currentMonth, $d);
            $dates[] = $d; 
            
            $inc = array_filter($transactions, function($t) use ($date) {
                return $t['date'] == $date && $t['type'] == 'income';
            });
            $incomeData[] = !empty($inc) ? array_values($inc)[0]['total'] : 0;

            $exp = array_filter($transactions, function($t) use ($date) {
                return $t['date'] == $date && $t['type'] == 'expense';
            });
            $expenseData[] = !empty($exp) ? array_values($exp)[0]['total'] : 0;
        }

        return $this->response->setJSON([
            'labels' => $dates,
            'income' => $incomeData,
            'expense' => $expenseData,
            'summary' => [
                'income' => array_sum($incomeData),
                'expense' => array_sum($expenseData)
            ]
        ]);
    }

    public function save_transaction()
    {
        $userId = session()->get('id');

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

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return redirect()->to('/dashboard')->with('error', 'Gagal: ' . reset($errors));
        }

        $file = $this->request->getFile('attachment');
        $fileName = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/struk', $fileName);
        }

        $data = [
            'user_id'        => $userId,
            'amount'         => $this->request->getPost('amount'),
            'type'           => $this->request->getPost('type'),
            'category_id'    => $this->request->getPost('category_id'),
            'wallet_id'      => $this->request->getPost('wallet_id'),
            'description'    => $this->request->getPost('description'),
            'attachment'     => $fileName,
            'transaction_date' => date('Y-m-d H:i:s'),
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        try {
            $this->transactionModel->insert($data);
            return redirect()->to('/dashboard')->with('success', 'Transaksi berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->to('/dashboard')->with('error', $e->getMessage());
        }
    }

    /**
     * EXPORT EXCEL (Updated: Support Filter Bulan)
     */
    public function export_excel()
    {
        $userId = session()->get('id');
        $month = $this->request->getGet('month');
        $year = $this->request->getGet('year');

        $builder = $this->transactionModel
            ->select('transactions.*, categories.name as category_name, wallets.name as wallet_name')
            ->join('categories', 'categories.id = transactions.category_id')
            ->join('wallets', 'wallets.id = transactions.wallet_id')
            ->where('transactions.user_id', $userId);

        // Logic Filter
        if ($month && $year) {
            $builder->where('MONTH(transaction_date)', $month)
                    ->where('YEAR(transaction_date)', $year);
            $fileName = 'Laporan_Keuangan_' . date('F_Y', mktime(0, 0, 0, $month, 10, $year)) . '.xls';
        } else {
            $fileName = 'Laporan_Semua_Transaksi.xls';
        }

        $transactions = $builder->orderBy('transaction_date', 'ASC')->findAll();

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        echo "<table border='1'>
                <thead>
                    <tr style='background-color: #f2f2f2; font-weight: bold;'>
                        <th>Tanggal</th><th>Keterangan</th><th>Kategori</th><th>Dompet</th><th>Tipe</th><th>Nominal</th>
                    </tr>
                </thead>
                <tbody>";
        foreach ($transactions as $row) {
            $warnaTipe = ($row['type'] == 'income') ? 'blue' : 'red';
            $tipeText = ($row['type'] == 'income') ? 'Pemasukan' : 'Pengeluaran';
            echo "<tr>
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
     * EXPORT PDF (Updated: Support Filter Bulan)
     */
    public function export_pdf()
    {
        $userId = session()->get('id');
        $month = $this->request->getGet('month');
        $year = $this->request->getGet('year');
        
        $builder = $this->transactionModel
            ->select('transactions.*, categories.name as category_name, wallets.name as wallet_name')
            ->join('categories', 'categories.id = transactions.category_id')
            ->join('wallets', 'wallets.id = transactions.wallet_id')
            ->where('transactions.user_id', $userId);

        // Logic Filter Tanggal
        if ($month && $year) {
            $startDate = "$year-$month-01";
            $endDate = date("Y-m-t", strtotime($startDate)); // Akhir bulan
        } else {
            $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
            $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');
        }

        $transactions = $builder->where('transaction_date >=', $startDate)
                                ->where('transaction_date <=', $endDate)
                                ->orderBy('transaction_date', 'ASC')
                                ->findAll();

        $data = [
            'transactions' => $transactions,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'user_name'    => session()->get('name'),
            'business_name'=> session()->get('business_name')
        ];

        return view('export/pdf_view', $data);
    }
}