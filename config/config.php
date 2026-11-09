<?php
/**
 * Configuración de conexión a la base de datos.
 *
 * Cuando contrates tu hosting, cambia SOLO los valores de la derecha
 * (los que están después de ?:) por los datos que te dé tu proveedor.
 * Las variables de entorno (getenv) se usan automáticamente en GitHub Codespaces.
 */
return [
    'db_host'    => getenv('DB_HOST') ?: 'localhost',
    'db_port'    => getenv('DB_PORT') ?: '3306',
    'db_name'    => getenv('DB_NAME') ?: 'somos_mascoteros',
    'db_user'    => getenv('DB_USER') ?: 'root',
    'db_pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    // Zona horaria del negocio (lista: https://www.php.net/manual/es/timezones.php)
    'timezone'   => getenv('APP_TIMEZONE') ?: 'America/Mexico_City',
    // Pon en true solo mientras desarrollas para ver errores detallados
    'debug'      => getenv('APP_DEBUG') === '1',
    // Tamaño máximo de imágenes subidas (MB)
    'max_upload_mb' => 8,
];
