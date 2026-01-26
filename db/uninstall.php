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
 * Uninstall cleanup for Moodle MCP.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Executed on plugin uninstall.
 *
 * @return bool
 */
function xmldb_local_moodlemcp_uninstall() {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/lib/accesslib.php');
    require_once($CFG->dirroot . '/user/lib.php');
    require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

    $userid = (int)get_config('local_moodlemcp', 'moodlemcp_userid');
    if (!$userid) {
        $user = $DB->get_record('user', ['username' => 'moodlemcp', 'deleted' => 0], 'id', IGNORE_MISSING);
        $userid = $user ? (int)$user->id : 0;
    }

    $services = local_moodlemcp_get_service_definitions();
    $shortnames = array_column($services, 'shortname');
    $shortnames = array_merge($shortnames, [
        'local_moodlemcp',
        'mcp_admin',
        'mcp_manager',
        'mcp_teacher',
        'mcp_noneditingteacher',
        'mcp_student',
        'mcp_authenticateduser',
        'mcd_admin',
    ]);

    $roleid = (int)get_config('local_moodlemcp', 'moodlemcp_roleid');
    if (!$roleid) {
        $role = $DB->get_record('role', ['shortname' => 'moodlemcp'], 'id', IGNORE_MISSING);
        $roleid = $role ? (int)$role->id : 0;
    }

    $serviceids = [];
    $bycomponent = $DB->get_records('external_services', ['component' => 'local_moodlemcp'], '', 'id');
    foreach ($bycomponent as $service) {
        $serviceids[] = (int)$service->id;
    }

    foreach ($shortnames as $shortname) {
        $service = $DB->get_record('external_services', ['shortname' => $shortname], 'id', IGNORE_MISSING);
        if ($service) {
            $serviceids[] = (int)$service->id;
        }
    }

    $serviceids = array_values(array_unique($serviceids));

    foreach ($serviceids as $serviceid) {
        $DB->delete_records('external_services_functions', ['externalserviceid' => $serviceid]);
        $DB->delete_records('external_services_users', ['externalserviceid' => $serviceid]);
        $DB->delete_records('external_tokens', ['externalserviceid' => $serviceid]);
        if ($DB->get_manager()->table_exists('external_services_roles')) {
            $DB->delete_records('external_services_roles', ['externalserviceid' => $serviceid]);
        }
        $DB->delete_records('external_services', ['id' => $serviceid]);
    }

    if ($userid) {
        $DB->delete_records('external_tokens', ['userid' => $userid]);
        $user = $DB->get_record('user', ['id' => $userid], '*', IGNORE_MISSING);
        if ($user) {
            user_delete_user($user);
        }
    }

    if ($roleid && $DB->record_exists('role', ['id' => $roleid])) {
        delete_role($roleid);
    }

    // Clean up plugin config.
    unset_config('last_generated_token', 'local_moodlemcp');
    unset_config('show_token_once', 'local_moodlemcp');
    unset_config('moodlemcp_userid', 'local_moodlemcp');
    unset_config('moodlemcp_serviceid', 'local_moodlemcp');
    unset_config('moodlemcp_roleid', 'local_moodlemcp');
    unset_config('license_key', 'local_moodlemcp');
    unset_config('license_status', 'local_moodlemcp');
    unset_config('license_checked_at', 'local_moodlemcp');
    unset_config('license_last_error', 'local_moodlemcp');
    unset_config('auto_sync', 'local_moodlemcp');
    unset_config('auto_email', 'local_moodlemcp');
    unset_config('email_subject', 'local_moodlemcp');
    unset_config('email_body', 'local_moodlemcp');

    if ($DB->get_manager()->table_exists('task_scheduled')) {
        $DB->delete_records('task_scheduled', ['component' => 'local_moodlemcp']);
    }

    // Rebuild permissions caches after removing the role.
    accesslib_clear_all_caches(true);

    return true;
}
