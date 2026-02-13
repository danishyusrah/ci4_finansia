<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\WalletModel;
use App\Models\CategoryModel;

class Auth extends BaseController
{
    public function login()
    {
        // Jika sudah login, lempar ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/register');
    }

    public function process_register()
    {
        // Pastikan UserModel di-load (walaupun tidak dipakai langsung untuk validasi di sini, 
        // tapi baik untuk memastikan kelasnya ada)
        $userModel = new UserModel(); 
        
        // --- PERBAIKAN SINTAKS VALIDASI DI SINI ---
        // Mengubah kurung biasa () menjadi kurung siku []
        $rules = [
            'name' => 'required|min_length[3]', // Sebelumnya min_length(3) -> Error
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]', // Sebelumnya min_length(6) -> Error
            'confpassword' => 'matches[password]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Simpan User Baru
        $userData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'business_name' => $this->request->getPost('business_name'),
            'locked_capital_amount' => 0, // Default 0
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db = \Config\Database::connect();
        $db->table('users')->insert($userData);
        $userId = $db->insertID();

        // --- SETUP DATA AWAL ---
        
        // 1. Dompet Default
        $walletModel = new WalletModel();
        $walletModel->insert(['user_id' => $userId, 'name' => 'Kas Tunai', 'balance' => 0, 'created_at' => date('Y-m-d H:i:s')]);
        
        // 2. Kategori Default
        $categoryModel = new CategoryModel();
        $defaults = [
            ['user_id' => $userId, 'name' => 'Penjualan', 'type' => 'income', 'is_prive' => 0],
            ['user_id' => $userId, 'name' => 'Operasional', 'type' => 'expense', 'is_prive' => 0],
            ['user_id' => $userId, 'name' => 'Beli Stok', 'type' => 'expense', 'is_prive' => 0],
            ['user_id' => $userId, 'name' => 'Gaji Owner (Prive)', 'type' => 'expense', 'is_prive' => 1],
        ];
        $db->table('categories')->insertBatch($defaults);

        return redirect()->to('/login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    public function process_login()
    {
        $db = \Config\Database::connect();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $db->table('users')->where('email', $email)->get()->getRowArray();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Set Session
                $sessionData = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'business_name' => $user['business_name'],
                    'isLoggedIn' => true
                ];
                session()->set($sessionData);
                return redirect()->to('/dashboard');
            }
        }

        return redirect()->back()->withInput()->with('error', 'Email atau Password salah.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}