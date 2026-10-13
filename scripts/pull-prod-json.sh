#!/bin/bash

# Load environment variables
if [ -f .env ]; then
    export $(cat .env | grep -v '#' | awk '/=/ {print $1}')
else
    echo "Error: .env file not found. Please copy .env.example to .env and fill in the credentials."
    exit 1
fi

echo "Pulling glossary.json from SFTP: $FTP_HOST:22"

lftp -c "
set xfer:clobber on
set sftp:auto-confirm yes
set sftp:connect-program 'ssh -a -x -o StrictHostKeyChecking=no'
open -u $FTP_USER,$FTP_PASSWORD sftp://$FTP_HOST:22
get $FTP_REMOTE_DIR/data/glossary.json -o ../data/glossary.json
bye
"

echo "Pull finished."
