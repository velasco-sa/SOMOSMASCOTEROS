-- =====================================================================
--  SOMOS MASCOTEROS · Datos modelo (puedes modificarlos desde el panel)
-- =====================================================================
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
--  Seguridad
-- ---------------------------------------------------------------------
INSERT INTO roles (id, name, description, is_system) VALUES
 (1, 'Super administrador', 'Acceso total al sistema. No puede eliminarse.', 1),
 (2, 'Editor de contenido', 'Edita textos, imágenes, galería, alojamientos y datos de contacto.', 0),
 (3, 'Recepción', 'Gestiona reservas, clientes y mensajes recibidos.', 0),
 (4, 'Solo lectura', 'Puede consultar la información sin modificarla.', 0);

INSERT INTO module_groups (id, name, sort_order) VALUES
 (1, 'General', 1), (2, 'Contenido del sitio', 2), (3, 'Galería y medios', 3),
 (4, 'Reservas y disponibilidad', 4), (5, 'Contacto y ubicación', 5), (6, 'Seguridad', 6), (7, 'Sistema', 7);

INSERT INTO modules (id, module_group_id, code, name, description, icon, sort_order) VALUES
 (1, 1, 'dashboard',          'Panel principal',          'Resumen general del sitio',                 'fa-solid fa-gauge-high', 1),
 (2, 1, 'settings',           'Configuración del sitio',  'Nombre, logo, colores, tipografías y SEO',  'fa-solid fa-sliders', 2),
 (3, 2, 'pages',              'Páginas',                  'Páginas públicas del sitio',                'fa-solid fa-file-lines', 1),
 (4, 2, 'sections',           'Secciones',                'Bloques de contenido de cada página',       'fa-solid fa-layer-group', 2),
 (5, 2, 'section_items',      'Elementos de sección',     'Tarjetas, cifras, pasos y listas',          'fa-solid fa-table-cells-large', 3),
 (6, 7, 'section_types',      'Tipos de sección',         'Plantillas visuales disponibles',           'fa-solid fa-shapes', 6),
 (7, 3, 'media',              'Biblioteca de medios',     'Imágenes subidas al sitio',                 'fa-solid fa-photo-film', 1),
 (8, 3, 'gallery_categories', 'Categorías de galería',    'Filtros de la galería',                     'fa-solid fa-tags', 2),
 (9, 3, 'gallery_photos',     'Fotos de galería',         'Fotos que se muestran en la galería',       'fa-solid fa-images', 3),
 (10, 4, 'bookings',          'Reservas',                 'Estancias registradas y solicitudes',       'fa-solid fa-calendar-check', 1),
 (11, 4, 'customers',         'Clientes',                 'Dueños de las mascotas',                    'fa-solid fa-user-group', 2),
 (12, 4, 'pet_types',         'Tipos de mascota',         'Especies que se reciben',                   'fa-solid fa-paw', 3),
 (13, 4, 'accommodations',    'Alojamientos',             'Dónde duermen las mascotas y su capacidad', 'fa-solid fa-bed', 4),
 (14, 4, 'booking_statuses',  'Estados de reserva',       'Pendiente, confirmada, cancelada...',       'fa-solid fa-flag', 5),
 (15, 4, 'closed_dates',      'Fechas cerradas',          'Días en que no se reciben mascotas',        'fa-solid fa-calendar-xmark', 6),
 (16, 5, 'contact_messages',  'Mensajes recibidos',       'Mensajes del formulario de contacto',       'fa-solid fa-envelope-open-text', 1),
 (17, 5, 'contact_channels',  'Medios de contacto',       'Teléfonos, correos y WhatsApp',             'fa-solid fa-address-book', 2),
 (18, 5, 'contact_types',     'Tipos de contacto',        'Catálogo de tipos de contacto',             'fa-solid fa-id-card', 3),
 (19, 5, 'social_links',      'Redes sociales',           'Enlaces a redes sociales',                  'fa-solid fa-share-nodes', 4),
 (20, 5, 'social_platforms',  'Plataformas sociales',     'Catálogo de redes sociales',                'fa-solid fa-hashtag', 5),
 (21, 5, 'locations',         'Ubicaciones',              'Dirección y mapa',                          'fa-solid fa-location-dot', 6),
 (22, 5, 'business_hours',    'Horarios',                 'Horario de atención por día',               'fa-solid fa-clock', 7),
 (23, 6, 'users',             'Usuarios',                 'Personas con acceso al panel',              'fa-solid fa-users', 1),
 (24, 6, 'roles',             'Roles',                    'Roles y sus permisos por módulo',           'fa-solid fa-user-shield', 2),
 (25, 7, 'modules',           'Módulos',                  'Módulos del panel de administración',       'fa-solid fa-puzzle-piece', 1),
 (26, 7, 'module_groups',     'Grupos de módulos',        'Agrupación del menú lateral',               'fa-solid fa-folder-tree', 2),
 (27, 7, 'permissions',       'Permisos',                 'Acciones que se pueden otorgar',            'fa-solid fa-key', 3),
 (28, 7, 'activity_logs',     'Bitácora',                 'Historial de cambios realizados',           'fa-solid fa-clock-rotate-left', 4),
 (29, 7, 'setting_groups',    'Grupos de configuración',  'Agrupación de la configuración',            'fa-solid fa-gears', 5);

