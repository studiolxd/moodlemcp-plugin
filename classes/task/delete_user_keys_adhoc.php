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
 * Ad-hoc task to delete a user's keys in the panel.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moodlemcp\task;

use core\task\adhoc_task;

defined('MOODLE_INTERNAL') || die();

class delete_user_keys_adhoc extends adhoc_task {
    /**
     * Execute the task.
     *
     * @return void
     */
    public function execute(): void {
        global $CFG;

        require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

        $data = $this->get_custom_data();
        $userid = isset($data->userid) ? (int) $data->userid : 0;
        if (!$userid) {
            return;
        }

        local_moodlemcp_delete_user_keys($userid);
    }
}
