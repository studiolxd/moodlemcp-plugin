<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Spanish language pack for Moodle MCP
 *
 * @package    local_moodlemcp
 * @category   string
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Moodle MCP';

$string['privacy:metadata'] = 'El plugin local_moodlemcp no almacena datos personales.';

$string['privacy:metadata:moodlemcp'] = 'Datos enviados al servicio del panel MCP para crear y gestionar claves API.';
$string['privacy:metadata:moodlemcp:userid'] = 'El ID de usuario de Moodle.';
$string['privacy:metadata:moodlemcp:token'] = 'El token de servicio web generado para el usuario.';
$string['privacy:metadata:moodlemcp:roles'] = 'Los roles del usuario mapeados a servicios MCP.';
$string['privacy:metadata:moodlemcp:email'] = 'La dirección de correo electrónico del usuario, utilizada al enviar claves MCP.';
$string['privacy:metadata:moodlemcp:firstname'] = 'El nombre del usuario, utilizado en plantillas de correo.';
$string['privacy:metadata:moodlemcp:lastname'] = 'El apellido del usuario, utilizado en plantillas de correo.';

$string['adminpage'] = 'Moodle MCP';
$string['settings'] = 'Configuración';
$string['changes_saved'] = 'Cambios guardados.';
$string['ok'] = 'OK';
$string['missing'] = 'Falta';

$string['editservice'] = 'Editar servicio';
$string['editfunctions'] = 'Editar funciones';
$string['service_restore'] = 'Restaurar servicio';
$string['service_restored'] = 'Servicio "{$a}" restaurado a la configuración base.';
$string['service_restore_failed'] = 'No se pudo restaurar la configuración base del servicio.';
$string['service_updated'] = 'Servicio "{$a}" actualizado.';
$string['service_functions'] = 'Funciones permitidas';
$string['service_edit_heading'] = 'Editar funciones del servicio "{$a}"';
$string['services_created'] = 'Se crearon {$a} servicio(s) MoodleMCP.';
$string['invalidservice'] = 'Servicio desconocido.';
$string['missingservice'] = 'Falta el registro del servicio.';

$string['tab_license'] = 'Licencia';
$string['tab_services'] = 'Servicios';
$string['tab_users'] = 'Usuarios';
$string['tab_keys'] = 'Claves';
$string['tab_settings'] = 'Configuración';

$string['service_name_admin'] = 'Administrador';
$string['service_name_manager'] = 'Gestor';
$string['service_name_editingteacher'] = 'Profesor';
$string['service_name_teacher'] = 'Profesor sin permiso de edición';
$string['service_name_student'] = 'Estudiante';
$string['service_name_user'] = 'Usuario identificado';

$string['setup_summary'] = 'Este plugin crea servicios externos Moodle MCP y requiere una licencia válida para activarse.';
$string['setup_failed'] = 'Error en la configuración: {$a}';

$string['license_heading'] = 'Licencia';
$string['license_label'] = 'Clave de licencia';
$string['license_help'] = 'Introduce tu clave de licencia y valídala.';
$string['license_status_label'] = 'Estado de licencia: {$a}';
$string['license_status_ok'] = 'Configurada';
$string['license_status_error'] = 'Incorrecta';
$string['license_status_missing'] = 'No configurada';
$string['license_required'] = 'Se requiere una licencia válida para activar Moodle MCP.';
$string['license_save'] = 'Validar licencia';
$string['license_ok'] = 'Licencia verificada.';
$string['license_error'] = 'La licencia es incorrecta o no se pudo verificar.';
$string['license_empty'] = 'La clave de licencia es obligatoria.';
$string['license_http_error'] = 'Falló la verificación de licencia: {$a}';
$string['license_invalid'] = 'La licencia es incorrecta.';
$string['license_invalid_reason'] = 'La licencia es incorrecta ({$a}).';
$string['license_error_invalid_request'] = 'Verificación de licencia fallida: solicitud inválida.';
$string['license_error_invalid_license'] = 'Verificación de licencia fallida: licencia inválida.';
$string['license_error_not_configured'] = 'Verificación de licencia fallida: licencia no configurada.';
$string['license_error_url_mismatch'] = 'Verificación de licencia fallida: la URL no coincide.';
$string['license_checked_at'] = 'Última comprobación: {$a}';

$string['services_heading'] = 'Servicios';
$string['services_table_service'] = 'Servicio';
$string['services_table_status'] = 'Estado';
$string['services_table_actions'] = 'Acciones';


