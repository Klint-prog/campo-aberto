#!/usr/bin/env sh
set -eu

BACKUP_DIR="${BACKUP_DIR:-/backups/postgres}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-30}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
DATABASE="${POSTGRES_DB:-${DB_DATABASE:-campo_aberto}}"
USER="${POSTGRES_USER:-${DB_USERNAME:-campo_aberto}}"
HOST="${POSTGRES_HOST:-${DB_HOST:-postgres}}"
PORT="${POSTGRES_PORT:-${DB_PORT:-5432}}"
FILE="${BACKUP_DIR}/${DATABASE}-${TIMESTAMP}.dump"
SHA_FILE="${FILE}.sha256"

mkdir -p "${BACKUP_DIR}"
chmod 700 "${BACKUP_DIR}"

pg_dump \
  --format=custom \
  --no-owner \
  --no-privileges \
  --host="${HOST}" \
  --port="${PORT}" \
  --username="${USER}" \
  --dbname="${DATABASE}" \
  --file="${FILE}"

sha256sum "${FILE}" > "${SHA_FILE}"
chmod 600 "${FILE}" "${SHA_FILE}"
find "${BACKUP_DIR}" -type f -name '*.dump' -mtime +"${RETENTION_DAYS}" -delete
find "${BACKUP_DIR}" -type f -name '*.sha256' -mtime +"${RETENTION_DAYS}" -delete

echo "Backup PostgreSQL criado: ${FILE}"
