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
 * Notifier tests
 * @package tool_import_user_datas
 * @subpackage
 * @copyright  2025 Université de Strasbourg  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_import_user_datas;
use advanced_testcase;
use filter_embedquestion\output\error_message;
use tool_import_user_datas\tools;
use tool_import_user_datas\user_import_preferences_and_datas_task;

/**
 * notifier test class
 */
final class notifier_test extends advanced_testcase {

    /**
     * Test task schedule notifications
     * @covers \local_generic_admin_notifier\admin_notification_list::notify_admin
     * @return void
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function test_admin_notifier_usernotfound(): void {
        global $DB;
        $clem = new \stdClass();
        $clem->username = 'clemence';
        $this->getDataGenerator()->create_user($clem);
        user_import_preferences_and_datas_task::schedule_user_datas_import('clemence', 'manual');
        $sink = $this->redirectMessages();
        $savedmessages = $sink->get_messages();
        $this->assertCount(0, $savedmessages);
        $sink->clear();
        $DB->delete_records('user', ['username' => $clem->username]);
        tools::import_user_preferences_and_datas_from_remote();
        $savedmessages = $sink->get_messages();
        $this->assertCount(1, $savedmessages);
        $this->assertEquals(
            $savedmessages[0]->fullmessage,
            get_string(
                'admin_notifier_usernotfound',
                'tool_import_user_datas',
                (object)['username' => 'clemence']
            )
        );
        $sink->close();
    }

    /**
     * Test error notification while calling webservice
     * @covers \local_generic_admin_notifier\admin_notification_list::notify_admin
     * @return void
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function test_admin_notifier_errorwhilecallingwebservice(): void {
        $user = $this->getDataGenerator()->create_user();
        user_import_preferences_and_datas_task::schedule_user_datas_import($user->username, 'manual');
        $sink = $this->redirectMessages();
        $savedmessages = $sink->get_messages();
        $this->assertCount(0, $savedmessages);
        $sink->clear();
        tools::import_user_preferences_and_datas_from_remote();
        $savedmessages = $sink->get_messages();
        $this->assertCount(1, $savedmessages);
        $this->assertStringStartsWith(
            get_string(
                'admin_notifier_errorwhilecallingwebservice',
                'tool_import_user_datas',
                (object)['username' => $user->username, 'errormessage' => '']
            ),
            $savedmessages[0]->fullmessage
        );
        $sink->close();
        $this->assertEquals(
            user_import_preferences_and_datas_task::STATUS_ERROR,
            user_import_preferences_and_datas_task::get_task_status($user->username)
        );
    }

    /**
     * Tests error notification while setting preferences
     * @covers \local_generic_admin_notifier\admin_notification_list::notify_admin
     * @return void
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    public function test_admin_notifier_errorwhilsettingpreferencesanddatas(): void {
        $user = $this->getDataGenerator()->create_user();
        user_import_preferences_and_datas_task::schedule_user_datas_import($user->username, 'manual');
        $sink = $this->redirectMessages();
        $savedmessages = $sink->get_messages();
        $this->assertCount(0, $savedmessages);
        $sink->clear();
        $mockedresponse = [
            'preferences' => [
                ['name' => 'country', 'value' => 'inexistant'],
            ],
        ];
        \curl::mock_response(json_encode($mockedresponse));
        tools::import_user_preferences_and_datas_from_remote();
        $savedmessages = $sink->get_messages();
        $this->assertCount(1, $savedmessages);
        $this->assertStringStartsWith(
            get_string(
                'admin_notifier_errorwhilsettingpreferencesanddatas',
                'tool_import_user_datas',
                (object)['username' => $user->username, 'errormessage' => '']
            ),
            $savedmessages[0]->fullmessage
        );
        $sink->close();
        $this->assertEquals(
            user_import_preferences_and_datas_task::STATUS_ERROR,
            user_import_preferences_and_datas_task::get_task_status($user->username)
        );
    }

    /**
     * setup
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->preventResetByRollback(); // Logging waits till the transaction gets committed.
    }
}
