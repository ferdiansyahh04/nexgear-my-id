#!/usr/bin/env bash
# Wire Resend SMTP into the app's .env and send a verification message.
#
# Resend SMTP credentials:
#   host: smtp.resend.com
#   port: 465 (TLS) or 587 (STARTTLS)
#   user: resend
#   pass: <API key from /root/.resend-key>
#
# Idempotent — re-running just rewrites the email.* lines in .env.
set -euo pipefail

ENV_FILE=/var/www/nexgear-store/.env
KEY_FILE=/root/.resend-key
FROM_EMAIL='hello@nexgear.my.id'
FROM_NAME='NexGear Store'

if [ ! -f "$KEY_FILE" ]; then
    echo "ERROR: $KEY_FILE missing — upload the Resend API key first"
    exit 1
fi

# Trim any trailing whitespace/newlines from the key file
RESEND_KEY=$(tr -d '[:space:]' < "$KEY_FILE")
KEY_LEN=${#RESEND_KEY}
if [ "$KEY_LEN" -lt 20 ]; then
    echo "ERROR: API key looks too short ($KEY_LEN chars). Aborting."
    exit 1
fi
echo "Resend API key loaded ($KEY_LEN chars)"

# Strip any pre-existing email.* lines (commented or not) so we don't get drift
sed -i '/^[#[:space:]]*email\./d' "$ENV_FILE"

# Append the production block. Use port 587 + STARTTLS — works on more VPS
# providers than 465 SSL because some block 465 outbound by default.
cat >> "$ENV_FILE" <<EOF

# ── Resend SMTP (production) ──
email.protocol     = smtp
email.fromEmail    = ${FROM_EMAIL}
email.fromName     = '${FROM_NAME}'
email.SMTPHost     = smtp.resend.com
email.SMTPUser     = resend
email.SMTPPass     = ${RESEND_KEY}
email.SMTPPort     = 587
email.SMTPCrypto   = tls
email.SMTPTimeout  = 10
email.mailType     = html
email.charset      = UTF-8
EOF

# Lock down .env permissions (it now has the API key inside)
chown www-data:www-data "$ENV_FILE"
chmod 640 "$ENV_FILE"

# Clear cache so config is re-read
sudo -u www-data php /var/www/nexgear-store/spark cache:clear >/dev/null

echo
echo '=== email.* in .env (key redacted) ==='
grep '^email\.' "$ENV_FILE" | sed -E 's/(email\.SMTPPass\s*=\s*).*$/\1[REDACTED]/'

echo
echo '=== .env permissions ==='
ls -la "$ENV_FILE"
