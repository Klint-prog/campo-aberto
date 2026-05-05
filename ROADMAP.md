# Roadmap público — Campo Aberto Tecnologia Rural

O Campo Aberto é uma plataforma brasileira open source para gestão agrícola e pecuária com mapas, clima, BI e IA, licenciada sob AGPL-3.0-or-later.

Este roadmap organiza a evolução futura pós-MVP. Ele não autoriza implementação antecipada de fases futuras; cada item deve virar issue, discussão técnica e pull request separado.

## Princípios

- Manter o núcleo do MVP simples, testável e operável.
- Não poluir o escopo inicial com integrações complexas antes da base estar estável.
- Preservar `tenant_id` desde o início para permitir evolução SaaS.
- Priorizar dados abertos brasileiros, interoperabilidade e soberania tecnológica.
- Exigir testes, migrations incrementais, documentação e revisão para cada módulo.
- Respeitar LGPD, segurança, observabilidade e licença AGPL-3.0-or-later.

## Marcos futuros propostos

### M10.1 — Experiência offline e PWA

Objetivo: transformar a interface web em experiência instalável, responsiva e tolerante a conexão instável.

Escopo futuro:

- Manifest PWA.
- Service worker.
- Estratégia de cache offline-first para telas críticas.
- Sincronização posterior de operações de campo.
- Indicadores visuais de conectividade.
- Testes de degradação offline.

Fora do escopo neste momento:

- Aplicativo nativo.
- Sincronização complexa multi-dispositivo.
- Push notification em produção.

### M10.2 — Aplicativo mobile nativo

Objetivo: planejar app Android/iOS para operação rural em campo.

Escopo futuro:

- Definir stack mobile.
- Login integrado com API interna/pública.
- Captura offline de atividades, pesagens, vacinação e fotos.
- Geolocalização de campo.
- Sincronização segura.

Critério de entrada:

- API estável.
- Contratos documentados.
- Estratégia de autenticação consolidada.

### M10.3 — INPE, satélite e NDVI

Objetivo: integrar dados públicos nacionais e imagens de satélite para inteligência agrícola.

Escopo futuro:

- Pesquisa técnica de fontes do INPE.
- Pipeline de ingestão de metadados.
- Camadas raster/vetoriais.
- Cálculo ou consumo de NDVI.
- Histórico por talhão/fazenda.
- Alertas de anomalia vegetativa.

Riscos:

- Volume de dados geoespaciais.
- Custo computacional.
- Licenças e termos de uso das fontes.
- Precisão agronômica e validação em campo.

### M10.4 — Camadas de produtividade e modelos preditivos

Objetivo: evoluir relatórios e BI para análises agronômicas preditivas.

Escopo futuro:

- Mapas de produtividade.
- Correlação entre clima, solo, safra e produtividade.
- Séries históricas por cultura, talhão e safra.
- Modelos preditivos próprios.
- Explicabilidade mínima dos modelos.

Regra:

- IA não deve tomar decisão automática sem revisão humana.

### M10.5 — Sensores IoT, RFID e balanças

Objetivo: preparar integrações de campo para coleta automática de dados.

Escopo futuro:

- Gateway de ingestão IoT.
- Identificação RFID de animais.
- Integração com balanças.
- Normalização de eventos de hardware.
- Tolerância a duplicidade e perda de conexão.
- Auditoria de leituras recebidas.

Eventos candidatos:

- `iot.sensor_reading_received`
- `animal.rfid_detected`
- `animal.weight_imported_from_scale`

### M10.6 — API pública documentada

Objetivo: expor integração pública segura para parceiros, ERPs externos, automações e comunidade.

Escopo futuro:

- OpenAPI/Swagger.
- Versionamento `/api/public/v1`.
- Tokens pessoais ou OAuth2.
- Rate limit por tenant/cliente.
- Webhooks.
- Política de breaking changes.
- Exemplos de SDK ou snippets.

Premissas:

- API interna `/api/internal/v1` deve estar madura antes da API pública.

### M10.7 — Integrações externas e marketplace

Objetivo: permitir ecossistema de módulos e integrações sem acoplar o core.

Escopo futuro:

- ERPs externos.
- WhatsApp.
- Metabase.
- Marketplace de módulos.
- Convenções de extensão.
- Permissões por módulo.

Regra:

- Integrações devem ser opcionais e desativáveis.

### M10.8 — SaaS multi-tenant

Objetivo: evoluir a instalação open source para operação SaaS sem quebrar a edição self-hosted.

Estratégias possíveis:

- `tenant_id` compartilhado no mesmo banco.
- Schema por tenant.
- Banco por tenant para clientes maiores.

Escopo futuro:

- Provisionamento de tenants.
- Isolamento de dados.
- Billing/planos somente quando autorizado em fase específica.
- Métricas por tenant.
- Backups e restauração por cliente.
- Hardening LGPD.

Regra:

- O core deve continuar instalável em Docker Compose para uso próprio.

### M10.9 — Demo pública

Objetivo: criar instância pública com dados fictícios para atrair usuários, comunidade e colaboradores.

Opções de hospedagem:

- Render.
- Railway.
- VPS própria.
- Fly.io.
- Oracle Cloud Free Tier.

Dados fictícios mínimos:

- Fazenda fictícia.
- Talhões.
- Pastagens.
- Safra exemplo.
- Lote animal.
- Atividades.
- Estoque.
- Financeiro simples.
- Dashboard.

Requisitos:

- Reset periódico dos dados.
- Aviso claro de ambiente demonstrativo.
- Sem dados pessoais reais.

### M10.10 — Governança open source

Objetivo: tornar o projeto colaborativo, seguro e previsível.

Escopo:

- `CONTRIBUTING.md`.
- `CODE_OF_CONDUCT.md`.
- `SECURITY.md`.
- Templates de issue.
- Template de pull request.
- Padrão de branches.
- Padrão de commits.
- Documentação de arquitetura.
- Documentação da API.

## Ordem recomendada

1. Consolidar MVP e testes.
2. Publicar governança open source mínima.
3. Publicar roadmap e issues futuras.
4. Preparar API e contratos.
5. Criar demo pública.
6. Evoluir PWA.
7. Planejar app nativo.
8. Integrar INPE/NDVI.
9. Integrar IoT/RFID/balanças.
10. Evoluir SaaS, marketplace e modelos preditivos.

## Critério para começar uma fase futura

Uma fase futura só deve começar quando:

- A fase anterior estiver testada.
- As migrations estiverem estáveis.
- O escopo estiver aprovado em issue.
- Houver critérios de aceite objetivos.
- A mudança não invadir módulos futuros não autorizados.