INSERT INTO permissions (id, code, name, description) VALUES
 (1, 'view',   'Ver',      'Consultar la información del módulo'),
 (2, 'create', 'Crear',    'Agregar nuevos registros'),
 (3, 'edit',   'Editar',   'Modificar registros existentes'),
 (4, 'delete', 'Eliminar', 'Eliminar registros');

-- Super administrador: todo
INSERT INTO role_module_permissions (role_id, module_id, permission_id)
SELECT 1, m.id, p.id FROM modules m CROSS JOIN permissions p;

-- Editor de contenido
INSERT INTO role_module_permissions (role_id, module_id, permission_id)
SELECT 2, m.id, p.id FROM modules m CROSS JOIN permissions p
WHERE m.id IN (1,2,3,4,5,7,8,9,12,13,17,19,21,22) AND p.code IN ('view','create','edit');
INSERT INTO role_module_permissions (role_id, module_id, permission_id)
SELECT 2, m.id, 4 FROM modules m WHERE m.id IN (5,7,9);

-- Recepción
INSERT INTO role_module_permissions (role_id, module_id, permission_id)
SELECT 3, m.id, p.id FROM modules m CROSS JOIN permissions p
WHERE m.id IN (10,11) AND p.code IN ('view','create','edit');
INSERT INTO role_module_permissions (role_id, module_id, permission_id)
SELECT 3, m.id, 1 FROM modules m WHERE m.id IN (1,12,13,15,16);
INSERT INTO role_module_permissions (role_id, module_id, permission_id) VALUES (3, 16, 3);

-- Solo lectura
INSERT INTO role_module_permissions (role_id, module_id, permission_id)
SELECT 4, m.id, 1 FROM modules m WHERE m.module_group_id <> 7 AND m.id NOT IN (23,24);

-- Usuario administrador inicial
-- Correo: admin@somosmascoteros.com · Contraseña: Mascoteros2026!  (¡cámbiala al entrar!)
INSERT INTO users (id, role_id, full_name, email, password_hash) VALUES
 (1, 1, 'Administrador', 'admin@somosmascoteros.com', '$2y$12$9CLPUKR9.R9w2SSS4NwwZOqyotUZz2v6aG6qkCMk90gh3glFkezC.');

-- ---------------------------------------------------------------------
--  Medios (imágenes de ejemplo)
-- ---------------------------------------------------------------------
INSERT INTO media (id, file_path, original_name, mime_type, size_bytes, alt_text, uploaded_by) VALUES
 (1,  'uploads/seed/logo.svg',          'logo.svg',          'image/svg+xml', 0, 'Logo Somos Mascoteros', 1),
 (2,  'uploads/seed/hero.svg',          'hero.svg',          'image/svg+xml', 0, 'Mascotas felices en Somos Mascoteros', 1),
 (3,  'uploads/seed/nosotros.svg',      'nosotros.svg',      'image/svg+xml', 0, 'Nuestro equipo', 1),
 (4,  'uploads/seed/historia.svg',      'historia.svg',      'image/svg+xml', 0, 'Cómo empezó todo', 1),
 (5,  'uploads/seed/suite-canina.svg',  'suite-canina.svg',  'image/svg+xml', 0, 'Suite canina', 1),
 (6,  'uploads/seed/loft-pequenos.svg', 'loft-pequenos.svg', 'image/svg+xml', 0, 'Loft para razas pequeñas', 1),
 (7,  'uploads/seed/cuarto-felino.svg', 'cuarto-felino.svg', 'image/svg+xml', 0, 'Habitación felina', 1),
 (8,  'uploads/seed/juego.svg',         'juego.svg',         'image/svg+xml', 0, 'Juego en grupo', 1),
 (9,  'uploads/seed/paseo.svg',         'paseo.svg',         'image/svg+xml', 0, 'Paseos diarios', 1),
 (10, 'uploads/seed/agility.svg',       'agility.svg',       'image/svg+xml', 0, 'Circuito de agilidad', 1),
 (11, 'uploads/seed/siesta.svg',        'siesta.svg',        'image/svg+xml', 0, 'Hora de la siesta', 1),
 (12, 'uploads/seed/galeria-1.svg',     'galeria-1.svg',     'image/svg+xml', 0, 'Galería 1', 1),
 (13, 'uploads/seed/galeria-2.svg',     'galeria-2.svg',     'image/svg+xml', 0, 'Galería 2', 1),
 (14, 'uploads/seed/galeria-3.svg',     'galeria-3.svg',     'image/svg+xml', 0, 'Galería 3', 1),
 (15, 'uploads/seed/galeria-4.svg',     'galeria-4.svg',     'image/svg+xml', 0, 'Galería 4', 1),
 (16, 'uploads/seed/galeria-5.svg',     'galeria-5.svg',     'image/svg+xml', 0, 'Galería 5', 1),
 (17, 'uploads/seed/galeria-6.svg',     'galeria-6.svg',     'image/svg+xml', 0, 'Galería 6', 1),
 (18, 'uploads/seed/galeria-7.svg',     'galeria-7.svg',     'image/svg+xml', 0, 'Galería 7', 1),
 (19, 'uploads/seed/galeria-8.svg',     'galeria-8.svg',     'image/svg+xml', 0, 'Galería 8', 1),
 (20, 'uploads/seed/vision.svg',        'vision.svg',        'image/svg+xml', 0, 'Nuestra visión', 1);

