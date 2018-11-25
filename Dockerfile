ARG ALPINE_TAG="3.8"
ARG PHP_TAG="7.2-cli-alpine3.8"

FROM php:$PHP_TAG as ext-builder
RUN docker-php-source extract && \
    apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS

FROM ext-builder as ext-pdomysql
RUN docker-php-ext-install pdo_mysql

FROM ext-builder as ext-swoole
ARG SWOOLE_VERSION=4.2.3
RUN pecl install swoole-$SWOOLE_VERSION && \
    docker-php-ext-enable swoole

FROM composer:latest as app-installer
WORKDIR /usr/src/app
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative --ansi
COPY composer.json composer.lock ./
RUN composer validate
ARG COMPOSER_ARGS=install
RUN composer "$COMPOSER_ARGS" --prefer-dist --ignore-platform-reqs --no-progress --no-suggest --no-scripts --no-autoloader --ansi
COPY . ./
RUN composer dump-autoload --classmap-authoritative --ansi

FROM php:$PHP_TAG as base
WORKDIR /usr/src/app
RUN apk add --no-cache libstdc++
COPY --from=ext-swoole /usr/local/lib/php/extensions/no-debug-non-zts-20170718/swoole.so /usr/local/lib/php/extensions/no-debug-non-zts-20170718/swoole.so
COPY --from=ext-swoole /usr/local/etc/php/conf.d/docker-php-ext-swoole.ini /usr/local/etc/php/conf.d/docker-php-ext-swoole.ini
COPY --from=ext-pdomysql /usr/local/lib/php/extensions/no-debug-non-zts-20170718/pdo_mysql.so /usr/local/lib/php/extensions/no-debug-non-zts-20170718/pdo_mysql.so
COPY --from=ext-pdomysql /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini
COPY --from=app-installer /usr/src/app ./

FROM base as SymfonyConsole
ENTRYPOINT ["bin/console"]
CMD ["list"]

FROM base as Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=app-installer /usr/bin/composer /usr/local/bin/composer
ENTRYPOINT ["composer"]
CMD ["list"]
