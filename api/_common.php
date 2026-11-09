<?php
declare(strict_types=1);
define('BASE_PREFIX', '../');
require dirname(__DIR__) . '/includes/bootstrap.php';
header('X-Robots-Tag: noindex, nofollow');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_out(['ok' => false, 'message' => 'Método no permitido.'], 405);
}
if (!csrf_check()) {
    json_out(['ok' => false, 'message' => 'Tu sesión expiró. Recarga la página e inténtalo de nuevo.'], 419);
}

/** Límite simple de envíos por sesión (anti-spam). */
function rate_limit(string $key, int $max, int $seconds): void
{
    $now = time();
    $list = array_filter($_SESSION['rl'][$key] ?? [], fn($t) => $t > $now - $seconds);
    if (count($list) >= $max) {
        json_out(['ok' => false, 'message' => 'Has enviado demasiadas solicitudes. Espera unos minutos.'], 429);
    }
    $list[] = $now;
    $_SESSION['rl'][$key] = array_values($list);
}

/** Valida las fechas y el tipo de mascota de una consulta. */
function read_stay(): array
{
    $pet = (int)($_POST['pet_type_id'] ?? 0);
    $in = (string)($_POST['check_in'] ?? '');
    $out = (string)($_POST['check_out'] ?? '');
    $count = max(1, min(20, (int)($_POST['pets_count'] ?? 1)));

    if (!$pet || !db_value('SELECT id FROM pet_types WHERE id = ? AND is_active = 1', [$pet])) {
        json_out(['ok' => false, 'message' => 'Selecciona un tipo de mascota.'], 422);
    }
    if (!valid_date($in) || !valid_date($out)) {
        json_out(['ok' => false, 'message' => 'Selecciona las fechas de llegada y salida.'], 422);
    }
    if ($out <= $in) {
        json_out(['ok' => false, 'message' => 'La fecha de salida debe ser posterior a la de llegada.'], 422);
    }
    $minAdvance = max(0, (int)setting('booking_min_advance_days', '0'));
    $minDate = (new DateTimeImmutable('today'))->modify("+$minAdvance day")->format('Y-m-d');
    if ($in < $minDate) {
        json_out(['ok' => false, 'message' => 'La fecha de llegada debe ser a partir del ' . date('d/m/Y', strtotime($minDate)) . '.'], 422);
    }
    $maxNights = max(1, (int)setting('booking_max_nights', '60'));
    $nights = (int)(new DateTime($in))->diff(new DateTime($out))->days;
    if ($nights > $maxNights) {
        json_out(['ok' => false, 'message' => "La estancia máxima es de $maxNights noches."], 422);
    }
    return [$pet, $in, $out, $count];
}
