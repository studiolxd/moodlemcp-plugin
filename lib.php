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
 * Library functions for Moodle MCP.
 *
 * @package    local_moodlemcp
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the base URL for the MoodleMCP API.
 *
 * @return string
 */
function local_moodlemcp_api_base_url(): string {
    return 'https://moodlemcp.com';
}

/**
 * Returns the list of external services created by this plugin.
 *
 * @return array[]
 */
function local_moodlemcp_get_service_definitions(): array {
    return [
        [
            'shortname' => 'moodlemcp_admin',
            'name' => 'moodlemcp_admin',
            'functions' => [],
        ],
        [
            'shortname' => 'moodlemcp_manager',
            'name' => 'moodlemcp_manager',
            'functions' => [],
        ],
        [
            'shortname' => 'moodlemcp_editingteacher',
            'name' => 'moodlemcp_editingteacher',
            'functions' => [],
        ],
        [
            'shortname' => 'moodlemcp_teacher',
            'name' => 'moodlemcp_teacher',
            'functions' => [],
        ],
        [
            'shortname' => 'moodlemcp_student',
            'name' => 'moodlemcp_student',
            'functions' => [],
        ],
        [
            'shortname' => 'moodlemcp_user',
            'name' => 'moodlemcp_user',
            'functions' => [],
        ],
    ];
}

/**
 * Ensures the MoodleMCP services exist (creates missing ones only).
 *
 * @return int Number of services created.
 */
function local_moodlemcp_ensure_services(): int {
    global $DB;

    $created = 0;
    $now = time();

    foreach (local_moodlemcp_get_service_definitions() as $service) {
        if ($DB->record_exists('external_services', ['shortname' => $service['shortname']])) {
            continue;
        }

        $record = new stdClass();
        $record->name = $service['name'];
        $record->shortname = $service['shortname'];
        $record->enabled = 1;
        $record->restrictedusers = 1;
        $record->requiredcapability = '';
        $record->component = 'local_moodlemcp';
        $record->timecreated = $now;
        $record->timemodified = $now;

        $serviceid = $DB->insert_record('external_services', $record);
        local_moodlemcp_set_service_functions($serviceid, $service['functions']);
        $created++;
    }

    return $created;
}

/**
 * Restores a service's function list to the baseline definitions.
 *
 * @param string $shortname
 * @return bool
 */
function local_moodlemcp_restore_service_baseline(string $shortname): bool {
    global $DB;

    $definition = null;
    foreach (local_moodlemcp_get_service_definitions() as $service) {
        if ($service['shortname'] === $shortname) {
            $definition = $service;
            break;
        }
    }
    if ($definition === null) {
        return false;
    }

    $record = $DB->get_record('external_services', ['shortname' => $shortname], 'id', IGNORE_MISSING);
    if (!$record) {
        return false;
    }

    local_moodlemcp_set_service_functions((int) $record->id, $definition['functions']);
    return true;
}

/**
 * Updates the function whitelist for a service.
 *
 * @param int $serviceid
 * @param string[] $functions
 * @return void
 */
function local_moodlemcp_set_service_functions(int $serviceid, array $functions): void {
    global $DB;

    $DB->delete_records('external_services_functions', ['externalserviceid' => $serviceid]);
    foreach ($functions as $functionname) {
        $sf = new stdClass();
        $sf->externalserviceid = $serviceid;
        $sf->functionname = $functionname;
        $DB->insert_record('external_services_functions', $sf);
    }
}

/**
 * Returns a list of available external functions for form selection.
 *
 * @return array<string,string>
 */
function local_moodlemcp_get_external_function_choices(): array {
    global $DB;

    $records = $DB->get_records('external_functions', null, 'name ASC', 'name,component');
    $choices = [];
    foreach ($records as $record) {
        $component = $record->component !== '' ? $record->component : 'core';
        $choices[$record->name] = $record->name . ' (' . $component . ')';
    }

    return $choices;
}

/**
 * Determines whether a user has any of the given role shortnames at system context.
 *
 * @param int $userid
 * @param string[] $shortnames
 * @return bool
 */
function local_moodlemcp_user_has_system_role(int $userid, array $shortnames): bool {
    global $DB;

    if (empty($shortnames)) {
        return false;
    }

    $systemcontext = context_system::instance();
    list($insql, $params) = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    $params['contextid'] = $systemcontext->id;

    $sql = "SELECT 1
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
             WHERE ra.userid = :userid
               AND ra.contextid = :contextid
               AND r.shortname {$insql}";

    return $DB->record_exists_sql($sql, $params);
}

/**
 * Determines whether a user has any of the given role shortnames in any course context.
 *
 * @param int $userid
 * @param string[] $shortnames
 * @return bool
 */
function local_moodlemcp_user_has_course_role(int $userid, array $shortnames): bool {
    global $DB;

    if (empty($shortnames)) {
        return false;
    }

    list($insql, $params) = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    $params['contextlevel'] = CONTEXT_COURSE;

    $sql = "SELECT 1
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
              JOIN {context} c ON c.id = ra.contextid
             WHERE ra.userid = :userid
               AND r.shortname {$insql}
               AND c.contextlevel = :contextlevel";

    return $DB->record_exists_sql($sql, $params);
}

/**
 * Calculates the effective role array for a Moodle user.
 *
 * @param int $userid
 * @return string[]
 */
function local_moodlemcp_get_effective_roles(int $userid): array {
    $roles = [];

    if (is_siteadmin($userid)) {
        $roles[] = 'admin';
    }

    // Check for manager role at system level OR course level
    if (
        local_moodlemcp_user_has_system_role($userid, ['manager']) ||
        local_moodlemcp_user_has_course_role($userid, ['manager'])
    ) {
        $roles[] = 'manager';
    }

    $haseditingteacher = local_moodlemcp_user_has_course_role($userid, ['editingteacher']);
    if ($haseditingteacher) {
        $roles[] = 'editingteacher';
    }

    $hasnonediting = local_moodlemcp_user_has_course_role($userid, ['teacher', 'noneditingteacher']);
    if ($hasnonediting) {
        $roles[] = 'teacher';
    }

    if (local_moodlemcp_user_has_course_role($userid, ['student'])) {
        $roles[] = 'student';
    }

    $hascourse = in_array('editingteacher', $roles, true) ||
        in_array('teacher', $roles, true) ||
        in_array('student', $roles, true);

    if (!$hascourse) {
        $roles[] = 'user';
    }

    return array_values(array_unique($roles));
}

