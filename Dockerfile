FROM php:7.2.2-cli
LABEL maintainer="raymondelooff"

COPY .docker/conf/php.ini /usr/local/etc/php/

RUN apt-get update && \
  DEBIAN_FRONTEND=noninteractive apt-get install -y \
    git \
    zip \
  && rm -r /var/lib/apt/lists/*

RUN pecl install -o -f redis && \
    rm -rf /tmp/pear && \
    docker-php-ext-enable redis

ENV COMPOSER_HOME /composer
ENV PATH /composer/vendor/bin:$PATH
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json src/ /usr/src/benchmark/

WORKDIR /usr/src/benchmark/

RUN composer global require phpbench/phpbench && \
    composer install && \
    composer clear-cache

CMD ["phpbench", "run", "src/"]
