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