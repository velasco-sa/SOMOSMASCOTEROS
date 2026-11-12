<?php
/** Solicitud pública de reserva (queda como "Pendiente"). */
require __DIR__ . '/_common.php';

if (setting('booking_requests_enabled', '1') !== '1') {
    json_out(['ok' => false, 'message' => 'Las solicitudes en línea no están disponibles por ahora.'], 403);
}
if (!empty($_POST['website'])) { // trampa para bots
    json_out(['ok' => true, 'message' => setting('booking_success_message')]);
}
rate_limit('booking', 5, 600);
[$pet, $in, $out, $count] = read_stay();

$name  = trim((string)($_POST['full_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$petName = trim((string)($_POST['pet_name'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));

if ($name === '' || mb_strlen($name) > 120) json_out(['ok' => false, 'message' => 'Escribe tu nombre.', 'field' => 'full_name'], 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 160) json_out(['ok' => false, 'message' => 'Escribe un correo válido.', 'field' => 'email'], 422);
if (!preg_match('/^[0-9+\-\s()]{7,30}$/', $phone)) json_out(['ok' => false, 'message' => 'Escribe un teléfono válido.', 'field' => 'phone'], 422);
if ($petName === '' || mb_strlen($petName) > 80) json_out(['ok' => false, 'message' => 'Escribe el nombre de tu mascota.', 'field' => 'pet_name'], 422);
$notes = mb_substr($notes, 0, 1000);

$pdo = db();
$pdo->beginTransaction();
try {
    // Bloqueo para evitar dos reservas simultáneas sobre el último lugar
    $pdo->prepare('SELECT id FROM pet_types WHERE id = ? FOR UPDATE')->execute([$pet]);
    $r = compute_availability($pet, $in, $out, $count);
    if (!$r['available']) {
        $pdo->rollBack();
        json_out(['ok' => false, 'message' => setting('availability_full_message')], 409);
    }
    $customer = db_one('SELECT id FROM customers WHERE email = ?', [$email]);
    if ($customer) {
        $customerId = (int)$customer['id'];
        db_exec('UPDATE customers SET full_name = ?, phone = ? WHERE id = ?', [$name, $phone, $customerId]);
    } else {
        db_exec('INSERT INTO customers (full_name, email, phone) VALUES (?,?,?)', [$name, $email, $phone]);
        $customerId = (int)$pdo->lastInsertId();
    }
    $status = (int)db_value("SELECT id FROM booking_statuses WHERE code = 'pending'")
        ?: (int)db_value('SELECT id FROM booking_statuses ORDER BY sort_order LIMIT 1');
    db_exec(
        'INSERT INTO bookings (customer_id, pet_type_id, booking_status_id, pet_name, pets_count, check_in, check_out, notes)
         VALUES (?,?,?,?,?,?,?,?)',
        [$customerId, $pet, $status, $petName, $count, $in, $out, $notes !== '' ? $notes : null]
    );
    $bookingId = (int)$pdo->lastInsertId();
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_out(['ok' => false, 'message' => 'No pudimos registrar tu solicitud. Inténtalo más tarde.'], 500);
}

// Aviso por correo al negocio (después de responder al visitante)
require_once dirname(__DIR__) . '/includes/mailer.php';
respond_then(['ok' => true, 'message' => setting('booking_success_message')], function () use ($bookingId, $pet, $in, $out, $count, $name, $email, $phone, $petName, $notes) {
    $petType = (string)db_value('SELECT name FROM pet_types WHERE id = ?', [$pet]);
    $nights = (int)(new DateTime($in))->diff(new DateTime($out))->days;
    $html = mail_template('Nueva solicitud de reserva', [
        'Cliente'   => $name,
        'Correo'    => $email,
        'Teléfono'  => $phone,
        'Mascota'   => $petName . ' (' . $petType . ($count > 1 ? ' · ' . $count . ' mascotas' : '') . ')',
        'Llegada'   => fecha_es($in),
        'Salida'    => fecha_es($out),
        'Noches'    => (string)$nights,
        'Notas'     => $notes,
        'Reserva #' => (string)$bookingId,
    ], 'Un visitante solicitó una estancia desde el sitio web. Quedó como "Pendiente": confírmala o cancélala desde el panel, en Reservas.');
    notify_admin('booking', '🐾 Nueva reserva: ' . $petName . ' · ' . fecha_es($in) . ' → ' . fecha_es($out), $html, $email, $name);
});
