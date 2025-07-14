FROM php:8.2-apache
COPY . /var/www/html
RUN docker-php-ext-install curl
EXPOSE 8080
CMD ["apache2-foreground"]