#!/bin/bash

# Docker Restart Script - Restart all containers
# Use this to restart the project (stops and starts)

echo "🛑 Stopping containers..."
docker-compose stop

echo "⏳ Waiting 2 seconds..."
sleep 2

echo "🚀 Starting containers..."
docker-compose start

echo "✅ Containers restarted!"
echo ""
echo "To view logs, run:"
echo "  ./tools/docker-logs.sh"

