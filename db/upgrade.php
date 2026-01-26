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
 * Upgrade steps for Moodle MCP
 *
 * Documentation: {@link https://moodledev.io/docs/guides/upgrade}
 *
 * @package    local_moodlemcp
 * @category   upgrade
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute the plugin upgrade steps from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_moodlemcp_upgrade($oldversion) {
    global $CFG;

    require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

    if ($oldversion < 2026012001) {
        local_moodlemcp_ensure_services();

        if (get_config('local_moodlemcp', 'license_status') === false) {
            set_config('license_status', 'missing', 'local_moodlemcp');
        }

        if (get_config('local_moodlemcp', 'auto_sync') === false) {
            set_config('auto_sync', 0, 'local_moodlemcp');
        }
        if (get_config('local_moodlemcp', 'auto_email') === false) {
            set_config('auto_email', 0, 'local_moodlemcp');
        }
        if (get_config('local_moodlemcp', 'email_subject') === false) {
            set_config('email_subject', get_string('email_subject_default', 'local_moodlemcp'), 'local_moodlemcp');
        }
        if (get_config('local_moodlemcp', 'email_body') === false) {
            set_config('email_body', get_string('email_body_default', 'local_moodlemcp'), 'local_moodlemcp');
        }

        upgrade_plugin_savepoint(true, 2026012001, 'local', 'moodlemcp');
    }

    if ($oldversion < 2026012002) {
        upgrade_plugin_savepoint(true, 2026012002, 'local', 'moodlemcp');
    }

    if ($oldversion < 2026012003) {
        upgrade_plugin_savepoint(true, 2026012003, 'local', 'moodlemcp');
    }

    return true;
}
