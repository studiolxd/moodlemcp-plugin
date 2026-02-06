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
 * User management for MoodleMCP services.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/moodlemcp/lib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

local_moodlemcp_ensure_services();

$definitions = local_moodlemcp_get_service_definitions();
$shortnames = array_column($definitions, 'shortname');
$service = optional_param('service', $shortnames[0] ?? '', PARAM_ALPHANUMEXT);
if (!in_array($service, $shortnames, true) && !empty($shortnames)) {
    $service = $shortnames[0];
}

$serviceid = local_moodlemcp_get_service_id($service);
if (!$serviceid) {
    throw new moodle_exception('missingservice', 'local_moodlemcp');
}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/moodlemcp/users.php', ['service' => $service]));
$PAGE->set_title(get_string('adminpage', 'local_moodlemcp'));
$PAGE->set_heading(get_string('adminpage', 'local_moodlemcp'));

$selectoroptions = [
    'service' => $service,
    'context' => $context,
    'preserveselected' => true,
    'autoselectunique' => true,
    'searchanywhere' => true,
    'perpage' => 100,
];
$potentialselector = new \local_moodlemcp\selector\potential_users('addselect', $selectoroptions + [
    'file' => 'local/moodlemcp/classes/selector/potential_users.php',
]);
$existingselector = new \local_moodlemcp\selector\existing_users('removeselect', $selectoroptions + [
    'file' => 'local/moodlemcp/classes/selector/existing_users.php',
]);

$notifications = [];
$license = local_moodlemcp_get_license_key();

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    if ($license === '' || !local_moodlemcp_license_is_valid()) {
        $notifications[] = ['message' => get_string('keys_missing_license', 'local_moodlemcp'), 'type' => 'notifyproblem'];
    } else {
        $selected = $potentialselector->get_selected_users();
        $added = 0;
        $failed = 0;
        if ($selected) {
            $errors = [];
            foreach ($selected as $user) {
                try {
                    $result = local_moodlemcp_assign_user_to_service((int) $user->id, $service);
                    if ($result['ok']) {
                        $added++;
                    } else {
                        $failed++;
                        if (!empty($result['error'])) {
                            $errors[] = $result['error'];
                        }
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = $e->getMessage();
                }
            }
        }
        if ($added > 0) {
            $msg = $added === 1
                ? get_string('users_added_singular', 'local_moodlemcp')
                : get_string('users_added_plural', 'local_moodlemcp', $added);
            $type = 'notifysuccess';
            if (isset($result['first_error'])) {
                $msg .= ' (Debug: ' . s($result['first_error']) . ')';
                $type = 'notifyproblem'; // Change to problem if errors occurred
            }
            $notifications[] = [
                'message' => $msg,
                'type' => $type,
            ];
        }
        if ($failed > 0) {
            $msg = $failed === 1
                ? get_string('users_add_failed_singular', 'local_moodlemcp')
                : get_string('users_add_failed_plural', 'local_moodlemcp', $failed);
            $notifications[] = [
                'message' => $msg,
                'type' => 'notifyproblem',
            ];
            if (!empty($errors)) {
                foreach (array_unique($errors) as $err) {
                    $notifications[] = ['message' => htmlspecialchars($err), 'type' => 'notifyproblem'];
                }
            }
        }
    }
}

if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $selected = $existingselector->get_selected_users();
    $removed = 0;
    if ($selected) {
        foreach ($selected as $user) {
            $DB->delete_records('external_services_users', [
                'externalserviceid' => $serviceid,
                'userid' => $user->id,
            ]);
            local_moodlemcp_recalculate_user_key((int) $user->id);
            $removed++;
        }
    }
    if ($removed > 0) {
        $msg = $removed === 1
            ? get_string('users_removed_singular', 'local_moodlemcp')
            : get_string('users_removed_plural', 'local_moodlemcp', $removed);
        $notifications[] = [
            'message' => $msg,
            'type' => 'notifysuccess',
        ];
    }
}

if (optional_param('syncall', false, PARAM_BOOL) && confirm_sesskey()) {
    if ($license === '' || !local_moodlemcp_license_is_valid()) {
        $notifications[] = ['message' => get_string('keys_missing_license', 'local_moodlemcp'), 'type' => 'notifyproblem'];
    } else {
        $task = new \local_moodlemcp\task\sync_all_users_adhoc();
        $task->set_custom_data(['servicefilter' => $service]);
        $task->set_component('local_moodlemcp');
        \core\task\manager::queue_adhoc_task($task);

        $notifications[] = [
            'message' => get_string('users_sync_queued', 'local_moodlemcp'),
            'type' => 'notifysuccess',
        ];
    }
}
// Context and Page setup moved to top

echo $OUTPUT->header();

local_moodlemcp_print_tabs('users');

foreach ($notifications as $notification) {
    echo $OUTPUT->notification($notification['message'], $notification['type']);
}


$options = [];
foreach ($definitions as $definition) {
    $options[$definition['shortname']] = local_moodlemcp_get_service_display_name($definition['shortname']);
}

$select = new single_select(new moodle_url('/local/moodlemcp/users.php'), 'service', $options, $service);
echo $OUTPUT->render($select);

echo html_writer::start_tag('div', [
    'id' => 'addadmisform',
]);

echo html_writer::tag('h3', get_string('users_manage', 'local_moodlemcp'), ['class' => 'main']);

echo html_writer::start_tag('form', ['id' => 'assignform', 'method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

echo html_writer::start_tag('table', [
    'class' => 'table generaltable groupmanagementtable table-hover',
    'summary' => '',
]);
echo html_writer::start_tag('tbody');
echo html_writer::start_tag('tr');

// Existing Users Cell
echo html_writer::start_tag('td', ['id' => 'existingcell']);
echo html_writer::tag('p', html_writer::tag('label', get_string('users_assigned', 'local_moodlemcp'), ['for' => 'removeselect']));
$existingselector->display();
echo html_writer::end_tag('td');

// Buttons Cell
echo html_writer::start_tag('td', ['id' => 'buttonscell']);
echo html_writer::start_tag('p', ['class' => 'arrow_button']);
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'name' => 'add',
    'id' => 'add',
    'value' => '◀︎ ' . get_string('users_add', 'local_moodlemcp'),
    'title' => get_string('users_add', 'local_moodlemcp'),
    'class' => 'btn btn-secondary',
]);
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'name' => 'remove',
    'id' => 'remove',
    'value' => get_string('users_remove', 'local_moodlemcp') . ' ▶︎',
    'title' => get_string('users_remove', 'local_moodlemcp'),
    'class' => 'btn btn-secondary',
]);
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'name' => 'syncall',
    'id' => 'syncall',
    'value' => get_string('users_sync_all', 'local_moodlemcp'),
    'title' => get_string('users_sync_all', 'local_moodlemcp'),
    'class' => 'btn btn-secondary',
]);
echo html_writer::end_tag('p');
echo html_writer::end_tag('td');

// Potential Users Cell
echo html_writer::start_tag('td', ['id' => 'potentialcell']);
echo html_writer::tag('p', html_writer::tag('label', get_string('users_available', 'local_moodlemcp'), ['for' => 'addselect']));
$potentialselector->display();
echo html_writer::end_tag('td');

echo html_writer::end_tag('tr');
echo html_writer::end_tag('tbody');
echo html_writer::end_tag('table');

echo html_writer::end_tag('form');
echo html_writer::end_tag('div');

echo $OUTPUT->footer();
