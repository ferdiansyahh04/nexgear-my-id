#!/usr/bin/env bash
# Add the DataByte AI provider config to OpenClaw without disturbing
# anything else (gateway auth, allowedOrigins, ollama provider, etc.).
#
# Strategy: Python deep-merge with a backup of the existing config.
# Idempotent — re-running just rewrites the same keys.
set -euo pipefail

CONFIG=/root/.openclaw/openclaw.json
BACKUP="${CONFIG}.bak-$(date +%Y%m%d-%H%M%S)"

if [ ! -f "$CONFIG" ]; then
    echo "ERROR: $CONFIG not found"; exit 1
fi

cp -p "$CONFIG" "$BACKUP"
echo "[OK] Backup written to $BACKUP"

python3 <<'PY'
import json
from pathlib import Path

CONFIG = Path('/root/.openclaw/openclaw.json')

with CONFIG.open() as f:
    cfg = json.load(f)

# 1. env.DATABYTE_API_KEY — placeholder if not already set; user fills in later
env = cfg.setdefault('env', {})
if 'DATABYTE_API_KEY' not in env:
    env['DATABYTE_API_KEY'] = 'sk-db-xxxx'  # placeholder — user replaces this
    print('[OK] env.DATABYTE_API_KEY added (placeholder — replace with real key)')
else:
    print('[skip] env.DATABYTE_API_KEY already set — preserved as-is')

# 2. models.providers.databyte
providers = cfg.setdefault('models', {}).setdefault('providers', {})
providers['databyte'] = {
    'baseUrl': 'https://ai.databyte.co.id/v1',
    'apiKey': '${DATABYTE_API_KEY}',
    'api': 'openai-completions',
    'models': [
        {'id': 'databyte-m1', 'name': 'DataByte M1'},
    ],
}
print('[OK] models.providers.databyte set')

# Pretty-print, preserve trailing newline
with CONFIG.open('w') as f:
    json.dump(cfg, f, indent=2, ensure_ascii=False)
    f.write('\n')

print()
print('=== validation ===')
PY

# Validate via OpenClaw itself — catches any schema issues early
openclaw config validate 2>&1 | tail -10

echo
echo '=== databyte block (apiKey redacted) ==='
python3 -c "
import json
cfg = json.load(open('/root/.openclaw/openclaw.json'))
db = cfg['models']['providers']['databyte']
print(json.dumps(db, indent=2))
print()
print('env.DATABYTE_API_KEY length:', len(cfg.get('env', {}).get('DATABYTE_API_KEY', '')))
print('(replace this placeholder with your real key via:')
print(\"   openclaw config set env.DATABYTE_API_KEY '\\\"sk-db-...\\\"' --strict-json )\")
"

# Permissions: keep root-only
chmod 600 "$CONFIG"
echo
echo "[DONE] Restart OpenClaw to pick up provider changes:"
echo "       sudo pkill -f 'openclaw.*gateway'   # supervisor will respawn"
