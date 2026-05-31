<?php

namespace App\Models;

use CodeIgniter\Model;

class CartModel extends Model
{
    protected $table = 'cart';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'status', 'total', 'coupon_code', 'discount', 'payment_status', 'payment_ref', 'payment_token', 'snap_token', 'payment_method', 'paid_at', 'shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_postal_code'];
}
