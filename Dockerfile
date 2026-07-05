# Use an official PHP image
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    supervisor \
    cron \
    nodejs \
    npm \
    libonig-dev \
    nginx

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Configure PHP-FPM to inherit system environment variables (for Render Dashboard variables)
RUN echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:2.8.1 /usr/bin/composer /usr/bin/composer

COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy Nginx configuration
COPY ./docker/nginx/default.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Copy Cron configuration
COPY ./docker/cron/crontab /etc/cron.d/laravel-cron

# Give execute permissions to cron file
RUN chmod 0644 /etc/cron.d/laravel-cron

# Apply cron job
RUN crontab /etc/cron.d/laravel-cron

WORKDIR /var/www

COPY . /var/www

RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www

RUN mkdir /var/log/supervisord/
RUN chown -R www-data:www-data /var/log/supervisord/
RUN chmod -R 755 /var/log/supervisord/

RUN bash ./setup.sh

VOLUME ["/var/www"]

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
