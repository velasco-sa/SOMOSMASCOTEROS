<?php
/**
 * Envío de correos (avisos de reservas y mensajes).
 *
 * Si en config/config.php se completan 'smtp_user' y 'smtp_pass' (por ejemplo,
 * tu Gmail y una "contraseña de aplicación"), los correos salen por el servidor
 * de Gmail y llegan sin caer en spam. Si están vacíos, se usa mail() de PHP.
 */
declare(strict_types=1);

/** Configuración de correo con valores por defecto. */
function mail_config(): array
{
    global $CONFIG;
    return array_merge([
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 465,
        'smtp_secure' => 'ssl',
        'smtp_user' => '',
        'smtp_pass' => '',
    ], (array)($CONFIG['mail'] ?? []));
}

/**
 * Envía un correo HTML. Devuelve [ok(bool), detalle(string)].
 */
function send_mail(string $to, string $subject, string $html, ?string $replyTo = null, ?string $replyName = null): array
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return [false, 'El correo de destino no es válido.'];
    }
    $cfg = mail_config();
    $siteName = setting('site_name', 'Somos Mascoteros');
    $host = preg_replace('/^www\./', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $host = preg_replace('/:\d+$/', '', $host);
    $fromEmail = $cfg['smtp_user'] !== '' ? $cfg['smtp_user'] : 'no-responder@' . $host;
    $plain = preg_replace('~</td>(?=<td)~i', ': </td>', $html);
    $plain = preg_replace('~<br\s*/?>|</p>|</tr>|</h\d>~i', "\n", $plain);
    $text = trim(html_entity_decode(strip_tags($plain), ENT_QUOTES, 'UTF-8'));

    $boundary = 'sm_' . bin2hex(random_bytes(12));
    $headers = [
        'Date: ' . date(DATE_RFC2822),
        'From: ' . mime_header($siteName) . ' <' . $fromEmail . '>',
        'To: <' . $to . '>',
        'Subject: ' . mime_header($subject),
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . (strpos($fromEmail, '@') ? substr($fromEmail, strpos($fromEmail, '@') + 1) : $host) . '>',
    ];
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . ($replyName ? mime_header($replyName) . ' ' : '') . '<' . $replyTo . '>';
    }
    $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($text)) . "\r\n"
        . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html)) . "\r\n--$boundary--\r\n";

    if ($cfg['smtp_user'] !== '' && $cfg['smtp_pass'] !== '') {
        return smtp_send($cfg, $fromEmail, $to, implode("\r\n", $headers) . "\r\n\r\n" . $body);
    }

    // Alternativa sin SMTP: mail() de PHP (puede llegar a la carpeta de spam)
    $mailHeaders = array_values(array_filter($headers, fn($h) => !preg_match('/^(To|Subject|Date):/i', $h)));
    $ok = @mail($to, mime_header($subject), $body, implode("\r\n", $mailHeaders), '-f' . $fromEmail);
    return [$ok, $ok ? 'Enviado con mail() de PHP.' : 'El servidor no pudo enviar el correo con mail(). Configura Gmail en config/config.php.'];
}

/** Codifica un encabezado con acentos (RFC 2047). */
function mime_header(string $s): string
{
    $s = str_replace(["\r", "\n"], ' ', $s);
    return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
}

/** Cliente SMTP mínimo con SSL/TLS y AUTH LOGIN. */
function smtp_send(array $cfg, string $from, string $to, string $data): array
{
    $port = (int)$cfg['smtp_port'];
    $host = (string)$cfg['smtp_host'];
    $implicit = strtolower((string)$cfg['smtp_secure']) === 'ssl';
    $remote = ($implicit ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true, 'peer_name' => $host]]);
    $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        return [false, "No se pudo conectar con $host:$port ($errstr)."];
    }
    stream_set_timeout($fp, 20);

    $read = function () use ($fp): string {
        $out = '';
        while (($line = fgets($fp, 515)) !== false) {
            $out .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return $out;
    };
    $cmd = function (string $c, array $okCodes) use ($fp, $read): string {
        if ($c !== '') {
            fwrite($fp, $c . "\r\n");
        }
        $r = $read();
        if (!in_array((int)substr($r, 0, 3), $okCodes, true)) {
            throw new RuntimeException(trim($r) ?: 'Sin respuesta del servidor de correo.');
        }
        return $r;
    };

    try {
        $cmd('', [220]);
        $me = gethostname() ?: 'localhost';
        $cmd("EHLO $me", [250]);
        if (!$implicit) {
            $cmd('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                throw new RuntimeException('No se pudo iniciar la conexión segura (TLS).');
            }
            $cmd("EHLO $me", [250]);
        }
        $cmd('AUTH LOGIN', [334]);
        $cmd(base64_encode((string)$cfg['smtp_user']), [334]);
        try {
            $cmd(base64_encode(str_replace(' ', '', (string)$cfg['smtp_pass'])), [235]);
        } catch (RuntimeException $e) {
            throw new RuntimeException('Gmail rechazó el usuario o la contraseña de aplicación. Revisa config/config.php. (' . $e->getMessage() . ')');
        }
        $cmd("MAIL FROM:<$from>", [250]);
        $cmd("RCPT TO:<$to>", [250, 251]);
        $cmd('DATA', [354]);
        $data = preg_replace('/^\./m', '..', str_replace(["\r\n", "\n"], ["\n", "\r\n"], $data));
        $cmd($data . "\r\n.", [250]);
        $cmd('QUIT', [221]);
        fclose($fp);
        return [true, 'Enviado por ' . $host . '.'];
    } catch (Throwable $e) {
        @fwrite($fp, "QUIT\r\n");
        @fclose($fp);
        return [false, $e->getMessage()];
    }
}