/**
 * Returns the role name from a MoodleMCP service shortname.
 *
 * @param string $shortname
 * @return string
 */
function local_moodlemcp_role_from_service(string $shortname): string {
    if (substr($shortname, 0, 10) === 'moodlemcp_') {
        return substr($shortname, 10);
    }
    if (substr($shortname, 0, 7) === 'moodle_') {
        return substr($shortname, 7);
    }

    return $shortname;
}

/**
 * Checks if auto-sync is enabled for a specific service.
 *
 * @param string $service Service shortname (e.g., 'moodlemcp_admin', 'moodlemcp_teacher')
 * @return bool
 */
function local_moodlemcp_is_auto_sync_enabled_for_service(string $service): bool {
    $role = local_moodlemcp_role_from_service($service);
    $config_key = 'auto_sync_' . $role;
    return (int) get_config('local_moodlemcp', $config_key) === 1;
}

/**
 * Gets the human-readable name for a service.
 *
 * @param string $service Service shortname (e.g., 'moodlemcp_admin')
 * @return string
 */
function local_moodlemcp_get_service_display_name(string $service): string {
    $role = local_moodlemcp_role_from_service($service);
    $string_key = 'service_name_' . $role;

    if (get_string_manager()->string_exists($string_key, 'local_moodlemcp')) {
        return get_string($string_key, 'local_moodlemcp');
    }

    // Fallback to service shortname
    return $service;
}


/**
 * Determines whether a user is eligible for a MoodleMCP service.
 *
 * @param int $userid
 * @param string $shortname
 * @return bool
 */
function local_moodlemcp_user_is_eligible_for_service(int $userid, string $shortname): bool {
    $role = local_moodlemcp_role_from_service($shortname);

    switch ($role) {
        case 'admin':
            return is_siteadmin($userid);
        case 'manager':
            return local_moodlemcp_user_has_system_role($userid, ['manager']) ||
                local_moodlemcp_user_has_course_role($userid, ['manager']);
        case 'editingteacher':
            return local_moodlemcp_user_has_course_role($userid, ['editingteacher']);
        case 'teacher':
            // Checks for non-editing teacher role (legacy names: teacher, noneditingteacher)
            return local_moodlemcp_user_has_course_role($userid, ['teacher', 'noneditingteacher']);
        case 'student':
            return local_moodlemcp_user_has_course_role($userid, ['student']);
        case 'user':
            return true;
        default:
            return false;
    }
}

/**
 * Maps a role name to the corresponding MoodleMCP service shortname.
 *
 * @param string $role
 * @return string
 */
function local_moodlemcp_service_for_role(string $role): string {
    return 'moodlemcp_' . $role;
}

/**
 * Returns the service shortnames required for the given roles.
 *
 * @param string[] $roles
 * @return string[]
 */
function local_moodlemcp_services_for_roles(array $roles): array {
    $services = [];
    foreach ($roles as $role) {
        $services[] = local_moodlemcp_service_for_role($role);
    }

    return array_values(array_unique($services));
}

/**
 * Picks the primary service shortname for a user based on role priority.
 *
 * @param string[] $roles
 * @return string
 */
function local_moodlemcp_primary_service_for_roles(array $roles): string {
    return local_moodlemcp_service_for_role(local_moodlemcp_primary_role_for_roles($roles));
}

/**
 * Picks the primary role name for a user based on role priority.
 *
 * @param string[] $roles
 * @return string
 */
function local_moodlemcp_primary_role_for_roles(array $roles): string {
    $priority = ['admin', 'manager', 'editingteacher', 'teacher', 'student', 'user'];
    foreach ($priority as $role) {
        if (in_array($role, $roles, true)) {
            return $role;
        }
    }

    return 'user';
}

/**
 * Ensures a user is authorized for a service.
 *
 * @param int $userid
 * @param int $serviceid
 * @return void
 */
function local_moodlemcp_authorize_user_for_service(int $userid, int $serviceid): void {
    global $DB;

    if (
        $DB->record_exists('external_services_users', [
            'externalserviceid' => $serviceid,
            'userid' => $userid,
        ])
    ) {
        return;
    }

    $record = new stdClass();
    $record->externalserviceid = $serviceid;
    $record->userid = $userid;
    $DB->insert_record('external_services_users', $record);
}

/**
 * Removes a user from all MoodleMCP services except the given service id.
 *
 * @param int $userid
 * @param int $serviceid
 * @return void
 */
function local_moodlemcp_remove_user_from_other_services(int $userid, int $serviceid): void {
    global $DB;

    $serviceids = local_moodlemcp_get_moodlemcp_service_ids();
    if (empty($serviceids)) {
        return;
    }

    $serviceids = array_values(array_diff($serviceids, [$serviceid]));
    if (empty($serviceids)) {
        return;
    }

    list($insql, $params) = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    $DB->delete_records_select('external_services_users', "userid = :userid AND externalserviceid {$insql}", $params);
}

/**
 * Revokes tokens for a user and service.
 *
 * @param int $userid
 * @param int $serviceid
 * @return void
 */
function local_moodlemcp_revoke_user_tokens(int $userid, int $serviceid): void {
    global $DB;

    $DB->delete_records('external_tokens', [
        'userid' => $userid,
        'externalserviceid' => $serviceid,
    ]);
}

/**
 * Revokes all MoodleMCP tokens for a user except the given service id.
 *
 * @param int $userid
 * @param int $serviceid
 * @return void
 */