-- ---------------------------------------------------------------------
--  Configuración general
-- ---------------------------------------------------------------------
INSERT INTO setting_groups (id, name, icon, sort_order) VALUES
 (1, 'Identidad', 'fa-solid fa-id-badge', 1),
 (2, 'Colores y tipografía', 'fa-solid fa-palette', 2),
 (3, 'Buscadores (SEO)', 'fa-solid fa-magnifying-glass', 3),
 (4, 'Pie de página', 'fa-solid fa-shoe-prints', 4),
 (5, 'Reservas', 'fa-solid fa-calendar-days', 5),
 (6, 'Contacto', 'fa-solid fa-comments', 6);

INSERT INTO settings (setting_group_id, setting_key, label, help_text, input_type, setting_value, media_id, sort_order) VALUES
 (1, 'site_name',      'Nombre del negocio', NULL, 'text', 'Somos Mascoteros', NULL, 1),
 (1, 'site_tagline',   'Eslogan', 'Se muestra junto al nombre y en el pie de página', 'text', 'Guardería y hotel para mascotas', NULL, 2),
 (1, 'logo',           'Logotipo', 'Se usa en el encabezado, pie de página, panel y pantalla de carga', 'image', NULL, 1, 3),
 (1, 'favicon',        'Ícono de pestaña (favicon)', 'Imagen cuadrada; idealmente PNG de 512×512', 'image', NULL, 1, 4),
 (1, 'show_name_in_header', 'Mostrar el nombre junto al logo', 'Desactívalo si tu logotipo ya incluye el nombre', 'bool', '1', NULL, 5),
 (2, 'color_background', 'Color de fondo', NULL, 'color', '#F0F8FB', NULL, 1),
 (2, 'color_primary',    'Color principal', NULL, 'color', '#1C3762', NULL, 2),
 (2, 'color_secondary',  'Color secundario', NULL, 'color', '#9ACCCD', NULL, 3),
 (2, 'color_accent',     'Color de acento', NULL, 'color', '#F5C15C', NULL, 4),
 (2, 'font_headings',    'Tipografía de títulos', 'Fuente de Google Fonts', 'font', 'Montserrat', NULL, 5),
 (2, 'font_body',        'Tipografía de textos', 'Fuente de Google Fonts', 'font', 'Raleway', NULL, 6),
 (3, 'meta_title',       'Título para buscadores', NULL, 'text', 'Somos Mascoteros · Guardería y hotel para mascotas', NULL, 1),
 (3, 'meta_description', 'Descripción para buscadores', 'Máximo ~160 caracteres', 'textarea', 'Guardería, hotel y cuidado profesional para perros y gatos. Instalaciones seguras, actividades diarias y mucho amor.', NULL, 2),
 (3, 'meta_keywords',    'Palabras clave', 'Separadas por comas', 'text', 'guardería de mascotas, hotel para perros, hotel para gatos, cuidado de mascotas', NULL, 3),
 (4, 'footer_about',     'Texto del pie de página', NULL, 'textarea', 'Cuidamos a tu mejor amigo como parte de nuestra familia. Juego, descanso y atención profesional, todos los días.', NULL, 1),
 (4, 'footer_copyright', 'Derechos reservados', NULL, 'text', 'Somos Mascoteros. Todos los derechos reservados.', NULL, 2),
 (4, 'footer_cta',       'Frase destacada del pie', NULL, 'text', '¿Listo para que tu peludo viva una aventura?', NULL, 3),
 (4, 'footer_cta_button','Texto del botón del pie', NULL, 'text', 'Consultar disponibilidad', NULL, 4),
 (4, 'footer_cta_link',  'Enlace del botón del pie', 'Ej: #disponibilidad o index.php?p=politicas', 'text', '#disponibilidad', NULL, 5),
 (5, 'booking_requests_enabled', 'Permitir solicitudes de reserva desde la web', NULL, 'bool', '1', NULL, 1),
 (5, 'booking_max_nights',       'Máximo de noches por estancia', NULL, 'number', '60', NULL, 2),
 (5, 'booking_min_advance_days', 'Días mínimos de anticipación', '0 = se puede reservar para hoy', 'number', '1', NULL, 3),
 (5, 'availability_ok_message',  'Mensaje cuando hay lugar', NULL, 'textarea', '¡Buenas noticias! Tenemos lugar para tu mascota en esas fechas.', NULL, 4),
 (5, 'availability_full_message','Mensaje cuando no hay lugar', NULL, 'textarea', 'Lo sentimos, no hay lugares disponibles en todas esas fechas. Prueba con otro rango o escríbenos.', NULL, 5),
 (5, 'booking_success_message',  'Mensaje al enviar una solicitud', NULL, 'textarea', '¡Recibimos tu solicitud! Te contactaremos muy pronto para confirmar la estancia.', NULL, 6),
 (6, 'nav_cta_label',           'Texto del botón de WhatsApp del menú', 'Déjalo vacío para ocultar el botón', 'text', 'Escríbenos', NULL, 0),
 (6, 'whatsapp_float',          'Mostrar botón flotante de WhatsApp', 'Usa el primer medio de contacto de tipo WhatsApp', 'bool', '1', NULL, 1),
 (6, 'whatsapp_float_message',  'Mensaje inicial de WhatsApp', NULL, 'text', '¡Hola! Me gustaría recibir información sobre la guardería.', NULL, 2),
 (6, 'contact_success_message', 'Mensaje al enviar el formulario de contacto', NULL, 'textarea', '¡Gracias por escribirnos! Te responderemos lo antes posible.', NULL, 3);

