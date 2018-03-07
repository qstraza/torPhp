FROM php:7.1-alpine

LABEL maintainer="rok@rojal.si"

ADD ./docroot /app
WORKDIR /app
RUN apk --update add zlib-dev && rm /var/cache/apk/*
RUN docker-php-ext-install zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
RUN composer install --no-dev --no-interaction -o
RUN rm -f /usr/bin/composer
VOLUME ["/firefox-profiles", "/google-jsons"]

CMD ["php", "-a"]
