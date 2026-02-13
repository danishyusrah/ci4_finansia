<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $allowedFields    = ['user_id', 'name', 'type', 'is_prive'];

    // Helper untuk mengambil kategori berdasarkan tipe (Income/Expense)
    public function getByUser($userId, $type = null)
    {
        $builder = $this->where('user_id', $userId);
        if ($type) {
            $builder->where('type', $type);
        }
        return $builder->findAll();
    }
}