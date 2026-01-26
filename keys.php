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
 * Keys management page for Moodle MCP.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/moodlemcp/keys.php'));
$PAGE->set_title(get_string('adminpage', 'local_moodlemcp'));
$PAGE->set_heading(get_string('adminpage', 'local_moodlemcp'));

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$mcpkey = optional_param('mcpkey', '', PARAM_RAW_TRIMMED);
$token = optional_param('token', '', PARAM_RAW_TRIMMED);
$mcpurl = optional_param('mcpurl', '', PARAM_RAW_TRIMMED);
$expireson = optional_param('expireson', '', PARAM_RAW_TRIMMED);
$sentat = optional_param('sentat', '', PARAM_RAW_TRIMMED);
$notifications = [];

if ($action !== '' && confirm_sesskey()) {
    if ($action === 'revoke') {
        $result = local_moodlemcp_panel_revoke_key($mcpkey);
        if ($result['ok']) {
            $user = local_moodlemcp_get_user_by_token($token);
            if ($user) {
                // When revoked, remove user from MoodleMCP services
                local_moodlemcp_revoke_moodlemcp_tokens((int) $user->id);
                // Also remove service assignments
                $serviceids = local_moodlemcp_get_moodlemcp_service_ids();
                if (!empty($serviceids)) {
                    list($insql, $params) = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED);
                    $params['userid'] = $user->id;
                    $DB->delete_records_select('external_services_users', "userid = :userid AND externalserviceid {$insql}", $params);
                }
            } else {
                local_moodlemcp_revoke_token_value($token);
            }
            $notifications[] = ['message' => get_string('key_revoked', 'local_moodlemcp'), 'type' => 'notifysuccess'];
        } else {
            $notifications[] = ['message' => get_string('key_revoke_failed', 'local_moodlemcp'), 'type' => 'notifyproblem'];
        }
    } else if ($action === 'delete') {
        // Delete action usually means remove from list, but API has separate delete endpoint?
        // User didn't specify Delete endpoint in this request, but in previous code distinct Delete existed.
        // Retain existing deletion logic if untouchedmains similar or maps to Revoke if redundant?
        // Keeping as is for now unless specified.
        $result = local_moodlemcp_panel_delete_key($mcpkey);
        if ($result['ok']) {
            $user = local_moodlemcp_get_user_by_token($token);
            if ($user) {
                // Remove tokens
                local_moodlemcp_revoke_moodlemcp_tokens((int) $user->id);
                // Also remove service assignments (requested by user)
                $serviceids = local_moodlemcp_get_moodlemcp_service_ids();
                if (!empty($serviceids)) {
                    list($insql, $params) = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED);
                    $params['userid'] = $user->id;
                    $DB->delete_records_select('external_services_users', "userid = :userid AND externalserviceid {$insql}", $params);
                }
            } else {
                local_moodlemcp_revoke_token_value($token);
            }
            $notifications[] = ['message' => get_string('key_deleted', 'local_moodlemcp'), 'type' => 'notifysuccess'];
        } else {
            $notifications[] = ['message' => get_string('key_delete_failed', 'local_moodlemcp'), 'type' => 'notifyproblem'];
        }
    } else if ($action === 'suspend') {
        $result = local_moodlemcp_panel_suspend_key($mcpkey, true);
        if ($result['ok']) {
            $notifications[] = ['message' => get_string('key_suspended', 'local_moodlemcp'), 'type' => 'notifysuccess'];
        } else {
            $notifications[] = ['message' => get_string('key_suspend_failed', 'local_moodlemcp'), 'type' => 'notifyproblem'];
        }
    } else if ($action === 'activate') {
        $result = local_moodlemcp_panel_suspend_key($mcpkey, false);
        if ($result['ok']) {
            $notifications[] = ['message' => get_string('key_activated', 'local_moodlemcp'), 'type' => 'notifysuccess'];
        } else {
            $notifications[] = ['message' => get_string('key_activate_failed', 'local_moodlemcp'), 'type' => 'notifyproblem'];
        }
    } else if ($action === 'send') {
        $user = local_moodlemcp_get_user_by_token($token);
        if ($user && local_moodlemcp_send_key_email($user, $mcpkey, $mcpurl)) {
            local_moodlemcp_panel_mark_sent($mcpkey);
            $notifications[] = ['message' => get_string('key_sent', 'local_moodlemcp'), 'type' => 'notifysuccess'];
        } else {
            $notifications[] = ['message' => get_string('key_send_failed', 'local_moodlemcp'), 'type' => 'notifyproblem'];
        }
    } else if ($action === 'regenerate') {
        $user = local_moodlemcp_get_user_by_token($token);
        if (!$user) {
            $notifications[] = ['message' => get_string('key_regen_failed', 'local_moodlemcp'), 'type' => 'notifyproblem'];
        } else {
            $roles = local_moodlemcp_get_effective_roles($user->id);
            $primaryrole = local_moodlemcp_primary_role_for_roles($roles);
            $serviceid = local_moodlemcp_get_service_id(local_moodlemcp_service_for_role($primaryrole));
            if (!$serviceid) {
                $notifications[] = ['message' => get_string('key_regen_failed', 'local_moodlemcp'), 'type' => 'notifyproblem'];
            } else {
                // Delete the old key from the panel first to avoid duplicates
                if ($mcpkey !== '') {
                    local_moodlemcp_panel_delete_key($mcpkey);
                }

                local_moodlemcp_authorize_user_for_service($user->id, $serviceid);
                local_moodlemcp_revoke_moodlemcp_tokens($user->id);
                $newtoken = local_moodlemcp_rotate_user_token($user->id, $serviceid, 0);
                $result = local_moodlemcp_panel_create_key($newtoken, $primaryrole, $expireson !== '' ? $expireson : null);
                if ($result['ok'] && !empty($result['data']['mcpKey']) && !empty($result['data']['mcpUrl'])) {
                    $mcpkey = (string) $result['data']['mcpKey'];
                    $mcpurl = (string) $result['data']['mcpUrl'];
                    if ((int) get_config('local_moodlemcp', 'auto_email') === 1 && $sentat === '') {
                        if (local_moodlemcp_send_key_email($user, $mcpkey, $mcpurl)) {
                            local_moodlemcp_panel_mark_sent($mcpkey);
                        }
                    }
                    $notifications[] = ['message' => get_string('key_regenerated', 'local_moodlemcp'), 'type' => 'notifysuccess'];
                } else {
                    $notifications[] = ['message' => get_string('key_regen_failed', 'local_moodlemcp'), 'type' => 'notifyproblem'];
                }
            }
        }
    }
}



