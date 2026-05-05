# Campo Aberto Tecnologia Rural

Plataforma brasileira open source para gestão agrícola e pecuária com mapas, clima, BI e IA.

## Status

Fundação inicial do projeto conforme a fase **00.5 — Framework, tooling, testes e CI/CD**.

## Stack oficial

- **Framework:** Laravel 11+
- **Linguagem:** PHP 8.3+
- **Banco:** PostgreSQL 16+ com PostGIS
- **Cache/Fila:** Redis
- **Web server:** Nginx
- **Containers:** Docker Compose
- **Frontend inicial:** Blade + Bootstrap 5/Tailwind CSS em fase posterior
- **Testes:** PHPUnit / Laravel Test Suite
- **CI/CD:** GitHub Actions

## Arquitetura inicial

Esta base prepara o projeto para desenvolvimento incremental por fases:

1. Laravel como framework principal.
2. Docker Compose com app PHP-FPM, Nginx, PostgreSQL/PostGIS e Redis.
3. Migrations Laravel como padrão obrigatório para alterações estruturais de banco.
4. Testes desde o início.
5. GitHub Actions em push e pull request.
6. Preparação conceitual para `tenant_id`, `farm_id` e `audit_logs` nas fases seguintes.

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

Depois gere a chave da aplicação:

```bash
docker compose exec app php artisan key:generate
```

Execute as migrations:

```bash
docker compose exec app php artisan migrate
```

Execute os testes:

```bash
docker compose exec app php artisan test
```

Rodar Laravel Pint:

```bash
docker compose exec app ./vendor/bin/pint
```

## Serviços e portas

| Serviço | Porta local | Container |
|---|---:|---|
| Nginx / Laravel | 8088 | `web` |
| PostgreSQL/PostGIS | 54329 | `db` |
| Redis | 63799 | `redis` |

## Variáveis principais

Arquivo de referência: `.env.example`.

```env
APP_NAME="Campo Aberto"
APP_ENV=local
APP_URL=http://localhost:8088
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=campo_aberto
DB_USERNAME=campo_aberto
DB_PASSWORD=campo_aberto
REDIS_HOST=redis
```

## CI/CD

O workflow está em `.github/workflows/ci.yml` e executa:

- `composer install`
- cópia de `.env.testing.example`
- `php artisan key:generate`
- `php artisan migrate --force`
- `php artisan test`
- Laravel Pint em modo check

## Regras globais do projeto

- Uma fase por janela de contexto.
- Não implementar fase posterior antes da base estar estável.
- Toda alteração estrutural de banco deve ser migration Laravel.
- Toda tabela operacional deve avaliar `tenant_id` e `farm_id`.
- Toda operação crítica deve gerar `audit_log`.
- Todo módulo deve ter testes mínimos.
- Toda consulta deve respeitar escopo por tenant e fazenda.
- Evitar dependência obrigatória de APIs pagas no MVP.

## Licença

AGPL-3.0-or-later.
