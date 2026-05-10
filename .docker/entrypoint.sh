#!/usr/bin/env sh
set -eu

APP_DIR="/app"
COMPOSER_HASH_FILE="storage/framework/cache/.composer.lock.hash"

log() {
    printf '[entrypoint] %s\n' "$1"
}

ensure_env_file() {
    if [ -f "${APP_DIR}/.env" ]; then
        return
    fi

    if [ -f "${APP_DIR}/.env.example" ]; then
        cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
        log ".env criado a partir de .env.example"
        return
    fi

    touch "${APP_DIR}/.env"
    log ".env criado vazio"
}

load_env_file() {
    set -a
    # shellcheck disable=SC1091
    . "${APP_DIR}/.env"
    set +a
}

ensure_runtime_directories() {
    mkdir -p \
        "${APP_DIR}/bootstrap/cache" \
        "${APP_DIR}/storage/api-docs" \
        "${APP_DIR}/storage/framework/cache/data" \
        "${APP_DIR}/storage/framework/sessions" \
        "${APP_DIR}/storage/framework/views" \
        "${APP_DIR}/storage/logs"
}

composer_lock_hash() {
    if [ ! -f "${APP_DIR}/composer.lock" ]; then
        return
    fi

    sha1sum "${APP_DIR}/composer.lock" | awk '{print $1}'
}

install_composer_dependencies() {
    if [ "${RUN_COMPOSER_INSTALL:-true}" != "true" ]; then
        return
    fi

    CURRENT_HASH="$(composer_lock_hash || true)"
    STORED_HASH=""

    if [ -f "${APP_DIR}/${COMPOSER_HASH_FILE}" ]; then
        STORED_HASH="$(cat "${APP_DIR}/${COMPOSER_HASH_FILE}")"
    fi

    if [ ! -f "${APP_DIR}/vendor/autoload.php" ] || [ "${CURRENT_HASH}" != "${STORED_HASH}" ]; then
        log "Instalando dependências do Composer"
        composer install --prefer-dist --no-interaction

        if [ -n "${CURRENT_HASH}" ]; then
            printf '%s' "${CURRENT_HASH}" > "${APP_DIR}/${COMPOSER_HASH_FILE}"
        fi
    fi
}

ensure_app_key() {
    if grep -Eq '^APP_KEY=.+' "${APP_DIR}/.env"; then
        return
    fi

    log "Gerando APP_KEY"
    php artisan key:generate --force --ansi
}

ensure_jwt_secret() {
    if grep -Eq '^JWT_SECRET=.+' "${APP_DIR}/.env"; then
        return
    fi

    log "Gerando JWT_SECRET"
    php artisan jwt:secret --force --ansi
}

wait_for_database() {
    if [ "${RUN_MIGRATIONS:-true}" != "true" ]; then
        return
    fi

    if [ "${DB_CONNECTION:-pgsql}" != "pgsql" ]; then
        return
    fi

    RETRIES="${DB_WAIT_RETRIES:-30}"
    log "Aguardando PostgreSQL em ${DB_HOST:-postgres}:${DB_PORT:-5432}"

    until php -r '
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST") ?: "postgres",
            getenv("DB_PORT") ?: "5432",
            getenv("DB_DATABASE") ?: "tvshow"
        );

        try {
            new PDO($dsn, getenv("DB_USERNAME") ?: "postgres", getenv("DB_PASSWORD") ?: "postgres");
            exit(0);
        } catch (\Throwable $exception) {
            fwrite(STDERR, $exception->getMessage() . PHP_EOL);
            exit(1);
        }
    '; do
        RETRIES=$((RETRIES - 1))

        if [ "${RETRIES}" -le 0 ]; then
            log "Banco indisponível após múltiplas tentativas"
            exit 1
        fi

        sleep 2
    done
}

run_migrations() {
    if [ "${RUN_MIGRATIONS:-true}" != "true" ]; then
        return
    fi

    log "Executando migrations"
    php artisan migrate --force --ansi
}

generate_swagger_docs() {
    if [ "${GENERATE_SWAGGER:-true}" != "true" ]; then
        return
    fi

    log "Gerando documentação Swagger"
    php artisan l5-swagger:generate --ansi
}

bootstrap_application() {
    ensure_env_file
    load_env_file
    ensure_runtime_directories
    install_composer_dependencies
    ensure_app_key
    ensure_jwt_secret
    load_env_file
    wait_for_database
    run_migrations
    generate_swagger_docs
}

cd "${APP_DIR}"
bootstrap_application

exec "$@"