echo $OUTPUT->header();

local_moodlemcp_print_tabs('keys');

if (!empty($notifications)) {
    foreach ($notifications as $notification) {
        echo $OUTPUT->notification($notification['message'], $notification['type']);
    }
}

$licensekey = local_moodlemcp_get_license_key();
if ($licensekey === '' || !local_moodlemcp_license_is_valid()) {
    echo $OUTPUT->notification(get_string('keys_missing_license', 'local_moodlemcp'), 'notifyproblem');
    echo $OUTPUT->footer();
    return;
}

$result = local_moodlemcp_panel_list_keys();
if (!$result['ok'] || !isset($result['data']['keys']) || !is_array($result['data']['keys'])) {
    echo $OUTPUT->notification(get_string('keys_load_failed', 'local_moodlemcp'), 'notifyproblem');
    echo $OUTPUT->footer();
    return;
}

$keys = $result['data']['keys'];
$keys = array_values(array_filter($keys, static function (array $key): bool {
    return (($key['createdBy'] ?? '') === 'moodle');
}));

if (empty($keys)) {
    echo $OUTPUT->notification(get_string('keys_empty', 'local_moodlemcp'), 'notifyinfo');
    echo $OUTPUT->footer();
    return;
}

// Pagination
$page = optional_param('page', 0, PARAM_INT);
$perpage = 20;
$totalkeys = count($keys);
$totalpages = ceil($totalkeys / $perpage);
$start = $page * $perpage;
$keys_page = array_slice($keys, $start, $perpage);

$table = new html_table();
$table->head = [
    get_string('keys_user', 'local_moodlemcp'),
    get_string('keys_role', 'local_moodlemcp'),
    get_string('keys_status', 'local_moodlemcp'),
    get_string('keys_expires', 'local_moodlemcp'),
    get_string('keys_sent', 'local_moodlemcp'),
    get_string('keys_actions', 'local_moodlemcp'),
];
$table->data = [];

