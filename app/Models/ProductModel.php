<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['user_id', 'name', 'code', 'buy_price', 'sell_price', 'stock', 'created_at', 'updated_at'];
    protected $useTimestamps    = true;

    // Helper untuk menghitung total nilai aset barang (Valuasi)
    public function getTotalAssetValue($userId)
    {
        $products = $this->where('user_id', $userId)->findAll();
        $total = 0;
        foreach($products as $p) {
            $total += ($p['buy_price'] * $p['stock']);
        }
        return $total;
    }
}