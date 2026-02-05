#!/bin/bash

# Docker Logs Script - View container logs
# Usage: ./docker-logs.sh [service_name] [--follow]

SERVICE=${1:-app}
FOLLOW=$2

echo "📋 Viewing logs for: $SERVICE"

if [ "$FOLLOW" = "--follow" ] || [ "$FOLLOW" = "-f" ]; then
    docker-compose logs -f "$SERVICE"
else
    docker-compose logs --tail=100 "$SERVICE"
fi

