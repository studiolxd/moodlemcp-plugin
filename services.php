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
 * Services management page for Moodle MCP.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$serviceshortname = optional_param('service', '', PARAM_ALPHANUMEXT);
$notifications = [];

// Handle restore service action
if ($action === 'restore_service' && confirm_sesskey()) {
    if ($serviceshortname !== '' && local_moodlemcp_restore_service_baseline($serviceshortname)) {
        $notifications[] = [
            'message' => get_string('service_restored', 'local_moodlemcp', $serviceshortname),
            'type' => 'notifysuccess',
        ];
    } else {
        $notifications[] = [
            'message' => get_string('service_restore_failed', 'local_moodlemcp'),
            'type' => 'notifyproblem',
        ];
    }
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/moodlemcp/services.php'));
$PAGE->set_title(get_string('adminpage', 'local_moodlemcp'));
$PAGE->set_heading(get_string('adminpage', 'local_moodlemcp'));

echo $OUTPUT->header();

if (!empty($notifications)) {
    foreach ($notifications as $notification) {
        echo $OUTPUT->notification($notification['message'], $notification['type']);
    }
}

local_moodlemcp_print_tabs('services');

echo html_writer::tag('h3', get_string('services_heading', 'local_moodlemcp'));

$services = local_moodlemcp_get_service_definitions();
$table = new html_table();
$table->head = [
    get_string('services_table_service', 'local_moodlemcp'),
    get_string('services_table_status', 'local_moodlemcp'),
    get_string('services_table_actions', 'local_moodlemcp'),
];
$table->data = [];
foreach ($services as $service) {
    $record = $DB->get_record('external_services', ['shortname' => $service['shortname']], 'id', IGNORE_MISSING);
    $label = local_moodlemcp_get_service_display_name($service['shortname']);
    if ($record) {
        $restoreurl = new moodle_url('/local/moodlemcp/services.php', [
            'action' => 'restore_service',
            'service' => $service['shortname'],
            'sesskey' => sesskey(),
        ]);
        $editfunctions = new moodle_url('/local/moodlemcp/service.php', [
            'service' => $service['shortname'],
        ]);
        $actions = implode(' ', [
            html_writer::tag('a', get_string('editfunctions', 'local_moodlemcp'), [
                'href' => $editfunctions,
                'class' => 'btn btn-secondary',
            ]),
            html_writer::tag('a', get_string('service_restore', 'local_moodlemcp'), [
                'href' => $restoreurl,
                'class' => 'btn btn-secondary',
            ]),
        ]);
        $table->data[] = [
            $label,
            get_string('ok', 'local_moodlemcp'),
            $actions,
        ];
    } else {
        $table->data[] = [
            $label,
            get_string('missing', 'local_moodlemcp'),
            '-',
        ];
    }
}
echo html_writer::table($table);

echo $OUTPUT->footer();
