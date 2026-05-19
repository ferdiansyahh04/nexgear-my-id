<?php
/**
 * CLI error view used by spark commands.
 * Displays exception class, message, and trace for command-line debugging.
 */
$ti = function ($n) { return str_repeat(' ', $n); };

echo "\n";
echo "ERROR: " . $exception::class . "\n";
echo "  " . $exception->getMessage() . "\n\n";
echo "  at " . $exception->getFile() . ':' . $exception->getLine() . "\n\n";

if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') {
    echo "Stack trace:\n";
    foreach ($exception->getTrace() as $i => $frame) {
        $where = ($frame['file'] ?? '<internal>') . (isset($frame['line']) ? ':' . $frame['line'] : '');
        $func  = ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '');
        echo "{$ti(2)}#{$i} {$where} — {$func}()\n";
    }
    echo "\n";
}
