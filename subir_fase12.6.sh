#!/bin/bash

# Variáveis
REPO_DIR=~/Downloads/campo-aberto   # ajuste se seu repositório estiver em outro local
BRANCH_NAME=fase/12.6-dashboard
BASE_SHA=872fb1a953f556ecb9e379aa8709c368b1c1aa8e
PR_TITLE="Fase 12.6 - Dashboard operacional e indicadores reais"
PR_BODY="Implementação completa da Fase 12.6:

- Dashboard com indicadores reais por tenant
- Cards de área, fazendas, talhões, culturas e safras
- Atividades do dia, semana, atrasadas e concluídas
- Colheitas recentes e produtividade média
- Animais ativos, vendidos, mortos e próximos eventos
- Estoque baixo, últimas movimentações
- Máquinas disponíveis, manutenção e indisponíveis
- Financeiro resumido (receitas, despesas, saldo estimado)
- Alertas operacionais e atalhos rápidos
- Testes PHPUnit de acesso e métricas"

# Entrar no diretório do repositório
cd $REPO_DIR || exit

# Criar branch a partir do SHA fornecido
git fetch origin
git checkout -b $BRANCH_NAME $BASE_SHA

# Adicionar arquivos da Fase 12.6
git add \
app/Http/Controllers/DashboardController.php \
app/Services/DashboardMetricService.php \
app/Services/DashboardAlertService.php \
resources/views/dashboard.blade.php \
resources/views/dashboard/partials/cards.blade.php \
resources/views/dashboard/partials/alerts.blade.php \
resources/views/dashboard/partials/shortcuts.blade.php \
tests/Feature/DashboardAccessTest.php \
tests/Unit/DashboardMetricServiceTest.php \
tests/Unit/DashboardAlertServiceTest.php \
routes/web.php

# Commit
git commit -m "Fase 12.6: Implementação dashboard operacional com indicadores reais e alertas"

# Push para GitHub
git push origin $BRANCH_NAME

# Criar Pull Request usando GitHub CLI
gh pr create \
--title "$PR_TITLE" \
--body "$PR_BODY" \
--head $BRANCH_NAME \
--base main \
--repo Klint-prog/campo-aberto
