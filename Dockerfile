FROM php:8.1.22RC1-zts-bullseye

# Install composer
COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer

# Install dependencies php8.1-bcmath php8.1-dom php8.1-mbstring php8.1-curl
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    git \
    unzip \
    python3.9 \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql bcmath dom mbstring curl pcntl

# Run composer install
# COPY composer.json composer.lock artisan /app/
# COPY database /app/database
# COPY tests /app/tests
# COPY bootstrap /app/bootstrap
COPY . /app
WORKDIR /app
RUN composer install
# COPY . /app

# set SQLite database environment variables

# ENV DB_CONNECTION=sqlite
# ENV DB_DATABASE=/data/database/db.sqlite

#  Run artisan commands
RUN php artisan key:generate
RUN php artisan config:cache

# RUN php artisan migrate

# RUN php artisan config:cache

# RUN php artisan alttp:updatebuildrecord

