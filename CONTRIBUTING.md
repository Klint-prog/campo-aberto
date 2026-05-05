# Guia de contribuição

Obrigado por considerar contribuir com o Campo Aberto Tecnologia Rural.

## Stack oficial

- Laravel 11+
- PHP 8.3+
- PostgreSQL 16+ com PostGIS
- Redis
- Nginx
- Docker Compose
- PHPUnit
- GitHub Actions

## Regras globais

- Use migrations Laravel para toda alteração de banco.
- Preserve `tenant_id` desde o início em entidades de domínio.
- Não implemente fases futuras sem issue aprovada.
- Mantenha API interna versionada em `/api/internal/v1`.
- Escreva testes PHPUnit para regras de domínio, escopo por tenant e endpoints críticos.
- Evite acoplamento entre módulos agrícolas, pecuários, financeiros, BI, IA e integrações.

## Fluxo de contribuição

1. Abra ou escolha uma issue.
2. Confirme o escopo da fase.
3. Crie uma branch curta e descritiva.
4. Implemente com commits pequenos.
5. Rode testes localmente.
6. Abra pull request preenchendo o template.

## Padrão de branches

Use nomes como:

- `docs/roadmap-futuro`
- `feat/agricultura-atividades`
- `fix/tenant-scope-talhao`
- `test/pecuaria-eventos`
- `chore/github-actions`

## Padrão de commits

Preferir Conventional Commits:

- `feat: adiciona endpoint geojson de talhoes`
- `fix: corrige escopo por tenant em fazendas`
- `docs: adiciona roadmap público`
- `test: cobre eventos de vacinação animal`
- `chore: ajusta pipeline ci`

## Critérios mínimos para pull request

- Escopo aderente à issue.
- Migrations incrementais, sem recriar tabelas existentes.
- Testes adicionados ou justificativa clara quando não aplicável.
- Documentação atualizada quando houver mudança de contrato ou operação.
- Nenhum segredo, senha ou dado real commitado.
- Compatível com licença AGPL-3.0-or-later.

## Áreas futuras

PWA, app mobile, INPE, NDVI, IoT, RFID, balanças, API pública, SaaS multi-tenant, demo pública e marketplace devem seguir o `ROADMAP.md` e não devem ser implementados antes de aprovação explícita.
