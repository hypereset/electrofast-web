FROM php:8.2-apache

# 1. Instalar dependencias del sistema necesarias para GD (Gráficos)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Configurar e instalar extensiones de PHP (GD y MySQLi)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli \
    && docker-php-ext-enable gd mysqli

# 3. Activar mod_rewrite para URLs amigables
RUN a2enmod rewrite

# 4. Copiar todo el contenido al contenedor
COPY . /var/www/html/

# 5. Dar permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 6. Exponer puerto
EXPOSE 80