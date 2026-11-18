FROM php:8.3-apache

# Extension cần cho CodeIgniter 4 + MySQL
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev \
    && docker-php-ext-install mysqli intl zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Apache trỏ vào thư mục public/ của CodeIgniter và cho phép .htaccess
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername
COPY docker/php/apache.conf /etc/apache2/conf-available/nova.conf
RUN a2enconf nova

COPY docker/php/entrypoint.sh /usr/local/bin/nova-entrypoint
RUN chmod +x /usr/local/bin/nova-entrypoint

WORKDIR /var/www/html
ENTRYPOINT ["nova-entrypoint"]
CMD ["apache2-foreground"]
