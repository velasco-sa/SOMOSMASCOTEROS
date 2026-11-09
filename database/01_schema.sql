-- =====================================================================
--  SOMOS MASCOTEROS · Esquema de base de datos (MySQL 5.7+ / MariaDB 10.3+)
--  Normalizado a Tercera Forma Normal (3FN):
--   · Cada tabla tiene clave primaria y atributos atómicos (1FN).
--   · Ningún atributo depende de una parte de una clave compuesta (2FN).
--   · Ningún atributo no clave depende de otro atributo no clave (3FN):
--     los catálogos (roles, módulos, permisos, tipos, estados, días...)
--     viven en tablas propias y se referencian por clave foránea.
--   · Los datos derivados (capacidad total, ocupación, disponibilidad)
--     NO se almacenan: se calculan con consultas.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_logs, login_attempts, role_module_permissions, users, permissions,
  modules, module_groups, roles, settings, setting_groups, section_items, sections, section_types,
  pages, gallery_photos, gallery_categories, bookings, booking_statuses, customers, closed_dates,
  accommodations, pet_types, contact_channels, contact_types, social_links, social_platforms,
  business_hours, weekdays, locations, contact_messages, media;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
--  SEGURIDAD: roles, módulos, permisos y usuarios
-- ---------------------------------------------------------------------
CREATE TABLE roles (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,
  description   VARCHAR(255) NULL,
  is_system     TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = rol protegido (no se puede eliminar)',
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE module_groups (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,
  sort_order    SMALLINT     NOT NULL DEFAULT 0,
  UNIQUE KEY uq_module_groups_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE modules (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module_group_id INT UNSIGNED NOT NULL,
  code            VARCHAR(50)  NOT NULL COMMENT 'Identificador interno usado por el sistema',
  name            VARCHAR(80)  NOT NULL,
  description     VARCHAR(255) NULL,
  icon            VARCHAR(60)  NOT NULL DEFAULT 'fa-solid fa-circle',
  sort_order      SMALLINT     NOT NULL DEFAULT 0,
  is_active       TINYINT(1)   NOT NULL DEFAULT 1,
  UNIQUE KEY uq_modules_code (code),
  CONSTRAINT fk_modules_group FOREIGN KEY (module_group_id) REFERENCES module_groups(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code          VARCHAR(30)  NOT NULL,
  name          VARCHAR(60)  NOT NULL,
  description   VARCHAR(255) NULL,
  UNIQUE KEY uq_permissions_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_module_permissions (
  role_id       INT UNSIGNED NOT NULL,
  module_id     INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, module_id, permission_id),
  CONSTRAINT fk_rmp_role   FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_rmp_module FOREIGN KEY (module_id)     REFERENCES modules(id)     ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_rmp_perm   FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id        INT UNSIGNED NOT NULL,
  full_name      VARCHAR(120) NOT NULL,
  email          VARCHAR(160) NOT NULL,
  password_hash  VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt/argon2 generado con password_hash()',
  is_active      TINYINT(1)   NOT NULL DEFAULT 1,
  last_login_at  DATETIME     NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email          VARCHAR(160) NOT NULL,
  ip_address     VARCHAR(45)  NOT NULL,
  was_successful TINYINT(1)   NOT NULL DEFAULT 0,
  attempted_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_login_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id        INT UNSIGNED NULL,
  module_id      INT UNSIGNED NOT NULL,
  permission_id  INT UNSIGNED NOT NULL COMMENT 'Acción realizada (crear, editar, eliminar)',
  record_id      INT UNSIGNED NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_activity_created (created_at),
  CONSTRAINT fk_activity_user   FOREIGN KEY (user_id)       REFERENCES users(id)       ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_activity_module FOREIGN KEY (module_id)     REFERENCES modules(id)     ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_activity_perm   FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  BIBLIOTECA DE MEDIOS
-- ---------------------------------------------------------------------
CREATE TABLE media (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  file_path      VARCHAR(255) NOT NULL COMMENT 'Ruta relativa a la raíz del sitio',
  original_name  VARCHAR(255) NOT NULL,
  mime_type      VARCHAR(80)  NOT NULL,
  size_bytes     INT UNSIGNED NOT NULL DEFAULT 0,
  alt_text       VARCHAR(255) NULL,
  uploaded_by    INT UNSIGNED NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_media_path (file_path),
  CONSTRAINT fk_media_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  CONFIGURACIÓN GENERAL DEL SITIO
-- ---------------------------------------------------------------------
CREATE TABLE setting_groups (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,
  icon          VARCHAR(60)  NOT NULL DEFAULT 'fa-solid fa-gear',
  sort_order    SMALLINT     NOT NULL DEFAULT 0,
  UNIQUE KEY uq_setting_groups_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_group_id INT UNSIGNED NOT NULL,
  setting_key      VARCHAR(60)  NOT NULL,
  label            VARCHAR(120) NOT NULL,
  help_text        VARCHAR(255) NULL,
  input_type       ENUM('text','textarea','color','image','email','url','number','bool','font') NOT NULL DEFAULT 'text',
  setting_value    TEXT         NULL COMMENT 'Valor para todos los tipos excepto imagen',
  media_id         INT UNSIGNED NULL COMMENT 'Valor para el tipo imagen',
  sort_order       SMALLINT     NOT NULL DEFAULT 0,
  UNIQUE KEY uq_settings_key (setting_key),
  CONSTRAINT fk_settings_group FOREIGN KEY (setting_group_id) REFERENCES setting_groups(id) ON UPDATE CASCADE,
  CONSTRAINT fk_settings_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  PÁGINAS Y SECCIONES (contenido editable)
-- ---------------------------------------------------------------------
CREATE TABLE pages (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug             VARCHAR(80)  NOT NULL,
  title            VARCHAR(120) NOT NULL,
  meta_description VARCHAR(255) NULL,
  is_home          TINYINT(1)   NOT NULL DEFAULT 0,
  is_published     TINYINT(1)   NOT NULL DEFAULT 1,
  show_in_menu     TINYINT(1)   NOT NULL DEFAULT 0,
  sort_order       SMALLINT     NOT NULL DEFAULT 0,
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE section_types (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code          VARCHAR(40)  NOT NULL COMMENT 'Plantilla visual que usa el sitio',
  name          VARCHAR(80)  NOT NULL,
  description   VARCHAR(255) NULL,
  icon          VARCHAR(60)  NOT NULL DEFAULT 'fa-solid fa-square',
  UNIQUE KEY uq_section_types_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sections (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_id         INT UNSIGNED NOT NULL,
  section_type_id INT UNSIGNED NOT NULL,
  anchor          VARCHAR(80)  NOT NULL COMMENT 'Ancla para el menú (#ancla)',
  menu_label      VARCHAR(60)  NULL,
  eyebrow         VARCHAR(80)  NULL COMMENT 'Texto pequeño sobre el título',
  title           VARCHAR(160) NULL,
  subtitle        VARCHAR(255) NULL,
  body            TEXT         NULL,
  media_id        INT UNSIGNED NULL,
  is_visible      TINYINT(1)   NOT NULL DEFAULT 1,
  show_in_menu    TINYINT(1)   NOT NULL DEFAULT 0,
  sort_order      SMALLINT     NOT NULL DEFAULT 0,
  UNIQUE KEY uq_sections_anchor (page_id, anchor),
  CONSTRAINT fk_sections_page  FOREIGN KEY (page_id)         REFERENCES pages(id)         ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_sections_type  FOREIGN KEY (section_type_id) REFERENCES section_types(id) ON UPDATE CASCADE,
  CONSTRAINT fk_sections_media FOREIGN KEY (media_id)        REFERENCES media(id)         ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE section_items (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section_id    INT UNSIGNED NOT NULL,
  title         VARCHAR(160) NOT NULL,
  description   TEXT         NULL,
  icon          VARCHAR(60)  NULL,
  highlight     VARCHAR(80)  NULL COMMENT 'Dato destacado: precio, año, cifra, horario...',
  link_url      VARCHAR(255) NULL,
  media_id      INT UNSIGNED NULL,
  is_visible    TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order    SMALLINT     NOT NULL DEFAULT 0,
  CONSTRAINT fk_items_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_items_media   FOREIGN KEY (media_id)   REFERENCES media(id)    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  GALERÍA
-- ---------------------------------------------------------------------
CREATE TABLE gallery_categories (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,
  sort_order    SMALLINT     NOT NULL DEFAULT 0,
  UNIQUE KEY uq_gallery_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gallery_photos (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  gallery_category_id INT UNSIGNED NOT NULL,
  media_id            INT UNSIGNED NOT NULL,
  caption             VARCHAR(160) NULL,
  is_visible          TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order          SMALLINT     NOT NULL DEFAULT 0,
  CONSTRAINT fk_photos_category FOREIGN KEY (gallery_category_id) REFERENCES gallery_categories(id) ON UPDATE CASCADE,
  CONSTRAINT fk_photos_media    FOREIGN KEY (media_id)            REFERENCES media(id)              ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  ALOJAMIENTO Y DISPONIBILIDAD
-- ---------------------------------------------------------------------
CREATE TABLE pet_types (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,
  icon          VARCHAR(60)  NOT NULL DEFAULT 'fa-solid fa-paw',
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order    SMALLINT     NOT NULL DEFAULT 0,
  UNIQUE KEY uq_pet_types_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE accommodations (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_type_id   INT UNSIGNED NOT NULL,
  name          VARCHAR(100) NOT NULL,
  description   TEXT         NULL,
  capacity      SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Mascotas que puede alojar por noche',
  media_id      INT UNSIGNED NULL,
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order    SMALLINT     NOT NULL DEFAULT 0,
  CONSTRAINT fk_accommodations_pet   FOREIGN KEY (pet_type_id) REFERENCES pet_types(id) ON UPDATE CASCADE,
  CONSTRAINT fk_accommodations_media FOREIGN KEY (media_id)    REFERENCES media(id)     ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customers (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(120) NOT NULL,
  email         VARCHAR(160) NOT NULL,
  phone         VARCHAR(30)  NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_customers_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE booking_statuses (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code            VARCHAR(30)  NOT NULL,
  name            VARCHAR(60)  NOT NULL,
  color           VARCHAR(7)   NOT NULL DEFAULT '#9ACCCD',
  blocks_capacity TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1 = ocupa lugar en el calendario',
  sort_order      SMALLINT     NOT NULL DEFAULT 0,
  UNIQUE KEY uq_booking_statuses_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bookings (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id       INT UNSIGNED NOT NULL,
  pet_type_id       INT UNSIGNED NOT NULL,
  booking_status_id INT UNSIGNED NOT NULL,
  pet_name          VARCHAR(80)  NOT NULL,
  pets_count        TINYINT UNSIGNED NOT NULL DEFAULT 1,
  check_in          DATE         NOT NULL,
  check_out         DATE         NOT NULL,
  notes             TEXT         NULL,
  created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY ix_bookings_range (pet_type_id, check_in, check_out),
  CONSTRAINT fk_bookings_customer FOREIGN KEY (customer_id)       REFERENCES customers(id)        ON UPDATE CASCADE,
  CONSTRAINT fk_bookings_pet      FOREIGN KEY (pet_type_id)       REFERENCES pet_types(id)        ON UPDATE CASCADE,
  CONSTRAINT fk_bookings_status   FOREIGN KEY (booking_status_id) REFERENCES booking_statuses(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE closed_dates (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  date_from     DATE         NOT NULL,
  date_to       DATE         NOT NULL,
  reason        VARCHAR(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  CONTACTO, REDES Y UBICACIÓN
-- ---------------------------------------------------------------------
CREATE TABLE contact_types (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,
  icon          VARCHAR(60)  NOT NULL DEFAULT 'fa-solid fa-address-card',
  url_prefix    VARCHAR(60)  NULL COMMENT 'tel:, mailto:, https://wa.me/ ...',
  UNIQUE KEY uq_contact_types_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_channels (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  contact_type_id INT UNSIGNED NOT NULL,
  label           VARCHAR(80)  NOT NULL,
  value           VARCHAR(160) NOT NULL,
  is_visible      TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order      SMALLINT     NOT NULL DEFAULT 0,
  CONSTRAINT fk_channels_type FOREIGN KEY (contact_type_id) REFERENCES contact_types(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE social_platforms (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,
  icon          VARCHAR(60)  NOT NULL,
  UNIQUE KEY uq_social_platforms_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE social_links (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  social_platform_id INT UNSIGNED NOT NULL,
  url                VARCHAR(255) NOT NULL,
  is_visible         TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order         SMALLINT     NOT NULL DEFAULT 0,
  CONSTRAINT fk_social_platform FOREIGN KEY (social_platform_id) REFERENCES social_platforms(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE locations (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(100) NOT NULL,
  address        VARCHAR(200) NOT NULL,
  city           VARCHAR(80)  NOT NULL,
  state          VARCHAR(80)  NULL,
  postal_code    VARCHAR(15)  NULL,
  country        VARCHAR(80)  NOT NULL,
  latitude       DECIMAL(10,7) NULL,
  longitude      DECIMAL(10,7) NULL,
  reference_notes VARCHAR(255) NULL COMMENT 'Referencias para llegar',
  is_visible     TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order     SMALLINT     NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE weekdays (
  id            TINYINT UNSIGNED PRIMARY KEY COMMENT '1 = lunes ... 7 = domingo (ISO-8601)',
  name          VARCHAR(20)  NOT NULL,
  UNIQUE KEY uq_weekdays_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE business_hours (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  location_id   INT UNSIGNED NOT NULL,
  weekday_id    TINYINT UNSIGNED NOT NULL,
  is_closed     TINYINT(1)   NOT NULL DEFAULT 0,
  opens_at      TIME         NULL,
  closes_at     TIME         NULL,
  UNIQUE KEY uq_hours_day (location_id, weekday_id),
  CONSTRAINT fk_hours_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_hours_weekday  FOREIGN KEY (weekday_id)  REFERENCES weekdays(id)  ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(120) NOT NULL,
  email         VARCHAR(160) NOT NULL,
  phone         VARCHAR(30)  NULL,
  subject       VARCHAR(160) NULL,
  message       TEXT         NOT NULL,
  is_read       TINYINT(1)   NOT NULL DEFAULT 0,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_messages_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
