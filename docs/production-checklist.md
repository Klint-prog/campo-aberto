# Campo Aberto - Checklist de produção, LGPD, observabilidade e recuperação

Fase 09: Segurança, LGPD, observabilidade, backup e produção.

## 1. Checklist obrigatório antes de produção

- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] `APP_KEY` gerado e armazenado como segredo.
- [ ] HTTPS obrigatório no proxy/Nginx e redirecionamento HTTP -> HTTPS.
- [ ] HSTS ativo após validação do domínio.
- [ ] Banco PostgreSQL acessível apenas pela rede interna.
- [ ] Redis acessível apenas pela rede interna.
- [ ] pgAdmin desativado ou restrito por VPN/IP allowlist/autenticação forte.
- [ ] Usuário do banco sem privilégios administrativos desnecessários.
- [ ] Backups criptografados ou armazenados em volume/repositório protegido.
- [ ] Retenção mínima de 30 dias configurada.
- [ ] Restore testado em ambiente isolado.
- [ ] Logs centralizados ou coletáveis por volume/agente.
- [ ] Workers e scheduler com logs persistentes.
- [ ] Healthcheck monitorado por orquestrador ou uptime monitor.
- [ ] Rate limit habilitado nas rotas públicas e internas sensíveis.
- [ ] Policies/Gates revisados para tenant_id e farm_id.
- [ ] Segredos nunca commitados no repositório.

## 2. Ajustes de segurança

### Laravel

- `APP_DEBUG=false` em produção.
- Usar `Hash::make()` para senhas e nunca gravar senha em texto puro.
- CSRF obrigatório em rotas web.
- Form Requests para validação de entrada.
- Policies/Gates para autorização por papel, tenant e fazenda.
- Rate limit por rota e por usuário/IP.
- Logs de auditoria para alterações críticas.

### Headers HTTP

A fase 09 define headers em `config/phase09.php` e middleware `App\\Http\\Middleware\\SecurityHeaders`:

- `Strict-Transport-Security`.
- `X-Frame-Options`.
- `X-Content-Type-Options`.
- `Referrer-Policy`.
- `Permissions-Policy`.
- `Cross-Origin-Opener-Policy`.
- `Cross-Origin-Resource-Policy`.

Registrar o middleware globalmente no bootstrap/app.php ou no grupo web/api conforme o bootstrap atual do projeto.

## 3. Fluxo LGPD mínimo

### Política de privacidade e termos

- Informar quais dados são coletados.
- Informar finalidade: autenticação, autorização, gestão rural, auditoria e operação.
- Informar base legal: execução de contrato, legítimo interesse, obrigação legal e consentimento quando aplicável.
- Informar prazo de retenção.
- Informar canal de atendimento ao titular.

### Exportação de dados

Fluxo mínimo:

1. Usuário solicita exportação.
2. Administrador valida identidade e autorização.
3. Sistema gera pacote com dados pessoais mínimos relacionados ao usuário, tenant e fazenda.
4. Operação registra audit log `user.data_exported`.
5. Pacote é entregue por canal seguro.

### Exclusão ou anonimização

Fluxo mínimo:

1. Usuário solicita exclusão.
2. Administrador avalia obrigações legais, fiscais e operacionais.
3. Se exclusão for possível, remover dados pessoais não essenciais.
4. Se retenção for obrigatória, anonimizar nome/e-mail quando aplicável e preservar trilha operacional necessária.
5. Operação registra audit log `user.deleted_or_anonymized`.

### Minimização

- Não coletar documentos pessoais quando nome/e-mail/papel bastam.
- Não armazenar tokens externos em texto puro.
- Não exportar dados de outros tenants/fazendas.

## 4. Backup e restore

### Backup diário PostgreSQL

```bash
chmod +x scripts/backup-postgres.sh
BACKUP_DIR=/backups/postgres BACKUP_RETENTION_DAYS=30 scripts/backup-postgres.sh
```

O script gera `.dump` em formato custom do PostgreSQL e `.sha256` para verificação de integridade.

### Backup de uploads

```bash
chmod +x scripts/backup-uploads.sh
UPLOADS_DIR=storage/app/public BACKUP_UPLOADS_DIR=/backups/uploads scripts/backup-uploads.sh
```

### Restore PostgreSQL

Executar apenas em ambiente isolado ou durante janela de manutenção:

```bash
chmod +x scripts/restore-postgres.sh
RESTORE_CONFIRM=I_UNDERSTAND_THIS_REPLACES_DATA scripts/restore-postgres.sh /backups/postgres/campo_aberto-YYYYMMDDTHHMMSSZ.dump
```

### Validação periódica

- Testar restore no mínimo mensalmente.
- Registrar data, operador, arquivo usado e resultado.
- Alertar se backup diário não existir ou falhar.

## 5. Observabilidade básica

### Logs mínimos

- Laravel: `storage/logs/laravel.log`.
- Nginx: access/error logs do container/proxy.
- PHP-FPM: stdout/stderr e slow log quando configurado.
- PostgreSQL: logs de erro, conexões e queries lentas.
- Worker: logs do processo de fila.
- Scheduler: logs de execução do cron/scheduler.

### Métricas mínimas

- CPU.
- RAM.
- Disco.
- Tempo de resposta.
- Status do healthcheck.
- Idade do último backup.

### Healthcheck

Endpoint sugerido: `/api/internal/v1/health`.

Resposta esperada:

```json
{
  "status": "ok",
  "checks": {
    "app": true,
    "database": true,
    "cache": true,
    "storage": true
  }
}
```

## 6. Critérios de aceite da fase 09

- [ ] Erros em produção não expõem stack trace com `APP_DEBUG=false`.
- [ ] Usuário não acessa tenant/fazenda não autorizados.
- [ ] Backup PostgreSQL gera arquivo `.dump` e `.sha256`.
- [ ] Restore funciona em ambiente de teste.
- [ ] Healthcheck retorna 200 quando dependências estão OK.
- [ ] Logs principais estão documentados e coletáveis.
- [ ] Checklist de produção revisado antes do deploy.