function local_moodlemcp_revoke_other_service_tokens(int $userid, int $serviceid): void {
    global $DB;

    $serviceids = local_moodlemcp_get_moodlemcp_service_ids();
    if (empty($serviceids)) {
        return;
    }

    $serviceids = array_values(array_diff($serviceids, [$serviceid]));
    if (empty($serviceids)) {
        return;
    }

    list($insql, $params) = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    $DB->delete_records_select('external_tokens', "userid = :userid AND externalserviceid {$insql}", $params);
}

/**
 * Revokes all MoodleMCP tokens for a user across all MoodleMCP services.
 *
 * @param int $userid
 * @return void
 */
function local_moodlemcp_revoke_moodlemcp_tokens(int $userid): void {
    global $DB;

    $serviceids = local_moodlemcp_get_moodlemcp_service_ids();
    if (empty($serviceids)) {
        return;
    }

    list($insql, $params) = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    $DB->delete_records_select('external_tokens', "userid = :userid AND externalserviceid {$insql}", $params);
}

/**
 * Creates a new Moodle web service token for a user/service, rotating any previous one.
 *
 * @param int $userid
 * @param int $serviceid
 * @param int $validuntil
 * @return string
 */
function local_moodlemcp_rotate_user_token(int $userid, int $serviceid, int $validuntil = 0): string {
    global $CFG;

    require_once($CFG->dirroot . '/webservice/lib.php');
    require_once($CFG->libdir . '/externallib.php');

    $systemcontext = context_system::instance();

    local_moodlemcp_revoke_user_tokens($userid, $serviceid);

    return external_generate_token(EXTERNAL_TOKEN_PERMANENT, $serviceid, $userid, $systemcontext, $validuntil, '');
}

/**
 * Returns an existing token for a user/service pair.
 *
 * @param int $userid
 * @param int $serviceid
 * @return string|null
 */
function local_moodlemcp_get_user_service_token(int $userid, int $serviceid): ?string {
    global $DB;

    $record = $DB->get_record('external_tokens', [
        'userid' => $userid,
        'externalserviceid' => $serviceid,
    ], 'token', IGNORE_MISSING);

    return $record ? (string) $record->token : null;
}

/**
 * Revokes a Moodle token by its raw value.
 *
 * @param string $token
 * @return void
 */
function local_moodlemcp_revoke_token_value(string $token): void {
    global $DB;

    if ($token === '') {
        return;
    }

    $DB->delete_records('external_tokens', ['token' => $token]);
}

/**
 * Returns a user record based on a Moodle web service token.
 *
 * @param string $token
 * @return stdClass|null
 */
function local_moodlemcp_get_user_by_token(string $token): ?stdClass {
    global $DB;

    if ($token === '') {
        return null;
    }

    $tokerecord = $DB->get_record('external_tokens', ['token' => $token], 'userid', IGNORE_MISSING);
    if (!$tokerecord) {
        return null;
    }

    return $DB->get_record('user', ['id' => $tokerecord->userid, 'deleted' => 0], '*', IGNORE_MISSING) ?: null;
}

/**
 * Returns the external service id for a MoodleMCP service shortname.
 *
 * @param string $shortname
 * @return int|null
 */
function local_moodlemcp_get_service_id(string $shortname): ?int {
    global $DB;

    $record = $DB->get_record('external_services', ['shortname' => $shortname], 'id', IGNORE_MISSING);
    return $record ? (int) $record->id : null;
}

/**
 * Returns all MoodleMCP service ids that exist.
 *
 * @return int[]
 */
function local_moodlemcp_get_moodlemcp_service_ids(): array {
    global $DB;

    $shortnames = array_column(local_moodlemcp_get_service_definitions(), 'shortname');
    if (empty($shortnames)) {
        return [];
    }

    list($insql, $params) = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED);
    $records = $DB->get_records_select('external_services', "shortname {$insql}", $params, '', 'id');

    $ids = [];
    foreach ($records as $record) {
        $ids[] = (int) $record->id;
    }

    return $ids;
}

/**
 * Deletes MCP keys for a user in the panel and removes their Moodle tokens.
 *
 * @param int $userid
 * @return void
 */
function local_moodlemcp_delete_user_keys(int $userid): void {
    global $DB;

    $serviceids = local_moodlemcp_get_moodlemcp_service_ids();
    if (empty($serviceids)) {
        return;
    }

    list($insql, $params) = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    $tokens = $DB->get_records_select('external_tokens', "userid = :userid AND externalserviceid {$insql}", $params);
    if (empty($tokens)) {
        return;
    }

    $tokenvalues = [];
    foreach ($tokens as $token) {
        if (!empty($token->token)) {
            $tokenvalues[] = (string) $token->token;
        }
    }

    $keymap = [];
    $list = local_moodlemcp_panel_list_keys();
    if ($list['ok'] && !empty($list['data']['keys']) && is_array($list['data']['keys'])) {
        foreach ($list['data']['keys'] as $key) {
            if (!empty($key['moodleToken']) && !empty($key['mcpKey'])) {
                $keymap[(string) $key['moodleToken']] = (string) $key['mcpKey'];
            }
        }
    }

    foreach ($tokenvalues as $tokenvalue) {
        if (isset($keymap[$tokenvalue])) {
            local_moodlemcp_panel_delete_key($keymap[$tokenvalue]);
        }
        local_moodlemcp_revoke_token_value($tokenvalue);
    }
}

/**
 * Recalculates and updates the user's MCP key based on their remaining service assignments.
 *
 * Use this after removing a user from a service to ensure their key downgrades to
 * the next available role or is revoked if no services remain.
 *
 * @param int $userid
 * @return array{ok:bool,data:array|null,error:string|null}
 */
