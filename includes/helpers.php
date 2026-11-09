<?php
declare(strict_types=1);

/** Escapa texto para imprimirlo en HTML. */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Prefijo de rutas relativas (vacío en la raíz, '../' dentro de /admin o /api). */
function base(): string
{
    return defined('BASE_PREFIX') ? BASE_PREFIX : '';
}

/** Ruta a un archivo estático o subido. Las URL absolutas se devuelven tal cual. */
function url_for(?string $path): string
{
    if ($path === null || $path === '') {
        return '';
    }
    if (preg_match('~^(https?:)?//~i', $path)) {
        return $path;
    }
    return base() . ltrim($path, '/');
}

/** Todas las configuraciones del sitio (clave => valor) con la ruta de imagen resuelta. */
function settings(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    $rows = db_all('SELECT s.setting_key, s.input_type, s.setting_value, m.file_path
                    FROM settings s LEFT JOIN media m ON m.id = s.media_id');
    foreach ($rows as $r) {
        $cache[$r['setting_key']] = $r['input_type'] === 'image' ? ($r['file_path'] ?? '') : ($r['setting_value'] ?? '');
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $s = settings();
    return isset($s[$key]) && $s[$key] !== '' ? (string)$s[$key] : $default;
}

/** Valida un color hexadecimal; si no es válido devuelve el valor por defecto. */
function safe_color(string $value, string $default): string
{
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $default;
}

/** Nombre de fuente seguro para Google Fonts. */
function safe_font(string $value, string $default): string
{
    return preg_match('/^[A-Za-z0-9 ]{2,40}$/', $value) ? $value : $default;
}

/** Convierte un color hexadecimal en "r, g, b" para usar en rgba(). */
function hex_to_rgb(string $hex): string
{
    $hex = ltrim($hex, '#');
    return hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
}

/** Bloque <style> con las variables de color y tipografía tomadas de la base de datos. */
function theme_style_tag(): string
{
    $bg  = safe_color(setting('color_background'), '#F0F8FB');
    $pri = safe_color(setting('color_primary'), '#1C3762');
    $sec = safe_color(setting('color_secondary'), '#9ACCCD');
    $acc = safe_color(setting('color_accent'), '#F5C15C');
    $fh  = safe_font(setting('font_headings'), 'Montserrat');
    $fb  = safe_font(setting('font_body'), 'Raleway');
    return "<style>:root{--c-bg:$bg;--c-primary:$pri;--c-secondary:$sec;--c-accent:$acc;"
        . '--rgb-bg:' . hex_to_rgb($bg) . ';--rgb-primary:' . hex_to_rgb($pri) . ';--rgb-secondary:' . hex_to_rgb($sec)
        . ';--rgb-accent:' . hex_to_rgb($acc) . ";--f-head:'$fh',system-ui,sans-serif;--f-body:'$fb',system-ui,sans-serif;}</style>";
}

/** Enlace de Google Fonts para las tipografías configuradas. */
function google_fonts_url(): string
{
    $fams = array_unique([safe_font(setting('font_headings'), 'Montserrat'), safe_font(setting('font_body'), 'Raleway')]);
    $q = implode('&', array_map(fn($f) => 'family=' . str_replace(' ', '+', $f) . ':wght@300;400;500;600;700;800', $fams));
    return 'https://fonts.googleapis.com/css2?' . $q . '&display=swap';
}

/** Token CSRF por sesión. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): bool
{
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? '');
    return is_string($sent) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

/** Respuesta JSON y fin de la ejecución. */
function json_out(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/** Valida una fecha AAAA-MM-DD. */
function valid_date(string $d): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt !== false && $dt->format('Y-m-d') === $d;
}

/**
 * Limpia HTML enriquecido dejando solo etiquetas y atributos seguros.
 */
function sanitize_html(?string $html): string
{
    $html = trim((string)$html);
    if ($html === '' || $html === '<p><br></p>') {
        return '';
    }
    $allowed = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'a', 'h2', 'h3', 'h4', 'blockquote', 'span'];
    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('__root');
    if (!$root) {
        return e(strip_tags($html));
    }
    $walk = function (DOMNode $node) use (&$walk, $allowed, $doc) {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                    $node->removeChild($child);
                    continue;
                }
                $walk($child);
                if (!in_array($tag, $allowed, true)) {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    $keep = false;
                    if ($tag === 'a' && $name === 'href') {
                        $keep = (bool)preg_match('~^(https?:|mailto:|tel:|#|/|[a-z0-9_\-./]+(\?|#|$))~i', trim($attr->value))
                            && !preg_match('~^\s*javascript:~i', $attr->value);
                    } elseif ($tag === 'a' && $name === 'target') {
                        $keep = $attr->value === '_blank';
                    } elseif ($name === 'class') {
                        $keep = (bool)preg_match('/^(ql-[a-z0-9\-]+\s*)+$/', $attr->value);
                    }
                    if (!$keep) {
                        $child->removeAttribute($attr->name);
                    }
                }
                if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                    $child->setAttribute('rel', 'noopener');
                }
            } elseif ($child instanceof DOMComment) {
                $node->removeChild($child);
            }
        }
    };
    $walk($root);
    $out = '';
    foreach ($root->childNodes as $c) {
        $out .= $doc->saveHTML($c);
    }
    return $out;
}

