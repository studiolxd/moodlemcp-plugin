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
 * TODO describe file settings
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_moodlemcp',
        get_string('adminpage', 'local_moodlemcp'),
        new moodle_url('/local/moodlemcp/index.php')
    ));

    $ADMIN->add('localplugins', new admin_externalpage(
        'local_moodlemcp_keys',
        get_string('keys_page', 'local_moodlemcp'),
        new moodle_url('/local/moodlemcp/keys.php')
    ));
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_moodlemcp_users',
        get_string('users_page', 'local_moodlemcp'),
        new moodle_url('/local/moodlemcp/users.php')
    ));

    $ADMIN->add('localplugins', new admin_externalpage(
        'local_moodlemcp_settings',
        get_string('settings', 'local_moodlemcp'),
        new moodle_url('/local/moodlemcp/settings_page.php')
    ));
}
