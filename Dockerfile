# Use official PHP with Apache as base image
FROM php:8.2-apache

# Install system dependencies required for MongoDB and Redis extensions
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Install MySQL extension (built-in)
RUN docker-php-ext-install mysqli

# Install MongoDB and Redis extensions via PECL
# Then enable them in PHP configuration
RUN pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis

# Enable Apache mod_rewrite for cleaner URLs (optional but standard)
RUN a2enmod rewrite

# Copy project files into the container's web root
COPY . /var/www/html/

# Ensure web server has permission to read/write files
RUN chown -R www-data:www-data /var/www/html/

# Expose port 80 for Render to map
EXPOSE 80

# Default command starts Apache in the foreground
CMD ["apache2-foreground"]
