<?php
declare(strict_types=1);

/**
 * Devuelve la conexión PDO (una sola por petición).
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    global $CONFIG;
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $CONFIG['db_host'],
        $CONFIG['db_port'],
        $CONFIG['db_name']
    );
    try {
        $pdo = new PDO($dsn, $CONFIG['db_user'], $CONFIG['db_pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '" . date('P') . "'");
    } catch (PDOException $e) {
        http_response_code(500);
        $detail = $CONFIG['debug'] ? htmlspecialchars($e->getMessage()) : 'Revisa los datos de conexión en config/config.php.';
        echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Sin conexión</title><body style="margin:0;min-height:100vh;display:grid;place-items:center;'
            . 'background:#F0F8FB;font-family:system-ui,sans-serif;color:#1C3762;padding:16px">'
            . '<div style="max-width:520px;background:#fff;border-radius:20px;padding:32px;box-shadow:0 20px 60px rgba(28,55,98,.12)">'
            . '<h1 style="margin:0 0 8px;font-size:1.4rem">No se pudo conectar a la base de datos</h1>'
            . '<p style="margin:0;line-height:1.6">' . $detail . '</p></div></body>';
        exit;
    }
    return $pdo;
}

function db_all(string $sql, array $params = []): array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $row = $st->fetch();
    return $row === false ? null : $row;
}

function db_value(string $sql, array $params = [])
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $v = $st->fetchColumn();
    return $v === false ? null : $v;
}

function db_exec(string $sql, array $params = []): int
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->rowCount();
}
