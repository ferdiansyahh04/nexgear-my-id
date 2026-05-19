<?php

namespace App\Models;

use CodeIgniter\Model;

class AbandonedCartModel extends Model
{
    protected $table         = 'abandoned_carts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'items_json', 'total', 'item_count', 'last_activity_at', 'reminded_at'];
}
