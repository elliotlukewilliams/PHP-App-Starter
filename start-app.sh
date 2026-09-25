#!/bin/bash

set -e  # stop script on error

# Create local env file from the example if missing
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

echo "Starting Docker containers..."
docker-compose up -d --build

echo "Waiting for the database to be ready..."

# Poll MYSQL until it accepts connections - maximum of 60 attempts
for attempt in $(seq 1 60); do
    if docker-compose exec -T web php -r 'new PDO("mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT"), getenv("DB_USER"), getenv("DB_PASSWORD"));' > /dev/null 2>&1; then
        break
    fi
    if [ "$attempt" -eq 60 ]; then
        echo "Database did not become ready in time" >&2
        exit 1
    fi
    sleep 2
done

echo "Installing Composer dependencies..."
# Run as the host user so vendor/ isn't owned by root on the mounted project folder
docker-compose exec -T -u "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer web composer install --no-interaction

echo "Making uploads directory writable by Apache..."
docker-compose exec -T web chown www-data:www-data public/images/uploads

echo "Running migrations..."
docker-compose exec -T web php migrations/index.php

echo "App is ready!"