-- ---------------------------------------------------------------------
--  Páginas, tipos de sección y secciones
-- ---------------------------------------------------------------------
INSERT INTO pages (id, slug, title, meta_description, is_home, is_published, show_in_menu, sort_order) VALUES
 (1, 'inicio',    'Inicio', 'Guardería y hotel para mascotas.', 1, 1, 0, 1),
 (2, 'politicas', 'Políticas', 'Políticas de estancia y requisitos para hospedar a tu mascota.', 0, 1, 1, 2);

INSERT INTO section_types (id, code, name, description, icon) VALUES
 (1,  'hero',           'Portada',                'Bloque principal con imagen, título y botones (elementos con enlace = botones, sin enlace = insignias)', 'fa-solid fa-star'),
 (2,  'about',          'Quiénes somos',          'Texto con imagen y cifras destacadas (elementos: dato destacado + título)', 'fa-solid fa-heart'),
 (3,  'story',          'Historia / línea de tiempo', 'Texto con imagen y línea de tiempo (elementos: año + título + descripción)', 'fa-solid fa-timeline'),
 (4,  'vision',         'Misión y visión',        'Tarjetas de misión, visión, valores y metas', 'fa-solid fa-mountain-sun'),
 (5,  'services',       'Servicios',              'Tarjetas de servicios con ícono y precio', 'fa-solid fa-bell-concierge'),
 (6,  'accommodations', 'Dónde duermen',          'Muestra automáticamente los alojamientos registrados', 'fa-solid fa-bed'),
 (7,  'care',           'Cuidados',               'Lista de cuidados con íconos', 'fa-solid fa-hand-holding-heart'),
 (8,  'activities',     'Actividades',            'Tarjetas con imagen y horario', 'fa-solid fa-futbol'),
 (9,  'gallery',        'Galería',                'Muestra automáticamente las fotos de la galería con filtros', 'fa-solid fa-images'),
 (10, 'availability',   'Simulador de disponibilidad', 'Formulario para consultar fechas disponibles y solicitar reserva', 'fa-solid fa-calendar-check'),
 (11, 'testimonials',   'Testimonios',            'Opiniones de clientes (elementos: nombre + mascota + opinión)', 'fa-solid fa-quote-left'),
 (12, 'faq',            'Preguntas frecuentes',   'Acordeón de preguntas y respuestas', 'fa-solid fa-circle-question'),
 (13, 'contact',        'Contacto',               'Formulario, medios de contacto y redes sociales', 'fa-solid fa-envelope'),
 (14, 'location',       'Ubicación',              'Mapa, dirección y horarios', 'fa-solid fa-map-location-dot'),
 (15, 'text',           'Texto libre',            'Texto con formato y una imagen opcional', 'fa-solid fa-align-left'),
 (16, 'cta',            'Llamado a la acción',    'Banner llamativo con botones', 'fa-solid fa-bullhorn');

