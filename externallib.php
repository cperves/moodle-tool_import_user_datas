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
 * admin tool tool_import_user_datas webserices
 * an admin tool to import user preferences from one Moodle to another
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\exception\moodle_exception;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use tool_import_user_datas\tools;
use tool_import_user_datas\user_import_preferences_and_datas_task;

/**
 * Webservice functions defintion class to import user datas from a remote Moodle
 */
class tool_import_user_datas_external extends \core_external\external_api {

    /**
     * webservice function parameters function
     * @return external_function_parameters
     */
    public static function get_user_datas_and_preferences_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username of the user, default to current user', VALUE_REQUIRED),
                'userauth' => new external_value(PARAM_TEXT, 'user authentication method', VALUE_REQUIRED),
                'preferences' => new external_value(PARAM_TEXT, 'preferences to import', VALUE_REQUIRED),
                'userdatas' => new external_value(PARAM_TEXT, 'user datas to import', VALUE_REQUIRED),
            ]
        );
    }

    /**
     *  webservice function execution function that retrieve user datas
     * @param $username
     * @param $userauth
     * @param $preferences
     * @param $userdatas
     * @return array
     * @throws \moodle_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */
    public static function get_user_datas_and_preferences($username, $userauth, $preferences, $userdatas) {
        global $DB, $CFG, $USER;
        require_capability('tool/import_user_datas:get_user_preferences_and_datas_for_user', context_system::instance());
        require_once($CFG->dirroot . '/user/externallib.php');
        self::validate_parameters(self::get_user_datas_and_preferences_parameters(),
            [
                'username' => $username,
                'userauth' => $userauth,
                'preferences' => $preferences,
                'userdatas' => $userdatas,
            ]
        );
        $preferences = explode(";", $preferences);
        $userdatas = explode(";", $userdatas);
        // Search user.
        $user = $DB->get_record('user', ['username' => $username, 'auth' => $userauth]);
        if (!$user) {
            throw new moodle_exception("user $username not exists", 'tool_import_user_datas');
        }
        // Call $preferences web service.
        $preferencestoimport = core_user_external::get_user_preferences('', $user->id);
        foreach ($preferencestoimport['preferences'] as $index => $preferencetoimport) {
            if (!in_array($preferencetoimport['name'], $preferences) ) {
                unset($preferencestoimport['preferences'][$index]);
            }
        }
        $datastoimport = [];
        foreach ($userdatas as $data) {
            if (property_exists($user, $data)) {
                $datastoimport[] = ['name' => $data, 'value' => $user->$data];
            }
        }
        return [
            'preferences' => $preferencestoimport['preferences'],
            'userdatas' => $datastoimport,
            ];
    }

    /**
     * webservice function returns
     * @return external_single_structure
     */
    public static function get_user_datas_and_preferences_returns() {
        return new external_single_structure(
            [
                'preferences' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'name' => new external_value(PARAM_RAW, 'The name of the preference'),
                            'value' => new external_value(PARAM_RAW, 'The value of the preference'),
                        ]
                    ),
                    'User custom fields (also known as user profile fields)'
                ),
                'userdatas' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'name' => new external_value(PARAM_RAW, 'The name of the preference'),
                            'value' => new external_value(PARAM_RAW, 'The value of the preference'),
                        ]
                    ),
                    'User datas'
                ),
            ]
        );
    }

    /**
     * Webservice function parameters for change task status
     * @return external_function_parameters
     */
    public static function change_task_status_parameters(): external_function_parameters {
        return new external_function_parameters([
            'taskid' => new external_value(PARAM_INT, 'task id', VALUE_REQUIRED),
            'status' => new external_value(PARAM_INT, 'status value', VALUE_REQUIRED),
        ]);
    }

    /**
     * Webservice function execution for change task status
     * @param $taskid
     * @param $status
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function change_task_status($taskid, $status) {
        self::validate_parameters(self::change_task_status_parameters(),
            [
                'taskid' => $taskid,
                'status' => $status,
            ]
        );
        user_import_preferences_and_datas_task::update_scheduled_status_by_id($taskid, $status);
        return ['status' => $status];
    }

    /**
     * Webservice function returns for change task status
     * @return external_single_structure
     */
    public static function change_task_status_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, 'status value applied to task'),
        ]);
    }
}
