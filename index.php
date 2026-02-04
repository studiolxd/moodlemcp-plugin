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
 * License management page for Moodle MCP.
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
$validated = false;

$created = local_moodlemcp_ensure_services();
if ($created > 0) {
    $notifications[] = [
        'message' => get_string('services_created', 'local_moodlemcp', $created),
        'type' => 'notifysuccess',
    ];
}

if ($action === 'save_license' && confirm_sesskey()) {
    $license = optional_param('license_key', '', PARAM_RAW_TRIMMED);
    $result = local_moodlemcp_validate_license($license);

    set_config('license_key', $license, 'local_moodlemcp');
    set_config('license_status', $result['status'], 'local_moodlemcp');
    set_config('license_checked_at', time(), 'local_moodlemcp');

    if ($result['message'] !== '') {
        set_config('license_last_error', $result['message'], 'local_moodlemcp');
    } else {
        unset_config('license_last_error', 'local_moodlemcp');
    }

    if ($result['status'] === 'ok') {
        $notifications[] = [
            'message' => get_string('license_ok', 'local_moodlemcp'),
            'type' => 'notifysuccess',
        ];
    } else {
        $notifications[] = [
            'message' => get_string('license_error', 'local_moodlemcp'),
            'type' => 'notifyproblem',
        ];
    }
    $validated = true;
}


$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/moodlemcp/index.php'));
$PAGE->set_title(get_string('adminpage', 'local_moodlemcp'));
$PAGE->set_heading(get_string('adminpage', 'local_moodlemcp'));

echo $OUTPUT->header();

if (!empty($notifications)) {
    foreach ($notifications as $notification) {
        echo $OUTPUT->notification($notification['message'], $notification['type']);
    }
}

local_moodlemcp_print_tabs('license');

$licensekey = (string) get_config('local_moodlemcp', 'license_key');
$license_status = (string) get_config('local_moodlemcp', 'license_status');
$license_error = (string) get_config('local_moodlemcp', 'license_last_error');
$checkedat = (int) get_config('local_moodlemcp', 'license_checked_at');

if (!$validated) {
    if ($licensekey !== '') {
        $result = local_moodlemcp_validate_license($licensekey);
        set_config('license_status', $result['status'], 'local_moodlemcp');
        set_config('license_checked_at', time(), 'local_moodlemcp');
        if ($result['message'] !== '') {
            set_config('license_last_error', $result['message'], 'local_moodlemcp');
        } else {
            unset_config('license_last_error', 'local_moodlemcp');
        }
        $license_status = $result['status'];
        $license_error = $result['message'];
        $checkedat = (int) get_config('local_moodlemcp', 'license_checked_at');
    } else if ($license_status !== 'missing') {
        set_config('license_status', 'missing', 'local_moodlemcp');
        unset_config('license_last_error', 'local_moodlemcp');
        $license_status = 'missing';
        $license_error = '';
    }
}

if ($license_status !== 'ok') {
    echo $OUTPUT->notification(get_string('license_required', 'local_moodlemcp'), 'warning');
}

echo html_writer::tag('h3', get_string('license_heading', 'local_moodlemcp'));

$statuslabel = get_string('license_status_missing', 'local_moodlemcp');
if ($license_status === 'ok') {
    $statuslabel = get_string('license_status_ok', 'local_moodlemcp');
} else if ($license_status === 'error') {
    $statuslabel = get_string('license_status_error', 'local_moodlemcp');
}

echo html_writer::tag('p', get_string('license_status_label', 'local_moodlemcp', $statuslabel));

if ($checkedat > 0) {
    echo html_writer::tag('p', get_string('license_checked_at', 'local_moodlemcp', userdate($checkedat)));
}

if (!empty($license_error) && $license_status !== 'ok') {
    echo $OUTPUT->notification(s($license_error), 'notifyproblem');
}

echo html_writer::start_tag('form', [
    'method' => 'post',
    'action' => $PAGE->url,
    'style' => 'margin-bottom: 1.5rem;',
]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'save_license']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::tag('label', get_string('license_label', 'local_moodlemcp'), ['for' => 'local_moodlemcp_license']);
echo html_writer::empty_tag('input', [
    'type' => 'password',
    'name' => 'license_key',
    'id' => 'local_moodlemcp_license',
    'value' => $licensekey,
    'size' => 40,
]);
echo html_writer::tag('p', get_string('license_help', 'local_moodlemcp'));
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('license_save', 'local_moodlemcp'),
    'class' => 'btn btn-primary',
]);
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