INSERT INTO sections (id, page_id, section_type_id, anchor, menu_label, eyebrow, title, subtitle, body, media_id, is_visible, show_in_menu, sort_order) VALUES
 (1, 1, 1, 'inicio', 'Inicio', 'Guardería · Hotel · Diversión',
  'Su segundo hogar, lleno de juego y amor',
  'Cuidamos a tu mascota como parte de nuestra familia mientras tú viajas, trabajas o descansas.',
  NULL, 2, 1, 0, 1),
 (2, 1, 2, 'nosotros', 'Nosotros', 'Quiénes somos',
  'Un equipo que vive y respira mascotas',
  'Somos una guardería creada por amantes de los animales.',
  '<p>En <strong>Somos Mascoteros</strong> creemos que cada mascota merece atención individual, espacios limpios y un día lleno de estímulos. Nuestro equipo está formado por cuidadores capacitados en primeros auxilios veterinarios, comportamiento animal y manejo responsable.</p><p>Trabajamos con grupos pequeños, rutinas claras y comunicación constante con cada familia: recibirás fotos y reportes durante la estancia de tu peludo.</p>',
  3, 1, 1, 2),
 (3, 1, 3, 'historia', 'Historia', 'Cómo nace el emprendimiento',
  'Todo empezó con una pregunta: ¿quién lo cuidará?',
  'De un problema personal nació un proyecto con propósito.',
  '<p>Cuando tuvimos que viajar y no encontramos un lugar en el que confiáramos plenamente, decidimos crearlo nosotros. Comenzamos cuidando a las mascotas de amigos y vecinos en casa; pronto la recomendación de boca en boca nos llevó a abrir nuestras propias instalaciones.</p><p>Hoy seguimos con la misma idea del primer día: que cada mascota se sienta en casa.</p>',
  4, 1, 0, 3),
 (4, 1, 5, 'servicios', 'Servicios', 'Nuestros servicios',
  'Todo lo que tu mascota necesita, en un solo lugar',
  'Elige el plan que mejor se adapte a tu rutina.', NULL, NULL, 1, 1, 4),
 (5, 1, 6, 'instalaciones', 'Instalaciones', 'Dónde duermen',
  'Espacios cómodos, seguros y climatizados',
  'Cada especie y tamaño tiene su propio espacio de descanso.',
  '<p>Nuestras áreas de descanso están separadas por especie y tamaño, con camas ortopédicas, ventilación natural, limpieza diaria y monitoreo por cámaras las 24 horas.</p>',
  NULL, 1, 1, 5),
 (6, 1, 7, 'cuidados', 'Cuidados', 'Qué cuidados reciben',
  'Atención profesional en cada detalle',
  'Nos encargamos de su bienestar físico y emocional.', NULL, NULL, 1, 0, 6),
 (7, 1, 8, 'actividades', 'Actividades', 'Qué actividades realizan',
  'Días llenos de juego, ejercicio y descanso',
  'Una rutina equilibrada para que regresen felices y cansados.', NULL, NULL, 1, 1, 7),
 (8, 1, 9, 'galeria', 'Galería', 'Galería',
  'Momentos mascoteros',
  'Un vistazo a la vida diaria de nuestros huéspedes.', NULL, NULL, 1, 1, 8),
 (9, 1, 10, 'disponibilidad', 'Disponibilidad', 'Simula tu estancia',
  '¿Tenemos lugar en tus fechas?',
  'Elige el tipo de mascota y el rango de fechas para consultar la disponibilidad al instante.', NULL, NULL, 1, 1, 9),
 (10, 1, 4, 'vision', 'Visión', 'A dónde queremos llegar',
  'Soñamos en grande por y para ellos',
  'Nuestro propósito guía cada decisión.',
  '<p>Queremos convertirnos en la red de guarderías para mascotas más confiable de la región, impulsando la tenencia responsable y la adopción.</p>',
  20, 1, 0, 10),
 (11, 1, 11, 'testimonios', 'Testimonios', 'Familias felices',
  'Lo que dicen de nosotros',
  NULL, NULL, NULL, 1, 0, 11),
 (12, 1, 12, 'preguntas', 'Preguntas', 'Preguntas frecuentes',
  'Resolvemos tus dudas',
  NULL, NULL, NULL, 1, 0, 12),
 (13, 1, 14, 'ubicacion', 'Ubicación', 'Dónde localizarnos',
  'Ven a conocernos',
  'Agenda una visita y conoce nuestras instalaciones.', NULL, NULL, 1, 0, 13),
 (14, 1, 13, 'contacto', 'Contacto', 'Contacto',
  'Hablemos de tu mascota',
  'Escríbenos y te respondemos a la brevedad.',
  '<p>¿Tienes dudas, quieres agendar una visita o cotizar una estancia? Envíanos un mensaje o contáctanos por el medio que prefieras.</p>',
  NULL, 1, 1, 14),
 (15, 2, 15, 'politicas', NULL, 'Políticas de estancia',
  'Requisitos y políticas',
  'Para la seguridad de todos nuestros huéspedes.',
  '<h3>Requisitos de ingreso</h3><ul><li>Cartilla de vacunación vigente (múltiple y antirrábica).</li><li>Desparasitación interna y externa de los últimos 3 meses.</li><li>Mascotas mayores de 4 meses.</li><li>Collar con placa de identificación.</li></ul><h3>Durante la estancia</h3><ul><li>Puedes traer su alimento habitual, cama o juguete favorito.</li><li>Enviamos reportes con fotos diariamente.</li><li>Ante cualquier síntoma, contactamos a la familia y a nuestro veterinario aliado.</li></ul><h3>Cancelaciones</h3><p>Las cancelaciones con más de 48 horas de anticipación no tienen costo.</p>',
  NULL, 1, 0, 1),
 (16, 2, 16, 'reserva', NULL, '¿Todo listo?',
  'Reserva hoy la próxima aventura de tu peludo',
  'Consulta disponibilidad en segundos.', NULL, NULL, 1, 0, 2);

