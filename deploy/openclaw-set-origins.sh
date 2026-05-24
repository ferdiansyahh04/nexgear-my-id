#!/usr/bin/env bash
# Whitelist the public chat hostname as a Control UI origin so OpenClaw
# accepts WebSocket connections from the dashboard served at our domain.
set -euo pipefail

# JSON array of allowed origins. Add 127.0.0.1 too so local SSH-tunnel use
# still works alongside the public hostname.
openclaw config set gateway.controlUi.allowedOrigins \
    '["https://chat.nexgear.my.id", "http://127.0.0.1:18789"]' \
    --strict-json

echo
echo '=== verify ==='
openclaw config get gateway.controlUi.allowedOrigins

echo
echo '=== restart OpenClaw ==='
PID=$(pgrep -f 'openclaw|node.*18789' | head -1)
if [ -n "$PID" ]; then
    echo "Killing pid $PID — supervisor should respawn"
    kill "$PID" 2>/dev/null || true
fi

# Wait up to 20s for the gateway to come back
for i in $(seq 1 20); do
    if ss -tlnp 2>/dev/null | grep -q ':18789'; then
        echo "[OK] OpenClaw back up after ${i}s"
        exit 0
    fi
    sleep 1
done

echo '[WARN] OpenClaw did not come back automatically'
echo 'You may need to start it manually with: openclaw gateway run'
exit 1
