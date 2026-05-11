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
 * tool_import_user_datas tools class
 * an admin tool to import user preferences from one Moodle to another
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_import_user_datas;

use context_system;
use core\exception\moodle_exception;
use core\task\manager;
use curl;
use Exception;
use local_generic_admin_notifier\notification;
use local_generic_admin_notifier\admin_notification_list;
use stdClass;
use tool_import_user_datas\task\perform_import_user_datas_task;
use webservice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');


/**
 * general tool class for tool_import_user_datas tool
 */
class tools {
    /**
     * constant for webservice role name
     */
    public const TOOL_IMPORT_USER_DATAS_WS_ROLE = 'tool_import_user_datas_ws';
    /**
     * constant for webservice user username
     */
    public const TOOL_IMPORT_USER_DATAS_WS_USER = 'tool_import_user_datas_ws_user';

    /**
     * core function to import user datas from remote moodle
     * @return void
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function import_user_preferences_and_datas_from_remote() {
        $tasks = user_import_preferences_and_datas_task::retrieve_tasks_to_perform();
        foreach ($tasks as $task) {
            if (get_config('tool_import_user_datas', 'adhoctasks')) {
                $adhoctask = new perform_import_user_datas_task();
                $adhoctask->set_custom_data($task);
                manager::queue_adhoc_task($adhoctask);
            } else {
                self::import_preferences_and_datas($task);
            }
        }
    }

    /**
     * rest call method to call remote moodle
     * @param string $wsfunction webservice function name
     * @param array $params webservice function parameters
     * @param string $restformat rest format, default json
     * @param string $method curl method, default post
     * @return mixed
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function rest_call_remote_moodle($wsfunction, $params = [], $restformat = 'json', $method = 'post') {
        global $CFG;
        require_once($CFG->dirroot . '/webservice/lib.php');
        $config = get_config('tool_import_user_datas');
        $serverurl = $config->remote_url . '/webservice/rest/server.php'
            . '?wstoken=' . $config->remote_token . '&wsfunction=' . $wsfunction;
        $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
        $curl = new curl();
        $resp = null;
        if ($method == 'get') {
            $resp = $curl->get($serverurl . $restformat, $params);
        } else if ($method == 'post') {
            $resp = $curl->post($serverurl . $restformat, $params);
        }
        try {
            $resp = json_decode($resp ?? '');
        } catch (Exception $ex) {
            throw new moodle_exception($ex);
        }
        // Check if errors encountered.
        if (!isset($resp)) {
            // Retrieve infos in curl.
            throw new moodle_exception(
                'curl error , httpcode : ' . $curl->get_info()['http_code'] . ' curl error number '
                . $curl->get_errno()
            );
        }
        return $resp;
    }

    /**
     * return config preferences setting
     * @return string[]
     * @throws \dml_exception
     */
    public static function get_config_preferences() {
        $configprefs = get_config('tool_import_user_datas', 'preferences');
        $configprefs = explode(';', $configprefs);
        return $configprefs;
    }

