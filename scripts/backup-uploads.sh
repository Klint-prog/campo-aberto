#!/usr/bin/env sh
set -eu

SOURCE_DIR="${UPLOADS_DIR:-storage/app/public}"
BACKUP_DIR="${BACKUP_UPLOADS_DIR:-/backups/uploads}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-30}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
FILE="${BACKUP_DIR}/uploads-${TIMESTAMP}.tar.gz"
SHA_FILE="${FILE}.sha256"

mkdir -p "${BACKUP_DIR}"
chmod 700 "${BACKUP_DIR}"

if [ ! -d "${SOURCE_DIR}" ]; then
  echo "Diretório de uploads não existe: ${SOURCE_DIR}" >&2
  exit 66
fi

tar -czf "${FILE}" -C "${SOURCE_DIR}" .
sha256sum "${FILE}" > "${SHA_FILE}"
chmod 600 "${FILE}" "${SHA_FILE}"
find "${BACKUP_DIR}" -type f -name '*.tar.gz' -mtime +"${RETENTION_DAYS}" -delete
find "${BACKUP_DIR}" -type f -name '*.sha256' -mtime +"${RETENTION_DAYS}" -delete

echo "Backup de uploads criado: ${FILE}"
