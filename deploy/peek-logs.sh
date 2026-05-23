#!/usr/bin/env bash
LOG="/var/www/nexgear-store/writable/logs/log-$(date +%Y-%m-%d).log"
if [ -f "$LOG" ]; then
    echo "=== $(basename "$LOG") (last 40 lines) ==="
    tail -40 "$LOG"
else
    echo "No log for today yet"
fi
echo
echo "=== nginx access (POST to /admin/security) ==="
grep -E 'POST /admin/security' /var/log/nginx/access.log 2>/dev/null | tail -5
