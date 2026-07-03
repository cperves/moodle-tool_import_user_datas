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
 * Privacy Subsystem implementation for tool_import_user_datas.
 *
 * @package    tool_import_user_datas
 * @copyright  2025 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_import_user_datas\privacy;

use context_course;
use context_user;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use tool_import_user_datas\user_import_preferences_and_datas_task;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Privacy Subsystem for block_my_external_backup_restore_courses implementing metadata, plugin, and user_preference providers.
 *
 * @copyright  2019 University of Strasbourg
 * @author Céline Pervès <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Returns meta data about this system.
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'tool_import_user_datas',
            [
                'username' => 'privacy:metadata:tool_import_user_datas:username',
                'auth' => 'privacy:metadata:tool_import_user_datas:auth',
                'status' => 'privacy:metadata:tool_import_user_datas:status',
                'timecreated' => 'privacy:metadata:tool_import_user_datas:timecreated',
                'timemodified' => 'privacy:metadata:tool_import_user_datas:timemodified',
                'timeprocessed' => 'privacy:metadata:tool_import_user_datas:timeprocessed',
            ],
            'privacy:metadata:block_my_external_backup_restore_courses:tool_import_user_datas'
        );
        $collection->add_external_location_link(
            'remote.moodle',
            [
                'username' =>
                    'privacy:metadata:tool_import_user_datas:remote_moodle:username',
            ],
            'privacy:metadata:tool_import_user_datas:remote_moodle'
        );
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        // Store datas in system and course context.
        // Since enrolment is a subsystem link does not return user enrolments in course.
        $contextlist = new contextlist();
        $contextlist->add_user_context($userid);
        // Add linked course context.
        $sql = 'select ctx.id from {tool_import_user_datas} i
                    inner join {user} u on u.username = i.username
                    inner join {context} ctx on ctx.instanceid=u.id and ctx.contextlevel=:usercontext
                    where u.id=ctx.instanceid';
        $params = [
            'usercontext' => CONTEXT_USER,
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context instanceof context_user || $context instanceof context_course) {
            if ($context instanceof context_user) {
                $sql = 'select u.id as userid from {tool_import_user_datas} i
                            inner join {user} u on u.username=i.username jwhere u.id=:userid';
                $params = [
                    'userid' => $context->instanceid,
                ];
                $userlist->add_from_sql('userid', $sql, $params);
            }
        }
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        // Sanitize contexts.
        $aprovedcontextlist = self::validate_contextlist_contexts($contextlist, [CONTEXT_USER]);
        if (empty($aprovedcontextlist)) {
            return;
        }
        $entries = [];
        // Return database entries.
        foreach ($aprovedcontextlist as $approvedcontext) {
            if ($approvedcontext instanceof \context_user) {
                $entries = $DB->get_records_sql(
                    'select i.* from {tool_import_user_datas} i
                    inner join {user} u on u.username=i.username where u.id=:userid',
                    ['userid' => $approvedcontext->instanceid]
                );
            }
            if (!empty($entries)) {
                writer::with_context($approvedcontext)->export_data(
                    [
                        get_string('pluginname', 'tool_import_user_datas'),
                    ],
                    (object)['import_user_datas_tasks' => $entries]
                );
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        // Can't remove enrolment since can\'t be sure it comes from.
        if ($context instanceof \context_user) {
            $DB->execute(
                "delete from {tool_import_user_datas}
                        where username in (
                         select username from {user} where id=:userid
                        )
                        and status <> :status",
                [
                    'userid' => $context->instanceid,
                    'status' => user_import_preferences_and_datas_task::STATUS_INPROGRESS,
                ]
            );
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_user) {
                $DB->execute(
                    "delete from {tool_import_user_datas}
                        where username in (
                         select username from {user} where id=:userid
                        )
                        and status <> :status",
                    [
                        'userid' => $context->instanceid,
                        'status' => user_import_preferences_and_datas_task::STATUS_INPROGRESS,
                    ]
                );
            }
        }
    }
    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        foreach ($userlist as $user) {
            if ($context instanceof \context_user && $user->id == $context->instanceid) {
                $DB->execute(
                    "delete from {tool_import_user_datas}
                        where username in (
                         select username from {user} where id=:userid
                        )
                        and status <> :status",
                    [
                        'userid' => $context->instanceid,
                        'status' => user_import_preferences_and_datas_task::STATUS_INPROGRESS,
                    ]
                );
            }
        }
    }

    /**
     * Sanitize contextlist course and system context
     * @param approved_contextlist $contextlist
     * @param $contextlevellist
     * @return mixed
     */
    protected static function validate_contextlist_contexts(approved_contextlist $contextlist, $contextlevellist) {
        return array_reduce($contextlist->get_contexts(), function ($carry, $context) use ($contextlevellist) {
            if (in_array($context->contextlevel, $contextlevellist)) {
                $carry[$context->id] = $context;
            }
            return $carry;
        }, []);
    }
}
