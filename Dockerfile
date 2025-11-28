FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Activar mod_rewrite para URLs amigables
RUN a2enmod rewrite

# Copiar todo el contenido de la carpeta actual a la raíz de Apache
COPY . /var/www/html/


# Dar permisos al usuario de Apache (www-data) para que pueda leer todo
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponer el puerto 80
EXPOSE 80