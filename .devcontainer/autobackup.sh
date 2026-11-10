#!/usr/bin/env bash
# Guarda una copia de la base de datos cada 10 minutos (lo lanza start.sh).
cd "$(dirname "$0")/.." || exit 1
SUDO=""; [ "$(id -u)" -ne 0 ] && command -v sudo >/dev/null 2>&1 && SUDO="sudo"
while sleep 600; do
  $SUDO mysqldump -uroot --single-transaction somos_mascoteros > database/respaldo-auto.sql.tmp 2>/dev/null \
    && mv database/respaldo-auto.sql.tmp database/respaldo-auto.sql
done
