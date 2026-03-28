# Use official PHP with Apache as base image
FROM php:8.2-apache

# 1. Install system dependencies required for MongoDB and Redis extensions
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# 2. Install MySQL extension (built-in)
RUN docker-php-ext-install mysqli

# 3. Install MongoDB and Redis extensions via PECL
# Then enable them in PHP configuration
RUN pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis

# 4. Enable Apache mod_rewrite for cleaner URLs
RUN a2enmod rewrite

# 5. Set the working directory
WORKDIR /var/www/html

# 6. Copy project files into the container's web root
COPY . /var/www/html/

# 7. CRITICAL: Configure Apache to look for index.html first and fix 403 Forbidden
# We create a .htaccess file if it doesn't exist
RUN echo "DirectoryIndex index.html index.php" > /var/www/html/.htaccess

# 8. Set robust permissions for the web server user (www-data)
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# 9. Expose port 80 for Render
EXPOSE 80

# 10. Start Apache
CMD ["apache2-foreground"]
