<?php
/**
 * Motor CRUD genérico del panel.
 */
declare(strict_types=1);

function entities(): array
{
    static $all = null;
    if ($all === null) {
        $all = require __DIR__ . '/entities.php';
    }
    return $all;
}

function entity(string $code): ?array
{
    $all = entities();
    if (!isset($all[$code]) || !preg_match('/^[a-z_]+$/', $code)) {
        return null;
    }
    $cfg = $all[$code];
    $cfg['code'] = $code;
    $cfg += ['can_create' => true, 'can_edit' => true, 'can_delete' => true, 'search' => [], 'row_links' => [], 'unique' => [], 'view' => 'table'];
    return $cfg;
}

/** Permisos efectivos del usuario sobre la entidad. */
function entity_perms(array $cfg): array
{
    return [
        'view'   => can($cfg['code'], 'view'),
        'create' => $cfg['can_create'] && can($cfg['code'], 'create'),
        'edit'   => $cfg['can_edit'] && can($cfg['code'], 'edit'),
        'delete' => $cfg['can_delete'] && can($cfg['code'], 'delete'),
    ];
}

/** Opciones de un campo de selección. */
function field_options(array $f): array
{
    $ref = $f['ref'] ?? null;
    if (!$ref) {
        return [];
    }
    if (!empty($ref['sql'])) {
        $rows = db_all($ref['sql']);
    } else {
        $cols = 'id, `' . $ref['label'] . '` AS label';
        foreach (['icon', 'color', 'desc'] as $k) {
            if (!empty($ref[$k])) {
                $cols .= ', `' . $ref[$k] . '` AS `' . $k . '`';
            }
        }
        $rows = db_all("SELECT $cols FROM `{$ref['table']}` ORDER BY " . ($ref['order'] ?? 'id'));
    }
    return array_map(fn($r) => array_filter([
        'id' => (int)$r['id'], 'label' => (string)$r['label'],
        'icon' => $r['icon'] ?? null, 'color' => $r['color'] ?? null, 'desc' => $r['desc'] ?? null,
    ], fn($v) => $v !== null), $rows);
}

/** Metadatos para el JavaScript del panel. */
function entity_meta(array $cfg): array
{
    $fields = [];
    foreach ($cfg['fields'] as $name => $f) {
        $m = [
            'name' => $name, 'label' => $f['label'], 'type' => $f['type'],
            'required' => !empty($f['required']), 'list' => !empty($f['list']), 'filter' => !empty($f['filter']),
            'readonly' => !empty($f['readonly']), 'full' => !empty($f['full']) || in_array($f['type'], ['richtext'], true),
            'help' => $f['help'] ?? null, 'max' => $f['max'] ?? null, 'default' => $f['default'] ?? null,
            'from' => $f['from'] ?? null, 'min' => $f['min'] ?? null, 'max_value' => $f['max_value'] ?? null,
        ];
        if ($f['type'] === 'select') {
            $m['options'] = field_options($f);
        }
        $fields[] = $m;
    }
    return [
        'code' => $cfg['code'], 'singular' => $cfg['singular'], 'intro' => $cfg['intro'] ?? null,
        'fields' => $fields, 'perms' => entity_perms($cfg), 'rowLinks' => $cfg['row_links'],
        'searchable' => !empty($cfg['search']), 'view' => $cfg['view'],
    ];
}

/** Lista paginada con búsqueda y filtros. */
function crud_list(array $cfg, array $in): array
{
    $t = $cfg['table'];
    $select = ['t.id'];
    $joins = [];
    $i = 0;
    foreach ($cfg['fields'] as $name => $f) {
        if (!empty($f['virtual'])) {
            continue;
        }
        $select[] = "t.`$name`";
        if ($f['type'] === 'image') {
            $i++;
            $joins[] = "LEFT JOIN media m$i ON m$i.id = t.`$name`";
            $select[] = "m$i.file_path AS `{$name}__path`";
        }
    }
    $where = [];
    $params = [];
    $q = trim((string)($in['q'] ?? ''));
    if ($q !== '' && $cfg['search']) {
        $or = [];
        foreach ($cfg['search'] as $col) {
            $or[] = "t.`$col` LIKE ?";
            $params[] = '%' . $q . '%';
        }
        $where[] = '(' . implode(' OR ', $or) . ')';
    }
    $filters = is_array($in['filter'] ?? null) ? $in['filter'] : [];
    foreach ($filters as $col => $val) {
        if ($val === '' || $val === null || !isset($cfg['fields'][$col])) {
            continue;
        }
        $f = $cfg['fields'][$col];
        if (!in_array($f['type'], ['select', 'bool'], true) && !preg_match('/_id$/', $col)) {
            continue;
        }
        $where[] = "t.`$col` = ?";
        $params[] = (int)$val;
    }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $total = (int)db_value("SELECT COUNT(*) FROM `$t` t $whereSql", $params);
    $per = max(5, min(100, (int)($in['per_page'] ?? 15)));
    $pages = max(1, (int)ceil($total / $per));
    $page = max(1, min($pages, (int)($in['page'] ?? 1)));
    $offset = ($page - 1) * $per;
    $order = implode(', ', array_map(fn($p) => 't.' . trim($p), explode(',', $cfg['order'])));
    $rows = db_all('SELECT ' . implode(', ', $select) . " FROM `$t` t " . implode(' ', $joins) . " $whereSql ORDER BY $order LIMIT $per OFFSET $offset", $params);
    return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $per];
}

