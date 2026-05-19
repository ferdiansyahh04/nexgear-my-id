<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Pure-PHP database backup that doesn't depend on `mysqldump` being on PATH.
 * Generates a portable .sql file with CREATE TABLE + INSERT statements.
 *
 * Usage:
 *   php spark db:backup                # writable/backups/nexgear-YYYYMMDD-HHMMSS.sql
 *   php spark db:backup --name foo     # writable/backups/foo.sql
 *   php spark db:backup --keep 7       # also prunes older than 7 backups
 *
 * Production: pair with cron, e.g. daily:
 *   30 2 * * * cd /var/www/nexgear && php spark db:backup --keep 14
 */
class DbBackup extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'db:backup';
    protected $description = 'Dump the default database to writable/backups/.';
    protected $usage       = 'db:backup [--name <basename>] [--keep <count>]';
    protected $options     = [
        '--name' => 'Basename for the dump (default: timestamp).',
        '--keep' => 'Keep only the N most recent backups (default: keep all).',
    ];

    public function run(array $params)
    {
        $name = (string) (CLI::getOption('name') ?: '');
        $keep = (int)    (CLI::getOption('keep') ?: 0);

        $dir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $basename = $name !== ''
            ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name)
            : 'nexgear-' . date('Ymd-His');
        $path = $dir . $basename . '.sql';

        $db    = \Config\Database::connect();
        $dbName = (string) ($db->getDatabase() ?? '');
        $tables = $db->listTables();

        if ($tables === []) {
            CLI::write('No tables to back up.', 'red');
            return;
        }

        $fp = fopen($path, 'w');
        if (! $fp) {
            CLI::write("Cannot write to {$path}", 'red');
            return;
        }

        fwrite($fp, "-- NexGear database backup\n");
        fwrite($fp, "-- Generated at " . date('c') . "\n");
        fwrite($fp, "-- Source: {$dbName}\n\n");
        fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($fp, "SET NAMES utf8mb4;\n\n");

        foreach ($tables as $table) {
            // CREATE TABLE
            $row = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
            $createSql = $row['Create Table'] ?? null;
            if (! $createSql) continue;

            fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($fp, $createSql . ";\n\n");

            // Data rows
            $rows = $db->query("SELECT * FROM `{$table}`")->getResultArray();
            if ($rows === []) {
                continue;
            }

            $columns = array_map(static fn ($c) => "`{$c}`", array_keys($rows[0]));
            $colList = implode(', ', $columns);

            // Chunk INSERTs at 100 rows for memory friendliness
            foreach (array_chunk($rows, 100) as $chunk) {
                $values = [];
                foreach ($chunk as $rec) {
                    $escaped = array_map(static function ($v) use ($db) {
                        if ($v === null) return 'NULL';
                        if (is_int($v) || is_float($v)) return (string) $v;
                        return $db->escape((string) $v);
                    }, array_values($rec));
                    $values[] = '(' . implode(', ', $escaped) . ')';
                }
                fwrite($fp, "INSERT INTO `{$table}` ({$colList}) VALUES\n  ");
                fwrite($fp, implode(",\n  ", $values));
                fwrite($fp, ";\n");
            }
            fwrite($fp, "\n");
        }

        fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($fp);

        $bytes = filesize($path) ?: 0;
        CLI::write("[OK] {$path}  (" . CLI::color($this->humanBytes($bytes), 'green') . ')');

        if ($keep > 0) {
            $this->pruneOlder($dir, $keep);
        }
    }

    private function pruneOlder(string $dir, int $keep): void
    {
        $files = glob($dir . '*.sql') ?: [];
        if (count($files) <= $keep) return;
        usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
        $remove = array_slice($files, $keep);
        foreach ($remove as $f) {
            @unlink($f);
            CLI::write('  pruned ' . basename($f), 'yellow');
        }
    }

    private function humanBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $val = $bytes;
        while ($val >= 1024 && $i < count($units) - 1) {
            $val /= 1024;
            $i++;
        }
        return number_format($val, $val < 10 ? 2 : 1) . ' ' . $units[$i];
    }
}
