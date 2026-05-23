#!/usr/bin/env bash
# Clone repo, install dependencies, configure environment, import schema.
#
# Idempotent — safe to re-run. Pulls latest on existing checkout.
set -euo pipefail

APP_DIR=/var/www/nexgear-store
REPO_URL=https://github.com/ferdiansyahh04/nexgear-my-id.git
CRED_FILE=/root/.nexgear-db.env

if [ ! -f "$CRED_FILE" ]; then
    echo "ERROR: $CRED_FILE not found — run setup-db.sh first"
    exit 1
fi
# shellcheck disable=SC1090
. "$CRED_FILE"

# 1. Clone or pull
if [ -d "$APP_DIR/.git" ]; then
    echo "Updating existing checkout in $APP_DIR"
    git -C "$APP_DIR" fetch --quiet origin
    git -C "$APP_DIR" reset --hard origin/main
else
    echo "Cloning $REPO_URL → $APP_DIR"
    mkdir -p /var/www
    git clone --quiet "$REPO_URL" "$APP_DIR"
fi

cd "$APP_DIR"

# 2. Composer install (production mode, no plugin scripts as root)
echo 'Running composer install...'
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# 3. .env file (only if missing — preserve existing customisations)
if [ ! -f .env ]; then
    cp .env.example .env
    echo 'Created .env from template'
fi

# Apply production settings via sed (idempotent — replaces lines if found)
sed -i "s|^CI_ENVIRONMENT.*|CI_ENVIRONMENT = production|" .env
sed -i "s|^app.baseURL.*|app.baseURL = 'https://nexgear.my.id/'|" .env
sed -i "s|^database.default.hostname.*|database.default.hostname = 127.0.0.1|" .env
sed -i "s|^database.default.database.*|database.default.database = ${DB_NAME}|" .env
sed -i "s|^database.default.username.*|database.default.username = ${DB_USER}|" .env
sed -i "s|^database.default.password.*|database.default.password = ${DB_PASS}|" .env

# 4. Encryption key — only generate if still placeholder
if grep -q 'CHANGE_THIS_RUN_PHP_SPARK_KEY_GENERATE' .env 2>/dev/null; then
    echo 'Generating new encryption key...'
    php spark key:generate --force
fi

# 5. Import schema if products table doesn't exist (idempotent)
TABLE_COUNT=$(mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -N -B -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}'")
if [ "$TABLE_COUNT" -eq 0 ]; then
    echo 'Importing schema...'
    mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < database/nexgear_store.sql
    echo 'Schema imported'
else
    echo "Schema already present (${TABLE_COUNT} tables) — skipping import"
fi

# 6. File permissions
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 750 {} \;
find "$APP_DIR" -type f -exec chmod 640 {} \;
# writable + uploads need write access for the web user
chmod -R 770 "$APP_DIR/writable"
chmod -R 770 "$APP_DIR/public/uploads"

echo
echo '=== App setup complete ==='
echo "App dir:  $APP_DIR"
echo "Owner:    www-data"
echo "Database: $DB_NAME (user: $DB_USER)"
echo
echo 'Smoke test:'
sudo -u www-data php "$APP_DIR/spark" db:table products 2>&1 | head -5 || true
