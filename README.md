# Avaliação Desenvolvedor Backend PHP

## Objetivo:
Avaliar a capacidade do desenvolvedor em construir e evoluir uma API REST robusta, utilizando
PHP (versão estável mais recente), Laravel, banco de dados PostgreSQL, integrações externas,
boas práticas de arquitetura, organização de código, segurança, Docker e documentação

## Requisitos técnicos:

- PHP 8.3
- Laravel 12
- Docker
- PostgreSQL 16.X
- Migrations (Laravel)
- JWT Auth (tymon/jwt-auth)
- Arquitetura em camadas
- Guzzle HTTP Client
- Tratamento de erros padronizado
- Documentação via L5-Swagger (OpenAPI)

## Estrutura disponibilizada

- Camada de segurança parcialmente pronta (Middlewares JWT e Role)
- DTOs da API externa (Integration/DTO)
- User (Controller, Service, Model)
- Migrations com tabelas users e shows
- Classe de paginação (PaginationHelper)
- Classe modelo para chamadas externas (AbstractRequest)

## Como executar

```bash
docker-compose up --build
```

A aplicação estará disponível em `http://localhost:9012`

## Swagger

Acesse `http://localhost:9012/api/documentation` após iniciar a aplicação.