function crud_get(array $cfg, int $id): ?array
{
    $t = $cfg['table'];
    $select = ['t.id'];
    $joins = [];
    $i = 0;
    foreach ($cfg['fields'] as $name => $f) {
        if (!empty($f['virtual'])) {
            continue;
        }
        $select[] = "t.`$name`";
        if ($f['type'] === 'image') {
            $i++;
            $joins[] = "LEFT JOIN media m$i ON m$i.id = t.`$name`";
            $select[] = "m$i.file_path AS `{$name}__path`";
        }
    }
    return db_one('SELECT ' . implode(', ', $select) . " FROM `$t` t " . implode(' ', $joins) . ' WHERE t.id = ?', [$id]);
}

/** Convierte un texto en identificador para URL. */
function slugify(string $s): string
{
    $s = mb_strtolower(trim($s));
    $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u', 'ç' => 'c']);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string)$s, '-');
}

class ValidationError extends RuntimeException
{
    public ?string $field;
    public function __construct(string $message, ?string $field = null)
    {
        parent::__construct($message);
        $this->field = $field;
    }
}

/** Valida y normaliza los datos enviados. */
function crud_normalize(array $cfg, array $in, ?array $current): array
{
    $data = [];
    $isNew = $current === null;
    foreach ($cfg['fields'] as $name => $f) {
        if (!empty($f['readonly']) || $f['type'] === 'datetime') {
            continue;
        }
        $label = $f['label'];
        $raw = $in[$name] ?? null;
        if (is_array($raw)) {
            $raw = null;
        }
        $raw = $raw === null ? null : trim((string)$raw);
        $empty = $raw === null || $raw === '';
        $type = $f['type'];

        if ($type === 'password') {
            if ($empty) {
                if ($isNew) {
                    throw new ValidationError('La contraseña es obligatoria para un usuario nuevo.', $name);
                }
                continue;
            }
            if (mb_strlen($raw) < 8 || !preg_match('/[A-Za-z]/', $raw) || !preg_match('/\d/', $raw)) {
                throw new ValidationError('La contraseña debe tener al menos 8 caracteres e incluir letras y números.', $name);
            }
            $data['password_hash'] = password_hash($raw, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT);
            continue;
        }
        if ($type === 'bool') {
            $data[$name] = in_array($raw, ['1', 'true', 'on'], true) ? 1 : 0;
            continue;
        }
        if ($empty) {
            if (!empty($f['required'])) {
                throw new ValidationError("El campo \"$label\" es obligatorio.", $name);
            }
            $data[$name] = in_array($type, ['number'], true) ? (int)($f['default'] ?? 0) : null;
            continue;
        }
        switch ($type) {
            case 'slug':
                $v = slugify($raw);
                if ($v === '') {
                    throw new ValidationError("El campo \"$label\" no es válido.", $name);
                }
                break;
            case 'richtext':
                $v = sanitize_html($raw);
                if ($v === '' && !empty($f['required'])) {
                    throw new ValidationError("El campo \"$label\" es obligatorio.", $name);
                }
                $v = $v === '' ? null : $v;
                break;
            case 'number':
                if (!preg_match('/^-?\d+$/', $raw)) {
                    throw new ValidationError("\"$label\" debe ser un número entero.", $name);
                }
                $v = (int)$raw;
                break;
            case 'decimal':
                $raw = str_replace(',', '.', $raw);
                if (!is_numeric($raw)) {
                    throw new ValidationError("\"$label\" debe ser un número.", $name);
                }
                $v = (float)$raw;
                break;
            case 'email':
                if (!filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                    throw new ValidationError("\"$label\" no es un correo válido.", $name);
                }
                $v = mb_strtolower($raw);
                break;
            case 'url':
                if (!filter_var($raw, FILTER_VALIDATE_URL) || !preg_match('~^https?://~i', $raw)) {
                    throw new ValidationError("\"$label\" debe ser un enlace completo que empiece con https://", $name);
                }
                $v = $raw;
                break;
            case 'link':
                if (preg_match('~^\s*(javascript|data|vbscript):~i', $raw)) {
                    throw new ValidationError("\"$label\" no es un enlace permitido.", $name);
                }
                $v = $raw;
                break;
            case 'date':
                if (!valid_date($raw)) {
                    throw new ValidationError("\"$label\" no es una fecha válida.", $name);
                }
                $v = $raw;
                break;
            case 'time':
                if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $raw)) {
                    throw new ValidationError("\"$label\" no es una hora válida.", $name);
                }
                $v = substr($raw, 0, 5);
                break;
            case 'color':
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $raw)) {
                    throw new ValidationError("\"$label\" debe ser un color válido.", $name);
                }
                $v = strtoupper($raw);
                break;
            case 'icon':
                if (!preg_match('/^fa-(solid|regular|brands) fa-[a-z0-9\-]+$/', $raw)) {
                    throw new ValidationError("\"$label\" no es un ícono válido.", $name);
                }
                $v = $raw;
                break;
            case 'select':
                $id = (int)$raw;
                $ids = array_column(field_options($f), 'id');
                if (!in_array($id, $ids, true)) {
                    throw new ValidationError("Selecciona una opción válida en \"$label\".", $name);
                }
                $v = $id;
                break;
            case 'image':
                $id = (int)$raw;
                if (!db_value('SELECT id FROM media WHERE id = ?', [$id])) {
                    throw new ValidationError("La imagen seleccionada en \"$label\" ya no existe.", $name);
                }
                $v = $id;
                break;
            default:
                $v = $raw;
        }
        if (is_string($v) && !empty($f['max']) && mb_strlen($v) > $f['max'] && $type !== 'richtext') {
            throw new ValidationError("\"$label\" admite máximo {$f['max']} caracteres.", $name);
        }
        if (is_int($v) || is_float($v)) {
            if (isset($f['min']) && $v < $f['min']) {
                throw new ValidationError("\"$label\" debe ser mayor o igual a {$f['min']}.", $name);
            }
            if (isset($f['max_value']) && $v > $f['max_value']) {
                throw new ValidationError("\"$label\" debe ser menor o igual a {$f['max_value']}.", $name);
            }
        }
        $data[$name] = $v;
    }
    return $data;
}

