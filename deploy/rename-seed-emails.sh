#!/usr/bin/env bash
# Rename seed user emails to the production domain.
#  • Pulls latest source
#  • Updates rows in `users` table (idempotent — only renames if old email exists)
#  • Clears app cache
#  • Verifies password hashes still verify against documented credentials
set -euo pipefail

# shellcheck disable=SC1091
. /root/.nexgear-db.env

APP_DIR=/var/www/nexgear-store

cd "$APP_DIR"
sudo -u www-data git fetch --quiet origin
sudo -u www-data git reset --hard origin/main >/dev/null
echo "Pulled to $(sudo -u www-data git rev-parse --short HEAD)"

mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" <<'SQL'
-- Idempotent rename. Each UPDATE is no-op if the row was already renamed.
UPDATE users SET email = 'admin@nexgear.my.id' WHERE email = 'admin@nexgear.test';
UPDATE users SET email = 'user@nexgear.my.id'  WHERE email = 'user@nexgear.test';
SELECT id, name, email, role FROM users ORDER BY id;
SQL

sudo -u www-data php spark cache:clear >/dev/null 2>&1
echo
echo 'Verifying credentials with check:login...'
sudo -u www-data php spark check:login | grep -E 'Email:|password_verify|Total'