/** Plantilla HTML de los avisos. */
function mail_template(string $title, array $rows, string $intro = '', ?string $buttonUrl = null, string $buttonText = 'Abrir el panel'): string
{
    $pri = safe_color(setting('color_primary'), '#1C3762');
    $acc = safe_color(setting('color_accent'), '#F5C15C');
    $bg  = safe_color(setting('color_background'), '#F0F8FB');
    $site = e(setting('site_name', 'Somos Mascoteros'));
    $tr = '';
    foreach ($rows as $label => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $tr .= '<tr><td style="padding:10px 14px;border-bottom:1px solid #e6eef3;color:#6b7c93;font-size:13px;white-space:nowrap;vertical-align:top">'
            . e($label) . '</td><td style="padding:10px 14px;border-bottom:1px solid #e6eef3;color:' . $pri . ';font-size:15px;font-weight:600">'
            . nl2br(e((string)$value)) . '</td></tr>';
    }
    $btn = $buttonUrl ? '<p style="text-align:center;margin:26px 0 6px"><a href="' . e($buttonUrl) . '" style="background:' . $acc . ';color:' . $pri
        . ';text-decoration:none;font-weight:700;padding:12px 24px;border-radius:999px;display:inline-block">' . e($buttonText) . '</a></p>' : '';
    return '<!doctype html><html><body style="margin:0;background:' . $bg . ';font-family:Arial,Helvetica,sans-serif;padding:24px 12px">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#fff;border-radius:18px;overflow:hidden">'
        . '<tr><td style="background:' . $pri . ';padding:22px 26px;color:#fff;font-size:20px;font-weight:700">🐾 ' . $site . '</td></tr>'
        . '<tr><td style="padding:24px 26px"><h1 style="margin:0 0 8px;font-size:21px;color:' . $pri . '">' . e($title) . '</h1>'
        . ($intro ? '<p style="margin:0 0 18px;color:#4a5b73;font-size:15px;line-height:1.5">' . e($intro) . '</p>' : '')
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e6eef3;border-radius:12px;border-collapse:separate">' . $tr . '</table>'
        . $btn . '</td></tr>'
        . '<tr><td style="padding:14px 26px;background:#f7fafc;color:#8a9ab0;font-size:12px;text-align:center">Aviso automático de ' . $site . '. Puedes responder este correo para contestarle directamente al cliente.</td></tr>'
        . '</table></body></html>';
}

/** Dirección base del sitio (https://dominio/). */
function site_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    $dir = rtrim(str_replace('\\', '/', dirname(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')))), '/');
    return ($https ? 'https' : 'http') . '://' . $host . $dir . '/';
}

/** Envía un aviso si están activados en la configuración. No interrumpe nunca la operación principal. */
function notify_admin(string $type, string $subject, string $html, ?string $replyTo = null, ?string $replyName = null): void
{
    try {
        $to = trim(setting('notify_email'));
        $flag = $type === 'booking' ? 'notify_bookings' : 'notify_messages';
        if ($to === '' || setting($flag, '1') !== '1') {
            return;
        }
        [$ok, $detail] = send_mail($to, $subject, $html, $replyTo, $replyName);
        if (!$ok) {
            error_log('[Somos Mascoteros] No se pudo enviar el aviso por correo: ' . $detail);
        }
    } catch (Throwable $e) {
        error_log('[Somos Mascoteros] Error al enviar aviso: ' . $e->getMessage());
    }
}

/** Responde al visitante de inmediato y luego ejecuta $after (por ejemplo, enviar el aviso). */
function respond_then(array $data, callable $after): void
{
    ignore_user_abort(true);
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } elseif (function_exists('litespeed_finish_request')) {
        litespeed_finish_request();
    } else {
        @ob_flush();
        @flush();
    }
    $after();
    exit;
}

/** Fecha legible en español: "lun 24 nov 2026". */
function fecha_es(string $ymd): string
{
    $d = ['lun', 'mar', 'mié', 'jue', 'vie', 'sáb', 'dom'];
    $m = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $t = strtotime($ymd);
    return $d[(int)date('N', $t) - 1] . ' ' . date('j', $t) . ' ' . $m[(int)date('n', $t) - 1] . ' ' . date('Y', $t);
}
