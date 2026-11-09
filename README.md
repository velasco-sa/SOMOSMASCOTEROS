# 🐾 Somos Mascoteros · Sitio web + Panel de administración

Sitio web para la guardería **Somos Mascoteros** con un **panel de administración** donde cualquier persona (sin saber programar) puede cambiar textos, imágenes, servicios, redes sociales, reservas, usuarios, permisos y todo lo demás.

> **Nada del contenido está escrito en el código:** todo se guarda en la base de datos y se edita desde el panel.

---

## 📁 ¿Qué hay en este proyecto?

| Carpeta / archivo | Para qué sirve |
|---|---|
| `index.php` | El sitio público (lo que ven tus clientes). |
| `includes/sections/` | El diseño de cada tipo de sección (portada, servicios, galería, etc.). |
| `api/` | Recibe el simulador de disponibilidad, las solicitudes de reserva y el formulario de contacto. |
| `admin/` | El panel de administración (**no tiene ningún botón en el sitio público**; se entra solo escribiendo la dirección). |
| `assets/` | Estilos (CSS), interacciones (JavaScript), librerías y logo. |
| `uploads/` | Imágenes. `uploads/seed/` son las ilustraciones de ejemplo; `uploads/media/` es donde se guardan las que subas. |
| `database/01_schema.sql` | Crea las tablas de la base de datos. |
| `database/02_seed.sql` | Carga los datos de ejemplo (textos, servicios, usuario administrador…). |
| `config/config.php` | Datos de conexión a la base de datos (lo cambiarás al contratar el hosting). |
| `.devcontainer/` | Configuración para abrir el proyecto en **GitHub Codespaces** (verlo funcionando sin hosting). |

**Tecnologías:** HTML, CSS, JavaScript, PHP 8 y MySQL/MariaDB. Librerías incluidas en el proyecto (sin depender de internet): jQuery, Select2, Flatpickr (calendario), Quill (editor de texto) y Font Awesome (íconos). Tipografías Montserrat y Raleway desde Google Fonts.

---

## 🚀 Cómo verlo funcionando SIN hosting (paso a paso)

> GitHub Pages **no** puede ejecutar PHP ni bases de datos, por eso usaremos **GitHub Codespaces**: una computadora en la nube, gratuita para cuentas personales (incluye 60 horas al mes), que abre tu repositorio y lo ejecuta.

1. Entra a tu repositorio en GitHub: `https://github.com/velasco-sa/SOMOSMASCOTEROS`
2. Haz clic en el botón verde **`<> Code`**.
3. Elige la pestaña **Codespaces** y pulsa **Create codespace on main**.
4. Espera de 3 a 5 minutos la primera vez (se instala PHP y la base de datos y se cargan los datos de ejemplo automáticamente). Verás un editor parecido a Visual Studio Code dentro del navegador.
5. Cuando termine, aparecerá un aviso abajo a la derecha: *"Your application running on port 8080 is available"* → pulsa **Open in Browser**.
   - Si no aparece: abre la pestaña **PORTS / Puertos** (parte inferior), busca el puerto **8080** y haz clic en el ícono del 🌐 globo.
6. ¡Listo! Verás el sitio público.
7. Para entrar al panel, agrega **`/admin/`** al final de la dirección del navegador. Ejemplo:
   `https://tu-codespace-8080.app.github.dev/admin/`

### 🔐 Datos de acceso iniciales al panel

| Correo | Contraseña |
|---|---|
| `admin@somosmascoteros.com` | `Mascoteros2026!` |

> ⚠️ **Cámbiala de inmediato** en el panel: menú de usuario (arriba a la derecha) → **Mi perfil y contraseña**.

### Cosas que conviene saber de Codespaces
- Si cierras la pestaña, el codespace se **detiene** solo al poco tiempo (no gasta horas). Para volver: `<> Code` → `Codespaces` → haz clic en el que ya creaste. Tus cambios en la base de datos se conservan mientras no lo elimines.
- La dirección del puerto 8080 es **privada** (solo tú, con tu sesión de GitHub, puedes verla). Para enseñársela a alguien más: en **PORTS**, clic derecho sobre 8080 → *Port Visibility* → *Public*.
- Si el sitio no carga después de reabrir el codespace, abre la **Terminal** (menú ☰ → Terminal → New Terminal) y escribe:
  ```bash
  bash .devcontainer/start.sh
  ```
