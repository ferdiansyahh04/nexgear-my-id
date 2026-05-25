#!/usr/bin/env bash
# Inspect what's currently in the OpenClaw config without leaking secrets.
set -euo pipefail

CONFIG=/root/.openclaw/openclaw.json

if [ ! -f "$CONFIG" ]; then
    echo "ERROR: $CONFIG not found"; exit 1
fi

ls -la "$CONFIG"
echo
echo '=== top-level keys ==='
python3 -c "import json; print(sorted(json.load(open('$CONFIG')).keys()))"

echo
echo '=== models.providers (key names only) ==='
python3 -c "
import json
d = json.load(open('$CONFIG'))
providers = d.get('models', {}).get('providers', {})
print(sorted(providers.keys()) if providers else '(none)')
"

echo
echo '=== env keys (names only, NOT values) ==='
python3 -c "
import json
d = json.load(open('$CONFIG'))
env = d.get('env', {})
print(sorted(env.keys()) if env else '(none)')
"

echo
echo '=== gateway settings (sanity check that auth + origins still set) ==='
python3 -c "
import json
d = json.load(open('$CONFIG'))
gw = d.get('gateway', {})
auth = gw.get('auth', {})
control = gw.get('controlUi', {})
print('  token set:', bool(auth.get('token')))
print('  password set:', bool(auth.get('password')))
print('  allowedOrigins:', control.get('allowedOrigins', []))
"
