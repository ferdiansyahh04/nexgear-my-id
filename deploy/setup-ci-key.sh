#!/usr/bin/env bash
# Generate a dedicated SSH key pair for GitHub Actions deploy.
#
# Output: prints the private key to stdout so it can be pasted into the
# VPS_SSH_KEY GitHub secret. Public key is appended to ~/.ssh/authorized_keys
# with a 'ci-deploy' marker so we can revoke it specifically later.
#
# Idempotent: if the key already exists, just re-prints it.
set -euo pipefail

KEY_DIR=/root/.ssh
KEY_FILE="$KEY_DIR/nexgear-ci"
AUTH_FILE="$KEY_DIR/authorized_keys"

mkdir -p "$KEY_DIR"
chmod 700 "$KEY_DIR"

if [ ! -f "$KEY_FILE" ]; then
    ssh-keygen -t ed25519 -C "nexgear-ci@github-actions" -N '' -f "$KEY_FILE" >/dev/null
    echo "[OK] Generated new key at $KEY_FILE"
else
    echo "[skip] Key already exists at $KEY_FILE — reprinting"
fi

# Make sure pub key is in authorized_keys (idempotent)
PUB=$(cat "$KEY_FILE.pub")
if ! grep -qF "$PUB" "$AUTH_FILE" 2>/dev/null; then
    echo "$PUB" >> "$AUTH_FILE"
    chmod 600 "$AUTH_FILE"
    echo "[OK] Added public key to $AUTH_FILE"
else
    echo "[skip] Public key already in $AUTH_FILE"
fi

echo
echo '=== PUBLIC KEY (for reference) ==='
cat "$KEY_FILE.pub"
echo
echo '=== PRIVATE KEY (paste into GitHub secret VPS_SSH_KEY) ==='
echo '--- copy from BEGIN to END inclusive ---'
cat "$KEY_FILE"
echo '--- end of private key ---'
echo
echo 'GitHub secrets to create:'
echo '  VPS_HOST     = 103.227.147.234'
echo '  VPS_SSH_PORT = 2278'
echo '  VPS_USER     = root'
echo '  VPS_SSH_KEY  = (private key above, ENTIRE content including BEGIN/END lines)'

# Also ensure www-data can pull from the public github repo without auth
# prompts, and that the directory is marked safe for git as both www-data
# (when CI runs commands) and root (manual maintenance).
git config --system --add safe.directory /var/www/nexgear-store 2>/dev/null || true
echo
echo '[OK] Marked /var/www/nexgear-store as safe.directory in git system config'