/** Reglas de negocio específicas por módulo (antes de guardar). */
function crud_rules(array $cfg, array &$data, ?array $current): void
{
    $id = $current ? (int)$current['id'] : 0;
    $me = current_user();

    // Unicidad
    foreach ($cfg['fields'] as $name => $f) {
        if (!empty($f['unique']) && array_key_exists($name, $data) && $data[$name] !== null) {
            if (db_value("SELECT id FROM `{$cfg['table']}` WHERE `$name` = ? AND id <> ?", [$data[$name], $id])) {
                throw new ValidationError("Ya existe un registro con ese valor en \"{$f['label']}\".", $name);
            }
        }
    }
    foreach ($cfg['unique'] as $u) {
        [$a, $b, $msg] = $u;
        $va = $data[$a] ?? ($current[$a] ?? null);
        $vb = $data[$b] ?? ($current[$b] ?? null);
        if (db_value("SELECT id FROM `{$cfg['table']}` WHERE `$a` = ? AND `$b` = ? AND id <> ?", [$va, $vb, $id])) {
            throw new ValidationError($msg, $b);
        }
    }

    switch ($cfg['code']) {
        case 'pages':
            if (db_value('SELECT id FROM pages WHERE slug = ? AND id <> ?', [$data['slug'], $id])) {
                throw new ValidationError('Ya existe una página con esa dirección.', 'slug');
            }
            if ($current && (int)$current['is_home'] === 1 && empty($data['is_home'])) {
                throw new ValidationError('Debe existir una página de inicio. Marca otra página como inicio en lugar de desmarcar esta.', 'is_home');
            }
            if (!empty($data['is_home'])) {
                $data['is_published'] = 1;
            }
            break;

        case 'bookings':
            if ($data['check_out'] <= $data['check_in']) {
                throw new ValidationError('La salida debe ser posterior a la llegada.', 'check_out');
            }
            $blocks = (int)db_value('SELECT blocks_capacity FROM booking_statuses WHERE id = ?', [$data['booking_status_id']]);
            if ($blocks) {
                $r = compute_availability((int)$data['pet_type_id'], $data['check_in'], $data['check_out'], (int)$data['pets_count'], $id ?: null);
                if (!$r['available']) {
                    $bad = array_values(array_filter($r['days'], fn($d) => !$d['ok']));
                    $first = $bad ? date('d/m/Y', strtotime($bad[0]['date'])) : '';
                    throw new ValidationError("No hay lugares suficientes (o está cerrado) a partir del $first. Cambia las fechas, la cantidad o usa un estado que no ocupe lugar.", 'check_in');
                }
            }
            break;

        case 'closed_dates':
            if ($data['date_to'] < $data['date_from']) {
                throw new ValidationError('La fecha final debe ser igual o posterior a la inicial.', 'date_to');
            }
            break;

        case 'business_hours':
            if (!empty($data['is_closed'])) {
                $data['opens_at'] = null;
                $data['closes_at'] = null;
            } elseif (!$data['opens_at'] || !$data['closes_at']) {
                throw new ValidationError('Indica la hora de apertura y de cierre, o marca el día como cerrado.', 'opens_at');
            } elseif ($data['closes_at'] <= $data['opens_at']) {
                throw new ValidationError('La hora de cierre debe ser posterior a la de apertura.', 'closes_at');
            }
            break;

        case 'users':
            if ($current && (int)$current['id'] === (int)$me['id']) {
                if (empty($data['is_active'])) {
                    throw new ValidationError('No puedes desactivar tu propio usuario.', 'is_active');
                }
                if ((int)$data['role_id'] !== (int)$current['role_id']) {
                    throw new ValidationError('No puedes cambiar tu propio rol. Pide a otro administrador que lo haga.', 'role_id');
                }
            }
            if ($current) {
                ensure_admin_remains((int)$current['id'], (int)$data['role_id'], (int)$data['is_active']);
            }
            break;
    }
}

