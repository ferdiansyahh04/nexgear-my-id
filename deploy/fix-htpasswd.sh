#!/usr/bin/env bash
# Re-build /etc/nginx/.openclaw-htpasswd from /root/.openclaw-creds, after
# stripping any CRLF artefacts from the upload pipeline.
set -euo pipefail

CREDS=/root/.openclaw-creds
HTPASSWD=/etc/nginx/.openclaw-htpasswd

if [ ! -f "$CREDS" ]; then
    echo "ERROR: $CREDS missing"; exit 1
fi

# Normalise line endings — strip CR, ensure trailing LF
sed -i 's/\r$//' "$CREDS"

USER_LINE=$(sed -n '1p' "$CREDS")
PASS_LINE=$(sed -n '2p' "$CREDS")

if [ -z "$USER_LINE" ] || [ -z "$PASS_LINE" ]; then
    echo "ERROR: creds file must have username on line 1, password on line 2"
    exit 1
fi

htpasswd -B -b -c "$HTPASSWD" "$USER_LINE" "$PASS_LINE" >/dev/null
chmod 640 "$HTPASSWD"
chown root:www-data "$HTPASSWD"

echo "[OK] htpasswd rebuilt for user: $USER_LINE"
echo "    file: $HTPASSWD"

nginx -t && systemctl reload nginx
echo "[OK] nginx reloaded"

# Optional: server-side verify the hash actually matches the supplied pw
if htpasswd -v -b "$HTPASSWD" "$USER_LINE" "$PASS_LINE" >/dev/null 2>&1; then
    echo "[VERIFY] password verifies against the stored hash"
else
    echo "[VERIFY] FAILED — something is still off"
    exit 1
fi
