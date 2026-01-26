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
 * Selector for existing MoodleMCP service users.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moodlemcp\selector;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/selector/lib.php');

/**
 * Selector for existing users.
 */
class existing_users extends \user_selector_base
{
    /** @var array */
    protected $options;

    /**
     * Constructor.
     *
     * @param string $name control name
     * @param array $options options
     */
    public function __construct($name, $options)
    {
        parent::__construct($name, $options);
        $this->options = $options;
    }
    /**
     * Finds users already assigned to the service.
     *
     * @param string $search
     * @return array
     */
    public function find_users($search): array
    {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

        $service = $this->options['service'] ?? '';
        $serviceid = local_moodlemcp_get_service_id($service);
        if (!$serviceid) {
            return [];
        }

        $wheres = [
            'esu.externalserviceid = ?',
            'u.deleted = 0',
            'u.suspended = 0',
            'u.id <> ?'
        ];
        $sqlparams = [
            $serviceid,
            isset($CFG->siteguest) ? (int) $CFG->siteguest : 0
        ];

        // Manual search SQL construction to ensure strictly positional parameters
        if ($search !== '') {
            $conditions = [];
            $words = explode(' ', trim($search));
            foreach ($words as $word) {
                if ($word === '') {
                    continue;
                }
                $like = '%' . $DB->sql_like_escape($word) . '%';
                $subconditions = [];

                $subconditions[] = 'u.firstname LIKE ?';
                $sqlparams[] = $like;
                $subconditions[] = 'u.lastname LIKE ?';
                $sqlparams[] = $like;
                $subconditions[] = 'u.email LIKE ?';
                $sqlparams[] = $like;
                $subconditions[] = 'u.username LIKE ?';
                $sqlparams[] = $like;

                $conditions[] = '(' . implode(' OR ', $subconditions) . ')';
            }
            if (!empty($conditions)) {
                $wheres[] = '(' . implode(' AND ', $conditions) . ')';
            }
        }

        $sql = "SELECT u.*
                  FROM {external_services_users} esu
                  JOIN {user} u ON u.id = esu.userid
                 WHERE " . implode(' AND ', $wheres) . "
              ORDER BY u.lastname, u.firstname";

        $limit = $this->options['perpage'] ?? 100;
        $users = $DB->get_records_sql($sql, $sqlparams, 0, $limit);

        if (empty($users)) {
            return [];
        }

        return [get_string('existing_users', 'local_moodlemcp') => $users];
    }

    /**
     * Returns selector options.
     *
     * @return array
     */
    protected function get_options(): array
    {
        $options = parent::get_options();
        $options['service'] = $this->options['service'] ?? '';
        $options['file'] = 'local/moodlemcp/classes/selector/existing_users.php';
        return $options;
    }
}
