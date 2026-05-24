#!/usr/bin/env bash
# Test the full WebSocket upgrade handshake against chat.nexgear.my.id
# from inside the VPS (loopback through Cloudflare? No — directly to nginx
# via the public hostname, but resolving via /etc/hosts is unreliable, so
# use --resolve to pin to localhost).
# Note: curl returns non-zero for HTTP errors with -f, but here we DON'T
# want to abort on those — we want to see all responses. So no `set -e`.

USER_LINE=$(sed -n '1p' /root/.openclaw-creds)
PASS_LINE=$(sed -n '2p' /root/.openclaw-creds)

echo '=== plain HTTPS GET with auth (should be 200 from OpenClaw or 502/upstream) ==='
curl -sk -i -u "${USER_LINE}:${PASS_LINE}" --max-time 8 \
    --resolve chat.nexgear.my.id:443:127.0.0.1 \
    https://chat.nexgear.my.id/ 2>&1 | head -8

echo
echo '=== WS upgrade handshake with auth (should be 101 Switching Protocols) ==='
curl -sk -i -u "${USER_LINE}:${PASS_LINE}" --max-time 8 \
    -H 'Connection: Upgrade' \
    -H 'Upgrade: websocket' \
    -H 'Sec-WebSocket-Version: 13' \
    -H 'Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==' \
    --resolve chat.nexgear.my.id:443:127.0.0.1 \
    https://chat.nexgear.my.id/ 2>&1 | head -10

echo
echo '=== Direct loopback to OpenClaw (no nginx, no auth) ==='
curl -s -i --max-time 5 http://127.0.0.1:18789/ 2>&1 | head -10
