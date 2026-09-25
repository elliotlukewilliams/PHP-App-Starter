#!/bin/bash

set -e  # stop script on error

# Create local env file from the example if missing
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

echo "Starting Docker containers..."
docker-compose up -d --build

echo "Waiting for containers to be ready..."
sleep 5

echo "Installing Composer dependencies..."
# Run as the host user so vendor/ isn't owned by root on the mounted project folder
docker-compose exec -T -u "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer web composer install --no-interaction

echo "Making uploads directory writable by Apache..."
docker-compose exec web chown www-data:www-data public/images/uploads

echo "Running migrations..."
docker-compose exec web php migrations/index.php

echo "App is ready!"