#!/usr/bin/env bash
# Remove HTTP Basic Auth from chat.nexgear.my.id vhost.
# OpenClaw's gateway.auth.password is the active auth layer.
set -euo pipefail

SERVER_FILE=/etc/nginx/sites-available/chat.nexgear.my.id

# Comment out (rather than delete) the auth_basic lines so they're easy to
# re-enable later if needed.
sed -i -E 's|^(\s*)(auth_basic[^;]*;)|\1# \2  # disabled — incompatible with browser WebSocket; OpenClaw password handles auth|' "$SERVER_FILE"

echo '=== auth_basic lines (now commented out) ==='
grep -n auth_basic "$SERVER_FILE" || echo '(none — nothing to comment)'

echo
nginx -t && systemctl reload nginx
echo '[OK] nginx reloaded — Basic Auth disabled on chat.nexgear.my.id'

# Optional: shred the htpasswd file since we no longer use it
if [ -f /etc/nginx/.openclaw-htpasswd ]; then
    shred -u /etc/nginx/.openclaw-htpasswd 2>/dev/null || rm -f /etc/nginx/.openclaw-htpasswd
    echo '[OK] /etc/nginx/.openclaw-htpasswd shredded'
fi

# Also drop the saved Basic Auth credentials — no longer needed
if [ -f /root/.openclaw-creds ]; then
    shred -u /root/.openclaw-creds 2>/dev/null || rm -f /root/.openclaw-creds
    echo '[OK] /root/.openclaw-creds shredded'
fi

echo
echo '=== smoke test ==='
curl -sk -o /dev/null -w 'GET /: %{http_code} (expect 200)\n' https://chat.nexgear.my.id/
