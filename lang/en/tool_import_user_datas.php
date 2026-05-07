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
 *
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['activated'] = 'Activated';
$string['activated_desc'] = 'Plugin is activated';
$string['addimportusertask'] = 'Add import user task';
$string['adhoctaks'] = 'Use adhoc tasks';
$string['adhoctaks_desc'] = 'Use adhoc tasks, one per user import';
$string['admin_notifier_errorwhilecallingwebservice'] = 'Import user datas : error while updating status for user {$a->username}, error message : {$a->errormessage}';
$string['admin_notifier_errorwhilsettingpreferencesanddatas'] = 'Import user datas : error while settings preferences and datas  for user {$a->username}, error message : {$a->errormessage}';
$string['admin_notifier_mail_subject'] = 'Error while import user datas and preferences';
$string['admin_notifier_usernotfound'] = 'User {$a->username} not found';
$string['auth'] = 'auth method';
$string['auth_help'] = 'enter auth method without plugin type; e.g manual';
$string['email'] = 'Email';
$string['errorstatus'] = 'Error';
$string['firstname'] = 'Firstname';
$string['id'] = 'id';
$string['import_taskXuser_already_exists'] = 'Import task already exists for user {$a}';
$string['import_user_datas:get_user_preferences_and_datas_for_user'] = 'get user preferences and datas for other users';
$string['import_user_datas:set_user_preferences_and_datas_for_user'] = 'Get user preferences and datas for other users';
$string['import_user_datas_entity'] = "Import user datas task";
$string['inprogressstatus'] = 'In progress';
$string['lastname'] = 'Lastname';
$string['managetasks'] = 'Manage import user datas tasks';
$string['messageprovider:error_import_user_datas_provider'] = 'Import user datas error message provider';
$string['paging'] = 'Paging';
$string['paging_desc'] = 'Paging';
$string['performedstatus'] = 'Performed';
$string['performscheduletask'] = 'Import user datas task that launch user datas and preferences sync beetween remote and current moodle';
$string['pluginname'] = 'Import user datas from a remote Moodle';
$string['preferences'] = 'Preferences';
$string['preferences_desc'] = 'Preferences';
$string['privacy:metadata:block_my_external_backup_restore_courses:tool_import_user_datas'] = 'tool_import_user_datas Table taht store import user datas tasks';
$string['privacy:metadata:tool_import_user_datas:auth'] = 'Auth method concerned with user data import';
$string['privacy:metadata:tool_import_user_datas:remote_moodle'] = 'Remote Moodle';
$string['privacy:metadata:tool_import_user_datas:remote_moodle:username'] = 'username for to retrieve user infos from remote moodle';
$string['privacy:metadata:tool_import_user_datas:status'] = 'Status of the task';
$string['privacy:metadata:tool_import_user_datas:timecreated'] = 'Time when task was created';
$string['privacy:metadata:tool_import_user_datas:timemodified'] = 'Time when task was modified';
$string['privacy:metadata:tool_import_user_datas:timeprocessed'] = 'Time when task was processed';
$string['privacy:metadata:tool_import_user_datas:username'] = 'username for who user datas will be imported ';
$string['remote_token'] = 'Remote Token';
$string['remote_token_desc'] = 'Remote Token for webservices';
$string['remote_url'] = 'Remote Moodle URL';
$string['remote_url_desc'] = 'Remote Moodle URL';
$string['scheduledstatus'] = 'Scheduled';
$string['settings'] = 'Settings';
$string['status'] = 'Status';
$string['task_added_success'] = 'Import Task created with success for user {$a->username} with auth {$a->auth}.';
$string['timecreated'] = 'Time created';
$string['timemodified'] = 'Time modified';
$string['timeprocessed'] = 'Time processed';
$string['trigger_on_create'] = 'Trigger on create';
$string['trigger_on_create_desc'] = 'Trigger on create';
$string['user_auth'] = 'Auth method for user';
$string['user_auth_desc'] = 'Auth method concerned for importing users:  moodle plugin name separated by ;';
$string['user_datas'] = 'User datas';
$string['user_datas_desc'] = 'User datas';
$string['user_fields'] = 'User fields';
$string['user_fields_desc'] = 'User fields';
$string['user_not_exists'] = 'user {$a->username} with auth {$a->auth} not found';
$string['username'] = 'Username';