function local_moodlemcp_recalculate_user_key(int $userid): array {
    global $DB;

    // 1. Get all MoodleMCP services
    $serviceids = local_moodlemcp_get_moodlemcp_service_ids();
    if (empty($serviceids)) {
        return ['ok' => false, 'data' => null, 'error' => 'no_services_defined'];
    }

    // 2. Find which of these the user is assigned to
    list($insql, $params) = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    $assignments = $DB->get_records_select(
        'external_services_users',
        "userid = :userid AND externalserviceid {$insql}",
        $params
    );

    if (empty($assignments)) {
        // User has no remaining MoodleMCP services. DELETE everything (cleanup).
        local_moodlemcp_delete_user_keys($userid);
        // Return OK since deletion was successful/intended
        return ['ok' => true, 'data' => null, 'error' => 'key_deleted'];
    }

    // 3. Determine the best remaining role
    $assignedserviceids = array_column($assignments, 'externalserviceid');
    $definitions = local_moodlemcp_get_service_definitions();

    $userroles = [];
    foreach ($definitions as $def) {
        $sid = local_moodlemcp_get_service_id($def['shortname']);
        if ($sid && in_array($sid, $assignedserviceids)) {
            $userroles[] = local_moodlemcp_role_from_service($def['shortname']);
        }
    }

    $primaryrole = local_moodlemcp_primary_role_for_roles($userroles);
    $targetservice = local_moodlemcp_service_for_role($primaryrole);
    $targetserviceid = local_moodlemcp_get_service_id($targetservice);

    if (!$targetserviceid) {
        return ['ok' => false, 'data' => null, 'error' => 'no_target_service'];
    }

    // 4. Ensure token points to this target service
    // Important: if the token changes (e.g. upgraded service), the old key on the panel might remain orphaned
    // unless the API handles token updates by user/license.
    // To be safe, we check if the token changed and if so, we should remove the old one from the panel?
    // Actually, local_moodlemcp_revoke_other_service_tokens handles the token removal locally.
    // We need to identify if there are other keys on the panel for this user that DO NOT match the new token.

    // Helper to cleanup orphans on the panel:
    local_moodlemcp_cleanup_orphan_keys_on_panel($userid, $targetserviceid);

    local_moodlemcp_revoke_other_service_tokens($userid, $targetserviceid);

    $token = local_moodlemcp_get_user_service_token($userid, $targetserviceid);
    if (!$token) {
        $token = local_moodlemcp_rotate_user_token($userid, $targetserviceid, 0);
    }

    // 5. Update the Panel with the set of roles
    return local_moodlemcp_panel_create_key($token, $userroles, null);
}

/**
 * Clean up keys on the panel that belong to a user but don't match their primary service token.
 *
 * @param int $userid
 * @param int $targetserviceid
 * @return void
 */
function local_moodlemcp_cleanup_orphan_keys_on_panel(int $userid, int $targetserviceid): void {
    global $DB;

    // Get all valid tokens for this user across all moodlemcp services
    $serviceids = local_moodlemcp_get_moodlemcp_service_ids();
    if (empty($serviceids)) {
        return;
    }

    list($insql, $params) = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    $tokens = $DB->get_records_select('external_tokens', "userid = :userid AND externalserviceid {$insql}", $params);

    $tokensrevocable = [];
    foreach ($tokens as $t) {
        if ((int) $t->externalserviceid !== $targetserviceid) {
            $tokensrevocable[] = (string) $t->token;
        }
    }

    if (empty($tokensrevocable)) {
        return;
    }

    $list = local_moodlemcp_panel_list_keys();
    if ($list['ok'] && !empty($list['data']['keys']) && is_array($list['data']['keys'])) {
        foreach ($list['data']['keys'] as $key) {
            if (!empty($key['moodleToken']) && in_array((string) $key['moodleToken'], $tokensrevocable, true)) {
                if (!empty($key['mcpKey'])) {
                    local_moodlemcp_panel_delete_key((string) $key['mcpKey']);
                }
            }
        }
    }
}

/**
 * Sends the MCP key email to a user.
 *
 * @param stdClass $user
 * @param string $mcpkey
 * @param string $mcpurl
 * @return bool
 */
function local_moodlemcp_send_key_email(stdClass $user, string $mcpkey, string $mcpurl): bool {
    $subjecttemplate = (string) get_config('local_moodlemcp', 'email_subject');
    $bodytemplate = (string) get_config('local_moodlemcp', 'email_body');

    $replacements = [
        '{$a->firstname}' => $user->firstname ?? '',
        '{$a->lastname}' => $user->lastname ?? '',
        '{$a->username}' => $user->username ?? '',
        '{$a->email}' => $user->email ?? '',
        '{$a->mcpkey}' => $mcpkey,
        '{$a->mcpurl}' => $mcpurl,
    ];

    $subject = strtr($subjecttemplate, $replacements);
    $body = strtr($bodytemplate, $replacements);

    return email_to_user($user, core_user::get_noreply_user(), $subject, $body);
}

/**
 * Revokes MCP keys for a user in the panel and removes their Moodle tokens.
 *
 * @param int $userid
 * @return void
 */
function local_moodlemcp_revoke_user_keys(int $userid): void {
    global $DB;

    $serviceids = local_moodlemcp_get_moodlemcp_service_ids();
    if (empty($serviceids)) {
        return;
    }

    list($insql, $params) = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED);
    $params['userid'] = $userid;
    $tokens = $DB->get_records_select('external_tokens', "userid = :userid AND externalserviceid {$insql}", $params);
    if (empty($tokens)) {
        return;
    }

    $tokenvalues = [];
    foreach ($tokens as $token) {
        if (!empty($token->token)) {
            $tokenvalues[] = (string) $token->token;
        }
    }

    $keymap = [];
    $list = local_moodlemcp_panel_list_keys();
    if ($list['ok'] && !empty($list['data']['keys']) && is_array($list['data']['keys'])) {
        foreach ($list['data']['keys'] as $key) {
            if (!empty($key['moodleToken']) && !empty($key['mcpKey'])) {
                $keymap[(string) $key['moodleToken']] = (string) $key['mcpKey'];
            }
        }
    }

    foreach ($tokenvalues as $tokenvalue) {
        if (isset($keymap[$tokenvalue])) {
            local_moodlemcp_panel_revoke_key($keymap[$tokenvalue]);
        }
        local_moodlemcp_revoke_token_value($tokenvalue);
    }
}

