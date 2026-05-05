# Política de segurança

## Versões suportadas

Enquanto o projeto estiver em desenvolvimento inicial, a branch `main` representa a linha ativa de correções.

## Como reportar vulnerabilidades

Não abra issue pública com detalhes exploráveis de segurança.

Abra um relatório privado pelo recurso de segurança do GitHub, quando disponível, ou entre em contato com o mantenedor do repositório.

Inclua, quando possível:

- Descrição objetiva da falha.
- Passos de reprodução.
- Impacto estimado.
- Ambiente usado.
- Evidências sem expor dados sensíveis.

## Escopo de segurança

Áreas consideradas sensíveis:

- Autenticação e sessão.
- Autorização, papéis, permissões, policies e gates.
- Isolamento por `tenant_id`.
- Dados pessoais e LGPD.
- Uploads e anexos.
- API interna e futura API pública.
- Integrações com ERPs, WhatsApp, IoT, RFID e balanças.
- Segredos de ambiente e credenciais.

## Expectativa de correção

Falhas críticas devem ser priorizadas antes de novas features.

## Boas práticas para contribuidores

- Nunca commitar `.env` real, tokens, senhas, dumps ou chaves privadas.
- Não usar dados pessoais reais em seeds, testes ou demo pública.
- Adicionar testes para correções de segurança.
- Validar isolamento multi-tenant em toda query de domínio.
