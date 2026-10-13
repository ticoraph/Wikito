#!/bin/bash

# Load environment variables
if [ -f .env ]; then
    export $(cat .env | grep -v '#' | awk '/=/ {print $1}')
else
    echo "Error: .env file not found. Please copy .env.example to .env and fill in the credentials."
    exit 1
fi

if [ -z "$HTPASSWD_PATH" ]; then
    echo "Error: HTPASSWD_PATH is not set in .env (absolute path of .htpasswd on the server)."
    exit 1
fi

# Generate the .htaccess with the server-specific .htpasswd path
HTACCESS_TMP="$(mktemp)"
trap 'rm -f "$HTACCESS_TMP"' EXIT
sed "s|__HTPASSWD_PATH__|$HTPASSWD_PATH|" ../.htaccess > "$HTACCESS_TMP"

echo "Deploying to SFTP: $FTP_HOST:22"

# .htaccess is uploaded separately (generated above).
# .htpasswd and data/glossary.json live on the server and are never overwritten.
lftp -d -c "
set sftp:auto-confirm yes
set sftp:connect-program 'ssh -a -x -o StrictHostKeyChecking=no'
open -u $FTP_USER,$FTP_PASSWORD sftp://$FTP_HOST:22
mirror -R -v \
    --exclude-glob .git/ \
    --exclude-glob .gitignore \
    --exclude-glob .htaccess \
    --exclude-glob .htpasswd \
    --exclude-glob README.md \
    --exclude-glob scripts/ \
    --exclude-glob automation/ \
    --exclude-glob data/glossary.json \
    --exclude-glob data/glossary.example.json \
    --exclude-glob .DS_Store \
    ../ $FTP_REMOTE_DIR
put -O $FTP_REMOTE_DIR $HTACCESS_TMP -o .htaccess
bye
"

echo "Deployment finished."
