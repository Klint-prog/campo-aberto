#!/usr/bin/env sh
set -eu

if [ "${1:-}" = "" ]; then
  echo "Uso: scripts/restore-postgres.sh /caminho/backup.dump" >&2
  exit 64
fi

BACKUP_FILE="$1"
DATABASE="${POSTGRES_DB:-${DB_DATABASE:-campo_aberto}}"
USER="${POSTGRES_USER:-${DB_USERNAME:-campo_aberto}}"
HOST="${POSTGRES_HOST:-${DB_HOST:-postgres}}"
PORT="${POSTGRES_PORT:-${DB_PORT:-5432}}"

if [ ! -f "${BACKUP_FILE}" ]; then
  echo "Backup não encontrado: ${BACKUP_FILE}" >&2
  exit 66
fi

if [ -f "${BACKUP_FILE}.sha256" ]; then
  sha256sum -c "${BACKUP_FILE}.sha256"
fi

if [ "${RESTORE_CONFIRM:-}" != "I_UNDERSTAND_THIS_REPLACES_DATA" ]; then
  echo "Defina RESTORE_CONFIRM=I_UNDERSTAND_THIS_REPLACES_DATA para restaurar." >&2
  exit 78
fi

pg_restore \
  --clean \
  --if-exists \
  --no-owner \
  --no-privileges \
  --host="${HOST}" \
  --port="${PORT}" \
  --username="${USER}" \
  --dbname="${DATABASE}" \
  "${BACKUP_FILE}"

echo "Restore PostgreSQL concluído a partir de: ${BACKUP_FILE}"
