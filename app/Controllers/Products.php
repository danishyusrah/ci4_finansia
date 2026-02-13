<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Products extends BaseController
{
    protected $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $userId = session()->get('id');

        $products = $this->productModel
            ->where('user_id', $userId)
            ->orderBy('stock', 'ASC') // Tampilkan stok paling sedikit di atas (urgent)
            ->findAll();

        $totalAsset = $this->productModel->getTotalAssetValue($userId);

        $data = [
            'title'      => 'Stok Barang (Inventory)',
            'products'   => $products,
            'total_asset'=> $totalAsset
        ];

        return view('products/index', $data);
    }

    public function save()
    {
        $userId = session()->get('id');
        
        $data = [
            'user_id'    => $userId,
            'name'       => $this->request->getPost('name'),
            'code'       => $this->request->getPost('code'),
            'buy_price'  => $this->request->getPost('buy_price'),
            'sell_price' => $this->request->getPost('sell_price'),
            'stock'      => $this->request->getPost('stock'),
        ];

        $id = $this->request->getPost('id');

        if($id) {
            // Update
            $this->productModel->update($id, $data);
            $msg = 'Produk berhasil diperbarui.';
        } else {
            // Insert Baru
            $this->productModel->insert($data);
            $msg = 'Produk baru berhasil ditambahkan.';
        }

        return redirect()->to('/products')->with('success', $msg);
    }

    public function delete($id)
    {
        $userId = session()->get('id');
        $product = $this->productModel->where('id', $id)->where('user_id', $userId)->first();
        
        if($product) {
            $this->productModel->delete($id);
            return redirect()->to('/products')->with('success', 'Produk dihapus.');
        }
        
        return redirect()->to('/products')->with('error', 'Produk tidak ditemukan.');
    }
}