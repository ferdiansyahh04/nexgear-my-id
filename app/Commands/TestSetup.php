<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * One-shot bootstrap for the PHPUnit test database.
 *
 * Creates `nexgear_test` (or whatever is configured in the `tests` group),
 * imports the canonical schema from database/nexgear_store.sql, and
 * verifies the connection.
 *
 * Usage:
 *   php spark test:setup
 *
 * Re-running is safe — schema file uses CREATE DATABASE IF NOT EXISTS and
 * DROP TABLE IF EXISTS for every table.
 */
class TestSetup extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'test:setup';
    protected $description = 'Provision the test database from database/nexgear_store.sql.';

    public function run(array $params)
    {
        $config = config('Database');
        $tests  = $config->tests;
        $dbName = (string) $tests['database'];

        if ($dbName === '') {
            CLI::write('No database name configured for the tests group.', 'red');
            return;
        }

        // Connect without selecting the DB so we can CREATE it
        $tests['database'] = '';
        $db = \Config\Database::connect($tests, false);

        try {
            $db->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            CLI::write("[OK] database `{$dbName}` ready", 'green');
        } catch (\Throwable $e) {
            CLI::write('Failed to create database: ' . $e->getMessage(), 'red');
            return;
        }

        // Reconnect with the DB selected and run the schema file
        $tests['database'] = $dbName;
        $db = \Config\Database::connect($tests, false);

        $sqlFile = ROOTPATH . 'database/nexgear_store.sql';
        if (! is_file($sqlFile)) {
            CLI::write("Schema file not found at {$sqlFile}", 'red');
            return;
        }

        $sql = file_get_contents($sqlFile) ?: '';
        // Strip the leading CREATE DATABASE / USE statements (we already selected the test DB)
        $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql, 1) ?? $sql;
        $sql = preg_replace('/USE\s+\w+\s*;/i', '', $sql, 1) ?? $sql;

        // Strip line comments so they don't confuse the splitter
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;

        // Naive but practical splitter: split on `;` followed by a newline
        // then a non-whitespace character (i.e. the start of the next stmt).
        $statements = array_filter(
            array_map('trim', preg_split('/;\s*[\r\n]+(?=\S)/', $sql) ?: []),
            static fn ($s) => $s !== '' && ! str_starts_with($s, '--')
        );
        $applied = 0;
        foreach ($statements as $stmt) {
            // Re-strip a trailing `;` if any lingered
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
        CLI::write("[OK] applied {$applied} SQL statements", 'green');

        $tables = $db->listTables();
        CLI::write('Test DB now contains ' . count($tables) . ' table(s).', 'cyan');
    }
}
