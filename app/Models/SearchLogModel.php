<?php

namespace App\Models;

use CodeIgniter\Model;

class SearchLogModel extends Model
{
    protected $table         = 'search_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['query', 'count', 'last_seen_at'];

    /**
     * Atomic upsert: increments count when query already exists, otherwise
     * inserts a fresh row. Uses raw SQL because the Query Builder doesn't
     * expose ON DUPLICATE KEY UPDATE cleanly. Also clears the trending cache
     * so subsequent overlay opens see fresh data soon after a search.
     */
    public function record(string $query): void
    {
        $query = mb_substr(strtolower(trim($query)), 0, 120);
        if ($query === '') return;

        $now = date('Y-m-d H:i:s');
        $db  = $this->db;
        $sql = "INSERT INTO {$this->table} (`query`, `count`, `last_seen_at`, `created_at`, `updated_at`)
                VALUES (?, 1, ?, ?, ?)
                ON DUPLICATE KEY UPDATE `count` = `count` + 1, `last_seen_at` = ?, `updated_at` = ?";
        $db->query($sql, [$query, $now, $now, $now, $now, $now]);

        // Best-effort cache bust
        try {
            cache()->deleteMatching('search_trending_*');
        } catch (\Throwable) {
            // file/dummy cache backends may not support deleteMatching — ignore
        }
    }

    /**
     * Trending = most-searched in the last $hours window. Cached 5 minutes.
     *
     * @return array<int, array{query: string, count: int}>
     */
    public function trending(int $limit = 6, int $hours = 168): array
    {
        $cacheKey = "search_trending_{$limit}_{$hours}";
        $cached = cache($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        $rows = $this->select('query, count')
            ->where('last_seen_at >=', $cutoff)
            ->orderBy('count', 'DESC')
            ->orderBy('last_seen_at', 'DESC')
            ->limit($limit)
            ->find();

        cache()->save($cacheKey, $rows, 300);
        return $rows;
    }
}
