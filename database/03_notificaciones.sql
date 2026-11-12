-- =====================================================================
--  Actualización: avisos por correo (reservas y mensajes de contacto)
--  Ejecútalo UNA vez en phpMyAdmin (pestaña SQL) sobre tu base de datos.
--  Es seguro ejecutarlo de nuevo: no duplica ni borra nada.
-- =====================================================================
SET NAMES utf8mb4;

INSERT IGNORE INTO setting_groups (name, icon, sort_order) VALUES
 ('Notificaciones', 'fa-solid fa-bell', 7);

INSERT IGNORE INTO settings (setting_group_id, setting_key, label, help_text, input_type, setting_value, media_id, sort_order)
SELECT g.id, v.k, v.l, v.h, v.t, v.val, NULL, v.o
FROM setting_groups g
JOIN (
  SELECT 'notify_email' AS k, 'Correo que recibe los avisos' AS l, 'Ej: tucorreo@gmail.com. Déjalo vacío para no recibir avisos' AS h, 'email' AS t, '' AS val, 1 AS o
  UNION ALL SELECT 'notify_bookings', 'Avisarme cuando llegue una solicitud de reserva', NULL, 'bool', '1', 2
  UNION ALL SELECT 'notify_messages', 'Avisarme cuando llegue un mensaje de contacto', NULL, 'bool', '1', 3
) v
WHERE g.name = 'Notificaciones';
