<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Restore a backup .sql created by db:backup.
 *
 * Usage:
 *   php spark db:restore                          # interactive picker
 *   php spark db:restore writable/backups/foo.sql # explicit
 *
 * Asks for confirmation before running because restore is destructive.
 */
class DbRestore extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'db:restore';
    protected $description = 'Restore a NexGear backup .sql into the default database.';
    protected $usage       = 'db:restore [path]';

    public function run(array $params)
    {
        $path = $params[0] ?? null;
        $dir  = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;

        if ($path === null) {
            $files = glob($dir . '*.sql') ?: [];
            if ($files === []) {
                CLI::write('No backups found in ' . $dir, 'red');
                return;
            }
            usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));

            CLI::write('Available backups:', 'cyan');
            foreach ($files as $i => $f) {
                $kb = number_format((filesize($f) ?: 0) / 1024, 1);
                CLI::write('  [' . $i . '] ' . basename($f) . " ({$kb} KB · " . date('Y-m-d H:i', filemtime($f) ?: 0) . ')');
            }
            $idx = (int) CLI::prompt('Pick a backup index', '0');
            if (! isset($files[$idx])) {
                CLI::write('Invalid selection.', 'red');
                return;
            }
            $path = $files[$idx];
        }

        if (! is_file($path)) {
            CLI::write("Backup file not found: {$path}", 'red');
            return;
        }

        $confirm = CLI::prompt(
            CLI::color('This will OVERWRITE all data in the default database. Type RESTORE to continue', 'red'),
            ''
        );
        if ($confirm !== 'RESTORE') {
            CLI::write('Aborted.', 'yellow');
            return;
        }

        $sql = file_get_contents($path) ?: '';
        // Strip line comments
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        // Practical splitter
        $statements = array_filter(
            array_map('trim', preg_split('/;\s*[\r\n]+(?=\S)/', $sql) ?: []),
            static fn ($s) => $s !== ''
        );

        $db = \Config\Database::connect();
        $applied = 0;
        foreach ($statements as $stmt) {
            $stmt = rtrim($stmt, "; \r\n\t");
            if ($stmt === '') continue;
            try {
                $db->query($stmt);
                $applied++;
            } catch (\Throwable $e) {
                CLI::write('SQL error: ' . $e->getMessage(), 'red');
                CLI::write('Statement: ' . substr($stmt, 0, 200) . '...', 'yellow');
                return;
            }
        }

        CLI::write("Restore complete. Applied {$applied} statements from " . basename($path), 'green');
    }
}
