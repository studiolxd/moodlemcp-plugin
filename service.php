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
 * Edit functions for a MoodleMCP external service.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$shortname = required_param('service', PARAM_ALPHANUMEXT);

$definitions = local_moodlemcp_get_service_definitions();
$validshortnames = array_column($definitions, 'shortname');
if (!in_array($shortname, $validshortnames, true)) {
    throw new moodle_exception('invalidservice', 'local_moodlemcp');
}

local_moodlemcp_ensure_services();

$service = $DB->get_record('external_services', ['shortname' => $shortname], '*', IGNORE_MISSING);
if (!$service) {
    throw new moodle_exception('missingservice', 'local_moodlemcp');
}

$current = $DB->get_records_menu('external_services_functions', ['externalserviceid' => $service->id], '', 'functionname,functionname');
$choices = local_moodlemcp_get_external_function_choices();

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/moodlemcp/service.php', ['service' => $shortname]));
$PAGE->set_title(get_string('editfunctions', 'local_moodlemcp'));
$PAGE->set_heading(get_string('editfunctions', 'local_moodlemcp'));

$form = new \local_moodlemcp\form\service_functions_form(null, [
    'shortname' => $shortname,
    'choices' => $choices,
]);
$form->set_data([
    'service' => $shortname,
    'functions' => array_keys($current),
]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/moodlemcp/index.php'));
}

if ($data = $form->get_data()) {
    $functions = $data->functions ?? [];
    if (!is_array($functions)) {
        $functions = [$functions];
    }
    local_moodlemcp_set_service_functions((int) $service->id, $functions);
    redirect(
        new moodle_url('/local/moodlemcp/index.php'),
        get_string('service_updated', 'local_moodlemcp', $shortname),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}


echo $OUTPUT->header();

local_moodlemcp_print_tabs('services');



echo $OUTPUT->heading(get_string('service_edit_heading', 'local_moodlemcp', $shortname), 3);

$form->display();

echo $OUTPUT->footer();
