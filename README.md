# Campo Aberto Tecnologia Rural

Plataforma brasileira open source para gestão agrícola e pecuária com mapas, clima, BI e IA.

## Status

Fundação inicial do projeto conforme a fase **01 — Fundação Laravel, Docker, migrations e tenancy base**.

## Stack oficial

- **Framework:** Laravel 11+
- **Linguagem:** PHP 8.3+
- **Banco:** PostgreSQL 16+ com PostGIS
- **Cache/Fila:** Redis
- **Web server:** Nginx
- **Containers:** Docker Compose
- **Testes:** PHPUnit / Laravel Test Suite
- **CI/CD:** GitHub Actions

## Escopo desta fase

Incluído nesta fase:

- Laravel funcional.
- Docker Compose com Nginx, PHP-FPM, PostgreSQL/PostGIS, Redis, pgAdmin, worker, scheduler e backup.
- Migrations iniciais para tenancy base, usuários, papéis, permissões, fazendas mínimas e auditoria.
- Ativação das extensões PostgreSQL `postgis`, `pg_trgm` e `pgcrypto`.
- Seeder inicial com tenant, usuário admin, papel, permissão e fazenda de demonstração.
- Testes iniciais de aplicação, banco, extensões e seeders.
- GitHub Actions executando instalação, migrations, seeders, testes e Pint.

Fora do escopo desta fase:

- Autenticação completa.
- Fazendas avançadas.
- Mapas e geometrias operacionais.
- Agricultura, pecuária, financeiro, BI ou IA.

## Requisitos locais

- Docker
- Docker Compose
- Git

Composer local é opcional se você usar apenas os containers.

## Como subir o ambiente

```bash
git clone git@github.com:Klint-prog/campo-aberto.git
cd campo-aberto
cp .env.example .env
docker compose up -d --build
```

Depois instale dependências e gere a chave da aplicação:

```bash
docker compose exec php composer install
docker compose exec php php artisan key:generate
```

Execute migrations e seeders:

```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed
```

Execute os testes:

```bash
docker compose exec php php artisan test
```

Rodar Laravel Pint:

```bash
docker compose exec php ./vendor/bin/pint --test
```

## Serviços e portas

| Serviço | Porta local | Container |
|---|---:|---|
| Nginx / Laravel | 8088 | `nginx` |
| PostgreSQL/PostGIS | 54329 | `postgres` |
| Redis | 63799 | `redis` |
| pgAdmin | 8089 | `pgadmin` |
| Worker | interno | `worker` |
| Scheduler | interno | `scheduler` |
| Backup | interno | `backup` |

## Credenciais iniciais de desenvolvimento

```text
Admin: admin@campoaberto.local
Senha: CampoAberto@2026
```

A credencial acima é apenas para ambiente local e seed inicial. Não representa autenticação completa.

## Variáveis principais

Arquivo de referência: `.env.example`.

```env
APP_NAME="Campo Aberto"
APP_ENV=local
APP_URL=http://localhost:8088
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=campo_aberto
DB_USERNAME=campo_aberto
DB_PASSWORD=campo_aberto
REDIS_HOST=redis
```

## Regras geoespaciais reservadas

- SRID padrão web: **4326**.
- Para dados brasileiros, documentar e avaliar **SRID 4674 / SIRGAS 2000**.
- Geometrias operacionais e índices GIST serão criados apenas nas fases de mapas/geodados.

## CI/CD

O workflow está em `.github/workflows/ci.yml` e executa:

- `composer install`
- cópia de `.env.testing.example`
- `php artisan key:generate`
- `php artisan migrate --force`
- `php artisan db:seed --force`
- `php artisan test`
- Laravel Pint em modo check

## Licença

AGPL-3.0-or-later.
