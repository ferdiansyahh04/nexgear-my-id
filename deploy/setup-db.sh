#!/usr/bin/env bash
# Provision MariaDB:
#  1. Generate a random app-user password
#  2. Create database `nexgear_store`
#  3. Create user `nexgear_app`@'localhost' with scoped privileges
#  4. Persist credentials to /root/.nexgear-db.env (chmod 600) so subsequent
#     deploy steps can read them without re-running this script.
#
# Idempotent — re-running is safe; user/DB created only if missing.
set -euo pipefail

CRED_FILE=/root/.nexgear-db.env
DB_NAME=nexgear_store
DB_USER=nexgear_app

if [ -f "$CRED_FILE" ]; then
    echo "Credential file already exists — reusing existing password"
    # shellcheck disable=SC1090
    . "$CRED_FILE"
else
    # NOTE: pipefail + head -c on /dev/urandom causes SIGPIPE to tr.
    # Use openssl rand instead — generates 32 chars from a 24-byte source.
    DB_PASS=$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | cut -c1-32)
    umask 077
    cat > "$CRED_FILE" <<EOF
DB_NAME="$DB_NAME"
DB_USER="$DB_USER"
DB_PASS="$DB_PASS"
EOF
    chmod 600 "$CRED_FILE"
    echo "Generated new app password and stored to $CRED_FILE (chmod 600)"
fi

# Use socket-auth root (default on Ubuntu/MariaDB)
mysql --protocol=socket -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, REFERENCES, CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "Database \`${DB_NAME}\` ready, user '${DB_USER}'@'localhost' provisioned"
mysql --protocol=socket -uroot -e "SHOW DATABASES LIKE '${DB_NAME}'; SELECT User, Host FROM mysql.user WHERE User='${DB_USER}';"
