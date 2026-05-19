<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table         = 'reviews';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'product_id', 'rating', 'title', 'body', 'verified_purchase'];

    /**
     * Per-product aggregate: avg rating + total count + breakdown 1..5.
     *
     * @return array{average: float, count: int, breakdown: array<int,int>}
     */
    public function aggregate(int $productId): array
    {
        $row = $this->select('AVG(rating) AS avg_rating, COUNT(*) AS total')
            ->where('product_id', $productId)
            ->first();

        $rows = $this->select('rating, COUNT(*) AS c')
            ->where('product_id', $productId)
            ->groupBy('rating')
            ->findAll();

        $breakdown = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($rows as $r) $breakdown[(int) $r['rating']] = (int) $r['c'];

        return [
            'average'   => round((float) ($row['avg_rating'] ?? 0), 2),
            'count'     => (int) ($row['total'] ?? 0),
            'breakdown' => $breakdown,
        ];
    }
}
