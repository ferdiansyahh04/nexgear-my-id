#!/usr/bin/env bash
# Set a fresh OpenClaw gateway.auth.password sourced from /root/.openclaw-pw,
# then restart OpenClaw and clean up the temp file.
#
# /root/.openclaw-pw must be a single line (no trailing CR), the desired
# password. The file is shredded after use.
set -euo pipefail

PW_FILE=/root/.openclaw-pw
if [ ! -f "$PW_FILE" ]; then
    echo "ERROR: $PW_FILE missing — upload the password first."
    exit 1
fi

# Strip CR (Windows line endings)
sed -i 's/\r$//' "$PW_FILE"
PW=$(cat "$PW_FILE")
PW_LEN=${#PW}

if [ "$PW_LEN" -lt 8 ]; then
    echo "ERROR: password too short ($PW_LEN chars). Aborting."
    exit 1
fi
echo "Loaded password ($PW_LEN chars)"

# Set the password (value passed as JSON-encoded string so any special chars are safe)
JSON_PW=$(printf '%s' "$PW" | python3 -c 'import json,sys; print(json.dumps(sys.stdin.read()))')

# OpenClaw config set with --strict-json so the value is parsed as JSON string
openclaw config set gateway.auth.password "$JSON_PW" --strict-json
echo "[OK] password configured"

# Find and restart the running OpenClaw process so it picks up the new config
echo
echo '=== Restarting OpenClaw ==='
# Try systemd first
if systemctl list-unit-files 2>/dev/null | grep -q openclaw; then
    systemctl restart openclaw && echo '[OK] systemd restart' || true
else
    # Fall back to killing the node process and letting whatever supervisor restarts it
    PID=$(pgrep -f 'openclaw|node.*18789' | head -1)
    if [ -n "$PID" ]; then
        echo "Killing pid $PID — process supervisor should respawn it"
        kill "$PID" 2>/dev/null || true
        sleep 2
    fi
fi

# Verify it came back up
sleep 1
if ss -tlnp 2>/dev/null | grep -q ':18789'; then
    echo '[OK] OpenClaw listening on 18789'
else
    echo '[WARN] OpenClaw not listening yet — give it a few seconds'
fi

# Shred the password file (no plaintext lingering)
shred -u "$PW_FILE" 2>/dev/null || rm -f "$PW_FILE"
echo "[OK] password file shredded"
