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
 * admin tool tool_import_user_datas observer
 * an admin tool to import user preferences from one Moodle to another
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_import_user_datas;

/**
 * event observer class
 */
class observers {
    /**
     * user created event handler
     * @param \core\event\user_created $event
     * @return void
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public static function user_created(\core\event\user_created $event) {
        global $DB, $CFG;
        $config = get_config('tool_import_user_datas');
        if ($config->trigger_on_create) {
            $user = $DB->get_record('user', ['id' => $event->objectid]);
            if ($user != false && $user->auth == $config->user_auth) {
                if ($user->mnethostid != $CFG->mnet_localhost_id) {
                    mtrace("username $user->username with mnethostid = $user->mnethostid not programmed because not same site.");
                }
                // Check that an other task with same username is not already in task table.
                // This is due to username with other mnethostid for example.
                if (user_import_preferences_and_datas_task::get_task_for_username($user->username)) {
                    mtrace("username $user->username already programmed for user datas and preferences import.");
                } else {
                    user_import_preferences_and_datas_task::schedule_user_datas_import($user->username, $config->user_auth);
                }
            }
        }
    }
}
