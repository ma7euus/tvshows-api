FROM php:8.3-cli

ARG DOCKER_UID=1000
ARG DOCKER_GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_CACHE_DIR=/tmp/composer/cache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        libpq-dev \
        postgresql-client \
        unzip \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd --gid "${DOCKER_GID}" app \
    && useradd --uid "${DOCKER_UID}" --gid app --create-home --shell /bin/bash app

WORKDIR /app

COPY --chown=app:app ../.. /app
COPY --chown=app:app .docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p \
        /app/bootstrap/cache \
        /app/storage/api-docs \
        /app/storage/framework/cache/data \
        /app/storage/framework/sessions \
        /app/storage/framework/views \
        /app/storage/logs \
        /tmp/composer/cache \
    && chown -R app:app /app /tmp/composer

USER app

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