/**
 * Calls a MoodleMCP panel API endpoint.
 *
 * @param string $path
 * @param array $payload
 * @return array{ok:bool,data:array|null,error:string|null}
 */
function local_moodlemcp_call_panel_api(string $path, array $payload): array {
    global $CFG;

    require_once($CFG->libdir . '/filelib.php');

    $curl = new curl(['timeout' => 15]);
    $curl->setHeader('Accept: application/json');
    $curl->setHeader('Content-Type: application/json');

    $response = $curl->post(local_moodlemcp_api_base_url() . $path, json_encode($payload));
    $info = $curl->get_info();

    if (!empty($curl->error)) {
        $errormsg = $curl->error . ' (Status: ' . ($info['http_code'] ?? 'N/A') . ') Payload: ' . json_encode($payload);
        debugging('local_moodlemcp: API call to ' . $path . ' failed: ' . $errormsg, DEBUG_DEVELOPER);
        return ['ok' => false, 'data' => null, 'error' => $errormsg];
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        $errormsg = 'invalid_json: ' . substr($response, 0, 200) . ' (Status: ' . ($info['http_code'] ?? 'N/A')
            . ') Payload: ' . json_encode($payload);
        debugging('local_moodlemcp: API call to ' . $path . ' returned invalid JSON: ' . $errormsg, DEBUG_DEVELOPER);
        return ['ok' => false, 'data' => null, 'error' => $errormsg];
    }

    if (isset($decoded['error'])) {
        $message = is_string($decoded['error']) ? $decoded['error'] : 'api_error';
        if (isset($decoded['message']) && is_string($decoded['message'])) {
            $message = $decoded['message'];
        }
        debugging('local_moodlemcp: API call to ' . $path . ' returned error: ' . $message, DEBUG_DEVELOPER);
        return ['ok' => false, 'data' => $decoded, 'error' => $message];
    }

    if (array_key_exists('success', $decoded) && empty($decoded['success'])) {
        debugging('local_moodlemcp: API call to ' . $path . ' returned success=false', DEBUG_DEVELOPER);
        return ['ok' => false, 'data' => $decoded, 'error' => 'api_error'];
    }

    return ['ok' => true, 'data' => $decoded, 'error' => null];
}

/**
 * Returns the configured license key.
 *
 * @return string
 */
function local_moodlemcp_get_license_key(): string {
    return (string) get_config('local_moodlemcp', 'license_key');
}

/**
 * Returns whether the license is validated.
 *
 * @return bool
 */
function local_moodlemcp_license_is_valid(): bool {
    return get_config('local_moodlemcp', 'license_status') === 'ok';
}

/**
 * Creates or updates a MCP key on the panel.
 *
 * @param string $moodletoken
 * @param string|array $moodleroles Single role string or array of role strings
 * @param string|null $expireson
 * @return array{ok:bool,data:array|null,error:string|null}
 */
function local_moodlemcp_panel_create_key(string $moodletoken, $moodleroles, ?string $expireson): array {
    $license = local_moodlemcp_get_license_key();
    if ($license === '' || !local_moodlemcp_license_is_valid()) {
        return ['ok' => false, 'data' => null, 'error' => 'invalid_license'];
    }

    $user = local_moodlemcp_get_user_by_token($moodletoken);
    $username = $user ? $user->username : '';

    // Ensure we send an array
    $roles = is_array($moodleroles) ? $moodleroles : [$moodleroles];

    $payload = [
        'licenseKey' => $license,
        'moodleToken' => $moodletoken,
        'moodleRoles' => $roles,
        'moodleUsername' => $username,
        'expiresOn' => $expireson ?: null,
    ];

    return local_moodlemcp_call_panel_api('/api/mcp/create', $payload);
}

/**
 * Fetches all keys for this license from the panel.
 *
 * @return array{ok:bool,data:array|null,error:string|null}
 */
function local_moodlemcp_panel_list_keys(): array {
    $license = local_moodlemcp_get_license_key();
    if ($license === '' || !local_moodlemcp_license_is_valid()) {
        return ['ok' => false, 'data' => null, 'error' => 'invalid_license'];
    }

    return local_moodlemcp_call_panel_api('/api/mcp/list', ['licenseKey' => $license]);
}

/**
 * Revokes a key in the panel.
 *
 * @param string $mcpkey
 * @return array{ok:bool,data:array|null,error:string|null}
 */
function local_moodlemcp_panel_revoke_key(string $mcpkey): array {
    $license = local_moodlemcp_get_license_key();
    if ($license === '' || $mcpkey === '' || !local_moodlemcp_license_is_valid()) {
        return ['ok' => false, 'data' => null, 'error' => 'invalid_license'];
    }

    $payload = [
        'licenseKey' => $license,
        'mcpKey' => $mcpkey,
    ];

    return local_moodlemcp_call_panel_api('/api/mcp/revoke', $payload);
}

/**
 * Deletes a key in the panel.
 *
 * @param string $mcpkey
 * @return array{ok:bool,data:array|null,error:string|null}
 */
function local_moodlemcp_panel_delete_key(string $mcpkey): array {
    $license = local_moodlemcp_get_license_key();
    if ($license === '' || $mcpkey === '' || !local_moodlemcp_license_is_valid()) {
        return ['ok' => false, 'data' => null, 'error' => 'invalid_license'];
    }

    return local_moodlemcp_call_panel_api('/api/mcp/delete', [
        'licenseKey' => $license,
        'mcpKey' => $mcpkey,
    ]);
}

/**
 * Suspends or reactivates a key in the panel.
 *
 * @param string $mcpkey
 * @param bool $suspend
 * @return array{ok:bool,data:array|null,error:string|null}
 */
function local_moodlemcp_panel_suspend_key(string $mcpkey, bool $suspend): array {
    $license = local_moodlemcp_get_license_key();
    if ($license === '' || $mcpkey === '' || !local_moodlemcp_license_is_valid()) {
        return ['ok' => false, 'data' => null, 'error' => 'invalid_license'];
    }

    return local_moodlemcp_call_panel_api('/api/mcp/suspend', [
        'licenseKey' => $license,
        'mcpKey' => $mcpkey,
        'suspend' => $suspend,
    ]);
}

