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
 * Admin settings registration for Moodle MCP.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Categoría "Moodle MCP" (título sin link).
    $ADMIN->add('localplugins', new admin_category(
        'local_moodlemcp_category',
        get_string('pluginname', 'local_moodlemcp')
    ));

    // Licencia.
    $ADMIN->add('local_moodlemcp_category', new admin_externalpage(
        'local_moodlemcp',
        get_string('tab_license', 'local_moodlemcp'),
        new moodle_url('/local/moodlemcp/index.php')
    ));

    // Servicios.
    $ADMIN->add('local_moodlemcp_category', new admin_externalpage(
        'local_moodlemcp_services',
        get_string('tab_services', 'local_moodlemcp'),
        new moodle_url('/local/moodlemcp/services.php')
    ));

    // Usuarios.
    $ADMIN->add('local_moodlemcp_category', new admin_externalpage(
        'local_moodlemcp_users',
        get_string('tab_users', 'local_moodlemcp'),
        new moodle_url('/local/moodlemcp/users.php')
    ));

    // Claves.
    $ADMIN->add('local_moodlemcp_category', new admin_externalpage(
        'local_moodlemcp_keys',
        get_string('tab_keys', 'local_moodlemcp'),
        new moodle_url('/local/moodlemcp/keys.php')
    ));

    // Configuración.
    $ADMIN->add('local_moodlemcp_category', new admin_externalpage(
        'local_moodlemcp_settings',
        get_string('tab_settings', 'local_moodlemcp'),
        new moodle_url('/local/moodlemcp/settings_page.php')
    ));
}
