# Friends PHP App - Dockerfile
#
# Compatible with Docker, Podman, and OpenShift (runs on port 8080 as non-root).
#
# Build:  docker build -t php-app .
# Run:    docker run -p 8080:8080 \
#           -e DBHOST=mysql -e MYSQL_USER=swapnil \
#           -e MYSQL_PASSWORD=redhat -e MYSQL_DATABASE=friends \
#           php-app

FROM php:8.2-apache

# Install the MySQLi extension (built-in helper provided by official PHP image)
RUN docker-php-ext-install mysqli

# Reconfigure Apache to listen on 8080 instead of 80
# This makes the image OpenShift-compatible (no privileged ports needed)
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/g' \
        /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY *.php friends.jpg /var/www/html/

# Remove the default Apache placeholder page
RUN rm -f /var/www/html/index.html

# Adjust permissions so OpenShift's arbitrary UID can write logs/run Apache
RUN chgrp -R root /var/log/apache2 /var/run/apache2 /var/lock/apache2 && \
    chmod -R g=u  /var/log/apache2 /var/run/apache2 /var/lock/apache2

EXPOSE 8080

# Optional env vars (override at runtime)
ENV APP_VERSION=1.0 \
    APP_COLOR=#3c6eb4

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -f http://localhost:8080/health.php || exit 1

CMD ["apache2-foreground"]
