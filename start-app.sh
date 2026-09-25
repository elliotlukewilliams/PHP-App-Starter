#!/bin/bash

set -e  # stop script on error

echo "Starting Docker containers..."
docker-compose up -d --build

echo "Waiting for containers to be ready..."
sleep 5

echo "Running migrations..."
docker-compose exec web php migrations/index.php

echo "App is ready!"