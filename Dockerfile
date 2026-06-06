FROM php:8.1-apache

# Instala extensão do MySQL (PDO)
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# Copia todos os arquivos do projeto para dentro do container
COPY . /var/www/html/

# Permissões corretas
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80