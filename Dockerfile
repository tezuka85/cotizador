# Utiliza la imagen oficial de PHP 8.2 como base
FROM php:8.2-fpm

# Copia los archivos composer.json y composer.lock al directorio /var/www/
COPY composer.json composer.lock /var/www/

# Establece el directorio de trabajo en /var/www/
WORKDIR /var/www/

# Instala las dependencias necesarias
RUN apt-get update && apt-get install -y \
    build-essential \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    git \
    curl \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libgd-dev \
    libmagickwand-dev \
    libbz2-dev \
    libpq-dev \
    libreadline-dev \
    libtidy-dev \
    libxslt1-dev \
    libjpeg-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libgmp-dev \
    libkrb5-dev \
    libldap2-dev \
    libpspell-dev \
    librecode-dev \
    libsnmp-dev \
    libsodium-dev \
    libsqlite3-dev \
    libxslt1-dev \
    libzip-dev \
    unzip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd bcmath xml

# Instala extensiones PECL
RUN pecl install imagick && \
    docker-php-ext-enable imagick

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia todos los archivos de la carpeta actual (archivos de Laravel) a /var/www/
COPY . /var/www

# Configura los directorios de almacenamiento
RUN mkdir -p /var/log/php-fpm/cotizaciones/storage/logs \
    /var/cache/php-fpm/cotizaciones/bootstrap/cache \
    /var/cache/php-fpm/cotizaciones/storage/debugbar/app/public \
    /var/cache/php-fpm/cotizaciones/storage/framework/views \
    /var/cache/php-fpm/cotizaciones/storage/framework/sessions \
    /var/cache/php-fpm/cotizaciones/storage/framework/cache \
    /var/cache/php-fpm/cotizaciones/storage/framework/cache/data \
    /var/cache/php-fpm/cotizaciones/storage/framework/testing \
    /var/cache/php-fpm/cotizaciones/storage/debugbar \
    /var/cache/php-fpm/cotizaciones/storage/app \
    /var/cache/php-fpm/cotizaciones/storage/app/public \
    /var/cache/php-fpm/cotizaciones/storage/app/web

# Copia los archivos oauth-private.key y oauth-public.key desde el directorio storage a /var/cache/php-fpm/cotizaciones/storage
COPY storage/oauth-private.key /var/cache/php-fpm/cotizaciones/storage/oauth-private.key
COPY storage/oauth-public.key /var/cache/php-fpm/cotizaciones/storage/oauth-public.key

# Asigna permisos a los directorios de almacenamiento y de arranque
RUN chown -R www-data:www-data /var/www \
    && chown -R www-data:www-data /var/log/php-fpm/cotizaciones/storage/logs \
    && chown -R www-data:www-data /var/cache/php-fpm/cotizaciones/bootstrap/cache \
    && chown -R www-data:www-data /var/cache/php-fpm/cotizaciones/storage

# Asigna permisos de escritura a los directorios necesarios
RUN chmod -R 775 /var/cache/php-fpm/cotizaciones/storage \
    /var/cache/php-fpm/cotizaciones/bootstrap/cache

# Expone el puerto 9000
EXPOSE 9000

# Comando de inicio para PHP-FPM
CMD ["php-fpm"]
