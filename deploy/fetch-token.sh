#!/usr/bin/env bash
# Fetch the gateway auth token from OpenClaw config and write it to a file
# we'll explicitly fetch by name (so it doesn't leak in shell history).
set -euo pipefail

OUT=/root/.openclaw-token
python3 << 'EOF' > "$OUT"
import json
with open('/root/.openclaw/openclaw.json') as f:
    d = json.load(f)
tok = d.get('gateway', {}).get('auth', {}).get('token', '')
if not tok:
    raise SystemExit("ERROR: gateway.auth.token is empty")
print(tok, end='')
EOF
chmod 600 "$OUT"

echo "[OK] Token written to $OUT (chmod 600)"
echo "    length: $(wc -c < $OUT) bytes"
echo "    first 4 chars: $(head -c 4 $OUT)..."
echo
echo "Retrieve from your laptop with:"
echo "  scp -P 2278 root@103.227.147.234:/root/.openclaw-token C:/Users/Admin/Desktop/openclaw-token.txt"
echo
echo "After copying, delete it from the VPS:"
echo "  ssh -p 2278 root@103.227.147.234 'shred -u /root/.openclaw-token'"
