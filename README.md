# Campo Aberto Tecnologia Rural

Plataforma brasileira open source para gestão agrícola e pecuária, construída com Laravel, PostgreSQL/PostGIS, Redis, Nginx e Docker Compose.

O objetivo do Campo Aberto é oferecer uma base tecnológica simples, auditável e extensível para gestão de propriedades rurais, operações agrícolas, pecuária, estoque, máquinas, financeiro, relatórios e indicadores operacionais.

---

## Licença

Este projeto é distribuído sob a licença **AGPL-3.0-or-later**.

Isso significa que qualquer uso, modificação ou distribuição deve respeitar os termos da GNU Affero General Public License v3.0 ou posterior.

---

## Stack principal

* Laravel 11+
* PHP 8.3+
* PostgreSQL 16+
* PostGIS
* Redis
* Nginx
* Docker Compose
* Blade
* PHPUnit
* GitHub Actions

---

## Módulos disponíveis

A plataforma inclui funcionalidades operacionais para:

* Autenticação, usuários, papéis, permissões e políticas de acesso
* Gestão multi-tenant com `tenant_id`
* Gestão de fazendas
* Talhões, pastagens e geometrias com PostGIS
* Endpoints GeoJSON
* Mapa básico com Leaflet/OpenStreetMap
* Cadastros rurais
* Culturas, variedades e safras
* Atividades agrícolas
* Plantio, colheita e produtividade
* Pecuária: animais, lotes, pesagens, sanidade, vacinação, reprodução, compra, venda e mortalidade
* Estoque e movimentações
* Máquinas e manutenções
* Financeiro operacional
* Relatórios operacionais com exportação CSV
* Dashboard operacional com indicadores reais
* API interna versionada em `/api/internal/v1`

---

## Requisitos para rodar localmente

Para testar a plataforma localmente, é necessário ter instalado:

* Docker
* Docker Compose v2
* Git

Opcionalmente:

* PHP 8.3+
* Composer
* Node.js/NPM, caso queira trabalhar fora do container

---

## Como clonar o projeto

```bash
git clone git@github.com:Klint-prog/campo-aberto.git
cd campo-aberto
```

Caso não use SSH no GitHub, clone por HTTPS:

```bash
git clone https://github.com/Klint-prog/campo-aberto.git
cd campo-aberto
```

---

## Configuração inicial

Copie o arquivo de ambiente de exemplo:

```bash
cp .env.example .env
```

Confira se as variáveis principais estão semelhantes a estas:

```env
APP_NAME="Campo Aberto"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8088

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=campo_aberto
DB_USERNAME=campo_aberto
DB_PASSWORD=campo_aberto

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Se o projeto estiver rodando via Docker Compose, o `DB_HOST` deve apontar para o nome do serviço PostgreSQL definido no `docker-compose.yml`, normalmente `postgres`.

---

## Subindo a aplicação com Docker

Execute:

```bash
docker compose up -d --build
```

Depois instale as dependências, gere a chave da aplicação, rode as migrations, seeders e limpe os caches:

```bash
docker compose exec php composer install

docker compose exec php php artisan key:generate

docker compose exec php php artisan migrate:fresh --seed

docker compose exec php php artisan optimize:clear
```

Acesse no navegador:

```text
http://localhost:8088/login
```

---

## Usuário padrão de teste

Após rodar os seeders, a plataforma deve disponibilizar um usuário administrativo de demonstração.

```text
E-mail: admin@campoaberto.local
Senha: password
```

Caso o login não funcione, rode novamente:

```bash
docker compose exec php php artisan migrate:fresh --seed
```

Atenção: `migrate:fresh --seed` apaga e recria todas as tabelas. Use apenas em ambiente local ou de teste.

---

## Comandos úteis

Ver containers ativos:

```bash
docker compose ps
```

Ver logs:

```bash
docker compose logs -f
```

Ver logs somente do PHP/Laravel:

```bash
docker compose logs -f php
```

Entrar no container PHP:

```bash
docker compose exec php bash
```

Limpar cache do Laravel:

```bash
docker compose exec php php artisan optimize:clear
```

Listar rotas:

```bash
docker compose exec php php artisan route:list
```

Rodar migrations:

```bash
docker compose exec php php artisan migrate
```

Recriar banco local com dados de demonstração:

```bash
docker compose exec php php artisan migrate:fresh --seed
```

Rodar testes:

```bash
docker compose exec php php artisan test
```

Derrubar containers:

```bash
docker compose down
```

Derrubar containers e apagar volumes locais do banco:

```bash
docker compose down -v
```

---

## Validação rápida da instalação

Depois de subir o projeto, valide:

```bash
docker compose ps

