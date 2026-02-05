#!/bin/bash

# Docker Start Script - Build and start all containers
# Use this to start the project for the first time or after clean

echo "🔨 Building and starting containers..."
docker-compose up --build -d

echo ""
echo "⏳ Waiting for services to be ready..."
sleep 5

echo ""
echo "✅ Services started! Access the application at:"
echo "   http://localhost:8000"
echo ""
echo "📊 Database is available at:"
echo "   Host: localhost"
echo "   Port: 3306"
echo "   Database: mobileservice_db"
echo "   User: digitalmart"
echo "   Password: Genius@2026"

