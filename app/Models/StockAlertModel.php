<?php

namespace App\Models;

use CodeIgniter\Model;

class StockAlertModel extends Model
{
    protected $table         = 'stock_alerts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'email', 'product_id', 'notified_at'];
}
