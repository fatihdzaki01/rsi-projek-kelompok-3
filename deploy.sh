#!/bin/bash
# Berbagive Deployment Script
set -e

echo "=== Berbagive Deployment ==="

# Check dependencies
command -v docker >/dev/null 2>&1 || { echo "Docker is required but not installed."; exit 1; }
command -v docker compose >/dev/null 2>&1 || { echo "Docker Compose is required but not installed."; exit 1; }

# Prepare .env from template if not exists
if [ ! -f .env ]; then
    echo "Creating .env from .env.docker template..."
    cp .env.docker .env
    echo "Generating APP_KEY..."
    php backend/artisan key:generate --show > /dev/null 2>&1 && \
        APP_KEY=$(php backend/artisan key:generate --show) && \
        sed -i "s/APP_KEY=.*/APP_KEY=${APP_KEY}/" .env || \
        echo "WARNING: Could not generate APP_KEY. Set it manually."
    echo "Edit .env with your production values before continuing."
    exit 0
fi

# Source env
set -a; source .env; set +a

# Build frontend
echo "Building frontend..."
if [ ! -d frontend/node_modules ]; then
    (cd frontend && npm install)
fi
(cd frontend && npm run build)

# Build and start Docker
echo "Building Docker images..."
docker compose build --pull

echo "Starting containers..."
docker compose up -d

# Wait for database to be ready
echo "Waiting for database..."
sleep 10

# Run Laravel setup
echo "Running Laravel setup..."
docker compose exec -T app php artisan key:generate --force
docker compose exec -T app php artisan migrate --force --seed
docker compose exec -T app php artisan storage:link

# Create MinIO bucket
echo "Creating MinIO bucket..."
docker compose exec -T minio mc alias set local http://localhost:9000 ${MINIO_ROOT_USER:-berbagive} ${MINIO_ROOT_PASSWORD:-berbagive123} 2>/dev/null || true
docker compose exec -T minio mc mb local/berbagive --ignore-existing 2>/dev/null || true
docker compose exec -T minio mc anonymous set public local/berbagive 2>/dev/null || true

echo ""
echo "=== Deployment complete! ==="
echo "Application: http://localhost:${HTTP_PORT:-80}"
echo "MinIO Console: http://localhost:9001 (login: ${MINIO_ROOT_USER:-berbagive})"
echo ""
echo "API Docs: http://localhost:${HTTP_PORT:-80}/api/v1/docs"
echo "Health:   http://localhost:${HTTP_PORT:-80}/api/v1/health"
