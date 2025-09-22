#!/bin/bash

# Docker Setup Script for Laravel Order Management System
echo "Setting up Docker environment for Order Management System..."

# Build and start containers
echo "Building and starting Docker containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
sleep 30

# Install PHP dependencies
echo "Installing PHP dependencies..."
docker-compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Wait a moment for files to be ready
echo "Waiting for files to be ready..."
sleep 5

# Generate application key
echo "Generating application key..."
docker-compose exec app php artisan key:generate

# Run database migrations
echo "Running database migrations..."
docker-compose exec app php artisan migrate --force

# Seed the database
echo "Seeding the database..."
docker-compose exec app php artisan db:seed --force

# Set proper permissions
echo "Setting proper permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/bootstrap/cache

# Clear caches
echo "Clearing caches..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

echo "Docker setup complete!"
echo ""
echo "Application is now available at:"
echo "   - Main App: http://localhost:8080"
echo ""
