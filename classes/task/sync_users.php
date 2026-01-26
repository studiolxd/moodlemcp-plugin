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
 * Scheduled task to sync MoodleMCP users and keys.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moodlemcp\task;

use core\task\scheduled_task;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task to sync users and keys with the panel.
 */
class sync_users extends scheduled_task {
    /**
     * Returns the task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_sync_users', 'local_moodlemcp');
    }

    /**
     * Executes the task.
     *
     * @return void
     */
    public function execute(): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

        if ((int)get_config('local_moodlemcp', 'auto_sync') !== 1) {
            return;
        }

        $result = local_moodlemcp_sync_all_users();
        if (!$result['ok']) {
            mtrace('MoodleMCP: sync skipped (invalid license).');
            return;
        }
        mtrace('MoodleMCP: synced users: ' . $result['synced'] . ', revoked keys: ' . $result['revoked']);
    }
}
