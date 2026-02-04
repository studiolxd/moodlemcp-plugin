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
 * Privacy API provider for Moodle MCP.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moodlemcp\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;

/**
 * Privacy provider for local_moodlemcp.
 *
 * This plugin sends user data to the external MCP panel service
 * (moodlemcp.com) and manages web service tokens and service
 * assignments through Moodle core tables. The core tables
 * (external_tokens, external_services_users) are handled by
 * the core_external privacy provider.
 */
class provider implements \core_privacy\local\metadata\provider {

    /**
     * Returns metadata about user data managed by this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('moodlemcp.com', [
            'userid' => 'privacy:metadata:moodlemcp:userid',
            'token' => 'privacy:metadata:moodlemcp:token',
            'roles' => 'privacy:metadata:moodlemcp:roles',
            'email' => 'privacy:metadata:moodlemcp:email',
            'firstname' => 'privacy:metadata:moodlemcp:firstname',
            'lastname' => 'privacy:metadata:moodlemcp:lastname',
        ], 'privacy:metadata:moodlemcp');

        return $collection;
    }
}
