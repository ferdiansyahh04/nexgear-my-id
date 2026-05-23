#!/usr/bin/env bash
# Use CodeIgniter's Email service to send a test message via Resend.
set -euo pipefail

cd /var/www/nexgear-store

# Use a one-shot inline PHP script invoked via spark namespace bootstrap so
# we get config + services. We write it as a temp file and run it.
TMPFILE=/tmp/nexgear-smtp-test.php
cat > "$TMPFILE" <<'PHP'
<?php
/* Bootstrap CodeIgniter just enough to get the Email service. */
chdir(__DIR__ . '/../var/www/nexgear-store') ?: chdir('/var/www/nexgear-store');
require __DIR__ . '/../var/www/nexgear-store/vendor/autoload.php';
require '/var/www/nexgear-store/app/Config/Paths.php';

$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$to = $argv[1] ?? '';
if ($to === '') {
    fwrite(STDERR, "Usage: php smtp-test.sh <recipient-email>\n");
    exit(1);
}

$email = \Config\Services::email();
$email->setTo($to);
$email->setSubject('NexGear SMTP test ' . date('H:i:s'));
$email->setMessage('<h2>It works!</h2><p>This is a NexGear SMTP test sent via Resend at ' . date('c') . '.</p>');

$ok = $email->send();
if ($ok) {
    echo "OK — message dispatched to {$to}\n";
} else {
    echo "FAIL — debugger output below:\n";
    echo $email->printDebugger(['headers']);
    exit(1);
}
PHP

# Run as www-data so it reads the same .env that PHP-FPM reads.
sudo -u www-data php "$TMPFILE" "${1:-ferdiansyahh670@gmail.com}"
rm -f "$TMPFILE"
