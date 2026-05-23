#!/usr/bin/env bash
# Install Composer with checksum verification.
set -euo pipefail

cd /tmp
curl -fsS https://getcomposer.org/installer -o composer-setup.php

EXPECTED=$(curl -fsS https://composer.github.io/installer.sig)
ACTUAL=$(php -r "echo hash_file('sha384', 'composer-setup.php');")

if [ "$EXPECTED" != "$ACTUAL" ]; then
    echo 'CHECKSUM MISMATCH — aborting'
    rm -f composer-setup.php
    exit 1
fi

echo 'CHECKSUM OK'
php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
rm -f composer-setup.php

composer --version