foreach ($keys_page as $key) {
    $name = isset($key['name']) ? (string) $key['name'] : '';
    $rawrole = $key['moodleRoles'] ?? $key['moodleRole'] ?? [];

    // Translate roles to human-friendly names
    $roles_array = is_array($rawrole) ? $rawrole : [$rawrole];
    $translated_roles = [];
    foreach ($roles_array as $r) {
        // The role from the panel already has the format 'moodlemcp_xxx'
        // So we pass it directly to get_service_display_name
        $translated_roles[] = local_moodlemcp_get_service_display_name($r);
    }
    $role = implode(', ', $translated_roles);


    $status = isset($key['status']) ? (string) $key['status'] : '';

    // Translate status
    $status_key = 'key_status_' . $status;
    if (get_string_manager()->string_exists($status_key, 'local_moodlemcp')) {
        $status_display = get_string($status_key, 'local_moodlemcp');
    } else {
        $status_display = $status;
    }

    $expiresonraw = !empty($key['expiresOn']) ? (string) $key['expiresOn'] : '';
    $sentatraw = !empty($key['sentAt']) ? (string) $key['sentAt'] : '';

    // Format dates (only date, no time)
    $expires = '-';
    if ($expiresonraw !== '') {
        try {
            $dt = new DateTime($expiresonraw);
            $expires = userdate($dt->getTimestamp(), get_string('strftimedatefullshort', 'langconfig'));
        } catch (Exception $e) {
            $expires = $expiresonraw;
        }
    }

    $sent = '-';
    if ($sentatraw !== '') {
        try {
            $dt = new DateTime($sentatraw);
            $sent = userdate($dt->getTimestamp(), get_string('strftimedatefullshort', 'langconfig'));
        } catch (Exception $e) {
            $sent = $sentatraw;
        }
    }

    $mcpkey = isset($key['mcpKey']) ? (string) $key['mcpKey'] : '';
    $mcpurl = isset($key['mcpUrl']) ? (string) $key['mcpUrl'] : '';
    $tokenvalue = isset($key['moodleToken']) ? (string) $key['moodleToken'] : '';
    $sentatvalue = $sentatraw;

    $actions = [];

    // Revoked keys can only be deleted
    if ($status === 'revoked') {
        $actions[] = local_moodlemcp_render_key_action('delete', get_string('key_delete', 'local_moodlemcp'), $mcpkey, $tokenvalue, $mcpurl, $expiresonraw, $sentatvalue);
    } else {
        // All other statuses allow full actions
        $actions[] = local_moodlemcp_render_key_action('regenerate', get_string('key_regenerate', 'local_moodlemcp'), $mcpkey, $tokenvalue, $mcpurl, $expiresonraw, $sentatvalue);

        if ($status === 'suspended') {
            $actions[] = local_moodlemcp_render_key_action('activate', get_string('key_activate', 'local_moodlemcp'), $mcpkey, $tokenvalue, $mcpurl, $expiresonraw, $sentatvalue);
        } else if ($status === 'active') {
            $actions[] = local_moodlemcp_render_key_action('suspend', get_string('key_suspend', 'local_moodlemcp'), $mcpkey, $tokenvalue, $mcpurl, $expiresonraw, $sentatvalue);
        }

        $actions[] = local_moodlemcp_render_key_action('revoke', get_string('key_revoke', 'local_moodlemcp'), $mcpkey, $tokenvalue, $mcpurl, $expiresonraw, $sentatvalue);
        $actions[] = local_moodlemcp_render_key_action('delete', get_string('key_delete', 'local_moodlemcp'), $mcpkey, $tokenvalue, $mcpurl, $expiresonraw, $sentatvalue);

        if ($mcpkey !== '' && $mcpurl !== '' && $tokenvalue !== '') {
            $sendlabel = $sentatvalue === '' ? get_string('key_send', 'local_moodlemcp') : get_string('key_resend', 'local_moodlemcp');
            $actions[] = local_moodlemcp_render_key_action('send', $sendlabel, $mcpkey, $tokenvalue, $mcpurl, $expiresonraw, $sentatvalue);
        }
    }

    $table->data[] = [
        s($name),
        s($role),
        s($status_display),
        s($expires),
        s($sent),
        implode(' ', $actions),
    ];
}

echo html_writer::table($table);

// Pagination bar
echo $OUTPUT->paging_bar($totalkeys, $page, $perpage, new moodle_url('/local/moodlemcp/keys.php'));

echo $OUTPUT->footer();

/**
 * Renders a single POST action button for a key row.
 *
 * @param string $action
 * @param string $label
 * @param string $mcpkey
 * @param string $token
 * @param string $mcpurl
 * @param string $expireson
 * @param string $sentat
 * @return string
 */
function local_moodlemcp_render_key_action(
    string $action,
    string $label,
    string $mcpkey,
    string $token,
    string $mcpurl,
    string $expireson,
    string $sentat
): string {
    $form = html_writer::start_tag('form', ['method' => 'post', 'action' => (new moodle_url('/local/moodlemcp/keys.php'))->out(false)]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => $action]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mcpkey', 'value' => $mcpkey]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'token', 'value' => $token]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mcpurl', 'value' => $mcpurl]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'expireson', 'value' => $expireson]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sentat', 'value' => $sentat]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    $form .= html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-secondary', 'value' => $label]);
    $form .= html_writer::end_tag('form');
    return $form;
}
