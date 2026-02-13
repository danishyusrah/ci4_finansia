<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixCascadeSafety extends Migration
{
    public function up()
    {
        // 1. Hapus Foreign Key Lama yang berbahaya (CASCADE) di tabel transactions
        // Note: Nama constraint mungkin berbeda tergantung auto-generate DB, 
        // tapi biasanya formatnya: transactions_wallet_id_foreign
        
        $db = \Config\Database::connect();
        
        // Coba drop constraint lama (menggunakan raw query agar kompatibel)
        // Kita asumsikan nama constraint standar CI4. Jika error, cek nama constraint di PHPMyAdmin
        $this->db->query("ALTER TABLE transactions DROP FOREIGN KEY transactions_wallet_id_foreign");
        $this->db->query("ALTER TABLE transactions DROP FOREIGN KEY transactions_category_id_foreign");

        // 2. Buat Foreign Key Baru yang Aman (RESTRICT)
        // Jika user hapus Dompet/Kategori, tolak jika masih ada transaksinya.
        $this->db->query("
            ALTER TABLE transactions 
            ADD CONSTRAINT transactions_wallet_id_foreign_safe 
            FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE RESTRICT ON UPDATE CASCADE
        ");

        $this->db->query("
            ALTER TABLE transactions 
            ADD CONSTRAINT transactions_category_id_foreign_safe 
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
        ");
    }

    public function down()
    {
        // Kembalikan ke kondisi tidak aman (tidak disarankan, tapi wajib ada di migration)
        $this->db->query("ALTER TABLE transactions DROP FOREIGN KEY transactions_wallet_id_foreign_safe");
        $this->db->query("ALTER TABLE transactions DROP FOREIGN KEY transactions_category_id_foreign_safe");
        
        // Re-add Cascade (Kondisi awal)
        $this->forge->addForeignKey('wallet_id', 'wallets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'CASCADE');
    }
}