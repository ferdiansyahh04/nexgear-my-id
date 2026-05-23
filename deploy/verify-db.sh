#!/usr/bin/env bash
set -euo pipefail

# shellcheck disable=SC1091
. /root/.nexgear-db.env

echo '=== TABLE COUNT ==='
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -B -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME'"

echo
echo '=== TABLES ==='
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e 'SHOW TABLES'

echo
echo '=== ROWS ==='
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 'products' AS t, COUNT(*) AS c FROM products UNION ALL SELECT 'users', COUNT(*) FROM users UNION ALL SELECT 'categories', COUNT(*) FROM categories UNION ALL SELECT 'coupons', COUNT(*) FROM coupons"
