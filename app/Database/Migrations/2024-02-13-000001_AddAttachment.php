<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttachment extends Migration
{
    public function up()
    {
        $fields = [
            'attachment' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'description'
            ],
        ];
        $this->forge->addColumn('transactions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'attachment');
    }
}