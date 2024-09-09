FROM php:8.3-cli-alpine

RUN adduser app -shell /bin/sh --disabled-password --uid 1000

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

CMD [ "php", "-a" ]