INSERT INTO section_items (section_id, title, description, icon, highlight, link_url, media_id, sort_order) VALUES
 -- Portada
 (1, 'Consultar disponibilidad', NULL, 'fa-solid fa-calendar-check', NULL, '#disponibilidad', NULL, 1),
 (1, 'Conoce nuestros servicios', NULL, 'fa-solid fa-paw', NULL, '#servicios', NULL, 2),
 (1, 'Monitoreo 24/7', NULL, 'fa-solid fa-video', NULL, NULL, NULL, 3),
 (1, 'mascotas felices', NULL, 'fa-solid fa-heart', '+500', NULL, NULL, 4),
 -- Quiénes somos (cifras)
 (2, 'Años de experiencia', NULL, 'fa-solid fa-award', '5+', NULL, NULL, 1),
 (2, 'Familias confían en nosotros', NULL, 'fa-solid fa-house-chimney-user', '500+', NULL, NULL, 2),
 (2, 'Cuidadores certificados', NULL, 'fa-solid fa-user-nurse', '8', NULL, NULL, 3),
 (2, 'Supervisión', NULL, 'fa-solid fa-shield-dog', '24/7', NULL, NULL, 4),
 -- Historia (línea de tiempo)
 (3, 'La idea', 'Cuidábamos a las mascotas de amigos y vecinos en casa.', 'fa-solid fa-lightbulb', '2019', NULL, NULL, 1),
 (3, 'Primer local', 'Abrimos nuestro primer espacio con 6 lugares.', 'fa-solid fa-store', '2021', NULL, NULL, 2),
 (3, 'Crecimos', 'Ampliamos áreas de juego y sumamos el área felina.', 'fa-solid fa-seedling', '2023', NULL, NULL, 3),
 (3, 'Hoy', 'Más de 500 familias forman parte de la comunidad mascotera.', 'fa-solid fa-people-group', '2026', NULL, NULL, 4),
 -- Servicios
 (4, 'Guardería de día', 'Juego supervisado, socialización y descanso de lunes a sábado.', 'fa-solid fa-sun', 'Desde $250 / día', NULL, NULL, 1),
 (4, 'Hotel / Hospedaje', 'Estancias de una o varias noches con reportes diarios y fotos.', 'fa-solid fa-moon', 'Desde $450 / noche', NULL, NULL, 2),
 (4, 'Estética y baño', 'Baño, corte, limpieza de oídos y corte de uñas.', 'fa-solid fa-scissors', 'Desde $300', NULL, NULL, 3),
 (4, 'Paseos', 'Paseos individuales o grupales por zonas seguras.', 'fa-solid fa-person-walking', 'Desde $120', NULL, NULL, 4),
 (4, 'Adiestramiento básico', 'Obediencia y buenos hábitos con refuerzo positivo.', 'fa-solid fa-graduation-cap', 'Desde $1,800 / mes', NULL, NULL, 5),
 (4, 'Transporte', 'Recogemos y llevamos a tu mascota en un vehículo acondicionado.', 'fa-solid fa-van-shuttle', 'Cotiza', NULL, NULL, 6),
 -- Cuidados
 (6, 'Alimentación personalizada', 'Respetamos su dieta, horarios y porciones.', 'fa-solid fa-bowl-food', NULL, NULL, NULL, 1),
 (6, 'Medicación', 'Administramos medicamentos según indicaciones veterinarias.', 'fa-solid fa-pills', NULL, NULL, NULL, 2),
 (6, 'Veterinario aliado', 'Atención veterinaria inmediata ante cualquier situación.', 'fa-solid fa-stethoscope', NULL, NULL, NULL, 3),
 (6, 'Higiene diaria', 'Limpieza y desinfección de espacios varias veces al día.', 'fa-solid fa-soap', NULL, NULL, NULL, 4),
 (6, 'Reportes con fotos', 'Te enviamos fotos y novedades todos los días.', 'fa-solid fa-camera', NULL, NULL, NULL, 5),
 (6, 'Bienestar emocional', 'Atención individual para mascotas tímidas o ansiosas.', 'fa-solid fa-hand-holding-heart', NULL, NULL, NULL, 6),
 -- Actividades
 (7, 'Juego en grupo', 'Socialización en grupos pequeños según tamaño y energía.', 'fa-solid fa-futbol', '09:00 – 11:00', NULL, 8, 1),
 (7, 'Paseos', 'Caminatas para explorar y liberar energía.', 'fa-solid fa-person-walking', '07:30 y 18:00', NULL, 9, 2),
 (7, 'Circuito de agilidad', 'Túneles, rampas y saltos para estimular cuerpo y mente.', 'fa-solid fa-medal', '12:00 – 13:00', NULL, 10, 3),
 (7, 'Hora de la siesta', 'Descanso guiado con música relajante.', 'fa-solid fa-bed', '14:00 – 16:00', NULL, 11, 4),
 -- Misión y visión
 (10, 'Misión', 'Brindar cuidado profesional, seguro y amoroso a cada mascota, dando tranquilidad a sus familias.', 'fa-solid fa-bullseye', NULL, NULL, NULL, 1),
 (10, 'Visión', 'Ser la guardería de mascotas más confiable de la región y referente en bienestar animal.', 'fa-solid fa-eye', NULL, NULL, NULL, 2),
 (10, 'Valores', 'Amor, responsabilidad, transparencia y respeto por cada ser vivo.', 'fa-solid fa-gem', NULL, NULL, NULL, 3),
 (10, 'Meta 2030', 'Abrir 3 sedes y un programa permanente de adopción responsable.', 'fa-solid fa-rocket', NULL, NULL, NULL, 4),
 -- Testimonios
 (11, 'Mariana López', 'Dejé a Rocky una semana y regresó feliz. Las fotos diarias me dieron muchísima tranquilidad.', 'fa-solid fa-dog', 'Rocky · Golden Retriever', NULL, NULL, 1),
 (11, 'Carlos Méndez', 'Luna es muy tímida y aun así se adaptó increíble. Se nota el cariño del equipo.', 'fa-solid fa-cat', 'Luna · Gata siamés', NULL, NULL, 2),
 (11, 'Fernanda Ruiz', 'Instalaciones impecables y un trato excelente. Ya es su segundo hogar.', 'fa-solid fa-dog', 'Toby · Schnauzer', NULL, NULL, 3),
 -- Preguntas frecuentes
 (12, '¿Qué necesito para hospedar a mi mascota?', 'Cartilla de vacunación vigente, desparasitación reciente y una breve entrevista para conocer sus hábitos.', 'fa-solid fa-clipboard-check', NULL, NULL, NULL, 1),
 (12, '¿Puedo llevar su comida?', 'Sí, te recomendamos traer su alimento habitual para evitar cambios bruscos en su dieta.', 'fa-solid fa-bowl-food', NULL, NULL, NULL, 2),
 (12, '¿Puedo ver cómo está durante la estancia?', 'Claro, enviamos fotos y reportes diarios por WhatsApp.', 'fa-solid fa-mobile-screen', NULL, NULL, NULL, 3),
 (12, '¿Qué pasa si se enferma?', 'Contamos con un veterinario aliado y te contactamos de inmediato ante cualquier síntoma.', 'fa-solid fa-stethoscope', NULL, NULL, NULL, 4),
 -- CTA página de políticas
 (16, 'Consultar disponibilidad', NULL, 'fa-solid fa-calendar-check', NULL, 'index.php#disponibilidad', NULL, 1),
 (16, 'Escríbenos', NULL, 'fa-solid fa-envelope', NULL, 'index.php#contacto', NULL, 2);

