ARG ALPINE_TAG="3.8"
ARG PHP_TAG="7.2-cli-alpine3.8"

FROM php:$PHP_TAG as ext-builder
RUN docker-php-source extract && \
    apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS

FROM ext-builder as ext-pdo-mysql
RUN docker-php-ext-install pdo_mysql

FROM ext-builder as ext-swoole
ARG SWOOLE_VERSION=4.2.3
RUN pecl install swoole-$SWOOLE_VERSION && \
    docker-php-ext-enable swoole

FROM ext-builder as ext-oci8
#RUN echo "http://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories && \
#    apk add --no-cache libaio libnsl wget && \
#    ln -s /usr/lib/libnsl.so.2 /usr/lib/libnsl.so.1
RUN apk add --no-cache wget

WORKDIR /opt/oracle/lib
ARG INSTANT_CLIENT_VERSION="12.1.0.1.0"
RUN wget https://github.com/bumpx/oracle-instantclient/raw/master/instantclient-basic-linux.x64-${INSTANT_CLIENT_VERSION}.zip && \
    wget https://github.com/bumpx/oracle-instantclient/raw/master/instantclient-sdk-linux.x64-${INSTANT_CLIENT_VERSION}.zip

RUN mkdir -p instantclient && \
    LIBS="*/libociei.so */libons.so */libnnz12.so */libclntshcore.so.12.1 */libclntsh.so.12.1" && \
    unzip instantclient-basic-linux.x64-${INSTANT_CLIENT_VERSION}.zip ${LIBS} && \
    for lib in ${LIBS}; do mv ${lib} /opt/oracle/lib/instantclient; done && \
    ln -s /opt/oracle/lib/instantclient/libclntsh.so.12.1 /opt/oracle/lib/instantclient/libclntsh.so && \
    rm instantclient-basic-linux.x64-${INSTANT_CLIENT_VERSION}.zip

RUN mkdir -p instantclient/sdk/include && \
    HEADERS="*.h" && \
    unzip instantclient-sdk-linux.x64-${INSTANT_CLIENT_VERSION}.zip ${HEADERS} && \
    find ./ -type f -name ${HEADERS} -exec mv {} /opt/oracle/lib/instantclient/sdk/include \;

RUN docker-php-ext-configure oci8 --with-oci8=instantclient,/opt/oracle/lib/instantclient && \
    docker-php-ext-install oci8

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
RUN apk add --no-cache libaio libnsl libstdc++ && \
    ln -s /usr/lib/libnsl.so.2 /usr/lib/libnsl.so.1
COPY --from=ext-swoole /usr/local/lib/php/extensions/no-debug-non-zts-20170718/swoole.so /usr/local/lib/php/extensions/no-debug-non-zts-20170718/swoole.so
COPY --from=ext-swoole /usr/local/etc/php/conf.d/docker-php-ext-swoole.ini /usr/local/etc/php/conf.d/docker-php-ext-swoole.ini
COPY --from=ext-pdo-mysql /usr/local/lib/php/extensions/no-debug-non-zts-20170718/pdo_mysql.so /usr/local/lib/php/extensions/no-debug-non-zts-20170718/pdo_mysql.so
COPY --from=ext-pdo-mysql /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini
COPY --from=ext-oci8 /usr/local/lib/php/extensions/no-debug-non-zts-20170718/oci8.so /usr/local/lib/php/extensions/no-debug-non-zts-20170718/oci8.so
COPY --from=ext-oci8 /usr/local/etc/php/conf.d/docker-php-ext-oci8.ini /usr/local/etc/php/conf.d/docker-php-ext-oci8.ini
COPY --from=ext-oci8 /opt/oracle/lib/instantclient /opt/oracle/lib/instantclient
COPY --from=app-installer /usr/src/app ./
RUN mv .env.docker .env && \
    bin/console cache:clear

FROM base as SymfonyConsole
ENTRYPOINT ["bin/console"]
CMD ["list"]

FROM base as Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=app-installer /usr/bin/composer /usr/local/bin/composer
ENTRYPOINT ["composer"]
CMD ["list"]
