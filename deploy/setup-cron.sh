#!/usr/bin/env bash
# Pull latest, verify spark commands work, install cron.
#
# Cron runs as www-data so file permissions and PHP-FPM ownership stay
# consistent (we already chown the app dir to www-data:www-data).
set -euo pipefail

APP_DIR=/var/www/nexgear-store
CRON_FILE=/tmp/nexgear-cron.tab

cd "$APP_DIR"
sudo -u www-data git fetch --quiet origin
sudo -u www-data git reset --hard origin/main >/dev/null
echo "Pulled to $(sudo -u www-data git rev-parse --short HEAD)"

sudo -u www-data php spark cache:clear >/dev/null

echo
echo '=== Verify spark commands ==='
echo '--- cart:remind-abandoned ---'
sudo -u www-data php spark cart:remind-abandoned 2>&1 | tail -3
echo
echo '--- stock:dispatch-alerts ---'
sudo -u www-data php spark stock:dispatch-alerts 2>&1 | tail -3
echo

# Build the crontab. Idempotent — overwrites the entire www-data crontab.
# Using absolute paths so cron's empty PATH doesn't trip us.
PHP_BIN=$(command -v php)

cat > "$CRON_FILE" <<EOF
# NexGear scheduled tasks (managed by deploy/setup-cron.sh)
# Run as www-data to keep file ownership consistent.
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
MAILTO=""

# Nudge customers about idle carts (every 30 min)
*/30 * * * *  cd ${APP_DIR} && ${PHP_BIN} spark cart:remind-abandoned  >> ${APP_DIR}/writable/logs/cron.log 2>&1

# Notify users when subscribed products are back in stock (every 15 min)
*/15 * * * *  cd ${APP_DIR} && ${PHP_BIN} spark stock:dispatch-alerts  >> ${APP_DIR}/writable/logs/cron.log 2>&1

# Weekly DB backup, Sundays at 03:00 (writable/backups/)
0 3 * * 0     cd ${APP_DIR} && ${PHP_BIN} spark db:backup              >> ${APP_DIR}/writable/logs/cron.log 2>&1

# Prune backups older than 30 days, daily at 03:30
30 3 * * *    find ${APP_DIR}/writable/backups -name 'nexgear-*.sql' -mtime +30 -delete

EOF

# Install the crontab
crontab -u www-data "$CRON_FILE"
rm "$CRON_FILE"

# Make sure the log target exists with the right ownership
touch "$APP_DIR/writable/logs/cron.log"
chown www-data:www-data "$APP_DIR/writable/logs/cron.log"

echo '=== Installed crontab for www-data ==='
crontab -u www-data -l