/**
 * Marks a key as sent in the panel.
 *
 * @param string $mcpkey
 * @return array{ok:bool,data:array|null,error:string|null}
 */
function local_moodlemcp_panel_mark_sent(string $mcpkey): array {
    $license = local_moodlemcp_get_license_key();
    if ($license === '' || $mcpkey === '' || !local_moodlemcp_license_is_valid()) {
        return ['ok' => false, 'data' => null, 'error' => 'invalid_license'];
    }

    return local_moodlemcp_call_panel_api('/api/mcp/sent', [
        'licenseKey' => $license,
        'mcpKey' => $mcpkey,
    ]);
}

/**
 * Assigns a user to a MoodleMCP service and creates/updates their MCP key.
 *
 * @param int $userid
 * @param string $serviceshortname
 * @return array{ok:bool,error:string|null,mcpkey:string|null,mcpurl:string|null}
 */
function local_moodlemcp_assign_user_to_service(int $userid, string $serviceshortname): array {
    global $DB;

    if (!local_moodlemcp_license_is_valid()) {
        return ['ok' => false, 'error' => 'invalid_license', 'mcpkey' => null, 'mcpurl' => null];
    }

    if (!local_moodlemcp_user_is_eligible_for_service($userid, $serviceshortname)) {
        return ['ok' => false, 'error' => 'not_eligible', 'mcpkey' => null, 'mcpurl' => null];
    }

    local_moodlemcp_ensure_services();
    $serviceid = local_moodlemcp_get_service_id($serviceshortname);
    if (!$serviceid) {
        return ['ok' => false, 'error' => 'missing_service', 'mcpkey' => null, 'mcpurl' => null];
    }

    $transaction = $DB->start_delegated_transaction();
    try {
        local_moodlemcp_authorize_user_for_service($userid, $serviceid);

        // We do NOT remove from other services anymore to support multi-role keys.
        // Instead, we recalculate the key which handles token rotation if primary role changes.
        // And now we use the returned result directly!
        $result = local_moodlemcp_recalculate_user_key($userid);

        $mcpkey = '';
        $mcpurl = '';

        if ($result && isset($result['ok']) && $result['ok'] === true) {
            // If key deleted (ok=true, data=null), mcpkey remains empty.
            if (isset($result['data']['mcpKey'])) {
                $mcpkey = (string) $result['data']['mcpKey'];
                $mcpurl = (string) ($result['data']['mcpUrl'] ?? '');

                // Check if we need to send email
                if ((int) get_config('local_moodlemcp', 'auto_email') === 1) {
                    // Check if already sent? Result doesn't have sentAt typically for NEW keys.
                    // But we can just try sending. local_moodlemcp_send_key_email handles templates.
                    // Wait, we need to know if it was ALREADY sent to avoid spam.
                    // The panel response for 'create' returns current state.
                    // If it was just created, sentAt is null.
                    $sentat = $result['data']['sentAt'] ?? '';
                    if (empty($sentat)) {
                        $user = $DB->get_record('user', ['id' => $userid], '*', IGNORE_MISSING);
                        if ($user && local_moodlemcp_send_key_email($user, $mcpkey, $mcpurl)) {
                            local_moodlemcp_panel_mark_sent($mcpkey);
                        }
                    }
                }
            }
        } else {
            // Recalculate failed?
            throw new Exception($result['error'] ?? 'recalculate_failed');
        }

        $transaction->allow_commit();

        return ['ok' => true, 'error' => null, 'mcpkey' => $mcpkey, 'mcpurl' => $mcpurl];
    } catch (Exception $e) {
        $transaction->rollback($e);
        return ['ok' => false, 'error' => $e->getMessage(), 'mcpkey' => null, 'mcpurl' => null];
    }
}

/**
 * Syncs a user to their primary MoodleMCP role and key.
 *
 * @param stdClass $user
 * @param array $keymap
 * @param string|null $limit_to_service If set, only reconcile this specific service (add/remove). Leave others as is.
 * @param bool $remove_only If true, only remove services, don't add new ones (used when a role is unassigned)
 * @return array{ok:bool,added:int,removed:int,result:array|null}
 */
