#!/usr/bin/env bash
# UFW firewall setup — allow rules first, then enable.
#
# Order matters: if we 'ufw enable' before adding the SSH rule, an active
# session keeps working but new connections get blocked → reboot would
# lock us out. Adding the rule first is safe.
set -euo pipefail

echo '=== Current UFW status ==='
ufw status verbose || true

echo
echo '=== Adding rules ==='
# SSH on the custom port (2278). 'ufw allow 22' would NOT cover this.
ufw allow 2278/tcp comment 'SSH custom port'

# Nginx Full = 80 + 443 in one shortcut.
ufw allow 'Nginx Full'

# Outgoing stays open (apt, composer, certbot renewal, MailerService SMTP).
ufw default deny incoming
ufw default allow outgoing

echo
echo '=== Rules staged. Enabling now... ==='
# --force skips the interactive y/N prompt
ufw --force enable

echo
echo '=== Final status ==='
ufw status verbose