docker compose exec php php artisan migrate:status

docker compose exec php php artisan route:list

docker compose exec php php artisan test
```

A aplicação estará pronta para teste local quando:

* Os containers estiverem ativos
* As migrations estiverem aplicadas
* O login administrativo funcionar
* O dashboard carregar sem erro
* Os testes principais passarem

---

## Estrutura geral do projeto

```text
app/
  Http/
    Controllers/
    Middleware/
    Requests/
  Models/
  Policies/
  Services/

database/
  migrations/
  seeders/

resources/
  views/

routes/
  web.php
  api.php

tests/
  Feature/
  Unit/

docker-compose.yml
Dockerfile
README.md
```

---

## API interna

A API interna da plataforma é versionada em:

```text
/api/internal/v1
```

Novas rotas internas devem preservar o versionamento e não quebrar contratos existentes.

---

## Multi-tenant

As entidades de domínio da plataforma usam `tenant_id` desde a fundação do projeto.

Ao implementar novas tabelas, models, controllers ou relatórios, mantenha:

* Escopo por tenant
* Policies/Gates quando aplicável
* FormRequests para validação
* Restrições para evitar vazamento de dados entre tenants

---

## Banco de dados e PostGIS

O projeto utiliza PostgreSQL com PostGIS para recursos geográficos.

Funcionalidades relacionadas a fazendas, talhões, pastagens, geometrias e GeoJSON dependem da extensão PostGIS habilitada no banco.

No Docker, recomenda-se usar uma imagem compatível, como:

```text
postgis/postgis:16-3.4
```

---

## Testes

A suíte de testes usa PHPUnit.

Para executar:

```bash
docker compose exec php php artisan test
```

Os testes devem validar, no mínimo:

* Autenticação
* Acesso ao dashboard
* Escopo multi-tenant
* Rotas principais
* Relatórios operacionais
* Cálculos de indicadores
* Comportamento com base vazia ou dados incompletos

---

## Boas práticas de desenvolvimento

Antes de criar novas funcionalidades:

1. Verifique as migrations existentes.
2. Não recrie tabelas já existentes.
3. Use migrations incrementais.
4. Mantenha `tenant_id` em entidades de domínio.
5. Use FormRequests para validação.
6. Use Policies/Gates para autorização.
7. Mantenha a API interna versionada.
8. Escreva testes para regras importantes.
9. Não misture funcionalidades futuras sem planejamento.
10. Rode `php artisan test` antes de abrir pull request.

---

## Fluxo recomendado para contribuição

Crie uma branch a partir da `main`:

```bash
git checkout main
git pull origin main
git checkout -b minha-feature
```

Depois das alterações:

```bash
git status
git add .
git commit -m "Descrição objetiva da alteração"
git push origin minha-feature
```

Abra um Pull Request para `main`.

---

## Solução de problemas

### Erro: tabela `users` não existe

Rode:

```bash
docker compose exec php php artisan migrate:fresh --seed
```

### Erro de conexão com banco

Verifique se o container PostgreSQL está ativo:

```bash
docker compose ps
```

Verifique também se o `DB_HOST` no `.env` aponta para o nome correto do serviço no Docker Compose.

### Erro de cache/configuração

Rode:

```bash
docker compose exec php php artisan optimize:clear
```

### Erro de APP_KEY ausente

Rode:

```bash
docker compose exec php php artisan key:generate
```

### Quero resetar tudo localmente

```bash
docker compose down -v
docker compose up -d --build
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate:fresh --seed
docker compose exec php php artisan optimize:clear
```

---

## Segurança

Este projeto não deve ser usado em produção sem revisão adicional de segurança.

Antes de produção, revise:

* `APP_DEBUG=false`
* Senhas e secrets reais
* HTTPS
* Backups
* Logs
* Permissões de storage/cache
* Configuração de filas
* Política de CORS
* Rate limiting
* LGPD
* Monitoramento
* Hardening de containers

---

## Status do projeto

O Campo Aberto está em desenvolvimento ativo.

A versão local permite testar os principais fluxos operacionais da plataforma, incluindo autenticação, cadastros, operações rurais, relatórios e dashboard.

---

## Créditos

Jhonatas Mendes
Profissional de Infraestrutura e Desenvolvimento de Software - DevOps

Projeto Campo Aberto Tecnologia Rural.

Repositório:

```text
git@github.com:Klint-prog/campo-aberto.git
```
