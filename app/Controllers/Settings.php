<?php

namespace App\Controllers;

use App\Models\UserModel; // Kita perlu buat Model ini dulu sebentar lagi
use App\Models\WalletModel;

class Settings extends BaseController
{
    protected $walletModel;

    public function __construct()
    {
        $this->walletModel = new WalletModel();
    }

    public function index()
    {
        $userId = session()->get('id');

        // Kita akses tabel users manual pakai Query Builder agar cepat
        // (atau bisa pakai UserModel jika sudah dibuat)
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRow();

        $data = [
            'title'   => 'Pengaturan Bisnis',
            'user'    => $user,
            'wallets' => $this->walletModel->where('user_id', $userId)->findAll()
        ];

        return view('settings/index', $data);
    }

    public function update_profile()
    {
        $userId = session()->get('id');
        $db = \Config\Database::connect();

        $data = [
            'business_name'         => $this->request->getPost('business_name'),
            'name'                  => $this->request->getPost('name'),
            // Ini PENTING: User menentukan batas modal yang tidak boleh diambil
            'locked_capital_amount' => $this->request->getPost('locked_capital_amount'),
            'updated_at'            => date('Y-m-d H:i:s')
        ];

        $db->table('users')->where('id', $userId)->update($data);

        return redirect()->to('/settings')->with('success', 'Profil dan batas modal berhasil diperbarui.');
    }

    public function backup()
    {
        $db = \Config\Database::connect();
        $tables = $db->listTables();
        
        // Header File SQL
        $output = "-- Database Backup (Finansia)\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- PHP Version: " . phpversion() . "\n\n";
        
        // Matikan cek foreign key sementara agar tidak error saat import
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $output .= "-- Table structure for table `$table` --\n";
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            
            // 1. Ambil Structure (CREATE TABLE)
            $query = $db->query("SHOW CREATE TABLE `$table`");
            $row = $query->getRowArray();
            
            // Penanganan beda output array keys di tiap versi MySQL
            $createTable = $row['Create Table'] ?? $row['Create table'] ?? '';
            
            if ($createTable) {
                $output .= $createTable . ";\n\n";
            }

            // 2. Ambil Data (INSERT INTO)
            $output .= "-- Dumping data for table `$table` --\n";
            $query = $db->query("SELECT * FROM `$table`");
            $result = $query->getResultArray();
            
            foreach ($result as $row) {
                $output .= "INSERT INTO `$table` VALUES (";
                $values = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $values[] = "NULL";
                    } else {
                        // Escape string agar aman dari karakter aneh (kutip satu dll)
                        $values[] = "'" . $db->escapeString((string)$val) . "'";
                    }
                }
                $output .= implode(", ", $values);
                $output .= ");\n";
            }
            $output .= "\n";
        }
        
        // Hidupkan kembali cek foreign key
        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Nama file download
        $filename = 'Finansia_Backup_' . date('Y-m-d_H-i-s') . '.sql';

        // Download langsung sebagai file .sql
        return $this->response->download($filename, $output);
    }
    

    public function add_wallet()
    {
        $userId = session()->get('id');

        $data = [
            'user_id'    => $userId,
            'name'       => $this->request->getPost('wallet_name'),
            'balance'    => str_replace('.', '', $this->request->getPost('initial_balance')), // Hapus titik format rupiah
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->walletModel->insert($data);

        return redirect()->to('/settings')->with('success', 'Dompet baru berhasil ditambahkan.');
    }
}