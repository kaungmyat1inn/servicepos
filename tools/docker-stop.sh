#!/bin/bash

# Docker Stop Script - Stop all containers without removing data
# Use this to stop the project temporarily

echo "🛑 Stopping all containers..."
docker-compose stop

echo "✅ Containers stopped! Data is preserved."
echo ""
echo "To start again, run:"
echo "  docker-compose start"

