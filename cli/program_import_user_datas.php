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
 * tool to program user import
 *
 * Notes:
 *   - it is required to use the web server account when executing PHP CLI scripts
 *   - you need to change the "www-data" to match the apache user account
 *   - use "su" if "sudo" not available
 *
 * @package    tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author 2025 Celine Perves cperves@unistra.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_import_user_datas\user_import_preferences_and_datas_task;

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../../config.php');
global $CFG, $DB;
require_once("$CFG->libdir/clilib.php");

[$options, $unrecognised] = cli_get_params(
    [
        'help' => false,
        'auth' => null,
        'username' => null,
    ],
    [
        'h' => 'help',
        'a' => 'auth',
        'u' => 'username',
    ]
);

$usage = "
Add existing users to import user datas tasks depending of their auth method

Usage:
    # Help
    php admin/tool/import_user_datas/cli/program_import_user_datas.php [--help|-h]

    # Import all user with particular auth method
    php admin/tool/import_user_datas/cli/program_import_user_datas.php --auth=<auth>

    # Import only one user, auth required and only one auth entity
    php admin/tool/import_user_datas/cli/program_import_user_datas.php --username=<username> --auth=<auth>
Options:
    -h --help                   Print this help.
    -a --auth=<auth>  Auth methods, separated by ;, required
    -u --username=<username>
";

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL . '  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help']) {
    cli_writeln($usage);
    exit(2);
}

$auth = $options['auth'];
$username = $options['username'];
$concernedusers = [];
if ($auth != null) {
    $auth = explode(';', $auth);
    if (count($auth) == 0) {
        cli_error('auth is empty, please enter a valid auth');
    }
    if ($username != null) {
        $userid = $DB->record_exists('user', ['username' => $username]);
        if (!$userid) {
            cli_error("user $username not exists");
        }
        if (count($auth) > 1) {
            cli_error("since username is filled, auth must have only one value");
        }
        $concerneduser = new stdClass();
        $concerneduser->username = $username;
        $concerneduser->auth = $auth[0];
        $concerneduser->id = $userid;
        $concernedusers[] = $concerneduser;
    } else {
        [$where, $params] = $DB->get_in_or_equal(array_values($auth));
        $concernedusers = $DB->get_records_sql(
            'select id, username, auth from {user} where auth ' . $where,
            $params
        );
    }
} else {
    cli_error('auth parameter is required');
}

foreach ($concernedusers as $concerneduser) {
    if ($DB->get_record('tool_import_user_datas', ['username' => '' . $concerneduser->username])) {
        cli_writeln("user $concerneduser->username already programmed for user import datas.");
    } else {
        user_import_preferences_and_datas_task::schedule_user_datas_import($concerneduser->username, $concerneduser->auth);
        cli_writeln("user $concerneduser->username programmed for user import datas.");
    }
}
cli_writeln("End of process.");
exit(0);
