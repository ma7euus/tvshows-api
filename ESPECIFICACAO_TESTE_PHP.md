# Especificação do Teste Técnico (Ao Vivo) - PHP/Laravel

## Objetivo

Avaliar a capacidade do desenvolvedor em construir e evoluir uma API REST robusta, utilizando
PHP (versão estável mais recente), Laravel, banco de dados PostgreSQL, integrações externas,
boas práticas de arquitetura, organização de código, segurança, Docker e documentação

## Escopo Funcional mínimo

### 1. Sincronização de TV (Shows)

**POST /api/shows**
- Consumir a API TVMaze
- Persistir Show + Episodes
- Evitar duplicados
- Apenas ADMIN pode executar

API externa:
`https://api.tvmaze.com/singlesearch/shows?q=NOME&embed=episodes`

### 2. Listagem de Shows

**GET /api/shows**
- Paginação
- Filtro por nome
- Ordenação (opcional)

### 3. Nota média por temporada

**GET /api/episodes/average**
- Agrupar episódios por temporada
- Calcular média de rating
- Ignorar ratings nulos
- Se todos forem null → retornar 0
- Se não houver episódios → erro

---

## Requisitos técnicos

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

## Requisitos autenticação e permissões

- Existem erros inseridos no RoleMiddleware e nas rotas `/api/users`
- Usuário admin com nível ADMIN existente no banco
- Usuário ADMIN acessa tudo
- Criar um usuário nível USER, sua restrição é não poder sincronizar novos seriados, endpoint: POST /api/shows

## Consumo API externa

Documentação da API a ser consumida: https://www.tvmaze.com/api

Recurso que retorna o TV Show & Episódios:
`https://api.tvmaze.com/singlesearch/shows?q=NOME_SERIE&embed=episodes`

## Banco e Migração

Criar via Migration a tabela `episodes`:

- id (PK, UUID)
- id_integration
- show_id (FK → shows)
- name, season, number
- type, airdate, airtime, airstamp
- runtime, rating, summary
- created_at, updated_at

## Estrutura disponibilizada

- Camada de segurança parcialmente pronta (Middlewares JWT e Role)
- DTOs da API externa (Integration/DTO)
- User (Controller, Service, Model)
- Migrations com tabelas users e shows
- Classe de paginação (PaginationHelper)
- Classe modelo para chamadas externas (AbstractRequest)

## Tarefas a implementar

- Migration para episodes
- Model Episode
- Service e Controller para Show e Episode
- Segurança ajustada (corrigir bugs nos middlewares e rotas)
- Sincronização com API externa (implementar AbstractRequest)
- Paginação + filtro
- Cálculo de média
- Documentação mínima (Swagger)

## Teste Automatizado

Criar ao menos 1 teste (unitário ou integração)

## Entregáveis (fim da sessão)

- Aplicação funcionando via Docker
- Endpoints funcionais
- Segurança ajustada
- Teste automatizado
- Documentação acessível no Swagger
- README simples para execução
