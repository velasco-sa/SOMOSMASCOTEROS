<?php
/** Consulta pública de disponibilidad. */
require __DIR__ . '/_common.php';
rate_limit('availability', 40, 60);
[$pet, $in, $out, $count] = read_stay();
$r = compute_availability($pet, $in, $out, $count);
json_out([
    'ok'        => true,
    'available' => $r['available'],
    'nights'    => $r['nights'],
    'capacity'  => $r['capacity'],
    'days'      => $r['days'],
    'message'   => $r['available'] ? setting('availability_ok_message') : setting('availability_full_message'),
]);
