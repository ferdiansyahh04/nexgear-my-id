<?php

namespace App\Models;

use CodeIgniter\Model;

class CartModel extends Model
{
    protected $table = 'cart';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'status', 'total', 'shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_postal_code'];
}
