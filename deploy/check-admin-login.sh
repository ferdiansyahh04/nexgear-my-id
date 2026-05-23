#!/usr/bin/env bash
set -euo pipefail

# shellcheck disable=SC1091
. /root/.nexgear-db.env

echo '=== USERS in DB ==='
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
    "SELECT id, name, email, role, LEFT(password, 7) AS hash_prefix, LENGTH(password) AS hash_len, totp_enabled FROM users"

echo
echo '=== Verify admin password ==='
cd /var/www/nexgear-store
sudo -u www-data php spark check:login 2>&1 | head -20
