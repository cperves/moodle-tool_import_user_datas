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

namespace tool_import_user_datas;
use advanced_testcase;
use phpunit_util;
use tool_import_user_datas\task\perform_scheduled_preferences_import;

/**
 * test task scheduling
 * @package tool_import_user_datas
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class preferences_and_datas_task_test extends advanced_testcase {

    /**
     * test task scheduled
     * @covers \tool_import_user_datas\user_import_preferences_and_datas_task::schedule_user_datas_import
     * @return void
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function test_schedule_user_datas_import(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $dbrecords = $DB->get_records('tool_import_user_datas');
        $this->assertCount(0, $dbrecords);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user->username, $user->auth);
        $dbrecords = $DB->get_records('tool_import_user_datas');
        $this->assertCount(1, $dbrecords);
        $userrecord = array_pop($dbrecords);
        $this->assertEquals($user->username, $userrecord->username);
        $this->assertEquals($user->auth, $userrecord->auth);
        $this->assertEquals(user_import_preferences_and_datas_task::STATUS_SHEDULED, $userrecord->status);
    }

    /**
     * test various task state update
     * @covers \tool_import_user_datas\user_import_preferences_and_datas_task::update_scheduled_status
     * @return void
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function update_scheduled_status_test(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        user_import_preferences_and_datas_task::schedule_user_datas_import($user->username, $user->auth);
        $this->check_status($user->username, user_import_preferences_and_datas_task::STATUS_SHEDULED);
        user_import_preferences_and_datas_task::update_scheduled_status(
            $user->username,
            user_import_preferences_and_datas_task::STATUS_INPROGRESS
        );
        $this->check_status($user->username, user_import_preferences_and_datas_task::STATUS_INPROGRESS);
        user_import_preferences_and_datas_task::update_scheduled_status(
            $user->username,
            user_import_preferences_and_datas_task::STATUS_ERROR
        );
        $this->check_status($user->username, user_import_preferences_and_datas_task::STATUS_ERROR);
        user_import_preferences_and_datas_task::update_scheduled_status(
            $user->username,
            user_import_preferences_and_datas_task::STATUS_PERFORMED
        );
        $this->check_status($user->username, user_import_preferences_and_datas_task::STATUS_PERFORMED);

    }

    /**
     * test task to perform retrieval
     * @covers \tool_import_user_datas\user_import_preferences_and_datas_task::retrieve_tasks_to_perform
     * @return void
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function test_retrieve_tasks_to_perform(): void {
        global $DB;
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $dbrecords = $DB->get_records('tool_import_user_datas');
        $this->assertCount(0, $dbrecords);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user2->username, $user2->auth);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user3->username, $user3->auth);
        $tasks = user_import_preferences_and_datas_task::retrieve_tasks_to_perform();
        $this->assertCount(3, $tasks);
    }

    /**
     * test task to perform retrieval paging
     * @covers \tool_import_user_datas\user_import_preferences_and_datas_task::retrieve_tasks_to_perform
     * @return void
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function test_retrieve_tasks_to_perform_paging(): void {
        global $DB;
        set_config('paging', '2', 'tool_import_user_datas');
        set_config('activated', 1, 'tool_import_user_datas');
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();
        $user5 = $this->getDataGenerator()->create_user();
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user2->username, $user2->auth);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user3->username, $user3->auth);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user4->username, $user4->auth);
        user_import_preferences_and_datas_task::schedule_user_datas_import($user5->username, $user5->auth);
        $tasks = user_import_preferences_and_datas_task::retrieve_tasks_to_perform();
        $this->assertCount(2, $tasks);
        $task = array_shift($tasks);
        $this->assertEquals($user1->username, $task->username);
        $task = array_shift($tasks);
        $this->assertEquals($user2->username, $task->username);
    }

    /**
     * test task to perform cron
     * @covers \tool_import_user_datas\task\perform_scheduled_preferences_import
     * @return void
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function test_perform_task_cron(): void {
        self::setAdminUser();
        set_config('activated', 1, 'tool_import_user_datas');
        $user1 = $this->getDataGenerator()->create_user();
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        $tasks = user_import_preferences_and_datas_task::retrieve_tasks_to_perform();
        $this->assertCount(1, $tasks);
        $mockedresponse = [
            'preferences' => [
                ['name' => 'htmleditor', 'value' => 'tiny'],
                ['name' => 'calendar_maxevents', 'value' => 1],
            ],
            'userdatas' => [
                ['name' => 'autosubscribe', 'value' => 0],
                ['name' => 'city', 'value' => 'Strasbourg'],
                ['name' => 'firstnamephonetic', 'value' => 'fɜ:sneɪmefəʊnetɪk'],
            ],
        ];
        \curl::mock_response(json_encode($mockedresponse));
        // Var mockresponse will only be used one times, cleaned after fisrt use.
        $cron = new perform_scheduled_preferences_import();
        $cron->execute();
        $tasks = user_import_preferences_and_datas_task::retrieve_tasks_to_perform();
        $this->assertCount(0, $tasks);
        $tasks = user_import_preferences_and_datas_task::retrieve_tasks(
            user_import_preferences_and_datas_task::STATUS_PERFORMED
        );
        $this->assertCount(1, $tasks);
    }

    /**
     * test task to perform adhoc task
     * @covers \tool_import_user_datas\user_import_preferences_and_datas_task::retrieve_tasks_to_perform
     * @return void
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function test_perform_task_adhoc(): void {
        self::setAdminUser();
        set_config('activated', 1, 'tool_import_user_datas');
        set_config('adhoctasks', 1, 'tool_import_user_datas');
        $user1 = $this->getDataGenerator()->create_user();
        user_import_preferences_and_datas_task::schedule_user_datas_import($user1->username, $user1->auth);
        $tasks = user_import_preferences_and_datas_task::retrieve_tasks_to_perform();
        $this->assertCount(1, $tasks);
        $mockedresponse = [
            'preferences' => [
                ['name' => 'country', 'value' => 'inexistant'],
            ],
        ];
        \curl::mock_response(json_encode($mockedresponse));
        // Var mockresponse will only be used one times, cleaned after fisrt use.
        $cron = new perform_scheduled_preferences_import();
        $cron->execute();
        $tasks = user_import_preferences_and_datas_task::retrieve_tasks_to_perform();
        $this->assertCount(1, $tasks);
        phpunit_util::run_all_adhoc_tasks();
        $tasks = user_import_preferences_and_datas_task::retrieve_tasks(
            user_import_preferences_and_datas_task::STATUS_PERFORMED
        );
        $this->assertCount(1, $tasks);
    }

    /**
     * check status
     * @param moodle_database $DB
     * @param stdClass $user
     * @param $userrecord
     * @return void
     * @throws dml_exception
     */
    private function check_status($username, $status): void {
        global $DB;
        $dbrecord = $DB->get_record('tool_import_user_datas', ['username' => $username]);
        $this->assertEquals($status, $dbrecord->status);
    }

    /**
     * SetUp class
     * @return void
     *
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->preventResetByRollback(); // Logging waits till the transaction gets committed.
    }
}
