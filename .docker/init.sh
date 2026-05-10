#!/usr/bin/env sh
set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
ENV_FILE="${ROOT_DIR}/.env"
ENV_EXAMPLE_FILE="${ROOT_DIR}/.env.example"

log() {
    printf '[init] %s\n' "$1"
}

ensure_env_file() {
    if [ -f "${ENV_FILE}" ]; then
        return
    fi

    if [ -f "${ENV_EXAMPLE_FILE}" ]; then
        cp "${ENV_EXAMPLE_FILE}" "${ENV_FILE}"
        log ".env criado a partir de .env.example"
        return
    fi

    touch "${ENV_FILE}"
    log ".env criado vazio"
}

upsert_env_key() {
    KEY="$1"
    VALUE="$2"
    TMP_FILE="$(mktemp)"

    awk -v key="${KEY}" -v value="${VALUE}" '
        BEGIN {
            pattern = "^[[:space:]]*#?[[:space:]]*" key "="
            found = 0
        }
        $0 ~ pattern {
            print key "=" value
            found = 1
            next
        }
        { print }
        END {
            if (!found) {
                print key "=" value
            }
        }
    ' "${ENV_FILE}" > "${TMP_FILE}"

    mv "${TMP_FILE}" "${ENV_FILE}"
}

HOST_UID="$(id -u)"
HOST_GID="$(id -g)"

ensure_env_file
upsert_env_key "DOCKER_UID" "${HOST_UID}"
upsert_env_key "DOCKER_GID" "${HOST_GID}"

log "Subindo containers com UID=${HOST_UID} GID=${HOST_GID}"
cd "${ROOT_DIR}"
exec docker compose up -d --build "$@"
