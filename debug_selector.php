<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/moodlemcp/lib.php');

global $DB;

$service = 'moodlemcp_user';
$serviceid = local_moodlemcp_get_service_id($service);

echo "Service: $service\n";
echo "Service ID: " . ($serviceid ?: 'NULL') . "\n";

if (!$serviceid) {
    die("Service not found.\n");
}

$role = local_moodlemcp_role_from_service($service);
echo "Role: $role\n";

$wheres = ['u.deleted = 0', 'u.suspended = 0', 'u.confirmed = 1', 'u.id <> :guestid'];
$wheres[] = "u.id NOT IN (SELECT userid FROM {external_services_users} WHERE externalserviceid = :serviceid)";

$params = [
    'guestid' => $CFG->siteguest,
    'serviceid' => $serviceid
];

$sql = "SELECT Count(u.id)
          FROM {user} u
         WHERE " . implode(' AND ', $wheres);

echo "SQL: $sql\n";
echo "Params: " . json_encode($params) . "\n";

$count = $DB->count_records_sql($sql, $params);
echo "Potential Users Count: $count\n";

if ($count == 0) {
    echo "Debugging: Checking total users...\n";
    echo "Total users: " . $DB->count_records('user') . "\n";
    echo "Deleted users: " . $DB->count_records('user', ['deleted' => 1]) . "\n";
    echo "Suspended users: " . $DB->count_records('user', ['suspended' => 1]) . "\n";
    echo "Unconfirmed users: " . $DB->count_records('user', ['confirmed' => 0]) . "\n";

    $assigned_sql = "SELECT Count(id) FROM {external_services_users} WHERE externalserviceid = :serviceid";
    echo "Assigned users: " . $DB->count_records_sql($assigned_sql, ['serviceid' => $serviceid]) . "\n";
}