    /**
     * set user preferences in current moodle
     * @param string $username username
     * @param array $userdatasandprefs formatted as
     *      ['preferences'=>[['name'=>...,'value'=>...],...],'userdatas' => [['name' => ...,'value' => ...],...]]
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function set_user_preferences_and_datas(
        $username,
        $userdatasandprefs
    ): void {
        global $DB, $CFG;
        $config = get_config('tool_import_user_datas');
        $userdatastotreat = explode(';', $config->user_datas);
        $preferencestotreat = explode(';', $config->preferences);
        require_capability(
            'tool/import_user_datas:set_user_preferences_and_datas_for_user',
            context_system::instance()
        );
        $user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id]);
        if (property_exists($userdatasandprefs, 'preferences') && count($userdatasandprefs->preferences) > 0) {
            foreach ($userdatasandprefs->preferences as $remotepref) {
                if (in_array($remotepref->name, $preferencestotreat)) {
                    $prefname = $remotepref->name;
                    $prefvalue = $remotepref->value;
                    set_user_preference($prefname, $prefvalue, $user);
                }
            }
        }
        if (property_exists($userdatasandprefs, 'userdatas') && count($userdatasandprefs->userdatas) > 0) {
            $updateuser = false;
            foreach ($userdatasandprefs->userdatas as $userdata) {
                // Filter datas.
                if (in_array($userdata->name, $userdatastotreat)) {
                    if ($user->{$userdata->name} != $userdata->value) {
                        $user->{$userdata->name} = $userdata->value;
                        $updateuser = true;
                    }
                }
            }
            if ($updateuser) {
                user_update_user($user, false);
            }
        }
    }

    /**
     * all necessary to install webservice for cli usage
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function install_webservice() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/webservice/lib.php');
        $systemcontext = context_system::instance();
        $rolerecord = $DB->get_record('role', ['shortname' => self::TOOL_IMPORT_USER_DATAS_WS_ROLE]);
        $wsroleid = 0;
        if ($rolerecord) {
            $wsroleid = $rolerecord->id;
            cli_writeln('role ' . self::TOOL_IMPORT_USER_DATAS_WS_ROLE . ' already exists, we\'ll use it');
        } else {
            $wsroleid = create_role(
                self::TOOL_IMPORT_USER_DATAS_WS_ROLE,
                self::TOOL_IMPORT_USER_DATAS_WS_ROLE,
                self::TOOL_IMPORT_USER_DATAS_WS_ROLE
            );
        }
        assign_capability(
            'tool/import_user_datas:get_user_preferences_and_datas_for_user',
            CAP_ALLOW,
            $wsroleid,
            $systemcontext->id,
            true
        );
        assign_capability(
            'tool/import_user_datas:set_user_preferences_and_datas_for_user',
            CAP_ALLOW,
            $wsroleid,
            $systemcontext->id,
            true
        );
        assign_capability(
            'moodle/site:config',
            CAP_ALLOW,
            $wsroleid,
            $systemcontext->id,
            true
        );

        // Allow role assignment on system.
        set_role_contextlevels($wsroleid, [10 => 10]);
        $wsuser = $DB->get_record('user', ['username' => self::TOOL_IMPORT_USER_DATAS_WS_USER]);
        if (!$wsuser) {
            $wsuser = create_user_record(self::TOOL_IMPORT_USER_DATAS_WS_USER, generate_password(20));
            $wsuser->firstname = 'wsuser';
            $wsuser->lastname = self::TOOL_IMPORT_USER_DATAS_WS_USER;
            $wsuser->email = 'ws_dtas' . $CFG->noreplyaddress;
            $wsuser->confirmed = 1;
            $DB->update_record('user', $wsuser);
        } else {
            cli_writeln('user ' . self::TOOL_IMPORT_USER_DATAS_WS_USER . 'already exists, we\'ll use it');
        }
        role_assign($wsroleid, $wsuser->id, $systemcontext->id);
        $service = $DB->get_record('external_services', ['shortname' => 'wstoolimportuserdatas']);
        // Assign user to webservice.
        $webservicemanager = new webservice();
        $serviceuser = new stdClass();
        $serviceuser->externalserviceid = $service->id;
        $serviceuser->userid = $wsuser->id;
        $webservicemanager->add_ws_authorised_user($serviceuser);

        $params = [
            'objectid' => $serviceuser->externalserviceid,
            'relateduserid' => $serviceuser->userid,
        ];
        $event = \core\event\webservice_service_user_added::create($params);
        $event->trigger();
        $token = \core_external\util::generate_token(
            EXTERNAL_TOKEN_PERMANENT,
            $service,
            $wsuser->id,
            $systemcontext
        );
        return $token;
    }

    /**
     * import user preferences and datas from remote moodle, based on task information
     * @param mixed $task line from datatable
     * @return void
     * @throws moodle_exception
     */
    public static function import_preferences_and_datas(mixed $task): void {
        global $DB, $CFG;
        $config = get_config('tool_import_user_datas');
        $errors = new admin_notification_list(
            'tool_import_user_datas',
            'error_import_user_datas_provider'
        );
        $user = $DB->get_record(
            'user',
            ['username' => '' . $task->username, 'mnethostid' => $CFG->mnet_localhost_id]
        );
        if (!$user) {
            $task->errormessage = "user not found";
            $errors->add_error(
                new notification($task, 'usernotfound', $task->username)
            );
            user_import_preferences_and_datas_task::update_scheduled_status(
                $task->username,
                user_import_preferences_and_datas_task::STATUS_ERROR
            );
        } else {
            user_import_preferences_and_datas_task::update_scheduled_status(
                $task->username,
                user_import_preferences_and_datas_task::STATUS_INPROGRESS
            );
            $params = [
                'username' => $task->username,
                'userauth' => $task->auth,
                'preferences' => $config->preferences,
                'userdatas' => $config->user_datas,
            ];
            try {
                $userdatasandprefs = self::rest_call_remote_moodle(
                    'tool_import_user_datas_get_user_datas',
                    $params
                );
                try {
                    self::set_user_preferences_and_datas($task->username, $userdatasandprefs);
                    user_import_preferences_and_datas_task::update_scheduled_status(
                        $task->username,
                        user_import_preferences_and_datas_task::STATUS_PERFORMED
                    );
                } catch (moodle_exception $e) {
                    user_import_preferences_and_datas_task::update_scheduled_status(
                        $task->username,
                        user_import_preferences_and_datas_task::STATUS_ERROR
                    );
                    $task->errormessage = $e->getMessage();
                    $errors->add_error(
                        new notification($task, 'errorwhilsettingpreferencesanddatas', $user)
                    );
                }
            } catch (moodle_exception $e) {
                user_import_preferences_and_datas_task::update_scheduled_status(
                    $task->username,
                    user_import_preferences_and_datas_task::STATUS_ERROR
                );
                $task->errormessage = $e->getMessage();
                $errors->add_error(
                    new notification($task, 'errorwhilecallingwebservice', $user)
                );
            }
        }
        if ($errors->has_errors()) {
            $errors->notify_admin();
        }
    }
}
