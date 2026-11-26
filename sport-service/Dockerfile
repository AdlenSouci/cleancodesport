# Image du sport-service (PHP), construite en deux étapes.

# Étape 1 : installer les dépendances avec Composer (outil absent de l'image finale)
FROM composer:2 AS dependances
WORKDIR /app
COPY composer.json composer.lock ./
# --no-dev : PHPUnit et les outils de test ne partent pas en production
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-autoloader
COPY src/ src/
RUN composer dump-autoload --no-dev --optimize

# Étape 2 : image d'exécution, PHP + extension PostgreSQL (utile au TP-02)
FROM php:8.3-cli
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY . .
COPY --from=dependances /app/vendor/ vendor/

# Contrat d'intégration (README) : écoute sur 3000 dans le conteneur
EXPOSE 3000
# Serveur intégré de PHP, suffisant pour le projet ; public/index.php reçoit toutes les requêtes
CMD ["php", "-S", "0.0.0.0:3000", "public/index.php"]
