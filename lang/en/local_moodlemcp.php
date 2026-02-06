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
 * English language pack for Moodle MCP
 *
 * @package    local_moodlemcp
 * @category   string
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Moodle MCP';

$string['privacy:metadata'] = 'The local_moodlemcp plugin does not store personal data.';

$string['privacy:metadata:moodlemcp'] = 'Data sent to the MCP panel service to create and manage API keys.';
$string['privacy:metadata:moodlemcp:userid'] = 'The Moodle user ID.';
$string['privacy:metadata:moodlemcp:token'] = 'The web service token generated for the user.';
$string['privacy:metadata:moodlemcp:roles'] = 'The user roles mapped to MCP services.';
$string['privacy:metadata:moodlemcp:email'] = 'The user email address, used when sending MCP keys.';
$string['privacy:metadata:moodlemcp:firstname'] = 'The user first name, used in email templates.';
$string['privacy:metadata:moodlemcp:lastname'] = 'The user last name, used in email templates.';

$string['adminpage'] = 'Moodle MCP';
$string['settings'] = 'Settings';
$string['changes_saved'] = 'Changes saved.';
$string['ok'] = 'OK';
$string['missing'] = 'Missing';

$string['editservice'] = 'Edit service';
$string['editfunctions'] = 'Edit functions';
$string['service_restore'] = 'Restore service';
$string['service_restored'] = 'Service "{$a}" restored to baseline.';
$string['service_restore_failed'] = 'Unable to restore service baseline.';
$string['service_updated'] = 'Service "{$a}" updated.';
$string['service_functions'] = 'Allowed functions';
$string['service_edit_heading'] = 'Edit functions for service "{$a}"';
$string['services_created'] = '{$a} MoodleMCP service(s) were created.';
$string['invalidservice'] = 'Unknown service.';
$string['missingservice'] = 'Service record is missing.';

$string['tab_license'] = 'License';
$string['tab_services'] = 'Services';
$string['tab_users'] = 'Users';
$string['tab_keys'] = 'Keys';
$string['tab_settings'] = 'Settings';

$string['service_name_admin'] = 'Administrator';
$string['service_name_manager'] = 'Manager';
$string['service_name_editingteacher'] = 'Teacher';
$string['service_name_teacher'] = 'Non-editing teacher';
$string['service_name_student'] = 'Student';
$string['service_name_user'] = 'Authenticated user';
$string['setup_summary'] = 'This plugin creates Moodle MCP external services and requires a valid license key to activate.';
$string['setup_failed'] = 'Setup failed: {$a}';

$string['license_heading'] = 'License';
$string['license_label'] = 'License key';
$string['license_help'] = 'Enter your license key and validate it.';
$string['license_status_label'] = 'License status: {$a}';
$string['license_status_ok'] = 'Configured';
$string['license_status_error'] = 'Incorrect';
$string['license_status_missing'] = 'Missing';
$string['license_required'] = 'A valid license is required to activate Moodle MCP.';
$string['license_save'] = 'Validate license';
$string['license_ok'] = 'License verified.';
$string['license_error'] = 'License is incorrect or could not be verified.';
$string['license_empty'] = 'License key is required.';
$string['license_http_error'] = 'License verification failed: {$a}';
$string['license_invalid'] = 'License is incorrect.';
$string['license_invalid_reason'] = 'License is incorrect ({$a}).';
$string['license_error_invalid_request'] = 'License verification failed: invalid request.';
$string['license_error_invalid_license'] = 'License verification failed: invalid license.';
$string['license_error_not_configured'] = 'License verification failed: license not configured.';
$string['license_error_url_mismatch'] = 'License verification failed: URL mismatch.';
$string['license_checked_at'] = 'Last checked: {$a}';

$string['services_heading'] = 'Services';
$string['services_table_service'] = 'Service';
$string['services_table_status'] = 'Status';
$string['services_table_actions'] = 'Actions';


