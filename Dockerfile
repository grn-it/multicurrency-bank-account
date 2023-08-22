FROM php:8.2-alpine3.16 AS app
RUN apk add bash git make jq
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV PATH $PATH:/root/.composer/vendor/bin
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
CMD ["tail", "-f", "/dev/null"]