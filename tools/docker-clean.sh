#!/bin/bash

# Docker Clean Script - Stop and remove all containers, networks, and volumes
# Use this to completely clean up the Docker environment

echo "🛑 Stopping all containers..."
docker-compose down

echo "🗑️  Removing containers..."
docker-compose rm -f

echo "🗑️  Removing volumes..."
docker-compose down -v

echo "🗑️  Removing networks..."
docker network rm laravel-network 2>/dev/null || true

echo "✅ Docker environment cleaned completely!"

echo ""
echo "To rebuild and start the project, run:"
echo "  docker-compose up --build -d"

