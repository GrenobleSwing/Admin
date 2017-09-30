#! /bin/bash

# Create SSL keys for the creation of JWT
docker-compose exec -T php mkdir -p var/jwt
docker-compose exec -T php openssl genrsa -out var/jwt/private.pem -aes256 -passout pass:untruc 4096
docker-compose exec -T php openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem -passin pass:untruc

# Copy config file for the tests
docker-compose exec -T php cp app/config/parameters.yml.dist app/config/parameters.yml

# Install dependencies
docker-compose exec -T php composer install

# Create database and tables
docker-compose exec -T php php bin/console doctrine:database:create
docker-compose exec -T php php bin/console doctrine:schema:create

# Load some fixtures to test the app
docker-compose exec -T php php bin/console doctrine:fixtures:load -n

# Warmup the cache
docker-compose exec -T php php bin/console cache:clear --no-warmup
docker-compose exec -T php php bin/console cache:warmup