$string['auto_sync_admin'] = 'Sincronización automática de admins';
$string['auto_sync_admin_desc'] = 'Sincronizar automáticamente cuando se asigna o quita el rol de administrador del sitio.';
$string['auto_sync_manager'] = 'Sincronización automática de managers';
$string['auto_sync_manager_desc'] = 'Sincronizar automáticamente cuando se asigna o quita el rol de manager.';
$string['auto_sync_editingteacher'] = 'Sincronización automática de editingteachers';
$string['auto_sync_editingteacher_desc'] = 'Sincronizar automáticamente cuando se asigna o quita el rol de profesor con permiso de edición.';
$string['auto_sync_teacher'] = 'Sincronización automática de teachers';
$string['auto_sync_teacher_desc'] = 'Sincronizar automáticamente cuando se asigna o quita el rol de profesor sin permiso de edición.';
$string['auto_sync_student'] = 'Sincronización automática de students';
$string['auto_sync_student_desc'] = 'Sincronizar automáticamente cuando se matricula o desmatricula a un estudiante.';
$string['auto_sync_user'] = 'Sincronización automática de usuarios';
$string['auto_sync_user_desc'] = 'Sincronizar automáticamente cuando se crea un nuevo usuario en la plataforma.';

$string['auto_sync_section'] = 'Sincronización automática';
$string['email_section'] = 'Envío de claves por email';
$string['auto_email'] = 'Enviar claves MCP automáticamente por email';
$string['auto_email_desc'] = 'Cuando está habilitado, Moodle MCP envía las claves la primera vez que se crean.';
$string['email_subject'] = 'Asunto del email';
$string['email_subject_desc'] = 'Asunto del email con la clave MCP.';
$string['email_body'] = 'Cuerpo del email';
$string['email_body_desc'] = 'Plantilla del cuerpo del email con la clave MCP.';
$string['email_subject_default'] = 'Tu clave Moodle MCP';
$string['email_body_default'] = 'Hola, {$a->firstname}:' . "\n\n" .
    'Aquí tienes tu clave de Moodle MCP:' . "\n\n" .
    '{$a->mcpkey}' . "\n\n" .
    'Guárdala de forma segura. Contacta con tu administrador si necesitas una nueva.';

$string['keys_page'] = 'Claves MCP';
$string['keys_placeholder'] = 'Las claves aparecerán aquí cuando el panel esté conectado.';
$string['keys_missing_license'] = 'Configura una licencia antes de gestionar las claves.';
$string['keys_load_failed'] = 'No se pudieron cargar las claves desde el panel.';
$string['keys_empty'] = 'Aún no hay claves registradas para esta licencia.';
$string['keys_user'] = 'Usuario';
$string['keys_role'] = 'Roles';
$string['keys_status'] = 'Estado';
$string['keys_expires'] = 'Expira';
$string['keys_sent'] = 'Enviado';
$string['keys_actions'] = 'Acciones';

$string['key_status_active'] = 'Activa';
$string['key_status_suspended'] = 'Suspendida';
$string['key_status_revoked'] = 'Revocada';
$string['key_send'] = 'Enviar clave';
$string['key_resend'] = 'Reenviar clave';
$string['key_suspend'] = 'Suspender';
$string['key_activate'] = 'Activar';
$string['key_revoke'] = 'Revocar';
$string['key_delete'] = 'Eliminar';
$string['key_regenerate'] = 'Regenerar';
$string['key_sent'] = 'Email de clave enviado.';
$string['key_send_failed'] = 'No se pudo enviar el email con la clave.';
$string['key_suspended'] = 'Clave suspendida.';
$string['key_suspend_failed'] = 'No se pudo suspender la clave.';
$string['key_activated'] = 'Clave activada.';
$string['key_activate_failed'] = 'No se pudo activar la clave.';
$string['key_revoked'] = 'Clave revocada.';
$string['key_revoke_failed'] = 'No se pudo revocar la clave.';
$string['key_deleted'] = 'Clave eliminada.';
$string['key_delete_failed'] = 'No se pudo eliminar la clave.';
$string['key_regenerated'] = 'Clave regenerada.';
$string['key_regen_failed'] = 'No se pudo regenerar la clave.';

$string['users_page'] = 'Usuarios del servicio';
$string['users_available'] = 'Usuarios disponibles';
$string['users_assigned'] = 'Usuarios asignados';
$string['users_add'] = 'Añadir';
$string['users_remove'] = 'Quitar';
$string['users_added_singular'] = 'Se añadió 1 usuario.';
$string['users_added_plural'] = 'Se añadieron {$a} usuarios.';
$string['users_add_failed_singular'] = '1 usuario no se pudo añadir.';
$string['users_add_failed_plural'] = '{$a} usuarios no se pudieron añadir.';
$string['users_removed_singular'] = 'Se quitó 1 usuario.';
$string['users_removed_plural'] = 'Se quitaron {$a} usuarios.';
$string['users_sync_all'] = 'Sincronizar todo';
$string['users_sync_done'] = 'Sincronización completada.';
$string['users_sync_added_singular'] = 'Se añadió 1 usuario.';
$string['users_sync_added_plural'] = 'Se añadieron {$a} usuarios.';
$string['users_sync_removed_singular'] = 'Se quitó 1 usuario.';
$string['users_sync_removed_plural'] = 'Se quitaron {$a} usuarios.';
$string['users_sync_failed'] = 'La sincronización falló.';
$string['potential_users'] = 'Usuarios potenciales';
$string['existing_users'] = 'Usuarios existentes';

$string['users_manage'] = 'Gestionar usuarios';
$string['task_sync_users'] = 'Sincronizar usuarios de MoodleMCP';
