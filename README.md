## Como executar

```bash
./docker/init.sh
```

A aplicação estará disponível em `http://localhost:9012`

Na primeira subida o container da aplicação faz automaticamente:

- criação do `.env` a partir do `.env.example`, se necessário;
- ajuste automático de `DOCKER_UID` e `DOCKER_GID` no `.env`;
- `composer install`;
- geração de `APP_KEY`;
- geração de `JWT_SECRET`;
- espera do PostgreSQL;
- execução de `php artisan migrate --force`;
- geração do Swagger.

## Fluxo mínimo:

```bash
./docker/init.sh
docker compose logs -f app
```

Quando o healthcheck do serviço `app` estiver `healthy`, o ambiente está pronto.
Depois da primeira inicialização, `docker compose up -d` volta a funcionar normalmente com o `.env` já preparado.

## Comandos úteis

```bash
docker compose exec app php artisan test
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app composer install
docker compose down
docker compose down -v
```

Se você precisar forçar outro UID/GID, ajuste `DOCKER_UID` e `DOCKER_GID` no `.env`.

## Usuários padrão

- `admin` / `admin` (`ADMIN`)
- `user` / `user` (`USER`)

## Testes

```bash
docker compose exec app php artisan test
```

## Swagger

Acesse `http://localhost:9012/api/documentation` após iniciar a aplicação.