-- ---------------------------------------------------------------------
--  Galería
-- ---------------------------------------------------------------------
INSERT INTO gallery_categories (id, name, sort_order) VALUES
 (1, 'Instalaciones', 1), (2, 'Juego', 2), (3, 'Descanso', 3), (4, 'Paseos', 4);

INSERT INTO gallery_photos (gallery_category_id, media_id, caption, sort_order) VALUES
 (1, 12, 'Área de juego principal', 1),
 (2, 13, 'Amigos peludos jugando', 2),
 (3, 14, 'Siesta después del almuerzo', 3),
 (4, 15, 'Paseo de la tarde', 4),
 (2, 16, 'Hora de la pelota', 5),
 (1, 17, 'Suites recién preparadas', 6),
 (3, 18, 'Rincón felino', 7),
 (4, 19, 'Explorando el parque', 8);

-- ---------------------------------------------------------------------
--  Alojamiento y reservas
-- ---------------------------------------------------------------------
INSERT INTO pet_types (id, name, icon, sort_order) VALUES
 (1, 'Perro', 'fa-solid fa-dog', 1),
 (2, 'Gato',  'fa-solid fa-cat', 2);

INSERT INTO accommodations (pet_type_id, name, description, capacity, media_id, sort_order) VALUES
 (1, 'Suites caninas', 'Habitaciones individuales con cama ortopédica, ventilación natural y acceso al patio.', 6, 5, 1),
 (1, 'Loft para razas pequeñas', 'Espacio cálido y tranquilo pensado para perros pequeños y cachorros.', 4, 6, 2),
 (2, 'Habitaciones felinas', 'Torres para escalar, rascadores, ventanas y total separación de los perros.', 5, 7, 3);

