#!/usr/bin/env bash
# Provision chat.nexgear.my.id reverse-proxy to OpenClaw (127.0.0.1:18789).
#
# Steps:
#  1. Install apache2-utils (provides htpasswd)
#  2. Drop the nginx server block (HTTP only first, certbot adds 443)
#  3. Add the global "$connection_upgrade" map for WebSocket support
#  4. Test nginx config + reload
#  5. certbot --nginx for chat.nexgear.my.id (HTTP-01 challenge)
#  6. Final test
#
# Auth credentials get loaded separately via /root/.openclaw-creds (see README).
set -euo pipefail

VHOST=chat.nexgear.my.id
WS_MAP=/etc/nginx/conf.d/upgrade-map.conf
SERVER_FILE=/etc/nginx/sites-available/${VHOST}

echo '=== Install apache2-utils (htpasswd) ==='
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq apache2-utils >/dev/null
echo '[OK] htpasswd available:' "$(htpasswd -h 2>&1 | head -1 | tr -d '\n')"

echo
echo '=== Add WebSocket upgrade map (idempotent) ==='
if [ ! -f "$WS_MAP" ]; then
    cat > "$WS_MAP" <<'EOF'
# Used by reverse-proxy server blocks that need to forward websocket upgrades.
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
EOF
    echo "[OK] created $WS_MAP"
else
    echo "[skip] $WS_MAP already exists"
fi

echo
echo '=== Stage server block ==='
# /tmp transfer happens before this script runs; pick it up if available
if [ -f /tmp/nginx-chat.conf.template ]; then
    cp /tmp/nginx-chat.conf.template "$SERVER_FILE"
else
    echo 'ERROR: /tmp/nginx-chat.conf.template missing — upload it first via scp.'
    exit 1
fi

ln -sf "$SERVER_FILE" "/etc/nginx/sites-enabled/${VHOST}"

echo
echo '=== Build htpasswd from /root/.openclaw-creds ==='
if [ ! -f /root/.openclaw-creds ]; then
    echo 'ERROR: /root/.openclaw-creds missing.'
    echo 'Upload it via:'
    echo '  echo -e "username\npassword" | ssh root@host "cat > /root/.openclaw-creds && chmod 600 /root/.openclaw-creds"'
    exit 1
fi
USER_LINE=$(sed -n '1p' /root/.openclaw-creds)
PASS_LINE=$(sed -n '2p' /root/.openclaw-creds)
if [ -z "$USER_LINE" ] || [ -z "$PASS_LINE" ]; then
    echo 'ERROR: /root/.openclaw-creds must contain "username\\npassword".'
    exit 1
fi
htpasswd -B -b -c /etc/nginx/.openclaw-htpasswd "$USER_LINE" "$PASS_LINE" >/dev/null
chmod 640 /etc/nginx/.openclaw-htpasswd
chown root:www-data /etc/nginx/.openclaw-htpasswd
echo "[OK] htpasswd written for user: $USER_LINE"

echo
echo '=== nginx -t (HTTP-only stage) ==='
nginx -t

echo
echo '=== reload nginx ==='
systemctl reload nginx
echo '[OK] reloaded'

echo
echo '=== certbot HTTP-01 challenge ==='
certbot --nginx \
    -d "$VHOST" \
    --email ferdiansyahh670@gmail.com \
    --agree-tos --no-eff-email --redirect \
    --non-interactive 2>&1 | tail -15

echo
echo '=== Final reload + test ==='
nginx -t && systemctl reload nginx
curl -sk -o /dev/null -w "https://${VHOST}/ → %%{http_code} (auth required = 401)\n" https://${VHOST}/

echo
echo '[DONE] OpenClaw reverse proxy live at https://'"${VHOST}"'/'
echo '       Behind HTTP Basic Auth (user: '"$USER_LINE"')'
