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
    // Envío de avisos por correo (reservas y mensajes) usando tu Gmail.
    // smtp_user: tu Gmail completo. smtp_pass: la "contraseña de aplicación" de 16 letras
    // que genera Google (NO la contraseña normal de tu Gmail). Déjalos vacíos para no usar Gmail.
    'mail' => [
        'smtp_host'   => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'smtp_port'   => (int)(getenv('SMTP_PORT') ?: 465),
        'smtp_secure' => getenv('SMTP_SECURE') ?: 'ssl',   // 'ssl' (puerto 465) o 'tls' (puerto 587)
        'smtp_user'   => getenv('SMTP_USER') ?: '',
        'smtp_pass'   => getenv('SMTP_PASS') ?: '',
    ],
];
