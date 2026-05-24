#!/usr/bin/env bash
# Approve OpenClaw device pairing requests using the gateway token.
# Pulls token from the active config so we never echo it.
set -euo pipefail

TOKEN=$(python3 -c 'import json; print(json.load(open("/root/.openclaw/openclaw.json"))["gateway"]["auth"]["token"])')

if [ -z "$TOKEN" ]; then
    echo "ERROR: token empty"; exit 1
fi

REQUEST_ID="${1:-}"

if [ -z "$REQUEST_ID" ]; then
    echo "Approving the most recent pending pairing..."
    openclaw devices approve --token "$TOKEN" --latest 2>&1 | tail -20
else
    echo "Approving pairing $REQUEST_ID..."
    openclaw devices approve --token "$TOKEN" "$REQUEST_ID" 2>&1 | tail -20
fi

echo
echo '=== Status after ==='
openclaw devices list --token "$TOKEN" 2>&1 | tail -20
