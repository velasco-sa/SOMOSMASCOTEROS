#!/usr/bin/env bash
# Arranca la base de datos y el servidor web dentro de GitHub Codespaces.
# Se ejecuta solo al abrir el Codespace; también puedes correrlo a mano:
#   bash .devcontainer/start.sh
cd "$(dirname "$0")/.."
LOG=/tmp/php-server.log

echo "▶ Iniciando MariaDB…"
if ! command -v mysqld >/dev/null 2>&1 && ! command -v mariadbd >/dev/null 2>&1; then
  echo "✖ MariaDB no está instalado. El Codespace no se construyó con la configuración del proyecto."
  echo "  Solución: menú ☰ (o F1) → 'Codespaces: Rebuild Container' / 'Reconstruir contenedor'."
  exit 1
fi
service mariadb start >/dev/null 2>&1 || service mysql start >/dev/null 2>&1 || true
for i in $(seq 1 40); do mysqladmin ping --silent >/dev/null 2>&1 && break; sleep 1; done
if ! mysqladmin ping --silent >/dev/null 2>&1; then
  echo "✖ La base de datos no respondió. Vuelve a ejecutar: bash .devcontainer/start.sh"
  exit 1
fi

mysql -uroot <<'SQL'
CREATE DATABASE IF NOT EXISTS somos_mascoteros CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'mascoteros'@'localhost' IDENTIFIED BY 'mascoteros';
CREATE USER IF NOT EXISTS 'mascoteros'@'127.0.0.1' IDENTIFIED BY 'mascoteros';
GRANT ALL PRIVILEGES ON somos_mascoteros.* TO 'mascoteros'@'localhost';
GRANT ALL PRIVILEGES ON somos_mascoteros.* TO 'mascoteros'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

if ! mysql -uroot somos_mascoteros -e "SELECT 1 FROM users LIMIT 1" >/dev/null 2>&1; then
  echo "▶ Creando tablas y cargando datos de ejemplo…"
  mysql -uroot somos_mascoteros < database/01_schema.sql
  mysql -uroot somos_mascoteros < database/02_seed.sql
fi
mkdir -p uploads/media

# Variables de conexión (por si el Codespace no las cargó)
export DB_HOST="${DB_HOST:-127.0.0.1}" DB_PORT="${DB_PORT:-3306}" DB_NAME="${DB_NAME:-somos_mascoteros}"
export DB_USER="${DB_USER:-mascoteros}" DB_PASS="${DB_PASS:-mascoteros}"

if pgrep -f "php -S 0.0.0.0:8080" >/dev/null 2>&1; then
  echo "✔ El servidor web ya estaba encendido en el puerto 8080."
else
  echo "▶ Iniciando servidor web en el puerto 8080…"
  setsid nohup php -S 0.0.0.0:8080 -t . >"$LOG" 2>&1 < /dev/null &
  sleep 2
  if ! pgrep -f "php -S 0.0.0.0:8080" >/dev/null 2>&1; then
    echo "✖ El servidor web no pudo iniciar. Detalle:"; cat "$LOG"; exit 1
  fi
fi
echo ""
echo "✔ ¡Listo! Abre la pestaña PORTS (Puertos), busca el 8080 y haz clic en el globo 🌐."
echo "  Sitio:  la dirección del puerto 8080 (sin agregar nada)"
echo "  Panel:  la misma dirección + /admin/"