/** Texto plano corto a partir de HTML. */
function excerpt(?string $html, int $len = 140): string
{
    $t = trim(preg_replace('/\s+/', ' ', strip_tags((string)$html)));
    return mb_strlen($t) > $len ? mb_substr($t, 0, $len - 1) . '…' : $t;
}

/**
 * Calcula la disponibilidad de un tipo de mascota entre dos fechas.
 * check_in = día de llegada; check_out = día de salida (no cuenta como noche).
 */
function compute_availability(int $petTypeId, string $checkIn, string $checkOut, int $pets = 1, ?int $excludeBookingId = null): array
{
    $capacity = (int)db_value(
        'SELECT COALESCE(SUM(capacity),0) FROM accommodations WHERE pet_type_id = ? AND is_active = 1',
        [$petTypeId]
    );

    $params = [$petTypeId, $checkOut, $checkIn];
    $sql = 'SELECT b.check_in, b.check_out, b.pets_count FROM bookings b
            JOIN booking_statuses s ON s.id = b.booking_status_id
            WHERE s.blocks_capacity = 1 AND b.pet_type_id = ? AND b.check_in < ? AND b.check_out > ?';
    if ($excludeBookingId) {
        $sql .= ' AND b.id <> ?';
        $params[] = $excludeBookingId;
    }
    $bookings = db_all($sql, $params);
    $closed = db_all('SELECT date_from, date_to, reason FROM closed_dates WHERE date_from <= ? AND date_to >= ?', [$checkOut, $checkIn]);

    $days = [];
    $available = $capacity > 0;
    $start = new DateTimeImmutable($checkIn);
    $end = new DateTimeImmutable($checkOut);
    for ($d = $start; $d <= $end; $d = $d->modify('+1 day')) {
        $ds = $d->format('Y-m-d');
        $isNight = $d < $end;
        $occupied = 0;
        if ($isNight) {
            foreach ($bookings as $b) {
                if ($b['check_in'] <= $ds && $b['check_out'] > $ds) {
                    $occupied += (int)$b['pets_count'];
                }
            }
        }
        $closedReason = null;
        foreach ($closed as $c) {
            if ($c['date_from'] <= $ds && $c['date_to'] >= $ds) {
                $closedReason = $c['reason'];
                break;
            }
        }
        $free = max(0, $capacity - $occupied);
        $ok = $closedReason === null && (!$isNight || $free >= $pets);
        if (!$ok) {
            $available = false;
        }
        $days[] = [
            'date'     => $ds,
            'night'    => $isNight,
            'capacity' => $capacity,
            'occupied' => $occupied,
            'free'     => $free,
            'closed'   => $closedReason,
            'ok'       => $ok,
        ];
    }
    return [
        'available' => $available,
        'capacity'  => $capacity,
        'nights'    => max(0, (int)$start->diff($end)->days),
        'days'      => $days,
    ];
}
