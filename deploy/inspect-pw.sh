#!/usr/bin/env bash
python3 << 'EOF'
import json
with open('/root/.openclaw/openclaw.json') as f:
    d = json.load(f)
pw = d.get('gateway', {}).get('auth', {}).get('password', '')
print(f"password length: {len(pw)}")
print(f"  ends with CR (\\r): {pw.endswith(chr(13))}")
print(f"  contains CR anywhere: {chr(13) in pw}")
print(f"  contains LF: {chr(10) in pw}")
print(f"  first 2 chars hex: {' '.join(format(ord(c), '02x') for c in pw[:2])}")
print(f"  last 2 chars hex: {' '.join(format(ord(c), '02x') for c in pw[-2:])}")
EOF
