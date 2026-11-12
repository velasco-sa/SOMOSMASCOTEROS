<?php
/**
 * API JSON del panel de administración.
 */
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/crud.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_out(['ok' => false, 'message' => 'Método no permitido.'], 405);
}
require_login();
if (!csrf_check()) {
    json_out(['ok' => false, 'message' => 'La sesión de seguridad expiró. Recarga la página.'], 419);
}

$action = (string)($_POST['action'] ?? '');

try {
    switch ($action) {

        // ------------------------------------------------------------ CRUD genérico
        case 'crud_list':
        case 'crud_get':
        case 'crud_save':
        case 'crud_delete':
        case 'crud_toggle':
            $cfg = entity((string)($_POST['entity'] ?? ''));
            if (!$cfg) {
                json_out(['ok' => false, 'message' => 'Módulo desconocido.'], 404);
            }
            $perms = entity_perms($cfg);
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'crud_list') {
                require_perm($cfg['code'], 'view');
                json_out(['ok' => true] + crud_list($cfg, $_POST));
            }
            if ($action === 'crud_get') {
                require_perm($cfg['code'], 'view');
                $row = crud_get($cfg, $id);
                if (!$row) {
                    json_out(['ok' => false, 'message' => 'El registro ya no existe.'], 404);
                }
                if ($cfg['code'] === 'contact_messages' && !(int)$row['is_read'] && can('contact_messages', 'edit')) {
                    db_exec('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [$id]);
                    $row['is_read'] = 1;
                }
                json_out(['ok' => true, 'row' => $row]);
            }
            if ($action === 'crud_save') {
                if (!$perms[$id ? 'edit' : 'create']) {
                    json_out(['ok' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
                }
                $data = $_POST['data'] ?? [];
                $newId = crud_save($cfg, is_array($data) ? $data : [], $id ?: null);
                json_out(['ok' => true, 'id' => $newId, 'message' => $id ? 'Los cambios se guardaron correctamente.' : 'El registro se creó correctamente.']);
            }
            if ($action === 'crud_toggle') {
                if (!$perms['edit']) {
                    json_out(['ok' => false, 'message' => 'No tienes permiso para editar.'], 403);
                }
                $field = (string)($_POST['field'] ?? '');
                $f = $cfg['fields'][$field] ?? null;
                if (!$f || $f['type'] !== 'bool' || !empty($f['readonly'])) {
                    json_out(['ok' => false, 'message' => 'Campo no válido.'], 422);
                }
                $row = crud_get($cfg, $id);
                if (!$row) {
                    json_out(['ok' => false, 'message' => 'El registro ya no existe.'], 404);
                }
                $row[$field] = (int)!(int)$row[$field];
                crud_save($cfg, array_map(fn($v) => $v === null ? '' : (string)$v, $row), $id);
                json_out(['ok' => true, 'value' => $row[$field], 'message' => 'Actualizado.']);
            }
            if ($action === 'crud_delete') {
                if (!$perms['delete']) {
                    json_out(['ok' => false, 'message' => 'No tienes permiso para eliminar.'], 403);
                }
                crud_delete($cfg, $id);
                json_out(['ok' => true, 'message' => 'El registro se eliminó.']);
            }
            break;

        // ------------------------------------------------------------ Medios
        case 'media_list':
            if (!can('media', 'view') && !can('media', 'create')) {
                // Permite elegir imágenes a quien edite contenido
                $any = false;
                foreach (user_permissions() as $p) {
                    if (!empty($p['edit']) || !empty($p['create'])) { $any = true; break; }
                }
                if (!$any) {
                    json_out(['ok' => false, 'message' => 'No tienes permiso para ver la biblioteca.'], 403);
                }
            }
            $q = trim((string)($_POST['q'] ?? ''));
            $per = 24;
            $page = max(1, (int)($_POST['page'] ?? 1));
            $where = $q !== '' ? 'WHERE original_name LIKE ? OR alt_text LIKE ?' : '';
            $params = $q !== '' ? ["%$q%", "%$q%"] : [];
            $total = (int)db_value("SELECT COUNT(*) FROM media $where", $params);
            $pages = max(1, (int)ceil($total / $per));
            $page = min($page, $pages);
            $rows = db_all("SELECT id, file_path, original_name, mime_type, size_bytes, alt_text, created_at FROM media $where ORDER BY id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $params);
            json_out(['ok' => true, 'rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages,
                      'perms' => ['create' => can('media', 'create'), 'edit' => can('media', 'edit'), 'delete' => can('media', 'delete')]]);

        case 'media_upload':
            if (!can('media', 'create')) {
                json_out(['ok' => false, 'message' => 'No tienes permiso para subir imágenes.'], 403);
            }
            $saved = [];
            $files = $_FILES['files'] ?? null;
            if (!$files || !is_array($files['name'])) {
                json_out(['ok' => false, 'message' => 'No se recibió ningún archivo.'], 422);
            }
            foreach ($files['name'] as $k => $name) {
                $saved[] = media_store([
                    'name' => $name, 'tmp_name' => $files['tmp_name'][$k], 'error' => $files['error'][$k], 'size' => $files['size'][$k],
                ]);
            }
            json_out(['ok' => true, 'items' => $saved, 'message' => count($saved) === 1 ? 'Imagen subida.' : count($saved) . ' imágenes subidas.']);

        case 'media_update':
            if (!can('media', 'edit')) {
                json_out(['ok' => false, 'message' => 'No tienes permiso para editar.'], 403);
            }
            $alt = mb_substr(trim((string)($_POST['alt_text'] ?? '')), 0, 255);
            db_exec('UPDATE media SET alt_text = ? WHERE id = ?', [$alt !== '' ? $alt : null, (int)$_POST['id']]);
            log_activity('media', 'edit', (int)$_POST['id']);
            json_out(['ok' => true, 'message' => 'Descripción actualizada.']);

        case 'media_delete':
            if (!can('media', 'delete')) {
                json_out(['ok' => false, 'message' => 'No tienes permiso para eliminar.'], 403);
            }
            $id = (int)($_POST['id'] ?? 0);
            $m = db_one('SELECT * FROM media WHERE id = ?', [$id]);
            if (!$m) {
                json_out(['ok' => false, 'message' => 'La imagen ya no existe.'], 404);
            }
            $inSettings = db_value('SELECT label FROM settings WHERE media_id = ? LIMIT 1', [$id]);
            if ($inSettings) {
                json_out(['ok' => false, 'message' => "Esta imagen se usa como \"$inSettings\" en la configuración. Cámbiala primero."], 409);
            }
            db_exec('DELETE FROM media WHERE id = ?', [$id]);
            $path = ROOT_PATH . '/' . $m['file_path'];
            if (str_starts_with($m['file_path'], 'uploads/media/') && is_file($path)) {
                @unlink($path);
            }
            log_activity('media', 'delete', $id);
            json_out(['ok' => true, 'message' => 'Imagen eliminada.']);

        // ------------------------------------------------------------ Configuración
        case 'settings_save':
            require_perm('settings', 'edit');
            $values = is_array($_POST['settings'] ?? null) ? $_POST['settings'] : [];
            $rows = db_all('SELECT setting_key, label, input_type FROM settings');
            foreach ($rows as $r) {
                $k = $r['setting_key'];
                if (!array_key_exists($k, $values)) {
                    continue;
                }
                $v = trim((string)$values[$k]);
                switch ($r['input_type']) {
                    case 'image':
                        $mid = (int)$v;
                        if ($mid && !db_value('SELECT id FROM media WHERE id = ?', [$mid])) {
                            throw new ValidationError("La imagen de \"{$r['label']}\" no existe.", $k);
                        }
                        db_exec('UPDATE settings SET media_id = ? WHERE setting_key = ?', [$mid ?: null, $k]);
                        continue 2;
                    case 'color':
                        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $v)) {
                            throw new ValidationError("\"{$r['label']}\" debe ser un color válido.", $k);
                        }
                        $v = strtoupper($v);
                        break;
                    case 'font':
                        if (!preg_match('/^[A-Za-z0-9 ]{2,40}$/', $v)) {
                            throw new ValidationError("\"{$r['label']}\" no es una fuente válida.", $k);
                        }
                        break;
                    case 'bool':
                        $v = $v === '1' ? '1' : '0';
                        break;
                    case 'number':
                        if (!preg_match('/^\d{1,6}$/', $v)) {
                            throw new ValidationError("\"{$r['label']}\" debe ser un número entero.", $k);
                        }
                        break;
                    case 'email':
                        if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                            throw new ValidationError("\"{$r['label']}\" no es un correo válido.", $k);
                        }
                        break;
                    case 'url':
                        if ($v !== '' && !preg_match('~^https?://~i', $v)) {
                            throw new ValidationError("\"{$r['label']}\" debe empezar con https://", $k);
                        }
                        break;
                }
                db_exec('UPDATE settings SET setting_value = ? WHERE setting_key = ?', [mb_substr($v, 0, 5000), $k]);
            }
            log_activity('settings', 'edit');
            json_out(['ok' => true, 'message' => 'La configuración se guardó correctamente.']);

        // ------------------------------------------------------------ Correo de prueba
        case 'mail_test':
            require_perm('settings', 'edit');
            require_once dirname(__DIR__) . '/includes/mailer.php';
            $to = trim(setting('notify_email'));
            if ($to === '') {
                throw new ValidationError('Primero escribe el correo que recibe los avisos y pulsa "Guardar configuración".', 'notify_email');
            }
            $smtp = mail_config()['smtp_user'] !== '';
            $html = mail_template('¡Los avisos funcionan! 🎉', [
                'Enviado a' => $to,
                'Método'    => $smtp ? 'Gmail (' . mail_config()['smtp_user'] . ')' : 'Servidor del hosting (mail de PHP)',
                'Fecha'     => date('d/m/Y H:i'),
            ], 'Este es un correo de prueba. Desde ahora recibirás aquí un aviso por cada solicitud de reserva y cada mensaje de contacto del sitio.');
            [$ok, $detail] = send_mail($to, '✅ Prueba de avisos · ' . setting('site_name', 'Somos Mascoteros'), $html);
            if (!$ok) {
                json_out(['ok' => false, 'message' => 'No se pudo enviar: ' . $detail], 502);
            }
            json_out(['ok' => true, 'message' => "Correo de prueba enviado a $to. " . ($smtp ? '' : 'Como no configuraste Gmail en config.php, revisa también la carpeta de spam.')]);

        // ------------------------------------------------------------ Permisos por rol
        case 'permissions_save':
            require_perm('roles', 'edit');
            $roleId = (int)($_POST['role_id'] ?? 0);
            $role = db_one('SELECT * FROM roles WHERE id = ?', [$roleId]);
            if (!$role) {
                json_out(['ok' => false, 'message' => 'El rol no existe.'], 404);
            }
            if ((int)$role['is_system'] === 1) {
                json_out(['ok' => false, 'message' => 'El Super administrador siempre tiene acceso total.'], 422);
            }
            $grants = is_array($_POST['grants'] ?? null) ? $_POST['grants'] : [];
            $validMods = array_map('intval', array_column(db_all('SELECT id FROM modules'), 'id'));
            $validPerms = array_map('intval', array_column(db_all('SELECT id FROM permissions'), 'id'));
            $pdo = db();
            $pdo->beginTransaction();
            db_exec('DELETE FROM role_module_permissions WHERE role_id = ?', [$roleId]);
            $ins = $pdo->prepare('INSERT INTO role_module_permissions (role_id, module_id, permission_id) VALUES (?,?,?)');
            $count = 0;
            foreach ($grants as $g) {
                [$m, $p] = array_map('intval', explode(':', (string)$g) + [0, 0]);
                if (in_array($m, $validMods, true) && in_array($p, $validPerms, true)) {
                    $ins->execute([$roleId, $m, $p]);
                    $count++;
                }
            }
            $pdo->commit();
            log_activity('roles', 'edit', $roleId);
            json_out(['ok' => true, 'message' => "Permisos guardados ($count asignados)."]);

        // ------------------------------------------------------------ Perfil
        case 'profile_save':
            $me = current_user();
            $name = trim((string)($_POST['full_name'] ?? ''));
            $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
            $current = (string)($_POST['current_password'] ?? '');
            $new = (string)($_POST['new_password'] ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');
            if ($name === '' || mb_strlen($name) > 120) throw new ValidationError('Escribe tu nombre.', 'full_name');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ValidationError('Escribe un correo válido.', 'email');
            if (db_value('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $me['id']])) throw new ValidationError('Ese correo ya está en uso.', 'email');
            $hash = db_value('SELECT password_hash FROM users WHERE id = ?', [$me['id']]);
            if (!password_verify($current, (string)$hash)) {
                throw new ValidationError('Tu contraseña actual no es correcta.', 'current_password');
            }
            $params = [$name, $email];
            $sql = 'UPDATE users SET full_name = ?, email = ?';
            if ($new !== '') {
                if (mb_strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
                    throw new ValidationError('La nueva contraseña debe tener al menos 8 caracteres con letras y números.', 'new_password');
                }
                if ($new !== $confirm) throw new ValidationError('La confirmación no coincide con la nueva contraseña.', 'confirm_password');
                $sql .= ', password_hash = ?';
                $params[] = password_hash($new, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT);
            }
            $params[] = $me['id'];
            db_exec($sql . ' WHERE id = ?', $params);
            if ($new !== '') {
                session_regenerate_id(true);
            }
            json_out(['ok' => true, 'message' => $new !== '' ? 'Perfil y contraseña actualizados.' : 'Perfil actualizado.']);

        default:
            json_out(['ok' => false, 'message' => 'Acción desconocida.'], 400);
    }
} catch (ValidationError $e) {
    if (db()->inTransaction()) db()->rollBack();
    json_out(['ok' => false, 'message' => $e->getMessage(), 'field' => $e->field], 422);
} catch (Throwable $e) {
    if (db()->inTransaction()) db()->rollBack();
    global $CONFIG;
    json_out(['ok' => false, 'message' => 'Ocurrió un error inesperado.' . ($CONFIG['debug'] ? ' ' . $e->getMessage() : '')], 500);
}

/**
 * Guarda una imagen subida de forma segura (valida tipo real, renombra y re-codifica).
 */
function media_store(array $file): array
{
    global $CONFIG;
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? 'La imagen "' . $file['name'] . '" es demasiado grande.' : 'No se pudo subir "' . $file['name'] . '".';
        throw new ValidationError($msg);
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new ValidationError('Archivo no válido.');
    }
    $max = (int)$CONFIG['max_upload_mb'] * 1024 * 1024;
    if ($file['size'] > $max) {
        throw new ValidationError('"' . $file['name'] . '" supera el máximo de ' . $CONFIG['max_upload_mb'] . ' MB.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? null;
    if (!$ext || @getimagesize($file['tmp_name']) === false) {
        throw new ValidationError('"' . $file['name'] . '" no es una imagen válida (usa JPG, PNG, WEBP o GIF).');
    }
    $dir = 'uploads/media/' . date('Y/m');
    if (!is_dir(ROOT_PATH . '/' . $dir) && !mkdir(ROOT_PATH . '/' . $dir, 0755, true)) {
        throw new ValidationError('No se pudo crear la carpeta de imágenes. Revisa los permisos de "uploads".');
    }
    $rel = $dir . '/' . bin2hex(random_bytes(12)) . '.' . $ext;
    $abs = ROOT_PATH . '/' . $rel;

    // Re-codificar con GD (elimina metadatos y código oculto) y limitar tamaño a 2000px
    $done = false;
    if ($ext !== 'gif' && function_exists('imagecreatefromstring')) {
        $img = @imagecreatefromstring((string)file_get_contents($file['tmp_name']));
        if ($img) {
            $w = imagesx($img); $h = imagesy($img);
            $scale = min(1, 2000 / max($w, $h));
            if ($scale < 1) {
                $nw = (int)round($w * $scale); $nh = (int)round($h * $scale);
                $dst = imagecreatetruecolor($nw, $nh);
                imagealphablending($dst, false); imagesavealpha($dst, true);
                imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
                imagedestroy($img);
                $img = $dst;
            } else {
                imagealphablending($img, false); imagesavealpha($img, true);
            }
            $done = match ($ext) {
                'jpg'  => imagejpeg($img, $abs, 85),
                'png'  => imagepng($img, $abs, 7),
                'webp' => function_exists('imagewebp') ? imagewebp($img, $abs, 85) : false,
                default => false,
            };
            imagedestroy($img);
        }
    }
    if (!$done && !move_uploaded_file($file['tmp_name'], $abs)) {
        throw new ValidationError('No se pudo guardar la imagen en el servidor.');
    }
    $alt = pathinfo($file['name'], PATHINFO_FILENAME);
    $alt = mb_substr(trim(preg_replace('/[_\-]+/', ' ', $alt)), 0, 255);
    db_exec(
        'INSERT INTO media (file_path, original_name, mime_type, size_bytes, alt_text, uploaded_by) VALUES (?,?,?,?,?,?)',
        [$rel, mb_substr($file['name'], 0, 255), $mime, (int)filesize($abs), $alt ?: null, current_user()['id']]
    );
    $id = (int)db()->lastInsertId();
    log_activity('media', 'create', $id);
    return ['id' => $id, 'file_path' => $rel, 'original_name' => $file['name'], 'alt_text' => $alt];
}
