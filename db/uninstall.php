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
    require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

    $serviceids = [];
    $bycomponent = $DB->get_records('external_services', ['component' => 'local_moodlemcp'], '', 'id');
    foreach ($bycomponent as $service) {
        $serviceids[] = (int) $service->id;
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

    // Clean up plugin config.
    unset_all_config_for_plugin('local_moodlemcp');

    if ($DB->get_manager()->table_exists('task_scheduled')) {
        $DB->delete_records('task_scheduled', ['component' => 'local_moodlemcp']);
    }
    if ($DB->get_manager()->table_exists('task_adhoc')) {
        $DB->delete_records('task_adhoc', ['component' => 'local_moodlemcp']);
    }

    return true;
}