- Para **borrar todo y volver a los datos de ejemplo**:
  ```bash
  bash .devcontainer/reset-db.sh
  ```

### Alternativa: en tu propia computadora (Windows / Mac)
1. Instala **XAMPP** desde https://www.apachefriends.org (incluye Apache, PHP y MySQL).
2. Descarga este repositorio (`<> Code` → **Download ZIP**) y descomprímelo dentro de `C:\xampp\htdocs\somosmascoteros` (en Mac: `/Applications/XAMPP/htdocs/somosmascoteros`).
3. Abre el panel de XAMPP y enciende **Apache** y **MySQL**.
4. Entra a `http://localhost/phpmyadmin` → **Nueva** → crea una base llamada `somos_mascoteros` con cotejamiento `utf8mb4_unicode_ci`.
5. Selecciona esa base → pestaña **Importar** → importa primero `database/01_schema.sql` y luego `database/02_seed.sql`.
6. Abre `http://localhost/somosmascoteros/` (sitio) y `http://localhost/somosmascoteros/admin/` (panel).
   *(XAMPP usa el usuario `root` sin contraseña, que ya es la configuración por defecto de `config/config.php`.)*

---

## 🧭 Cómo usar el panel

El menú lateral muestra solo los módulos que el rol del usuario tiene permitidos.

| Módulo | Qué puedes hacer |
|---|---|
| **Panel principal** | Resumen: mascotas hospedadas hoy, solicitudes pendientes, mensajes, gráfica de ocupación de 14 días, actividad reciente. |
| **Configuración del sitio** | Nombre, eslogan, **logo**, **favicon** (ícono de la pestaña), **colores**, **tipografías**, textos SEO, pie de página, mensajes del simulador y del formulario, botón de WhatsApp. |
| **Páginas** | Crear, editar, eliminar o despublicar páginas; elegir cuál es la de inicio y cuáles salen en el menú. |
| **Secciones** | Los bloques de cada página: Quiénes somos, Cómo nace, Servicios, Dónde duermen, Cuidados, Actividades, Galería, Simulador, A dónde queremos llegar, Testimonios, Preguntas, Ubicación, Contacto… Cada sección tiene título, subtítulo, texto con formato, imagen, orden y visibilidad. |
| **Elementos de sección** | Las tarjetas dentro de cada sección (cada servicio con su precio, cada actividad con su horario, cada año de la historia, cada pregunta, cada testimonio, los botones de la portada…). |
| **Biblioteca de medios** | Subir fotos (arrastrar y soltar). Se optimizan solas (máx. 2000 px) y se eliminan metadatos. |
| **Categorías / Fotos de galería** | Organizar la galería con filtros. |
| **Reservas** | Ver y gestionar las solicitudes del simulador (llegan como *Pendiente*). El sistema impide sobrecupo. |
| **Clientes, Tipos de mascota, Alojamientos** | La **capacidad** de los alojamientos es lo que usa el simulador para calcular la disponibilidad. |
| **Estados de reserva, Fechas cerradas** | Por ejemplo, cerrar en Navidad: el simulador mostrará esos días como *Cerrado*. |
| **Mensajes recibidos** | Lo que escriben en el formulario de contacto. |
| **Medios de contacto, Redes sociales, Ubicaciones, Horarios** | Teléfonos, WhatsApp, correo, Instagram, Facebook, TikTok, dirección, mapa y horario por día. |
| **Usuarios** | Crear cuentas para tu equipo y asignarles un rol. |
| **Roles** → botón **Permisos** | Matriz para decidir qué puede **ver / crear / editar / eliminar** cada rol en cada módulo. |
| **Módulos, Grupos, Permisos, Bitácora** | Renombrar o reordenar el menú, desactivar módulos y revisar quién cambió qué y cuándo. |

### Roles incluidos
- **Super administrador**: acceso total (protegido, no se puede eliminar).
- **Editor de contenido**: textos, imágenes, galería, alojamientos y contacto.
- **Recepción**: reservas, clientes y mensajes.
- **Solo lectura**: puede consultar sin modificar.

