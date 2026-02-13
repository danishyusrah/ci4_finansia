<?php

namespace App\Models;

use CodeIgniter\Model;

class DebtModel extends Model
{
    protected $table            = 'debts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['user_id', 'type', 'party_name', 'amount', 'description', 'due_date', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps    = true;
}