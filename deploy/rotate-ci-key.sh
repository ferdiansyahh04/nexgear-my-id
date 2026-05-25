#!/usr/bin/env bash
# Revoke the previously-leaked CI key and generate a fresh pair.
# Print only the public key — the private key is fetched separately via scp
# (so it never lands in shell logs / chat / clipboard history).
set -euo pipefail

KEY_DIR=/root/.ssh
KEY_FILE="$KEY_DIR/nexgear-ci"
AUTH_FILE="$KEY_DIR/authorized_keys"

# Revoke old public key from authorized_keys (if it exists)
if [ -f "$KEY_FILE.pub" ]; then
    OLD_PUB=$(cat "$KEY_FILE.pub")
    grep -v -F "$OLD_PUB" "$AUTH_FILE" > "$AUTH_FILE.tmp" 2>/dev/null || true
    mv "$AUTH_FILE.tmp" "$AUTH_FILE"
    chmod 600 "$AUTH_FILE"
    echo "[OK] Revoked old key from $AUTH_FILE"
fi

# Shred the leaked pair
shred -u "$KEY_FILE" "$KEY_FILE.pub" 2>/dev/null || true

# Generate fresh pair
ssh-keygen -t ed25519 -C "nexgear-ci@github-actions" -N '' -f "$KEY_FILE" >/dev/null
cat "$KEY_FILE.pub" >> "$AUTH_FILE"
chmod 600 "$AUTH_FILE"
echo "[OK] Generated new ed25519 key pair"

echo
echo '=== Public key (safe to share) ==='
cat "$KEY_FILE.pub"
echo
echo 'Private key is at /root/.ssh/nexgear-ci on the VPS.'
echo 'Fetch it from your laptop with:'
echo '  scp -P 2278 root@103.227.147.234:/root/.ssh/nexgear-ci $env:TEMP/nexgear-ci'
echo
echo 'Then paste its full contents into the VPS_SSH_KEY GitHub secret.'
echo 'Delete the local copy after creating the secret:'
echo '  Remove-Item $env:TEMP/nexgear-ci -Force'