function local_moodlemcp_sync_user_auto(stdClass $user, array $keymap, ?string $limit_to_service = null, bool $remove_only = false): array {
    global $DB;

    if (!local_moodlemcp_license_is_valid()) {
        return ['ok' => false, 'added' => 0, 'removed' => 0, 'result' => null];
    }

    local_moodlemcp_ensure_services();

    // 1. Determine Target Services based on current Moodle Roles
    $effectiveRoles = local_moodlemcp_get_effective_roles($user->id);
    $targetServiceIds = [];
    foreach ($effectiveRoles as $role) {
        $shortname = local_moodlemcp_service_for_role($role);
        // If limiting to a service, only consider this role if it maps to the limited service
        if ($limit_to_service !== null && $shortname !== $limit_to_service) {
            continue;
        }
        $sid = local_moodlemcp_get_service_id($shortname);
        if ($sid) {
            $targetServiceIds[] = $sid;
        }
    }

    // 2. Reconcile with Current Assignments
    // Get current assignments
    $mcpServiceIds = local_moodlemcp_get_moodlemcp_service_ids();
    if (empty($mcpServiceIds)) {
        // Should have been ensured, but safety check
        return ['ok' => false, 'added' => 0, 'removed' => 0, 'result' => null];
    }

    list($insql, $params) = $DB->get_in_or_equal($mcpServiceIds, SQL_PARAMS_NAMED);
    $params['userid'] = $user->id;
    $currentAssignments = $DB->get_records_select(
        'external_services_users',
        "userid = :userid AND externalserviceid {$insql}",
        $params
    );
    $currentServiceIds = array_map(function ($a) {
        return (int) $a->externalserviceid;
    }, $currentAssignments);

    // Filter scope for reconciliation
    if ($limit_to_service !== null) {
        $limitsid = local_moodlemcp_get_service_id($limit_to_service);
        if ($limitsid) {
            // We only care about adding/removing THAT service.

            // ToAdd: If eligible (in target) AND not assigned.
            // targetServiceIds only has limited service if eligible.
            $is_eligible = in_array($limitsid, $targetServiceIds);
            $is_assigned = in_array($limitsid, $currentServiceIds);

            $toAdd = ($is_eligible && !$is_assigned && !$remove_only) ? [$limitsid] : [];
            $toRemove = (!$is_eligible && $is_assigned) ? [$limitsid] : [];
        } else {
            $toAdd = [];
            $toRemove = [];
        }
    } else {
        // Full Sync
        // Add missing (unless remove_only mode)
        $toAdd = $remove_only ? [] : array_diff($targetServiceIds, $currentServiceIds);
        // Remove extra
        $toRemove = array_diff($currentServiceIds, $targetServiceIds);
    }

    $added_count = 0;
    $removed_count = 0;

    foreach ($toRemove as $sid) {
        $DB->delete_records('external_services_users', ['externalserviceid' => $sid, 'userid' => $user->id]);
        $removed_count++;
    }
    foreach ($toAdd as $sid) {
        local_moodlemcp_authorize_user_for_service((int) $user->id, (int) $sid);
        $added_count++;
    }

    // 3. Recalculate Key (handles token rotation, key creation/deletion on panel)
    $result = local_moodlemcp_recalculate_user_key($user->id);

    // DEBUG: Capture trace if filtering
    if ($limit_to_service && isset($keymap['debug_trace'])) {
        $keymap['debug_trace'][] = "User {$user->id}: Recalculate result: " . json_encode($result);
        if (empty($targetServiceIds)) {
            $keymap['debug_trace'][] = "User {$user->id}: No target services found. Effective Roles: " . json_encode($effectiveRoles);
        }
    }

    // 4. Handle Email Notification


    // 4. Handle Email Notification
    // We rely on the result from recalculate, so we don't need the stale keymap for NEW keys.

    $mcpkey = '';
    $mcpurl = '';
    $sentat = '';

    if ($result && isset($result['ok']) && $result['ok'] === true) {
        if (isset($result['data']['mcpKey'])) {
            $mcpkey = (string) $result['data']['mcpKey'];
            $mcpurl = (string) ($result['data']['mcpUrl'] ?? '');
            $sentat = (string) ($result['data']['sentAt'] ?? '');
        }
    }



    if ((int) get_config('local_moodlemcp', 'auto_email') === 1 && $sentat === '' && $mcpkey !== '' && $mcpurl !== '') {
        if (local_moodlemcp_send_key_email($user, $mcpkey, $mcpurl)) {
            local_moodlemcp_panel_mark_sent($mcpkey);
        }
    }

    return [
        'ok' => true,
        'added' => $added_count,
        'removed' => $removed_count,
        'result' => $result
    ];
}

/**
 * Syncs users and revokes keys for deleted accounts.
 *
 * @param string|null $servicefilter Shortname of the service to filter sync by (optional).
 * @return array{ok:bool,synced:int,added:int,removed:int,revoked:int}
 */
function local_moodlemcp_sync_all_users(?string $servicefilter = null): array {
    global $CFG, $DB;

    if (!local_moodlemcp_license_is_valid()) {
        return ['ok' => false, 'synced' => 0, 'added' => 0, 'removed' => 0, 'revoked' => 0];
    }

    local_moodlemcp_ensure_services();

    $keymap = [];
    // Only fetch keys if we are doing a full sync or if efficient enough?
    // For now we fetch all keys to map them correctly.
    $list = local_moodlemcp_panel_list_keys();
    if ($list['ok'] && !empty($list['data']['keys']) && is_array($list['data']['keys'])) {
        foreach ($list['data']['keys'] as $key) {
            if (!empty($key['moodleToken'])) {
                $keymap[(string) $key['moodleToken']] = $key;
            }
        }
    }

    $revoked = 0;
    // Revocation only happens on FULL sync or can happen targeted?
    // If filtering, we probably shouldn't bulk revoke unrelated users.
    if ($servicefilter === null) {
        foreach ($keymap as $token => $key) {
            $user = local_moodlemcp_get_user_by_token($token);
            if (!$user) {
                if (!empty($key['mcpKey'])) {
                    local_moodlemcp_panel_revoke_key((string) $key['mcpKey']);
                }
                local_moodlemcp_revoke_token_value($token);
                $revoked++;
            }
        }
    }

    $synced = 0;
    $total_added = 0;
    $total_removed = 0;
    $guestid = isset($CFG->siteguest) ? (int) $CFG->siteguest : 0;

    // Build SQL based on filter
    $extrajoin = '';
    $extrawhere = '';
    $params = ['guestid' => $guestid];

    if ($servicefilter) {
        // If filtering by service, we only want users who are eligible for this service
        // This effectively means users who have the role mapping to this service.
        $role = local_moodlemcp_role_from_service($servicefilter);
        if ($role === 'admin') {
            // Admins are special, check is_siteadmin equivalent or just sync all for now?
            // Since is_siteadmin is a function, we can't easily SQL join it without specific tables.
            // We'll iterate all users and check capability in the loop for admins.
            // Or optimize: get_admins() returns list.
            $admins = get_admins();
            foreach ($admins as $admin) {
                $res = local_moodlemcp_sync_user_auto($admin, $keymap, $servicefilter);
                if ($res && $res['ok']) {
                    $total_added += $res['added'];
                    $total_removed += $res['removed'];
                }
                $synced++;
            }
            return ['ok' => true, 'synced' => $synced, 'added' => $total_added, 'removed' => $total_removed, 'revoked' => $revoked];
        } else {
            // For other roles, we can use get_role_users logic or just iterate all and filter in PHP?
            // Iterating all is safer but slower. 
            // Let's use get_role_users logic if possible, but role allocation is complex.
            // Simpler approach: Iterate all users, check if local_moodlemcp_user_is_eligible_for_service
            // matches the filter.
        }
    }

    // If filtered, pre-fetch currently assigned users to efficient removal checks
    $assigned_ids_map = [];
    if ($servicefilter) {
        $filter_sid = local_moodlemcp_get_service_id($servicefilter);
        if ($filter_sid) {
            $assigned_records = $DB->get_records('external_services_users', ['externalserviceid' => $filter_sid], '', 'userid');
            foreach ($assigned_records as $rec) {
                $assigned_ids_map[$rec->userid] = true;
            }
        }
    }

    $rs = $DB->get_recordset_select(
        'user',
        'deleted = 0 AND id <> :guestid',
        $params,
        'id',
        'id,username,firstname,lastname,email,suspended'
    );

    $first_error = null;

    foreach ($rs as $user) {
        if (!empty($user->suspended)) {
            continue;
        }

        if ($servicefilter) {
            // Check eligibility OR current assignment
            // We need to process the user if they are ELIGIBLE (to add) OR ASSIGNED (to remove)
            $is_eligible = local_moodlemcp_user_is_eligible_for_service($user->id, $servicefilter);
            $is_assigned = isset($assigned_ids_map[$user->id]);

            if (!$is_eligible && !$is_assigned) {
                continue;
            }
        }

        $res = local_moodlemcp_sync_user_auto($user, $keymap, $servicefilter);
        if ($res && isset($res['ok']) && $res['ok'] === true) {
            $total_added += $res['added'];
            $total_removed += $res['removed'];
        } elseif ($res && isset($res['ok']) && $res['ok'] === false && $first_error === null) {
            $first_error = $res['error'] ?? 'unknown_error';
            if (isset($res['data']))
                $first_error .= ' ' . json_encode($res['data']);
        }
        $synced++;
    }
    $rs->close();

    $return = ['ok' => true, 'synced' => $synced, 'added' => $total_added, 'removed' => $total_removed, 'revoked' => $revoked];
    if ($first_error !== null) {
        $return['first_error'] = $first_error;
    }
    return $return;
}