INSERT INTO booking_statuses (id, code, name, color, blocks_capacity, sort_order) VALUES
 (1, 'pending',   'Pendiente',  '#F5C15C', 1, 1),
 (2, 'confirmed', 'Confirmada', '#9ACCCD', 1, 2),
 (3, 'completed', 'Finalizada', '#1C3762', 0, 3),
 (4, 'cancelled', 'Cancelada',  '#E07A7A', 0, 4);

INSERT INTO customers (id, full_name, email, phone) VALUES
 (1, 'Mariana López', 'mariana@example.com', '5512345678'),
 (2, 'Carlos Méndez', 'carlos@example.com',  '5523456789'),
 (3, 'Fernanda Ruiz', 'fernanda@example.com','5534567890'),
 (4, 'Jorge Salas',   'jorge@example.com',   '5545678901');

-- Reservas de ejemplo relativas a la fecha actual.
-- Entre los días +10 y +14 los lugares para perros quedan llenos (para probar "no disponible").
INSERT INTO bookings (customer_id, pet_type_id, booking_status_id, pet_name, pets_count, check_in, check_out, notes) VALUES
 (1, 1, 2, 'Rocky', 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY),  DATE_ADD(CURDATE(), INTERVAL 6 DAY),  'Come dos veces al día.'),
 (2, 2, 2, 'Luna',  1, DATE_ADD(CURDATE(), INTERVAL 3 DAY),  DATE_ADD(CURDATE(), INTERVAL 8 DAY),  'Es tímida, necesita espacio tranquilo.'),
 (3, 1, 2, 'Toby',  2, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Vienen dos hermanos.'),
 (4, 1, 2, 'Max',   4, DATE_ADD(CURDATE(), INTERVAL 9 DAY),  DATE_ADD(CURDATE(), INTERVAL 16 DAY), 'Grupo familiar.'),
 (1, 1, 1, 'Nala',  4, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Solicitud enviada desde la web.'),
 (2, 2, 4, 'Michi', 1, DATE_ADD(CURDATE(), INTERVAL 5 DAY),  DATE_ADD(CURDATE(), INTERVAL 7 DAY),  'Cancelada por el cliente.');

INSERT INTO closed_dates (date_from, date_to, reason) VALUES
 (CONCAT(YEAR(CURDATE()), '-12-24'), CONCAT(YEAR(CURDATE()), '-12-25'), 'Cierre por Navidad');

-- ---------------------------------------------------------------------
--  Contacto, redes y ubicación
-- ---------------------------------------------------------------------
INSERT INTO contact_types (id, name, icon, url_prefix) VALUES
 (1, 'Teléfono', 'fa-solid fa-phone', 'tel:'),
 (2, 'WhatsApp', 'fa-brands fa-whatsapp', 'https://wa.me/'),
 (3, 'Correo electrónico', 'fa-solid fa-envelope', 'mailto:');

INSERT INTO contact_channels (contact_type_id, label, value, sort_order) VALUES
 (2, 'WhatsApp', '5215512345678', 1),
 (1, 'Teléfono', '+52 55 1234 5678', 2),
 (3, 'Correo', 'hola@somosmascoteros.com', 3);

INSERT INTO social_platforms (id, name, icon) VALUES
 (1, 'Instagram', 'fa-brands fa-instagram'),
 (2, 'Facebook',  'fa-brands fa-facebook-f'),
 (3, 'TikTok',    'fa-brands fa-tiktok'),
 (4, 'YouTube',   'fa-brands fa-youtube'),
 (5, 'X',         'fa-brands fa-x-twitter');

INSERT INTO social_links (social_platform_id, url, sort_order) VALUES
 (1, 'https://www.instagram.com/', 1),
 (2, 'https://www.facebook.com/', 2),
 (3, 'https://www.tiktok.com/', 3);

INSERT INTO locations (id, name, address, city, state, postal_code, country, latitude, longitude, reference_notes) VALUES
 (1, 'Sede principal', 'Av. de las Mascotas 123, Col. Centro', 'Tu ciudad', 'Tu estado', '00000', 'México', 19.4326077, -99.1332080, 'Frente al parque, portón azul con huellitas amarillas.');

INSERT INTO weekdays (id, name) VALUES
 (1, 'Lunes'), (2, 'Martes'), (3, 'Miércoles'), (4, 'Jueves'), (5, 'Viernes'), (6, 'Sábado'), (7, 'Domingo');

INSERT INTO business_hours (location_id, weekday_id, is_closed, opens_at, closes_at) VALUES
 (1, 1, 0, '07:00', '20:00'), (1, 2, 0, '07:00', '20:00'), (1, 3, 0, '07:00', '20:00'),
 (1, 4, 0, '07:00', '20:00'), (1, 5, 0, '07:00', '20:00'), (1, 6, 0, '09:00', '15:00'),
 (1, 7, 1, NULL, NULL);

INSERT INTO contact_messages (full_name, email, phone, subject, message, is_read) VALUES
 ('Ana Torres', 'ana@example.com', '5598765432', 'Información de hospedaje', 'Hola, ¿cuál es el precio por una semana para un perro mediano?', 0);
