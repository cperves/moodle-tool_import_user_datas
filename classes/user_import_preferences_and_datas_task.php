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
 * admin tool tool_import_user_datas entity class
 * an admin tool to import user preferences from one Moodle to another
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_import_user_datas;

use core\exception\moodle_exception;
use enrol_self\self_test;

/**
 * import user datas task datatable entity
 */
class user_import_preferences_and_datas_task {
    /**
     * Task error status
     */
    const STATUS_ERROR = -1;
    /**
     * Task scheduled status
     */
    const STATUS_SHEDULED = 0;
    /**
     * Task In progress status
     */
    const STATUS_INPROGRESS = 1;
    /**
     * Task performed status
     */
    const STATUS_PERFORMED = 2;

    /**
     * schedule user data import task by saving in datatable
     * @param $username
     * @param $auth
     * @return int
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function schedule_user_datas_import($username, $auth): int {
        global $DB;
        if (!$DB->record_exists('user', ['username' => $username])) {
            throw new moodle_exception("user $username does not exist");
        }
        if ($DB->record_exists('tool_import_user_datas', ['username' => $username])) {
            throw new moodle_exception("import task for user $username already exits");
        }
        $record = new \stdClass();
        $record->username = $username;
        $record->auth = $auth;
        $record->status = self::STATUS_SHEDULED;
        $record->timecreated = time();
        return $DB->insert_record('tool_import_user_datas', $record);
    }

    /**
     * get task associated to a given username
     * @param $username
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    public static function get_task_for_username($username) {
        global $DB;
        return $DB->get_record('tool_import_user_datas', ['username' => $username]);
    }

    /**
     * update schedule status of a task associated to a username
     * @param $username
     * @param $status
     * @return bool
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function update_scheduled_status($username, $status): bool {
        global $DB;
        if ( !in_array($status,
            [self::STATUS_INPROGRESS, self::STATUS_PERFORMED, self::STATUS_SHEDULED, self::STATUS_ERROR])) {
            throw new moodle_exception('bad status parameter');
        }
        $record = $DB->get_record('tool_import_user_datas', ['username' => $username]);
        if (!$record) {
            throw new moodle_exception("scheduled import user datas task not found for username $username");
        }
        $record->status = $status;
        $record->timemodified = time();
        if ($status == self::STATUS_PERFORMED) {
            $record->timeprocessed = $record->timemodified;
        }
        return $DB->update_record('tool_import_user_datas', $record);
    }

    /**
     * update status for a scheduled task with a given taskid
     * @param $taskid
     * @param $status
     * @return bool
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function update_scheduled_status_by_id($taskid, $status): bool {
        global $DB;
        if ( !in_array($status,
            [self::STATUS_INPROGRESS, self::STATUS_PERFORMED, self::STATUS_SHEDULED, self::STATUS_ERROR])) {
            throw new moodle_exception('bad status parameter');
        }
        $record = $DB->get_record('tool_import_user_datas', ['id' => $taskid]);
        if (!$record) {
            throw new moodle_exception("scheduled import user datas task not found for id $taskid");
        }
        $record->status = $status;
        $record->timemodified = time();
        if ($status == self::STATUS_PERFORMED) {
            $record->timeprocessed = $record->timemodified;
        }
        return $DB->update_record('tool_import_user_datas', $record);
    }

    /**
     * retrieve tasks wih a scheduled status
     * @return array
     */
    public static function retrieve_tasks_to_perform() {
        return self::retrieve_tasks(self::STATUS_SHEDULED);
    }

    /**
     * retrieve tasks for a given status
     * @param $status
     * @return array
     * @throws \dml_exception
     */
    public static function retrieve_tasks($status = null) {
        global $DB;
        $config = get_config('tool_import_user_datas');
        $params = [];
        if (is_null($status)) {
            $request = "select * from {tool_import_user_datas} order by timecreated, id asc "
                . ($config->paging > 0 ? "limit $config->paging" : "");
        } else {
            $request = "select * from {tool_import_user_datas} where status=:status order by timecreated, id asc "
                . ($config->paging > 0 ? "limit $config->paging" : "");
            $params = ['status' => $status];
        }
        return $DB->get_records_sql($request, $params);

    }

    /**
     * return the task associated to a given username
     * @param $username
     * @return mixed
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function get_task_status($username) {
        global $DB;
        $record = $DB->get_record('tool_import_user_datas', ['username' => $username]);
        if (!$record) {
            throw new moodle_exception("task not found for username $username");
        }
        return $record->status;
    }
}