/** Evita quedarse sin ningún super administrador activo. */
function ensure_admin_remains(int $userId, ?int $newRole, ?int $newActive): void
{
    $wasAdmin = (int)db_value('SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND r.is_system = 1 AND u.is_active = 1', [$userId]);
    if (!$wasAdmin) {
        return;
    }
    $staysAdmin = $newRole !== null && $newActive && (int)db_value('SELECT is_system FROM roles WHERE id = ?', [$newRole]);
    if ($staysAdmin) {
        return;
    }
    $others = (int)db_value('SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id <> ? AND r.is_system = 1 AND u.is_active = 1', [$userId]);
    if ($others === 0) {
        throw new ValidationError('Debe quedar al menos un Super administrador activo.');
    }
}

function crud_save(array $cfg, array $in, ?int $id): int
{
    $current = $id ? db_one("SELECT * FROM `{$cfg['table']}` WHERE id = ?", [$id]) : null;
    if ($id && !$current) {
        throw new ValidationError('El registro ya no existe.');
    }
    $data = crud_normalize($cfg, $in, $current);
    crud_rules($cfg, $data, $current);
    if (!$data) {
        return (int)$id;
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        if ($current) {
            $sets = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
            db_exec("UPDATE `{$cfg['table']}` SET $sets WHERE id = ?", [...array_values($data), $id]);
        } else {
            $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
            $ph = implode(', ', array_fill(0, count($data), '?'));
            db_exec("INSERT INTO `{$cfg['table']}` ($cols) VALUES ($ph)", array_values($data));
            $id = (int)$pdo->lastInsertId();
        }
        if ($cfg['code'] === 'pages' && !empty($data['is_home'])) {
            db_exec('UPDATE pages SET is_home = 0 WHERE id <> ?', [$id]);
        }
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() === '23000') {
            throw new ValidationError('Ya existe un registro con esos datos o hay una referencia inválida.');
        }
        throw $e;
    }
    log_activity($cfg['code'], $current ? 'edit' : 'create', $id);
    return (int)$id;
}

function crud_delete(array $cfg, int $id): void
{
    $row = db_one("SELECT * FROM `{$cfg['table']}` WHERE id = ?", [$id]);
    if (!$row) {
        throw new ValidationError('El registro ya no existe.');
    }
    $me = current_user();
    switch ($cfg['code']) {
        case 'pages':
            if ((int)$row['is_home'] === 1) {
                throw new ValidationError('No puedes eliminar la página de inicio. Primero marca otra página como inicio.');
            }
            break;
        case 'roles':
            if ((int)$row['is_system'] === 1) {
                throw new ValidationError('El rol de Super administrador está protegido y no se puede eliminar.');
            }
            if (db_value('SELECT COUNT(*) FROM users WHERE role_id = ?', [$id])) {
                throw new ValidationError('Este rol tiene usuarios asignados. Cámbiales el rol antes de eliminarlo.');
            }
            break;
        case 'users':
            if ($id === (int)$me['id']) {
                throw new ValidationError('No puedes eliminar tu propio usuario.');
            }
            ensure_admin_remains($id, null, 0);
            break;
    }
    try {
        db_exec("DELETE FROM `{$cfg['table']}` WHERE id = ?", [$id]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            throw new ValidationError('No se puede eliminar porque otros registros dependen de él. Elimina o cambia primero esos registros.');
        }
        throw $e;
    }
    log_activity($cfg['code'], 'delete', $id);
}
