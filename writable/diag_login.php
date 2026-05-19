<?php
/**
 * One-off diagnostic for the login flow.
 * Run with: php writable/diag_login.php
 */

require __DIR__ . '/../vendor/autoload.php';

$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
\CodeIgniter\Services::createRequest((new Config\App()), false);

$db = \Config\Database::connect();

echo "── Connection ──────────────\n";
try {
    $db->connect();
    echo "  ✓ DB connected: " . $db->getDatabase() . "\n\n";
} catch (\Throwable $e) {
    echo "  ✗ DB error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "── Users table ─────────────\n";
$users = $db->table('users')->get()->getResultArray();
if (! $users) {
    echo "  ✗ users table is empty. Re-import database/nexgear_store.sql.\n";
    exit(1);
}

foreach ($users as $u) {
    echo "  • #{$u['id']} {$u['email']} role={$u['role']}\n";
    echo "    hash: " . substr($u['password'], 0, 20) . "...\n";
    foreach (['password', 'admin', 'user'] as $candidate) {
        $ok = password_verify($candidate, $u['password']);
        echo "    verify('{$candidate}') = " . ($ok ? '✓' : '✗') . "\n";
    }
    echo "\n";
}

echo "── Active sessions ─────────\n";
$sessions = $db->tableExists('ci_sessions')
    ? $db->table('ci_sessions')->countAllResults()
    : 'table missing';
echo "  ci_sessions rows: {$sessions}\n";
