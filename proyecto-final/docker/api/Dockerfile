FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Extensión de SQL Server para PHP (pdo_sqlsrv / sqlsrv) + driver ODBC 18.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        ca-certificates \
        curl \
        gnupg \
        libgssapi-krb5-2 \
        unixodbc-dev \
    && install -d /usr/share/keyrings \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
        | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list \
        > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && a2enmod rewrite headers \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' \
        /etc/apache2/apache2.conf \
        /etc/apache2/sites-available/000-default.conf \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

EXPOSE 80
