#!/usr/bin/env bash
# Show where the DataByte API key resolves from, without echoing the key.
set -euo pipefail

python3 <<'PY'
import json
from pathlib import Path

CFG_MAIN  = Path('/root/.openclaw/openclaw.json')
CFG_AGENT = Path('/root/.openclaw/agents/main/agent/models.json')

def redact(v):
    if not isinstance(v, str) or len(v) < 12:
        return f"<{len(v) if isinstance(v, str) else 'n/a'} chars>"
    return f"{v[:6]}...{v[-4:]} (len={len(v)})"

def inspect(path):
    print(f"=== {path} ===")
    if not path.exists():
        print("(file does not exist)")
        return
    with path.open() as f:
        d = json.load(f)

    env_key = d.get('env', {}).get('DATABYTE_API_KEY')
    if env_key is not None:
        print(f"env.DATABYTE_API_KEY = {redact(env_key)}")

    providers = d.get('models', {}).get('providers', {})
    if 'databyte' in providers:
        api_key = providers['databyte'].get('apiKey')
        print(f"models.providers.databyte.apiKey = {redact(api_key) if api_key else '(unset)'}")
        print(f"  baseUrl  = {providers['databyte'].get('baseUrl')}")
        print(f"  api      = {providers['databyte'].get('api')}")
        print(f"  models   = {[m.get('id') for m in providers['databyte'].get('models', [])]}")
    else:
        print("(no databyte block)")
    print()

inspect(CFG_MAIN)
inspect(CFG_AGENT)
PY
