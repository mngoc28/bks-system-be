#!/bin/bash

# Copy .env.dev to .env
cp /var/www/.env.dev /var/www/.env

# Install Composer dependencies
composer install --ignore-platform-reqs
composer update
# Generate application key
# php artisan key:generate

# Migration
# php artisan migrate

# Optimize Laravel cache
php artisan optimize

# Install npm
npm install

# Run API DOC
cd api-doc
npm install apidoc -g
apidoc -i . -o ../public/apidoc

# # Ensure the correct permissions
chown -R www-data:www-data /var/www
chmod -R 755 /var/www
