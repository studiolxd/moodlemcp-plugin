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
 * Settings page for Moodle MCP.
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
$notifications = [];

// Handle Form Submission manually
if ($action === 'save' && confirm_sesskey()) {
    // Auto-sync settings for each service
    $services = ['admin', 'manager', 'editingteacher', 'teacher', 'student', 'user'];
    foreach ($services as $service) {
        $value = optional_param('auto_sync_' . $service, 0, PARAM_INT);
        set_config('auto_sync_' . $service, $value, 'local_moodlemcp');
    }

    $autoemail = optional_param('auto_email', 0, PARAM_INT);
    $emailsubject = optional_param('email_subject', '', PARAM_TEXT);
    $emailbody = optional_param('email_body', '', PARAM_RAW); // Allow raw for placeholders

    set_config('auto_email', $autoemail, 'local_moodlemcp');
    set_config('email_subject', $emailsubject, 'local_moodlemcp');
    set_config('email_body', $emailbody, 'local_moodlemcp');

    $notifications[] = ['message' => get_string('changes_saved', 'local_moodlemcp'), 'type' => 'notifysuccess'];
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/moodlemcp/settings_page.php'));
$PAGE->set_title(get_string('adminpage', 'local_moodlemcp'));
$PAGE->set_heading(get_string('adminpage', 'local_moodlemcp'));

echo $OUTPUT->header();

local_moodlemcp_print_tabs('settings');

if (!empty($notifications)) {
    foreach ($notifications as $notification) {
        echo $OUTPUT->notification($notification['message'], $notification['type']);
    }
}

// Load current values
$services = ['admin', 'manager', 'editingteacher', 'teacher', 'student', 'user'];
$autosync_values = [];
foreach ($services as $service) {
    $autosync_values[$service] = (int) get_config('local_moodlemcp', 'auto_sync_' . $service);
}

$autoemail = (int) get_config('local_moodlemcp', 'auto_email');
$emailsubject = get_config('local_moodlemcp', 'email_subject');
if ($emailsubject === false) {
    // Default fallback if not set (though install/upgrade usually sets it)
    $emailsubject = get_string('email_subject_default', 'local_moodlemcp');
}
$emailbody = get_config('local_moodlemcp', 'email_body');
if ($emailbody === false) {
    $emailbody = get_string('email_body_default', 'local_moodlemcp');
}

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'save']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);


// Auto Sync - Granular per service
echo html_writer::tag('h4', get_string('auto_sync_section', 'local_moodlemcp'), ['class' => 'mt-4 mb-3']);

foreach ($services as $service) {
    echo html_writer::start_div('form-item row mb-3');
    echo html_writer::start_div('col-md-9 offset-md-3');
    echo html_writer::start_div('form-check');
    $fieldname = 'auto_sync_' . $service;
    echo html_writer::checkbox($fieldname, 1, $autosync_values[$service] == 1, '', ['class' => 'form-check-input', 'id' => 'id_' . $fieldname]);
    echo html_writer::tag('label', get_string($fieldname, 'local_moodlemcp'), ['class' => 'form-check-label', 'for' => 'id_' . $fieldname]);
    echo html_writer::end_div(); // form-check
    echo html_writer::div(get_string($fieldname . '_desc', 'local_moodlemcp'), 'form-text text-muted');
    echo html_writer::end_div(); // col
    echo html_writer::end_div(); // form-item
}

// Email section
echo html_writer::tag('h4', get_string('email_section', 'local_moodlemcp'), ['class' => 'mt-4 mb-3']);

// Auto Email
echo html_writer::start_div('form-item row mb-3');
echo html_writer::start_div('col-md-9 offset-md-3');
echo html_writer::start_div('form-check');
echo html_writer::checkbox('auto_email', 1, $autoemail == 1, '', ['class' => 'form-check-input']);
echo html_writer::tag('label', get_string('auto_email', 'local_moodlemcp'), ['class' => 'form-check-label', 'for' => 'auto_email']);
echo html_writer::end_div(); // form-check
echo html_writer::div(get_string('auto_email_desc', 'local_moodlemcp'), 'form-text text-muted');
echo html_writer::end_div();
echo html_writer::end_div();

// Email Subject
echo html_writer::start_div('form-item row mb-3');
echo html_writer::tag('label', get_string('email_subject', 'local_moodlemcp'), ['class' => 'col-md-3 col-form-label', 'for' => 'id_email_subject']);
echo html_writer::start_div('col-md-9');
echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'email_subject', 'value' => $emailsubject, 'class' => 'form-control', 'id' => 'id_email_subject']);
echo html_writer::div(get_string('email_subject_desc', 'local_moodlemcp'), 'form-text text-muted');
echo html_writer::end_div();
echo html_writer::end_div();

// Email Body
echo html_writer::start_div('form-item row mb-3');
echo html_writer::tag('label', get_string('email_body', 'local_moodlemcp'), ['class' => 'col-md-3 col-form-label', 'for' => 'id_email_body']);
echo html_writer::start_div('col-md-9');
echo html_writer::tag('textarea', s($emailbody), ['name' => 'email_body', 'class' => 'form-control', 'id' => 'id_email_body', 'rows' => 10]);
echo html_writer::div(get_string('email_body_desc', 'local_moodlemcp'), 'form-text text-muted');
echo html_writer::end_div();
echo html_writer::end_div();

// Submit
echo html_writer::start_div('form-item row');
echo html_writer::start_div('col-md-9 offset-md-3');
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('savechanges'), 'class' => 'btn btn-primary']);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_tag('form');

echo $OUTPUT->footer();
