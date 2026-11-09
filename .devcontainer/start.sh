#!/usr/bin/env bash
# Arranca la base de datos y el servidor web dentro de GitHub Codespaces.
set -e
cd "$(dirname "$0")/.."

echo "▶ Iniciando MariaDB…"
service mariadb start >/dev/null 2>&1 || service mysql start >/dev/null 2>&1 || true
for i in $(seq 1 30); do mysqladmin ping --silent 2>/dev/null && break; sleep 1; done

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
pkill -f "php -S 0.0.0.0:8080" >/dev/null 2>&1 || true
echo "▶ Iniciando servidor web en el puerto 8080…"
setsid nohup php -S 0.0.0.0:8080 -t . > /tmp/php-server.log 2>&1 < /dev/null &
sleep 1
echo "✔ Listo. Abre la pestaña PORTS (Puertos) y haz clic en el globo del puerto 8080."
