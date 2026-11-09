#!/usr/bin/env bash
# Borra TODO el contenido y vuelve a cargar los datos de ejemplo.
set -e
cd "$(dirname "$0")/.."
read -r -p "Esto borrará todos los cambios hechos desde el panel. ¿Continuar? (si/no): " ok
[ "$ok" = "si" ] || { echo "Cancelado."; exit 0; }
mysql -uroot somos_mascoteros < database/01_schema.sql
mysql -uroot somos_mascoteros < database/02_seed.sql
echo "✔ Base de datos restaurada con los datos de ejemplo."
