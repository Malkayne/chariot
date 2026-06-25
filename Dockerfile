# ==========================================
# Stage 1: Build frontend assets
# ==========================================
FROM node:18-alpine AS frontend-builder
WORKDIR /app

# Copy package files and install dependencies
COPY package.json ./
RUN npm install

# Copy Vite configuration and asset source files
COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

# Compile frontend assets
RUN npm run build

# ==========================================
# Stage 2: Production PHP and Apache environment
# ==========================================
FROM php:8.2-apache AS runner

# Install system dependencies and required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip gd bcmath opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Replace default Apache VirtualHost configuration
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Set default working directory
WORKDIR /var/www/html

# Copy application source code
COPY . .

# Copy compiled frontend assets from Stage 1
COPY --from=frontend-builder /app/public/build ./public/build

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install production PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions for storage and bootstrap cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy entrypoint script and clean Windows line-endings if any
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i -e 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose HTTP port 80
EXPOSE 80

# Configure entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
