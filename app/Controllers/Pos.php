<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\TransactionModel;
use App\Models\WalletModel;
use App\Models\CategoryModel;

class Pos extends BaseController
{
    protected $productModel;
    protected $transactionModel;
    protected $walletModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->transactionModel = new TransactionModel();
        $this->walletModel = new WalletModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        $userId = session()->get('id');

        // Ambil produk yang stoknya > 0
        $products = $this->productModel
            ->where('user_id', $userId)
            ->where('stock >', 0)
            ->orderBy('name', 'ASC')
            ->findAll();

        // Ambil Dompet (untuk terima pembayaran)
        $wallets = $this->walletModel->where('user_id', $userId)->findAll();
        
        // Ambil Kategori Income (untuk label transaksi)
        $categories = $this->categoryModel
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->findAll();

        $data = [
            'title'      => 'Mesin Kasir (POS)',
            'products'   => $products,
            'wallets'    => $wallets,
            'categories' => $categories
        ];

        return view('pos/index', $data);
    }

    public function checkout()
    {
        $userId = session()->get('id');
        $db = \Config\Database::connect();
        
        // Ambil data dari Form
        $cartItems = json_decode($this->request->getPost('cart_data'), true); // Array items
        $walletId = $this->request->getPost('wallet_id');
        $categoryId = $this->request->getPost('category_id');
        $totalAmount = $this->request->getPost('total_amount');
        
        if (empty($cartItems)) {
            return redirect()->back()->with('error', 'Keranjang belanja kosong.');
        }

        // Mulai Database Transaction (Agar aman: jika gagal satu, batal semua)
        $db->transStart();

        // 1. Catat Transaksi Keuangan (Income)
        // Buat deskripsi detail item
        $description = "POS: ";
        foreach($cartItems as $item) {
            $description .= $item['name'] . " (" . $item['qty'] . "x), ";
        }
        $description = rtrim($description, ", ");

        $trxData = [
            'user_id'          => $userId,
            'wallet_id'        => $walletId,
            'category_id'      => $categoryId,
            'amount'           => $totalAmount,
            'type'             => 'income',
            'description'      => $description,
            'transaction_date' => date('Y-m-d H:i:s'),
        ];
        
        // Simpan Transaksi (Model akan otomatis update saldo dompet)
        $this->transactionModel->insert($trxData);

        // 2. Kurangi Stok Barang (Inventory)
        foreach($cartItems as $item) {
            // Ambil stok saat ini
            $product = $this->productModel->find($item['id']);
            if($product) {
                $newStock = $product['stock'] - $item['qty'];
                // Update Stok
                $this->productModel->update($item['id'], ['stock' => $newStock]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->back()->with('error', 'Transaksi Gagal. Silakan coba lagi.');
        }

        return redirect()->to('/pos')->with('success', 'Transaksi Berhasil! Stok berkurang & Saldo bertambah.');
    }
}