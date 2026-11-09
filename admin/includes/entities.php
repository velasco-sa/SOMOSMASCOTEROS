<?php
/**
 * Definición de los módulos administrables (motor CRUD genérico).
 * Cada entidad describe su tabla y sus campos; el panel genera
 * automáticamente la tabla, los filtros, el formulario y las validaciones.
 *
 * Tipos de campo: text, slug, textarea, richtext, number, decimal, email, url, link,
 * date, time, color, bool, select, enum, image, icon, password, datetime (solo lectura).
 */
declare(strict_types=1);

$SECTION_LABEL_SQL = "SELECT s.id, CONCAT(p.title, ' › ', COALESCE(NULLIF(s.title, ''), s.anchor)) AS label, t.icon
                      FROM sections s JOIN pages p ON p.id = s.page_id JOIN section_types t ON t.id = s.section_type_id
                      ORDER BY p.sort_order, s.sort_order, s.id";

return [

    // ------------------------------------------------------------------ CONTENIDO
    'pages' => [
        'table' => 'pages', 'singular' => 'página', 'order' => 'sort_order, id', 'search' => ['title', 'slug'],
        'intro' => 'Crea, edita o elimina las páginas del sitio. La página marcada como "inicio" es la que se muestra al entrar.',
        'fields' => [
            'title'            => ['label' => 'Título', 'type' => 'text', 'required' => true, 'max' => 120, 'list' => true],
            'slug'             => ['label' => 'Dirección (URL)', 'type' => 'slug', 'required' => true, 'max' => 80, 'list' => true, 'from' => 'title', 'help' => 'Solo minúsculas, números y guiones. Ej: politicas → index.php?p=politicas'],
            'meta_description' => ['label' => 'Descripción (buscadores y subtítulo)', 'type' => 'textarea', 'max' => 255, 'full' => true],
            'is_home'          => ['label' => 'Es la página de inicio', 'type' => 'bool', 'list' => true],
            'is_published'     => ['label' => 'Publicada', 'type' => 'bool', 'default' => 1, 'list' => true],
            'show_in_menu'     => ['label' => 'Mostrar en el menú', 'type' => 'bool', 'list' => true],
            'sort_order'       => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
            'updated_at'       => ['label' => 'Actualizada', 'type' => 'datetime', 'readonly' => true, 'list' => true],
        ],
        'row_links' => [
            ['label' => 'Secciones', 'icon' => 'fa-solid fa-layer-group', 'entity' => 'sections', 'param' => 'page_id'],
            ['label' => 'Ver página', 'icon' => 'fa-solid fa-eye', 'url' => '../index.php?p={slug}'],
        ],
    ],

    'sections' => [
        'table' => 'sections', 'singular' => 'sección', 'order' => 'page_id, sort_order, id', 'search' => ['title', 'anchor', 'menu_label', 'eyebrow'],
        'intro' => 'Cada sección es un bloque de contenido dentro de una página (quiénes somos, servicios, galería...). El "tipo" define su diseño.',
        'fields' => [
            'page_id'         => ['label' => 'Página', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true,
                                  'ref' => ['table' => 'pages', 'label' => 'title', 'order' => 'sort_order, id']],
            'section_type_id' => ['label' => 'Tipo de sección (diseño)', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true,
                                  'ref' => ['table' => 'section_types', 'label' => 'name', 'icon' => 'icon', 'desc' => 'description', 'order' => 'id']],
            'title'           => ['label' => 'Título', 'type' => 'text', 'max' => 160, 'list' => true],
            'eyebrow'         => ['label' => 'Etiqueta superior', 'type' => 'text', 'max' => 80, 'help' => 'Texto pequeño que aparece arriba del título'],
            'subtitle'        => ['label' => 'Subtítulo', 'type' => 'text', 'max' => 255, 'full' => true],
            'body'            => ['label' => 'Texto principal', 'type' => 'richtext', 'full' => true],
            'media_id'        => ['label' => 'Imagen', 'type' => 'image'],
            'anchor'          => ['label' => 'Ancla (identificador)', 'type' => 'slug', 'required' => true, 'max' => 80, 'from' => 'title', 'help' => 'Se usa en el menú: index.php#ancla'],
            'menu_label'      => ['label' => 'Texto en el menú', 'type' => 'text', 'max' => 60],
            'show_in_menu'    => ['label' => 'Mostrar en el menú', 'type' => 'bool', 'list' => true],
            'is_visible'      => ['label' => 'Visible', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order'      => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
        'unique' => [['page_id', 'anchor', 'Ya existe una sección con esa ancla en la página.']],
        'row_links' => [
            ['label' => 'Elementos', 'icon' => 'fa-solid fa-table-cells-large', 'entity' => 'section_items', 'param' => 'section_id'],
        ],
    ],

    'section_items' => [
        'table' => 'section_items', 'singular' => 'elemento', 'order' => 'section_id, sort_order, id', 'search' => ['title', 'description', 'highlight'],
        'intro' => 'Tarjetas, cifras, pasos, botones, testimonios o preguntas que pertenecen a una sección. El uso de cada campo depende del tipo de sección.',
        'fields' => [
            'section_id'  => ['label' => 'Sección', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true, 'ref' => ['sql' => $SECTION_LABEL_SQL]],
            'title'       => ['label' => 'Título / nombre', 'type' => 'text', 'required' => true, 'max' => 160, 'list' => true],
            'icon'        => ['label' => 'Ícono', 'type' => 'icon', 'list' => true],
            'highlight'   => ['label' => 'Dato destacado', 'type' => 'text', 'max' => 80, 'list' => true, 'help' => 'Precio, año, cifra, horario o mascota (según la sección)'],
            'description' => ['label' => 'Descripción', 'type' => 'textarea', 'full' => true],
            'link_url'    => ['label' => 'Enlace (convierte el elemento en botón)', 'type' => 'link', 'max' => 255, 'help' => 'Ej: #contacto, index.php?p=politicas o https://...'],
            'media_id'    => ['label' => 'Imagen', 'type' => 'image'],
            'is_visible'  => ['label' => 'Visible', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order'  => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    'section_types' => [
        'table' => 'section_types', 'singular' => 'tipo de sección', 'order' => 'id', 'search' => ['name', 'code'],
        'intro' => 'Plantillas visuales disponibles. Puedes cambiar nombres, descripciones e íconos. El código debe coincidir con una plantilla existente.',
        'can_create' => false, 'can_delete' => false,
        'fields' => [
            'name'        => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 80, 'list' => true],
            'code'        => ['label' => 'Código de plantilla', 'type' => 'text', 'readonly' => true, 'list' => true],
            'icon'        => ['label' => 'Ícono', 'type' => 'icon', 'list' => true],
            'description' => ['label' => 'Descripción', 'type' => 'textarea', 'max' => 255, 'full' => true, 'list' => true],
        ],
    ],

    // ------------------------------------------------------------------ GALERÍA
    'gallery_categories' => [
        'table' => 'gallery_categories', 'singular' => 'categoría', 'order' => 'sort_order, name', 'search' => ['name'],
        'fields' => [
            'name'       => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true, 'unique' => true],
            'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
        'row_links' => [['label' => 'Fotos', 'icon' => 'fa-solid fa-images', 'entity' => 'gallery_photos', 'param' => 'gallery_category_id']],
    ],

    'gallery_photos' => [
        'table' => 'gallery_photos', 'singular' => 'foto', 'order' => 'sort_order, id', 'search' => ['caption'], 'view' => 'grid',
        'fields' => [
            'media_id'            => ['label' => 'Imagen', 'type' => 'image', 'required' => true, 'list' => true],
            'gallery_category_id' => ['label' => 'Categoría', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true,
                                      'ref' => ['table' => 'gallery_categories', 'label' => 'name', 'order' => 'sort_order, name']],
            'caption'             => ['label' => 'Descripción', 'type' => 'text', 'max' => 160, 'list' => true],
            'is_visible'          => ['label' => 'Visible', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order'          => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    // ------------------------------------------------------------------ RESERVAS
    'bookings' => [
        'table' => 'bookings', 'singular' => 'reserva', 'order' => 'check_in DESC, id DESC', 'search' => ['pet_name', 'notes'],
        'intro' => 'Las solicitudes hechas desde el sitio llegan como "Pendiente". Confírmalas, finalízalas o cancélalas aquí.',
        'fields' => [
            'customer_id'       => ['label' => 'Cliente', 'type' => 'select', 'required' => true, 'list' => true, 'ref' => ['table' => 'customers', 'label' => 'full_name', 'order' => 'full_name']],
            'pet_type_id'       => ['label' => 'Tipo de mascota', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true, 'ref' => ['table' => 'pet_types', 'label' => 'name', 'icon' => 'icon', 'order' => 'sort_order']],
            'pet_name'          => ['label' => 'Nombre de la mascota', 'type' => 'text', 'required' => true, 'max' => 80, 'list' => true],
            'pets_count'        => ['label' => 'Cantidad de mascotas', 'type' => 'number', 'required' => true, 'default' => 1, 'min' => 1, 'max_value' => 50],
            'check_in'          => ['label' => 'Llegada', 'type' => 'date', 'required' => true, 'list' => true],
            'check_out'         => ['label' => 'Salida', 'type' => 'date', 'required' => true, 'list' => true],
            'booking_status_id' => ['label' => 'Estado', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true, 'ref' => ['table' => 'booking_statuses', 'label' => 'name', 'color' => 'color', 'order' => 'sort_order']],
            'notes'             => ['label' => 'Notas', 'type' => 'textarea', 'full' => true],
            'created_at'        => ['label' => 'Registrada', 'type' => 'datetime', 'readonly' => true],
        ],
    ],

    'customers' => [
        'table' => 'customers', 'singular' => 'cliente', 'order' => 'full_name', 'search' => ['full_name', 'email', 'phone'],
        'fields' => [
            'full_name'  => ['label' => 'Nombre completo', 'type' => 'text', 'required' => true, 'max' => 120, 'list' => true],
            'email'      => ['label' => 'Correo', 'type' => 'email', 'required' => true, 'max' => 160, 'list' => true, 'unique' => true],
            'phone'      => ['label' => 'Teléfono', 'type' => 'text', 'max' => 30, 'list' => true],
            'created_at' => ['label' => 'Registrado', 'type' => 'datetime', 'readonly' => true, 'list' => true],
        ],
        'row_links' => [['label' => 'Reservas', 'icon' => 'fa-solid fa-calendar-check', 'entity' => 'bookings', 'param' => 'customer_id']],
    ],

    'pet_types' => [
        'table' => 'pet_types', 'singular' => 'tipo de mascota', 'order' => 'sort_order, name', 'search' => ['name'],
        'fields' => [
            'name'       => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true, 'unique' => true],
            'icon'       => ['label' => 'Ícono', 'type' => 'icon', 'list' => true],
            'is_active'  => ['label' => 'Se recibe actualmente', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    'accommodations' => [
        'table' => 'accommodations', 'singular' => 'alojamiento', 'order' => 'sort_order, id', 'search' => ['name', 'description'],
        'intro' => 'Espacios donde duermen las mascotas. La suma de capacidades activas por tipo de mascota define la disponibilidad del simulador.',
        'fields' => [
            'name'        => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 100, 'list' => true],
            'pet_type_id' => ['label' => 'Tipo de mascota', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true, 'ref' => ['table' => 'pet_types', 'label' => 'name', 'icon' => 'icon', 'order' => 'sort_order']],
            'capacity'    => ['label' => 'Capacidad (mascotas por noche)', 'type' => 'number', 'required' => true, 'default' => 1, 'min' => 0, 'max_value' => 500, 'list' => true],
            'media_id'    => ['label' => 'Imagen', 'type' => 'image', 'list' => true],
            'description' => ['label' => 'Descripción', 'type' => 'textarea', 'full' => true],
            'is_active'   => ['label' => 'Activo', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order'  => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    'booking_statuses' => [
        'table' => 'booking_statuses', 'singular' => 'estado', 'order' => 'sort_order, id', 'search' => ['name'],
        'fields' => [
            'name'            => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true],
            'code'            => ['label' => 'Código interno', 'type' => 'slug', 'required' => true, 'max' => 30, 'list' => true, 'unique' => true, 'from' => 'name', 'help' => '"pending" es el estado que reciben las solicitudes de la web'],
            'color'           => ['label' => 'Color', 'type' => 'color', 'default' => '#9ACCCD', 'list' => true],
            'blocks_capacity' => ['label' => 'Ocupa lugar en el calendario', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order'      => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    'closed_dates' => [
        'table' => 'closed_dates', 'singular' => 'cierre', 'order' => 'date_from DESC', 'search' => ['reason'],
        'intro' => 'En estas fechas el simulador mostrará "Cerrado" y no se aceptarán solicitudes.',
        'fields' => [
            'date_from' => ['label' => 'Desde', 'type' => 'date', 'required' => true, 'list' => true],
            'date_to'   => ['label' => 'Hasta', 'type' => 'date', 'required' => true, 'list' => true],
            'reason'    => ['label' => 'Motivo', 'type' => 'text', 'required' => true, 'max' => 160, 'list' => true, 'full' => true],
        ],
    ],

    // ------------------------------------------------------------------ CONTACTO
    'contact_messages' => [
        'table' => 'contact_messages', 'singular' => 'mensaje', 'order' => 'is_read, created_at DESC', 'search' => ['full_name', 'email', 'subject', 'message'],
        'can_create' => false,
        'fields' => [
            'full_name'  => ['label' => 'Nombre', 'type' => 'text', 'readonly' => true, 'list' => true],
            'email'      => ['label' => 'Correo', 'type' => 'email', 'readonly' => true, 'list' => true],
            'phone'      => ['label' => 'Teléfono', 'type' => 'text', 'readonly' => true],
            'subject'    => ['label' => 'Asunto', 'type' => 'text', 'readonly' => true, 'list' => true],
            'message'    => ['label' => 'Mensaje', 'type' => 'textarea', 'readonly' => true, 'full' => true],
            'is_read'    => ['label' => 'Leído', 'type' => 'bool', 'list' => true, 'filter' => true],
            'created_at' => ['label' => 'Recibido', 'type' => 'datetime', 'readonly' => true, 'list' => true],
        ],
    ],

    'contact_channels' => [
        'table' => 'contact_channels', 'singular' => 'medio de contacto', 'order' => 'sort_order, id', 'search' => ['label', 'value'],
        'intro' => 'Para WhatsApp escribe el número con código de país y sin espacios (ej: 5215512345678).',
        'fields' => [
            'contact_type_id' => ['label' => 'Tipo', 'type' => 'select', 'required' => true, 'list' => true, 'filter' => true, 'ref' => ['table' => 'contact_types', 'label' => 'name', 'icon' => 'icon', 'order' => 'name']],
            'label'           => ['label' => 'Etiqueta', 'type' => 'text', 'required' => true, 'max' => 80, 'list' => true],
            'value'           => ['label' => 'Valor (número, correo...)', 'type' => 'text', 'required' => true, 'max' => 160, 'list' => true],
            'is_visible'      => ['label' => 'Visible', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order'      => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    'contact_types' => [
        'table' => 'contact_types', 'singular' => 'tipo de contacto', 'order' => 'name', 'search' => ['name'],
        'fields' => [
            'name'       => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true, 'unique' => true],
            'icon'       => ['label' => 'Ícono', 'type' => 'icon', 'required' => true, 'list' => true],
            'url_prefix' => ['label' => 'Prefijo del enlace', 'type' => 'text', 'max' => 60, 'list' => true, 'help' => 'tel: para teléfono, mailto: para correo, https://wa.me/ para WhatsApp'],
        ],
    ],

    'social_links' => [
        'table' => 'social_links', 'singular' => 'red social', 'order' => 'sort_order, id', 'search' => ['url'],
        'fields' => [
            'social_platform_id' => ['label' => 'Plataforma', 'type' => 'select', 'required' => true, 'list' => true, 'ref' => ['table' => 'social_platforms', 'label' => 'name', 'icon' => 'icon', 'order' => 'name']],
            'url'                => ['label' => 'Enlace del perfil', 'type' => 'url', 'required' => true, 'max' => 255, 'list' => true, 'full' => true],
            'is_visible'         => ['label' => 'Visible', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order'         => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    'social_platforms' => [
        'table' => 'social_platforms', 'singular' => 'plataforma', 'order' => 'name', 'search' => ['name'],
        'fields' => [
            'name' => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true, 'unique' => true],
            'icon' => ['label' => 'Ícono', 'type' => 'icon', 'required' => true, 'list' => true],
        ],
    ],

    'locations' => [
        'table' => 'locations', 'singular' => 'ubicación', 'order' => 'sort_order, id', 'search' => ['name', 'address', 'city'],
        'intro' => 'Para obtener latitud y longitud: abre Google Maps, haz clic derecho sobre tu negocio y copia los números que aparecen.',
        'fields' => [
            'name'            => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 100, 'list' => true],
            'address'         => ['label' => 'Calle y número', 'type' => 'text', 'required' => true, 'max' => 200, 'list' => true, 'full' => true],
            'city'            => ['label' => 'Ciudad', 'type' => 'text', 'required' => true, 'max' => 80, 'list' => true],
            'state'           => ['label' => 'Estado / Provincia', 'type' => 'text', 'max' => 80],
            'postal_code'     => ['label' => 'Código postal', 'type' => 'text', 'max' => 15],
            'country'         => ['label' => 'País', 'type' => 'text', 'required' => true, 'max' => 80],
            'latitude'        => ['label' => 'Latitud', 'type' => 'decimal', 'min' => -90, 'max_value' => 90],
            'longitude'       => ['label' => 'Longitud', 'type' => 'decimal', 'min' => -180, 'max_value' => 180],
            'reference_notes' => ['label' => 'Referencias para llegar', 'type' => 'text', 'max' => 255, 'full' => true],
            'is_visible'      => ['label' => 'Visible', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order'      => ['label' => 'Orden', 'type' => 'number', 'default' => 0],
        ],
        'row_links' => [['label' => 'Horarios', 'icon' => 'fa-solid fa-clock', 'entity' => 'business_hours', 'param' => 'location_id']],
    ],

    'business_hours' => [
        'table' => 'business_hours', 'singular' => 'horario', 'order' => 'location_id, weekday_id', 'search' => [],
        'fields' => [
            'location_id' => ['label' => 'Ubicación', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true, 'ref' => ['table' => 'locations', 'label' => 'name', 'order' => 'sort_order']],
            'weekday_id'  => ['label' => 'Día', 'type' => 'select', 'required' => true, 'list' => true, 'ref' => ['table' => 'weekdays', 'label' => 'name', 'order' => 'id']],
            'is_closed'   => ['label' => 'Cerrado ese día', 'type' => 'bool', 'list' => true],
            'opens_at'    => ['label' => 'Abre', 'type' => 'time', 'list' => true],
            'closes_at'   => ['label' => 'Cierra', 'type' => 'time', 'list' => true],
        ],
        'unique' => [['location_id', 'weekday_id', 'Ese día ya tiene horario en esta ubicación.']],
    ],

    // ------------------------------------------------------------------ SEGURIDAD
    'users' => [
        'table' => 'users', 'singular' => 'usuario', 'order' => 'full_name', 'search' => ['full_name', 'email'],
        'intro' => 'Las contraseñas se guardan cifradas con hash seguro (bcrypt/argon2): nadie, ni siquiera el administrador, puede verlas.',
        'fields' => [
            'full_name'     => ['label' => 'Nombre completo', 'type' => 'text', 'required' => true, 'max' => 120, 'list' => true],
            'email'         => ['label' => 'Correo (usuario de acceso)', 'type' => 'email', 'required' => true, 'max' => 160, 'list' => true, 'unique' => true],
            'role_id'       => ['label' => 'Rol', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true, 'ref' => ['table' => 'roles', 'label' => 'name', 'order' => 'name']],
            'password'      => ['label' => 'Contraseña', 'type' => 'password', 'virtual' => true, 'help' => 'Mínimo 8 caracteres, con letras y números. Déjala vacía para no cambiarla.'],
            'is_active'     => ['label' => 'Activo', 'type' => 'bool', 'default' => 1, 'list' => true],
            'last_login_at' => ['label' => 'Último acceso', 'type' => 'datetime', 'readonly' => true, 'list' => true],
        ],
    ],

    'roles' => [
        'table' => 'roles', 'singular' => 'rol', 'order' => 'id', 'search' => ['name'],
        'intro' => 'Usa el botón "Permisos" para definir qué puede ver, crear, editar o eliminar cada rol en cada módulo.',
        'fields' => [
            'name'        => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true, 'unique' => true],
            'description' => ['label' => 'Descripción', 'type' => 'text', 'max' => 255, 'list' => true, 'full' => true],
            'is_system'   => ['label' => 'Rol protegido (acceso total)', 'type' => 'bool', 'readonly' => true, 'list' => true],
        ],
        'row_links' => [
            ['label' => 'Permisos', 'icon' => 'fa-solid fa-key', 'url' => 'permissions.php?role={id}'],
            ['label' => 'Usuarios', 'icon' => 'fa-solid fa-users', 'entity' => 'users', 'param' => 'role_id'],
        ],
    ],

    // ------------------------------------------------------------------ SISTEMA
    'modules' => [
        'table' => 'modules', 'singular' => 'módulo', 'order' => 'module_group_id, sort_order, id', 'search' => ['name', 'code'],
        'intro' => 'Controla el nombre, ícono, grupo y orden de cada módulo del menú lateral, o desactívalo.',
        'can_create' => false, 'can_delete' => false,
        'fields' => [
            'name'            => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 80, 'list' => true],
            'code'            => ['label' => 'Código', 'type' => 'text', 'readonly' => true, 'list' => true],
            'module_group_id' => ['label' => 'Grupo del menú', 'type' => 'select', 'required' => true, 'filter' => true, 'list' => true, 'ref' => ['table' => 'module_groups', 'label' => 'name', 'order' => 'sort_order']],
            'icon'            => ['label' => 'Ícono', 'type' => 'icon', 'required' => true, 'list' => true],
            'description'     => ['label' => 'Descripción', 'type' => 'text', 'max' => 255, 'full' => true],
            'is_active'       => ['label' => 'Activo', 'type' => 'bool', 'default' => 1, 'list' => true],
            'sort_order'      => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    'module_groups' => [
        'table' => 'module_groups', 'singular' => 'grupo', 'order' => 'sort_order, id', 'search' => ['name'],
        'fields' => [
            'name'       => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true, 'unique' => true],
            'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    'permissions' => [
        'table' => 'permissions', 'singular' => 'permiso', 'order' => 'id', 'search' => ['name'],
        'can_create' => false, 'can_delete' => false,
        'fields' => [
            'name'        => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true],
            'code'        => ['label' => 'Código', 'type' => 'text', 'readonly' => true, 'list' => true],
            'description' => ['label' => 'Descripción', 'type' => 'text', 'max' => 255, 'list' => true, 'full' => true],
        ],
    ],

    'setting_groups' => [
        'table' => 'setting_groups', 'singular' => 'grupo', 'order' => 'sort_order, id', 'search' => ['name'],
        'can_create' => false, 'can_delete' => false,
        'fields' => [
            'name'       => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true, 'unique' => true],
            'icon'       => ['label' => 'Ícono', 'type' => 'icon', 'required' => true, 'list' => true],
            'sort_order' => ['label' => 'Orden', 'type' => 'number', 'default' => 0, 'list' => true],
        ],
    ],

    'activity_logs' => [
        'table' => 'activity_logs', 'singular' => 'registro', 'order' => 'created_at DESC, id DESC', 'search' => [],
        'can_create' => false, 'can_edit' => false,
        'fields' => [
            'created_at'    => ['label' => 'Fecha', 'type' => 'datetime', 'readonly' => true, 'list' => true],
            'user_id'       => ['label' => 'Usuario', 'type' => 'select', 'filter' => true, 'list' => true, 'readonly' => true, 'ref' => ['table' => 'users', 'label' => 'full_name', 'order' => 'full_name']],
            'module_id'     => ['label' => 'Módulo', 'type' => 'select', 'filter' => true, 'list' => true, 'readonly' => true, 'ref' => ['table' => 'modules', 'label' => 'name', 'icon' => 'icon', 'order' => 'name']],
            'permission_id' => ['label' => 'Acción', 'type' => 'select', 'filter' => true, 'list' => true, 'readonly' => true, 'ref' => ['table' => 'permissions', 'label' => 'name', 'order' => 'id']],
            'record_id'     => ['label' => 'Registro #', 'type' => 'number', 'readonly' => true, 'list' => true],
        ],
    ],
];
