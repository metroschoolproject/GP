#!/bin/bash
# Auto-restart tunnel script — keeps your tunnel alive
# Usage: bash tunnel.sh

echo "🔄 Starting tunnel to localhost:80..."
echo "   Press Ctrl+C to stop"
echo ""

while true; do
    echo "[$(date +%H:%M:%S)] Connecting..."
    ssh -o StrictHostKeyChecking=no \
        -o ServerAliveInterval=15 \
        -o ServerAliveCountMax=5 \
        -o ConnectTimeout=15 \
        -o TCPKeepAlive=yes \
        -o ExitOnForwardFailure=yes \
        -R 80:localhost:80 serveo.net 2>&1 | while read line; do
        clean=$(echo "$line" | sed 's/\x1b\[[0-9;]*m//g')
        echo "  $clean"
        if echo "$clean" | grep -q "Forwarding"; then
            URL=$(echo "$clean" | grep -o 'https://[^ ]*')
            echo ""
            echo "✅ Tunnel active: $URL/GP/"
            echo ""
        fi
    done

    EXIT_CODE=$?
    echo "[$(date +%H:%M:%S)] Tunnel dropped (exit=$EXIT_CODE). Restarting in 2s..."
    sleep 2
done