### Cómo poner tu logotipo
1. Entra al panel → **Configuración del sitio** → pestaña **Identidad**.
2. En **Logotipo** pulsa **Elegir imagen** → **Subir imágenes** → selecciona tu logo (PNG con fondo transparente es ideal).
3. Repite en **Ícono de pestaña (favicon)** (idealmente una versión cuadrada).
4. Pulsa **Guardar configuración**. El logo aparecerá en el encabezado, pie de página, pantalla de carga, inicio de sesión, panel y pestaña del navegador.
5. Si tu logo ya incluye el nombre, desactiva **"Mostrar el nombre junto al logo"**.

> En Codespaces, las imágenes que subas se guardan dentro del codespace (no en el repositorio). Al pasar a tu hosting definitivo las volverás a subir desde el panel.

---

## 🗄️ Base de datos (normalizada en 3FN)

- Cada tabla tiene clave primaria y datos atómicos (**1FN**).
- Las tablas con clave compuesta (`role_module_permissions`) no tienen atributos que dependan de solo una parte (**2FN**).
- No hay dependencias transitivas: los catálogos (roles, módulos, permisos, tipos de mascota, estados de reserva, tipos de contacto, plataformas sociales, días de la semana, tipos de sección…) viven en sus propias tablas y se relacionan por llaves foráneas (**3FN**).
- Los datos que se pueden calcular **no se guardan** (capacidad total, ocupación, disponibilidad): se calculan con consultas.

**Seguridad:** `roles`, `modules`, `module_groups`, `permissions`, `role_module_permissions`, `users`, `login_attempts`, `activity_logs`
**Contenido:** `pages`, `section_types`, `sections`, `section_items`, `media`, `settings`, `setting_groups`
**Galería:** `gallery_categories`, `gallery_photos`
**Reservas:** `pet_types`, `accommodations`, `customers`, `bookings`, `booking_statuses`, `closed_dates`
**Contacto:** `contact_types`, `contact_channels`, `social_platforms`, `social_links`, `locations`, `weekdays`, `business_hours`, `contact_messages`

---

## 🛡️ Seguridad incluida

- Contraseñas cifradas con **hash** (`password_hash`, Argon2id o bcrypt) y re-cifrado automático si el algoritmo mejora.
- Bloqueo temporal tras **5 intentos fallidos** en 15 minutos.
- Sesiones seguras (HttpOnly, SameSite) con cierre automático tras 2 h sin actividad.
- Protección **CSRF** en todos los formularios y acciones.
- Consultas preparadas (**sin inyección SQL**), limpieza del texto con formato (sin scripts) y escape de todo lo que se muestra.
- Subida de imágenes validando el tipo real, con nombre aleatorio y re-codificación; la carpeta `uploads` no ejecuta código.
- El panel envía `noindex, nofollow` (meta y cabecera `X-Robots-Tag`) y **no aparece enlazado** en el sitio ni en `robots.txt`.
- Permisos verificados en el servidor para cada acción, no solo ocultando botones.

---

## 🌐 Cuando contrates hosting y dominio

1. Contrata un hosting con **PHP 8.1 o superior** y **MySQL/MariaDB** (casi todos los planes compartidos con cPanel lo incluyen).
2. En cPanel → **Bases de datos MySQL**: crea una base, un usuario con contraseña segura y asígnale **todos los privilegios**.
3. En **phpMyAdmin** importa `database/01_schema.sql` y después `database/02_seed.sql` (o exporta tu base desde Codespaces para conservar lo que ya editaste).
4. Abre `config/config.php` y reemplaza los valores de la derecha:
   ```php
   'db_host' => getenv('DB_HOST') ?: 'localhost',
   'db_name' => getenv('DB_NAME') ?: 'NOMBRE_DE_TU_BASE',
   'db_user' => getenv('DB_USER') ?: 'TU_USUARIO',
   'db_pass' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'TU_CONTRASEÑA',
   ```
5. Sube todos los archivos (con el **Administrador de archivos** de cPanel o por FTP) a la carpeta `public_html`. No es necesario subir `.devcontainer/` ni `README.md`.
6. Activa el certificado **SSL gratuito** (AutoSSL / Let's Encrypt) para que tu sitio abra con `https://`.
7. Entra a `https://tudominio.com/admin/`, cambia la contraseña y sube tu logo y fotos reales.
8. **Consejo de seguridad:** puedes renombrar la carpeta `admin` (por ejemplo a `panel-sm-2026`) para que la dirección del panel sea más difícil de adivinar; todo sigue funcionando.