$string['auto_sync_admin'] = 'Auto-sync admins';
$string['auto_sync_admin_desc'] = 'Automatically sync when site administrator role is assigned or removed.';
$string['auto_sync_manager'] = 'Auto-sync managers';
$string['auto_sync_manager_desc'] = 'Automatically sync when manager role is assigned or removed.';
$string['auto_sync_editingteacher'] = 'Auto-sync editing teachers';
$string['auto_sync_editingteacher_desc'] = 'Automatically sync when editing teacher role is assigned or removed.';
$string['auto_sync_teacher'] = 'Auto-sync teachers';
$string['auto_sync_teacher_desc'] = 'Automatically sync when non-editing teacher role is assigned or removed.';
$string['auto_sync_student'] = 'Auto-sync students';
$string['auto_sync_student_desc'] = 'Automatically sync when student is enrolled or unenrolled.';
$string['auto_sync_user'] = 'Auto-sync users';
$string['auto_sync_user_desc'] = 'Automatically sync when a new user is created on the platform.';

$string['auto_sync_section'] = 'Automatic synchronization';
$string['email_section'] = 'Email key delivery';
$string['auto_email'] = 'Send MCP keys automatically by email';
$string['auto_email_desc'] = 'When enabled, Moodle MCP emails keys the first time they are created.';
$string['email_subject'] = 'Email subject';
$string['email_subject_desc'] = 'Subject line for the MCP key email.';
$string['email_body'] = 'Email body';
$string['email_body_desc'] = 'Body template for the MCP key email.';
$string['email_subject_default'] = 'Your Moodle MCP key';
$string['email_body_default'] = 'Hello, {$a->firstname}:' . "\n\n" .
    'Here is your Moodle MCP key:' . "\n\n" .
    '{$a->mcpkey}' . "\n\n" .
    'Keep it safe. Contact your administrator if you need a new one.';

$string['keys_page'] = 'MCP Keys';
$string['keys_placeholder'] = 'Keys will appear here once the panel API is connected.';
$string['keys_missing_license'] = 'Configure a license before managing keys.';
$string['keys_load_failed'] = 'Failed to load keys from the panel.';
$string['keys_empty'] = 'No keys are registered for this license yet.';
$string['keys_user'] = 'User';
$string['keys_role'] = 'Roles';
$string['keys_status'] = 'Status';
$string['keys_expires'] = 'Expires';
$string['keys_sent'] = 'Sent';
$string['keys_actions'] = 'Actions';

$string['key_status_active'] = 'Active';
$string['key_status_suspended'] = 'Suspended';
$string['key_status_revoked'] = 'Revoked';
$string['key_send'] = 'Send key';
$string['key_resend'] = 'Resend key';
$string['key_suspend'] = 'Suspend';
$string['key_activate'] = 'Activate';
$string['key_revoke'] = 'Revoke';
$string['key_delete'] = 'Delete';
$string['key_regenerate'] = 'Regenerate';
$string['key_sent'] = 'Key email sent.';
$string['key_send_failed'] = 'Failed to send key email.';
$string['key_suspended'] = 'Key suspended.';
$string['key_suspend_failed'] = 'Failed to suspend key.';
$string['key_activated'] = 'Key activated.';
$string['key_activate_failed'] = 'Failed to activate key.';
$string['key_revoked'] = 'Key revoked.';
$string['key_revoke_failed'] = 'Failed to revoke key.';
$string['key_deleted'] = 'Key deleted.';
$string['key_delete_failed'] = 'Failed to delete key.';
$string['key_regenerated'] = 'Key regenerated.';
$string['key_regen_failed'] = 'Failed to regenerate key.';

$string['users_page'] = 'Service users';
$string['users_available'] = 'Available users';
$string['users_assigned'] = 'Assigned users';
$string['users_add'] = 'Add';
$string['users_remove'] = 'Remove';
$string['users_added_singular'] = '1 user added.';
$string['users_added_plural'] = '{$a} users added.';
$string['users_add_failed_singular'] = '1 user could not be added.';
$string['users_add_failed_plural'] = '{$a} users could not be added.';
$string['users_removed_singular'] = '1 user removed.';
$string['users_removed_plural'] = '{$a} users removed.';
$string['users_sync_all'] = 'Sync all';
$string['users_sync_queued'] = 'Sync queued. It will run in the background.';
$string['users_sync_done'] = 'Sync completed.';
$string['users_sync_added_singular'] = '1 user added.';
$string['users_sync_added_plural'] = '{$a} users added.';
$string['users_sync_removed_singular'] = '1 user removed.';
$string['users_sync_removed_plural'] = '{$a} users removed.';
$string['users_sync_failed'] = 'Sync failed.';
$string['potential_users'] = 'Potential users';
$string['existing_users'] = 'Existing users';

$string['users_manage'] = 'Manage users';
$string['task_sync_users'] = 'Sync MoodleMCP users';
