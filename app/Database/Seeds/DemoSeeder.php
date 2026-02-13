<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Insert User (Owner)
        // Kita set modal terkunci Rp 10 Juta
        $userData = [
            'name'                  => 'Budi Owner',
            'email'                 => 'owner@finansia.com',
            'password'              => password_hash('123456', PASSWORD_DEFAULT),
            'business_name'         => 'Toko Serba Ada',
            'locked_capital_amount' => 10000000.00, // Modal Awal yg tidak boleh diambil
            'created_at'            => date('Y-m-d H:i:s'),
        ];
        $db->table('users')->insert($userData);
        $userId = $db->insertID();

        // 2. Insert Wallets (Dompet)
        $wallets = [
            [
                'user_id' => $userId,
                'name'    => 'Kas Tunai (Laci)',
                'balance' => 2500000.00, // Ada uang cash 2.5jt
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => $userId,
                'name'    => 'Bank BCA Bisnis',
                'balance' => 15000000.00, // Ada uang bank 15jt
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];
        $db->table('wallets')->insertBatch($wallets);

        // 3. Insert Categories
        $categories = [
            // INCOME
            ['user_id' => $userId, 'name' => 'Penjualan Produk', 'type' => 'income', 'is_prive' => 0],
            ['user_id' => $userId, 'name' => 'Pendapatan Jasa', 'type' => 'income', 'is_prive' => 0],
            
            // EXPENSE OPERATIONAL
            ['user_id' => $userId, 'name' => 'Beli Bahan Baku', 'type' => 'expense', 'is_prive' => 0],
            ['user_id' => $userId, 'name' => 'Gaji Karyawan', 'type' => 'expense', 'is_prive' => 0],
            ['user_id' => $userId, 'name' => 'Listrik & Internet', 'type' => 'expense', 'is_prive' => 0],
            
            // PRIVE (Crucial for Anti-Boncos Logic)
            ['user_id' => $userId, 'name' => 'Tarik Tunai Owner (Prive)', 'type' => 'expense', 'is_prive' => 1],
        ];
        $db->table('categories')->insertBatch($categories);
    }
}