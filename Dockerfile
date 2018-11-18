ARG PHP_TAG="7.2-cli-alpine3.8"
FROM php:$PHP_TAG as base
RUN docker-php-ext-install pdo_mysql
WORKDIR /usr/src/app
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative --ansi
COPY composer.json composer.lock symfony.lock ./
RUN composer install --ansi --ignore-platform-reqs --no-scripts
COPY . ./
RUN mv .env.docker .env

FROM base as SymfonyConsole
RUN bin/console cache:clear
ENTRYPOINT ["php", "-d", "memory_limit=-1", "./bin/console"]
CMD ["list"]

FROM base as Composer
ENTRYPOINT ["php", "-d", "memory_limit=-1", "/usr/local/bin/composer"]