/**
 * Validates a license key against the remote API.
 *
 * @param string $license
 * @return array{status:string,message:string}
 */
function local_moodlemcp_validate_license(string $license): array {
    global $CFG;

    $license = trim($license);
    if ($license === '') {
        return [
            'status' => 'error',
            'message' => get_string('license_empty', 'local_moodlemcp'),
        ];
    }

    require_once($CFG->libdir . '/filelib.php');

    $curl = new curl(['timeout' => 10]);
    $curl->setHeader('Accept: application/json');
    $curl->setHeader('Content-Type: application/json');

    $moodleurl = rtrim($CFG->wwwroot, '/');
    $response = $curl->post(local_moodlemcp_api_base_url() . '/api/license/verify', json_encode([
        'licenseKey' => $license,
        'moodleUrl' => $moodleurl,
    ]));
    if (!empty($curl->error)) {
        return [
            'status' => 'error',
            'message' => get_string('license_http_error', 'local_moodlemcp', $curl->error),
        ];
    }

    if (local_moodlemcp_license_response_ok($response)) {
        return [
            'status' => 'ok',
            'message' => '',
        ];
    }

    return [
        'status' => 'error',
        'message' => local_moodlemcp_license_error_message($response),
    ];
}

/**
 * Checks if the license API response represents success.
 *
 * @param string $response
 * @return bool
 */
function local_moodlemcp_license_response_ok(string $response): bool {
    $trimmed = trim($response);
    if ($trimmed === '') {
        return false;
    }

    $decoded = json_decode($trimmed, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return false;
    }

    return !empty($decoded['valid']);
}

/**
 * Builds a user-facing error message for a failed license verification.
 *
 * @param string $response
 * @return string
 */
function local_moodlemcp_license_error_message(string $response): string {
    $trimmed = trim($response);
    if ($trimmed === '') {
        return get_string('license_invalid', 'local_moodlemcp');
    }

    $decoded = json_decode($trimmed, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return get_string('license_invalid', 'local_moodlemcp');
    }

    if (!empty($decoded['error'])) {
        $error = (string) $decoded['error'];
        $map = [
            'invalid_request' => get_string('license_error_invalid_request', 'local_moodlemcp'),
            'invalid_license' => get_string('license_error_invalid_license', 'local_moodlemcp'),
            'license_not_configured' => get_string('license_error_not_configured', 'local_moodlemcp'),
            'url_mismatch' => get_string('license_error_url_mismatch', 'local_moodlemcp'),
        ];
        if (isset($map[$error])) {
            return $map[$error];
        }
        return get_string('license_invalid_reason', 'local_moodlemcp', $error);
    }

    return get_string('license_invalid', 'local_moodlemcp');
}
/**
 * Prints the module navigation tabs.
 *
 * @param string $current The current tab ID.
 * @return void
 */
function local_moodlemcp_print_tabs(string $current): void {
    $tabs = [
        new tabobject(
            'license',
            new moodle_url('/local/moodlemcp/index.php'),
            get_string('tab_license', 'local_moodlemcp')
        ),
        new tabobject(
            'services',
            new moodle_url('/local/moodlemcp/services.php'),
            get_string('tab_services', 'local_moodlemcp')
        ),
        new tabobject(
            'users',
            new moodle_url('/local/moodlemcp/users.php'),
            get_string('tab_users', 'local_moodlemcp')
        ),
        new tabobject(
            'keys',
            new moodle_url('/local/moodlemcp/keys.php'),
            get_string('tab_keys', 'local_moodlemcp')
        ),
        new tabobject(
            'settings',
            new moodle_url('/local/moodlemcp/settings_page.php'),
            get_string('tab_settings', 'local_moodlemcp')
        ),
    ];
    print_tabs([$tabs], $current);
}

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
    $form = html_writer::start_tag('form', [
        'method' => 'post',
        'action' => (new moodle_url('/local/moodlemcp/keys.php'))->out(false),
    ]);
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
