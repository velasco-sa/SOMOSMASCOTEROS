<?php
/** Formulario público de contacto. */
require __DIR__ . '/_common.php';

if (!empty($_POST['website'])) {
    json_out(['ok' => true, 'message' => setting('contact_success_message')]);
}
rate_limit('contact', 4, 600);

$name = trim((string)($_POST['full_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || mb_strlen($name) > 120) json_out(['ok' => false, 'message' => 'Escribe tu nombre.', 'field' => 'full_name'], 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 160) json_out(['ok' => false, 'message' => 'Escribe un correo válido.', 'field' => 'email'], 422);
if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{7,30}$/', $phone)) json_out(['ok' => false, 'message' => 'El teléfono no parece válido.', 'field' => 'phone'], 422);
if (mb_strlen($message) < 5) json_out(['ok' => false, 'message' => 'Escribe tu mensaje.', 'field' => 'message'], 422);

db_exec(
    'INSERT INTO contact_messages (full_name, email, phone, subject, message) VALUES (?,?,?,?,?)',
    [$name, $email, $phone ?: null, $subject !== '' ? mb_substr($subject, 0, 160) : null, mb_substr($message, 0, 3000)]
);
json_out(['ok' => true, 'message' => setting('contact_success_message')]);
