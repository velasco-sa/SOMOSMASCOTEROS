#!/usr/bin/env bash
# Arranca la base de datos y el servidor web dentro de GitHub Codespaces.
# Se ejecuta solo al abrir el Codespace; también puedes correrlo a mano:
#   bash .devcontainer/start.sh
cd "$(dirname "$0")/.." || exit 1
ROOT="$(pwd)"
LOG=/tmp/php-server.log
BACKUP="$ROOT/database/respaldo-auto.sql"
SUDO=""; [ "$(id -u)" -ne 0 ] && command -v sudo >/dev/null 2>&1 && SUDO="sudo"

ok()   { echo "✔ $*"; }
step() { echo "▶ $*"; }
fail() { echo ""; echo "✖ $*"; echo "  Toma una captura de esta terminal y envíasela a quien te ayuda."; exit 1; }

step "Carpeta del proyecto: $ROOT"
[ -f "$ROOT/index.php" ] || fail "No encuentro index.php en $ROOT. Ejecuta primero: git pull"

# ---------- 1. Programas necesarios (se instalan solos si faltan) ----------
need_pkgs=""
command -v php >/dev/null 2>&1 || need_pkgs="php-cli php-mysql php-gd php-mbstring php-xml"
if command -v php >/dev/null 2>&1 && ! php -m 2>/dev/null | grep -qi '^pdo_mysql$'; then
  need_pkgs="$need_pkgs php-mysql"
fi
if ! command -v mysqld >/dev/null 2>&1 && ! command -v mariadbd >/dev/null 2>&1 && [ ! -x /usr/sbin/mysqld ] && [ ! -x /usr/sbin/mariadbd ]; then
  need_pkgs="$need_pkgs mariadb-server mariadb-client"
fi
if [ -n "$need_pkgs" ]; then
  step "Instalando programas que faltan ($need_pkgs). Puede tardar 1-3 minutos…"
  $SUDO apt-get update -qq >/dev/null 2>&1
  DEBIAN_FRONTEND=noninteractive $SUDO apt-get install -y -qq $need_pkgs >/dev/null 2>&1 \
    || fail "No se pudieron instalar: $need_pkgs"
fi
command -v php >/dev/null 2>&1 || fail "PHP no está disponible."
php -m 2>/dev/null | grep -qi '^pdo_mysql$' || fail "PHP no tiene la extensión pdo_mysql. Reconstruye el contenedor: F1 → 'Codespaces: Rebuild Container'."
ok "PHP $(php -r 'echo PHP_VERSION;') listo"

# ---------- 2. Base de datos ----------
step "Iniciando la base de datos…"
$SUDO service mariadb start >/dev/null 2>&1 || $SUDO service mysql start >/dev/null 2>&1 || true
for i in $(seq 1 40); do $SUDO mysqladmin ping --silent >/dev/null 2>&1 && break; sleep 1; done
$SUDO mysqladmin ping --silent >/dev/null 2>&1 || fail "La base de datos no respondió."

$SUDO mysql -uroot <<'SQL' || fail "No se pudo preparar la base de datos."
CREATE DATABASE IF NOT EXISTS somos_mascoteros CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'mascoteros'@'localhost' IDENTIFIED BY 'mascoteros';
CREATE USER IF NOT EXISTS 'mascoteros'@'127.0.0.1' IDENTIFIED BY 'mascoteros';
GRANT ALL PRIVILEGES ON somos_mascoteros.* TO 'mascoteros'@'localhost';
GRANT ALL PRIVILEGES ON somos_mascoteros.* TO 'mascoteros'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

if ! $SUDO mysql -uroot somos_mascoteros -e "SELECT 1 FROM users LIMIT 1" >/dev/null 2>&1; then
  if [ -s "$BACKUP" ]; then
    step "Restaurando tu último respaldo automático…"
    $SUDO mysql -uroot somos_mascoteros < "$BACKUP" || fail "Error al restaurar el respaldo."
  else
    step "Creando tablas y cargando datos de ejemplo…"
    $SUDO mysql -uroot somos_mascoteros < database/01_schema.sql || fail "Error al crear las tablas."
    $SUDO mysql -uroot somos_mascoteros < database/02_seed.sql   || fail "Error al cargar los datos de ejemplo."
  fi
fi
ok "Base de datos lista"

# Respaldo automático ahora y cada 10 minutos, dentro de la carpeta del proyecto
# (sobrevive a reinicios y reconstrucciones del Codespace)
$SUDO mysqldump -uroot --single-transaction somos_mascoteros > "$BACKUP.tmp" 2>/dev/null && mv "$BACKUP.tmp" "$BACKUP" \
  && ok "Respaldo guardado en database/respaldo-auto.sql"
pkill -f "sm_autobackup_loop" >/dev/null 2>&1
setsid nohup bash .devcontainer/autobackup.sh sm_autobackup_loop >/dev/null 2>&1 < /dev/null &
mkdir -p uploads/media 2>/dev/null; $SUDO chmod -R a+rwX uploads 2>/dev/null

# ---------- 3. Servidor web ----------
export DB_HOST="${DB_HOST:-127.0.0.1}" DB_PORT="${DB_PORT:-3306}" DB_NAME="${DB_NAME:-somos_mascoteros}"
export DB_USER="${DB_USER:-mascoteros}" DB_PASS="${DB_PASS:-mascoteros}"

pkill -f "php .*-S .*:8080" >/dev/null 2>&1; $SUDO pkill -f "php .*-S .*:8080" >/dev/null 2>&1
sleep 1
step "Iniciando el servidor web en el puerto 8080…"
probe() { php -r '$c=@file_get_contents($argv[1]); echo isset($http_response_header[0]) ? $http_response_header[0] : "sin respuesta";' "$1"; }
serve() { setsid nohup php -d upload_max_filesize=10M -d post_max_size=12M -S "$1" -t "$ROOT" >"$LOG" 2>&1 < /dev/null & sleep 2; }
# Escuchar en IPv6 + IPv4 (el reenvío de puertos de Codespaces puede usar cualquiera de los dos)
serve "[::]:8080"
case "$(probe http://127.0.0.1:8080/)" in
  *" 200"*) ok "Servidor escuchando en IPv4 e IPv6" ;;
  *) pkill -f "php .*-S .*:8080" >/dev/null 2>&1; sleep 1; serve "0.0.0.0:8080"; ok "Servidor escuchando en IPv4" ;;
esac

# ---------- 4. Comprobación ----------
code=$(probe http://127.0.0.1:8080/)
echo "  Respuesta del sitio (IPv4): $code"
echo "  Respuesta del sitio (IPv6): $(probe 'http://[::1]:8080/')"
case "$code" in
  *" 200"*) ok "El sitio responde correctamente." ;;
  *) echo "--- Registro del servidor ---"; tail -20 "$LOG"; fail "El sitio no respondió bien." ;;
esac

echo ""
if [ -n "$CODESPACE_NAME" ] && [ -n "$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN" ]; then
  URL="https://${CODESPACE_NAME}-8080.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}/"
  echo "🌐 Abre tu sitio aquí (Ctrl + clic):  $URL"
  echo "🔐 Panel de administración:          ${URL}admin/"
else
  echo "🌐 Abre la pestaña PORTS, busca el puerto 8080 y haz clic en el globo."
fi
echo "   Si el navegador muestra error, espera 10 segundos y recarga la página."
