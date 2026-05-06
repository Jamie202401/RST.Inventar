# ============================================================
# Dockerfile – RST-Inventar Webserver
# Basis:    PHP 8.2 mit Apache
# Projekt:  Inventarverwaltungssystem RST-Veolia GmbH & Co. KG
# ============================================================

# ── Basis-Image ──────────────────────────────────────────────
# von Docker Hub: https://hub.docker.com/_/php
FROM php:8.2-apache

# ── System-Pakete aktualisieren ──────────────────────────────

RUN apt-get update && apt-get install -y \
    --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*
# rm -rf /var/lib/apt/lists/* löscht den apt-Cache
# → reduziert die Image-Größe deutlich

# ── PHP-Erweiterungen installieren mit PDO Statement───────────────────────────

RUN docker-php-ext-install pdo pdo_mysql

# ── Apache Konfiguration ─────────────────────────────────────
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod ssl rewrite headers

# ── PHP Konfiguration ─────────────────────────────────────────
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Eigene PHP-Einstellungen überschreiben

COPY app/config/php-custom.ini "$PHP_INI_DIR/conf.d/php-custom.ini"

# ── Arbeitsverzeichnis setzen ────────────────────────────────

WORKDIR /var/www/html

# ── Dateiberechtigungen ───────────────────────────────────────

RUN chown -R www-data:www-data /var/www/html

# ── Port freigeben ────────────────────────────────────────────

EXPOSE 80 443

# ── Start-Befehl ─────────────────────────────────────────────

CMD ["apache2-foreground"]