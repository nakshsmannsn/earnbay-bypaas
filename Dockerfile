FROM php:8.2-apache

# Install system dependencies for curl
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    && docker-php-ext-install curl

COPY . /var/www/html

EXPOSE 8080
CMD ["apache2-foreground"]
