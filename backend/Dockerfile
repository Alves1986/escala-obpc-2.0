# Use a imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instale dependências do sistema e extensões necessárias
RUN apt-get update && \
    apt-get install -y unzip libzip-dev && \
    docker-php-ext-install zip

# Instale o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie o código do projeto para o container
COPY . /var/www/html/

# Instale as dependências do Composer
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Dê permissão à pasta uploads (se necessário)
RUN mkdir -p /var/www/html/uploads && chmod -R 755 /var/www/html/uploads

# Exponha a porta padrão do Apache
EXPOSE 80 

# Ajusta a porta do Apache para a variável de ambiente $PORT (usada pelo Render)
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf 
RUN a2enmod rewrite 